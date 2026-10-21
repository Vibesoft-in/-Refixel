<?php
declare(strict_types=1);

/**
 * Security Phase 1 Verification Test Suite
 * 
 * Verifies:
 * 1. OTP not leaked in API response, status messages, or HTML views
 * 2. IDOR protection on /book-success and /enquiry-success (strict 404, guest token check, password display removed)
 * 3. Bcrypt password_hash authentication and elimination of plain_password writes
 * 4. Logger regex scrubber on message strings and sensitive context
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Controllers\AuthController;
use App\Models\Booking;
use App\Models\User;

echo "=== RUNNING SECURITY PHASE 1 VERIFICATION TESTS ===\n\n";

$bookingController = new BookingController();
$authController = new AuthController();

// --------------------------------------------------------------------------
// TEST 1: debug_otp and OTP leakage removal
// --------------------------------------------------------------------------
echo "[TEST 1] Verifying OTP is never leaked in API response or status messages...\n";

$testEmail = 'phase1_test_' . time() . '@refixel.com';
$sendReq = new Request([], [
    '_csrf'        => Csrf::token(),
    'name'         => 'Security Audit User',
    'phone'        => '9876543210',
    'email'        => $testEmail,
    'service_name' => 'AC Servicing',
    'location'     => 'Kashipur',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/send-otp']);

$sendResp = $bookingController->apiSendEnquiryOtp($sendReq);
assert($sendResp->getStatusCode() === 200, "Send OTP response code must be 200");
$sendData = json_decode($sendResp->getBody(), true);

assert(!isset($sendData['debug_otp']), "FAIL: debug_otp MUST NOT be present in JSON response");
assert(!isset($sendData['otp']), "FAIL: otp MUST NOT be present in JSON response");

// Retrieve generated OTP from notifications table for valid test flow
$notifRow = Database::fetchOne("SELECT payload FROM notifications WHERE to_address = :email ORDER BY id DESC LIMIT 1", ['email' => $testEmail]);
assert($notifRow !== null, "Notification must be logged for test user");
$notifPayload = json_decode($notifRow['payload'], true);
$rawOtp = (string)($notifPayload['otp'] ?? '');
assert(strlen($rawOtp) === 6, "Notification payload should contain valid OTP");

// Verify status message in response does NOT contain the 6-digit OTP
assert(!str_contains($sendData['message'] ?? '', $rawOtp), "FAIL: Response message must not contain the raw OTP");

echo "[PASS] Test 1: debug_otp and raw OTP completely absent from API responses and status messages.\n\n";

// --------------------------------------------------------------------------
// TEST 2: IDOR Protection & Plain Password Removal on Success Pages
// --------------------------------------------------------------------------
echo "[TEST 2] Verifying IDOR protection on /book-success and /enquiry-success...\n";

// Create a booking owned by user #1
$ownerToken = Booking::generateGuestToken();
$bookingNo = 'BK-SEC-TEST-' . strtoupper(bin2hex(random_bytes(3)));
$testBookingId = Booking::create([
    'booking_no'     => $bookingNo,
    'guest_token'    => $ownerToken,
    'customer_id'    => 57, // customer@refixel.com
    'service_id'     => 1,
    'name'           => 'Owner Customer',
    'phone'          => '9876500001',
    'email'          => 'owner@refixel.com',
    'address'        => '123 Secure Lane',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '10:00 AM',
    'status'         => 'new',
]);

// Case A: Unauthenticated guest attempts to access by booking_no without token -> MUST RETURN 404
Auth::logout();
$unauthReq = new Request(['booking_no' => $bookingNo], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/book-success']);
$unauthResp = $bookingController->success($unauthReq);
assert($unauthResp->getStatusCode() === 404, "FAIL: Unauthenticated access without guest token must return 404, got " . $unauthResp->getStatusCode());

$unauthEnquiryReq = new Request(['booking_no' => $bookingNo], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/enquiry-success']);
$unauthEnquiryResp = $bookingController->enquirySuccess($unauthEnquiryReq);
assert($unauthEnquiryResp->getStatusCode() === 404, "FAIL: Unauthenticated enquiry access without guest token must return 404, got " . $unauthEnquiryResp->getStatusCode());

// Case B: Another logged-in user (User #30 superadmin or User #56 tech) attempts to view customer #57's booking -> MUST RETURN 404
$attackerUser = User::find(56);
Auth::login($attackerUser);
$attackerReq = new Request(['booking_no' => $bookingNo], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/book-success']);
$attackerResp = $bookingController->success($attackerReq);
assert($attackerResp->getStatusCode() === 404, "FAIL: Logged-in user accessing another user's booking must return 404, got " . $attackerResp->getStatusCode());

// Case C: Legitimate owner logged in -> MUST SUCCEED (200)
$ownerUser = User::find(57);
Auth::login($ownerUser);
$ownerReq = new Request(['booking_no' => $bookingNo], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/book-success']);
$ownerResp = $bookingController->success($ownerReq);
assert($ownerResp->getStatusCode() === 200, "FAIL: Booking owner must be able to view their booking, got " . $ownerResp->getStatusCode());

// Case D: Guest access with valid 64-char guest_token -> MUST SUCCEED (200)
Auth::logout();
$guestReq = new Request(['token' => $ownerToken], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/book-success']);
$guestResp = $bookingController->success($guestReq);
assert($guestResp->getStatusCode() === 200, "FAIL: Valid guest token must grant access to booking success screen, got " . $guestResp->getStatusCode());

// Case E: Verify plain password is NOT in HTML views
$bookSuccessHtml = file_get_contents(ROOT_PATH . '/app/views/customer/book-success.php');
assert(!str_contains($bookSuccessHtml, '$displayPassword'), "FAIL: book-success.php must not reference displayPassword");
assert(!str_contains($bookSuccessHtml, 'onclick="rfxCopy(\'<?= \App\Core\View::e($displayPassword)'), "FAIL: Password copy button must be removed from book-success.php");

$enquirySuccessHtml = file_get_contents(ROOT_PATH . '/app/views/customer/enquiry-success.php');
assert(!str_contains($enquirySuccessHtml, '$displayPassword'), "FAIL: enquiry-success.php must not reference displayPassword");
assert(!str_contains($enquirySuccessHtml, 'Copy Password'), "FAIL: Copy Password button must be removed from enquiry-success.php");

echo "[PASS] Test 2: IDOR strictly blocked with 404, guest token authorization verified, and plain password display removed.\n\n";

// --------------------------------------------------------------------------
// TEST 3: Password Authentication & Elimination of Plaintext Storage
// --------------------------------------------------------------------------
echo "[TEST 3] Verifying bcrypt password_hash authentication & no plaintext password writes...\n";

// Test signup does NOT populate plain_password
$signupEmail = 'sec_signup_' . time() . '@refixel.com';
$signupPhone = '999' . substr((string)time(), -7);
$signupPass = 'SecPass#2026!';

$signupReq = new Request([], [
    '_csrf'                 => Csrf::token(),
    'name'                  => 'Signup Security User',
    'phone'                 => $signupPhone,
    'email'                 => $signupEmail,
    'password'              => $signupPass,
    'password_confirmation' => $signupPass,
    'terms'                 => '1',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/signup']);

$signupResp = $authController->signup($signupReq);
$createdUser = Database::fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $signupEmail]);
assert($createdUser !== null, "User should be created");
assert(password_verify($signupPass, $createdUser['password_hash']), "password_hash must verify with bcrypt");
assert(empty($createdUser['plain_password']), "FAIL: plain_password MUST be NULL or empty on user creation, got: " . ($createdUser['plain_password'] ?? ''));

// Test login works with bcrypt password_hash
Auth::logout();
$loginReq = new Request([], [
    '_csrf'      => Csrf::token(),
    'identifier' => $signupEmail,
    'password'   => $signupPass,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login']);

$loginResp = $authController->login($loginReq);
assert(Auth::check() && Auth::id() === (int)$createdUser['id'], "FAIL: User must authenticate successfully via password_verify");

// Test wrong password rejected
Auth::logout();
$wrongLoginReq = new Request([], [
    '_csrf'      => Csrf::token(),
    'identifier' => $signupEmail,
    'password'   => 'WrongPassword123!',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login']);

$authController->login($wrongLoginReq);
assert(!Auth::check(), "FAIL: Incorrect password must not authenticate user");

// Clean up created user and booking
Database::query("DELETE FROM customer_profiles WHERE user_id = :uid", ['uid' => $createdUser['id']]);
Database::query("DELETE FROM users WHERE id = :uid", ['uid' => $createdUser['id']]);
Database::query("DELETE FROM bookings WHERE id = :bid", ['bid' => $testBookingId]);

echo "[PASS] Test 3: Authentication functions exclusively on bcrypt password_hash; plain_password writes eliminated.\n\n";

// --------------------------------------------------------------------------
// TEST 4: Logger Regex Scrubber and Storage Access Protection
// --------------------------------------------------------------------------
echo "[TEST 4] Verifying Logger regex scrubber and storage .htaccess protection...\n";

// 4A: Test scrubString on free-form message
$sensitiveMsg = "Error in auth: user_password='SecretP@ssword123!' and otp: 876543, signature: rzp_sig_1234567890abcdef123, card: 4111 2222 3333 4444, cvv: 789";
$scrubbed = Logger::scrubString($sensitiveMsg);

assert(!str_contains($scrubbed, 'SecretP@ssword123!'), "FAIL: Password must be scrubbed from log string");
assert(!str_contains($scrubbed, '876543'), "FAIL: OTP must be scrubbed from log string");
assert(!str_contains($scrubbed, 'rzp_sig_1234567890abcdef123'), "FAIL: Signature must be scrubbed from log string");
assert(!str_contains($scrubbed, '4111 2222 3333 4444'), "FAIL: Card number must be scrubbed from log string");
assert(!str_contains($scrubbed, '789'), "FAIL: CVV must be scrubbed from log string");

// 4B: Test maskSensitive on array context
$contextData = [
    'user_id'            => 101,
    'password'           => 'PlainSecretPassword',
    'plain_password'     => 'PlainSecretPassword',
    'otp'                => '654321',
    'razorpay_signature' => 'rzp_sig_abcdef0123456789',
    'nested' => [
        'api_key' => 'live_key_999998888877777',
        'email'   => 'admin@refixel.com',
    ],
];
$maskedContext = Logger::maskSensitive($contextData);
assert($maskedContext['password'] === '********', "password in context must be masked");
assert($maskedContext['plain_password'] === '********', "plain_password in context must be masked");
assert($maskedContext['otp'] === '********', "otp in context must be masked");
assert($maskedContext['razorpay_signature'] === '********', "signature in context must be masked");
assert($maskedContext['nested']['api_key'] === '********', "nested api_key must be masked");
assert($maskedContext['nested']['email'] === 'admin@refixel.com', "non-sensitive context must be preserved");

// 4C: Test storage directory .htaccess protection files exist
assert(file_exists(ROOT_PATH . '/storage/.htaccess'), "FAIL: storage/.htaccess must exist");
assert(file_exists(ROOT_PATH . '/storage/logs/.htaccess'), "FAIL: storage/logs/.htaccess must exist");
assert(file_exists(ROOT_PATH . '/storage/cache/.htaccess'), "FAIL: storage/cache/.htaccess must exist");

$storageHtaccess = file_get_contents(ROOT_PATH . '/storage/.htaccess');
assert(str_contains($storageHtaccess, 'Require all denied') || str_contains($storageHtaccess, 'Deny from all'), "FAIL: storage/.htaccess must deny all HTTP access");

echo "[PASS] Test 4: Logger regex scrubbing, context masking, and storage web blocking verified.\n\n";

echo "=== ALL SECURITY PHASE 1 TESTS PASSED SUCCESSFULLY! ===\n";
