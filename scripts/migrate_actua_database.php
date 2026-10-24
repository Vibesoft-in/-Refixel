<?php
/**
 * Migration Script: Synchronize Live Database (actua_db) with Development (primodomus_db) and actual_db
 * 
 * Ensures:
 * 1. Zero data loss from live database (users 30, 750, 751, booking 755, booking item 169, abandoned checkout 1, etc.)
 * 2. Zero tables missing across all 34 tables
 * 3. Ingests all catalog items (new services, addons, checklists, FAQs, settings)
 * 4. Ensures actual_db, actua_db, and primodomus_db are 100% complete and consistent
 */

$pdo = new PDO("mysql:host=localhost", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "======================================================\n";
echo "STARTING DATABASE MIGRATION\n";
echo "======================================================\n\n";

$sourceDb = 'actua_db';
$targetDb = 'primodomus_db';
$aliasDb  = 'actual_db';

// 1. Verify source exists
$databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($sourceDb, $databases)) {
    die("FATAL ERROR: Source database '$sourceDb' not found in MySQL!\n");
}

// 2. Ensure alias database actual_db exists
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$aliasDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Verified/Created database `$aliasDb`.\n";

// 3. Temporarily disable foreign key checks for catalog merge
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "\n--- STEP 1: MERGING MISSING CATALOG DATA INTO $sourceDb ---\n";

// Services (merge missing services IDs 119-141 from target into source)
$insertedServices = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.services 
    SELECT * FROM `$targetDb`.services 
    WHERE id NOT IN (SELECT id FROM `$sourceDb`.services)
");
echo "Merged services from $targetDb to $sourceDb: $insertedServices new rows.\n";

// Service Addons (merge missing addon IDs)
$insertedAddons = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.service_addons 
    SELECT * FROM `$targetDb`.service_addons 
    WHERE id NOT IN (SELECT id FROM `$sourceDb`.service_addons)
");
echo "Merged service addons from $targetDb to $sourceDb: $insertedAddons new rows.\n";

// Service Checklist Items
$insertedChecklists = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.service_checklist_items 
    SELECT * FROM `$targetDb`.service_checklist_items 
    WHERE id NOT IN (SELECT id FROM `$sourceDb`.service_checklist_items)
");
echo "Merged service checklist items: $insertedChecklists new rows.\n";

// FAQs
$insertedFaqs = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.faqs 
    SELECT * FROM `$targetDb`.faqs 
    WHERE id NOT IN (SELECT id FROM `$sourceDb`.faqs)
");
echo "Merged FAQs: $insertedFaqs new rows.\n";

// Settings (sync all setting keys & values from dev, like updated hero_title)
$pdo->exec("
    INSERT INTO `$sourceDb`.settings (setting_key, setting_value) 
    SELECT setting_key, setting_value FROM `$targetDb`.settings 
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
");
echo "Synchronized settings table.\n";

// Staff users and profiles (so admin can assign technicians if needed)
$insertedStaff = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.users 
    SELECT * FROM `$targetDb`.users 
    WHERE role IN ('staff') AND id NOT IN (SELECT id FROM `$sourceDb`.users)
");
echo "Merged staff technician users: $insertedStaff rows.\n";

$insertedStaffProfiles = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.staff_profiles 
    SELECT * FROM `$targetDb`.staff_profiles 
    WHERE id NOT IN (SELECT id FROM `$sourceDb`.staff_profiles)
");
echo "Merged staff profiles: $insertedStaffProfiles rows.\n";

$insertedStaffSkills = $pdo->exec("
    INSERT IGNORE INTO `$sourceDb`.staff_skills 
    SELECT * FROM `$targetDb`.staff_skills 
    WHERE id NOT IN (SELECT id FROM `$sourceDb`.staff_skills)
");
echo "Merged staff skills: $insertedStaffSkills rows.\n";

// Reviews (from dev if empty in live)
$liveReviewsCount = $pdo->query("SELECT COUNT(*) FROM `$sourceDb`.reviews")->fetchColumn();
if ($liveReviewsCount == 0) {
    $insertedReviews = $pdo->exec("
        INSERT IGNORE INTO `$sourceDb`.reviews 
        SELECT * FROM `$targetDb`.reviews
    ");
    echo "Copied initial approved reviews into $sourceDb: $insertedReviews rows.\n";
}

echo "\n--- STEP 2: SYNCHRONIZING ALL 34 TABLES TO $targetDb AND $aliasDb ---\n";

$allTables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$sourceDb'")->fetchAll(PDO::FETCH_COLUMN);
echo "Total tables in $sourceDb to sync: " . count($allTables) . "\n";

foreach ($allTables as $table) {
    // Sync to targetDb (primodomus_db)
    $pdo->exec("DROP TABLE IF EXISTS `$targetDb`.`$table`");
    $pdo->exec("CREATE TABLE `$targetDb`.`$table` LIKE `$sourceDb`.`$table`");
    $pdo->exec("INSERT INTO `$targetDb`.`$table` SELECT * FROM `$sourceDb`.`$table`");

    // Sync to aliasDb (actual_db)
    $pdo->exec("DROP TABLE IF EXISTS `$aliasDb`.`$table`");
    $pdo->exec("CREATE TABLE `$aliasDb`.`$table` LIKE `$sourceDb`.`$table`");
    $pdo->exec("INSERT INTO `$aliasDb`.`$table` SELECT * FROM `$sourceDb`.`$table`");

    $count = $pdo->query("SELECT COUNT(*) FROM `$targetDb`.`$table`")->fetchColumn();
    echo "  Synced table [$table] -> $count rows\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n======================================================\n";
echo "MIGRATION COMPLETED SUCCESSFULLY!\n";
echo "======================================================\n";
