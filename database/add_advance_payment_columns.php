<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

echo "=== MIGRATING ADVANCE PAYMENT COLUMNS ===\n";

// 1. Add columns to bookings if not present
$bookingCols = Database::fetchAll("SHOW COLUMNS FROM bookings");
$existingCols = array_column($bookingCols, 'Field');

if (!in_array('total_amount', $existingCols, true)) {
    Database::execute("ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER issue_details");
    echo "✓ Added total_amount to bookings\n";
}
if (!in_array('advance_amount', $existingCols, true)) {
    Database::execute("ALTER TABLE bookings ADD COLUMN advance_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_amount");
    echo "✓ Added advance_amount to bookings\n";
}
if (!in_array('due_amount', $existingCols, true)) {
    Database::execute("ALTER TABLE bookings ADD COLUMN due_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER advance_amount");
    echo "✓ Added due_amount to bookings\n";
}
if (!in_array('payment_type', $existingCols, true)) {
    Database::execute("ALTER TABLE bookings ADD COLUMN payment_type VARCHAR(20) NOT NULL DEFAULT 'cod' AFTER due_amount");
    echo "✓ Added payment_type to bookings\n";
}

// 2. Add columns to invoices if not present
$invoiceCols = Database::fetchAll("SHOW COLUMNS FROM invoices");
$existingInvCols = array_column($invoiceCols, 'Field');

if (!in_array('advance_paid', $existingInvCols, true)) {
    Database::execute("ALTER TABLE invoices ADD COLUMN advance_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total");
    echo "✓ Added advance_paid to invoices\n";
}
if (!in_array('due_amount', $existingInvCols, true)) {
    Database::execute("ALTER TABLE invoices ADD COLUMN due_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER advance_paid");
    echo "✓ Added due_amount to invoices\n";
}

echo "=== MIGRATION COMPLETE! ===\n";
