<?php
declare(strict_types=1);

/**
 * Migration: Ensure all user passwords are encrypted using bcrypt password_hash
 * 
 * Usage:
 *   php database/migrations/2026_10_21_ensure_password_hash.php
 *   php database/migrations/2026_10_21_ensure_password_hash.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "   REFIXEL PASSWORD HASH MIGRATION & AUDIT UTILITY      \n";
echo "========================================================\n";

if ($isRollback) {
    echo "Mode: ROLLBACK (Restoring previous password hashes)\n";
    $backupTableExists = Database::fetchOne("SHOW TABLES LIKE 'users_password_migration_backup'");
    if (!$backupTableExists) {
        echo "ERROR: Backup table 'users_password_migration_backup' does not exist. Nothing to rollback.\n";
        exit(1);
    }

    $backups = Database::fetchAll("SELECT * FROM users_password_migration_backup");
    if (empty($backups)) {
        echo "No backup records found to restore.\n";
        exit(0);
    }

    $restoredCount = 0;
    foreach ($backups as $b) {
        Database::execute(
            "UPDATE users SET password_hash = :hash WHERE id = :id",
            ['hash' => $b['old_password_hash'], 'id' => $b['user_id']]
        );
        $restoredCount++;
        echo "Restored user #{$b['user_id']} to previous password hash.\n";
    }

    echo "\nRollback complete. {$restoredCount} user(s) restored.\n";
    exit(0);
}

// 1. Create backup table if not exists
Database::query("
    CREATE TABLE IF NOT EXISTS users_password_migration_backup (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        old_password_hash VARCHAR(255) NULL,
        migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// 2. Fetch all users and check their hashes
$users = Database::fetchAll("SELECT id, name, email, phone, role, password_hash, plain_password FROM users");
echo "Found " . count($users) . " total user account(s) in database.\n\n";

$migratedCount = 0;
$alreadyValidCount = 0;

foreach ($users as $u) {
    $userId = (int)$u['id'];
    $currentHash = (string)($u['password_hash'] ?? '');
    $plain = (string)($u['plain_password'] ?? '');
    $info = password_get_info($currentHash);

    $needsMigration = false;
    $reason = '';

    if (empty($currentHash)) {
        $needsMigration = true;
        $reason = 'empty hash';
    } elseif ($info['algo'] === 0) {
        // Not a standard recognized hash (e.g. plain text or md5 in password_hash column)
        $needsMigration = true;
        $reason = 'unrecognized / plain algorithm (' . substr($currentHash, 0, 10) . '...)';
    }

    if ($needsMigration) {
        if (!empty($plain)) {
            $newHash = password_hash($plain, PASSWORD_BCRYPT);
        } else {
            // Generate fallback secure random password
            $randomPass = bin2hex(random_bytes(8));
            $newHash = password_hash($randomPass, PASSWORD_BCRYPT);
            echo "[NOTICE] User #{$userId} had no plain_password; generated secure random password.\n";
        }

        // Backup old value
        Database::execute(
            "INSERT INTO users_password_migration_backup (user_id, old_password_hash) VALUES (:uid, :old)",
            ['uid' => $userId, 'old' => $currentHash]
        );

        // Update user
        Database::execute(
            "UPDATE users SET password_hash = :hash WHERE id = :id",
            ['hash' => $newHash, 'id' => $userId]
        );

        echo "[MIGRATED] User #{$userId} ({$u['email']}) - Reason: {$reason} -> bcrypt hash generated.\n";
        $migratedCount++;
    } else {
        $alreadyValidCount++;
        echo "[VALID] User #{$userId} ({$u['email']}) - Algorithm: {$info['algoName']} (Verified Bcrypt/Argon).\n";
    }
}

echo "\n--------------------------------------------------------\n";
echo "Migration finished: {$alreadyValidCount} already valid, {$migratedCount} migrated.\n";
echo "Backup saved in 'users_password_migration_backup' table.\n";
echo "To rollback anytime: php database/migrations/2026_10_21_ensure_password_hash.php --rollback\n";
echo "========================================================\n";
