<?php
declare(strict_types=1);

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create an order with the payment gateway provider.
     * Returns array with order_id, amount, currency, and provider reference.
     */
    public function createOrder(int $bookingId, float $amount, string $currency = 'INR', array $metadata = []): array;

    /**
     * Verify payment signature / webhook callback integrity.
     */
    public function verifySignature(array $payload): bool;

    /**
     * Issue refund through the payment gateway.
     */
    public function refund(string $paymentRef, float $amount, ?string $reason = null): array;

    /**
     * Get provider name identifier.
     */
    public function getProviderName(): string;
}
