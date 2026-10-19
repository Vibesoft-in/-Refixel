<?php
declare(strict_types=1);

/**
 * Migration: Update admin notification email settings
 * Sets active admin email and company email to contact@refixel.com
 */

require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

\App\Core\Env::load(__DIR__ . '/../.env');

try {
    $pdo = \App\Core\Database::getConnection();

    echo "1. Updating admin_notification_email in settings table..." . PHP_EOL;
    $pdo->exec("UPDATE settings SET setting_value = 'contact@refixel.com' WHERE setting_key = 'admin_notification_email'");

    echo "2. Updating company_email in settings table..." . PHP_EOL;
    $pdo->exec("UPDATE settings SET setting_value = 'contact@refixel.com' WHERE setting_key = 'company_email'");

    echo "3. Verifying updated settings..." . PHP_EOL;
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_notification_email', 'company_email')");
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    print_r($rows);

    echo "Migration completed successfully!" . PHP_EOL;
} catch (\Throwable $e) {
    echo "Migration error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
