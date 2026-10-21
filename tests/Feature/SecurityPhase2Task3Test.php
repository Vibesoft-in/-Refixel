<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\RazorpayGateway;

class SecurityPhase2Task3Test
{
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function run(): void
    {
        echo "\n=== SecurityPhase2Task3Test: Payment Verification & CSRF Security ===\n";

        $this->testInvalidSignatureRejectsPayment();
        $this->testAmountMismatchRejectsPayment();
        $this->testUnauthorizedOwnershipRejection();
        $this->testValidSignatureAndOwnershipMarksPaid();
        $this->testRouteProtectedByCsrf();

        echo "\nResults: {$this->passed} passed, {$this->failed} failed\n";
        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function testInvalidSignatureRejectsPayment(): void
    {
        $guestToken = bin2hex(random_bytes(16));
        $bookingId = Booking::create([
            'booking_no'       => 'TEST-SIG-' . time(),
            'guest_token'      => $guestToken,
            'name'             => 'Test Sig User',
            'phone'            => '9876543210',
            'address'          => 'Test Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => 'order_test_sig_123',
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => 'order_test_sig_123',
        ]);

        // Submit invalid signature
        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => 'order_test_sig_123',
            'razorpay_payment_id' => 'pay_test_123',
            'razorpay_signature'  => 'tampered_invalid_signature',
            'guest_token'         => $guestToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        // Verify payment is NOT marked paid
        $paymentStatus = $this->pdo->query("SELECT status FROM payments WHERE booking_id = {$bookingId}")->fetchColumn();

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 400 && $paymentStatus === 'pending') {
            echo "  ✓ PASS: Invalid signature correctly rejected (HTTP 400) and payment remains pending\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Invalid signature was not rejected as expected (Status: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    private function testAmountMismatchRejectsPayment(): void
    {
        $guestToken = bin2hex(random_bytes(16));
        $bookingId = Booking::create([
            'booking_no'       => 'TEST-AMT-' . time(),
            'guest_token'      => $guestToken,
            'name'             => 'Test Amt User',
            'phone'            => '9876543210',
            'address'          => 'Test Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => 'order_test_amt_123',
            'status'           => 'new',
        ]);

        // Pending payment recorded with different amount (e.g. 500 instead of 899)
        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 500.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => 'order_test_amt_123',
        ]);

        // Valid signature for order_test_amt_123 & pay_test_amt
        $secret = (string)\App\Core\Env::get('RAZORPAY_KEY_SECRET', 'secret_placeholder');
        $validSig = hash_hmac('sha256', 'order_test_amt_123|pay_test_amt', $secret);

        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => 'order_test_amt_123',
            'razorpay_payment_id' => 'pay_test_amt',
            'razorpay_signature'  => $validSig,
            'guest_token'         => $guestToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        $paymentStatus = $this->pdo->query("SELECT status FROM payments WHERE booking_id = {$bookingId}")->fetchColumn();

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 400 && $paymentStatus === 'pending') {
            echo "  ✓ PASS: Amount mismatch correctly rejected (HTTP 400) and payment remains pending\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Amount mismatch was not rejected properly (Status: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    private function testUnauthorizedOwnershipRejection(): void
    {
        $victimToken = bin2hex(random_bytes(16));
        $attackerToken = bin2hex(random_bytes(16));

        $existingUserId = (int)$this->pdo->query("SELECT id FROM users WHERE role = 'customer' LIMIT 1")->fetchColumn();
        if ($existingUserId <= 0) {
            $existingUserId = (int)$this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
        }

        $bookingId = Booking::create([
            'booking_no'       => 'TEST-IDOR-' . time(),
            'guest_token'      => $victimToken,
            'customer_id'      => $existingUserId, // Another customer
            'name'             => 'Victim User',
            'phone'            => '9876543210',
            'address'          => 'Victim Address',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => 'order_test_idor_123',
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => 'order_test_idor_123',
        ]);

        // Attempt verification with attacker token and not logged in as victim
        unset($_SESSION['user_id'], $_SESSION['user'], $_SESSION['guest_account_created_booking_id']);

        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => 'order_test_idor_123',
            'razorpay_payment_id' => 'pay_test_idor',
            'razorpay_signature'  => 'any_sig',
            'guest_token'         => $attackerToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 403) {
            echo "  ✓ PASS: Unauthorized verification attempt strictly blocked (HTTP 403 Forbidden)\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: IDOR unauthorized access was not blocked (Status: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    private function testValidSignatureAndOwnershipMarksPaid(): void
    {
        $guestToken = bin2hex(random_bytes(16));
        $orderId = 'order_valid_' . bin2hex(random_bytes(6));
        $paymentId = 'pay_valid_' . bin2hex(random_bytes(6));

        $bookingId = Booking::create([
            'booking_no'       => 'TEST-OK-' . time(),
            'guest_token'      => $guestToken,
            'name'             => 'Legit User',
            'phone'            => '9876543210',
            'address'          => 'Legit Address',
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

        $gateway = new RazorpayGateway();
        $reflector = new ReflectionClass($gateway);
        $prop = $reflector->getProperty('keySecret');
        $prop->setAccessible(true);
        $secret = $prop->getValue($gateway);
        $validSig = hash_hmac('sha256', "{$orderId}|{$paymentId}", $secret);

        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature'  => $validSig,
            'guest_token'         => $guestToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        $payment = $this->pdo->query("SELECT * FROM payments WHERE booking_id = {$bookingId}")->fetch(PDO::FETCH_ASSOC);

        // Cleanup
        $this->pdo->exec("DELETE FROM invoices WHERE payment_id = " . (int)($payment['id'] ?? 0));
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 200 && $payment && $payment['status'] === 'paid' && $payment['gateway_payment_id'] === $paymentId) {
            echo "  ✓ PASS: Valid signature & ownership transitions payment to 'paid' with gateway_payment_id\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Payment was not marked paid properly\n";
            $this->failed++;
        }
    }

    private function testRouteProtectedByCsrf(): void
    {
        $routesContent = file_get_contents(__DIR__ . '/../../config/routes.php');
        $hasCsrf = (bool)preg_match('/Router::post\(\s*\'\/book\/verify-payment\'\s*,\s*\'BookingController@verifyPayment\'\s*,\s*\[\s*\'VerifyCsrf\'\s*\]\s*\)/', $routesContent);

        if ($hasCsrf) {
            echo "  ✓ PASS: /book/verify-payment is guarded by VerifyCsrf middleware in routes.php\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: /book/verify-payment is missing VerifyCsrf middleware\n";
            $this->failed++;
        }
    }
}

(new SecurityPhase2Task3Test())->run();
