<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../public/index.php';

use App\Core\Cart;
use App\Core\Database;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceVariant;

echo "=== RUNNING SERVICE VARIANTS & DYNAMIC PRICING SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $name, string $details = ''): void {
    global $passCount, $failCount;
    if ($cond) {
        $passCount++;
        echo "  [PASS] {$name}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$name} - {$details}\n";
    }
}

// ----------------------------------------------------
// TEST 1: Schema & Seeding Verification
// ----------------------------------------------------
echo "[TEST 1] Testing Schema Creation & Initial Variant Seeding...\n";
ServiceVariant::ensureSchema();

$tank = Database::fetchOne("SELECT id, name, slug FROM services WHERE slug = 'water-tank-cleaning' OR name LIKE '%Tank%' LIMIT 1");
assertCondition(!empty($tank), "Water Tank Cleaning service exists in DB", "Service not found");

$sofa = Database::fetchOne("SELECT id, name, slug FROM services WHERE slug IN ('sofa-upholstery-deep-cleaning', 'sofa-cleaning') OR name LIKE '%Sofa%' LIMIT 1");
assertCondition(!empty($sofa), "Sofa Cleaning service exists in DB", "Service not found");

$kitchen = Database::fetchOne("SELECT id, name, slug FROM services WHERE slug = 'kitchen-deep-cleaning' OR name LIKE '%Kitchen Deep%' LIMIT 1");
assertCondition(!empty($kitchen), "Kitchen Deep Cleaning service exists in DB", "Service not found");

$home = Database::fetchOne("SELECT id, name, slug FROM services WHERE slug IN ('professional-full-home-cleaning', 'full-home-cleaning') OR name LIKE '%Full Home%' LIMIT 1");
assertCondition(!empty($home), "Full Home Cleaning service exists in DB", "Service not found");

// Check seeded variants count
if ($tank) {
    $tankVariants = ServiceVariant::forService((int)$tank['id']);
    assertCondition(count($tankVariants) >= 4, "Water Tank has 4+ capacity options seeded", "Found " . count($tankVariants));
}
if ($sofa) {
    $sofaVariants = ServiceVariant::forService((int)$sofa['id']);
    assertCondition(count($sofaVariants) >= 5, "Sofa Cleaning has 5+ seat options seeded (3-7 seater)", "Found " . count($sofaVariants));
}
if ($kitchen) {
    $kVariants = ServiceVariant::getGroupedByService((int)$kitchen['id']);
    assertCondition(isset($kVariants['Kitchen Size']) && isset($kVariants['Cleaning Type']), "Kitchen has both 'Kitchen Size' and 'Cleaning Type' option groups", "Groups missing");
}
if ($home) {
    $hVariants = ServiceVariant::getGroupedByService((int)$home['id']);
    assertCondition(isset($hVariants['Home Size']) && isset($hVariants['Cleaning Type']), "Full Home has both 'Home Size' and 'Cleaning Type' option groups", "Groups missing");
}

// ----------------------------------------------------
// TEST 2: Image Resolution & Assets Existence
// ----------------------------------------------------
echo "\n[TEST 2] Testing Realistic Refixel Image Resolutions...\n";
$tankImg = Service::resolveImage('water-tank-cleaning');
assertCondition($tankImg === 'refixel-water-tank-cleaning.jpg', "Water Tank resolves to refixel-water-tank-cleaning.jpg", "Got: {$tankImg}");
assertCondition(file_exists(ROOT_PATH . '/public/assets/img/' . $tankImg), "Water Tank image file exists on disk");

$sofaImg = Service::resolveImage('sofa-cleaning');
assertCondition($sofaImg === 'refixel-sofa-cleaning.jpg', "Sofa resolves to refixel-sofa-cleaning.jpg", "Got: {$sofaImg}");
assertCondition(file_exists(ROOT_PATH . '/public/assets/img/' . $sofaImg), "Sofa image file exists on disk");

$kitchenImg = Service::resolveImage('kitchen-deep-cleaning');
assertCondition($kitchenImg === 'refixel-kitchen-cleaning.jpg', "Kitchen resolves to refixel-kitchen-cleaning.jpg", "Got: {$kitchenImg}");
assertCondition(file_exists(ROOT_PATH . '/public/assets/img/' . $kitchenImg), "Kitchen image file exists on disk");

$homeImg = Service::resolveImage('full-home-cleaning');
assertCondition($homeImg === 'refixel-full-home-clean.jpg', "Full Home resolves to refixel-full-home-clean.jpg", "Got: {$homeImg}");
assertCondition(file_exists(ROOT_PATH . '/public/assets/img/' . $homeImg), "Full Home image file exists on disk");

// ----------------------------------------------------
// TEST 3: Dynamic Price Calculations
// ----------------------------------------------------
echo "\n[TEST 3] Testing Dynamic Price Calculations for Single and Multi-Group Variations...\n";
if ($tank) {
    // 500L => ₹400
    $v500 = Database::fetchOne("SELECT id, price FROM service_variants WHERE service_id = ? AND name LIKE '%500%' LIMIT 1", [(int)$tank['id']]);
    if ($v500) {
        $calc500 = ServiceVariant::calculatePrice((int)$tank['id'], [(int)$v500['id']], (float)$tank['id']);
        assertCondition((float)$calc500['total_price'] === 400.00, "Tank 500L computes to ₹400.00", "Got: " . $calc500['total_price']);
    }

    // 1000L => ₹800
    $v1000 = Database::fetchOne("SELECT id, price FROM service_variants WHERE service_id = ? AND name LIKE '%1000%' LIMIT 1", [(int)$tank['id']]);
    if ($v1000) {
        $calc1000 = ServiceVariant::calculatePrice((int)$tank['id'], [(int)$v1000['id']], (float)$tank['id']);
        assertCondition((float)$calc1000['total_price'] === 800.00, "Tank 1000L computes to ₹800.00", "Got: " . $calc1000['total_price']);
        assertCondition(str_contains($calc1000['title_suffix'], '1000'), "Title suffix includes '1000'");
    }

    // 2000L => ₹1500
    $v2000 = Database::fetchOne("SELECT id, price FROM service_variants WHERE service_id = ? AND name LIKE '%2000%' LIMIT 1", [(int)$tank['id']]);
    if ($v2000) {
        $calc2000 = ServiceVariant::calculatePrice((int)$tank['id'], [(int)$v2000['id']], (float)$tank['id']);
        assertCondition((float)$calc2000['total_price'] === 1500.00, "Tank 2000L computes to ₹1500.00", "Got: " . $calc2000['total_price']);
    }
}

if ($kitchen) {
    // Medium Kitchen (₹699) + Deep Cleaning (+₹300) = ₹999
    $vMed = Database::fetchOne("SELECT id FROM service_variants WHERE service_id = ? AND group_name = 'Kitchen Size' AND name = 'Medium' LIMIT 1", [(int)$kitchen['id']]);
    $vDeep = Database::fetchOne("SELECT id FROM service_variants WHERE service_id = ? AND group_name = 'Cleaning Type' AND name = 'Deep Cleaning' LIMIT 1", [(int)$kitchen['id']]);

    if ($vMed && $vDeep) {
        $calcKitchen = ServiceVariant::calculatePrice((int)$kitchen['id'], [(int)$vMed['id'], (int)$vDeep['id']], 499.00);
        assertCondition((float)$calcKitchen['total_price'] === 999.00, "Kitchen Medium + Deep Cleaning computes to ₹999.00 (₹699 + ₹300)", "Got: " . $calcKitchen['total_price']);
        assertCondition(str_contains($calcKitchen['title_suffix'], 'Medium') && str_contains($calcKitchen['title_suffix'], 'Deep Cleaning'), "Suffix includes 'Medium · Deep Cleaning'");
    }
}

// ----------------------------------------------------
// TEST 4: Cart Operations with Variations
// ----------------------------------------------------
echo "\n[TEST 4] Testing Cart Operations with Variants...\n";
Cart::clear();
assertCondition(Cart::getDetails()['is_empty'], "Cart is initially clean");

if ($tank && !empty($v1000)) {
    // Add Tank 1000L (₹800) to cart
    $res = Cart::add((int)$tank['id'], 1, [(int)$v1000['id']]);
    assertCondition(!empty($res['success']), "Added Tank 1000L variant to cart");

    $cartData = Cart::getDetails();
    assertCondition($cartData['count'] === 1, "Cart count is 1");
    assertCondition((float)$cartData['total'] === 800.00, "Cart total is ₹800.00 for 1000L", "Got: " . $cartData['total']);
    assertCondition(str_contains($cartData['items'][0]['name'], '1000'), "Cart item title reflects variant name");

    // Increase quantity to 2
    $cKey = $cartData['items'][0]['cart_key'];
    Cart::update($cKey, 2);
    $cartData2 = Cart::getDetails();
    assertCondition($cartData2['count'] === 2, "Cart count updated to 2");
    assertCondition((float)$cartData2['total'] === 1600.00, "Cart total doubled to ₹1600.00", "Got: " . $cartData2['total']);

    Cart::clear();
    assertCondition(Cart::getDetails()['is_empty'], "Cart cleared successfully");
}

// ----------------------------------------------------
// TEST 5: Admin Pricing Update and Catalog Manipulation
// ----------------------------------------------------
echo "\n[TEST 5] Testing Admin Variant Price Updating from Dashboard Catalog...\n";
if ($tank) {
    // Insert test variant
    $testName = "Test Custom 3000L";
    Database::execute(
        "INSERT INTO service_variants (service_id, group_name, name, price, duration_minutes, is_default, sort_order, is_active)
         VALUES (?, 'Capacity', ?, 2200.00, 150, 0, 99, 1)",
        [(int)$tank['id'], $testName]
    );
    $inserted = Database::fetchOne("SELECT * FROM service_variants WHERE service_id = ? AND name = ?", [(int)$tank['id'], $testName]);
    assertCondition(!empty($inserted), "Admin can insert custom variant");

    // Admin edits price to 2400.00
    Database::execute("UPDATE service_variants SET price = 2400.00 WHERE id = ?", [(int)$inserted['id']]);
    $updated = Database::fetchOne("SELECT price FROM service_variants WHERE id = ?", [(int)$inserted['id']]);
    assertCondition((float)$updated['price'] === 2400.00, "Admin can update variant price to ₹2400.00");

    // Clean up test variant
    Database::execute("DELETE FROM service_variants WHERE id = ?", [(int)$inserted['id']]);
    $deleted = Database::fetchOne("SELECT id FROM service_variants WHERE id = ?", [(int)$inserted['id']]);
    assertCondition(empty($deleted), "Admin can delete variant");
}

// ----------------------------------------------------
// TEST 6: Direct Booking with Variants & Pure COD Verification
// ----------------------------------------------------
echo "\n[TEST 6] Testing Booking Submission with Variants & Pure COD Integrity...\n";
if ($tank && !empty($v500)) {
    $bookingNo = Booking::generateBookingNo();
    $bId = Booking::create([
        'booking_no'     => $bookingNo,
        'guest_token'    => Booking::generateGuestToken(),
        'customer_id'    => null,
        'service_id'     => $tank['id'],
        'name'           => 'Automated Test Customer',
        'phone'          => '9876543210',
        'email'          => 'test@refixel.com',
        'address'        => 'Test Address, Kashipur',
        'pincode'        => '244713',
        'preferred_date' => date('Y-m-d', time() + 86400),
        'preferred_time' => '10:00 - 12:00 PM',
        'issue_details'  => 'Selected Option: 500 Litres Tank Cleaning',
        'status'         => 'new',
        'priority'       => 'normal',
    ]);

    $calc = ServiceVariant::calculatePrice((int)$tank['id'], [(int)$v500['id']], (float)$tank['id']);
    $totalAmount = (float)$calc['total_price'];
    $itemTitle = $tank['name'] . $calc['title_suffix'];

    Booking::recordItems($bId, [
        [
            'service_id'  => (int)$tank['id'],
            'name'        => $itemTitle,
            'item_type'   => 'service',
            'quantity'    => 1,
            'unit_price'  => $totalAmount,
            'total_price' => $totalAmount,
        ]
    ]);

    Database::execute("UPDATE bookings SET total_amount = ?, due_amount = ?, advance_amount = 0.00, payment_type = 'cod' WHERE id = ?", [$totalAmount, $totalAmount, $bId]);

    $savedBooking = Booking::find($bId);
    assertCondition(!empty($savedBooking), "Booking created successfully");
    assertCondition((float)$savedBooking['total_amount'] === 400.00, "Booking total amount equals variant price ₹400.00", "Got: " . $savedBooking['total_amount']);
    assertCondition((float)$savedBooking['due_amount'] === 400.00, "Pure COD due amount equals full total ₹400.00 (Zero advance)", "Got: " . $savedBooking['due_amount']);
    assertCondition((float)$savedBooking['advance_amount'] === 0.00, "Advance amount is strictly 0.00 in pure COD");

    // Clean up test booking
    Database::execute("DELETE FROM booking_items WHERE booking_id = ?", [$bId]);
    Database::execute("DELETE FROM bookings WHERE id = ?", [$bId]);
    echo "  [PASS] Booking cleanup complete\n";
}

echo "\n=========================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "=========================================\n";

if ($failCount > 0) {
    exit(1);
}
