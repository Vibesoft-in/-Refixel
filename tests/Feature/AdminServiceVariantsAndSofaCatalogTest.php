<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../public/index.php';

use App\Core\Database;
use App\Core\Request;
use App\Models\Service;
use App\Models\ServiceVariant;
use App\Controllers\Admin\ServiceController as AdminServiceController;

echo "=== RUNNING ADMIN SERVICE VARIANTS & SOFA CATALOG TEST SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $name, string $details = ''): void {
    global $passCount, $failCount;
    if ($cond) {
        $passCount++;
        echo "  [PASS] {$name}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$name}" . ($details ? " - {$details}" : '') . "\n";
    }
}

// -------------------------------------------------------------
// TEST 1: Sofa Deep Cleaning Catalog Integrity
// -------------------------------------------------------------
echo "[TEST 1] Verifying Sofa Cleaning Presence in Main Services Catalog...\n";
Service::ensureServiceSchema();

$sofa = Database::fetchOne("
    SELECT s.*, c.name as category_name, c.slug as category_slug 
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.slug IN ('sofa-upholstery-deep-cleaning', 'sofa-cleaning') OR s.name LIKE '%Sofa%'
    LIMIT 1
");

assertCondition(!empty($sofa), "Sofa Cleaning exists in services table");
assertCondition((int)$sofa['category_id'] === 1, "Sofa belongs to Cleaning Category (id: 1)", "Got: " . ($sofa['category_id'] ?? 'null'));
assertCondition((int)$sofa['is_addon'] === 0, "Sofa is strictly a Main Service (is_addon = 0)", "Got: " . ($sofa['is_addon'] ?? 'null'));
assertCondition((int)$sofa['is_active'] === 1, "Sofa is active (is_active = 1)", "Got: " . ($sofa['is_active'] ?? 'null'));
assertCondition(str_contains((string)$sofa['image'], 'sofa'), "Sofa image points to refixel-sofa-cleaning.jpg", "Got: " . ($sofa['image'] ?? 'null'));

// Test Service::getActive() returns Sofa
$activeServices = Service::getActive();
$hasSofaInActive = false;
foreach ($activeServices as $as) {
    if ((int)$as['id'] === (int)$sofa['id']) {
        $hasSofaInActive = true;
        break;
    }
}
assertCondition($hasSofaInActive, "Service::getActive() includes Sofa Cleaning in public catalog");

// -------------------------------------------------------------
// TEST 2: Water Tank Cleaning Starting Price & Category Integrity
// -------------------------------------------------------------
echo "\n[TEST 2] Verifying Water Tank Starting Price & Category...\n";
$tank = Database::fetchOne("
    SELECT s.*, c.name as category_name 
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.slug = 'water-tank-cleaning' OR s.name LIKE '%Tank Cleaning%'
    LIMIT 1
");

assertCondition(!empty($tank), "Water Tank service exists in services table");
assertCondition((int)$tank['category_id'] === 1, "Water Tank belongs to Category 1 (Cleaning)", "Got: " . ($tank['category_id'] ?? 'null'));
assertCondition((int)$tank['is_addon'] === 0, "Water Tank is_addon = 0", "Got: " . ($tank['is_addon'] ?? 'null'));
assertCondition((float)$tank['starting_price'] <= 400.00, "Water Tank starting price is ₹299.00 (not 799)", "Got: " . ($tank['starting_price'] ?? 'null'));
assertCondition($tank['image'] === 'refixel-water-tank-cleaning.jpg', "Water Tank image is refixel-water-tank-cleaning.jpg (not refixel-plumber.jpg)", "Got: " . ($tank['image'] ?? 'null'));

// -------------------------------------------------------------
// TEST 3: Variants Grouping & Data Loading
// -------------------------------------------------------------
echo "\n[TEST 3] Testing Service Variants Retrieval & Grouping...\n";
$allGrouped = ServiceVariant::getAllGroupedByServiceId();
$tankId = (int)$tank['id'];
$sofaId = (int)$sofa['id'];

assertCondition(isset($allGrouped[$tankId]), "Water Tank has variants registered in service_variants");
assertCondition(count($allGrouped[$tankId]) >= 4, "Water Tank has 4+ size options (250L, 500L, 1000L, 2000L)", "Found: " . count($allGrouped[$tankId] ?? []));

assertCondition(isset($allGrouped[$sofaId]), "Sofa has variants registered in service_variants");
assertCondition(count($allGrouped[$sofaId]) >= 5, "Sofa has 5+ seat options (3 to 7 seater)", "Found: " . count($allGrouped[$sofaId] ?? []));

// -------------------------------------------------------------
// TEST 4: Admin API - variantsJson()
// -------------------------------------------------------------
echo "\n[TEST 4] Testing Admin variantsJson API...\n";
$adminCtrl = new AdminServiceController();
$req = new Request();

$jsonResp = $adminCtrl->variantsJson($req, (string)$tankId);
$payload = json_decode($jsonResp->getContent(), true);

assertCondition($payload['success'] === true, "variantsJson returns success = true");
assertCondition(is_array($payload['variants']) && count($payload['variants']) >= 4, "variantsJson returns all capacity variants");
$firstVar = $payload['variants'][0];
assertCondition(!empty($firstVar['name']) && isset($firstVar['price']), "Variant object contains name and price", json_encode($firstVar));

// -------------------------------------------------------------
// TEST 5: Admin API - Quick Price Updating according to Size
// -------------------------------------------------------------
echo "\n[TEST 5] Testing Admin Quick Price Update According to Size...\n";
$var500L = Database::fetchOne("
    SELECT * FROM service_variants 
    WHERE service_id = ? AND (name LIKE '%500%' OR name LIKE '%1000%') 
    LIMIT 1
", [$tankId]);

assertCondition(!empty($var500L), "Target variant found for price editing");
$vId = (int)$var500L['id'];
$originalPrice = (float)$var500L['price'];
$testNewPrice = 450.00;

$_POST['price'] = $testNewPrice;
$priceReq = new Request();
$quickPriceResp = $adminCtrl->quickPriceVariant($priceReq, (string)$vId);
$quickPayload = json_decode($quickPriceResp->getContent(), true);

assertCondition($quickPayload['success'] === true, "quickPriceVariant returns success");
assertCondition((float)$quickPayload['price'] === $testNewPrice, "quickPriceVariant returns updated price ₹{$testNewPrice}");

$updatedInDb = Database::fetchOne("SELECT price FROM service_variants WHERE id = ?", [$vId]);
assertCondition((float)$updatedInDb['price'] === $testNewPrice, "DB reflects updated price for size {$var500L['name']}");

// Revert back
$_POST['price'] = $originalPrice;
$revertReq = new Request();
$adminCtrl->quickPriceVariant($revertReq, (string)$vId);
$revertedInDb = Database::fetchOne("SELECT price FROM service_variants WHERE id = ?", [$vId]);
assertCondition((float)$revertedInDb['price'] === $originalPrice, "Price reverted back to original ₹{$originalPrice}");

// -------------------------------------------------------------
// TEST 6: Admin API - Adding New Size Option
// -------------------------------------------------------------
echo "\n[TEST 6] Testing Admin Quick Size Addition...\n";
$_POST['name'] = 'Test 5000 Litres Mega';
$_POST['price'] = 2999.00;
$_POST['group_name'] = 'Capacity';
$addReq = new Request();

$addResp = $adminCtrl->addQuickVariant($addReq, (string)$tankId);
$addPayload = json_decode($addResp->getContent(), true);

assertCondition($addPayload['success'] === true, "addQuickVariant returns success");
$newVarId = (int)($addPayload['variant']['id'] ?? 0);
assertCondition($newVarId > 0, "New variant ID generated: {$newVarId}");

$checkNewInDb = Database::fetchOne("SELECT * FROM service_variants WHERE id = ?", [$newVarId]);
assertCondition(!empty($checkNewInDb), "New size 'Test 5000 Litres Mega' verified in DB");
assertCondition((float)$checkNewInDb['price'] === 2999.00, "New size price is ₹2999.00");

// Clean up test variant
Database::execute("DELETE FROM service_variants WHERE id = ?", [$newVarId]);
assertCondition(empty(Database::fetchOne("SELECT id FROM service_variants WHERE id = ?", [$newVarId])), "Test size cleaned up cleanly");

// -------------------------------------------------------------
// TEST 7: View Template Checks
// -------------------------------------------------------------
echo "\n[TEST 7] Verifying Admin & Customer View Templates...\n";
$adminIndexView = file_get_contents(ROOT_PATH . '/app/views/admin/services/index.php');
assertCondition(str_contains($adminIndexView, 'edit_variant_dropdown'), "Admin index view contains edit_variant_dropdown size dropdown");
assertCondition(str_contains($adminIndexView, 'btn_save_single_variant_price'), "Admin index view contains quick price save button");
assertCondition(str_contains($adminIndexView, 'edit_variants_tbody'), "Admin index view contains table for editing all size prices");
assertCondition(str_contains($adminIndexView, 'variantsByService'), "Admin index view displays variant size badges in table column");

$categoryView = file_get_contents(ROOT_PATH . '/app/views/customer/services/category.php');
assertCondition(str_contains($categoryView, 'card-variant-pill'), "Category view contains card-variant-pill for customer size switching");
assertCondition(str_contains($categoryView, 'card-price-display'), "Category view contains card-price-display for dynamic price update");

echo "\n=========================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "=========================================\n";

if ($failCount > 0) {
    exit(1);
}
