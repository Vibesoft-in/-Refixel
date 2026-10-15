<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Env.php';

use App\Core\Database;
use App\Core\Env;

Env::load(__DIR__ . '/../.env');

echo "Checking and adding settlement columns to staff_earnings...\n";

try {
    $cols = Database::fetchAll("SHOW COLUMNS FROM staff_earnings");
    $colNames = array_column($cols, 'Field');

    if (!in_array('settled_method', $colNames, true)) {
        Database::query("ALTER TABLE staff_earnings ADD COLUMN settled_method VARCHAR(50) NULL AFTER is_settled");
        echo "✓ Added 'settled_method'\n";
    }
    if (!in_array('settled_ref', $colNames, true)) {
        Database::query("ALTER TABLE staff_earnings ADD COLUMN settled_ref VARCHAR(100) NULL AFTER settled_method");
        echo "✓ Added 'settled_ref'\n";
    }
    if (!in_array('settled_notes', $colNames, true)) {
        Database::query("ALTER TABLE staff_earnings ADD COLUMN settled_notes VARCHAR(255) NULL AFTER settled_ref");
        echo "✓ Added 'settled_notes'\n";
    }
    echo "✓ staff_earnings schema is up to date.\n";
} catch (\Throwable $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}
