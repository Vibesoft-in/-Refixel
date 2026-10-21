<?php
declare(strict_types=1);

/**
 * Migration: Enhance payments and bookings tables with indexed gateway fields
 * 
 * Adds:
 * - payments.gateway_order_id VARCHAR(100) NULL (INDEX)
 * - payments.gateway_payment_id VARCHAR(100) NULL (UNIQUE)
 * - bookings.gateway_order_id VARCHAR(100) NULL (UNIQUE)
 * 
 * Shifts away from overwriting transaction_ref with payment IDs and
 * stores only gateway identifiers, amount, and status.
 * 
 * Usage:
 *   php database/migrations/2026_10_21_enhance_payments_table.php
 *   php database/migrations/2026_10_21_enhance_payments_table.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "   MIGRATION: ENHANCE PAYMENTS & BOOKINGS SCHEMA       \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function indexExists(PDO $pdo, string $table, string $indexName): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $stmt->execute([$table, $indexName]);
    return (int)$stmt->fetchColumn() > 0;
}

if ($isRollback) {
    echo "Mode: ROLLBACK (Removing gateway columns and indexes)\n\n";

    // 1. Rollback payments table changes
    if (indexExists($pdo, 'payments', 'uq_payments_gateway_payment_id')) {
        $pdo->exec("ALTER TABLE `payments` DROP INDEX `uq_payments_gateway_payment_id`");
        echo "✓ Dropped index uq_payments_gateway_payment_id from payments\n";
    }
    if (indexExists($pdo, 'payments', 'idx_payments_gateway_order_id')) {
        $pdo->exec("ALTER TABLE `payments` DROP INDEX `idx_payments_gateway_order_id`");
        echo "✓ Dropped index idx_payments_gateway_order_id from payments\n";
    }
    if (columnExists($pdo, 'payments', 'gateway_payment_id')) {
        $pdo->exec("ALTER TABLE `payments` DROP COLUMN `gateway_payment_id`");
        echo "✓ Dropped column gateway_payment_id from payments\n";
    }
    if (columnExists($pdo, 'payments', 'gateway_order_id')) {
        $pdo->exec("ALTER TABLE `payments` DROP COLUMN `gateway_order_id`");
        echo "✓ Dropped column gateway_order_id from payments\n";
    }

    // 2. Rollback bookings table changes
    if (indexExists($pdo, 'bookings', 'uq_bookings_gateway_order_id')) {
        $pdo->exec("ALTER TABLE `bookings` DROP INDEX `uq_bookings_gateway_order_id`");
        echo "✓ Dropped index uq_bookings_gateway_order_id from bookings\n";
    }
    if (columnExists($pdo, 'bookings', 'gateway_order_id')) {
        $pdo->exec("ALTER TABLE `bookings` DROP COLUMN `gateway_order_id`");
        echo "✓ Dropped column gateway_order_id from bookings\n";
    }

    echo "\nRollback completed successfully.\n";
    exit(0);
}

// UP Migration
echo "Mode: UP (Adding gateway columns & indexes)\n\n";

// 1. Enhance payments table
if (!columnExists($pdo, 'payments', 'gateway_order_id')) {
    $pdo->exec("ALTER TABLE `payments` ADD COLUMN `gateway_order_id` VARCHAR(100) NULL DEFAULT NULL AFTER `transaction_ref`");
    echo "✓ Added column gateway_order_id to payments\n";
} else {
    echo "- Column gateway_order_id already exists in payments\n";
}

if (!columnExists($pdo, 'payments', 'gateway_payment_id')) {
    $pdo->exec("ALTER TABLE `payments` ADD COLUMN `gateway_payment_id` VARCHAR(100) NULL DEFAULT NULL AFTER `gateway_order_id`");
    echo "✓ Added column gateway_payment_id to payments\n";
} else {
    echo "- Column gateway_payment_id already exists in payments\n";
}

if (!indexExists($pdo, 'payments', 'idx_payments_gateway_order_id')) {
    $pdo->exec("ALTER TABLE `payments` ADD INDEX `idx_payments_gateway_order_id` (`gateway_order_id`)");
    echo "✓ Added index idx_payments_gateway_order_id on payments(gateway_order_id)\n";
} else {
    echo "- Index idx_payments_gateway_order_id already exists on payments\n";
}

if (!indexExists($pdo, 'payments', 'uq_payments_gateway_payment_id')) {
    $pdo->exec("ALTER TABLE `payments` ADD UNIQUE KEY `uq_payments_gateway_payment_id` (`gateway_payment_id`)");
    echo "✓ Added UNIQUE index uq_payments_gateway_payment_id on payments(gateway_payment_id)\n";
} else {
    echo "- UNIQUE index uq_payments_gateway_payment_id already exists on payments\n";
}

// 2. Enhance bookings table
if (!columnExists($pdo, 'bookings', 'gateway_order_id')) {
    $pdo->exec("ALTER TABLE `bookings` ADD COLUMN `gateway_order_id` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_type`");
    echo "✓ Added column gateway_order_id to bookings\n";
} else {
    echo "- Column gateway_order_id already exists in bookings\n";
}

if (!indexExists($pdo, 'bookings', 'uq_bookings_gateway_order_id')) {
    $pdo->exec("ALTER TABLE `bookings` ADD UNIQUE KEY `uq_bookings_gateway_order_id` (`gateway_order_id`)");
    echo "✓ Added UNIQUE index uq_bookings_gateway_order_id on bookings(gateway_order_id)\n";
} else {
    echo "- UNIQUE index uq_bookings_gateway_order_id already exists on bookings\n";
}

echo "\n✓ Migration applied successfully. Schema updated and secured.\n";
