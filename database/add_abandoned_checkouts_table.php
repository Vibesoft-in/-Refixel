<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

try {
    Database::execute("
        CREATE TABLE IF NOT EXISTS abandoned_checkouts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id INT UNSIGNED NULL,
            customer_id INT UNSIGNED NULL,
            service_id INT UNSIGNED NULL,
            name VARCHAR(120) NULL,
            phone VARCHAR(20) NULL,
            email VARCHAR(160) NULL,
            city VARCHAR(80) NULL,
            address VARCHAR(255) NULL,
            service_name VARCHAR(160) NULL,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            step VARCHAR(60) NOT NULL DEFAULT 'payment_dropped',
            notes TEXT NULL,
            is_recovered TINYINT(1) NOT NULL DEFAULT 0,
            recovered_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_phone (phone),
            INDEX idx_booking_id (booking_id),
            INDEX idx_recovered (is_recovered),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "abandoned_checkouts table created or verified successfully.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
