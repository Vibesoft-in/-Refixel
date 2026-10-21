<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;
use App\Controllers\ServiceController;
use App\Controllers\BookingController;
use App\Controllers\Admin\ServiceController as AdminServiceController;

echo "=== RUNNING SERVICE COMING SOON TOGGLE & DISPLAY FEATURE TEST ===\n\n";

// 1. Ensure schema includes is_coming_soon
Service::ensureServiceSchema();
$cols = Database::fetchAll("SHOW COLUMNS FROM services");
$fields = array_column($cols, 'Field');
assert(in_array('is_coming_soon', $fields, true), "Expected 'is_coming_soon' column to exist on services table");
echo "[PASS] Test 1: 'is_coming_soon' column verified on services table.\n\n";

// 2. Find a test service
$service = Database::fetchOne("SELECT * FROM services WHERE is_active = 1 LIMIT 1");
assert(!empty($service), "Expected at least one active service in DB");
$serviceId = (int)$service['id'];
$slug = $service['slug'];

// Reset to is_coming_soon = 0 initially
Database::execute("UPDATE services SET is_coming_soon = 0 WHERE id = :id", ['id' => $serviceId]);

Auth::login([
    'id'    => 1,
    'name'  => 'Admin User',
    'phone' => '9999900000',
    'role'  => 'admin',
]);

// 3. Test Admin Toggle Endpoint: Turn ON Coming Soon
echo "[RUN] Test 2: Admin toggle endpoint turning ON Coming Soon\n";
$adminCtrl = new AdminServiceController();
$toggleReq = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$toggleResp = $adminCtrl->toggleComingSoon($toggleReq, (string)$serviceId);
$toggleData = json_decode($toggleResp->getContent(), true);

assert($toggleData['success'] === true, "Expected toggle to succeed");
assert($toggleData['is_coming_soon'] === 1, "Expected is_coming_soon to be 1 after first toggle");

$updated = Service::find($serviceId);
assert(!empty($updated['is_coming_soon']), "DB value should be 1 for is_coming_soon");
echo "[PASS] Test 2: Admin toggle successfully turned ON Coming Soon for service #{$serviceId}.\n\n";

// 4. Test Customer View show.php with Coming Soon turned ON
echo "[RUN] Test 3: Customer service details page renders Coming Soon UI\n";
$svcCtrl = new ServiceController();
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/' . $slug]);
$resp = $svcCtrl->serviceClean($req, $slug);
assert($resp->getStatusCode() === 200, "Expected 200 OK from serviceClean");
$html = $resp->getContent();

assert(str_contains($html, 'hero-coming-soon-badge'), "Expected show.php to contain .hero-coming-soon-badge when Coming Soon is ON");
assert(str_contains($html, 'coming-soon-banner'), "Expected show.php to contain .coming-soon-banner when Coming Soon is ON");
assert(str_contains($html, 'Coming Soon • Bookings Opening Soon'), "Expected show.php to contain 'Coming Soon • Bookings Opening Soon'");
assert(str_contains($html, 'Enquire Launch Date via WhatsApp'), "Expected WhatsApp button to enquire launch date");
echo "[PASS] Test 3: Customer details page displays all Coming Soon badges & banners.\n\n";

// 5. Test Cart & Booking API Rejection when Coming Soon is ON
echo "[RUN] Test 4: /api/cart/add and Cart::add rejection\n";
Cart::clear();
$cartRes = Cart::add($serviceId, 1);
assert($cartRes['success'] === false, "Expected Cart::add to fail for coming soon service");
assert(!empty($cartRes['is_coming_soon']), "Expected Cart::add response to indicate is_coming_soon");

$bookingCtrl = new BookingController();
$addReq = new Request([], ['service_id' => $serviceId, 'quantity' => 1], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/cart/add', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$addResp = $bookingCtrl->addToCart($addReq);
assert($addResp->getStatusCode() === 400, "Expected 400 Bad Request from /api/cart/add for coming soon service");
$addJson = json_decode($addResp->getContent(), true);
assert($addJson['success'] === false, "Expected success: false in /api/cart/add response");
assert(!empty($addJson['is_coming_soon']), "Expected is_coming_soon: true in API response");
assert(str_contains($addJson['error'], 'coming soon'), "Expected error message to mention 'coming soon'");
echo "[PASS] Test 4: /api/cart/add properly blocked and returned informative Coming Soon error.\n\n";

// 6. Test Direct Booking submission rejection
echo "[RUN] Test 5: Booking submission blocked for Coming Soon service\n";
$submitReq = new Request([], [
    'service_id'     => $serviceId,
    'name'           => 'John Doe',
    'phone'          => '9876543210',
    'address'        => '123 Main Street, Kashipur',
    'preferred_date' => date('Y-m-d', strtotime('+2 days')),
    'preferred_time' => '10:00 AM - 12:00 PM',
    'is_ajax'        => '1',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/book', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$submitResp = $bookingCtrl->submit($submitReq);
assert($submitResp->getStatusCode() === 400, "Expected 400 Bad Request from submit()");
$submitJson = json_decode($submitResp->getContent(), true);
assert($submitJson['success'] === false, "Expected submit to be rejected");
assert(!empty($submitJson['is_coming_soon']), "Expected is_coming_soon in submit response");
echo "[PASS] Test 5: Direct booking submission blocked with 400 Bad Request.\n\n";

// 7. Test Admin Toggle Endpoint: Turn OFF Coming Soon (Make Live)
echo "[RUN] Test 6: Admin toggle endpoint turning OFF Coming Soon\n";
$toggleResp2 = $adminCtrl->toggleComingSoon($toggleReq, (string)$serviceId);
$toggleData2 = json_decode($toggleResp2->getContent(), true);
assert($toggleData2['success'] === true, "Expected toggle to succeed");
assert($toggleData2['is_coming_soon'] === 0, "Expected is_coming_soon to be 0 after second toggle");

$updated2 = Service::find($serviceId);
assert(empty($updated2['is_coming_soon']), "DB value should be 0 for is_coming_soon");
echo "[PASS] Test 6: Admin toggle successfully turned OFF Coming Soon (service is Live).\n\n";

// 8. Test Customer View show.php with Coming Soon turned OFF (Live state)
echo "[RUN] Test 7: Customer service details page renders normal Add to Cart & Book Now\n";
$resp2 = $svcCtrl->serviceClean($req, $slug);
$html2 = $resp2->getContent();

assert(str_contains($html2, 'class="btn-add-to-cart hero-img-cart-btn"'), "Expected normal hero cart button when live");
assert(str_contains($html2, 'class="btn btn-add-to-cart desktop-cart-btn"'), "Expected normal desktop cart button when live");
assert(str_contains($html2, 'class="btn btn-add-to-cart smb-cart-btn"'), "Expected normal mobile cart button when live");
assert(!str_contains($html2, 'hero-coming-soon-badge'), "Should not contain hero-coming-soon-badge when live");

// Adding to cart should succeed now
$addResp2 = $bookingCtrl->addToCart($addReq);
assert($addResp2->getStatusCode() === 200, "Expected 200 OK from /api/cart/add when live");
$addJson2 = json_decode($addResp2->getContent(), true);
assert($addJson2['success'] === true, "Expected success: true in /api/cart/add response when live");
echo "[PASS] Test 7: Normal booking and Add to Cart flow restored when Coming Soon is OFF.\n\n";

// 9. Verify Admin View renders Coming Soon column & toggle buttons
echo "[RUN] Test 8: Admin Services Catalog index page renders Coming Soon column & buttons\n";
$adminIndexReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/services']);
$adminIndexResp = $adminCtrl->index($adminIndexReq);
assert($adminIndexResp->getStatusCode() === 200, "Expected 200 OK from /admin/services");
$adminHtml = $adminIndexResp->getContent();

assert(str_contains($adminHtml, '<th class="py-3 text-dark text-center">Coming Soon</th>'), "Expected Coming Soon table header in admin view");
assert(str_contains($adminHtml, 'toggle-coming-soon'), "Expected toggle-coming-soon action URL in admin view");
assert(str_contains($adminHtml, 'edit_svc_is_coming_soon'), "Expected edit modal to have edit_svc_is_coming_soon checkbox");
assert(str_contains($adminHtml, 'new_is_coming_soon'), "Expected add modal to have new_is_coming_soon checkbox");
echo "[PASS] Test 8: Admin services catalog renders Coming Soon table column, toggle buttons, and modal checkboxes.\n\n";

echo "=== ALL SERVICE COMING SOON FEATURE TESTS PASSED SUCCESSFULLY! ===\n";
