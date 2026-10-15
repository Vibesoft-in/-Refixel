<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Models\Booking;
use App\Controllers\BookingController;

echo "=== RUNNING HOMEPAGE ENQUIRY EMAIL OTP VERIFICATION TESTS ===\n";

// Cleanup old test data
Database::query("DELETE FROM notifications WHERE to_address = 'enquiry_otp_test@refixel.com'");
Database::query("DELETE FROM jobs WHERE booking_id IN (SELECT id FROM bookings WHERE email = 'enquiry_otp_test@refixel.com')");
Database::query("DELETE FROM bookings WHERE email = 'enquiry_otp_test@refixel.com'");
Database::query("DELETE FROM users WHERE email = 'enquiry_otp_test@refixel.com'");
unset($_SESSION['enquiry_otp'], $_SESSION['last_enquiry']);

$controller = new BookingController();

// 1. Test Validation in Send OTP
$invalidReq = new Request([], [
    '_csrf' => Csrf::token(),
    'name'  => '',
    'phone' => '123',
    'email' => 'bad-email',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/send-otp']);

$invalidResp = $controller->apiSendEnquiryOtp($invalidReq);
assert($invalidResp->getStatusCode() === 422, "Expected 422 for invalid form inputs");
echo "[PASS] Test 1: API correctly validates required name, phone, and valid email\n";

// 2. Test Successful OTP Generation & Email Dispatch
$sendReq = new Request([], [
    '_csrf'     => Csrf::token(),
    'name'      => 'Aakash Verma',
    'phone'     => '9876543219',
    'email'     => 'enquiry_otp_test@refixel.com',
    'service'   => 'AC Jet Service',
    'location'  => 'Mallital, Nainital',
    'message'   => 'Need AC high pressure cleaning service urgently',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/send-otp']);

$sendResp = $controller->apiSendEnquiryOtp($sendReq);
assert($sendResp->getStatusCode() === 200, "Expected 200 for successful OTP generation");

$sendData = json_decode($sendResp->getBody(), true);
assert($sendData['status'] === 'success', "Response status must be success");
assert(isset($_SESSION['enquiry_otp']), "Session enquiry_otp must be populated");
assert(!empty($_SESSION['enquiry_otp']['otp_hash']), "Session must hold hashed OTP");

// Verify OTP notification in database
$otpNotif = Database::fetchOne("SELECT * FROM notifications 
                                WHERE to_address = 'enquiry_otp_test@refixel.com' 
                                AND template = 'enquiry_email_otp' 
                                ORDER BY id DESC LIMIT 1");
assert($otpNotif !== null, "OTP notification must be recorded in notifications table");
$notifPayload = json_decode($otpNotif['payload'], true);
$actualOtp = $notifPayload['otp'] ?? '';
assert(strlen((string)$actualOtp) === 6, "Generated OTP in email payload must be exactly 6 digits");
echo "[PASS] Test 2: OTP successfully generated ({$actualOtp}), hashed in session, and emailed to customer\n";

// 3. Test Resend Cooldown
$resendReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/resend-otp']);
$resendBlockedResp = $controller->apiResendEnquiryOtp($resendReq);
assert($resendBlockedResp->getStatusCode() === 429, "Expected 429 cooldown protection on immediate resend");

// Fast forward cooldown time
$_SESSION['enquiry_otp']['resend_at'] = time() - 5;
$resendAllowedResp = $controller->apiResendEnquiryOtp($resendReq);
assert($resendAllowedResp->getStatusCode() === 200, "Expected 200 after cooldown expires");
echo "[PASS] Test 3: OTP resend cooldown (rate-limiting) strictly enforced\n";

// Fetch updated OTP
$updatedOtpNotif = Database::fetchOne("SELECT * FROM notifications 
                                       WHERE to_address = 'enquiry_otp_test@refixel.com' 
                                       AND template = 'enquiry_email_otp' 
                                       ORDER BY id DESC LIMIT 1");
$latestOtp = json_decode($updatedOtpNotif['payload'], true)['otp'] ?? '';

// 4. Test Rejecting Invalid OTP
$wrongVerifyReq = new Request([], [
    '_csrf' => Csrf::token(),
    'otp'   => '000000',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/verify-otp']);

$wrongResp = $controller->apiVerifyEnquiryOtp($wrongVerifyReq);
assert($wrongResp->getStatusCode() === 400, "Expected 400 for incorrect OTP");
$wrongData = json_decode($wrongResp->getBody(), true);
assert(str_contains(strtolower($wrongData['message']), 'invalid otp'), "Error message must state invalid OTP");
echo "[PASS] Test 4: Incorrect OTP correctly rejected with attempt tracking\n";

// 5. Test Successful OTP Verification and Enquiry Submission
$correctVerifyReq = new Request([], [
    '_csrf' => Csrf::token(),
    'otp'   => (string)$latestOtp,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/enquiry/verify-otp']);

$correctResp = $controller->apiVerifyEnquiryOtp($correctVerifyReq);
assert($correctResp->getStatusCode() === 200, "Expected 200 for correct OTP");
$correctData = json_decode($correctResp->getBody(), true);
assert($correctData['status'] === 'success', "Response status must be success");
assert(!empty($correctData['booking_no']), "Booking number must be returned");
assert(!isset($_SESSION['enquiry_otp']), "OTP session must be cleared after verification");

// Verify booking in database
$createdBooking = Database::fetchOne("SELECT * FROM bookings WHERE booking_no = :bno", ['bno' => $correctData['booking_no']]);
assert($createdBooking !== null, "Booking record must exist in database");
assert($createdBooking['name'] === 'Aakash Verma', "Customer name must match");
assert($createdBooking['phone'] === '9876543219', "Customer phone must match");
assert($createdBooking['email'] === 'enquiry_otp_test@refixel.com', "Customer email must match");
assert($createdBooking['status'] === 'new', "Booking status must be 'new'");

// Verify Customer Confirmation Email dispatched
$confirmNotif = Database::fetchOne("SELECT * FROM notifications 
                                    WHERE to_address = 'enquiry_otp_test@refixel.com' 
                                    AND template IN ('booking_confirmation', 'customer_welcome_booking') 
                                    ORDER BY id DESC LIMIT 1");
assert($confirmNotif !== null, "Customer must receive booking confirmation email after successful OTP verification");
echo "[PASS] Test 5: Verified OTP successfully creates booking (#{$correctData['booking_no']}), clears OTP session, and dispatches confirmation\n";

// Cleanup
Database::query("DELETE FROM notifications WHERE to_address = 'enquiry_otp_test@refixel.com'");
Database::query("DELETE FROM jobs WHERE booking_id IN (SELECT id FROM bookings WHERE email = 'enquiry_otp_test@refixel.com')");
Database::query("DELETE FROM bookings WHERE email = 'enquiry_otp_test@refixel.com'");
Database::query("DELETE FROM users WHERE email = 'enquiry_otp_test@refixel.com'");
unset($_SESSION['enquiry_otp'], $_SESSION['last_enquiry']);

echo "\n=== ALL 5 HOMEPAGE ENQUIRY OTP TESTS PASSED SUCCESSFULLY! ===\n";
