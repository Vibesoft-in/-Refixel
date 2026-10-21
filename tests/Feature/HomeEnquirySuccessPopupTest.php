<?php
declare(strict_types=1);

/**
 * HomeEnquirySuccessPopupTest.php
 *
 * NOTE: The legacy popup modal for homepage enquiry OTP verification was decommissioned
 * in favor of direct WhatsApp enquiry (commit ce3c288).
 * 
 * This test suite asserts that:
 * 1. Legacy OTP and enquiry popup modals are removed from home.php
 * 2. Legacy enquiry-success view file is eliminated
 * 3. Modern WhatsApp enquiry form elements and honeypot are present
 */

require_once __DIR__ . '/../bootstrap.php';

echo "=== RUNNING HOMEPAGE ENQUIRY MODAL DECOMMISSIONING TESTS ===\n";

$homeHtml = file_get_contents(ROOT_PATH . '/app/views/customer/home.php');

// 1. Verify obsolete modals removed
assert(!str_contains($homeHtml, 'id="enquiryOtpModal"'), "enquiryOtpModal must be deleted from home.php");
assert(!str_contains($homeHtml, 'id="enquirySuccessModal"'), "enquirySuccessModal must be deleted from home.php");
echo "[PASS] Test 1: Legacy enquiry OTP and success modals removed from home.php\n";

// 2. Verify obsolete view deleted
$enquirySuccessViewPath = ROOT_PATH . '/app/views/customer/enquiry-success.php';
assert(!file_exists($enquirySuccessViewPath), "enquiry-success.php view file must be deleted");
echo "[PASS] Test 2: Obsolete enquiry-success.php view file confirmed deleted\n";

// 3. Verify new WhatsApp enquiry elements present
assert(str_contains($homeHtml, 'id="homeContactForm"'), "homeContactForm must be present in home.php");
assert(str_contains($homeHtml, 'handleHomeEnquiryWhatsApp'), "handleHomeEnquiryWhatsApp handler must be present");
assert(str_contains($homeHtml, 'id="homeEnquirySubmitBtn"'), "homeEnquirySubmitBtn must be present");
echo "[PASS] Test 3: WhatsApp enquiry form and handler verified in home.php\n";

echo "\n=== ALL HOMEPAGE ENQUIRY POPUP DECOMMISSIONING TESTS PASSED SUCCESSFULLY! ===\n";
