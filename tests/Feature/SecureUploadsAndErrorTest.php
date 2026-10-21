<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\DocumentController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Upload;
use App\Core\View;
use App\Models\User;

// Load routes
require_once ROOT_PATH . '/config/routes.php';

echo "=== Running Secure Uploads & Error Handling Tests ===\n\n";

// --------------------------------------------------------------------------
// TEST 1: Secure Uploads Stored Outside Webroot in storage/private_documents
// --------------------------------------------------------------------------
echo "[TEST 1] Testing Upload::processSecure stores files outside webroot...\n";

// Create a dummy KYC document (PDF)
$tempPdf = tempnam(sys_get_temp_dir(), 'kyc_test_');
file_put_contents($tempPdf, "%PDF-1.4\n%Test KYC Document for Partner Verification\n%%EOF");

$fakeKycFile = [
    'name'     => 'aadhaar_card_proof.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => $tempPdf,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempPdf),
];

$secureResult = Upload::processSecure($fakeKycFile, 'partner_docs');

assert(
    !empty($secureResult['relative_path']),
    "FAIL: processSecure must return relative_path"
);
assert(
    file_exists($secureResult['full_path']),
    "FAIL: Secure document must exist on disk at " . $secureResult['full_path']
);

// Verify the file is strictly outside public/ webroot
$publicDir = realpath(ROOT_PATH . '/public');
$documentDir = realpath($secureResult['full_path']);
assert(
    !str_starts_with($documentDir, $publicDir),
    "FAIL: Sensitive document was found inside public/ webroot! Must be outside in storage/private_documents"
);

assert(
    str_contains(str_replace('\\', '/', $secureResult['full_path']), 'storage/private_documents'),
    "FAIL: Document path does not contain storage/private_documents"
);

echo "[PASS] Test 1: Secure document successfully isolated outside webroot in storage/private_documents.\n\n";

// --------------------------------------------------------------------------
// TEST 2: Secure Uploads Reject Malicious/Unapproved MIME Types
// --------------------------------------------------------------------------
echo "[TEST 2] Testing processSecure rejects dangerous script uploads (e.g. PHP)...\n";

$tempPhp = tempnam(sys_get_temp_dir(), 'php_test_');
file_put_contents($tempPhp, "<?php phpinfo(); ?>");

$fakePhpFile = [
    'name'     => 'webshell.php',
    'type'     => 'application/x-php',
    'tmp_name' => $tempPhp,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempPhp),
];

$threwMimeException = false;
try {
    Upload::processSecure($fakePhpFile, 'partner_docs');
} catch (\RuntimeException $e) {
    $threwMimeException = true;
    assert(str_contains($e->getMessage(), 'Invalid file format'), "FAIL: Unexpected exception message: " . $e->getMessage());
}
@unlink($tempPhp);

assert($threwMimeException === true, "FAIL: Malicious PHP file was not rejected by processSecure!");

echo "[PASS] Test 2: Dangerous files strictly rejected by MIME validator.\n\n";

// --------------------------------------------------------------------------
// TEST 3: DocumentController Rejects Unauthenticated Access
// --------------------------------------------------------------------------
echo "[TEST 3] Testing unauthenticated access to /documents is blocked...\n";

// Clear session
$_SESSION = [];

$docController = new DocumentController();
$unauthReq = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/documents/partner_docs/' . $secureResult['filename'],
]);

$unauthResp = $docController->serve($unauthReq, 'partner_docs', $secureResult['filename']);
assert(
    $unauthResp->getStatusCode() === 401,
    "FAIL: Unauthenticated access must return 401 Unauthorized, got " . $unauthResp->getStatusCode()
);

echo "[PASS] Test 3: Unauthenticated request to private documents blocked with 401 Unauthorized.\n\n";

// --------------------------------------------------------------------------
// TEST 4: DocumentController Blocks Path Traversal & Unauthorized Folders
// --------------------------------------------------------------------------
echo "[TEST 4] Testing path traversal defenses in DocumentController...\n";

// Authenticate as real super admin from database
$adminUser = \App\Core\Database::fetchOne("SELECT * FROM users WHERE role = 'super_admin' AND status = 'active' LIMIT 1");
assert($adminUser !== null, "FAIL: Super admin user must exist in database");
Auth::login($adminUser);

// Path traversal test
$traversalReq = new Request([], [], ['REQUEST_METHOD' => 'GET']);
$traversalResp = $docController->serve($traversalReq, 'partner_docs', '../../.env');
assert(
    $traversalResp->getStatusCode() === 403,
    "FAIL: Path traversal attempt must return 403 Forbidden, got " . $traversalResp->getStatusCode()
);

// Invalid folder test
$invalidFolderResp = $docController->serve($traversalReq, 'system_configs', 'app.php');
assert(
    $invalidFolderResp->getStatusCode() === 403,
    "FAIL: Access to non-whitelisted folder must return 403 Forbidden, got " . $invalidFolderResp->getStatusCode()
);

echo "[PASS] Test 4: Path traversal and illegal category access safely blocked with 403 Forbidden.\n\n";

// --------------------------------------------------------------------------
// TEST 5: DocumentController Streams Secure Document for Authorized User
// --------------------------------------------------------------------------
echo "[TEST 5] Testing authorized document streaming with security headers...\n";

$authResp = $docController->serve($traversalReq, 'partner_docs', $secureResult['filename']);
assert(
    $authResp->getStatusCode() === 200,
    "FAIL: Authorized admin should get 200 OK, got " . $authResp->getStatusCode()
);

$headers = $authResp->getHeaders();
assert(
    $headers['Content-Type'] === 'application/pdf',
    "FAIL: Content-Type header mismatch: " . ($headers['Content-Type'] ?? 'none')
);
assert(
    $headers['X-Content-Type-Options'] === 'nosniff',
    "FAIL: X-Content-Type-Options: nosniff header missing"
);
assert(
    str_contains($headers['Cache-Control'], 'no-store'),
    "FAIL: Cache-Control should prevent caching sensitive documents"
);
assert(
    str_contains($authResp->getBody(), '%Test KYC Document for Partner Verification'),
    "FAIL: Streamed file content mismatch"
);

echo "[PASS] Test 5: Authorized admin successfully streams document with secure no-cache headers.\n\n";

// Clean up test file
@unlink($secureResult['full_path']);

// --------------------------------------------------------------------------
// TEST 6: Api/UploadController Folder Whitelist Restriction
// --------------------------------------------------------------------------
echo "[TEST 6] Testing Api\\UploadController subfolder access control...\n";

// Unauthenticated user attempting to upload into 'services' or 'site'
$_SESSION = [];
$apiUploadController = new \App\Controllers\Api\UploadController();

$tempImg = tempnam(sys_get_temp_dir(), 'img_test_');
// 1x1 GIF
file_put_contents($tempImg, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));

$fakeImgFile = [
    'name'     => 'test.gif',
    'type'     => 'image/gif',
    'tmp_name' => $tempImg,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempImg),
];

$unauthReqUnauthorizedFolder = new Request(
    [],
    ['subfolder' => 'services'], // not in ['issues']
    ['REQUEST_METHOD' => 'POST'],
    ['file' => $fakeImgFile]
);

$apiResp = $apiUploadController->upload($unauthReqUnauthorizedFolder);
assert(
    $apiResp->getStatusCode() === 403,
    "FAIL: Unauthenticated upload to 'services' subfolder must return 403 Forbidden, got " . $apiResp->getStatusCode()
);

@unlink($tempImg);
echo "[PASS] Test 6: Api\\UploadController rejects unauthorized upload folders for guests.\n\n";

// --------------------------------------------------------------------------
// TEST 7: Branded Generic Error Templates (404 and 500)
// --------------------------------------------------------------------------
echo "[TEST 7] Verifying error views (partials/404.php and partials/500.php)...\n";

$view404 = file_get_contents(ROOT_PATH . '/app/views/partials/404.php');
assert(str_contains($view404, '404'), "FAIL: 404 view missing 404 code");
assert(!str_contains($view404, 'SQLSTATE'), "FAIL: 404 view must not expose SQL info");

$view500 = file_get_contents(ROOT_PATH . '/app/views/partials/500.php');
assert(str_contains($view500, '500'), "FAIL: 500 view missing 500 code");
assert(str_contains($view500, 'Something Went Wrong'), "FAIL: 500 view missing title");
assert(!str_contains($view500, 'stack trace'), "FAIL: 500 view must not contain stack trace");
assert(!str_contains($view500, 'DB_'), "FAIL: 500 view must not expose DB constants");

echo "[PASS] Test 7: Error views are clean, branded, and free of technical stack traces.\n\n";

echo "=== All Secure Uploads & Error Handling Tests PASSED! ===\n";
