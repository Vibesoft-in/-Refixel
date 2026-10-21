<?php
declare(strict_types=1);

/**
 * Migration: Create consents table for DPDP Act 2023 compliance audit logging
 * 
 * Usage:
 *   php database/migrations/2026_10_21_create_consents_table.php
 *   php database/migrations/2026_10_21_create_consents_table.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "   MIGRATION: CREATE CONSENTS TABLE                     \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

if ($isRollback) {
    echo "[ROLLBACK] Dropping consents table...\n";
    $pdo->exec("DROP TABLE IF EXISTS consents");
    echo "✓ Table consents dropped successfully.\n";
    exit(0);
}

echo "[UP] Creating consents table...\n";

$pdo->exec("
    CREATE TABLE IF NOT EXISTS consents (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL,
        identifier VARCHAR(191) NULL,
        purpose VARCHAR(64) NOT NULL DEFAULT 'service_and_privacy',
        consent_type VARCHAR(64) NULL,
        ip VARCHAR(45) NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        granted_at DATETIME NOT NULL,
        consented_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_purpose (purpose),
        INDEX idx_identifier (identifier)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "✓ Table consents created successfully.\n";
