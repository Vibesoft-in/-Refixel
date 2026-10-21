<?php
declare(strict_types=1);

/**
 * Migration: Add plain_password column to users table
 * Allows Super Admin Governance to view and manage normal admin credentials.
 */

require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

\App\Core\Env::load(__DIR__ . '/../.env');

try {
    $pdo = \App\Core\Database::getConnection();

    echo "1. Checking if 'plain_password' column exists in users table..." . PHP_EOL;
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'plain_password'");
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN plain_password VARCHAR(255) NULL DEFAULT NULL AFTER password_hash");
        echo "✓ Column 'plain_password' added to 'users' table successfully." . PHP_EOL;
    } else {
        echo "Column 'plain_password' already exists." . PHP_EOL;
    }

    echo "Migration completed successfully!" . PHP_EOL;
} catch (\Throwable $e) {
    echo "Migration error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
