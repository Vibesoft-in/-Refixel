<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\Service;

echo "=== RUNNING CUSTOMER BOOKING ENGINE & CART TESTS ===\n";

$ctrl = new BookingController();

// Clear any pre-existing cart state
Cart::clear();

// Test 1: Empty cart state
$cart1 = Cart::getDetails();
assert($cart1['count'] === 0, "Expected empty cart count = 0");
assert($cart1['is_empty'] === true, "Expected is_empty = true");
$reqCart = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/cart']);
$respCart = $ctrl->cart($reqCart);
assert($respCart->getStatusCode() === 200, "Expected 200 for /cart");
assert(str_contains($respCart->getContent(), 'Your cart is empty'), "Expected empty cart message");
echo "Test 1: Empty cart rendering - PASSED\n";

// Test 2: Add service to cart (server-side pricing lookup)
$service = Service::find(1); // Full Home Cleaning, 2499.00
assert($service !== null, "Service 1 must exist");
$addResult = Cart::add(1, 1);
assert($addResult['success'] === true, "Cart::add must succeed");
$cart2 = Cart::getDetails();
assert($cart2['count'] === 1, "Expected cart count = 1");
assert((float)$cart2['total'] === 2499.00, "Expected total = 2499.00, got {$cart2['total']}");
assert($cart2['items'][0]['service_id'] === 1, "Expected service_id = 1");
echo "Test 2: Server-side price calculation on Cart Add - PASSED\n";

// Test 3: Stepper quantity updates (plus and minus)
Cart::update(1, 2);
$cart3 = Cart::getDetails();
assert($cart3['count'] === 2, "Expected cart count = 2");
assert((float)$cart3['total'] === 4998.00, "Expected total = 4998.00");

Cart::update(1, 1);
$cart4 = Cart::getDetails();
assert($cart4['count'] === 1, "Expected cart count = 1 after decrement");
assert((float)$cart4['total'] === 2499.00, "Expected total = 2499.00");
echo "Test 3: Quantity increment/decrement recalculation - PASSED\n";

// Test 4: API Cart endpoint returns valid JSON
$apiReq = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/cart']);
$apiResp = $ctrl->apiCart($apiReq);
assert($apiResp->getStatusCode() === 200, "Expected 200 for /api/cart");
$apiData = json_decode($apiResp->getContent(), true);
assert($apiData['cart_count'] === 1, "Expected JSON cart_count = 1");
assert($apiData['cart_total'] == 2499, "Expected JSON cart_total = 2499");
echo "Test 4: /api/cart JSON endpoint - PASSED\n";

// Test 5: Populated cart view rendering
$respCartPopulated = $ctrl->cart($reqCart);
assert($respCartPopulated->getStatusCode() === 200, "Expected 200 for populated cart");
$popHtml = $respCartPopulated->getContent();
assert(str_contains($popHtml, 'Full Home Cleaning'), "Expected service name in cart view");
assert(str_contains($popHtml, 'Price Summary'), "Expected price summary");
assert(str_contains($popHtml, '2,499'), "Expected formatted price in cart view");
echo "Test 5: Populated cart view rendering - PASSED\n";

// Test 6: Booking submission with server-side validation & status=new
$token = Csrf::token();
Auth::login(['id' => 3, 'name' => 'Ananya Verma', 'email' => 'customer@primodomus.com', 'role' => 'customer']);

$postData = [
    '_csrf'          => $token,
    'from_cart'      => '1',
    'service_id'     => '1',
    'name'           => 'Ananya Verma',
    'phone'          => '9876543212',
    'email'          => 'customer@primodomus.com',
    'address'        => 'Station Road, Kashipur',
    'city'           => 'Kashipur',
    'pincode'        => '244713',
    'preferred_date' => date('Y-m-d', strtotime('+1 day')),
    'preferred_time' => '11:00 AM - 01:00 PM',
    'priority'       => 'high',
    'issue_details'  => 'Kitchen exhaust filter heavy grease build-up.',
];

$bookReq = new Request([], $postData, ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/book']);
$bookResp = $ctrl->submit($bookReq);

assert($bookResp->getStatusCode() === 302, "Expected 302 redirect after booking");

// Extract generated booking_no from redirect URL
$location = '';
// Query the latest booking from database
$latestBooking = \App\Core\Database::fetchOne("SELECT * FROM bookings ORDER BY id DESC LIMIT 1");
assert($latestBooking !== null, "Booking must have been created in database");
assert($latestBooking['status'] === 'new', "New booking status must be 'new'");
assert($latestBooking['priority'] === 'high', "Expected priority 'high'");
assert($latestBooking['customer_id'] == 3, "Booking must be associated with customer user 3");
assert(str_starts_with($latestBooking['booking_no'], 'BK-'), "Booking number must follow BK- prefix");
echo "Test 6: Booking creation (status=new, unique BK- number, customer association) - PASSED\n";

// Test 7: Cart cleared automatically after cart checkout
$cartAfterCheckout = Cart::getDetails();
assert($cartAfterCheckout['is_empty'] === true, "Cart must be emptied after successful checkout");
echo "Test 7: Cart auto-cleared on booking completion - PASSED\n";

// Test 8: Booking attachment metadata storage
$attachmentId = BookingAttachment::create([
    'booking_id' => $latestBooking['id'],
    'file_path'  => 'uploads/issues/test_grease_stain.jpg',
    'mime'       => 'image/jpeg',
    'size'       => 1048576, // 1 MB
]);
assert($attachmentId > 0, "Attachment record must be created");
$attachments = BookingAttachment::getByBooking((int)$latestBooking['id']);
assert(count($attachments) >= 1, "Booking attachments must be retrievable");
assert($attachments[0]['mime'] === 'image/jpeg', "Expected image/jpeg mime");
echo "Test 8: Customer issue photo attachment storage - PASSED\n";

// Test 9: Success screen rendering
$successReq = new Request(['booking_no' => $latestBooking['booking_no']], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/book-success']);
$successResp = $ctrl->success($successReq);
assert($successResp->getStatusCode() === 200, "Expected 200 for /book-success");
assert(str_contains($successResp->getContent(), $latestBooking['booking_no']), "Success page must display booking reference");
echo "Test 9: Booking confirmation success screen - PASSED\n";


echo "=== ALL CUSTOMER BOOKING ENGINE & CART TESTS PASSED! ===\n";
