<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

\App\Core\Env::load(__DIR__ . '/../.env');

try {
    $pdo = \App\Core\Database::getConnection();

    echo "1. Modifying users.status column to allow 'pending'..." . PHP_EOL;
    $pdo->exec("ALTER TABLE users MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'active'");
    echo "✓ users.status updated to VARCHAR(30)." . PHP_EOL;

    echo "2. Checking staff_profiles columns..." . PHP_EOL;
    $cols = $pdo->query("SHOW COLUMNS FROM staff_profiles")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('approved_at', $cols, true)) {
        $pdo->exec("ALTER TABLE staff_profiles ADD COLUMN approved_at DATETIME NULL AFTER availability_note");
        echo "✓ Added approved_at to staff_profiles." . PHP_EOL;
    }
    if (!in_array('approved_by', $cols, true)) {
        $pdo->exec("ALTER TABLE staff_profiles ADD COLUMN approved_by INT UNSIGNED NULL AFTER approved_at");
        echo "✓ Added approved_by to staff_profiles." . PHP_EOL;
    }

    echo "Migration completed successfully!" . PHP_EOL;
} catch (\Throwable $e) {
    echo "Migration error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
