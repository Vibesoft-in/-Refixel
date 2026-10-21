<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Controllers\WebhookController;
use App\Controllers\SuperAdmin\GatewayController;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Services\Payment\RazorpayGateway;

class SecurityPhase2MasterTest
{
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;
    private string $webhookSecret = 'whsec_master_test_secret_999';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        Env::set('RAZORPAY_WEBHOOK_SECRET', $this->webhookSecret);
    }

    public function run(): void
    {
        echo "====================================================================\n";
        echo "      SECURITY PHASE 2 MASTER TEST SUITE: PAYMENT & AUTH CLEANUP   \n";
        echo "====================================================================\n\n";

        $this->testScenario1_GalatSignaturePaymentReject();
        $this->testScenario2_AmountMismatchBookingNotPaid();
        $this->testScenario3_DuplicateWebhookNoDoubleConfirm();
        $this->testScenario4_ClientAmountIgnored();
        $this->testScenario5_UnauthorizedOwnershipBlocked();
        $this->testScenario6_KeySecretNeverSentToFrontend();

        echo "\n--------------------------------------------------------------------\n";
        echo "Master Test Results: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "--------------------------------------------------------------------\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    /**
     * 1. Galat signature par payment reject hota hai
     */
    private function testScenario1_GalatSignaturePaymentReject(): void
    {
        echo "[SCENARIO 1] Verifying invalid signature rejection...\n";
        $guestToken = bin2hex(random_bytes(16));
        $bookingId = Booking::create([
            'booking_no'       => 'MTR-SIG-' . time(),
            'guest_token'      => $guestToken,
            'name'             => 'Scenario 1 User',
            'phone'            => '9876543210',
            'address'          => 'Test St',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => 'order_mtr_sig_1',
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => 'order_mtr_sig_1',
        ]);

        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => 'order_mtr_sig_1',
            'razorpay_payment_id' => 'pay_mtr_sig_1',
            'razorpay_signature'  => 'forged_invalid_signature_xyz',
            'guest_token'         => $guestToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        $payment = $this->pdo->query("SELECT status FROM payments WHERE booking_id = {$bookingId}")->fetch(PDO::FETCH_ASSOC);

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 400 && $payment['status'] === 'pending') {
            echo "  ✓ PASS: Bad signature returned HTTP 400 and status remains 'pending'\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Bad signature was not rejected with HTTP 400 (Got: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    /**
     * 2. Amount mismatch par booking Paid nahi hoti
     */
    private function testScenario2_AmountMismatchBookingNotPaid(): void
    {
        echo "[SCENARIO 2] Verifying amount mismatch rejection...\n";
        $guestToken = bin2hex(random_bytes(16));
        $bookingId = Booking::create([
            'booking_no'       => 'MTR-AMT-' . time(),
            'guest_token'      => $guestToken,
            'name'             => 'Scenario 2 User',
            'phone'            => '9876543210',
            'address'          => 'Test St',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => 'order_mtr_amt_1',
            'status'           => 'new',
        ]);

        // Pending payment recorded with Rs 499 instead of expected Rs 899
        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 499.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => 'order_mtr_amt_1',
        ]);

        $gateway = new RazorpayGateway();
        $reflector = new ReflectionClass($gateway);
        $prop = $reflector->getProperty('keySecret');
        $prop->setAccessible(true);
        $secret = $prop->getValue($gateway);
        $validSig = hash_hmac('sha256', 'order_mtr_amt_1|pay_mtr_amt_1', $secret);

        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => 'order_mtr_amt_1',
            'razorpay_payment_id' => 'pay_mtr_amt_1',
            'razorpay_signature'  => $validSig,
            'guest_token'         => $guestToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        $payment = $this->pdo->query("SELECT status FROM payments WHERE booking_id = {$bookingId}")->fetch(PDO::FETCH_ASSOC);

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 400 && $payment['status'] === 'pending') {
            echo "  ✓ PASS: Amount mismatch rejected with HTTP 400 and payment remains 'pending'\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Amount mismatch did not reject with 400 (Got: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    /**
     * 3. Duplicate webhook se double confirm nahi hota
     */
    private function testScenario3_DuplicateWebhookNoDoubleConfirm(): void
    {
        echo "[SCENARIO 3] Verifying duplicate webhook idempotency...\n";
        $orderId = 'order_mtr_dup_' . bin2hex(random_bytes(6));
        $paymentId = 'pay_mtr_dup_' . bin2hex(random_bytes(6));

        $bookingId = Booking::create([
            'booking_no'       => 'MTR-DUP-' . time(),
            'name'             => 'Scenario 3 User',
            'phone'            => '9876543210',
            'address'          => 'Test St',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => $orderId,
            'status'           => 'new',
        ]);

        // Booking is ALREADY marked paid by prior verify-payment or initial webhook
        $paymentIdDb = Payment::create([
            'booking_id'         => $bookingId,
            'amount'             => 899.00,
            'method'             => 'upi',
            'status'             => 'paid',
            'gateway_order_id'   => $orderId,
            'gateway_payment_id' => $paymentId,
        ]);

        $payload = json_encode([
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
        ]);
        $sig = hash_hmac('sha256', $payload, $this->webhookSecret);

        $req = new Request([], [], ['REQUEST_METHOD' => 'POST', 'HTTP_X_RAZORPAY_SIGNATURE' => $sig]);
        $req->setRawBody($payload);

        $webhookController = new WebhookController();
        $res = $webhookController->razorpay($req);

        // Verify no duplicate invoice was generated
        $invoiceCount = (int)$this->pdo->query("SELECT count(*) FROM invoices WHERE payment_id = {$paymentIdDb}")->fetchColumn();

        // Cleanup
        $this->pdo->exec("DELETE FROM invoices WHERE payment_id = {$paymentIdDb}");
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 200 && $invoiceCount === 0) {
            echo "  ✓ PASS: Duplicate webhook acknowledged (200 OK) with 0 duplicate invoices and no re-confirmation\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Duplicate webhook not idempotent (invoiceCount: {$invoiceCount})\n";
            $this->failed++;
        }
    }

    /**
     * 4. Client ka amount ignore hota hai
     */
    private function testScenario4_ClientAmountIgnored(): void
    {
        echo "[SCENARIO 4] Verifying client-supplied amount is completely ignored...\n";
        $service = Service::find(1);
        $expectedPrice = $service ? (float)$service['starting_price'] : 899.00;

        // Tamper attempt: Client sends amount = 1.00
        $req = new Request([], [
            'name'           => 'Client Amount Tamper User',
            'phone'          => '9876543210',
            'address'        => 'Tamper Test Address',
            'city'           => 'Dehradun',
            'preferred_date' => date('Y-m-d', strtotime('+2 days')),
            'preferred_time' => '10:00 AM - 12:00 PM',
            'service_id'     => 1,
            'payment_method' => 'online',
            'amount'         => 1.00, // Malicious attempt to force Rs 1 payment
            'is_ajax'        => 1,
        ], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $controller = new BookingController();
        $res = $controller->submit($req);
        $data = json_decode($res->getContent(), true);

        $bId = (int)($data['booking_id'] ?? 0);
        $savedAdvance = (float)$this->pdo->query("SELECT advance_amount FROM bookings WHERE id = {$bId}")->fetchColumn();

        // Cleanup
        if ($bId > 0) {
            $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bId}");
            $this->pdo->exec("DELETE FROM booking_items WHERE booking_id = {$bId}");
            $this->pdo->exec("DELETE FROM bookings WHERE id = {$bId}");
        }

        if ($savedAdvance === $expectedPrice && $savedAdvance !== 1.00) {
            echo "  ✓ PASS: Client amount (Rs 1.00) strictly ignored; booking advance saved as Rs {$savedAdvance}\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Client amount was not ignored (Saved advance: Rs {$savedAdvance})\n";
            $this->failed++;
        }
    }

    /**
     * 5. Kisi aur ki booking ka payment verify nahi ho sakta
     */
    private function testScenario5_UnauthorizedOwnershipBlocked(): void
    {
        echo "[SCENARIO 5] Verifying IDOR unauthorized booking verification protection...\n";
        $victimToken = bin2hex(random_bytes(16));
        $attackerToken = bin2hex(random_bytes(16));

        $validUserId = (int)$this->pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

        $bookingId = Booking::create([
            'booking_no'       => 'MTR-IDOR-' . time(),
            'guest_token'      => $victimToken,
            'customer_id'      => $validUserId,
            'name'             => 'Victim User',
            'phone'            => '9876543210',
            'address'          => 'Victim House',
            'service_id'       => 1,
            'total_amount'     => 899.00,
            'advance_amount'   => 899.00,
            'due_amount'       => 0.00,
            'payment_type'     => 'online',
            'gateway_order_id' => 'order_mtr_idor_1',
            'status'           => 'new',
        ]);

        Payment::create([
            'booking_id'       => $bookingId,
            'amount'           => 899.00,
            'method'           => 'upi',
            'status'           => 'pending',
            'gateway_order_id' => 'order_mtr_idor_1',
        ]);

        // Attempt verification using attacker's guest token and no login
        unset($_SESSION['user_id'], $_SESSION['user'], $_SESSION['guest_account_created_booking_id']);

        $req = new Request([], [
            'booking_id'          => $bookingId,
            'razorpay_order_id'   => 'order_mtr_idor_1',
            'razorpay_payment_id' => 'pay_mtr_idor_1',
            'razorpay_signature'  => 'any_signature',
            'guest_token'         => $attackerToken,
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $controller = new BookingController();
        $res = $controller->verifyPayment($req);

        // Cleanup
        $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bookingId}");
        $this->pdo->exec("DELETE FROM bookings WHERE id = {$bookingId}");

        if ($res->getStatusCode() === 403) {
            echo "  ✓ PASS: Cross-account payment verification blocked with HTTP 403 Forbidden\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Cross-account payment verification did not return 403 (Got: {$res->getStatusCode()})\n";
            $this->failed++;
        }
    }

    /**
     * 6. key_secret frontend ko nahi jata
     */
    private function testScenario6_KeySecretNeverSentToFrontend(): void
    {
        echo "[SCENARIO 6] Verifying key_secret is NEVER sent to frontend, responses, or views...\n";

        // Check 1: Checkout response
        $req = new Request([], [
            'name'           => 'Frontend Secret Check User',
            'phone'          => '9876543210',
            'address'        => 'Checkout Address',
            'city'           => 'Dehradun',
            'preferred_date' => date('Y-m-d', strtotime('+2 days')),
            'preferred_time' => '10:00 AM - 12:00 PM',
            'service_id'     => 1,
            'payment_method' => 'online',
            'is_ajax'        => 1,
        ], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $controller = new BookingController();
        $res = $controller->submit($req);
        $body = $res->getContent();
        $data = json_decode($body, true);

        $bId = (int)($data['booking_id'] ?? 0);
        if ($bId > 0) {
            $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bId}");
            $this->pdo->exec("DELETE FROM booking_items WHERE booking_id = {$bId}");
            $this->pdo->exec("DELETE FROM bookings WHERE id = {$bId}");
        }

        $checkoutLeaksSecret = isset($data['key_secret'])
            || isset($data['razorpay_key_secret'])
            || str_contains($body, 'key_secret')
            || str_contains($body, 'webhook_secret');

        // Check 2: Gateway index view
        $superAdmin = User::findBy('role', 'super_admin');
        $_SESSION['user'] = $superAdmin;
        $gwController = new GatewayController();
        $gwRes = $gwController->index(new Request([]));
        $gwHtml = $gwRes->getContent();

        $viewLeaksSecret = (bool)preg_match('/name=["\']key_secret["\']\s+value=["\'][^"\']+["\']/', $gwHtml)
            || str_contains($gwHtml, 'toggleMask');

        if (!$checkoutLeaksSecret && !$viewLeaksSecret) {
            echo "  ✓ PASS: key_secret is 100% confined to backend server and never sent to frontend\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: key_secret detected in checkout response or gateway view\n";
            $this->failed++;
        }
    }
}

(new SecurityPhase2MasterTest())->run();
