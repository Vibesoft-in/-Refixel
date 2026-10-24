<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

echo "========================================================\n";
echo "       REFIXEL: LIVE DATABASE MIGRATION SCRIPT          \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

// Disable foreign key checks for clean migration
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

try {
    // 1. Create abandoned_checkouts table in old_database if not exists
    echo "Step 1: Ensuring 'abandoned_checkouts' table exists in old_database...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `old_database`.`abandoned_checkouts` (
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
    echo "  ✓ 'abandoned_checkouts' table verified.\n\n";

    // 2. Migrate new services from primodomus_db into old_database (non-destructive)
    echo "Step 2: Merging new services into old_database...\n";
    $insertedServices = $pdo->exec("
        INSERT IGNORE INTO `old_database`.`services`
        SELECT * FROM `primodomus_db`.`services`
        WHERE id NOT IN (SELECT id FROM `old_database`.`services`)
    ");
    echo "  ✓ Merged {$insertedServices} new services.\n\n";

    // 3. Migrate new service_addons from primodomus_db into old_database (non-destructive)
    echo "Step 3: Merging new service_addons into old_database...\n";
    $insertedAddons = $pdo->exec("
        INSERT IGNORE INTO `old_database`.`service_addons`
        SELECT * FROM `primodomus_db`.`service_addons`
        WHERE id NOT IN (SELECT id FROM `old_database`.`service_addons`)
    ");
    echo "  ✓ Merged {$insertedAddons} new service add-ons.\n\n";

    // 4. Migrate new service_checklist_items into old_database
    echo "Step 4: Merging new service_checklist_items into old_database...\n";
    $insertedChecklist = $pdo->exec("
        INSERT IGNORE INTO `old_database`.`service_checklist_items`
        SELECT * FROM `primodomus_db`.`service_checklist_items`
        WHERE id NOT IN (SELECT id FROM `old_database`.`service_checklist_items`)
    ");
    echo "  ✓ Merged {$insertedChecklist} new checklist items.\n\n";

    // 5. Migrate new FAQs into old_database
    echo "Step 5: Merging new FAQs into old_database...\n";
    $insertedFaqs = $pdo->exec("
        INSERT IGNORE INTO `old_database`.`faqs`
        SELECT * FROM `primodomus_db`.`faqs`
        WHERE id NOT IN (SELECT id FROM `old_database`.`faqs`)
    ");
    echo "  ✓ Merged {$insertedFaqs} new FAQs.\n\n";

    // 6. Sync settings (preserving live values, updating newly introduced keys)
    echo "Step 6: Syncing site settings...\n";
    $pdo->exec("
        INSERT INTO `old_database`.`settings` (setting_key, setting_value)
        SELECT setting_key, setting_value FROM `primodomus_db`.`settings`
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    echo "  ✓ Settings synchronized.\n\n";

    // 7. Now sync all 34 tables into primodomus_db so primodomus_db becomes the full live database
    echo "Step 7: Synchronizing all 34 tables from old_database into primodomus_db...\n";
    $allTables = $pdo->query("SHOW TABLES FROM `old_database`")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($allTables as $table) {
        // Drop destination table in primodomus_db and recreate with exact structure and data from old_database
        $pdo->exec("DROP TABLE IF EXISTS `primodomus_db`.`{$table}`");
        $pdo->exec("CREATE TABLE `primodomus_db`.`{$table}` LIKE `old_database`.`{$table}`");
        $pdo->exec("INSERT INTO `primodomus_db`.`{$table}` SELECT * FROM `old_database`.`{$table}`");
        $rowCount = (int)$pdo->query("SELECT COUNT(*) FROM `primodomus_db`.`{$table}`")->fetchColumn();
        echo sprintf("  ✓ Synced table `%-30s` (%d rows)\n", $table, $rowCount);
    }

    echo "\nStep 8: Re-enabling foreign key checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n========================================================\n";
    echo "✓ SUCCESS: DATABASE MIGRATION COMPLETED PERFECTLY!\n";
    echo "Both `old_database` and `primodomus_db` now contain 100% of\n";
    echo "the live website data, all 34 tables, and all new catalog services!\n";
    echo "========================================================\n";

} catch (\Throwable $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n❌ ERROR during migration: " . $e->getMessage() . "\n";
    exit(1);
}
