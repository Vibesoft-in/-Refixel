<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Notifier;
use App\Services\EmailProviderInterface;
use App\Services\MetaWhatsAppService;
use App\Services\Msg91SmsService;
use App\Services\SmsProviderInterface;
use App\Services\SmtpEmailService;
use App\Services\WhatsAppProviderInterface;

echo "=== RUNNING NOTIFICATIONS & INTEGRATIONS FEATURE TESTS (PROMPT 11) ===\n";

// ==========================================
// TEST 1: Provider Implementations & Interfaces
// ==========================================
$emailService = new SmtpEmailService();
assert($emailService instanceof EmailProviderInterface, "SmtpEmailService must implement EmailProviderInterface");

$smsService = new Msg91SmsService();
assert($smsService instanceof SmsProviderInterface, "Msg91SmsService must implement SmsProviderInterface");

$waService = new MetaWhatsAppService();
assert($waService instanceof WhatsAppProviderInterface, "MetaWhatsAppService must implement WhatsAppProviderInterface");

echo "Test 1: Provider Implementations & Interfaces - PASSED\n";

// ==========================================
// TEST 2: Direct Notification Dispatch & DB Audit Trail
// ==========================================
$initialCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM notifications")['c'];

$testTo = 'test_notify@primodomus.com';
$testPayload = ['booking_no' => 'BK-NOTIFY-99', 'service_name' => 'Sofa Deep Cleaning'];
$sent = Notifier::send('email', $testTo, 'booking_confirmation', $testPayload);
assert($sent === true, "Notifier::send should return true for email");

$latestLog = Database::fetchOne("SELECT * FROM notifications ORDER BY id DESC LIMIT 1");
assert($latestLog !== null, "Notification must be logged in database");
assert($latestLog['channel'] === 'email', "Channel must be 'email'");
assert($latestLog['to_address'] === $testTo, "Recipient must match");
assert($latestLog['template'] === 'booking_confirmation', "Template must match");
assert($latestLog['status'] === 'sent', "Status must be 'sent'");

$decodedPayload = json_decode((string)$latestLog['payload'], true);
assert($decodedPayload['booking_no'] === 'BK-NOTIFY-99', "Payload must preserve JSON data");
echo "Test 2: Direct Notification Dispatch & DB Audit Trail - PASSED\n";

// ==========================================
// TEST 3: Lifecycle Event - New Booking (Admin Notification)
// ==========================================
$bookingData = [
    'booking_no'     => 'BK-ADMIN-TEST',
    'service_name'   => 'Kitchen Deep Cleaning',
    'name'           => 'Rohan Sharma',
    'phone'          => '9876543210',
    'city'           => 'Kashipur',
    'preferred_date' => '2026-10-02',
    'preferred_time' => '10:00 AM - 01:00 PM',
    'address'        => 'Station Road, Kashipur',
];

Notifier::notifyNewBookingAdmin($bookingData);

$adminNotice = Database::fetchOne(
    "SELECT * FROM notifications WHERE template = 'admin_new_booking' ORDER BY id DESC LIMIT 1"
);
assert($adminNotice !== null, "Admin booking notification must be logged");
assert(str_contains($adminNotice['payload'], 'BK-ADMIN-TEST'), "Admin notice must contain booking number");
echo "Test 3: Lifecycle Event - New Booking (Admin Notification) - PASSED\n";

// ==========================================
// TEST 4: Lifecycle Event - Booking Confirmation (Customer Email + SMS)
// ==========================================
$custBookingData = [
    'booking_no'     => 'BK-CUST-CONFIRM',
    'name'           => 'Priya Mehra',
    'email'          => 'priya@example.com',
    'phone'          => '9812345678',
    'service_name'   => 'Bathroom Deep Cleaning',
    'preferred_date' => '2026-10-03',
    'preferred_time' => '02:00 PM - 05:00 PM',
    'address'        => 'Apartment 201, Green Glen',
];

Notifier::notifyBookingConfirmationCustomer($custBookingData);

$custEmail = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = 'priya@example.com' AND template = 'booking_confirmation' ORDER BY id DESC LIMIT 1"
);
assert($custEmail !== null, "Customer confirmation email must be recorded");

$custSms = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = '9812345678' AND template = 'booking_confirmed_sms' ORDER BY id DESC LIMIT 1"
);
assert($custSms !== null, "Customer confirmation SMS must be recorded");
echo "Test 4: Lifecycle Event - Booking Confirmation (Customer Email + SMS) - PASSED\n";

// ==========================================
// TEST 5: Lifecycle Event - Staff Assignment (Technician Notification)
// ==========================================
$staffData = [
    'name'  => 'Sunil Kumar',
    'email' => 'sunil.tech@primodomus.com',
    'phone' => '9988776655',
];

Notifier::notifyStaffAssigned($custBookingData, $staffData);

$staffEmail = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = 'sunil.tech@primodomus.com' AND template = 'staff_assigned' ORDER BY id DESC LIMIT 1"
);
assert($staffEmail !== null, "Staff assignment email must be logged");

$staffSms = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = '9988776655' AND template = 'staff_assigned_sms' ORDER BY id DESC LIMIT 1"
);
assert($staffSms !== null, "Staff assignment SMS must be logged");
echo "Test 5: Lifecycle Event - Staff Assignment (Technician Notification) - PASSED\n";

// ==========================================
// TEST 6: Lifecycle Event - Status Transition (Customer Update)
// ==========================================
Notifier::notifyBookingStatusChange($custBookingData, 'in_progress', 'Technician has arrived at doorstep');

$statusNotice = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = 'priya@example.com' AND template = 'status_updated' ORDER BY id DESC LIMIT 1"
);
assert($statusNotice !== null, "Status update notification must be logged");
assert(str_contains($statusNotice['payload'], 'in_progress'), "Payload must include new status");
echo "Test 6: Lifecycle Event - Status Transition (Customer Update) - PASSED\n";

// ==========================================
// TEST 7: Lifecycle Event - Job Completion & Invoice Notice
// ==========================================
Notifier::notifyJobCompleted($custBookingData, $staffData);

$completedNotice = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = 'priya@example.com' AND template = 'job_completed' ORDER BY id DESC LIMIT 1"
);
assert($completedNotice !== null, "Job completed notification must be logged for customer");

$invoiceNoticeData = [
    'invoice_no' => 'INV-202609-TEST',
    'total'      => 1499.00,
];
Notifier::notifyInvoiceCreated($invoiceNoticeData, ['name' => 'Priya Mehra', 'email' => 'priya@example.com']);

$invNotice = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = 'priya@example.com' AND template = 'invoice_created' ORDER BY id DESC LIMIT 1"
);
assert($invNotice !== null, "Invoice created notification must be logged");
echo "Test 7: Lifecycle Event - Job Completion & Invoice Notice - PASSED\n";

// ==========================================
// TEST 8: Lifecycle Event - Password Reset Email
// ==========================================
$resetEmail = 'customer_reset@primodomus.com';
$resetLink = 'https://www.primodomus.com/reset-password?token=abcdef123456';
Notifier::notifyPasswordReset($resetEmail, $resetLink);

$resetNotice = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = :email AND template = 'password_reset' ORDER BY id DESC LIMIT 1",
    ['email' => $resetEmail]
);
assert($resetNotice !== null, "Password reset notification must be logged");
assert(str_contains($resetNotice['payload'], 'abcdef123456'), "Reset token must be included in payload");
echo "Test 8: Lifecycle Event - Password Reset Email - PASSED\n";

// ==========================================
// TEST 9: Cron Job - Failed Notification Retry Sweep
// ==========================================
// Inject a failed notification
Database::query(
    "INSERT INTO notifications (channel, to_address, template, payload, status, error_message, created_at)
     VALUES ('email', 'retry_test@primodomus.com', 'admin_alert', '{\"title\":\"Test Alert\"}', 'failed', 'Connection timeout', NOW())"
);
$failedId = (int)Database::lastInsertId();

// Run retry logic
$failedBefore = Database::fetchOne("SELECT status FROM notifications WHERE id = :id", ['id' => $failedId]);
assert($failedBefore['status'] === 'failed', "Status before retry must be failed");

// Execute retry sweep script directly
ob_start();
require __DIR__ . '/../../cron/retry_failed_notifications.php';
$cronOutput = ob_get_clean();

$recoveredAfter = Database::fetchOne("SELECT status, error_message FROM notifications WHERE id = :id", ['id' => $failedId]);
assert($recoveredAfter['status'] === 'sent', "Failed notification must be recovered to 'sent' status");
assert(str_contains($recoveredAfter['error_message'], 'Recovered via cron retry'), "Error message should note recovery");
echo "Test 9: Cron Job - Failed Notification Retry Sweep - PASSED\n";

// ==========================================
// TEST 10: Cron Job - Upcoming Booking Reminders & Cleanup
// ==========================================
// Execute upcoming reminders cron
ob_start();
require __DIR__ . '/../../cron/upcoming_booking_reminders.php';
$reminderOutput = ob_get_clean();
assert(str_contains($reminderOutput, '[CRON] Finished upcoming booking reminders'), "Upcoming reminders cron must finish successfully");

// Execute system maintenance cleanup cron
ob_start();
require __DIR__ . '/../../cron/cleanup_tasks.php';
$cleanupOutput = ob_get_clean();
assert(str_contains($cleanupOutput, 'System cleanup finished successfully'), "Cleanup tasks cron must complete cleanly");
echo "Test 10: Cron Job - Upcoming Booking Reminders & Cleanup - PASSED\n";

echo "\nALL 10 NOTIFICATIONS & INTEGRATIONS TESTS PASSED SUCCESSFULLY!\n";
