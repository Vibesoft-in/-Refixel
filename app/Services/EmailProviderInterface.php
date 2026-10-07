<?php
declare(strict_types=1);

namespace App\Services;

interface EmailProviderInterface
{
    /**
     * Send an email with subject and HTML body.
     */
    public function send(string $to, string $subject, string $htmlBody, array $headers = []): bool;

    /**
     * Send a templated email with payload data.
     */
    public function sendTemplate(string $to, string $template, array $data = []): bool;
}
