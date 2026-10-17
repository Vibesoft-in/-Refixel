<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Controllers\AccountController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\PaymentController;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\InvoiceBuilder;
use App\Core\Request;
use App\Core\Workflow;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Review;
use App\Services\Payment\RazorpayGateway;

echo "=== RUNNING PAYMENT, INVOICE, REVIEW & WORKFLOW FEATURE TESTS (PROMPT 10) ===\n";

$accountCtrl = new AccountController();
$adminPaymentCtrl = new PaymentController();
$adminContentCtrl = new ContentController();

// 1. Setup Customer and Admin accounts
$customer = Database::fetchOne("SELECT * FROM users WHERE role = 'customer' LIMIT 1");
if (!$customer) {
    $cid = Database::query(
        "INSERT INTO users (name, phone, email, password_hash, role, is_active)
         VALUES ('Test Customer', '9811122233', 'cust_test@refixel.com', 'hash', 'customer', 1)"
    );
    $customer = Database::fetchOne("SELECT * FROM users WHERE email = 'cust_test@refixel.com'");
}
$customerId = (int)$customer['id'];

$admin = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
$adminId = (int)$admin['id'];

$service = Database::fetchOne("SELECT * FROM services WHERE is_active = 1 LIMIT 1");
$serviceId = (int)$service['id'];

// ==========================================
// TEST 1: Payment Gateway Abstraction & Razorpay
// ==========================================
$gateway = new RazorpayGateway('rzp_test_mock_key', 'mock_secret_key_123');
assert($gateway->getProviderName() === 'razorpay', "Provider name must be razorpay");

// Order creation
$order = $gateway->createOrder(999, 1499.00, 'INR', ['service' => 'Deep Cleaning']);
assert(!empty($order['order_id']), "Gateway must generate order_id");
assert($order['amount'] === 1499.00, "Gateway order amount must match");

// HMAC Signature verification
$orderId = 'order_test_987654';
$paymentId = 'pay_test_123456';
$secret = 'mock_secret_key_123';
$validSig = hash_hmac('sha256', "{$orderId}|{$paymentId}", $secret);

$validPayload = [
    'razorpay_order_id'   => $orderId,
    'razorpay_payment_id' => $paymentId,
    'razorpay_signature'  => $validSig,
];
assert($gateway->verifySignature($validPayload) === true, "Valid HMAC signature must verify as true");

$tamperedPayload = [
    'razorpay_order_id'   => $orderId,
    'razorpay_payment_id' => $paymentId,
    'razorpay_signature'  => 'forged_fake_signature_abc',
];
assert($gateway->verifySignature($tamperedPayload) === false, "Forged HMAC signature must be rejected");

// Refund
$refundRes = $gateway->refund($paymentId, 1499.00, 'Customer requested refund');
assert(!empty($refundRes['refund_id']), "Refund response must contain refund_id");
echo "Test 1: Payment Gateway Abstraction & Razorpay - PASSED\n";

// ==========================================
// TEST 2: GST Invoicing Calculation & Model
// ==========================================
// Create a test booking and payment
$bookingNo = 'BK-TEST-' . bin2hex(random_bytes(3));
Database::query(
    "INSERT INTO bookings (booking_no, customer_id, service_id, name, phone, address, status)
     VALUES (:bno, :cid, :sid, 'Test Customer', '9811122233', '123 Test Street', 'completed')",
    ['bno' => $bookingNo, 'cid' => $customerId, 'sid' => $serviceId]
);
$testBookingId = (int)Database::lastInsertId();

$paymentId = Payment::create([
    'booking_id'      => $testBookingId,
    'amount'          => 1000.00,
    'method'          => 'upi',
    'status'          => 'paid',
    'transaction_ref' => 'UPI-123456789',
    'paid_at'         => date('Y-m-d H:i:s'),
    'recorded_by'     => $adminId,
]);

$invoice = InvoiceBuilder::createForPayment($paymentId, 1000.00, 18.00);
assert(!empty($invoice['invoice_no']), "Invoice number must be generated");
assert((float)$invoice['subtotal'] === 1000.00, "Subtotal must be 1000.00");
assert((float)$invoice['gst_rate'] === 18.00, "GST rate must be 18%");
assert((float)$invoice['gst_amount'] === 180.00, "GST amount must be 180.00");
assert((float)$invoice['total'] === 1180.00, "Total must be 1180.00");

// Check Invoice::findWithDetails
$invDetails = Invoice::findWithDetails($invoice['id']);
assert($invDetails !== null, "Invoice details must be retrievable");
assert((int)$invDetails['customer_id'] === $customerId, "Invoice must match customer");
assert($invDetails['booking_no'] === $bookingNo, "Invoice must match booking number");

// Check Customer Invoices listing
$customerInvoices = Invoice::findByCustomer($customerId);
assert(!empty($customerInvoices), "Customer invoices must return at least 1 invoice");
echo "Test 2: GST Invoicing Calculation & Model - PASSED\n";

// ==========================================
// TEST 3: Customer Invoice View & Ownership Isolation
// ==========================================
Auth::login(['id' => $customerId, 'name' => 'Test Customer', 'role' => 'customer', 'must_change_password' => 0]);

$reqInv = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/account/invoices/{$invoice['id']}"]);
$invResponse = $accountCtrl->invoiceDetail($reqInv, (string)$invoice['id']);
assert($invResponse->getStatusCode() === 200, "Customer must be able to view their own invoice");
assert(str_contains($invResponse->getContent(), $invoice['invoice_no']), "Invoice view must show invoice number");

// Other customer blocked
Auth::login(['id' => 99999, 'name' => 'Other Customer', 'role' => 'customer', 'must_change_password' => 0]);
$otherResponse = $accountCtrl->invoiceDetail($reqInv, (string)$invoice['id']);
assert($otherResponse->getStatusCode() === 404, "Other customer must receive 404 when accessing another invoice");
echo "Test 3: Customer Invoice View & Ownership Isolation - PASSED\n";

// ==========================================
// TEST 4: Rescheduling Workflow (State: new -> rescheduled)
// ==========================================
Auth::login(['id' => $customerId, 'name' => 'Test Customer', 'role' => 'customer', 'must_change_password' => 0]);

$bReschedNo = 'BK-RES-' . bin2hex(random_bytes(3));
Database::query(
    "INSERT INTO bookings (booking_no, customer_id, service_id, name, phone, address, preferred_date, preferred_time, status)
     VALUES (:bno, :cid, :sid, 'Test Customer', '9811122233', '456 Lane', '2026-10-05', '09:00 AM - 12:00 PM', 'new')",
    ['bno' => $bReschedNo, 'cid' => $customerId, 'sid' => $serviceId]
);
$reschedBookingId = (int)Database::lastInsertId();

$newDate = date('Y-m-d', strtotime('+3 days'));
$newTime = '03:00 PM - 06:00 PM';
$reqResched = new Request([
    'preferred_date' => $newDate,
    'preferred_time' => $newTime,
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/account/bookings/{$reschedBookingId}/reschedule"]);

$reschedRes = $accountCtrl->rescheduleBooking($reqResched, (string)$reschedBookingId);
assert($reschedRes->getStatusCode() === 302, "Reschedule should redirect");

$updatedBooking = Booking::find($reschedBookingId);
assert($updatedBooking['status'] === Workflow::STATUS_RESCHEDULED, "Booking status must be 'rescheduled'");
assert($updatedBooking['preferred_date'] === $newDate, "Preferred date must be updated");
assert($updatedBooking['preferred_time'] === $newTime, "Preferred time must be updated");
echo "Test 4: Customer Rescheduling Workflow (new -> rescheduled) - PASSED\n";

// ==========================================
// TEST 5: Cancellation Workflow (State: rescheduled -> cancelled)
// ==========================================
$reqCancel = new Request([
    'reason' => 'Schedule conflict',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/account/bookings/{$reschedBookingId}/cancel"]);

$cancelRes = $accountCtrl->cancelBooking($reqCancel, (string)$reschedBookingId);
assert($cancelRes->getStatusCode() === 302, "Cancel should redirect");

$cancelledBooking = Booking::find($reschedBookingId);
assert($cancelledBooking['status'] === Workflow::STATUS_CANCELLED, "Booking status must be 'cancelled'");
echo "Test 5: Customer Cancellation Workflow (rescheduled -> cancelled) - PASSED\n";

// ==========================================
// TEST 6: Refund Request Workflow (State: cancelled -> refund_requested)
// ==========================================
// Add a paid payment for this cancelled booking
Payment::create([
    'booking_id'      => $reschedBookingId,
    'amount'          => 799.00,
    'method'          => 'card',
    'status'          => 'paid',
    'transaction_ref' => 'CARD-REF-999',
    'paid_at'         => date('Y-m-d H:i:s'),
    'recorded_by'     => $adminId,
]);

$reqRefund = new Request([
    'reason' => 'Service was cancelled, please refund to original card',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/account/bookings/{$reschedBookingId}/refund-request"]);

$refundReqRes = $accountCtrl->requestRefund($reqRefund, (string)$reschedBookingId);
assert($refundReqRes->getStatusCode() === 302, "Refund request should redirect");

$refundReqBooking = Booking::find($reschedBookingId);
assert($refundReqBooking['status'] === Workflow::STATUS_REFUND_REQUESTED, "Booking status must be 'refund_requested'");
echo "Test 6: Customer Refund Request (cancelled -> refund_requested) - PASSED\n";

// ==========================================
// TEST 7: Admin Refund Execution (State: refund_requested -> refunded)
// ==========================================
Auth::login(['id' => $adminId, 'name' => 'Admin User', 'role' => 'admin', 'must_change_password' => 0]);

$payRecord = Database::fetchOne("SELECT * FROM payments WHERE booking_id = :bid AND status = 'paid'", ['bid' => $reschedBookingId]);
assert($payRecord !== null, "Paid payment record must exist");

$reqAdminRefund = new Request([
    'reason' => 'Customer cancellation approved and refunded',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/payments/{$payRecord['id']}/refund"]);

$adminRefundRes = $adminPaymentCtrl->refundPayment($reqAdminRefund, (string)$payRecord['id']);
assert($adminRefundRes->getStatusCode() === 302, "Admin refund should redirect");

$refPay = Payment::find((int)$payRecord['id']);
assert($refPay['status'] === 'refunded', "Payment status must be 'refunded'");

$finalBooking = Booking::find($reschedBookingId);
assert($finalBooking['status'] === Workflow::STATUS_REFUNDED, "Booking status must transition to 'refunded'");
echo "Test 7: Admin Refund Execution (refund_requested -> refunded) - PASSED\n";

// ==========================================
// TEST 8: State Machine Boundary Rules & Rejection of Illegal Transitions
// ==========================================
// A refunded booking cannot transition backwards to in_progress or accepted
assert(Workflow::canTransition(Workflow::STATUS_REFUNDED, Workflow::STATUS_IN_PROGRESS, 'admin') === false, "Refunded booking cannot move to in_progress");
assert(Workflow::canTransition(Workflow::STATUS_REFUNDED, Workflow::STATUS_ACCEPTED, 'staff') === false, "Staff cannot accept refunded booking");
assert(Workflow::canTransition(Workflow::STATUS_COMPLETED, Workflow::STATUS_NEW, 'customer') === false, "Customer cannot revert completed to new");
// Customer cannot directly refund themselves
assert(Workflow::canTransition(Workflow::STATUS_CANCELLED, Workflow::STATUS_REFUNDED, 'customer') === false, "Customer cannot transition to refunded directly");
echo "Test 8: State Machine Boundary Rules & Rejection of Illegal Transitions - PASSED\n";

// ==========================================
// TEST 9: Customer Review Submission (1 per booking rule)
// ==========================================
Auth::login(['id' => $customerId, 'name' => 'Test Customer', 'role' => 'customer', 'must_change_password' => 0]);

// First review on $testBookingId (which is completed)
$reqReview = new Request([
    'rating'  => 5,
    'comment' => 'Fantastic cleaning work by the Refixel team! Very punctual and thorough.',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/account/bookings/{$testBookingId}/review"]);

$revRes = $accountCtrl->submitReview($reqReview, (string)$testBookingId);
assert($revRes->getStatusCode() === 302, "Review submission should redirect");

$reviewInDb = Review::findByBooking($testBookingId);
assert($reviewInDb !== null, "Review must be saved in database");
assert((int)$reviewInDb['rating'] === 5, "Rating must be 5");
assert((int)$reviewInDb['is_approved'] === 0, "Review must initially be unapproved (is_approved = 0)");

// Attempt duplicate review on same booking
$dupReq = new Request([
    'rating'  => 4,
    'comment' => 'Trying to submit duplicate review',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/account/bookings/{$testBookingId}/review"]);

$dupRes = $accountCtrl->submitReview($dupReq, (string)$testBookingId);
// Count of reviews for this booking must still be 1
$reviewCount = (int)Database::fetchOne("SELECT COUNT(*) as c FROM reviews WHERE booking_id = :bid", ['bid' => $testBookingId])['c'];
assert($reviewCount === 1, "Duplicate review must be rejected (1 review per booking strictly enforced)");
echo "Test 9: Customer Review Submission (1 per booking rule) - PASSED\n";

// ==========================================
// TEST 10: Admin Review Moderation & Public Reviews Gate
// ==========================================
Auth::login(['id' => $adminId, 'name' => 'Admin User', 'role' => 'admin', 'must_change_password' => 0]);

// Before approval: review must NOT appear in public approved reviews
$publicBefore = Review::getApproved();
$foundBefore = false;
foreach ($publicBefore as $r) {
    if ((int)$r['id'] === (int)$reviewInDb['id']) {
        $foundBefore = true;
        break;
    }
}
assert($foundBefore === false, "Unapproved review must not appear in public getApproved() list");

// Admin approves review
$reqApprove = new Request([], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/content/reviews/{$reviewInDb['id']}/approve"]);
$approveRes = $adminContentCtrl->approveReview($reqApprove, (string)$reviewInDb['id']);
assert($approveRes->getStatusCode() === 302, "Approve review should redirect");

$approvedReview = Review::find((int)$reviewInDb['id']);
assert((int)$approvedReview['is_approved'] === 1, "Review must now have is_approved = 1");

// After approval: review MUST appear in public approved reviews
$publicAfter = Review::getApproved();
$foundAfter = false;
foreach ($publicAfter as $r) {
    if ((int)$r['id'] === (int)$reviewInDb['id']) {
        $foundAfter = true;
        break;
    }
}
assert($foundAfter === true, "Approved review must now appear in public getApproved() list");
echo "Test 10: Admin Review Moderation & Public Reviews Gate - PASSED\n";

echo "\nALL 10 PAYMENT, INVOICE, REVIEW & WORKFLOW TESTS PASSED SUCCESSFULLY!\n";
