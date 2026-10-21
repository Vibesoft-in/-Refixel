<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    protected string $method;
    protected string $path;
    protected array $query;
    protected array $post;
    protected array $files;
    protected array $server;
    protected ?array $json = null;

    public function __construct(?array $query = null, ?array $post = null, ?array $server = null, ?array $files = null)
    {
        $this->server = $server ?? $_SERVER;
        $this->query  = $query ?? $_GET;
        $this->post   = $post ?? $_POST;
        $this->files  = $files ?? $_FILES;


        // Method detection and spoofing
        $rawMethod = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($rawMethod === 'POST' && isset($this->post['_method'])) {
            $this->method = strtoupper((string)$this->post['_method']);
        } else {
            $this->method = $rawMethod;
        }

        // Clean URI path extraction
        $rawUri = $this->server['REQUEST_URI'] ?? '/';
        $pathOnly = parse_url($rawUri, PHP_URL_PATH) ?? '/';

        // Strip subfolder prefix if hosted in a subfolder (e.g. /website/public or /website)
        $scriptName = str_replace('\\', '/', $this->server['SCRIPT_NAME'] ?? '');
        $publicDir  = dirname($scriptName);
        $rootDir    = dirname($publicDir);

        if ($publicDir !== '/' && $publicDir !== '.' && str_starts_with($pathOnly, $publicDir)) {
            $pathOnly = substr($pathOnly, strlen($publicDir));
        } elseif ($rootDir !== '/' && $rootDir !== '.' && str_starts_with($pathOnly, $rootDir)) {
            $pathOnly = substr($pathOnly, strlen($rootDir));
        }

        // Strip /public if still present
        if (str_starts_with($pathOnly, '/public')) {
            $pathOnly = substr($pathOnly, 7);
        }

        $this->path = '/' . trim($pathOnly, '/');
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        $uri = $this->path;
        if (!empty($this->query)) {
            $uri .= '?' . http_build_query($this->query);
        }
        return $uri;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function all(): array
    {
        if ($this->isJson()) {
            return array_merge($this->query, $this->getJsonData());
        }
        return array_merge($this->query, $this->post);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        $all = $this->all();
        return array_key_exists($key, $all);
    }

    public function set(string $key, mixed $value): void
    {
        $this->post[$key] = $value;
        if ($this->json !== null && is_array($this->json)) {
            $this->json[$key] = $value;
        }
    }

    public function merge(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->set((string)$k, $v);
        }
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && !empty($this->files[$key]['tmp_name']);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function isJson(): bool
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        return str_contains(strtolower($contentType), 'application/json');
    }

    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' || $this->isJson();
    }

    public function getJsonData(): array
    {
        if ($this->json === null) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $this->json = is_array($decoded) ? $decoded : [];
        }
        return $this->json;
    }

    public static function ipMatchesCidr(string $ip, string $cidr): bool
    {
        $cidr = trim($cidr);
        if ($cidr === '') {
            return false;
        }

        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr, 2);
        $mask = (int)$mask;

        // IPv4 CIDR matching
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($mask < 0 || $mask > 32) return false;
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $bitmask = $mask === 0 ? 0 : (~0 << (32 - $mask));
            return ($ipLong & $bitmask) === ($subnetLong & $bitmask);
        }

        // IPv6 CIDR matching
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if ($mask < 0 || $mask > 128) return false;
            $ipBytes = inet_pton($ip);
            $subnetBytes = inet_pton($subnet);
            if ($ipBytes === false || $subnetBytes === false) return false;

            $bytes = (int)($mask / 8);
            $bits = $mask % 8;

            if ($bytes > 0 && strncmp($ipBytes, $subnetBytes, $bytes) !== 0) {
                return false;
            }

            if ($bits > 0 && isset($ipBytes[$bytes], $subnetBytes[$bytes])) {
                $charMask = ~(0xff >> $bits);
                return (ord($ipBytes[$bytes]) & $charMask) === (ord($subnetBytes[$bytes]) & $charMask);
            }

            return true;
        }

        return false;
    }

    public function isFromTrustedProxy(?string $remoteAddr = null): bool
    {
        $remoteAddr = $remoteAddr ?? ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
        $rawProxies = (string)Env::get('TRUSTED_PROXIES', '');
        if ($rawProxies === '') {
            return false;
        }

        $proxies = array_filter(array_map('trim', explode(',', $rawProxies)));
        foreach ($proxies as $proxy) {
            if (self::ipMatchesCidr($remoteAddr, $proxy)) {
                return true;
            }
        }
        return false;
    }

    public function getIp(): string
    {
        $remoteAddr = $this->server['REMOTE_ADDR'] ?? '127.0.0.1';

        // Only trust client IP headers if request originated from an explicitly trusted proxy
        if ($this->isFromTrustedProxy($remoteAddr)) {
            // Cloudflare header
            if (!empty($this->server['HTTP_CF_CONNECTING_IP'])) {
                $cfIp = trim((string)$this->server['HTTP_CF_CONNECTING_IP']);
                if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
                    return $cfIp;
                }
            }

            // Standard X-Forwarded-For (take leftmost valid client IP)
            if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
                $parts = explode(',', (string)$this->server['HTTP_X_FORWARDED_FOR']);
                foreach ($parts as $part) {
                    $ip = trim($part);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }

            if (!empty($this->server['HTTP_CLIENT_IP'])) {
                $clientIp = trim((string)$this->server['HTTP_CLIENT_IP']);
                if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                    return $clientIp;
                }
            }
        }

        return $remoteAddr;
    }

    public function isHttps(): bool
    {
        if (!empty($this->server['HTTPS']) && strtolower((string)$this->server['HTTPS']) !== 'off') {
            return true;
        }

        if (isset($this->server['SERVER_PORT']) && (int)$this->server['SERVER_PORT'] === 443) {
            return true;
        }

        // X-Forwarded-Proto is ONLY trusted if coming from a trusted proxy
        $remoteAddr = $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->isFromTrustedProxy($remoteAddr)) {
            $proto = strtolower((string)($this->server['HTTP_X_FORWARDED_PROTO'] ?? ''));
            if ($proto === 'https') {
                return true;
            }
        }

        return false;
    }

    public function getUserAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    protected ?string $rawBody = null;

    public function getRawBody(): string
    {
        if ($this->rawBody === null) {
            $this->rawBody = (string)file_get_contents('php://input');
        }
        return $this->rawBody;
    }

    public function setRawBody(string $rawBody): void
    {
        $this->rawBody = $rawBody;
        $decoded = json_decode($rawBody, true);
        if (is_array($decoded)) {
            $this->json = $decoded;
        }
    }

    public function getHeader(string $name): ?string
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($this->server[$normalized])) {
            return (string)$this->server[$normalized];
        }
        if (isset($this->server[strtoupper($name)])) {
            return (string)$this->server[strtoupper($name)];
        }
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $k => $v) {
                if (strcasecmp($k, $name) === 0) {
                    return (string)$v;
                }
            }
        }
        return null;
    }
}
