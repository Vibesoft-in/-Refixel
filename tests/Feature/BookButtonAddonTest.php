<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Request;
use App\Core\Cart;
use App\Models\Service;
use App\Controllers\BookingController;

echo "=== RUNNING BOOK BUTTON ADDONS FEATURE TESTS ===\n";

// 1. Verify set_exact=1 on /api/cart/add
Cart::clear();
$service = Service::getActive()[0] ?? null;
assert($service !== null, "Expected at least 1 active service");

$controller = new BookingController();

// Add with set_exact=1
$req1 = new Request([], ['service_id' => $service['id'], 'quantity' => 1, 'set_exact' => 1]);
$res1 = $controller->addToCart($req1);
$data1 = json_decode($res1->getContent(), true);
assert($data1['success'] === true, "Expected success for first add");
assert($data1['cart_count'] === 1, "Expected cart_count 1, got {$data1['cart_count']}");

// Call again with set_exact=1 (simulating clicking Book Now multiple times)
$req2 = new Request([], ['service_id' => $service['id'], 'quantity' => 1, 'set_exact' => 1]);
$res2 = $controller->addToCart($req2);
$data2 = json_decode($res2->getContent(), true);
assert($data2['success'] === true, "Expected success for second add");
assert($data2['cart_count'] === 1, "Expected cart_count to remain 1 with set_exact, got {$data2['cart_count']}");
assert(!empty($data2['addons']), "Expected smart addons recommendations in response");
assert(!empty($data2['added_service']), "Expected added_service details in response");

echo "Test 1: /api/cart/add set_exact=1 and Addon recommendations - PASSED\n";

// 2. Verify views contain data-service-id on Book buttons
$indexView = file_get_contents(__DIR__ . '/../../app/views/customer/services/index.php');
assert(strpos($indexView, 'data-service-id="<?= (int)$svcId ?>"') !== false, "Expected data-service-id on index.php book button");
assert(strpos($indexView, "isBookMode: true") !== false, "Expected isBookMode in index.php book handler");

$catView = file_get_contents(__DIR__ . '/../../app/views/customer/services/category.php');
assert(strpos($catView, 'data-service-id="<?= (int)$svc[\'id\'] ?>"') !== false, "Expected data-service-id on category.php book button");
assert(strpos($catView, "isBookMode: true") !== false, "Expected isBookMode in category.php book handler");

$showView = file_get_contents(__DIR__ . '/../../app/views/customer/services/show.php');
assert(strpos($showView, 'service-book-visit-btn" data-service-id="<?= (int)$service[\'id\'] ?>"') !== false, "Expected data-service-id on show.php desktop book button");
assert(strpos($showView, 'smb-book-btn" data-service-id="<?= (int)$service[\'id\'] ?>"') !== false, "Expected data-service-id on show.php mobile book button");
assert(strpos($showView, "isBookMode: true") !== false, "Expected isBookMode in show.php book handler");

echo "Test 2: View Templates Verification for Book Now Add-on triggers - PASSED\n";

// 3. Verify cart-addon-modal.php supports isBookMode
$modalView = file_get_contents(__DIR__ . '/../../app/views/partials/cart-addon-modal.php');
assert(strpos($modalView, 'id="rfBadgeText"') !== false, "Expected rfBadgeText in modal");
assert(strpos($modalView, 'id="rfStatusPill"') !== false, "Expected rfStatusPill in modal");
assert(strpos($modalView, 'id="rfBtnCheckoutLabel"') !== false, "Expected rfBtnCheckoutLabel in modal");
assert(strpos($modalView, 'isBookMode') !== false, "Expected isBookMode support in modal script");

echo "Test 3: cart-addon-modal.php isBookMode and adaptive labels - PASSED\n";

Cart::clear();
echo "\n=== ALL BOOK BUTTON ADDON TESTS PASSED SUCCESSFULLY! ===\n";
