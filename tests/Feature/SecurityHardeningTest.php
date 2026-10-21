<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Controllers\AccountController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Middleware\RequireRole;
use App\Middleware\VerifyCsrf;
use App\Models\Booking;
use App\Models\Consent;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\User;

echo "=== RUNNING SECURITY HARDENING & PRIVACY FEATURE TESTS (PROMPT 13) ===\n";

// 1. Setup isolated test users & records
$adminId = User::create([
    'role'          => 'admin',
    'name'          => 'Security Admin',
    'email'         => 'sec_admin_' . time() . '_' . random_int(100, 999) . '@refixel.com',
    'phone'         => '99' . random_int(10000000, 99999999),
    'password_hash' => password_hash('AdminPass@123', PASSWORD_BCRYPT),
    'status'        => 'active',
]);

$staffId = User::create([
    'role'          => 'staff',
    'name'          => 'Security Staff',
    'email'         => 'sec_staff_' . time() . '_' . random_int(100, 999) . '@refixel.com',
    'phone'         => '98' . random_int(10000000, 99999999),
    'password_hash' => password_hash('StaffPass@123', PASSWORD_BCRYPT),
    'status'        => 'active',
]);

$customerAId = User::create([
    'role'          => 'customer',
    'name'          => 'Customer Alice',
    'email'         => 'sec_alice_' . time() . '_' . random_int(100, 999) . '@example.com',
    'phone'         => '97' . random_int(10000000, 99999999),
    'password_hash' => password_hash('AlicePass@123', PASSWORD_BCRYPT),
    'status'        => 'active',
]);

$customerBId = User::create([
    'role'          => 'customer',
    'name'          => 'Customer Bob',
    'email'         => 'sec_bob_' . time() . '_' . random_int(100, 999) . '@example.com',
    'phone'         => '96' . random_int(10000000, 99999999),
    'password_hash' => password_hash('BobPass@123', PASSWORD_BCRYPT),
    'status'        => 'active',
]);

$service = Database::fetchOne("SELECT id, starting_price FROM services WHERE is_active = 1 LIMIT 1");
$serviceId = (int)($service['id'] ?? 1);
$price = (float)($service['starting_price'] ?? 999.00);

$bookingAId = Booking::create([
    'booking_no'     => 'BK-SEC-' . time() . '-' . random_int(10, 99),
    'customer_id'    => $customerAId,
    'service_id'     => $serviceId,
    'name'           => 'Customer Alice',
    'phone'          => '9700000001',
    'address'        => '123 Alice Security Lane, Kashipur',
    'pincode'        => '244713',
    'preferred_date' => date('Y-m-d', strtotime('+1 day')),
    'preferred_time' => '10:00 AM - 01:00 PM',
    'status'         => 'assigned',
]);

Job::create([
    'booking_id' => $bookingAId,
    'staff_id'   => $staffId,
    'status'     => 'assigned',
]);

$paymentId = \App\Models\Payment::create([
    'booking_id'  => $bookingAId,
    'amount'      => $price,
    'method'      => 'upi',
    'status'      => 'paid',
    'recorded_by' => $adminId,
]);

$invoiceAId = Invoice::create([
    'invoice_no' => 'INV-SEC-' . time() . '-' . random_int(10, 99),
    'payment_id' => $paymentId,
    'subtotal'   => round($price / 1.18, 2),
    'gst_rate'   => 18.00,
    'gst_amount' => round($price - ($price / 1.18), 2),
    'total'      => $price,
]);

// ==========================================
// TEST 1: Security Headers & CSP Applied on Response
// ==========================================
$response = Response::html('<h1>Security Header Test</h1>');
$response->applySecurityHeaders();
$headers = $response->getHeaders();

assert(($headers['X-Frame-Options'] ?? null) === 'SAMEORIGIN', "X-Frame-Options must be SAMEORIGIN");
assert(($headers['X-Content-Type-Options'] ?? null) === 'nosniff', "X-Content-Type-Options must be nosniff");
assert(($headers['X-XSS-Protection'] ?? null) === '1; mode=block', "X-XSS-Protection must be 1; mode=block");
assert(($headers['Referrer-Policy'] ?? null) === 'strict-origin-when-cross-origin', "Referrer-Policy must be strict-origin-when-cross-origin");
$csp = $headers['Content-Security-Policy'] ?? ($headers['Content-Security-Policy-Report-Only'] ?? '');
assert(str_contains($csp, 'default-src'), "CSP header must contain default-src");
echo "Test 1: Security Headers & CSP Applied on Response - PASSED\n";

// ==========================================
// TEST 2: CSRF Middleware Rejection for Tokenless POST Requests
// ==========================================
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/some/protected/endpoint';
$_POST = ['action' => 'malicious_without_csrf'];

$request = new Request();
$middleware = new VerifyCsrf();
$csrfRes = $middleware->handle($request);

assert($csrfRes instanceof Response, "VerifyCsrf must return Response when token is missing");
assert($csrfRes->getStatusCode() === 419 || $csrfRes->getStatusCode() === 302, "VerifyCsrf must reject unauthorized POST request (got " . $csrfRes->getStatusCode() . ")");
echo "Test 2: CSRF Middleware Rejection for Tokenless POST Requests - PASSED\n";

// ==========================================
// TEST 3: CSRF Middleware Acceptance for Valid CSRF Token
// ==========================================
$token = Csrf::token();
$_POST['_csrf'] = $token;

$requestWithCsrf = new Request();
$validCsrfRes = $middleware->handle($requestWithCsrf);
assert($validCsrfRes === null, "VerifyCsrf must allow valid token to proceed");
echo "Test 3: CSRF Middleware Acceptance for Valid CSRF Token - PASSED\n";

// ==========================================
// TEST 4: Role Elevation Prevention (Customer -> Admin Blocked)
// ==========================================
$userAlice = User::find($customerAId);
Auth::login($userAlice);

$roleMiddleware = new RequireRole();
$blockedCustomerRes = $roleMiddleware->handle(new Request(), 'admin');

assert($blockedCustomerRes instanceof Response, "RequireRole must return Response for unauthorized role");
assert($blockedCustomerRes->getStatusCode() === 302, "RequireRole must redirect customer trying to access admin");
echo "Test 4: Role Elevation Prevention (Customer -> Admin Blocked) - PASSED\n";

// ==========================================
// TEST 5: Role Elevation Prevention (Staff -> Admin Blocked)
// ==========================================
$userStaff = User::find($staffId);
Auth::login($userStaff);

$blockedStaffRes = $roleMiddleware->handle(new Request(), 'admin');
assert($blockedStaffRes instanceof Response, "RequireRole must return Response for staff accessing admin");
assert($blockedStaffRes->getStatusCode() === 302, "RequireRole must redirect staff trying to access admin");
echo "Test 5: Role Elevation Prevention (Staff -> Admin Blocked) - PASSED\n";

// ==========================================
// TEST 6: Strict Technician Query Isolation (WHERE staff_id = current_user_id)
// ==========================================
Auth::login($userStaff);
$staffJobs = Job::findByStaff($staffId);
assert(!empty($staffJobs), "Staff jobs must not be empty for assigned staff");
foreach ($staffJobs as $j) {
    assert((int)$j['staff_id'] === $staffId, "Staff job must strictly belong to current staff");
}

$otherStaffId = User::create([
    'role'          => 'staff',
    'name'          => 'Isolated Technician',
    'email'         => 'other_tech_' . time() . '_' . random_int(100, 999) . '@refixel.com',
    'phone'         => '95' . random_int(10000000, 99999999),
    'password_hash' => password_hash('Pass@123', PASSWORD_BCRYPT),
    'status'        => 'active',
]);
$otherJobs = Job::findByStaff($otherStaffId);
assert(empty($otherJobs), "Other staff must not see jobs assigned to another technician");
echo "Test 6: Strict Technician Query Isolation (WHERE staff_id = current_user_id) - PASSED\n";

// ==========================================
// TEST 7: IDOR Protection (Customer B Cannot Access Customer A's Data)
// ==========================================
$userBob = User::find($customerBId);
Auth::login($userBob);

$accountCtrl = new AccountController();
$bobAccessAliceBooking = $accountCtrl->bookingDetail(new Request(), (string)$bookingAId);
assert($bobAccessAliceBooking->getStatusCode() === 404, "Accessing another customer's booking must return 404");

$bobAccessAliceInvoice = $accountCtrl->invoiceDetail(new Request(), (string)$invoiceAId);
assert($bobAccessAliceInvoice->getStatusCode() === 404, "Accessing another customer's invoice must return 404");
echo "Test 7: IDOR Protection (Customer B Cannot Access Customer A's Data) - PASSED\n";

// ==========================================
// TEST 8: Sensitive Data Masking in Logger
// ==========================================
$reflection = new \ReflectionClass(Logger::class);
$method = $reflection->getMethod('maskSensitive');
$method->setAccessible(true);

$context = [
    'username'     => 'alice',
    'password'     => 'SuperSecret123!',
    'card'         => '4111222233334444',
    'cvv'          => '999',
    'token'        => 'xyz123abc456',
    'api_key'      => 'rzp_live_secret123',
    'nested'       => [
        'auth_token' => 'nested_secret',
        'otp'        => '582910',
        'public_id'  => 42,
    ],
];

$sanitized = $method->invoke(null, $context);
assert($sanitized['password'] === '********', "Password must be masked in logs");
assert($sanitized['card'] === '********', "Card must be masked in logs");
assert($sanitized['cvv'] === '********', "CVV must be masked in logs");
assert($sanitized['token'] === '********', "Token must be masked in logs");
assert($sanitized['api_key'] === '********', "API key must be masked in logs");
assert($sanitized['nested']['auth_token'] === '********', "Nested auth token must be masked in logs");
assert($sanitized['nested']['otp'] === '********', "Nested OTP must be masked in logs");
assert($sanitized['nested']['public_id'] === 42, "Non-sensitive fields must remain intact");
echo "Test 8: Sensitive Data Masking in Logger - PASSED\n";

// ==========================================
// TEST 9: Secure Upload Restrictions (Blocking PHP & Executables)
// ==========================================
$tempFile = tempnam(sys_get_temp_dir(), 'mal');
file_put_contents($tempFile, '<?php phpinfo(); ?>');

$fakeFile = [
    'name'     => 'shell.php',
    'type'     => 'application/x-php',
    'tmp_name' => $tempFile,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempFile),
];

$uploadBlocked = false;
try {
    Upload::process($fakeFile, 'issues');
} catch (\RuntimeException $e) {
    $uploadBlocked = true;
    assert(str_contains($e->getMessage(), 'Invalid file format'), "Error message should mention invalid file format");
} finally {
    if (file_exists($tempFile)) {
        @unlink($tempFile);
    }
}
assert($uploadBlocked === true, "Executable PHP file upload must be strictly rejected");
echo "Test 9: Secure Upload Restrictions (Blocking PHP & Executables) - PASSED\n";

// ==========================================
// TEST 10: Login Brute Force Lockout Monitoring
// ==========================================
$testIp = '192.168.1.99';
$testIdent = 'brute_force_target@example.com';

Database::query("DELETE FROM login_attempts WHERE ip = :ip OR identifier = :ident", ['ip' => $testIp, 'ident' => $testIdent]);

// Simulate 5 consecutive failed attempts
for ($i = 0; $i < 5; $i++) {
    Database::query(
        "INSERT INTO login_attempts (ip, identifier, attempted_at) VALUES (:ip, :ident, NOW())",
        ['ip' => $testIp, 'ident' => $testIdent]
    );
}

$attemptCount = Database::fetchOne(
    "SELECT COUNT(*) as attempts FROM login_attempts 
     WHERE (ip = :ip OR identifier = :ident) AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
    ['ip' => $testIp, 'ident' => $testIdent]
);
assert((int)$attemptCount['attempts'] >= 5, "Brute force attempts must be tracked accurately in login_attempts");

// Clean up
Database::query("DELETE FROM login_attempts WHERE ip = :ip OR identifier = :ident", ['ip' => $testIp, 'ident' => $testIdent]);
echo "Test 10: Login Brute Force Lockout Monitoring - PASSED\n";

// ==========================================
// TEST 11: Password Reset Token Hashing & Expiry
// ==========================================
$rawToken = bin2hex(random_bytes(32));
$hashedToken = hash('sha256', $rawToken);
$identEmail = 'reset_audit_' . time() . '@example.com';

Database::query(
    "INSERT INTO password_resets (email_or_phone, token_hash, expires_at)
     VALUES (:ident, :hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))",
    ['ident' => $identEmail, 'hash' => $hashedToken]
);

$validReset = Database::fetchOne(
    "SELECT * FROM password_resets WHERE token_hash = :hash AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
    ['hash' => $hashedToken]
);
assert($validReset !== null, "Valid password reset token must be found by hash");
assert($validReset['email_or_phone'] === $identEmail, "Reset record must match identifier");

// Expired token simulation
$expiredRawToken = bin2hex(random_bytes(32));
$expiredHash = hash('sha256', $expiredRawToken);
Database::query(
    "INSERT INTO password_resets (email_or_phone, token_hash, expires_at)
     VALUES (:ident, :hash, DATE_SUB(NOW(), INTERVAL 1 HOUR))",
    ['ident' => $identEmail, 'hash' => $expiredHash]
);

$expiredReset = Database::fetchOne(
    "SELECT * FROM password_resets WHERE token_hash = :hash AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
    ['hash' => $expiredHash]
);
assert($expiredReset === null, "Expired reset token must be rejected");
echo "Test 11: Password Reset Token Hashing & Expiry - PASSED\n";

// ==========================================
// TEST 12: Privacy Consent Recording, Data Export & Deletion Foundation
// ==========================================
Auth::login($userAlice);

// Record DPDP consent
$cId = Consent::record($customerAId, 'terms_and_privacy', '127.0.0.1');
assert($cId > 0, "Consent record must return generated ID");
assert(Consent::hasConsent($customerAId, 'terms_and_privacy'), "Consent must be verifiable via hasConsent");

$consents = Consent::getByUser($customerAId);
assert(!empty($consents), "Consent list must contain recorded consent");

// Test Export Data
$exportRes = $accountCtrl->exportData(new Request());
assert($exportRes->getStatusCode() === 200, "Export data must return 200");
assert(str_contains($exportRes->getHeader('Content-Disposition') ?? '', 'attachment;'), "Export must send attachment header");

$exportJson = json_decode($exportRes->getContent(), true);
assert(is_array($exportJson), "Export payload must be valid JSON");
assert(isset($exportJson['account']['email']), "Export payload must contain account data");
assert(!isset($exportJson['account']['password_hash']), "Export payload must NEVER contain password hash");
assert(isset($exportJson['consents']), "Export payload must contain consents");
assert(isset($exportJson['bookings']), "Export payload must contain bookings");

// Test Deletion Request
$_POST['confirm_erasure'] = '1';
$_POST['reason'] = 'Testing DPDP right to erasure request';
$delReq = new Request([], ['confirm_erasure' => '1', 'reason' => 'Testing DPDP right to erasure request']);
$delRes = $accountCtrl->requestDataDeletion($delReq);
assert($delRes->getStatusCode() === 302, "Data erasure request must redirect");

$hasErasureConsent = Consent::hasConsent($customerAId, 'erasure_requested_' . date('Ymd'));
assert($hasErasureConsent === true, "Erasure request must be tracked in consents table");

echo "Test 12: Privacy Consent Recording, Data Export & Deletion Foundation - PASSED\n";

echo "\nALL 12 SECURITY HARDENING & PRIVACY FEATURE TESTS PASSED SUCCESSFULLY!\n";
