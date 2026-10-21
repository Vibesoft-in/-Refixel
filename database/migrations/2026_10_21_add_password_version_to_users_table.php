<?php
declare(strict_types=1);

/**
 * Migration: Add password_version to users table for session invalidation
 * 
 * Usage:
 *   php database/migrations/2026_10_21_add_password_version_to_users_table.php
 *   php database/migrations/2026_10_21_add_password_version_to_users_table.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "   MIGRATION: ADD PASSWORD_VERSION TO USERS TABLE       \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

if ($isRollback) {
    echo "[ROLLBACK] Removing password_version from users table...\n";
    try {
        $pdo->exec("ALTER TABLE users DROP COLUMN password_version");
        echo "✓ Dropped column password_version\n";
    } catch (\Throwable $e) {
        echo "- Column password_version does not exist or already dropped: " . $e->getMessage() . "\n";
    }
    echo "✓ Rollback completed successfully.\n";
    exit(0);
}

echo "[UP] Adding password_version to users table...\n";

$stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password_version'");
$stmt->execute();
$exists = ((int)$stmt->fetchColumn()) > 0;

if (!$exists) {
    $pdo->exec("ALTER TABLE users ADD COLUMN password_version INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Incremented on password change to invalidate older sessions' AFTER password_hash");
    echo "✓ Added column: password_version\n";
} else {
    echo "- Column already exists: password_version\n";
}

echo "\n✓ Password version migration completed successfully.\n";
