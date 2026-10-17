<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\Admin\ServiceAddonController;
use App\Core\Database;
use App\Core\Request;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceAddon;

echo "=== Running AdminMultiCategoryAddonTest ===\n";

// 1. Verify view rendering contains category checkboxes and quick buttons
$adminCtrl = new ServiceAddonController();
$reqIndex = new Request([], [], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/admin/services/addons',
]);
$resp = $adminCtrl->index($reqIndex);
$html = $resp->getContent();

assert(strpos($html, 'class="modal-cat-chk') !== false, "HTML must contain .modal-cat-chk checkboxes in Create modal");
assert(strpos($html, 'class="edit-modal-cat-chk') !== false, "HTML must contain .edit-modal-cat-chk checkboxes in Edit modal");
assert(strpos($html, 'select-all-cats-btn') !== false, "HTML must contain Select All quick button");
assert(strpos($html, 'clear-all-cats-btn') !== false, "HTML must contain Clear quick button");
assert(strpos($html, 'data-all-category-ids') !== false, "HTML must contain data-all-category-ids on edit buttons");

echo "Test 1: Admin Add-ons page renders category checkbox grid and quick controls - PASSED\n";

// 2. Test Store Add-on across Multiple Categories (e.g. Categories 1 (Cleaning), 6 (AC), 9 (Pest Control))
$testAddonSvc = Service::findBy('slug', 'bathroom-exhaust-fan-deep-clean') ?: Service::findBy('slug', 'cockroach-pest-control');
assert(!empty($testAddonSvc), "Test companion addon service must exist");
$aid = (int)$testAddonSvc['id'];

// Clean up any test pairings for this addon
Database::query("DELETE FROM service_addons WHERE addon_service_id = :aid AND custom_badge = 'Multi-Cat Test'", ['aid' => $aid]);

$catsToTest = [1, 6, 9]; // Cleaning, AC, Pest Control

$storeReq = new Request([], [
    'source_category_ids' => $catsToTest,
    'source_service_id'   => 0,
    'addon_service_id'    => $aid,
    'custom_badge'        => 'Multi-Cat Test',
    'custom_price'        => '111.00',
    'custom_note'         => 'Multi Category Note',
    'sort_order'          => 5,
], [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/admin/services/addons/store',
]);

$adminCtrl->store($storeReq);

// Verify pairings exist for all 3 categories
foreach ($catsToTest as $cid) {
    $row = Database::fetchOne(
        "SELECT id, custom_price, custom_badge FROM service_addons WHERE source_category_id = :cid AND addon_service_id = :aid AND custom_badge = 'Multi-Cat Test'",
        ['cid' => $cid, 'aid' => $aid]
    );
    assert(!empty($row), "Pairing must exist in category {$cid} for addon {$aid}");
    assert((float)$row['custom_price'] === 111.00, "Price in category {$cid} must be 111.00");
}

echo "Test 2: Store Add-on across multiple categories creates pairings for all selected categories - PASSED\n";

// 3. Test Update Add-on: edit one of the rules and add another category (Category 2: Painting)
$firstRule = Database::fetchOne(
    "SELECT id FROM service_addons WHERE source_category_id = 1 AND addon_service_id = :aid AND custom_badge = 'Multi-Cat Test'",
    ['aid' => $aid]
);
assert(!empty($firstRule), "First rule must exist");

$updateReq = new Request([], [
    'id'                  => (int)$firstRule['id'],
    'source_category_ids' => [1, 2], // Keep Cleaning, add Painting
    'addon_service_id'    => $aid,
    'custom_badge'        => 'Updated Multi-Cat',
    'custom_price'        => '122.00',
    'custom_note'         => 'Updated Note',
    'sort_order'          => 3,
    'is_active'           => 1,
], [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/admin/services/addons/' . $firstRule['id'] . '/update',
]);

$adminCtrl->update($updateReq, (int)$firstRule['id']);

// Verify Category 2 now also has this pairing with the updated details
$cat2Rule = Database::fetchOne(
    "SELECT id, custom_price, custom_badge FROM service_addons WHERE source_category_id = 2 AND addon_service_id = :aid",
    ['aid' => $aid]
);
assert(!empty($cat2Rule), "Category 2 must now have pairing for addon {$aid}");
assert($cat2Rule['custom_badge'] === 'Updated Multi-Cat', "Badge must be updated to 'Updated Multi-Cat'");

echo "Test 3: Update Add-on extends pairing across newly checked categories - PASSED\n";

// Clean up test data
Database::query("DELETE FROM service_addons WHERE addon_service_id = :aid AND (custom_badge = 'Multi-Cat Test' OR custom_badge = 'Updated Multi-Cat')", ['aid' => $aid]);

echo "=== All AdminMultiCategoryAddon Tests PASSED ===\n";
