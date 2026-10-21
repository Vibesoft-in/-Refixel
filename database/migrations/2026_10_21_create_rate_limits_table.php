<?php
declare(strict_types=1);

/**
 * Migration: Create rate_limits table for IP & Account-level Rate Limiting
 * 
 * Usage:
 *   php database/migrations/2026_10_21_create_rate_limits_table.php
 *   php database/migrations/2026_10_21_create_rate_limits_table.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "   MIGRATION: CREATE RATE_LIMITS TABLE                  \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

if ($isRollback) {
    echo "[ROLLBACK] Dropping rate_limits table if exists...\n";
    $pdo->exec("DROP TABLE IF EXISTS rate_limits");
    echo "✓ Table rate_limits dropped successfully.\n";
    exit(0);
}

echo "[UP] Creating rate_limits table...\n";

$pdo->exec("
    CREATE TABLE IF NOT EXISTS rate_limits (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        rate_key VARCHAR(191) NOT NULL UNIQUE,
        attempts INT UNSIGNED NOT NULL DEFAULT 1,
        reset_at INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_rate_key_reset (rate_key, reset_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "✓ Table rate_limits created successfully.\n";
