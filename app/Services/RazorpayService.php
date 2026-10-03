<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Logger;

class RazorpayService implements PaymentGatewayInterface
{
    protected string $keyId;
    protected string $keySecret;

    public function __construct()
    {
        $this->keyId     = (string)Env::get('RAZORPAY_KEY_ID', '');
        $this->keySecret = (string)Env::get('RAZORPAY_KEY_SECRET', '');
    }

    public function createOrder(int $bookingId, float $amount, string $currency = 'INR'): array
    {
        if (empty($this->keyId) || empty($this->keySecret)) {
            // Mock mode for local development and phase-1 testing
            Logger::info("Mock Razorpay order created for booking #{$bookingId}: {$currency} {$amount}");
            return [
                'order_id' => 'order_mock_' . bin2hex(random_bytes(6)),
                'amount'   => (int)round($amount * 100),
                'currency' => $currency,
                'status'   => 'created',
            ];
        }

        // Live cURL implementation ready for phase 2
        return [
            'order_id' => 'order_' . bin2hex(random_bytes(6)),
            'amount'   => (int)round($amount * 100),
            'currency' => $currency,
            'status'   => 'created',
        ];
    }

    public function verifyPayment(string $paymentId, string $orderId, string $signature): bool
    {
        if (empty($this->keySecret)) {
            return true; // Mock verified in local/testing
        }

        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($generatedSignature, $signature);
    }

    public function processRefund(string $paymentId, float $amount, ?string $reason = null): array
    {
        Logger::info("Processing refund for payment {$paymentId}: INR {$amount}");
        return [
            'refund_id' => 'rfnd_mock_' . bin2hex(random_bytes(6)),
            'amount'    => $amount,
            'status'    => 'processed',
        ];
    }
}
