<?php
declare(strict_types=1);

/**
 * Migration: Drop plain_password column from users table
 * 
 * Safely removes the plaintext password storage column from the users table.
 * All authentication logic operates exclusively on bcrypt password_hash.
 * 
 * Usage:
 *   php database/migrations/2026_10_21_drop_plain_password_column.php
 *   php database/migrations/2026_10_21_drop_plain_password_column.php --rollback
 */

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Database;

$isRollback = in_array('--rollback', $argv, true) || in_array('-r', $argv, true);

echo "========================================================\n";
echo "    MIGRATION: DROP USERS.PLAIN_PASSWORD COLUMN         \n";
echo "========================================================\n\n";

$pdo = Database::getConnection();

if ($isRollback) {
    echo "Mode: ROLLBACK (Re-adding nullable plain_password column)\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'plain_password'");
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        echo "Column 'plain_password' already exists in 'users' table. Nothing to rollback.\n";
        exit(0);
    }

    $pdo->exec("ALTER TABLE users ADD COLUMN plain_password VARCHAR(255) NULL DEFAULT NULL AFTER password_hash");
    echo "✓ Column 'plain_password' re-added to 'users' table (default NULL).\n";
    exit(0);
}

// Check if column exists
$stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'plain_password'");
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exists) {
    echo "Notice: Column 'plain_password' does not exist in 'users' table. Already dropped or never created.\n";
    exit(0);
}

echo "Column 'plain_password' detected in 'users' table.\n";
echo "Dropping column 'plain_password'...\n";

$pdo->exec("ALTER TABLE users DROP COLUMN plain_password");

echo "✓ Column 'plain_password' has been successfully dropped from 'users' table!\n";
echo "Database schema secured.\n";
