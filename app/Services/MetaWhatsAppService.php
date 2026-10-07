<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Logger;

class MetaWhatsAppService implements WhatsAppProviderInterface
{
    protected string $phoneId;
    protected string $accessToken;

    public function __construct()
    {
        $this->phoneId     = (string)Env::get('WHATSAPP_PHONE_ID', '');
        $this->accessToken = (string)Env::get('WHATSAPP_ACCESS_TOKEN', '');
    }

    public function sendTemplateMessage(string $phone, string $templateName, array $parameters): bool
    {
        if (empty($this->accessToken) || empty($this->phoneId) || !Env::get('WHATSAPP_ENABLED', false) || Env::get('APP_ENV') === 'test') {
            Logger::info("Mock WhatsApp template message sent to {$phone}: [{$templateName}]", $parameters);
            return true;
        }

        $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($formattedPhone) === 10) {
            $formattedPhone = '91' . $formattedPhone;
        }

        $paramsArray = [];
        foreach ($parameters as $p) {
            $paramsArray[] = [
                'type' => 'text',
                'text' => (string)$p,
            ];
        }

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to'                => $formattedPhone,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => 'en'],
                'components' => [
                    [
                        'type'       => 'body',
                        'parameters' => $paramsArray,
                    ]
                ],
            ],
        ]);

        $url = "https://graph.facebook.com/v19.0/{$this->phoneId}/messages";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !$response) {
            Logger::error("WhatsApp dispatch failed (HTTP {$httpCode}): " . ($response ?: 'Empty response'));
            return false;
        }

        return true;
    }
}
