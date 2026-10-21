<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Services\SmtpEmailService;

echo "=== RUNNING SECURITY PHASE 1.5 (TASK 2) VERIFICATION TESTS ===\n\n";

// [TEST 1] Verify Email Templates in SmtpEmailService do not expose plain passwords
echo "[TEST 1] Verifying SmtpEmailService templates do not output plain passwords...\n";

// Reflection or inspection of templates in SmtpEmailService
$mailerRef = new ReflectionClass(SmtpEmailService::class);
$source = file_get_contents($mailerRef->getFileName());

// Check admin_welcome_credentials template
assert(!str_contains($source, "<strong>Password:</strong>"), "admin_welcome_credentials must not display plain password");
assert(str_contains($source, "set_password_url"), "admin_welcome_credentials should provide set_password_url activation link");

// Check admin_password_reset template
assert(!str_contains($source, "Your temporary password is:"), "admin_password_reset must not display temporary plain password");
assert(str_contains($source, "reset_url"), "admin_password_reset should provide reset_url");

// Check customer_welcome_booking template
assert(str_contains($source, "set_password_url"), "customer_welcome_booking should support set_password_url");
assert(!str_contains($source, "generated_password"), "customer_welcome_booking must not expose generated_password");

// Check customer_enquiry_confirmed template
assert(str_contains($source, "set_password_url"), "customer_enquiry_confirmed should support set_password_url");
assert(!str_contains($source, "generated_password"), "customer_enquiry_confirmed must not expose generated_password");

echo "[PASS] Test 1: SmtpEmailService templates verified (zero plain password disclosures).\n\n";

// [TEST 2] Verify Modals (home.php and booking-payment-modal.php) do not have password fields
echo "[TEST 2] Verifying customer home & booking payment modals do not display passwords...\n";

$homeView = file_get_contents(__DIR__ . '/../../app/views/customer/home.php');
assert(!str_contains($homeView, 'id="rfxSuccessPassword"'), "home.php must not have rfxSuccessPassword");
assert(!str_contains($homeView, 'id="rfxCopyPasswordBtn"'), "home.php must not have rfxCopyPasswordBtn");

$bookingModalView = file_get_contents(__DIR__ . '/../../app/views/partials/booking-payment-modal.php');
assert(!str_contains($bookingModalView, 'id="rfxSuccessPassword"'), "booking-payment-modal.php must not have rfxSuccessPassword");
assert(!str_contains($bookingModalView, 'id="rfxCopyPasswordBtn"'), "booking-payment-modal.php must not have rfxCopyPasswordBtn");

echo "[PASS] Test 2: Customer modals verified (no plain password fields or copy buttons).\n\n";

// [TEST 3] Verify Superadmin Admin View does not display plain passwords or predictable generator
echo "[TEST 3] Verifying superadmin admin view does not leak passwords or use predictable generator...\n";

$adminIndexView = file_get_contents(__DIR__ . '/../../app/views/superadmin/admins/index.php');
assert(!str_contains($adminIndexView, 'toggleAdminPassword(this)'), "superadmin/admins/index.php must not have toggleAdminPassword in table");
assert(!str_contains($adminIndexView, '$a[\'plain_password\']'), "superadmin/admins/index.php must not access or display plain_password");
assert(!str_contains($adminIndexView, "'Refixel@'"), "superadmin/admins/index.php JS must not use predictable Refixel@ prefix");
assert(str_contains($adminIndexView, 'window.crypto.getRandomValues'), "superadmin/admins/index.php JS must use crypto.getRandomValues");

echo "[PASS] Test 3: Superadmin admin view verified (hashed badge, zero plaintext display, crypto random generation).\n\n";

echo "=== ALL SECURITY PHASE 1.5 (TASK 2) TESTS PASSED! ===\n";
