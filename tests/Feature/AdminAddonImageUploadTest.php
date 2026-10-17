<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\Admin\ServiceAddonController;
use App\Core\Database;
use App\Core\Request;
use App\Models\Service;
use App\Models\ServiceAddon;

echo "=== Running AdminAddonImageUploadTest ===\n";

// 1. Verify view rendering contains file inputs, enctype, and preview boxes
$adminCtrl = new ServiceAddonController();
$reqIndex = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/admin/services/addons',
]);
$resp = $adminCtrl->index($reqIndex);
$html = $resp->getContent();

assert(strpos($html, 'enctype="multipart/form-data"') !== false, "Forms must have enctype='multipart/form-data'");
assert(strpos($html, 'id="modalAddonImageInput"') !== false, "Create modal must have #modalAddonImageInput file input");
assert(strpos($html, 'id="editAddonImageInput"') !== false, "Edit modal must have #editAddonImageInput file input");
assert(strpos($html, 'id="modalAddonImagePreview"') !== false, "Create modal must have #modalAddonImagePreview");
assert(strpos($html, 'id="editAddonImagePreview"') !== false, "Edit modal must have #editAddonImagePreview");

echo "Test 1: Admin Add-ons page renders file upload controls and image previewers - PASSED\n";

// 2. Prepare test service and clean up old test pairings
$testAddonSvc = Service::findBy('slug', 'balcony-exhaust-fan-deep-clean') 
    ?: Service::findBy('slug', 'bathroom-exhaust-fan-deep-clean') 
    ?: Database::fetchOne("SELECT * FROM services WHERE is_active = 1 LIMIT 1");
assert(!empty($testAddonSvc), "Test companion service must exist");
$aid = (int)$testAddonSvc['id'];

Database::query("DELETE FROM service_addons WHERE addon_service_id = :aid AND custom_badge = 'Upload Test'", ['aid' => $aid]);

// Create real temporary PNG image from existing asset
$sourcePng = __DIR__ . '/../../public/favicon.png';
assert(file_exists($sourcePng), "Source test png must exist");

$tmpImgPath1 = sys_get_temp_dir() . '/test_addon_' . uniqid() . '.png';
copy($sourcePng, $tmpImgPath1);

assert(file_exists($tmpImgPath1), "Temp image 1 must be created");

$fileUpload1 = [
    'name'     => 'test_addon_image.png',
    'type'     => 'image/png',
    'tmp_name' => $tmpImgPath1,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tmpImgPath1),
];

// Test Store Addon with Image
$storeReq = new Request([], [
    'source_category_ids' => [1],
    'source_service_id'   => 0,
    'addon_service_id'    => $aid,
    'custom_badge'        => 'Upload Test',
    'custom_price'        => '299.00',
    'custom_note'         => 'Test note for image upload',
    'sort_order'          => 1,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/admin/services/addons/store',
], [
    'image' => $fileUpload1,
]);

$adminCtrl->store($storeReq);

// Verify the record in service_addons
$createdRow = Database::fetchOne(
    "SELECT id, custom_image, custom_price, custom_badge FROM service_addons WHERE addon_service_id = :aid AND custom_badge = 'Upload Test' LIMIT 1",
    ['aid' => $aid]
);

assert(!empty($createdRow), "Addon rule must be created");
assert(!empty($createdRow['custom_image']), "custom_image column must not be empty");
assert(str_starts_with($createdRow['custom_image'], 'uploads/addons/'), "custom_image path must start with uploads/addons/");

$savedFile1 = __DIR__ . '/../../public/' . $createdRow['custom_image'];
assert(file_exists($savedFile1), "Uploaded image file must exist on disk at {$savedFile1}");

// Verify service companion record also has image updated
$updatedService = Service::find($aid);
assert(!empty($updatedService['image']), "Companion service record must have image updated");
assert($updatedService['image'] === $createdRow['custom_image'], "Companion service image must match uploaded image");

echo "Test 2: Store add-on pairing with image upload saves file and database path - PASSED\n";

// 3. Test Update Add-on with a replacement image
$ruleId = (int)$createdRow['id'];

$tmpImgPath2 = sys_get_temp_dir() . '/test_addon_update_' . uniqid() . '.png';
copy($sourcePng, $tmpImgPath2);

$fileUpload2 = [
    'name'     => 'test_addon_update.png',
    'type'     => 'image/png',
    'tmp_name' => $tmpImgPath2,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tmpImgPath2),
];

$updateReq = new Request([], [
    'source_category_ids' => [1, 2],
    'source_service_id'   => 0,
    'addon_service_id'    => $aid,
    'custom_badge'        => 'Upload Test Updated',
    'custom_price'        => '349.00',
    'custom_note'         => 'Updated note with new image',
    'sort_order'          => 2,
    'is_active'           => 1,
], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => "/admin/services/addons/{$ruleId}/update",
], [
    'image' => $fileUpload2,
]);

$adminCtrl->update($updateReq, (string)$ruleId);

$updatedRow = Database::fetchOne(
    "SELECT id, custom_image, custom_price, custom_badge FROM service_addons WHERE id = :id",
    ['id' => $ruleId]
);

assert(!empty($updatedRow), "Updated row must exist");
assert($updatedRow['custom_badge'] === 'Upload Test Updated', "Badge should be updated");
assert($updatedRow['custom_image'] !== $createdRow['custom_image'], "New custom_image must differ from previous image");
assert(str_starts_with($updatedRow['custom_image'], 'uploads/addons/'), "New image path must start with uploads/addons/");

$savedFile2 = __DIR__ . '/../../public/' . $updatedRow['custom_image'];
assert(file_exists($savedFile2), "Second uploaded image file must exist on disk at {$savedFile2}");

echo "Test 3: Update add-on pairing with new image replaces custom_image and saves new file - PASSED\n";

// 4. Test ServiceAddon model output prioritizes custom_image
$parentSvc = Database::fetchOne("SELECT id FROM services WHERE category_id = 1 AND id != :aid AND is_active = 1 LIMIT 1", ['aid' => $aid]);
if ($parentSvc) {
    $addons = ServiceAddon::forService((int)$parentSvc['id'], [], 10);
    $foundTestAddon = false;
    foreach ($addons as $addon) {
        if ((int)$addon['id'] === $aid) {
            $foundTestAddon = true;
            assert(str_contains($addon['image'], $updatedRow['custom_image']), "Model formatAddonItem must prioritize custom_image");
            break;
        }
    }
    assert($foundTestAddon, "Uploaded addon must be returned by ServiceAddon::forService");
}

echo "Test 4: ServiceAddon model correctly prioritizes custom_image in recommendation payload - PASSED\n";

// 5. Clean up test files and DB records
if (file_exists($tmpImgPath1)) @unlink($tmpImgPath1);
if (file_exists($tmpImgPath2)) @unlink($tmpImgPath2);
if (file_exists($savedFile1)) @unlink($savedFile1);
if (file_exists($savedFile2)) @unlink($savedFile2);
Database::query("DELETE FROM service_addons WHERE addon_service_id = :aid AND custom_badge LIKE 'Upload Test%'", ['aid' => $aid]);

echo "=== AdminAddonImageUploadTest ALL TESTS PASSED! ===\n";
