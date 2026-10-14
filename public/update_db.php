<?php
define('ROOT_PATH', dirname(__DIR__));
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) { return; }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) { require_once $file; }
});
\App\Core\Env::load(ROOT_PATH . '/.env');

header('Content-Type: text/plain; charset=utf-8');
echo "=== REFIXEL Database Sync ===\n\n";

// 1. Customer profiles columns
try {
    \App\Core\Database::query("ALTER TABLE customer_profiles ADD COLUMN house_no VARCHAR(100) DEFAULT NULL AFTER user_id, ADD COLUMN street VARCHAR(255) DEFAULT NULL AFTER house_no, ADD COLUMN state VARCHAR(100) DEFAULT NULL AFTER city, ADD COLUMN address_type VARCHAR(50) DEFAULT 'Home' AFTER state;");
    echo "[OK] customer_profiles columns added\n";
} catch (Exception $e) {
    echo "[SKIP] customer_profiles: " . $e->getMessage() . "\n";
}

// 1a. Commission Rules service_id column
try {
    \App\Core\Database::query("ALTER TABLE commission_rules ADD COLUMN service_id INT(10) UNSIGNED NULL AFTER category_id, ADD INDEX idx_service_id (service_id);");
    echo "[OK] commission_rules service_id added\n";
} catch (Exception $e) {
    echo "[SKIP] commission_rules service_id: " . $e->getMessage() . "\n";
}

// 1b. Multiple Customer Addresses Table
try {
    \App\Core\Database::query("
        CREATE TABLE IF NOT EXISTS customer_addresses (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            label VARCHAR(80) NOT NULL DEFAULT 'Home',
            house_no VARCHAR(100) NULL,
            street VARCHAR(255) NULL,
            address VARCHAR(255) NULL,
            city VARCHAR(80) NULL,
            state VARCHAR(100) NULL,
            pincode VARCHAR(10) NULL,
            address_type VARCHAR(50) DEFAULT 'Home',
            lat DECIMAL(10,8) NULL,
            lng DECIMAL(11,8) NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[OK] customer_addresses table verified/created\n";
} catch (Exception $e) {
    echo "[SKIP] customer_addresses: " . $e->getMessage() . "\n";
}

// 2. Sync all service images to exact dedicated image assets
$imageUpdates = [
    'balcony-deep-pressure-wash'       => 'refixel-balcony-pressure-wash.png',
    'full-home-cleaning'               => 'refixel-cleaning.jpg',
    'bathroom-deep-cleaning'           => 'refixel-bathroom-cleaning.jpg',
    'kitchen-deep-cleaning'            => 'refixel-kitchen-cleaning.jpg',
    'sofa-cleaning'                    => 'refixel-sofa-cleaning.jpg',
    'office-cleaning'                  => 'refixel-office-cleaning.jpg',
    'interior-painting'                => 'refixel-painting.jpg',
    'cockroach-pest-control'           => 'service-pest-control.jpg',
    'tap-leak-repair'                  => 'refixel-plumber.jpg',
    'furniture-assembly'               => 'refixel-carpenter.jpg',
    'ac-jet-service'                   => 'refixel-ac-service.jpg',
    'fan-switchboard-repair'           => 'refixel-electrician.jpg',
    'appliance-diagnostic-repair'      => 'refixel-appliance-repair.jpg',
    'fall-ceiling-installation'        => 'refixel-fall-ceiling.jpg',
    'fall-ceiling-repair-modification' => 'refixel-fall-ceiling.jpg',
];

foreach ($imageUpdates as $slug => $img) {
    try {
        \App\Core\Database::query("UPDATE services SET image = :img WHERE slug = :slug", [
            'img'  => $img,
            'slug' => $slug
        ]);
        echo "[OK] Updated service '{$slug}' image => {$img}\n";
    } catch (Exception $e) {
        echo "[ERR] Service '{$slug}': " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
