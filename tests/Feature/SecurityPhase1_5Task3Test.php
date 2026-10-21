<?php
declare(strict_types=1);

/**
 * Security Phase 1.5 - Task 3 Verification Test Suite
 * 
 * Verifies:
 * 1. Notifier::sanitizePayload recursively redacts passwords, OTPs, tokens, and secrets.
 * 2. URL parameters containing tokens or secrets are masked.
 * 3. Non-sensitive fields (name, email, booking_no, amount, etc.) are preserved.
 * 4. End-to-end Notifier::send persists only sanitized payloads to the notifications audit table.
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Notifier;

echo "=== RUNNING SECURITY PHASE 1.5 (TASK 3) VERIFICATION TESTS ===\n\n";

// [TEST 1] Unit Test: Notifier::sanitizePayload unit verification
echo "[TEST 1] Verifying Notifier::sanitizePayload method...\n";

$rawPayload = [
    'booking_no'         => 'BK-20261021-9999',
    'customer_name'      => 'Aman Verma',
    'customer_email'     => 'aman@example.com',
    'password'           => 'SuperSecretPass123!',
    'plain_password'     => 'ClearPass987',
    'generated_password' => 'GeneratedPass456',
    'otp'                => '782910',
    'secret'             => 'webhook_secret_key',
    'razorpay_signature' => 'a9b8c7d6e5f4...',
    'set_password_url'   => 'https://refixel.com/reset-password?token=abcdef1234567890abcdef1234567890&email=aman%40example.com',
    'nested'             => [
        'user_token'     => 'bearer_xyz_999',
        'safe_info'      => 'All systems normal',
        'sub_credentials'=> [
            'admin_pass' => 'AdminP@ssw0rd',
        ],
    ],
];

$sanitized = Notifier::sanitizePayload($rawPayload);

// Assert non-sensitive fields are preserved
assert($sanitized['booking_no'] === 'BK-20261021-9999', "booking_no must be preserved");
assert($sanitized['customer_name'] === 'Aman Verma', "customer_name must be preserved");
assert($sanitized['customer_email'] === 'aman@example.com', "customer_email must be preserved");
assert($sanitized['nested']['safe_info'] === 'All systems normal', "safe_info must be preserved");

// Assert sensitive fields are redacted
assert($sanitized['password'] === '[REDACTED]', "password must be [REDACTED]");
assert($sanitized['plain_password'] === '[REDACTED]', "plain_password must be [REDACTED]");
assert($sanitized['generated_password'] === '[REDACTED]', "generated_password must be [REDACTED]");
assert($sanitized['otp'] === '[REDACTED]', "otp must be [REDACTED]");
assert($sanitized['secret'] === '[REDACTED]', "secret must be [REDACTED]");
assert($sanitized['razorpay_signature'] === '[REDACTED]', "razorpay_signature must be [REDACTED]");
assert($sanitized['nested']['user_token'] === '[REDACTED]', "nested user_token must be [REDACTED]");
assert($sanitized['nested']['sub_credentials']['admin_pass'] === '[REDACTED]', "nested admin_pass must be [REDACTED]");

// Assert URL parameter token is redacted
assert(!str_contains($sanitized['set_password_url'], 'abcdef1234567890'), "URL token must NOT appear in sanitized payload");
assert(str_contains($sanitized['set_password_url'], 'token=[REDACTED]'), "URL token must be replaced with [REDACTED]");

echo "[PASS] Test 1: Notifier::sanitizePayload successfully redacts all sensitive fields and URL tokens.\n\n";

// [TEST 2] End-to-End Test: Notifier::send logs sanitized payload into database
echo "[TEST 2] Verifying Notifier::send writes sanitized payload to notifications DB table...\n";

$testEmail = 'test_audit_sanitize_' . time() . '@example.com';
$testData = [
    'booking_no'         => 'BK-AUDIT-TEST',
    'name'               => 'Test User',
    'password'           => 'LeakedPlainPassword999',
    'otp'                => '987654',
    'guest_token'        => 'guest_token_secret_value',
    'set_password_url'   => 'https://refixel.com/reset-password?token=secrettokenvalue123',
];

$sent = Notifier::send('email', $testEmail, 'admin_alert', $testData);
assert($sent === true, "Notifier::send should succeed");

// Fetch the logged notification
$logged = Database::fetchOne(
    "SELECT * FROM notifications WHERE to_address = :email ORDER BY id DESC LIMIT 1",
    ['email' => $testEmail]
);

assert(!empty($logged), "Notification record must be present in database");
$persistedPayload = json_decode($logged['payload'], true);

assert($persistedPayload['name'] === 'Test User', "Preserved field 'name' should match");
assert($persistedPayload['booking_no'] === 'BK-AUDIT-TEST', "Preserved field 'booking_no' should match");

// Verify sensitive values are NEVER persisted
assert($persistedPayload['password'] === '[REDACTED]', "DB payload password must be [REDACTED]");
assert($persistedPayload['otp'] === '[REDACTED]', "DB payload otp must be [REDACTED]");
assert($persistedPayload['guest_token'] === '[REDACTED]', "DB payload guest_token must be [REDACTED]");
assert(!str_contains($logged['payload'], 'LeakedPlainPassword999'), "Raw password string must not exist anywhere in DB row");
assert(!str_contains($logged['payload'], '987654'), "Raw OTP string must not exist anywhere in DB row");
assert(!str_contains($logged['payload'], 'secrettokenvalue123'), "Raw token string must not exist anywhere in DB row");

// Cleanup
Database::query("DELETE FROM notifications WHERE to_address = :email", ['email' => $testEmail]);

echo "[PASS] Test 2: notifications table audit log confirmed 100% sanitized (no raw passwords, OTPs, or tokens in DB).\n\n";

echo "=== ALL SECURITY PHASE 1.5 (TASK 3) TESTS PASSED! ===\n";
