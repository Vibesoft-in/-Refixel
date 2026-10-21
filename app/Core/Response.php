<?php
declare(strict_types=1);

namespace App\Core;

class Response
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected string $content = '';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getBody(): string
    {
        return $this->content;
    }

    public static function buildSecurityHeaders(): array
    {
        $reportOnly = (bool)Env::get('CSP_REPORT_ONLY', true);
        $cspHeader = $reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';

        $cspPolicy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://checkout.razorpay.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://api.razorpay.com https://lumberjack.razorpay.com",
            "frame-src 'self' https://api.razorpay.com https://maps.google.com https://www.google.com",
            "frame-ancestors 'self'",
            "base-uri 'self'",
        ]);

        $headers = [
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection'       => '1; mode=block',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'geolocation=(self), microphone=(), camera=()',
            $cspHeader               => $cspPolicy,
        ];

        if (Request::isHttpsConnection()) {
            // Strict-Transport-Security WITHOUT preload (as explicitly requested by user)
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        return $headers;
    }

    public function applySecurityHeaders(): self
    {
        $defaultHeaders = self::buildSecurityHeaders();
        foreach ($defaultHeaders as $name => $value) {
            if (!isset($this->headers[$name])) {
                $this->headers[$name] = $value;
            }
        }

        return $this;
    }

    public function send(): void
    {
        $this->applySecurityHeaders();

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }
        echo $this->content;
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $response = new self();
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'application/json; charset=utf-8');
        $response->setContent(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $response;
    }

    public static function xml(string $xml, int $status = 200): self
    {
        $response = new self();
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->setContent($xml);
        return $response;
    }

    public static function plain(string $text, int $status = 200): self
    {
        $response = new self();
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent($text);
        return $response;
    }

    public static function html(string $content, int $status = 200): self
    {
        $response = new self();
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'text/html; charset=utf-8');
        $response->setContent($content);
        return $response;
    }

    public static function redirect(string $url, int $status = 302): self
    {
        // Handle relative vs absolute routing in subfolder
        $appUrl = rtrim((string)Env::get('APP_URL', ''), '/');
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $basePath = parse_url($appUrl, PHP_URL_PATH) ?? '';
            if ($basePath !== '' && !str_starts_with($url, $basePath)) {
                $url = rtrim($basePath, '/') . $url;
            }
        }

        $response = new self();
        $response->setStatusCode($status);
        $response->setHeader('Location', $url);
        return $response;
    }
}
