<?php
declare(strict_types=1);

/**
 * EnquiryEmailOtpVerificationTest.php
 *
 * NOTE: Homepage Enquiry Email OTP Verification was decommissioned and replaced by
 * direct WhatsApp Enquiry flow per explicit user instruction (commit ce3c288).
 * 
 * This test suite verifies:
 * 1. Legacy OTP methods are removed from BookingController
 * 2. Legacy OTP endpoints return 404 via Router
 * 3. Client form in home.php handles enquiry directly via WhatsApp without backend OTP
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Controllers\BookingController;

echo "=== RUNNING HOMEPAGE ENQUIRY RETIREMENT & WHATSAPP MIGRATION TESTS ===\n";

$controller = new BookingController();

// 1. Verify legacy methods are removed from controller
assert(!method_exists($controller, 'apiSendEnquiryOtp'), "apiSendEnquiryOtp must be removed");
assert(!method_exists($controller, 'apiVerifyEnquiryOtp'), "apiVerifyEnquiryOtp must be removed");
assert(!method_exists($controller, 'apiResendEnquiryOtp'), "apiResendEnquiryOtp must be removed");
echo "[PASS] Test 1: Legacy OTP methods removed from BookingController\n";

// 2. Verify legacy routes return 404 via Router
$legacyEndpoints = [
    ['POST', '/quick-enquiry'],
    ['GET',  '/enquiry-success'],
    ['POST', '/api/enquiry/send-otp'],
    ['POST', '/api/enquiry/verify-otp'],
    ['POST', '/api/enquiry/resend-otp'],
];

foreach ($legacyEndpoints as [$method, $endpoint]) {
    $req = new Request([], [], [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI'    => $endpoint,
    ]);
    $res = Router::dispatch($req);
    assert($res->getStatusCode() === 404, "Legacy route {$method} {$endpoint} must return 404");
}
echo "[PASS] Test 2: Legacy OTP and enquiry routes return 404 via Router\n";

// 3. Verify home.php handles enquiry via WhatsApp without backend OTP dependency
$homeHtml = file_get_contents(ROOT_PATH . '/app/views/customer/home.php');
assert(str_contains($homeHtml, 'handleHomeEnquiryWhatsApp'), "home.php must utilize direct WhatsApp enquiry handler");
assert(!str_contains($homeHtml, '/api/enquiry/send-otp'), "home.php must not reference legacy /api/enquiry/send-otp");
echo "[PASS] Test 3: home.php enquiry form transitioned completely to WhatsApp flow\n";

echo "\n=== ALL HOMEPAGE ENQUIRY DECOMMISSIONING TESTS PASSED SUCCESSFULLY! ===\n";
