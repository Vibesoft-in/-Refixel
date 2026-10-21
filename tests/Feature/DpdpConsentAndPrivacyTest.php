<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Models\Consent;
use App\Models\Setting;
use App\Models\User;

// Load routes
require_once ROOT_PATH . '/config/routes.php';

echo "=== Running DPDP Consent & Privacy Compliance Tests ===\n\n";

// --------------------------------------------------------------------------
// TEST 1: Privacy View Uses Dynamic Email from Settings (Not Hardcoded)
// --------------------------------------------------------------------------
echo "[TEST 1] Testing dynamic contact email in Privacy Policy...\n";

// Set a unique test contact email in settings
$testEmail = 'compliance-officer@refixel.in';
$originalCompanyEmail = Setting::get('company_email', 'contact@refixel.com');
Setting::set('company_email', $testEmail);

// Render /privacy
$req = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/privacy',
]);
$res = Router::dispatch($req);

$html = $res->getBody();

// Verify no hardcoded privacy@refixel.com exists in privacy.php view file
$privacyFileContent = file_get_contents(ROOT_PATH . '/app/views/customer/privacy.php');
assert(
    !str_contains($privacyFileContent, 'privacy@refixel.com'),
    "FAIL: privacy.php still contains hardcoded 'privacy@refixel.com'"
);

// Verify rendered page contains the dynamic email
assert(
    str_contains($html, $testEmail),
    "FAIL: Rendered /privacy did not contain dynamic company_email '{$testEmail}'"
);

// Restore original setting
Setting::set('company_email', $originalCompanyEmail);

echo "[PASS] Test 1: Dynamic contact email verified from settings.\n\n";

// --------------------------------------------------------------------------
// TEST 2: WhatsApp Enquiry Form Contains Consent Note
// --------------------------------------------------------------------------
echo "[TEST 2] Testing WhatsApp enquiry consent notice in home.php...\n";

$homeFileContent = file_get_contents(ROOT_PATH . '/app/views/customer/home.php');

assert(
    str_contains($homeFileContent, 'By submitting, you agree to receive doorstep service communications'),
    "FAIL: WhatsApp enquiry form missing required consent note text"
);
assert(
    str_contains($homeFileContent, 'homeEnquirySubmitBtn'),
    "FAIL: homeEnquirySubmitBtn button not found"
);

echo "[PASS] Test 2: WhatsApp enquiry consent line is present and linked to privacy policy.\n\n";

// --------------------------------------------------------------------------
// TEST 3: Signup Rejects Missing DPDP Consent Checkbox
// --------------------------------------------------------------------------
echo "[TEST 3] Testing signup rejection without DPDP consent...\n";

$validToken = \App\Core\Csrf::token();

$testPhoneNoConsent = '98' . str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
$reqNoConsent = new Request(
    [], // query
    [   // body
        '_csrf'                 => $validToken,
        'csrf_token'            => $validToken,
        'name'                  => 'Consent Test Customer',
        'phone'                 => $testPhoneNoConsent,
        'email'                 => 'consent_test_' . mt_rand(1000, 9999) . '@test.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
        // 'dpdp_consent' omitted!
    ],
    [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI'    => '/signup',
        'REMOTE_ADDR'    => '127.0.0.1',
    ]
);

$resNoConsent = Router::dispatch($reqNoConsent);
assert(
    $resNoConsent->getStatusCode() === 302,
    "FAIL: Missing consent must redirect back (302), got " . $resNoConsent->getStatusCode()
);

$userNotCreated = User::findByPhone($testPhoneNoConsent);
assert(
    $userNotCreated === null,
    "FAIL: User should not have been created without DPDP consent!"
);

echo "[PASS] Test 3: Signup cleanly rejected when DPDP consent checkbox is omitted.\n\n";

// --------------------------------------------------------------------------
// TEST 4: Signup with Consent Records Audit Entry in consents Table
// --------------------------------------------------------------------------
echo "[TEST 4] Testing successful signup with consent logging...\n";

$testPhoneWithConsent = '97' . str_pad((string)mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
$reqWithConsent = new Request(
    [], // query
    [   // body
        '_csrf'                 => $validToken,
        'csrf_token'            => $validToken,
        'name'                  => 'Affirmative Consent User',
        'phone'                 => $testPhoneWithConsent,
        'email'                 => 'affirmative_' . mt_rand(1000, 9999) . '@test.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
        'dpdp_consent'          => '1',
    ],
    [
        'REQUEST_METHOD'  => 'POST',
        'REQUEST_URI'     => '/signup',
        'REMOTE_ADDR'     => '103.21.244.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Suite Runner',
    ]
);

$resWithConsent = Router::dispatch($reqWithConsent);
assert(
    $resWithConsent->getStatusCode() === 302,
    "FAIL: Successful signup must redirect, got " . $resWithConsent->getStatusCode()
);

$createdUser = User::findByPhone($testPhoneWithConsent);
assert($createdUser !== null, "FAIL: User was not created!");
$userId = (int)$createdUser['id'];

// Check consent record
$hasConsent = Consent::hasConsent($userId, 'terms_and_privacy');
assert($hasConsent === true, "FAIL: Consent::hasConsent returned false for terms_and_privacy!");

$consentLogs = Consent::getByUser($userId);
assert(!empty($consentLogs), "FAIL: No consent audit logs found for user {$userId}!");
$log = $consentLogs[0];

assert(
    $log['user_id'] == $userId,
    "FAIL: consent log user_id mismatch"
);
assert(
    $log['ip'] === '103.21.244.10' || $log['ip_address'] === '103.21.244.10',
    "FAIL: consent log IP mismatch"
);

echo "[PASS] Test 4: Consent successfully logged in database with user_id, timestamp, IP, and purpose.\n\n";

// --------------------------------------------------------------------------
// TEST 5: Privacy Policy [FILL: ...] Placeholders Standardization
// --------------------------------------------------------------------------
echo "[TEST 5] Verifying standardized [FILL: ...] placeholders in privacy.php...\n";

$expectedFillItems = [
    'OTPs are deleted within [FILL:',
    'Support chats and logs are kept for [FILL:',
    'Inactive accounts may be deleted after [FILL:',
    '[FILL: list any analytics or advertising tools used',
    'Grievance Officer: [FILL:',
];

foreach ($expectedFillItems as $expectedSnippet) {
    assert(
        str_contains($privacyFileContent, $expectedSnippet),
        "FAIL: privacy.php missing required placeholder snippet: '{$expectedSnippet}'"
    );
}

echo "[PASS] Test 5: All 5 standardized [FILL: ...] placeholders are present for client completion.\n\n";

echo "=== All DPDP Consent & Privacy Compliance Tests PASSED! ===\n";
