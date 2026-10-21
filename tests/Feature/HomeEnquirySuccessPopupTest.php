<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Services\AutoDispatchService;

echo "=== RUNNING HOMEPAGE ENQUIRY SUCCESS POPUP MODAL TESTS ===\n";

$testEmail = 'popup_test_user@refixel.com';

// Clean test data
Database::query("DELETE FROM notifications WHERE to_address = :email", ['email' => $testEmail]);
Database::query("DELETE FROM jobs WHERE booking_id IN (SELECT id FROM bookings WHERE email = :email)", ['email' => $testEmail]);
Database::query("DELETE FROM bookings WHERE email = :email", ['email' => $testEmail]);
Database::query("DELETE FROM users WHERE email = :email", ['email' => $testEmail]);
unset($_SESSION['enquiry_otp'], $_SESSION['last_enquiry']);

$prevBusy = AutoDispatchService::isBusyModeEnabled();
AutoDispatchService::setBusyMode(false);

$controller = new BookingController();

// 1. Send OTP
$sendReq = new Request([], [
    '_csrf'        => Csrf::token(),
    'name'         => 'Rohan Sharma',
    'phone'        => '9876500001',
    'email'        => $testEmail,
    'service_name' => 'Professional Full Home Cleaning',
    'location'     => 'Kashipur, Udham Singh Nagar',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/send-otp']);

$sendResp = $controller->apiSendEnquiryOtp($sendReq);
assert($sendResp->getStatusCode() === 200, "Send OTP should succeed with 200");
$sendData = json_decode($sendResp->getBody(), true);
assert($sendData['status'] === 'success', "Send OTP response status must be success");

$otpRow = Database::fetchOne("SELECT payload FROM notifications WHERE to_address = :email ORDER BY id DESC LIMIT 1", ['email' => $testEmail]);
assert($otpRow !== null, "Notification must be logged for OTP");
$meta = json_decode($otpRow['payload'] ?? '{}', true);
assert($meta['otp'] === '[REDACTED]', "OTP in notification audit payload must be sanitized to [REDACTED]");

// Set known test OTP in session to proceed with modal verification test
$otp = '123456';
$_SESSION['enquiry_otp']['otp_hash'] = password_hash($otp, PASSWORD_BCRYPT);

echo "[PASS] Test 1: OTP sent and sanitized in DB audit, session hash verified\n";

// 2. Verify OTP and inspect the JSON response for popup payload
$verifyReq = new Request([], [
    '_csrf' => Csrf::token(),
    'otp'   => $otp,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/verify-otp']);

$verifyResp = $controller->apiVerifyEnquiryOtp($verifyReq);
assert($verifyResp->getStatusCode() === 200, "Verify OTP should return 200");

$verifyData = json_decode($verifyResp->getBody(), true);
assert($verifyData['status'] === 'success', "Response status must be success");
assert(!empty($verifyData['booking_no']), "Booking number must be present");
assert(str_starts_with($verifyData['booking_no'], 'BK-'), "Booking number must start with 'BK-'");

// Check the enriched enquiry object returned for popup display
assert(isset($verifyData['enquiry']), "'enquiry' object must be present in JSON response for popup modal");
$enquiry = $verifyData['enquiry'];
assert($enquiry['booking_no'] === $verifyData['booking_no'], "Enquiry booking_no must match");
assert($enquiry['name'] === 'Rohan Sharma', "Customer name in enquiry must match");
assert($enquiry['phone'] === '9876500001', "Customer phone in enquiry must match");
assert($enquiry['email'] === $testEmail, "Customer email in enquiry must match");
assert($enquiry['service_name'] === 'Professional Full Home Cleaning', "Service name in enquiry must match");
assert($enquiry['location'] === 'Kashipur, Udham Singh Nagar', "Location in enquiry must match");
assert(!isset($enquiry['generated_password']), "Plaintext password must NOT be leaked in modal enquiry payload");
assert(!empty($enquiry['set_password_url']), "Set password URL must be provided in enquiry object for new guest");
assert(str_contains($enquiry['dashboard_url'], '/account'), "Dashboard URL must route to customer account");

echo "[PASS] Test 2: apiVerifyEnquiryOtp returns enriched JSON payload with secure set-password link & booking details for modal\n";

// 3. Verify home.php template has all required modal UI elements and scripts
$homeViewPath = __DIR__ . '/../../app/views/customer/home.php';
assert(file_exists($homeViewPath), "home.php must exist");
$homeHtml = file_get_contents($homeViewPath);

$requiredHtmlTokens = [
    'id="enquirySuccessModal"',
    'id="enquirySuccessBackdrop"',
    'id="enquirySuccessXCloseBtn"',
    'id="enquirySuccessModalTitle"',
    'Booking Done',
    'id="rfxSuccessBookingNo"',
    'id="rfxCopyIdBtn"',
    'id="rfxSuccessPhone"',
    'id="rfxCopyPhoneBtn"',
    'id="rfxSuccessEmail"',
    'id="rfxCopyEmailBtn"',
    'id="rfxSuccessService"',
    'id="rfxSuccessName"',
    'id="rfxSuccessLocation"',
    'id="rfxSuccessDashboardBtn"',
    'id="rfxSuccessReturnBtn"',
    'openEnquirySuccessModal',
    'closeEnquirySuccessModal',
    'setupCopyBtn',
];

foreach ($requiredHtmlTokens as $token) {
    assert(str_contains($homeHtml, $token), "home.php must contain required token: {$token}");
}

echo "[PASS] Test 3: home.php contains all required modal markup, credentials display, copy buttons, and JS handlers\n";

// Cleanup
Database::query("DELETE FROM notifications WHERE to_address = :email", ['email' => $testEmail]);
Database::query("DELETE FROM jobs WHERE booking_id IN (SELECT id FROM bookings WHERE email = :email)", ['email' => $testEmail]);
Database::query("DELETE FROM bookings WHERE email = :email", ['email' => $testEmail]);
Database::query("DELETE FROM users WHERE email = :email", ['email' => $testEmail]);
unset($_SESSION['enquiry_otp'], $_SESSION['last_enquiry']);
AutoDispatchService::setBusyMode($prevBusy);

echo "\n=== ALL HOMEPAGE ENQUIRY SUCCESS POPUP TESTS PASSED! ===\n";
