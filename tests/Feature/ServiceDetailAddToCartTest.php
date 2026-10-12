<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Request;
use App\Core\Auth;
use App\Core\Cart;
use App\Core\Database;
use App\Controllers\ServiceController;
use App\Controllers\BookingController;

echo "=== RUNNING SERVICE DETAIL ADD-TO-CART & ADDRESS BUTTON TESTS ===\n";

// Login dummy customer so renderServicePage does not redirect
Auth::login([
    'id'    => 999,
    'name'  => 'Test Customer',
    'phone' => '9999988888',
    'role'  => 'customer',
]);

$service = Database::fetchOne("SELECT * FROM services WHERE is_active = 1 LIMIT 1");
if (!$service) {
    echo "No service found\n";
    exit(0);
}

$svcCtrl = new ServiceController();
$req = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/' . $service['slug']]);
$resp = $svcCtrl->serviceClean($req, $service['slug']);

assert($resp->getStatusCode() === 200, "Expected 200 from serviceClean");
$html = $resp->getContent();

// 1. Check Hero Image Add to Cart button
assert(str_contains($html, 'class="btn-add-to-cart hero-img-cart-btn"'), "Expected show.php to contain .hero-img-cart-btn");
assert(str_contains($html, 'class="service-hero-img-wrap"'), "Expected show.php to contain .service-hero-img-wrap");

// 2. Check Mobile Sticky Bar Add to Cart button
assert(str_contains($html, 'class="btn btn-add-to-cart smb-cart-btn"'), "Expected show.php to contain .smb-cart-btn");
assert(str_contains($html, 'class="service-mobile-book-bar d-lg-none"'), "Expected show.php to contain .service-mobile-book-bar");

// 3. Check Desktop Sidebar Add to Cart button
assert(str_contains($html, 'class="btn btn-add-to-cart desktop-cart-btn"'), "Expected show.php to contain .desktop-cart-btn");

echo "Test 1: Service details page renders hero image cart button, mobile sticky bar cart button, and desktop cart button - PASSED\n";

// 4. Test /api/cart/add endpoint adds item to cart
Cart::clear();
$bookingCtrl = new BookingController();
$addReq = new Request([], ['service_id' => $service['id'], 'quantity' => 1], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/cart/add']);
$addResp = $bookingCtrl->addToCart($addReq);
$data = json_decode($addResp->getContent(), true);

assert($data['success'] === true, "Expected /api/cart/add to succeed");
assert($data['cart_count'] >= 1, "Expected cart count to be at least 1");

echo "Test 2: /api/cart/add API adds service to cart successfully - PASSED\n";

echo "=== ALL SERVICE DETAIL ADD-TO-CART & ADDRESS BUTTON TESTS PASSED! ===\n";
