<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Database;
use App\Core\Request;
use App\Controllers\AccountController;
use App\Controllers\BookingController;
use App\Models\User;

echo "=== RUNNING CUSTOMER ADDRESS PERSISTENCE & AUTO-FILL TESTS ===\n";

// Setup test customer user
$testPhone = '9999988888';
$testEmail = 'addresstest@refixel.com';
Database::execute("DELETE FROM users WHERE phone = ?", [$testPhone]);

$userId = (int)Database::execute(
    "INSERT INTO users (name, phone, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')",
    ['AutoFill Test User', $testPhone, $testEmail, password_hash('Secret123!', PASSWORD_BCRYPT)],
    true
);

$user = User::find($userId);
Auth::login($user);

// 1. Test apiSaveAddress endpoint
$accountCtrl = new AccountController();
$saveReq = new Request([], [
    'house_no'     => 'Flat 303, Lotus Tower',
    'street'       => 'Station Road, Model Town',
    'city'         => 'Kashipur',
    'state'        => 'Uttarakhand',
    'pincode'      => '244713',
    'address'      => 'Near Civil Hospital, Station Road',
    'address_type' => 'Office',
], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/customer/save-address']);

$saveResp = $accountCtrl->apiSaveAddress($saveReq);
assert($saveResp->getStatusCode() === 200, "Expected status 200 from apiSaveAddress");
$saveData = json_decode($saveResp->getContent(), true);
assert($saveData['success'] === true, "Expected success=true from apiSaveAddress");
assert($saveData['profile']['house_no'] === 'Flat 303, Lotus Tower', "Expected saved house_no");
assert($saveData['profile']['address_type'] === 'Office', "Expected saved address_type");
echo "Test 1: apiSaveAddress saves full address to customer profile - PASSED\n";

// 2. Test apiGetProfile returns saved profile
$getReq = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/customer/profile']);
$getResp = $accountCtrl->apiGetProfile($getReq);
assert($getResp->getStatusCode() === 200, "Expected status 200 from apiGetProfile");
$getData = json_decode($getResp->getContent(), true);
assert($getData['authenticated'] === true, "Expected authenticated=true");
assert($getData['profile']['street'] === 'Station Road, Model Town', "Expected profile street matched");
assert($getData['profile']['pincode'] === '244713', "Expected profile pincode matched");
echo "Test 2: apiGetProfile retrieves saved address and profile - PASSED\n";

// 3. Test /book renders with prefilled customer details
$bookingCtrl = new BookingController();
$bookReq = new Request([], ['service_id' => 1], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/book?service_id=1']);
$bookResp = $bookingCtrl->showForm($bookReq);
assert($bookResp->getStatusCode() === 200, "Expected 200 from /book");
$bookHtml = $bookResp->getContent();
assert(str_contains($bookHtml, 'Flat 303, Lotus Tower'), "Expected /book to contain pre-filled house_no");
assert(str_contains($bookHtml, 'Station Road, Model Town'), "Expected /book to contain pre-filled street");
assert(str_contains($bookHtml, '244713'), "Expected /book to contain pre-filled pincode");
assert(str_contains($bookHtml, 'Near Civil Hospital, Station Road'), "Expected /book to contain pre-filled address landmark");
assert(str_contains($bookHtml, 'value="Office" checked'), "Expected /book to check saved Office radio");
assert(str_contains($bookHtml, 'AutoFill Test User'), "Expected /book to contain user name");
assert(!str_contains($bookHtml, 'id="btnAddNewLocation"'), "Expected /book to NOT contain removed btnAddNewLocation button");
assert(str_contains($bookHtml, 'id="savedAddressSummaryBox"'), "Expected /book to contain savedAddressSummaryBox");
assert(str_contains($bookHtml, 'id="btnToggleAddressDetails"'), "Expected /book to contain btnToggleAddressDetails dropdown button");
assert(str_contains($bookHtml, 'id="btnSaveAddress"'), "Expected /book to contain btnSaveAddress button");
assert(str_contains($bookHtml, 'id="btnOpenAddressPicker"'), "Expected /book to contain btnOpenAddressPicker button");
assert(str_contains($bookHtml, 'id="savedAddressesModal"'), "Expected /book to contain savedAddressesModal modal");
echo "Test 3: Booking form HTML renders with all pre-filled address & contact fields + Choose Address button & modal - PASSED\n";

// 4. Test /cart renders with prefilled customer details
Cart::clear();
Cart::add(1, 1);
$cartReq = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/cart']);
$cartResp = $bookingCtrl->cart($cartReq);
assert($cartResp->getStatusCode() === 200, "Expected 200 from /cart");
$cartHtml = $cartResp->getContent();
assert(str_contains($cartHtml, 'Flat 303, Lotus Tower'), "Expected /cart to contain pre-filled house_no");
assert(str_contains($cartHtml, 'Station Road, Model Town'), "Expected /cart to contain pre-filled street");
assert(str_contains($cartHtml, '244713'), "Expected /cart to contain pre-filled pincode");
assert(str_contains($cartHtml, 'Near Civil Hospital, Station Road'), "Expected /cart to contain pre-filled address landmark");
assert(str_contains($cartHtml, 'value="Office" checked'), "Expected /cart to check saved Office radio");
assert(str_contains($cartHtml, 'AutoFill Test User'), "Expected /cart to contain user name");
assert(!str_contains($cartHtml, 'id="cartBtnAddNewLocation"'), "Expected /cart to NOT contain removed cartBtnAddNewLocation button");
assert(str_contains($cartHtml, 'id="cartSavedAddressSummaryBox"'), "Expected /cart to contain cartSavedAddressSummaryBox");
assert(str_contains($cartHtml, 'id="cartBtnToggleAddressDetails"'), "Expected /cart to contain cartBtnToggleAddressDetails dropdown button");
assert(str_contains($cartHtml, 'id="cartBtnSaveAddress"'), "Expected /cart to contain cartBtnSaveAddress button");
assert(str_contains($cartHtml, 'id="cartBtnOpenAddressPicker"'), "Expected /cart to contain cartBtnOpenAddressPicker button");
assert(str_contains($cartHtml, 'id="cartSavedAddressesModal"'), "Expected /cart to contain cartSavedAddressesModal modal");
echo "Test 4: Cart checkout form HTML renders with all pre-filled address & contact fields + Choose Address button & modal - PASSED\n";

// 5. Test apiSaveAddress works for guest session
Auth::logout();
$guestSaveReq = new Request([], [
    'house_no'     => 'Villa 7',
    'street'       => 'Green Park',
    'city'         => 'Kashipur',
    'state'        => 'Uttarakhand',
    'pincode'      => '244713',
    'address'      => 'Opposite Community Hall',
    'address_type' => 'Home'
], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/customer/save-address']);
$guestSaveResp = $accountCtrl->apiSaveAddress($guestSaveReq);
assert($guestSaveResp->getStatusCode() === 200, "Expected 200 from guest apiSaveAddress");
$guestSaveData = json_decode($guestSaveResp->getContent(), true);
assert($guestSaveData['success'] === true, "Expected guest save success true");
assert($_SESSION['customer_profile']['street'] === 'Green Park', "Expected session customer_profile street to be saved");
echo "Test 5: apiSaveAddress works for guest sessions and stores address into session - PASSED\n";

// 6. Test apiGetSavedAddresses endpoint
$addrsReq = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/customer/addresses']);
$addrsResp = $accountCtrl->apiGetSavedAddresses($addrsReq);
assert($addrsResp->getStatusCode() === 200, "Expected 200 from /api/customer/addresses");
$addrsData = json_decode($addrsResp->getContent(), true);
assert($addrsData['success'] === true, "Expected success true from addresses endpoint");
assert(is_array($addrsData['addresses']), "Expected addresses array");
echo "Test 6: apiGetSavedAddresses returns address list successfully - PASSED\n";

// 7. Test multiple distinct addresses preservation without overwriting
Auth::login($user);
$addr1Req = new Request([], [
    'house_no'     => 'Flat 101',
    'street'       => 'Station Road',
    'city'         => 'Kashipur',
    'state'        => 'Uttarakhand',
    'pincode'      => '244713',
    'address_type' => 'Other'
], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/customer/save-address']);
$accountCtrl->apiSaveAddress($addr1Req);

$addr2Req = new Request([], [
    'house_no'     => 'Flat 202',
    'street'       => 'Bazpur Road',
    'city'         => 'Kashipur',
    'state'        => 'Uttarakhand',
    'pincode'      => '244713',
    'address_type' => 'Other'
], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/customer/save-address']);
$accountCtrl->apiSaveAddress($addr2Req);

$multiAddrsResp = $accountCtrl->apiGetSavedAddresses($addrsReq);
$multiAddrsData = json_decode($multiAddrsResp->getContent(), true)['addresses'];
$foundStation = false;
$foundBazpur = false;
foreach ($multiAddrsData as $ma) {
    if (($ma['street'] ?? '') === 'Station Road') $foundStation = true;
    if (($ma['street'] ?? '') === 'Bazpur Road') $foundBazpur = true;
}
assert($foundStation && $foundBazpur, "Expected both Station Road and Bazpur Road to be preserved without overwriting each other");
echo "Test 7: Multiple distinct addresses with same address type are preserved without overwriting - PASSED\n";

// Cleanup test user
Database::execute("DELETE FROM customer_addresses WHERE user_id = ?", [$userId]);
Database::execute("DELETE FROM customer_profiles WHERE user_id = ?", [$userId]);
Database::execute("DELETE FROM users WHERE id = ?", [$userId]);
Auth::logout();
Cart::clear();
unset($_SESSION['customer_profile']);
unset($_SESSION['customer_addresses']);

echo "=== ALL CUSTOMER ADDRESS PERSISTENCE & AUTO-FILL TESTS PASSED! ===\n";
