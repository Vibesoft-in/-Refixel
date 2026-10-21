<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Env.php';
require_once __DIR__ . '/../app/Core/Database.php';

\App\Core\Env::load(__DIR__ . '/../.env');

try {
    $pdo = \App\Core\Database::getConnection();

    echo "1. Updating users.role ENUM to include 'super_admin'..." . PHP_EOL;
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('customer','admin','staff','super_admin') NOT NULL");
    echo "ENUM updated successfully." . PHP_EOL;

    echo "2. Checking for Super Admin account..." . PHP_EOL;
    $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE role = 'super_admin' LIMIT 1");
    $stmt->execute();
    $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$superAdmin) {
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = 'superadmin@refixel.com' OR phone = '9876543200' LIMIT 1");
        $checkEmail->execute();
        $existing = $checkEmail->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $update = $pdo->prepare("UPDATE users SET role = 'super_admin' WHERE id = :id");
            $update->execute(['id' => $existing['id']]);
            echo "Updated existing user ID {$existing['id']} to super_admin." . PHP_EOL;
        } else {
            $randomPass = bin2hex(random_bytes(32));
            $insert = $pdo->prepare("INSERT INTO users (role, name, email, phone, password_hash, status, must_change_password) 
                                      VALUES ('super_admin', 'Master Super Admin', 'superadmin@refixel.com', '9876543200', :hash, 'active', 1)");
            $insert->execute(['hash' => password_hash($randomPass, PASSWORD_BCRYPT)]);
            unset($randomPass);
            echo "Created new Super Admin (email: superadmin@refixel.com, phone: 9876543200, secure random hash set, password reset required)." . PHP_EOL;
        }
    } else {
        echo "Super Admin already exists with ID: " . $superAdmin['id'] . PHP_EOL;
    }

    echo "Migration completed successfully!" . PHP_EOL;
} catch (\Throwable $e) {
    echo "Migration error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
