<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

try {
    Database::execute("
        CREATE TABLE IF NOT EXISTS customer_addresses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            label VARCHAR(80) NOT NULL DEFAULT 'Home',
            house_no VARCHAR(100) NULL,
            street VARCHAR(255) NULL,
            address VARCHAR(255) NULL,
            city VARCHAR(80) NULL,
            state VARCHAR(100) NULL,
            pincode VARCHAR(10) NULL,
            address_type VARCHAR(50) DEFAULT 'Home',
            lat DECIMAL(10,8) NULL,
            lng DECIMAL(11,8) NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "customer_addresses table created or verified successfully.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
