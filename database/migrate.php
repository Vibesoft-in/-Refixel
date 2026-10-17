<?php
declare(strict_types=1);

/**
 * REFIXEL Database Migration & Seeder Script
 */

require_once __DIR__ . '/../app/core/Env.php';
\App\Core\Env::load(__DIR__ . '/../.env');

$host = \App\Core\Env::get('DB_HOST', '127.0.0.1');
$port = \App\Core\Env::get('DB_PORT', 3306);
$db   = \App\Core\Env::get('DB_DATABASE', 'REFIXEL_db');
$user = \App\Core\Env::get('DB_USERNAME', 'root');
$pass = \App\Core\Env::get('DB_PASSWORD', '');
$charset = \App\Core\Env::get('DB_CHARSET', 'utf8mb4');

echo "Connecting to MySQL server at {$host}:{$port}...\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", (string)$user, (string)$pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "Creating database `{$db}` if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
    $pdo->exec("USE `{$db}`");

    echo "Importing database/schema.sql...\n";
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schemaSql);
    echo "Schema created successfully.\n";

    echo "Importing database/seed.sql...\n";
    $seedSql = file_get_contents(__DIR__ . '/seed.sql');
    $pdo->exec($seedSql);
    echo "Seed data inserted successfully.\n";

    echo "\nVerifying created tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($tables) . " tables in `{$db}`:\n";
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "  - {$t} ({$count} rows)\n";
    }

    echo "\nMigration completed successfully!\n";
} catch (\Throwable $e) {
    echo "MIGRATION FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
