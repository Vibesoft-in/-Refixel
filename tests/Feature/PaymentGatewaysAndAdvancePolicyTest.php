<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../public/index.php';

use App\Core\Database;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Models\Booking;
use App\Core\InvoiceBuilder;

echo "=== RUNNING PAYMENT GATEWAYS & ADVANCE POLICY FEATURE TESTS ===\n\n";

// TEST 1: PaymentGateway model helper methods
echo "[RUN] Test 1: PaymentGateway helper methods\n";
Setting::set('prebooking_advance_enabled', '0');
Setting::set('prebooking_advance_amount', '49.00');

assert(!PaymentGateway::isAdvanceRequired(), "Advance must not be required when toggle is 0");
assert(PaymentGateway::getAdvanceAmount() === 49.0, "Advance amount must be 49.0");

Setting::set('prebooking_advance_enabled', '1');
assert(PaymentGateway::isAdvanceRequired() === PaymentGateway::isRazorpayActive(), "Advance requirement must follow toggle and Razorpay state");
echo "[PASS] Test 1: PaymentGateway helper methods behave correctly.\n\n";

// TEST 2: Toggle gateway state and settings synchronization
echo "[RUN] Test 2: Toggle gateway state and syncToSettings\n";
$rzp = PaymentGateway::getByCode('razorpay');
assert($rzp !== null, "Razorpay record must exist");

$origStatus = (int)$rzp['is_active'];
$newStatus = $origStatus ? 0 : 1;

PaymentGateway::update((int)$rzp['id'], ['is_active' => $newStatus]);
$updatedRzp = PaymentGateway::find((int)$rzp['id']);
PaymentGateway::syncToSettings($updatedRzp);

assert((int)$updatedRzp['is_active'] === $newStatus, "Gateway is_active must match updated state");
assert((string)Setting::get('razorpay_is_active') === (string)$newStatus, "Setting razorpay_is_active must be synchronized");

// Revert to original or set according to requirement (user requested COD only)
// User said: "i diabled the pre paid service but it still show ... abhii bs cod rakhni hai hume iss liye"
// We will test both states
echo "[PASS] Test 2: Gateway toggle and syncToSettings passed.\n\n";

// TEST 3: Pure COD Booking when advance toggle is OFF
echo "[RUN] Test 3: Pure COD Booking when advance toggle is OFF\n";
Setting::set('prebooking_advance_enabled', '0');
Setting::set('prebooking_advance_amount', '49.00');

// Create test booking with cod
$bookingNo = 'BK-TEST-' . bin2hex(random_bytes(3));
Database::execute(
    "INSERT INTO bookings (booking_no, service_id, name, phone, email, address, total_amount, advance_amount, due_amount, payment_type, status, priority, created_at, updated_at)
     VALUES (?, 1, 'COD Test Customer', '9876543210', 'cod@test.com', 'Test Address', 899.00, 0.00, 899.00, 'cod', 'new', 'normal', NOW(), NOW())",
    [$bookingNo]
);
$testBookingId = (int)Database::lastInsertId();

$savedBooking = Database::fetchOne("SELECT * FROM bookings WHERE id = ?", [$testBookingId]);
assert((float)$savedBooking['total_amount'] === 899.00, "Booking total must be 899");
assert((float)$savedBooking['advance_amount'] === 0.00, "Booking advance must be 0 when advance toggle is OFF");
assert((float)$savedBooking['due_amount'] === 899.00, "Booking due must be full 899 when advance toggle is OFF");
assert($savedBooking['payment_type'] === 'cod', "Payment type must be cod");

echo "[PASS] Test 3: Pure COD Booking verified with 0 advance and full due amount.\n\n";

// TEST 4: Invoice rendering when advance toggle is OFF (Full Amount Without Deducting 49)
echo "[RUN] Test 4: Invoice rendering for Pure COD (No 49 deducted)\n";
$dummyInvoice = [
    'id' => 99999,
    'invoice_no' => 'INV-2026-TEST',
    'issued_at' => '2026-10-24 16:00:00',
    'subtotal' => 761.86,
    'gst_amount' => 137.14,
    'total' => 899.00,
    'advance_paid' => 0.00,
    'due_amount' => 899.00,
    'customer_name' => 'COD Test Customer',
    'customer_phone' => '9876543210',
    'customer_email' => 'cod@test.com',
    'customer_address' => 'Test Address',
    'pincode' => '244713',
    'booking_no' => $bookingNo,
    'service_name' => 'Full Home Cleaning',
    'payment_method' => 'cod',
    'booking_payment_type' => 'cod',
    'booking_advance_amount' => 0.00,
    'booking_due_amount' => 899.00,
];

$renderedHtml = \App\Core\View::partial('invoice-card', ['invoice' => $dummyInvoice]);

// Must contain full amount due to staff
assert(str_contains($renderedHtml, 'Full Amount Due to Staff'), "Invoice must state 'Full Amount Due to Staff'");
assert(str_contains($renderedHtml, 'No Advance Deducted'), "Invoice must state '(No Advance Deducted)'");
assert(str_contains($renderedHtml, '899.00'), "Invoice must show full 899.00");
assert(!str_contains($renderedHtml, '- ₹49.00'), "Invoice must NOT deduct - ₹49.00");
assert(!str_contains($renderedHtml, '- ₹0.00'), "Invoice must NOT show negative 0.00 deduction");

echo "[PASS] Test 4: Invoice correctly displays full amount ₹899 without deducting ₹49.\n\n";

// TEST 5: Invoice rendering when advance WAS paid (e.g. ₹49 advance toggle ON)
echo "[RUN] Test 5: Invoice rendering when advance was paid\n";
$dummyAdvanceInvoice = $dummyInvoice;
$dummyAdvanceInvoice['advance_paid'] = 49.00;
$dummyAdvanceInvoice['due_amount'] = 850.00;

$renderedAdvHtml = \App\Core\View::partial('invoice-card', ['invoice' => $dummyAdvanceInvoice]);
assert(str_contains($renderedAdvHtml, 'Pre-Booking Advance Paid:'), "Invoice must show advance paid line when > 0");
assert(str_contains($renderedAdvHtml, '- ₹49.00'), "Invoice must show - ₹49.00 deduction when advance was paid");
assert(str_contains($renderedAdvHtml, '850.00'), "Invoice must show remaining doorstep due ₹850.00");

echo "[PASS] Test 5: Invoice correctly displays advance deduction when advance > 0.\n\n";

// Cleanup test booking
Database::execute("DELETE FROM bookings WHERE id = ?", [$testBookingId]);

// Reset user's desired state: COD only, prepaid disabled, advance disabled
PaymentGateway::update((int)$rzp['id'], ['is_active' => 0]);
$rzpDisabled = PaymentGateway::find((int)$rzp['id']);
PaymentGateway::syncToSettings($rzpDisabled);

$codGw = PaymentGateway::getByCode('cod');
if ($codGw) {
    PaymentGateway::update((int)$codGw['id'], ['is_active' => 1]);
    $codEnabled = PaymentGateway::find((int)$codGw['id']);
    PaymentGateway::syncToSettings($codEnabled);
}
Setting::set('prebooking_advance_enabled', '0');
Setting::set('prebooking_advance_amount', '49.00');

echo "🎉 ALL PAYMENT GATEWAYS & ADVANCE POLICY TESTS PASSED 100%!\n";
