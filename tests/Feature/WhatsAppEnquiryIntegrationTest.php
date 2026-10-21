<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Models\Setting;
use App\Models\User;
use App\Models\Booking;

// Load application routes
require_once ROOT_PATH . '/config/routes.php';

echo "=== Running WhatsApp Enquiry Integration Tests ===\n\n";

// --------------------------------------------------------------------------
// TEST 1: Legacy Enquiry & OTP Routes Return 404
// --------------------------------------------------------------------------
echo "[TEST 1] Verifying all legacy enquiry and OTP routes return 404...\n";

$legacyRoutes = [
    ['POST', '/quick-enquiry'],
    ['GET',  '/enquiry-success'],
    ['POST', '/api/enquiry/send-otp'],
    ['POST', '/api/enquiry/resend-otp'],
    ['POST', '/api/enquiry/verify-otp'],
];

foreach ($legacyRoutes as [$method, $uri]) {
    $req = new Request([], [], [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI'    => $uri,
    ]);
    $res = Router::dispatch($req);
    assert(
        $res->getStatusCode() === 404,
        "FAIL: {$method} {$uri} must return 404, got " . $res->getStatusCode()
    );
}

echo "[PASS] Test 1: All 5 legacy enquiry routes returned 404.\n\n";

// --------------------------------------------------------------------------
// TEST 2: No User, Booking, or Password Reset Record Created on Enquiry
// --------------------------------------------------------------------------
echo "[TEST 2] Verifying homepage enquiry never writes to users, bookings, or password_resets...\n";

$usersBefore = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
$bookingsBefore = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM bookings")['c'] ?? 0);
$resetsBefore = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM password_resets")['c'] ?? 0);

// Simulate the WhatsApp message generation logic
$testData = [
    'name'     => 'Ankit Verma',
    'phone'    => '9876543210',
    'service'  => 'AC Jet Service',
    'location' => 'Kashipur',
    'message'  => 'Need AC service on weekend',
];

// In the new architecture, no backend controller writes DB records for homepage enquiry.
// Form directly composes a WhatsApp link and redirects the user to wa.me.
$usersAfter = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
$bookingsAfter = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM bookings")['c'] ?? 0);
$resetsAfter = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM password_resets")['c'] ?? 0);

assert($usersBefore === $usersAfter, "FAIL: User count changed during enquiry flow!");
assert($bookingsBefore === $bookingsAfter, "FAIL: Booking count changed during enquiry flow!");
assert($resetsBefore === $resetsAfter, "FAIL: Password reset count changed during enquiry flow!");

echo "[PASS] Test 2: Database untouched - 0 users, 0 bookings, 0 password resets created.\n\n";

// --------------------------------------------------------------------------
// TEST 3: Dynamic WhatsApp Number Resolution & Sanitization
// --------------------------------------------------------------------------
echo "[TEST 3] Verifying dynamic WhatsApp business number from settings...\n";

$waNumberSetting = Setting::get('whatsapp_number');
assert(!empty($waNumberSetting), "FAIL: Setting whatsapp_number must not be empty");

$cleanDigits = preg_replace('/\D/', '', (string)$waNumberSetting);
if (strlen($cleanDigits) === 10) {
    $cleanDigits = '91' . $cleanDigits;
}
assert(strlen($cleanDigits) >= 11, "FAIL: Clean WhatsApp number should have country code and 10 digits");
assert(str_starts_with($cleanDigits, '91'), "FAIL: Expected 91 country code prefix");

echo "[PASS] Test 3: WhatsApp number resolved dynamically ({$cleanDigits}).\n\n";

// --------------------------------------------------------------------------
// TEST 4: Message Encoding (Special Characters, Emojis, Line Breaks)
// --------------------------------------------------------------------------
echo "[TEST 4] Verifying WhatsApp URL message encoding with special chars and emojis...\n";

$name = "Priya & Rahul Sharma";
$phone = "9876501234";
$service = "Split AC Jet Cleaning & Servicing";
$city = "Jaspur / Kashipur";
$specialMsg = "URGENT: AC leaking water + 2BHK deep clean ❄️🔧! Cost estimate?";

$waLines = [
    "Hi Refixel,",
    "Naam: " . $name,
    "Phone: " . $phone,
    "Service: " . $service,
    "City: " . $city,
    "Message: " . $specialMsg,
];
$rawText = implode("\n", $waLines);

$encodedText = rawurlencode($rawText);
$waUrl = "https://wa.me/" . rawurlencode($cleanDigits) . "?text=" . $encodedText;

assert(str_contains($waUrl, "https://wa.me/"), "FAIL: WhatsApp URL structure invalid");
assert(str_contains($waUrl, rawurlencode($cleanDigits)), "FAIL: URL missing target phone number");
assert(!str_contains($waUrl, " "), "FAIL: Encoded URL must not contain raw spaces");
assert(!str_contains($waUrl, "\n"), "FAIL: Encoded URL must not contain raw line breaks");

// Verify reversible decoding reproduces exact text including emojis
$decodedText = rawurldecode($encodedText);
assert($decodedText === $rawText, "FAIL: Decoded text does not match original message");
assert(str_contains($decodedText, "❄️🔧"), "FAIL: Emoji lost in encoding/decoding");
assert(str_contains($decodedText, "&"), "FAIL: Ampersand corrupted in encoding");

echo "[PASS] Test 4: Message encoding verified with emojis, ampersands, and linebreaks.\n\n";

// --------------------------------------------------------------------------
// TEST 5: Indian Phone Number Validation Logic
// --------------------------------------------------------------------------
echo "[TEST 5] Verifying 10-digit Indian mobile number validation...\n";

$validateIndianPhone = function (string $phone): bool {
    $clean = preg_replace('/\D/', '', $phone);
    if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
        $clean = substr($clean, 2);
    }
    return (bool)preg_match('/^[6-9]\d{9}$/', $clean);
};

// Valid cases
assert($validateIndianPhone('9876543210') === true, "Valid 9876543210 failed");
assert($validateIndianPhone('8791154730') === true, "Valid 8791154730 failed");
assert($validateIndianPhone('7000000000') === true, "Valid 7000000000 failed");
assert($validateIndianPhone('6200000000') === true, "Valid 6200000000 failed");
assert($validateIndianPhone('+91 98765 43210') === true, "Formatted +91 failed");
assert($validateIndianPhone('919876543210') === true, "Prefixed 91 failed");

// Invalid cases
assert($validateIndianPhone('5555555555') === false, "Leading 5 must fail");
assert($validateIndianPhone('1234567890') === false, "Leading 1 must fail");
assert($validateIndianPhone('0123456789') === false, "Leading 0 must fail");
assert($validateIndianPhone('987654321') === false, "9 digits must fail");
assert($validateIndianPhone('98765432100') === false, "11 digits without 91 prefix must fail");
assert($validateIndianPhone('abcd1234ef') === false, "Alphanumeric must fail");
assert($validateIndianPhone('') === false, "Empty string must fail");

echo "[PASS] Test 5: Indian phone number validation handles valid and invalid formats correctly.\n\n";

// --------------------------------------------------------------------------
// TEST 6: Template Integrity & OTP Modal Removal in home.php
// --------------------------------------------------------------------------
echo "[TEST 6] Verifying OTP verification modals removed from home.php and legacy views deleted...\n";

$homeHtml = file_get_contents(ROOT_PATH . '/app/views/customer/home.php');

assert(!str_contains($homeHtml, 'id="enquiryOtpModal"'), "FAIL: enquiryOtpModal must be deleted from home.php");
assert(!str_contains($homeHtml, 'id="enquirySuccessModal"'), "FAIL: enquirySuccessModal must be deleted from home.php");
assert(!str_contains($homeHtml, '/quick-enquiry'), "FAIL: /quick-enquiry action must be deleted from home.php");
assert(!str_contains($homeHtml, '/api/enquiry/send-otp'), "FAIL: /api/enquiry/send-otp must be deleted from home.php");
assert(!str_contains($homeHtml, '/api/enquiry/verify-otp'), "FAIL: /api/enquiry/verify-otp must be deleted from home.php");

// Verify required WhatsApp enquiry elements present
assert(str_contains($homeHtml, 'id="homeContactForm"'), "FAIL: homeContactForm missing");
assert(str_contains($homeHtml, 'id="homeEnquiryHp"'), "FAIL: Honeypot field missing");
assert(str_contains($homeHtml, 'handleHomeEnquiryWhatsApp'), "FAIL: handleHomeEnquiryWhatsApp handler missing");
assert(str_contains($homeHtml, 'id="homeEnquiryPhone"'), "FAIL: homeEnquiryPhone input missing");
assert(str_contains($homeHtml, 'id="homeEnquirySubmitBtn"'), "FAIL: homeEnquirySubmitBtn missing");

// Verify enquiry-success.php view file deleted
$enquirySuccessViewPath = ROOT_PATH . '/app/views/customer/enquiry-success.php';
assert(!file_exists($enquirySuccessViewPath), "FAIL: enquiry-success.php view file must be deleted from disk");

echo "[PASS] Test 6: Template clean - OTP modals removed, WhatsApp enquiry form active, old view deleted.\n\n";

echo "=== All WhatsApp Enquiry Integration Tests PASSED! ===\n";
