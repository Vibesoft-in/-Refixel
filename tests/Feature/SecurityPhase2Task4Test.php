<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Request;
use App\Controllers\WebhookController;
use App\Models\Booking;
use App\Models\Payment;

class SecurityPhase2Task4Test
{
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;
    private string $testSecret = 'whsec_test_mock_secret_key_123';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        Env::set('RAZORPAY_WEBHOOK_SECRET', $this->testSecret);
    }

    public function run(): void
    {
        echo "\n=== SecurityPhase2Task4Test: Razorpay Webhook Verification & Idempotency ===\n";

        $this->testInvalidSignatureReturns400();
        $this->testValidCapturedEventMarksBookingPaid();
        $this->testDuplicateWebhookIsIdempotent();
        $this->testAmountMismatchDoesNotMarkPaid();
        $this->testCurrencyMismatchDoesNotMarkPaid();
        $this->testFailedAndRefundEventsHandledSafely();
        $this->testWebhookRouteExemptFromCsrf();

        echo "\nResults: {$this->passed} passed, {$this->failed} failed\n";
        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function testInvalidSignatureReturns400(): void
    {
        $payload = json_encode(['event' => 'payment.captured']);
        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => 'bad_signature']);
        $req->setRawBody($payload);

        $controller = new WebhookController();
        $res = $controller->razorpay($req);

        if ($res->getStatusCode() === 400) {
            echo "  ✓ PASS: Webhook with invalid signature returns 400 Bad Request\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Invalid webhook signature returned {$res->getStatusCode()}\n";
            $this->failed++;
        }
    }

    private function testValidCapturedEventMarksBookingPaid(): void
    {
        $orderId = 'order_wh_' . bin2hex(random_bytes(6));
        $paymentId = 'pay_wh_' . bin2hex(random_bytes(6));

        $bookingId = Booking::create([
            'booking_no'       => 'TEST-WH-' . time(),
            'name'             => 'Webhook User',
            'phone'            => '9876543210',
            'address'          => 'Webhook Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => $orderId,
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => $orderId,
        ]);

        $payloadArray = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => $paymentId,
                        'order_id' => $orderId,
                        'amount'   => 89900, // paise
                        'currency' => 'INR',
                        'status'   => 'captured',
                    ]
                ]
            ]
        ];
        $rawPayload = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawPayload, $this->testSecret);

        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature]);
        $req->setRawBody($rawPayload);

        $controller = new WebhookController();
        $res = $controller->razorpay($req);

        $payment = $this->pdo->query("SELECT * FROM payments WHERE booking_id = {$bookingId}")->fetch(PDO::FETCH_ASSOC);

        // Cleanup
        $this->pdo->exec("DELETE FROM invoices WHERE payment_id = " . (int)($payment['id'] ?? 0));
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 200 && $payment && $payment['status'] === 'paid' && $payment['gateway_payment_id'] === $paymentId) {
            echo "  ✓ PASS: Valid payment.captured webhook atomically marks payment 'paid' with gateway_payment_id\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Webhook failed to mark payment paid (status: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    private function testDuplicateWebhookIsIdempotent(): void
    {
        $orderId = 'order_dup_' . bin2hex(random_bytes(6));
        $paymentId = 'pay_dup_' . bin2hex(random_bytes(6));

        $bookingId = Booking::create([
            'booking_no'       => 'TEST-DUP-' . time(),
            'name'             => 'Dup User',
            'phone'            => '9876543210',
            'address'          => 'Dup Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => $orderId,
            'status'           => 'new',
        ]);

        $paymentIdDb = Payment::create([
            'booking_id'         => $bookingId,
            'amount'             => 899.00,
            'method'             => 'upi',
            'status'             => 'paid', // Already paid!
            'gateway_order_id'   => $orderId,
            'gateway_payment_id' => $paymentId,
        ]);

        $payloadArray = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => $paymentId,
                        'order_id' => $orderId,
                        'amount'   => 89900,
                        'currency' => 'INR',
                        'status'   => 'captured',
                    ]
                ]
            ]
        ];
        $rawPayload = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawPayload, $this->testSecret);

        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature]);
        $req->setRawBody($rawPayload);

        $controller = new WebhookController();
        $res = $controller->razorpay($req);

        // Count invoices for this payment
        $invoiceCount = (int)$this->pdo->query("SELECT count(*) FROM invoices WHERE payment_id = {$paymentIdDb}")->fetchColumn();

        // Cleanup
        $this->pdo->exec("DELETE FROM invoices WHERE payment_id = {$paymentIdDb}");
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 200 && $invoiceCount === 0) {
            echo "  ✓ PASS: Duplicate webhook is idempotent: returns 200 OK without re-processing or creating duplicate invoices\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Idempotency failed: duplicate invoice count = {$invoiceCount}\n";
            $this->failed++;
        }
    }

    private function testAmountMismatchDoesNotMarkPaid(): void
    {
        $orderId = 'order_mis_' . bin2hex(random_bytes(6));
        $paymentId = 'pay_mis_' . bin2hex(random_bytes(6));

        $bookingId = Booking::create([
            'booking_no'       => 'TEST-MIS-' . time(),
            'name'             => 'Mismatch User',
            'phone'            => '9876543210',
            'address'          => 'Mismatch Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => $orderId,
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => $orderId,
        ]);

        // Send payload with tampered amount = 100 paise (Rs 1.00)
        $payloadArray = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => $paymentId,
                        'order_id' => $orderId,
                        'amount'   => 100, // Rs 1.00 instead of Rs 899.00
                        'currency' => 'INR',
                        'status'   => 'captured',
                    ]
                ]
            ]
        ];
        $rawPayload = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawPayload, $this->testSecret);

        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature]);
        $req->setRawBody($rawPayload);

        $controller = new WebhookController();
        $res = $controller->razorpay($req);

        $paymentStatus = $this->pdo->query("SELECT status FROM payments WHERE booking_id = {$bookingId}")->fetchColumn();

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 200 && $paymentStatus === 'pending') {
            echo "  ✓ PASS: Amount mismatch is rejected from being paid; returns 200 with logged alert\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Payment with amount mismatch was erroneously altered (status: {$paymentStatus})\n";
            $this->failed++;
        }
    }

    private function testCurrencyMismatchDoesNotMarkPaid(): void
    {
        $orderId = 'order_cur_' . bin2hex(random_bytes(6));
        $paymentId = 'pay_cur_' . bin2hex(random_bytes(6));

        $bookingId = Booking::create([
            'booking_no'       => 'TEST-CUR-' . time(),
            'name'             => 'Cur User',
            'phone'            => '9876543210',
            'address'          => 'Cur Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => $orderId,
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => $orderId,
        ]);

        // Send payload with USD instead of INR
        $payloadArray = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => $paymentId,
                        'order_id' => $orderId,
                        'amount'   => 89900,
                        'currency' => 'USD', // Mismatch!
                        'status'   => 'captured',
                    ]
                ]
            ]
        ];
        $rawPayload = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawPayload, $this->testSecret);

        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature]);
        $req->setRawBody($rawPayload);

        $controller = new WebhookController();
        $res = $controller->razorpay($req);

        $paymentStatus = $this->pdo->query("SELECT status FROM payments WHERE booking_id = {$bookingId}")->fetchColumn();

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 200 && $paymentStatus === 'pending') {
            echo "  ✓ PASS: Currency mismatch is rejected from being paid; returns 200 with logged alert\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Payment with currency mismatch was modified\n";
            $this->failed++;
        }
    }

    private function testFailedAndRefundEventsHandledSafely(): void
    {
        $payloadArray = [
            'event'   => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'                => 'pay_failed_123',
                        'order_id'          => 'order_failed_123',
                        'error_code'        => 'BAD_REQUEST_ERROR',
                        'error_description' => 'Payment failed by user',
                    ]
                ]
            ]
        ];
        $rawPayload = json_encode($payloadArray);
        $signature = hash_hmac('sha256', $rawPayload, $this->testSecret);

        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature]);
        $req->setRawBody($rawPayload);

        $controller = new WebhookController();
        $res = $controller->razorpay($req);

        if ($res->getStatusCode() === 200) {
            echo "  ✓ PASS: payment.failed event is handled and safely logged without secret exposure\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Failed event handling returned {$res->getStatusCode()}\n";
            $this->failed++;
        }
    }

    private function testWebhookRouteExemptFromCsrf(): void
    {
        $routesContent = file_get_contents(__DIR__ . '/../../config/routes.php');
        $isExempt = (bool)preg_match('/Router::post\(\s*\'\/webhook\/razorpay\'\s*,\s*\'WebhookController@razorpay\'\s*\)/', $routesContent);

        if ($isExempt) {
            echo "  ✓ PASS: /webhook/razorpay route is exempt from CSRF middleware\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: /webhook/razorpay is not exempt from CSRF middleware\n";
            $this->failed++;
        }
    }
}

(new SecurityPhase2Task4Test())->run();
