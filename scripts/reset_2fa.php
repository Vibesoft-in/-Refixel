<?php
declare(strict_types=1);

/**
 * CLI Tool: Emergency 2FA Reset
 * 
 * Usage:
 *   php scripts/reset_2fa.php <user_id_or_email_or_phone>
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Access Denied: This script can only be executed from the server command line.\n";
    exit(1);
}

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;
use App\Models\User;

echo "========================================================\n";
echo "   REFIXEL CLI: EMERGENCY TWO-FACTOR (2FA) RESET        \n";
echo "========================================================\n\n";

if ($argc < 2 || empty(trim($argv[1]))) {
    echo "Usage:\n";
    echo "  php scripts/reset_2fa.php <user_id | email | phone>\n\n";
    echo "Examples:\n";
    echo "  php scripts/reset_2fa.php 1\n";
    echo "  php scripts/reset_2fa.php superadmin@refixel.com\n\n";

    echo "Currently enrolled 2FA administrative accounts:\n";
    $enrolled = Database::fetchAll("SELECT id, name, email, phone, role, totp_enabled, totp_confirmed_at FROM users WHERE totp_enabled = 1");
    if (empty($enrolled)) {
        echo "  (No users currently have 2FA enabled)\n";
    } else {
        foreach ($enrolled as $u) {
            echo "  - [ID: {$u['id']}] {$u['name']} ({$u['email']}) | Role: {$u['role']} | Confirmed: {$u['totp_confirmed_at']}\n";
        }
    }
    echo "\n";
    exit(1);
}

$target = trim($argv[1]);
$user = null;

if (ctype_digit($target)) {
    $user = User::find((int)$target);
}

if (!$user) {
    $user = User::findByEmailOrPhone($target);
}

if (!$user) {
    echo "ERROR: User not found for identifier '{$target}'.\n";
    exit(1);
}

echo "Found User:\n";
echo "  - ID:     {$user['id']}\n";
echo "  - Name:   {$user['name']}\n";
echo "  - Email:  " . ($user['email'] ?? 'N/A') . "\n";
echo "  - Phone:  " . ($user['phone'] ?? 'N/A') . "\n";
echo "  - Role:   {$user['role']}\n";
echo "  - Status: {$user['status']}\n";
echo "  - 2FA:    " . (!empty($user['totp_enabled']) ? 'ENABLED' : 'DISABLED') . "\n\n";

if (empty($user['totp_enabled'])) {
    echo "Notice: 2FA is already DISABLED for this account.\n";
    exit(0);
}

// Perform 2FA Reset
User::update((int)$user['id'], [
    'totp_secret'         => null,
    'totp_enabled'        => 0,
    'totp_recovery_codes' => null,
    'totp_last_step'      => null,
    'totp_confirmed_at'   => null,
]);

Logger::warning("2FA reset via CLI script for user ID: {$user['id']} ({$user['name']}, email: {$user['email']})");

echo "✓ SUCCESS: Two-Factor Authentication (2FA) has been RESET for {$user['name']} (ID: {$user['id']}).\n";
echo "  - Stored TOTP secret deleted.\n";
echo "  - Recovery codes purged.\n";
echo "  - Replay counter cleared.\n";
echo "  The user will be prompted to set up 2FA anew upon their next login.\n\n";
exit(0);
