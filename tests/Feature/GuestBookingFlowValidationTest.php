<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;

echo "====================================================\n";
echo "TEST SUITE: Guest Booking Flow & No Auto-Login Test\n";
echo "====================================================\n\n";

// Ensure clean unauthenticated session
Auth::logout();
Cart::clear();
assert(!Auth::check(), "Initial state must be unauthenticated");

// Find an active service for testing
$service = Service::getActive()[0] ?? null;
assert($service !== null, "At least one active service must exist");
$serviceId = (int)$service['id'];

// ----------------------------------------------------
// TEST 1: Category page enforces login redirect for guest clicks
// ----------------------------------------------------
echo "[TEST 1] Verifying Category Page login redirects for unauthenticated guests...\n";
$categoryHtml = file_get_contents(ROOT_PATH . '/app/views/customer/services/category.php');
assert(str_contains($categoryHtml, '$bookUrl = $isCustomerLoggedIn ?'), "category.php must redirect guests to login for booking");
assert(str_contains($categoryHtml, '$svcUrl = $isCustomerLoggedIn ?'), "category.php must redirect guests to login for checklists");
assert(str_contains($categoryHtml, 'var isLoggedIn = bookBtn.getAttribute'), "category.php JS must check guest Book Now login state");
echo "  ✓ PASS: category.php enforces login redirection for unauthenticated guest users\n\n";

// ----------------------------------------------------
// TEST 2: Service Detail Page has browser login redirect script for unauthenticated users
// ----------------------------------------------------
echo "[TEST 2] Verifying Service Detail Page contains login redirect script for guests...\n";
$showHtml = file_get_contents(ROOT_PATH . '/app/views/customer/services/show.php');
assert(str_contains($showHtml, "window.location.replace('<?= \App\Core\View::url('/login?redirect=') ?>'"), "show.php must contain login redirect for unauthenticated browser visitors");
echo "  ✓ PASS: show.php redirects unauthenticated visitors to login\n\n";

// ----------------------------------------------------
// TEST 3: Services Catalogue enforces login redirect for guest clicks
// ----------------------------------------------------
echo "[TEST 3] Verifying Catalogue page login redirects for unauthenticated guests...\n";
$indexHtml = file_get_contents(ROOT_PATH . '/app/views/customer/services/index.php');
assert(str_contains($indexHtml, '$bookUrl = $isCustomerLoggedIn ?'), "index.php must redirect guests to login for booking");
assert(str_contains($indexHtml, "var isLoggedIn = bookBtn.getAttribute('data-is-logged-in') === '1'"), "index.php JS must check guest login state on book");
echo "  ✓ PASS: services/index.php enforces login redirects for unauthenticated guests\n\n";

// ----------------------------------------------------
// TEST 4: Homepage category cards and marquee links enforce login redirect for guests
// ----------------------------------------------------
echo "[TEST 4] Verifying Homepage category cards and marquee links redirect unauthenticated guests...\n";
$homeHtml = file_get_contents(ROOT_PATH . '/app/views/customer/home.php');
assert(str_contains($homeHtml, '$cardHref = $isCustomerLoggedIn ?'), "home.php must redirect category cards to /login?redirect=");
assert(str_contains($homeHtml, '$mHref = $isCustomerLoggedIn ?'), "home.php must redirect marquee links to /login?redirect=");
assert(str_contains($homeHtml, 'window.location.href = baseUrl + \'/login?redirect=\''), "home.php JS must intercept guest clicks to login");
echo "  ✓ PASS: home.php category cards and marquee links enforce login redirect for guest users\n\n";

// ----------------------------------------------------
// TEST 5: Direct /book form submission by guest creates booking, does NOT auto-login, redirects to /book-success?token=
// ----------------------------------------------------
echo "[TEST 5] Testing direct /book submission by unauthenticated guest...\n";
Auth::logout();
$guestPhone = '98765' . random_int(10000, 99999);
$guestName = 'Guest Tester';
$prefDate = date('Y-m-d', strtotime('+2 days'));
$prefTime = '11:00 AM - 01:00 PM';

$reqData = [
    '_csrf'          => Csrf::token(),
    'service_id'     => $serviceId,
    'name'           => $guestName,
    'phone'          => $guestPhone,
    'address'        => '123 Test Street, Ward 4',
    'city'           => 'Kashipur',
    'pincode'        => '244713',
    'preferred_date' => $prefDate,
    'preferred_time' => $prefTime,
    'payment_method' => 'cod',
    'is_ajax'        => '1',
];

$request = new Request([], $reqData, [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$controller = new BookingController();
$response = $controller->submit($request);

assert($response->getStatusCode() === 200, "Expected HTTP 200 JSON response");
$resData = json_decode($response->getContent(), true);
assert(!empty($resData['success']), "Expected booking success");
assert(!empty($resData['guest_token']), "Expected non-empty guest_token in response");
assert(!empty($resData['redirect_url']), "Expected redirect_url in response");
assert(str_contains($resData['redirect_url'], '/book-success?token='), "redirect_url must point to /book-success?token=");
assert(!Auth::check(), "CRITICAL: Guest user must NOT be auto-logged in!");

// Verify DB record has guest_token
$createdBooking = Booking::find((int)$resData['booking_id']);
assert($createdBooking !== null, "Booking must be persisted in database");
assert(!empty($createdBooking['guest_token']), "Booking record must store guest_token");
assert(hash_equals($createdBooking['guest_token'], $resData['guest_token']), "Returned token must match DB token");
echo "  ✓ PASS: Direct guest booking succeeds with guest_token and strictly NO auto-login\n\n";

// ----------------------------------------------------
// TEST 6: Cart checkout submission by guest
// ----------------------------------------------------
echo "[TEST 6] Testing cart checkout submission by unauthenticated guest...\n";
Auth::logout();
Cart::clear();
Cart::add($serviceId, 1);
assert(count(Cart::getRaw()) === 1, "Cart should have 1 item");

$cartGuestPhone = '98764' . random_int(10000, 99999);
$cartReqData = [
    '_csrf'          => Csrf::token(),
    'service_id'     => $serviceId,
    'from_cart'      => '1',
    'name'           => 'Cart Guest User',
    'phone'          => $cartGuestPhone,
    'address'        => '456 Cart Lane',
    'city'           => 'Kashipur',
    'pincode'        => '244713',
    'preferred_date' => $prefDate,
    'preferred_time' => $prefTime,
    'payment_method' => 'cod',
    'is_ajax'        => '1',
];

$cartRequest = new Request([], $cartReqData, [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$cartResponse = $controller->submit($cartRequest);
$cartResData = json_decode($cartResponse->getContent(), true);
assert(!empty($cartResData['success']), "Cart booking must succeed");
assert(!empty($cartResData['guest_token']), "Cart booking must return guest_token");
assert(str_contains($cartResData['redirect_url'], '/book-success?token='), "Cart redirect_url must be /book-success?token=");
assert(!Auth::check(), "CRITICAL: Cart guest user must NOT be auto-logged in!");
echo "  ✓ PASS: Cart checkout for guest succeeds without login and strictly NO auto-login\n\n";

// ----------------------------------------------------
// TEST 7: book-success.php rendering for guest
// ----------------------------------------------------
echo "[TEST 7] Verifying book-success.php actions for guest...\n";
Auth::logout();
$successHtml = file_get_contents(ROOT_PATH . '/app/views/customer/book-success.php');
assert(str_contains($successHtml, 'View Booking Details'), "book-success.php must render 'View Booking Details' for guest");
assert(str_contains($successHtml, 'Auth::check()'), "book-success.php must check Auth::check() before displaying Open Dashboard");
echo "  ✓ PASS: book-success.php provides guest token link instead of dead Open Dashboard link\n\n";

echo "====================================================\n";
echo "ALL GUEST BOOKING FLOW TESTS PASSED SUCCESSFULLY! ✓\n";
echo "====================================================\n";
