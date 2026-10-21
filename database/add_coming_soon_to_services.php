<?php
declare(strict_types=1);

/**
 * Migration: Ensure is_coming_soon column on services table
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

echo "=== MIGRATION: ADD COMING SOON COLUMN TO SERVICES ===\n";

try {
    $cols = Database::fetchAll("SHOW COLUMNS FROM services");
    $fields = array_column($cols, 'Field');
    if (!in_array('is_coming_soon', $fields, true)) {
        Database::query("ALTER TABLE services ADD COLUMN is_coming_soon TINYINT(1) NOT NULL DEFAULT 0 AFTER is_addon");
        echo "✓ Added 'is_coming_soon' column to services table.\n";
    } else {
        echo "✓ 'is_coming_soon' column already exists on services table.\n";
    }
} catch (\Throwable $e) {
    echo "! Error ensuring is_coming_soon column: " . $e->getMessage() . "\n";
}

echo "=== MIGRATION COMPLETED ===\n";
