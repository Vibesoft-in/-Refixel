<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Models\AbandonedCheckout;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Controllers\Admin\EnquiryController;
use App\Controllers\Admin\DropoffController;

echo "=== RUNNING ABANDONED DROPOFFS EXCLUSION TEST ===\n\n";

// 1. Authenticate as Admin
$admin = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' AND status = 'active' LIMIT 1");
if (!$admin) {
    $admin = Database::fetchOne("SELECT * FROM users WHERE role = 'super_admin' AND status = 'active' LIMIT 1");
}
assert($admin !== null, "Admin user must exist");
Auth::login($admin);

// 2. Ensure an active service exists
$service = Service::find(1) ?? Database::fetchOne("SELECT * FROM services LIMIT 1");
assert($service !== null, "A service must exist");

// 3. Create a test booking that simulates a payment drop-off
$bookingNo = 'BK-TEST-DROP-' . bin2hex(random_bytes(3));
$bookingId = Booking::create([
    'booking_no'     => $bookingNo,
    'customer_id'    => null,
    'service_id'     => $service['id'],
    'name'           => 'Dropoff Test Customer',
    'phone'          => '9988776655',
    'email'          => 'dropoff@example.com',
    'address'        => 'Testing Dropoff Lane, Kashipur',
    'pincode'        => '244713',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '10:00 - 12:00 PM',
    'status'         => 'new',
    'priority'       => 'normal',
    'total_amount'   => 899.00,
    'advance_amount' => 49.00,
    'due_amount'     => 850.00,
    'payment_type'   => 'cod',
    'gateway_order_id' => 'order_test_dropoff_123',
]);
assert($bookingId > 0, "Booking must be created");

// 4. Record as an unrecovered drop-off in abandoned_checkouts
$dropoffId = AbandonedCheckout::record([
    'booking_id'   => $bookingId,
    'name'         => 'Dropoff Test Customer',
    'phone'        => '9988776655',
    'email'        => 'dropoff@example.com',
    'service_name' => $service['name'],
    'amount'       => 899.00,
    'step'         => 'payment_modal_dismissed',
    'notes'        => 'Closed ₹49 advance payment modal',
]);
assert($dropoffId > 0, "Dropoff must be recorded");

// 5. Test: EnquiryController index must NOT include this booking
$enqCtrl = new EnquiryController();
$req = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/enquiries']);
$resp = $enqCtrl->index($req);
assert($resp->getStatusCode() === 200, "Enquiries index must return 200");
$html = $resp->getContent();

assert(!str_contains($html, $bookingNo), "Unrecovered drop-off {$bookingNo} must NOT appear in Enquiries HTML!");
echo "[PASS] Test 1: Unrecovered drop-off booking is NOT visible in /admin/enquiries\n";

// 6. Test: Direct access to /admin/enquiries/{id} redirects to /admin/dropoffs
$showResp = $enqCtrl->show(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/admin/enquiries/{$bookingId}"]), (string)$bookingId);
assert($showResp->getStatusCode() === 302, "Expected redirect from /admin/enquiries/{id} for unrecovered drop-off");
assert(str_contains($showResp->getHeader('Location') ?? '', '/admin/dropoffs'), "Redirect target must be /admin/dropoffs");
echo "[PASS] Test 2: Direct view of unrecovered dropoff enquiry safely redirects to /admin/dropoffs\n";

// 7. Test: Convert to COD using DropoffController
$dropCtrl = new DropoffController();
$convertReq = new Request([], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/dropoffs/{$dropoffId}/convert-cod"]);
$convertResp = $dropCtrl->convertToCod($convertReq, (string)$dropoffId);
assert($convertResp->getStatusCode() === 302, "Expected 302 after convertToCod");

// 8. Test: After conversion to COD (recovery), it SHOULD now appear in /admin/enquiries
$respRecovered = $enqCtrl->index($req);
$htmlRecovered = $respRecovered->getContent();
assert(str_contains($htmlRecovered, $bookingNo), "Recovered booking {$bookingNo} MUST now appear in Enquiries!");
echo "[PASS] Test 3: Recovered booking successfully becomes available in /admin/enquiries\n";

// Clean up
Database::query("DELETE FROM abandoned_checkouts WHERE id = :id", ['id' => $dropoffId]);
Database::query("DELETE FROM bookings WHERE id = :id", ['id' => $bookingId]);

echo "\n🎉 ALL ABANDONED DROPOFFS EXCLUSION TESTS PASSED PERFECTLY!\n";
