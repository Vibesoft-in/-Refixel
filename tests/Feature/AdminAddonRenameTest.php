<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Controllers\Admin\ServiceAddonController;

echo "=== Running AdminAddonRenameTest ===\n";

// 1. Authenticate as Admin
Auth::login([
    'id'    => 1,
    'name'  => 'Admin User',
    'phone' => '9999900000',
    'role'  => 'admin',
]);

// 2. Setup a test add-on service
Service::ensureServiceSchema();

$testAddonName = 'Test Chimney Filter Degreasing ' . rand(1000, 9999);
Database::execute(
    "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, is_active, is_addon, created_at)
     VALUES (1, :name, :slug, 'Addon description', 299.00, 30, 1, 1, NOW())",
    [
        'name' => $testAddonName,
        'slug' => 'test-addon-rename-' . rand(1000, 9999),
    ]
);
$testAddonId = (int)Database::lastInsertId();
assert($testAddonId > 0, "Failed to create test add-on service");

// Create a pairing rule for this addon
$pairRuleId = ServiceAddon::create([
    'source_category_id' => 1,
    'source_service_id'  => null,
    'addon_service_id'   => $testAddonId,
    'custom_badge'       => '🔥 Popular Add-on',
    'custom_price'       => 249.00,
    'custom_note'        => 'Companion Special',
    'sort_order'         => 0,
    'is_active'          => 1,
]);
assert($pairRuleId > 0, "Failed to create test add-on pairing rule");

$controller = new ServiceAddonController();

// -------------------------------------------------------------
// Test 1: Quick Rename via AJAX
// -------------------------------------------------------------
echo "[RUN] Test 1: Quick rename add-on via AJAX\n";
$renamedTitle1 = 'Ultra Chimney Degreasing ' . rand(100, 999);
$ajaxReq = new Request(
    [],
    ['addon_service_id' => $testAddonId, 'name' => $renamedTitle1],
    ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/services/addons/rename', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
);
$ajaxResp = $controller->updateAddonName($ajaxReq);
assert($ajaxResp->getStatusCode() === 200, "Expected 200 OK from quick rename AJAX");

$ajaxData = json_decode($ajaxResp->getContent(), true);
assert($ajaxData['success'] === true, "Expected success: true in quick rename AJAX");
assert($ajaxData['name'] === $renamedTitle1, "Expected returned name to match new name");

$svcAfterAjax = Service::find($testAddonId);
assert($svcAfterAjax['name'] === $renamedTitle1, "Database service name must match new renamed title");
echo "[PASS] Test 1: Quick rename via AJAX succeeded and updated database.\n\n";

// -------------------------------------------------------------
// Test 2: Quick Rename via standard POST form submission (redirect)
// -------------------------------------------------------------
echo "[RUN] Test 2: Quick rename add-on via standard POST form submission\n";
$renamedTitle2 = 'Premium Chimney Jet Wash ' . rand(100, 999);
$formReq = new Request(
    [],
    ['addon_service_id' => $testAddonId, 'name' => $renamedTitle2],
    ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/services/addons/rename']
);
$formResp = $controller->updateAddonName($formReq);
assert($formResp->getStatusCode() === 302, "Expected 302 Redirect from standard POST quick rename");

$svcAfterForm = Service::find($testAddonId);
assert($svcAfterForm['name'] === $renamedTitle2, "Database service name must match form renamed title");
echo "[PASS] Test 2: Quick rename via standard POST redirect succeeded.\n\n";

// -------------------------------------------------------------
// Test 3: Validation on empty name & invalid ID
// -------------------------------------------------------------
echo "[RUN] Test 3: Validation handles empty name and invalid ID\n";
$emptyNameReq = new Request(
    [],
    ['addon_service_id' => $testAddonId, 'name' => '   '],
    ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
);
$emptyResp = $controller->updateAddonName($emptyNameReq);
assert($emptyResp->getStatusCode() === 400, "Expected 400 Bad Request for empty name");

$invalidIdReq = new Request(
    [],
    ['addon_service_id' => 999999, 'name' => 'Valid Name'],
    ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
);
$invalidResp = $controller->updateAddonName($invalidIdReq);
assert($invalidResp->getStatusCode() === 404, "Expected 404 Not Found for non-existent service ID");
echo "[PASS] Test 3: Validation correctly rejects empty names and non-existent IDs.\n\n";

// -------------------------------------------------------------
// Test 4: Renaming via pairing rule update()
// -------------------------------------------------------------
echo "[RUN] Test 4: Renaming add-on within pairing rule update modal\n";
$renamedTitle3 = 'Deluxe Chimney Degreaser & Polish ' . rand(100, 999);
$updateReq = new Request(
    [],
    [
        'id'                  => $pairRuleId,
        'source_category_ids' => [1],
        'addon_service_id'    => $testAddonId,
        'addon_name'          => $renamedTitle3,
        'custom_badge'        => '⭐ Must-Have Add-on',
        'custom_price'        => 279.00,
        'custom_note'         => 'Complete sanitization',
        'sort_order'          => 2,
        'is_active'           => 1,
    ],
    ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/services/addons/' . $pairRuleId . '/update']
);
$updateResp = $controller->update($updateReq, (string)$pairRuleId);
assert($updateResp->getStatusCode() === 302, "Expected 302 Redirect from pairing update");

$svcAfterPairingUpdate = Service::find($testAddonId);
assert($svcAfterPairingUpdate['name'] === $renamedTitle3, "Database service name must match addon_name updated via pairing form");
echo "[PASS] Test 4: Renaming within pairing rule update modal succeeded.\n\n";

// -------------------------------------------------------------
// Test 5: Verify rendered UI elements in Addons Management Page
// -------------------------------------------------------------
echo "[RUN] Test 5: Verify Edit Name button and modal on addons management page\n";
$indexReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/services/addons']);
$indexResp = $controller->index($indexReq);
assert($indexResp->getStatusCode() === 200, "Expected 200 OK from addons index");
$html = $indexResp->getContent();

assert(str_contains($html, 'quick-rename-btn'), "Expected .quick-rename-btn in table rows");
assert(str_contains($html, 'Edit Name'), "Expected 'Edit Name' button text in table");
assert(str_contains($html, 'quickRenameModal'), "Expected #quickRenameModal in addons page HTML");
assert(str_contains($html, 'editAddonNameInput'), "Expected #editAddonNameInput in edit pairing modal");
assert(str_contains($html, View::e($renamedTitle3)), "Expected newly renamed add-on title to appear in table HTML");
echo "[PASS] Test 5: Admin Addons page displays Edit Name buttons, quick rename modal, and updated add-on title.\n\n";

// Cleanup test records
Database::execute("DELETE FROM service_addons WHERE id = :id", ['id' => $pairRuleId]);
Database::execute("DELETE FROM services WHERE id = :id", ['id' => $testAddonId]);

echo "=== ALL AdminAddonRenameTest TESTS PASSED! ===\n";
