<?php
declare(strict_types=1);

namespace App\Services;

interface PaymentGatewayInterface
{
    public function createOrder(int $bookingId, float $amount, string $currency = 'INR'): array;
    public function verifyPayment(string $paymentId, string $orderId, string $signature): bool;
    public function processRefund(string $paymentId, float $amount, ?string $reason = null): array;
}
