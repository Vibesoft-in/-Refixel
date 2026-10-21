<?php
declare(strict_types=1);

/**
 * Migration: Add 2FA (TOTP) columns to users table
 * 
 * Usage:
 *   php database/migrations/2026_10_21_add_2fa_to_users_table.php
 *   php database/migrations/2026_10_21_add_2fa_to_users_table.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "   MIGRATION: ADD 2FA (TOTP) COLUMNS TO USERS TABLE     \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

if ($isRollback) {
    echo "[ROLLBACK] Removing 2FA columns from users table...\n";
    $columns = ['totp_secret', 'totp_enabled', 'totp_recovery_codes', 'totp_last_step', 'totp_confirmed_at'];
    foreach ($columns as $column) {
        try {
            $pdo->exec("ALTER TABLE users DROP COLUMN {$column}");
            echo "✓ Dropped column {$column}\n";
        } catch (\Throwable $e) {
            echo "- Column {$column} does not exist or already dropped\n";
        }
    }
    echo "✓ Rollback completed successfully.\n";
    exit(0);
}

echo "[UP] Adding 2FA columns to users table...\n";

// Helper to check if column exists
$hasColumn = function (string $col) use ($pdo): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = :col");
    $stmt->execute(['col' => $col]);
    return ((int)$stmt->fetchColumn()) > 0;
};

$columnsToAdd = [
    'totp_secret' => "ALTER TABLE users ADD COLUMN totp_secret TEXT NULL COMMENT 'AES-256-GCM encrypted TOTP secret' AFTER password_hash",
    'totp_enabled' => "ALTER TABLE users ADD COLUMN totp_enabled TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if 2FA is active' AFTER totp_secret",
    'totp_recovery_codes' => "ALTER TABLE users ADD COLUMN totp_recovery_codes JSON NULL COMMENT 'Hashed recovery codes array' AFTER totp_enabled",
    'totp_last_step' => "ALTER TABLE users ADD COLUMN totp_last_step INT UNSIGNED NULL COMMENT 'Last used TOTP time-step to prevent replay attacks' AFTER totp_recovery_codes",
    'totp_confirmed_at' => "ALTER TABLE users ADD COLUMN totp_confirmed_at DATETIME NULL COMMENT 'Timestamp when 2FA was verified' AFTER totp_last_step",
];

foreach ($columnsToAdd as $col => $sql) {
    if (!$hasColumn($col)) {
        $pdo->exec($sql);
        echo "✓ Added column: {$col}\n";
    } else {
        echo "- Column already exists: {$col}\n";
    }
}

echo "\n✓ 2FA columns migration completed successfully.\n";
