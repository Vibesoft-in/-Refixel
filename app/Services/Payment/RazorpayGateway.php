<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Core\Env;
use RuntimeException;

class RazorpayGateway implements PaymentGatewayInterface
{
    protected string $keyId;
    protected string $keySecret;
    protected string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct(?string $keyId = null, ?string $keySecret = null)
    {
        $this->keyId = $keyId ?? (string)Env::get('RAZORPAY_KEY_ID', 'rzp_test_placeholder');
        $this->keySecret = $keySecret ?? (string)Env::get('RAZORPAY_KEY_SECRET', 'secret_placeholder');
    }

    public function getProviderName(): string
    {
        return 'razorpay';
    }

    public function createOrder(int $bookingId, float $amount, string $currency = 'INR', array $metadata = []): array
    {
        $amountInPaise = (int)round($amount * 100);
        $receipt = 'BK-' . $bookingId . '-' . time();

        // If credentials are test/placeholder/mock, generate mock order response
        if (str_contains($this->keyId, 'placeholder') || str_contains($this->keyId, 'mock') || empty($this->keyId)) {
            return [
                'order_id' => 'order_' . bin2hex(random_bytes(8)),
                'amount'   => $amount,
                'currency' => $currency,
                'provider' => $this->getProviderName(),
                'receipt'  => $receipt,
                'status'   => 'created',
            ];
        }

        // Live cURL request to Razorpay Orders API
        $payload = json_encode([
            'amount'   => $amountInPaise,
            'currency' => $currency,
            'receipt'  => $receipt,
            'notes'    => $metadata,
        ]);

        $ch = curl_init("{$this->baseUrl}/orders");
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->keyId}:{$this->keySecret}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !$response) {
            throw new RuntimeException("Razorpay order creation failed (HTTP {$httpCode}): " . ($response ?: 'Empty response'));
        }

        $data = json_decode($response, true);
        return [
            'order_id' => $data['id'] ?? '',
            'amount'   => $amount,
            'currency' => $currency,
            'provider' => $this->getProviderName(),
            'receipt'  => $receipt,
            'status'   => $data['status'] ?? 'created',
        ];
    }

    public function verifySignature(array $payload): bool
    {
        $orderId = $payload['razorpay_order_id'] ?? '';
        $paymentId = $payload['razorpay_payment_id'] ?? '';
        $signature = $payload['razorpay_signature'] ?? '';

        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', "{$orderId}|{$paymentId}", $this->keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    public function refund(string $paymentRef, float $amount, ?string $reason = null): array
    {
        $amountInPaise = (int)round($amount * 100);

        if (str_contains($this->keyId, 'placeholder') || str_contains($this->keyId, 'mock') || empty($this->keyId)) {
            return [
                'refund_id' => 'rfnd_' . bin2hex(random_bytes(8)),
                'amount'    => $amount,
                'status'    => 'processed',
                'provider'  => $this->getProviderName(),
            ];
        }

        $payload = json_encode([
            'amount' => $amountInPaise,
            'notes'  => ['reason' => $reason ?? 'Customer cancellation refund'],
        ]);

        $ch = curl_init("{$this->baseUrl}/payments/{$paymentRef}/refund");
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->keyId}:{$this->keySecret}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !$response) {
            throw new RuntimeException("Razorpay refund failed (HTTP {$httpCode}): " . ($response ?: 'Empty response'));
        }

        $data = json_decode($response, true);
        return [
            'refund_id' => $data['id'] ?? '',
            'amount'    => $amount,
            'status'    => $data['status'] ?? 'processed',
            'provider'  => $this->getProviderName(),
        ];
    }
}
