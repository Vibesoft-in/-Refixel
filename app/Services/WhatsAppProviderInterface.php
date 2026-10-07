<?php
declare(strict_types=1);

namespace App\Services;

interface WhatsAppProviderInterface
{
    public function sendTemplateMessage(string $phone, string $templateName, array $parameters): bool;
}
