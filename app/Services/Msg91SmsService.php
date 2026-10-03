<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Logger;

class Msg91SmsService implements SmsProviderInterface
{
    protected string $apiKey;
    protected string $senderId;

    public function __construct()
    {
        $this->apiKey   = (string)Env::get('SMS_API_KEY', '');
        $this->senderId = (string)Env::get('SMS_SENDER_ID', 'PRMOHM');
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        if (empty($this->apiKey) || Env::get('SMS_GATEWAY') === 'mock' || Env::get('APP_ENV') === 'test') {
            Logger::info("Mock SMS OTP sent to {$phone}: [{$otp}]");
            return true;
        }

        $payload = json_encode([
            'template_id' => 'otp_template',
            'mobile'      => $phone,
            'authkey'     => $this->apiKey,
            'otp'         => $otp,
        ]);

        return $this->executeCurl('https://control.msg91.com/api/v5/otp', $payload);
    }

    public function sendTransactional(string $phone, string $templateId, array $variables): bool
    {
        if (empty($this->apiKey) || Env::get('SMS_GATEWAY') === 'mock' || Env::get('APP_ENV') === 'test') {
            Logger::info("Mock SMS Transactional sent to {$phone} (Template: {$templateId})", $variables);
            return true;
        }

        $payload = json_encode([
            'template_id' => $templateId,
            'sender'      => $this->senderId,
            'short_url'   => '0',
            'recipients'  => [
                [
                    'mobiles' => $phone,
                    'vars'    => $variables,
                ]
            ],
        ]);

        return $this->executeCurl('https://control.msg91.com/api/v5/flow/', $payload);
    }

    protected function executeCurl(string $url, string $payload): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authkey: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !$response) {
            Logger::error("SMS dispatch failed (HTTP {$httpCode}): " . ($response ?: 'Empty response'));
            return false;
        }

        return true;
    }
}
