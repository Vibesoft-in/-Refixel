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

    public function getIp(): string
    {
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        }
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $list = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
            return trim($list[0]);
        }
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function getUserAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}
