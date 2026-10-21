<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

echo "=== Adding guest_token column to bookings table ===\n";

try {
    $cols = Database::fetchAll("SHOW COLUMNS FROM bookings");
    $colNames = array_column($cols, 'Field');

    if (!in_array('guest_token', $colNames, true)) {
        Database::query("ALTER TABLE bookings ADD COLUMN guest_token VARCHAR(64) NULL AFTER booking_no, ADD INDEX idx_guest_token (guest_token)");
        echo "[OK] 'guest_token' column added successfully to 'bookings' table.\n";
    } else {
        echo "[INFO] 'guest_token' column already exists in 'bookings' table.\n";
    }

    // Populate existing bookings with a secure guest token if NULL
    $untokened = Database::fetchAll("SELECT id FROM bookings WHERE guest_token IS NULL OR guest_token = ''");
    if (!empty($untokened)) {
        $count = 0;
        foreach ($untokened as $row) {
            $token = bin2hex(random_bytes(32));
            Database::execute("UPDATE bookings SET guest_token = ? WHERE id = ?", [$token, (int)$row['id']]);
            $count++;
        }
        echo "[OK] Populated {$count} existing bookings with secure 64-char guest tokens.\n";
    }
} catch (\Throwable $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== Migration Complete ===\n";
