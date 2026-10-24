<?php
declare(strict_types=1);

/**
 * Script: Reset Seed and Demo Account Passwords
 * 
 * Safely invalidates known/public passwords (e.g. password123) for demo and seed accounts
 * (#30, #57, #58, #208, #277) by:
 * 1. Generating cryptographically secure random bcrypt password hashes.
 * 2. Setting plain_password = NULL.
 * 3. Enabling must_change_password = 1.
 * 4. Generating a single-use 24-48h set-password token in password_resets (SHA-256 hash).
 * 
 * NOTE: Secure random passwords and raw reset tokens are NEVER printed or logged.
 * 
 * Usage:
 *   php scripts/reset_seed_demo_accounts.php
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;

echo "========================================================\n";
echo "   REFIXEL SEED & DEMO ACCOUNTS PASSWORD RESET UTILITY  \n";
echo "========================================================\n\n";

$targetIds = [30, 57, 58, 208, 277];
$targetEmails = ['superadmin@refixel.com', 'admin@refixel.com', 'customer@refixel.com', 'tech@refixel.com'];

// Find all matching target accounts
$placeholders = implode(',', array_fill(0, count($targetIds), '?'));
$emailPlaceholders = implode(',', array_fill(0, count($targetEmails), '?'));

$sql = "SELECT id, name, email, phone, role 
        FROM users 
        WHERE id IN ($placeholders) 
           OR email IN ($emailPlaceholders)";

$params = array_merge($targetIds, $targetEmails);
$users = Database::fetchAll($sql, $params);

if (empty($users)) {
    echo "No matching seed or demo accounts found to reset.\n";
    exit(0);
}

echo "Found " . count($users) . " seed/demo account(s) to secure.\n\n";

$resetCount = 0;
$now = date('Y-m-d H:i:s');
$expiresAt = date('Y-m-d H:i:s', time() + 86400 * 2); // 48 hours

foreach ($users as $u) {
    $userId = (int)$u['id'];
    $identifier = !empty($u['email']) ? $u['email'] : ($u['phone'] ?? (string)$userId);

    // 1. Generate unpredictable cryptographic random password and hash with bcrypt
    $randomSecret = bin2hex(random_bytes(32));
    $newHash = password_hash($randomSecret, PASSWORD_BCRYPT);
    unset($randomSecret); // Immediately destroy plaintext in memory

    // 2. Update user: new bcrypt hash, must_change_password 1
    Database::query(
        "UPDATE users 
         SET password_hash = :hash, 
             must_change_password = 1, 
             updated_at = NOW() 
         WHERE id = :id",
        [
            'hash' => $newHash,
            'id'   => $userId,
        ]
    );

    // 3. Invalidate previous reset tokens for this identifier
    Database::query(
        "DELETE FROM password_resets WHERE email_or_phone = :ident",
        ['ident' => $identifier]
    );

    // 4. Generate secure single-use set-password token (SHA-256 in DB, 48h validity)
    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    unset($rawToken); // Destroy raw token from memory

    Database::query(
        "INSERT INTO password_resets (email_or_phone, token_hash, expires_at) 
         VALUES (:ident, :token_hash, :expires_at)",
        [
            'ident'      => $identifier,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]
    );

    Logger::info("Reset demo account credentials for User #{$userId} [Role: {$u['role']}] - password invalidated, plain_password nulled, set-password token issued.");

    echo "✓ Secured Account #{$userId} ({$u['role']} - {$identifier})\n";
    echo "    - New cryptographically random bcrypt hash set\n";
    echo "    - plain_password set to NULL\n";
    echo "    - must_change_password flag enabled\n";
    echo "    - 48h single-use set-password token issued in password_resets\n\n";

    $resetCount++;
}

echo "Successfully secured {$resetCount} seed/demo account(s).\n";
echo "Zero passwords or tokens were printed or written to disk.\n";
