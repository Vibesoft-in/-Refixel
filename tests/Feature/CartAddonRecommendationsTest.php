<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Cart;
use App\Core\Request;
use App\Models\Service;
use App\Controllers\BookingController;

echo "=== RUNNING CART ADD-ONS RECOMMENDATIONS FEATURE TESTS ===\n";

// Test 1: Service::getAddonRecommendations returns curated complementary services
$addons1 = Service::getAddonRecommendations(1, [], 3);
assert(!empty($addons1), "Test 1 Failed: Addons for service ID 1 should not be empty");
assert(count($addons1) <= 3, "Test 1 Failed: Addons should not exceed requested limit of 3");
$hasSofaOrPest = false;
foreach ($addons1 as $a) {
    if (in_array((int)$a['id'], [4, 7, 48, 2], true)) {
        $hasSofaOrPest = true;
        break;
    }
}
assert($hasSofaOrPest, "Test 1 Failed: Addons for Full Home Clean should include Sofa, Pest Control, or Balcony");
echo "Test 1: Smart Complementary Service Add-ons for Full Home Cleaning - PASSED\n";

// Test 2: Exclude IDs already in the cart
$addonsExcluding = Service::getAddonRecommendations(1, [4, 7], 3);
foreach ($addonsExcluding as $a) {
    assert((int)$a['id'] !== 4 && (int)$a['id'] !== 7, "Test 2 Failed: Excluded services (4, 7) must not appear in recommendations");
}
echo "Test 2: Exclude Items Already in Cart from Recommendations - PASSED\n";

// Test 3: Cart aggregate recommendations
$cartAddons = Service::getCartAddonRecommendations([2], 4);
assert(!empty($cartAddons), "Test 3 Failed: Cart addons for Bathroom Cleaning should not be empty");
foreach ($cartAddons as $ca) {
    assert((int)$ca['id'] !== 2, "Test 3 Failed: Current cart item (2) must not appear in cart recommendations");
    assert(!empty($ca['badge']), "Test 3 Failed: Addon item must include a marketing badge");
    assert(!empty($ca['image']), "Test 3 Failed: Addon item must include a resolved image");
}
echo "Test 3: Aggregate Cart Add-ons for Active Cart - PASSED\n";

// Test 4: BookingController::addToCart API response structure
Cart::clear();
$controller = new BookingController();
$req = new Request([], ['service_id' => 1, 'quantity' => 1], [], [], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$res = $controller->addToCart($req);
assert($res->getStatusCode() === 200, "Test 4 Failed: addToCart should return 200");
$data = json_decode($res->getContent(), true);
assert($data['success'] === true, "Test 4 Failed: Response success must be true");
assert(!empty($data['added_service']), "Test 4 Failed: Response must contain added_service");
assert((int)$data['added_service']['id'] === 1, "Test 4 Failed: Added service ID must be 1");
assert(!empty($data['addons']), "Test 4 Failed: Response must contain addons array");
echo "Test 4: /api/cart/add Response Includes added_service & smart addons - PASSED\n";

// Test 5: /api/cart/addons API endpoint
$reqAddons = new Request(['service_id' => 2], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
$resAddons = $controller->apiCartAddons($reqAddons);
assert($resAddons->getStatusCode() === 200, "Test 5 Failed: apiCartAddons should return 200");
$dataAddons = json_decode($resAddons->getContent(), true);
assert($dataAddons['success'] === true, "Test 5 Failed: apiCartAddons response success must be true");
assert(!empty($dataAddons['addons']), "Test 5 Failed: apiCartAddons response must contain addons");
echo "Test 5: /api/cart/addons Endpoint - PASSED\n";

// Test 6: Cart view renders with Frequently Added Add-ons section
$reqCart = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
$resCart = $controller->cart($reqCart);
assert($resCart->getStatusCode() === 200, "Test 6 Failed: cart page should return 200");
$cartHtml = $resCart->getContent();
assert(strpos($cartHtml, 'Frequently Added Add-ons') !== false, "Test 6 Failed: Cart page should contain 'Frequently Added Add-ons' section");
assert(strpos($cartHtml, 'btn-cart-addon-add') !== false, "Test 6 Failed: Cart page should contain 1-click add buttons");
echo "Test 6: Cart Page Renders 'Frequently Added Add-ons' Section - PASSED\n";

Cart::clear();
echo "\n=== ALL 6 CART ADD-ON RECOMMENDATION TESTS PASSED SUCCESSFULLY! ===\n";
