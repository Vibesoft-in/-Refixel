<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Controllers\ServiceController;

echo "=== RUNNING ELECTRICIAN SERVICES & NEW PACKAGES FEATURE TEST ===\n\n";

Service::ensureServiceSchema();

// -------------------------------------------------------------
// TEST 1: All 4 Electrician Packages Active in Database
// -------------------------------------------------------------
echo "[RUN] Test 1: Category 7 (Electrician) active services count & fields\n";
$electricianServices = Service::getActiveByCategory(7);
assert(count($electricianServices) >= 4, "Expected at least 4 active electrician services, found: " . count($electricianServices));

$slugs = array_column($electricianServices, 'slug');
assert(in_array('light-fitting-installation', $slugs, true), "Missing light-fitting-installation");
assert(in_array('wiring-mcb-repair', $slugs, true), "Missing wiring-mcb-repair");
assert(in_array('appliance-installation', $slugs, true), "Missing appliance-installation");
assert(in_array('fan-switchboard-repair', $slugs, true), "Missing fan-switchboard-repair");
echo "[PASS] Test 1: All 4 Electrician packages confirmed active in DB.\n\n";

// -------------------------------------------------------------
// TEST 2: Verify Dedicated Branded Images on Disk
// -------------------------------------------------------------
echo "[RUN] Test 2: Verify dedicated REFIXEL branded images exist on disk\n";
$imagesToCheck = [
    'service-light-fitting-installation.jpg',
    'service-wiring-mcb-repair.jpg',
    'service-appliance-installation.jpg',
    'service-fan-switchboard-repair.jpg',
    'refixel-electrician.jpg',
];

foreach ($imagesToCheck as $img) {
    $path1 = 'c:/xampp/htdocs/REFIXEL/public/assets/img/' . $img;
    $path2 = 'c:/xampp/htdocs/REFIXEL/public/img/' . $img;
    assert(file_exists($path1), "Missing image at: $path1");
    assert(file_exists($path2), "Missing mirrored image at: $path2");
    assert(filesize($path1) > 10000, "Image $img is too small or corrupt: " . filesize($path1) . " bytes");
    echo "  - Image verified: $img (" . round(filesize($path1)/1024) . " KB)\n";
}
echo "[PASS] Test 2: All 4 images exist and verified.\n\n";

// -------------------------------------------------------------
// TEST 3: Verify Checklists for Each New Service
// -------------------------------------------------------------
echo "[RUN] Test 3: Verify checklist items for each new service\n";
$lightSvc = Database::fetchOne("SELECT id FROM services WHERE slug = 'light-fitting-installation'");
$wiringSvc = Database::fetchOne("SELECT id FROM services WHERE slug = 'wiring-mcb-repair'");
$applianceSvc = Database::fetchOne("SELECT id FROM services WHERE slug = 'appliance-installation'");

assert($lightSvc !== null && $wiringSvc !== null && $applianceSvc !== null, "Service records not found in DB");

$lightChecklist = ServiceChecklistItem::getByService((int)$lightSvc['id']);
$wiringChecklist = ServiceChecklistItem::getByService((int)$wiringSvc['id']);
$applianceChecklist = ServiceChecklistItem::getByService((int)$applianceSvc['id']);

assert(count($lightChecklist) >= 4, "Expected at least 4 checklist items for Light Fitting");
assert(count($wiringChecklist) >= 5, "Expected at least 5 checklist items for Wiring & MCB");
assert(count($applianceChecklist) >= 5, "Expected at least 5 checklist items for Appliance Installation");
echo "[PASS] Test 3: Checklists verified for all 3 new services.\n\n";

// -------------------------------------------------------------
// TEST 4: Category Page Rendering (Electrician in Kashipur)
// -------------------------------------------------------------
echo "[RUN] Test 4: Category page renders all 4 packages\n";
$ctrl = new ServiceController();
$req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/electrician-services-in-kashipur']);
$resp = $ctrl->categoryInCity($req, 'electrician', 'kashipur');

assert($resp instanceof Response, "Expected Response");
assert($resp->getStatusCode() === 200, "Expected status 200, got: " . $resp->getStatusCode());
$body = $resp->getBody();

assert(str_contains($body, 'Light &amp; Fitting Installation') || str_contains($body, 'Light & Fitting Installation'), "Missing Light & Fitting Installation in category view");
assert(str_contains($body, 'Wiring &amp; MCB Repair') || str_contains($body, 'Wiring & MCB Repair'), "Missing Wiring & MCB Repair in category view");
assert(str_contains($body, 'Appliance Installation'), "Missing Appliance Installation in category view");
assert(str_contains($body, 'Fan &amp; Switchboard Repair') || str_contains($body, 'Fan & Switchboard Repair'), "Missing Fan & Switchboard Repair in category view");
assert(str_contains($body, '149'), "Missing starting price 149 in category view");
assert(str_contains($body, '249'), "Missing starting price 249 in category view");
assert(str_contains($body, '299'), "Missing starting price 299 in category view");
echo "[PASS] Test 4: Category page successfully rendered all 4 packages with prices & descriptions.\n\n";

echo "🎉 ALL ELECTRICIAN PACKAGES TESTS PASSED PERFECTLY!\n";
