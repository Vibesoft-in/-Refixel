<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Controllers\BookingController;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Services\Payment\RazorpayGateway;

echo "=== RUNNING RAZORPAY BOOKING PAYMENT & VERIFICATION TESTS ===\n";

$ctrl = new BookingController();
$service = Service::find(1); // Service with starting_price
assert($service !== null, "Service 1 must exist");

// TEST 1: Submit Booking with payment_method='online' -> triggers Razorpay order
$token = Csrf::token();
$postDataOnline = [
    '_csrf'          => $token,
    'is_ajax'        => '1',
    'service_id'     => '1',
    'name'           => 'Razorpay Customer',
    'phone'          => '9876543210',
    'email'          => 'razorpay.test@example.com',
    'address'        => '123 Tech Park, Kashipur',
    'city'           => 'Kashipur',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '10:00 - 12:00 PM',
    'payment_method' => 'online',
];

$reqOnline = new Request([], $postDataOnline, [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$respOnline = $ctrl->submit($reqOnline);
assert($respOnline->getStatusCode() === 200, "Online booking submission must return 200");
$dataOnline = json_decode($respOnline->getContent(), true);

assert($dataOnline['success'] === true, "Online booking response success must be true");
assert($dataOnline['requires_payment'] === true, "Online booking response must have requires_payment=true");
assert(!empty($dataOnline['razorpay_order_id']), "Must contain razorpay_order_id");
assert(!empty($dataOnline['razorpay_key_id']), "Must contain razorpay_key_id");
assert($dataOnline['amount'] > 0, "Amount must be in paise > 0");
$bookingId = (int)$dataOnline['booking_id'];
$bookingNo = (string)$dataOnline['booking_no'];
$orderId = (string)$dataOnline['razorpay_order_id'];

echo "Test 1: Online Booking Razorpay Order Generation - PASSED (Order: {$orderId})\n";

// TEST 2: Payment Verification via /book/verify-payment with HMAC SHA256 Signature
$paymentId = 'pay_test_' . bin2hex(random_bytes(4));
$gw = new RazorpayGateway();
$reflection = new \ReflectionClass($gw);
$secretProp = $reflection->getProperty('keySecret');
$secretProp->setAccessible(true);
$keySecret = $secretProp->getValue($gw);

$signature = hash_hmac('sha256', "{$orderId}|{$paymentId}", $keySecret);

$verifyData = [
    'booking_id'          => (string)$bookingId,
    'booking_no'          => $bookingNo,
    'service_name'        => 'Full Home Deep Cleaning',
    'razorpay_order_id'   => $orderId,
    'razorpay_payment_id' => $paymentId,
    'razorpay_signature'  => $signature,
];

$reqVerify = new Request([], $verifyData, [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book/verify-payment',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$respVerify = $ctrl->verifyPayment($reqVerify);
assert($respVerify->getStatusCode() === 200, "Verification must return 200");
$dataVerify = json_decode($respVerify->getContent(), true);

assert($dataVerify['success'] === true, "Verification response must be success");
assert($dataVerify['booking_no'] === $bookingNo, "Booking no must match in verification response");

// Verify DB updates
$dbPayment = Database::fetchOne("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1", [$bookingId]);
assert($dbPayment !== null, "Payment record must exist");
assert($dbPayment['status'] === 'paid', "Payment status must be 'paid'");
assert($dbPayment['transaction_ref'] === $paymentId, "Transaction ref must match payment ID");

$dbBooking = Booking::find($bookingId);
assert($dbBooking['priority'] === 'high', "Paid booking priority must be updated to 'high'");

$dbInvoice = Database::fetchOne("SELECT * FROM invoices WHERE payment_id = ? LIMIT 1", [$dbPayment['id']]);
assert($dbInvoice !== null, "GST Invoice must be auto-generated");
assert(str_starts_with($dbInvoice['invoice_no'], 'INV-'), "Invoice no must start with INV-");

echo "Test 2: Payment Signature Verification, DB Paid Status & Invoice Generation - PASSED\n";

// TEST 3: Invalid / Forged signature rejection
$tamperedVerifyData = $verifyData;
$tamperedVerifyData['razorpay_signature'] = 'invalid_forged_signature';

$reqTampered = new Request([], $tamperedVerifyData, [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book/verify-payment',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$respTampered = $ctrl->verifyPayment($reqTampered);
assert($respTampered->getStatusCode() === 400, "Tampered signature must return 400");
$dataTampered = json_decode($respTampered->getContent(), true);
assert($dataTampered['success'] === false, "Tampered signature response must have success=false");

echo "Test 3: Forged Signature Rejection - PASSED\n";

// TEST 4: Cash on Delivery (COD) booking submission
$postDataCod = [
    '_csrf'          => $token,
    'is_ajax'        => '1',
    'service_id'     => '1',
    'name'           => 'COD Customer',
    'phone'          => '9876543211',
    'email'          => 'cod.test@example.com',
    'address'        => '456 Market Road, Kashipur',
    'city'           => 'Kashipur',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '02:00 - 04:00 PM',
    'payment_method' => 'cod',
];

$reqCod = new Request([], $postDataCod, [], [], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/book',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
]);

$respCod = $ctrl->submit($reqCod);
assert($respCod->getStatusCode() === 200, "COD booking submission must return 200");
$dataCod = json_decode($respCod->getContent(), true);

assert($dataCod['success'] === true, "COD booking response success must be true");
assert($dataCod['requires_payment'] === false, "COD booking response must have requires_payment=false");

$codBookingId = (int)$dataCod['booking_id'];
$dbCodPayment = Database::fetchOne("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1", [$codBookingId]);
assert($dbCodPayment !== null, "COD payment record must exist");
assert($dbCodPayment['status'] === 'pending', "COD payment status must be 'pending'");
assert($dbCodPayment['method'] === 'cash', "COD payment method must be 'cash'");

echo "Test 4: Cash on Delivery (COD) Flow & Pending Payment Recording - PASSED\n";

echo "=== ALL 4 RAZORPAY & COD BOOKING PAYMENT TESTS PASSED PERFECTLY! ===\n";
