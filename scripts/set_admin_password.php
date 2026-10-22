<?php
declare(strict_types=1);

/**
 * CLI Tool: Set or Reset User Password
 *
 * Usage:
 *   php scripts/set_admin_password.php <new_password>
 *   php scripts/set_admin_password.php <email_or_id> <new_password>
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Access Denied: This script can only be executed from the command line.\n";
    exit(1);
}

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Models\User;

echo "========================================================\n";
echo "       REFIXEL CLI: SET ACCOUNT PASSWORD               \n";
echo "========================================================\n\n";

if ($argc < 2 || empty(trim($argv[1]))) {
    echo "Usage:\n";
    echo "  php scripts/set_admin_password.php <new_password>\n";
    echo "  php scripts/set_admin_password.php <email_or_id> <new_password>\n\n";
    echo "Example:\n";
    echo "  php scripts/set_admin_password.php MySecurePass@2026\n";
    echo "  php scripts/set_admin_password.php superadmin@refixel.com MySecurePass@2026\n\n";
    exit(1);
}

if ($argc === 2) {
    // Only password passed, target default superadmin
    $target = 'superadmin@refixel.com';
    $newPassword = trim($argv[1]);
} else {
    $target = trim($argv[1]);
    $newPassword = trim($argv[2]);
}

if (strlen($newPassword) < 6) {
    echo "ERROR: Password must be at least 6 characters long.\n";
    exit(1);
}

$user = null;
if (ctype_digit($target)) {
    $user = User::find((int)$target);
}

if (!$user) {
    $user = User::findByEmailOrPhone($target);
}

if (!$user) {
    echo "ERROR: User not found for identifier '{$target}'.\n";
    exit(1);
}

$hash = password_hash($newPassword, PASSWORD_BCRYPT);
$nextVersion = ((int)($user['password_version'] ?? 1)) + 1;

Database::query(
    "UPDATE users SET password_hash = :hash, password_version = :ver, must_change_password = 0, updated_at = NOW() WHERE id = :id",
    [
        'hash' => $hash,
        'ver'  => $nextVersion,
        'id'   => $user['id'],
    ]
);

echo "✓ SUCCESS: Password updated successfully for:\n";
echo "  - User ID: {$user['id']}\n";
echo "  - Name:    {$user['name']}\n";
echo "  - Email:   {$user['email']}\n";
echo "  - Role:    {$user['role']}\n\n";
echo "You can now log in using this email and your new password.\n";
exit(0);
