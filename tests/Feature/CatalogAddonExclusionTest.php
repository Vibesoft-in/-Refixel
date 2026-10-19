<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Controllers\Admin\ServiceController;
use App\Core\Request;

echo "=== Running CatalogAddonExclusionTest ===\n";

Service::ensureServiceSchema();

// Test 1: Category 1 (Cleaning) public services only contain main services
$cleaningServices = Service::getActiveByCategory(1);
$cleaningSlugs = array_column($cleaningServices, 'slug');
$addonSlugs = [
    'kitchen-chimney-degreasing',
    'microwave-oven-deep-clean',
    'refrigerator-interior-sanitization',
    'bathroom-tile-stain-removal',
    'tap-shower-chrome-descaling',
    'bathroom-exhaust-fan-detailing',
    'mattress-steam-cleaning',
    'ceiling-fan-chandelier-detailing',
];

foreach ($addonSlugs as $slug) {
    if (in_array($slug, $cleaningSlugs, true)) {
        echo "FAILED: Addon slug '{$slug}' found in public cleaning services list!\n";
        exit(1);
    }
}
echo "Test 1: Cleaning public catalog excludes all add-on services - PASSED\n";

// Test 2: Service::getActive() excludes all add-on services
$allActive = Service::getActive();
$allActiveIds = array_column($allActive, 'id');
$addonsInDb = Database::fetchAll("SELECT id, name FROM services WHERE is_addon = 1");

foreach ($addonsInDb as $addon) {
    if (in_array((int)$addon['id'], $allActiveIds, true)) {
        echo "FAILED: Service ID {$addon['id']} ({$addon['name']}) with is_addon=1 was returned by Service::getActive()!\n";
        exit(1);
    }
}
echo "Test 2: Service::getActive() strictly excludes all is_addon=1 services - PASSED\n";

// Test 3: Add-on items are still fully available in ServiceAddon::forService
$kitchenAddons = ServiceAddon::forService(3);
$kitchenAddonIds = array_column($kitchenAddons, 'id');
if (empty($kitchenAddons) || !in_array(52, $kitchenAddonIds, true)) {
    echo "FAILED: Chimney add-on (ID 52) not returned in Kitchen Deep Cleaning add-ons!\n";
    exit(1);
}
echo "Test 3: ServiceAddon::forService correctly returns companion add-ons in modal/cart - PASSED\n";

// Test 4: Admin ServiceController toggleAddonStatus toggles is_addon properly
$testSvc = Database::fetchOne("SELECT id, is_addon FROM services WHERE id = 52");
$origStatus = (int)$testSvc['is_addon'];

$adminCtrl = new ServiceController();
$_SERVER['REQUEST_METHOD'] = 'POST';
$req = new Request();
$adminCtrl->toggleAddonStatus($req, '52');

$updatedSvc = Database::fetchOne("SELECT is_addon FROM services WHERE id = 52");
if ((int)$updatedSvc['is_addon'] === $origStatus) {
    echo "FAILED: toggleAddonStatus did not toggle is_addon!\n";
    exit(1);
}

// Toggle it back to original status
$adminCtrl->toggleAddonStatus($req, '52');
$revertedSvc = Database::fetchOne("SELECT is_addon FROM services WHERE id = 52");
if ((int)$revertedSvc['is_addon'] !== $origStatus) {
    echo "FAILED: toggleAddonStatus could not revert is_addon!\n";
    exit(1);
}
echo "Test 4: Admin ServiceController toggleAddonStatus toggles is_addon reliably - PASSED\n";

echo "=== All CatalogAddonExclusion Tests PASSED! ===\n";
