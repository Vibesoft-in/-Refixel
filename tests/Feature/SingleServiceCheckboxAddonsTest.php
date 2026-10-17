<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Controllers\BookingController;
use App\Controllers\ServiceController;
use App\Core\Cart;
use App\Core\Request;
use App\Models\Service;
use App\Models\ServiceAddon;

echo "=== Running SingleServiceCheckboxAddonsTest ===\n";

// 1. Verify balcony service detail page renders related checkbox add-ons
$balconySvc = Service::findBy('slug', 'balcony-deep-pressure-wash');
assert(!empty($balconySvc), "Balcony service should exist");

$req = new Request([], [], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/balcony-deep-pressure-wash',
]);

$controller = new ServiceController();
$resp = $controller->serviceClean($req, 'balcony-deep-pressure-wash');
$html = $resp->getContent();

assert(strpos($html, 'id="serviceAddonsWidget"') !== false, "HTML must contain #serviceAddonsWidget");
assert(strpos($html, 'Frequently Added Together') !== false, "HTML must contain 'Frequently Added Together' title");
assert(strpos($html, 'class="service-addon-chk"') !== false, "HTML must contain checkboxes with class service-addon-chk");
assert(strpos($html, 'id="serviceAddonTotalStrip"') !== false, "HTML must contain dynamic total strip");

// Check that Balcony related add-ons are present
assert(strpos($html, 'Sofa &amp; Upholstery Deep Cleaning') !== false || strpos($html, 'Sofa & Upholstery Deep Cleaning') !== false, "Balcony page must show Sofa & Upholstery add-on");
assert(strpos($html, 'Cockroach &amp; Ant Pest Control') !== false || strpos($html, 'Cockroach & Ant Pest Control') !== false, "Balcony page must show Pest Control add-on");
assert(strpos($html, 'Professional Bathroom Cleaning') !== false, "Balcony page must show Bathroom cleaning add-on");

echo "Test 1: Balcony detail page renders checkbox add-ons widget with relevant pairings - PASSED\n";

// 2. Verify Kitchen service detail page renders kitchen-specific add-ons
$reqKitchen = new Request([], [], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/kitchen-deep-cleaning',
]);
$respKitchen = $controller->serviceClean($reqKitchen, 'kitchen-deep-cleaning');
$htmlKitchen = $respKitchen->getContent();

assert(strpos($htmlKitchen, 'id="serviceAddonsWidget"') !== false, "Kitchen page must contain #serviceAddonsWidget");
assert(strpos($htmlKitchen, 'Chimney') !== false, "Kitchen page must contain Chimney addon");
assert(strpos($htmlKitchen, 'Microwave') !== false, "Kitchen page must contain Microwave addon");
assert(strpos($htmlKitchen, 'Refrigerator') !== false, "Kitchen page must contain Refrigerator addon");

echo "Test 2: Kitchen detail page renders kitchen-specific add-ons - PASSED\n";

// 3. Verify Bathroom service detail page renders bathroom-specific add-ons
$reqBath = new Request([], [], [], [], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/bathroom-deep-cleaning',
]);
$respBath = $controller->serviceClean($reqBath, 'bathroom-deep-cleaning');
$htmlBath = $respBath->getContent();

assert(strpos($htmlBath, 'id="serviceAddonsWidget"') !== false, "Bathroom page must contain #serviceAddonsWidget");
assert(strpos($htmlBath, 'Bathroom Tiles Hard Water') !== false, "Bathroom page must contain Tiles Stain Removal addon");
assert(strpos($htmlBath, 'Tap &amp; Shower Head') !== false || strpos($htmlBath, 'Tap & Shower Head') !== false, "Bathroom page must contain Tap & Shower addon");

echo "Test 3: Bathroom detail page renders bathroom-specific add-ons - PASSED\n";

// 4. Test cart bundling: /api/cart/add with service_id + addon_ids
Cart::clear();
$bookingCtrl = new BookingController();

$addReq = new Request([], [
    'service_id' => (int)$balconySvc['id'],
    'quantity'   => 1,
    'addon_ids'  => '4,7', // Sofa Cleaning (ID 4) + Pest Control (ID 7)
], [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/cart/add',
]);

$addResp = $bookingCtrl->addToCart($addReq);
$data = json_decode($addResp->getContent(), true);

assert($data['success'] === true, "Add to cart with addon_ids must succeed");
assert($data['cart_count'] >= 3, "Cart must have at least 3 distinct items (base service + 2 add-ons), got: " . $data['cart_count']);

$cartRaw = Cart::getRaw();
assert(isset($cartRaw[$balconySvc['id']]), "Base balcony service must be in cart");
assert(isset($cartRaw[4]), "Addon ID 4 (Sofa Cleaning) must be in cart");
assert(isset($cartRaw[7]), "Addon ID 7 (Pest Control) must be in cart");

echo "Test 4: /api/cart/add bundles main service + checked add-on IDs into cart atomically - PASSED\n";

echo "=== All SingleServiceCheckboxAddons Tests PASSED ===\n";
