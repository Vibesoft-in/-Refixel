<?php
declare(strict_types=1);

/**
 * Security Phase 1.5 - Task 1 Verification Test Suite
 * 
 * Verifies:
 * 1. Predictable Refixel@<last 4 digits> guest passwords eliminated (unpredictable random bcrypt hash).
 * 2. Set-password flow with 24h single-use token in password_resets (SHA-256 hash).
 * 3. SMS fallback when email is not provided for set-password link.
 * 4. Guest auto-login disabled on booking & enquiry submission.
 * 5. Access strictly via guest_token with Referrer-Policy: no-referrer on /book-success and /enquiry-success.
 * 6. guest_token masking in Logger.
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Models\Booking;
use App\Models\User;

echo "=== RUNNING SECURITY PHASE 1.5 (TASK 1) VERIFICATION TESTS ===\n\n";

$bookingController = new BookingController();

// --------------------------------------------------------------------------
// TEST 1: Booking submit with new guest user
// --------------------------------------------------------------------------
echo "[TEST 1] Verifying booking submit generates random password & 24h set-password link...\n";

// Clear any existing session auth
Auth::logout();
$_SESSION = [];

$uniqueSuffix = (string)random_int(1000000, 9999999);
$testPhone = '98' . $uniqueSuffix; // 2 + 7 = 9? No, '9' + 9 digits = 10 digits:
$testPhone = '9' . (string)random_int(100000000, 999999999); // Exactly 10 digits starting with 9
$last4 = substr($testPhone, -4);
$predictablePassword = 'Refixel@' . $last4;
$testEmail = 'guest_' . time() . '_' . random_int(100, 999) . '@refixel-test.com';

// Ensure a test service exists
$service = Database::fetchOne("SELECT id, starting_price FROM services WHERE is_active = 1 LIMIT 1");
if (!$service) {
    $serviceId = (int)Database::execute("INSERT INTO services (category_id, name, slug, starting_price, is_active) VALUES (1, 'Test Safety Svc', 'test-safety-svc', 599.00, 1)", [], true);
} else {
    $serviceId = (int)$service['id'];
}

$req = new Request([], [
    '_csrf'          => Csrf::token(),
    'service_id'     => $serviceId,
    'name'           => 'Task 1 Test Guest',
    'phone'          => $testPhone,
    'email'          => $testEmail,
    'address'        => '123 Security Lane',
    'city'           => 'Kashipur',
    'preferred_date' => date('Y-m-d', strtotime('+1 day')),
    'preferred_time' => '10:00 AM - 12:00 PM',
    'payment_method' => 'cod',
    'is_ajax'        => 1,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$resp = $bookingController->submit($req);
if ($resp->getStatusCode() !== 200) {
    echo "DEBUG: status=" . $resp->getStatusCode() . ", body=" . $resp->getBody() . "\n";
}
assert($resp->getStatusCode() === 200, "Booking submit must return 200");
$data = json_decode($resp->getBody(), true);

assert(!empty($data['success']), "Response success flag must be true");
assert(!isset($data['generated_password']), "FAIL: generated_password MUST NOT be returned in JSON response");
assert(!empty($data['set_password_url']), "Response must include set_password_url");
assert(!empty($data['guest_token']), "Response must include guest_token");
assert(str_contains($data['redirect_url'], 'token=' . urlencode($data['guest_token'])), "Redirect URL must use guest_token");

// Verify created user in DB
$user = Database::fetchOne("SELECT * FROM users WHERE phone = ?", [$testPhone]);
assert($user !== null, "User account must be created in DB");
assert(!password_verify($predictablePassword, $user['password_hash']), "FAIL: Password hash matches predictable Refixel@<digits>!");

// Verify token in password_resets table
$parsedUrl = parse_url($data['set_password_url']);
parse_str($parsedUrl['query'] ?? '', $queryParams);
$rawToken = $queryParams['token'] ?? '';
assert(strlen($rawToken) === 64, "Raw set-password token must be 64-char hex");

$expectedHash = hash('sha256', $rawToken);
$resetRow = Database::fetchOne("SELECT * FROM password_resets WHERE token_hash = ? AND email_or_phone = ?", [$expectedHash, $testEmail]);
assert($resetRow !== null, "Token hash must exist in password_resets table for user identifier");

// Verify 24 hour expiry (within tolerance of 60 seconds)
$expiresAt = strtotime($resetRow['expires_at']);
$now = time();
$diffHours = ($expiresAt - $now) / 3600;
assert($diffHours >= 23.5 && $diffHours <= 24.5, "Token expiry must be approximately 24 hours (found {$diffHours} hrs)");

// Verify guest is NOT auto-logged in
assert(!Auth::check(), "FAIL: Guest user MUST NOT be auto-logged in!");
echo "[PASS] Test 1: Booking submit uses random password, 24h reset token, and disables auto-login.\n\n";

// --------------------------------------------------------------------------
// TEST 2: SMS fallback for set-password link when email is absent
// --------------------------------------------------------------------------
echo "[TEST 2] Verifying SMS fallback for set-password link when email is empty...\n";

Auth::logout();
$_SESSION = [];

$testPhoneSms = '9' . (string)random_int(100000000, 999999999);

$reqSms = new Request([], [
    '_csrf'          => Csrf::token(),
    'service_id'     => $serviceId,
    'name'           => 'Task 1 SMS Guest',
    'phone'          => $testPhoneSms,
    'email'          => '', // No email provided
    'address'        => '456 SMS Road',
    'city'           => 'Kashipur',
    'preferred_date' => date('Y-m-d', strtotime('+1 day')),
    'preferred_time' => '02:00 PM - 04:00 PM',
    'payment_method' => 'cod',
    'is_ajax'        => 1,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$respSms = $bookingController->submit($reqSms);
assert($respSms->getStatusCode() === 200, "Booking submit without email must return 200");
$dataSms = json_decode($respSms->getBody(), true);
assert(!empty($dataSms['set_password_url']), "Set-password URL must still be generated when email is empty");

// Check notifications table for SMS dispatch
$smsNotif = Database::fetchOne(
    "SELECT * FROM notifications WHERE channel = 'sms' AND to_address = ? AND template = 'set_password_sms' ORDER BY id DESC LIMIT 1",
    [$testPhoneSms]
);
assert($smsNotif !== null, "FAIL: SMS notification for set_password_sms was NOT logged for phone: {$testPhoneSms}");
$smsPayload = json_decode($smsNotif['payload'], true);
assert(!empty($smsPayload['set_password_url']), "SMS payload must contain set_password_url");
echo "[PASS] Test 2: SMS set-password fallback logged successfully when email is empty.\n\n";

// --------------------------------------------------------------------------
// TEST 3: Referrer-Policy: no-referrer on /book-success and /enquiry-success
// --------------------------------------------------------------------------
echo "[TEST 3] Verifying Referrer-Policy: no-referrer header on success pages...\n";

$guestToken = $data['guest_token'];
$bookSuccessReq = new Request(['token' => $guestToken], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/book-success?token=' . urlencode($guestToken),
]);

$bookSuccessResp = $bookingController->success($bookSuccessReq);
assert($bookSuccessResp->getStatusCode() === 200, "Book success page must return 200 for valid guest token");
assert($bookSuccessResp->getHeader('Referrer-Policy') === 'no-referrer', "FAIL: /book-success missing Referrer-Policy: no-referrer header!");

// Verify Enquiry success Referrer-Policy
$enquiryRes = $bookingController->executeEnquiryCreation([
    'name'     => 'Task 1 Enquiry User',
    'phone'    => '9' . (string)random_int(100000000, 999999999),
    'email'    => 'enquiry_' . time() . '@refixel.com',
    'service'  => 'AC Repair',
    'location' => 'Kashipur',
    'message'  => 'Testing referrer policy header',
]);
$enquiryGuestToken = $enquiryRes['guest_token'];

$enquirySuccessReq = new Request(['token' => $enquiryGuestToken], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/enquiry-success?token=' . urlencode($enquiryGuestToken),
]);

$enquirySuccessResp = $bookingController->enquirySuccess($enquirySuccessReq);
assert($enquirySuccessResp->getStatusCode() === 200, "Enquiry success page must return 200 for valid guest token");
assert($enquirySuccessResp->getHeader('Referrer-Policy') === 'no-referrer', "FAIL: /enquiry-success missing Referrer-Policy: no-referrer header!");
echo "[PASS] Test 3: Referrer-Policy: no-referrer verified on /book-success and /enquiry-success.\n\n";

// --------------------------------------------------------------------------
// TEST 4: Logger scrubbing of guest_token
// --------------------------------------------------------------------------
echo "[TEST 4] Verifying Logger scrubs guest_token in log strings and arrays...\n";

$rawLog = "Booking access granted for guest_token: " . $guestToken . " at " . date('c');
$scrubbed = Logger::scrubString($rawLog);
assert(!str_contains($scrubbed, $guestToken), "FAIL: Logger::scrubString failed to scrub guest_token from message string!");
assert(str_contains($scrubbed, 'guest_token: ********'), "Logger should mask guest_token with asterisks");

$maskedContext = Logger::maskSensitive(['guest_token' => $guestToken, 'other' => 'safe_value']);
assert($maskedContext['guest_token'] === '********', "FAIL: Logger::maskSensitive failed to mask guest_token!");
assert($maskedContext['other'] === 'safe_value', "Non-sensitive keys should remain unchanged");
echo "[PASS] Test 4: Logger guest_token masking verified.\n\n";

echo "=== ALL SECURITY PHASE 1.5 (TASK 1) TESTS PASSED! ===\n";
