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
