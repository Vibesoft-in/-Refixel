<?php
declare(strict_types=1);

namespace App\Services;

interface SmsProviderInterface
{
    public function sendOtp(string $phone, string $otp): bool;
    public function sendTransactional(string $phone, string $templateId, array $variables): bool;
}
