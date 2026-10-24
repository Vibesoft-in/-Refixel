<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../public/index.php';

use App\Core\View;

echo "=== RUNNING PURE COD VIEWS COMPREHENSIVE VERIFICATION TEST ===\n\n";

// Helper to check for unwanted advance references:
function findAdvanceLeak(string $html): array {
    $leaks = [];
    if (preg_match('/(?:₹|Rs\.?)\s*49(?:\.00)?/i', $html, $m)) {
        $leaks[] = "Found price '{$m[0]}'";
    }
    if (preg_match('/PRE\s*\([^)]*49[^)]*\)/i', $html, $m)) {
        $leaks[] = "Found advance badge '{$m[0]}'";
    }
    if (preg_match('/(?:Advance Paid|Adv):\s*(?:₹|Rs\.?)?\s*(?:49|0\.00|0)(?!\s*Adv)/i', $html, $m)) {
        // We allow 'Pure COD (₹0 Adv)' as a clean descriptive label, but not 'Advance Paid: ₹49' or 'Adv: ₹49' or 'Advance Paid: ₹0.00'
        if (!str_contains($m[0], 'Pure COD') && !str_contains($m[0], '₹0 Adv')) {
            $leaks[] = "Found advance display '{$m[0]}'";
        }
    }
    return $leaks;
}

$bookingData = [
    'id'                   => 760,
    'booking_no'           => 'BK-0D2FD804',
    'customer_id'          => 1,
    'service_id'           => 99,
    'service_name'         => 'Balcony Deep Pressure Wash',
    'name'                 => 'Rishabh Tyagi',
    'phone'                => '7668490935',
    'email'                => 'ritik@gmail.com',
    'address'              => 'Doorstep Service Area',
    'city'                 => 'Kashipur',
    'pincode'              => '244713',
    'preferred_date'       => '2026-10-25',
    'preferred_time'       => '09:00 - 11:00 AM',
    'status'               => 'new',
    'priority'             => 'normal',
    'payment_type'         => 'cod',
    'payment_paid_status'  => 'unpaid',
    'advance_amount'       => 0.00,
    'due_amount'           => 899.00,
    'total_amount'         => 899.00,
    'starting_price'       => 899.00,
];

// 1. ADMIN ENQUIRY SHOW VIEW
echo "[RUN] Test 1: Admin Enquiry Show view for pure COD\n";
ob_start();
extract([
    'booking'      => $bookingData,
    'staffMembers' => [],
    'attachments'  => [],
    'addons'       => [],
]);
require __DIR__ . '/../../app/views/admin/enquiries/show.php';
$htmlEnquiry = ob_get_clean();

$leaks1 = findAdvanceLeak($htmlEnquiry);
assert(empty($leaks1), "Admin Enquiry Show must have no advance leaks: " . implode(', ', $leaks1));
assert(str_contains($htmlEnquiry, 'COD (Pay After Service)'), "Must have COD badge");
assert(str_contains($htmlEnquiry, 'Pure COD (₹0 Adv)'), "Must show Pure COD status");
echo "  [PASS] Admin Enquiry Show verified clean.\n";

// 2. ADMIN BOOKING SHOW VIEW
echo "[RUN] Test 2: Admin Booking Show view for pure COD\n";
ob_start();
extract([
    'booking'      => $bookingData,
    'job'          => ['id' => 1, 'status' => 'new'],
    'staffMembers' => [],
    'history'      => [],
    'jobPhotos'    => [],
    'attachments'  => [],
    'payments'     => [],
    'invoice'      => null,
]);
require __DIR__ . '/../../app/views/admin/bookings/show.php';
$htmlBookingShow = ob_get_clean();

$leaks2 = findAdvanceLeak($htmlBookingShow);
assert(empty($leaks2), "Admin Booking Show must have no advance leaks: " . implode(', ', $leaks2));
assert(str_contains($htmlBookingShow, 'CASH ON DELIVERY (NO ADVANCE)'), "Must have COD badge");
assert(str_contains($htmlBookingShow, 'Pure COD (₹0 Adv)'), "Must show Pure COD status");
echo "  [PASS] Admin Booking Show verified clean.\n";

// 3. TECHNICIAN JOB SHOW VIEW
echo "[RUN] Test 3: Technician Job Show view for pure COD\n";
ob_start();
$jobData = array_merge($bookingData, [
    'id' => 10,
    'status' => 'in_progress',
    'due_amount' => 899.00,
    'advance_amount' => 0.00,
    'total_amount' => 899.00,
    'is_prepaid' => false,
]);
extract([
    'job' => $jobData,
    'jobPhotos' => [],
    'dueAmt' => 899.00,
    'advAmt' => 0.00,
    'totAmt' => 899.00,
]);
require __DIR__ . '/../../app/views/staff/jobs/show.php';
$htmlStaffShow = ob_get_clean();

$leaks3 = findAdvanceLeak($htmlStaffShow);
assert(empty($leaks3), "Technician Job Show must have no advance leaks: " . implode(', ', $leaks3));
assert(str_contains($htmlStaffShow, 'Pure Cash on Delivery'), "Must show Pure Cash on Delivery");
echo "  [PASS] Technician Job Show verified clean.\n";

// 4. TECHNICIAN JOB COMPLETE VIEW
echo "[RUN] Test 4: Technician Job Complete view for pure COD\n";
ob_start();
extract([
    'job' => $jobData,
    'beforePhotos' => [],
    'afterPhotos' => [],
    'dueAmt' => 899.00,
    'advAmt' => 0.00,
    'totAmt' => 899.00,
]);
require __DIR__ . '/../../app/views/staff/jobs/complete.php';
$htmlStaffComplete = ob_get_clean();

$leaks4 = findAdvanceLeak($htmlStaffComplete);
assert(empty($leaks4), "Technician Job Complete must have no advance leaks: " . implode(', ', $leaks4));
assert(str_contains($htmlStaffComplete, '100% Cash / UPI on Delivery'), "Must show 100% Cash/UPI on Delivery");
echo "  [PASS] Technician Job Complete verified clean.\n";

// 5. STAFF PAYMENT MODAL PARTIAL
echo "[RUN] Test 5: Staff Payment Modal partial for pure COD\n";
$htmlStaffModal = View::partial('staff-payment-modal', [
    'job' => $jobData,
    'totAmt' => 899.00,
    'advAmt' => 0.00,
    'dueAmt' => 899.00,
]);

$leaks5 = findAdvanceLeak($htmlStaffModal);
assert(empty($leaks5), "Staff Payment Modal must have no advance leaks: " . implode(', ', $leaks5));
assert(str_contains($htmlStaffModal, 'Total Amount to Collect:'), "Must state Total Amount to Collect");
echo "  [PASS] Staff Payment Modal verified clean.\n";

// 6. CUSTOMER BOOK SUCCESS VIEW
echo "[RUN] Test 6: Customer Book Success view for pure COD\n";
ob_start();
extract([
    'booking' => $bookingData,
    'service' => ['name' => 'Balcony Deep Pressure Wash'],
    'token' => 'test-token',
    'isCod' => true,
    'totAmt' => 899.00,
    'advAmt' => 0.00,
    'dueAmt' => 899.00,
]);
require __DIR__ . '/../../app/views/customer/book-success.php';
$htmlBookSuccess = ob_get_clean();

$leaks6 = findAdvanceLeak($htmlBookSuccess);
assert(empty($leaks6), "Customer Book Success must have no advance leaks: " . implode(', ', $leaks6));
assert(str_contains($htmlBookSuccess, 'Payable to Staff'), "Must state Payable to Staff");
echo "  [PASS] Customer Book Success verified clean.\n";

// 7. CUSTOMER ACCOUNT BOOKING DETAIL VIEW
echo "[RUN] Test 7: Customer Account Booking Detail view for pure COD\n";
ob_start();
extract([
    'booking' => $bookingData,
    'invoices' => [],
    'jobPhotos' => [],
    'job' => ['status' => 'new'],
    'staff' => null,
]);
require __DIR__ . '/../../app/views/customer/account/booking-detail.php';
$htmlCustDetail = ob_get_clean();

$leaks7 = findAdvanceLeak($htmlCustDetail);
assert(empty($leaks7), "Customer Account Booking Detail must have no advance leaks: " . implode(', ', $leaks7));
assert(str_contains($htmlCustDetail, 'Full Amount Due to Staff:'), "Must state Full Amount Due to Staff");
echo "  [PASS] Customer Account Booking Detail verified clean.\n";

// 8. INVOICE CARD PARTIAL
echo "[RUN] Test 8: Invoice Card partial for pure COD\n";
$dummyInvoice = [
    'id' => 101,
    'invoice_no' => 'INV-2026-0001',
    'issued_at' => '2026-10-24 12:00:00',
    'status' => 'issued',
    'customer_name' => 'Rishabh Tyagi',
    'customer_phone' => '7668490935',
    'customer_email' => 'ritik@gmail.com',
    'customer_address' => 'Doorstep Service Area',
    'pincode' => '244713',
    'booking_no' => 'BK-0D2FD804',
    'service_name' => 'Balcony Deep Pressure Wash',
    'payment_method' => 'cod',
    'booking_payment_type' => 'cod',
    'subtotal' => 761.86,
    'gst_amount' => 137.14,
    'total' => 899.00,
    'advance_paid' => 0.00,
    'due_amount' => 899.00,
    'booking_advance_amount' => 0.00,
    'booking_due_amount' => 899.00,
];

$htmlInvoice = View::partial('invoice-card', ['invoice' => $dummyInvoice]);

$leaks8 = findAdvanceLeak($htmlInvoice);
assert(empty($leaks8), "Invoice must have no advance leaks: " . implode(', ', $leaks8));
assert(str_contains($htmlInvoice, 'Full Amount Due to Staff:'), "Must state Full Amount Due to Staff");
assert(!str_contains($htmlInvoice, '- ₹'), "Invoice must not have negative deduction");
echo "  [PASS] Invoice Card verified clean.\n";

echo "\n=========================================\n";
echo "🎉 ALL 8 SCREENS VERIFIED 100% CLEAN: ZERO ADVANCE OR ₹49 SHOWN TO ADMIN, TECHNICIAN, OR CUSTOMER!\n";
echo "=========================================\n";
