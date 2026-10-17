<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Controllers\Admin\ServiceAddonController;
use App\Controllers\BookingController;

echo "=== RUNNING ADMIN ADD-ONS CONTROL & SENSIBLE PAIRING TESTS ===\n";

// 1. Test Sensible Kitchen Addons
$kitchenAddons = Service::getAddonRecommendations(3, [], 4);
assert(!empty($kitchenAddons), "Test 1 Failed: Kitchen addons should not be empty");
$hasKitchenSpecific = false;
foreach ($kitchenAddons as $a) {
    $slug = $a['slug'] ?? '';
    if (str_contains($slug, 'chimney') || str_contains($slug, 'microwave') || str_contains($slug, 'refrigerator') || str_contains($slug, 'pest') || str_contains($slug, 'drain')) {
        $hasKitchenSpecific = true;
        break;
    }
}
assert($hasKitchenSpecific, "Test 1 Failed: Kitchen addons must contain sensible kitchen-specific addons (Chimney, Microwave, Fridge, Drain, or Pest)");
echo "Test 1: Sensible Kitchen Cleaning Add-ons (Chimney, Microwave, Refrigerator, Pest, Drain) - PASSED\n";

// 2. Test Sensible Bathroom Addons
$bathroomAddons = Service::getAddonRecommendations(2, [], 4);
assert(!empty($bathroomAddons), "Test 2 Failed: Bathroom addons should not be empty");
$hasBathroomSpecific = false;
foreach ($bathroomAddons as $a) {
    $slug = $a['slug'] ?? '';
    if (str_contains($slug, 'tile') || str_contains($slug, 'tap') || str_contains($slug, 'exhaust') || str_contains($slug, 'drain')) {
        $hasBathroomSpecific = true;
        break;
    }
}
assert($hasBathroomSpecific, "Test 2 Failed: Bathroom addons must contain sensible bathroom-specific addons (Tiles, Tap/Shower, Exhaust, Drain)");
echo "Test 2: Sensible Bathroom Cleaning Add-ons (Tiles Stain, Tap Descaling, Exhaust Fan, Leak) - PASSED\n";

// 3. Test Sensible Room / Full Home Cleaning Addons
$homeAddons = Service::getAddonRecommendations(1, [], 4);
assert(!empty($homeAddons), "Test 3 Failed: Full Home addons should not be empty");
$hasHomeSpecific = false;
foreach ($homeAddons as $a) {
    $slug = $a['slug'] ?? '';
    if (str_contains($slug, 'sofa') || str_contains($slug, 'pest') || str_contains($slug, 'balcony') || str_contains($slug, 'mattress') || str_contains($slug, 'bathroom')) {
        $hasHomeSpecific = true;
        break;
    }
}
assert($hasHomeSpecific, "Test 3 Failed: Full Home addons must contain sensible companion addons (Sofa, Balcony, Pest, Mattress, Bath)");
echo "Test 3: Sensible Full Home Cleaning Add-ons (Sofa, Mattress, Balcony, Pest) - PASSED\n";

// 4. Test Admin Controller Index
// Create or authenticate admin
$adminUser = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if (!$adminUser) {
    $adminId = Database::query(
        "INSERT INTO users (name, email, phone, password, role, status, created_at)
         VALUES ('Admin Test', 'admintest_addon@refixel.com', '9888877771', 'hash', 'admin', 'active', NOW())"
    );
    $adminUser = Database::fetchOne("SELECT * FROM users WHERE email = 'admintest_addon@refixel.com'");
}
Auth::login($adminUser);

$addonCtrl = new ServiceAddonController();
$indexReq = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/services/addons']);
$indexRes = $addonCtrl->index($indexReq);
assert($indexRes->getStatusCode() === 200, "Test 4 Failed: Admin Addons index should return 200");
$indexHtml = $indexRes->getContent();
assert(strpos($indexHtml, 'Add-ons Control &amp; Smart Pairing') !== false || strpos($indexHtml, 'Add-ons Control') !== false, "Test 4 Failed: View should contain Add-ons Control heading");
assert(strpos($indexHtml, 'Auto-Sensible') !== false, "Test 4 Failed: View should mention Auto-Sensible engine");
echo "Test 4: Admin Add-ons Dashboard View & KPIs Rendering - PASSED\n";

// 5. Test Admin Store Pairing Rule
Database::query("DELETE FROM service_addons WHERE source_service_id = 6 AND addon_service_id = 14");

$storeReq = new Request([], [
    '_csrf'              => Csrf::token(),
    'source_service_id'  => 6, // Interior Home Painting
    'addon_service_id'   => 14, // Fall Ceiling Repair
    'custom_badge'       => '🎨 Perfect Match',
    'custom_price'       => '999.00',
    'custom_note'        => 'Patch ceiling before painting walls',
    'sort_order'         => 1,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/services/addons']);

$storeRes = $addonCtrl->store($storeReq);
assert($storeRes->getStatusCode() === 302, "Test 5 Failed: Store pairing rule should redirect (302)");

$createdRule = Database::fetchOne(
    "SELECT * FROM service_addons WHERE source_service_id = 6 AND addon_service_id = 14"
);
assert($createdRule !== null, "Test 5 Failed: Created rule must exist in database");
assert($createdRule['custom_badge'] === '🎨 Perfect Match', "Test 5 Failed: Custom badge must match");
assert((float)$createdRule['custom_price'] === 999.00, "Test 5 Failed: Custom companion price must match");
echo "Test 5: Admin Store Add-on Pairing Rule - PASSED\n";

// 6. Test Admin Update Pairing Rule (Change Category and Service)
$ruleId = (int)$createdRule['id'];
$updateReq = new Request([], [
    '_csrf'              => Csrf::token(),
    'source_category_id' => 1, // Category: Cleaning
    'source_service_id'  => 1, // Target: Full Home Cleaning
    'custom_badge'       => '⭐ Recommended Finish',
    'custom_price'       => '899.00',
    'custom_note'        => 'Special combo discount rate',
    'sort_order'         => 2,
    'is_active'          => 1,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/services/addons/{$ruleId}/update"]);

$updateRes = $addonCtrl->update($updateReq, (string)$ruleId);
assert($updateRes->getStatusCode() === 302, "Test 6 Failed: Update pairing rule should redirect");

$updatedRule = Database::fetchOne("SELECT * FROM service_addons WHERE id = :id", ['id' => $ruleId]);
assert($updatedRule['custom_badge'] === '⭐ Recommended Finish', "Test 6 Failed: Updated badge must match");
assert((float)$updatedRule['custom_price'] === 899.00, "Test 6 Failed: Updated price must match");
assert((int)$updatedRule['source_category_id'] === 1, "Test 6 Failed: Updated category must be 1 (Cleaning)");
assert((int)$updatedRule['source_service_id'] === 1, "Test 6 Failed: Updated source service must be 1 (Full Home Clean)");
echo "Test 6: Admin Update Add-on Rule (Put into Category & Target Service) - PASSED\n";

// 7. Test Admin Toggle Status
$toggleReq = new Request([], ['_csrf' => Csrf::token()], [
    'REQUEST_METHOD'         => 'POST',
    'HTTP_X_REQUESTED_WITH'  => 'XMLHttpRequest',
    'REQUEST_URI'            => "/admin/services/addons/{$ruleId}/toggle-status"
]);

$toggleRes = $addonCtrl->toggleStatus($toggleReq, (string)$ruleId);
assert($toggleRes->getStatusCode() === 200, "Test 7 Failed: AJAX toggle should return 200");
$toggleData = json_decode($toggleRes->getContent(), true);
assert($toggleData['status'] === 'success', "Test 7 Failed: Toggle status must be success");
assert($toggleData['is_active'] === 0, "Test 7 Failed: Status should toggle from 1 to 0");
echo "Test 7: Admin 1-Click Toggle Active Status - PASSED\n";

// 8. Test Toggle Auto-Sensible Engine
$initialAuto = ServiceAddon::isAutoSensibleEnabled();
$autoReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/services/addons/toggle-auto-engine']);
$autoRes = $addonCtrl->toggleAutoEngine($autoReq);
assert($autoRes->getStatusCode() === 302, "Test 8 Failed: Toggle auto engine should redirect");
assert(ServiceAddon::isAutoSensibleEnabled() === !$initialAuto, "Test 8 Failed: Auto sensible engine state must invert");
// Restore auto sensible to true
ServiceAddon::setAutoSensibleEnabled(true);
echo "Test 8: Admin Global Auto-Sensible Engine Toggle - PASSED\n";

// 9. Test Delete Pairing Rule
$delReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/services/addons/{$ruleId}/delete"]);
$delRes = $addonCtrl->delete($delReq, (string)$ruleId);
assert($delRes->getStatusCode() === 302, "Test 9 Failed: Delete should redirect");
$deletedCheck = Database::fetchOne("SELECT id FROM service_addons WHERE id = :id", ['id' => $ruleId]);
assert($deletedCheck === null, "Test 9 Failed: Deleted rule must no longer exist");
echo "Test 9: Admin Delete Add-on Pairing Rule - PASSED\n";

// 10. Test Customer API Endpoint with Sensible Addons
$bookingCtrl = new BookingController();
$apiReq = new Request(['service_id' => 3], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/cart/addons?service_id=3']);
$apiRes = $bookingCtrl->apiCartAddons($apiReq);
assert($apiRes->getStatusCode() === 200, "Test 10 Failed: apiCartAddons should return 200");
$apiData = json_decode($apiRes->getContent(), true);
assert($apiData['success'] === true, "Test 10 Failed: Success must be true");
assert(!empty($apiData['addons']), "Test 10 Failed: Addons array must not be empty");
foreach ($apiData['addons'] as $add) {
    assert(!empty($add['id']), "Test 10 Failed: Addon item must have ID");
    assert(!empty($add['name']), "Test 10 Failed: Addon item must have name");
    assert(!empty($add['starting_price']), "Test 10 Failed: Addon item must have price");
    assert(!empty($add['badge']), "Test 10 Failed: Addon item must have marketing badge");
}
echo "Test 10: Customer /api/cart/addons API with Sensible Add-ons Data - PASSED\n";

echo "\n=== ALL 10 ADMIN ADD-ONS CONTROL & SENSIBLE PAIRING TESTS PASSED! ===\n";
