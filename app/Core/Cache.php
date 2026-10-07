<?php
declare(strict_types=1);

namespace App\Core;

class Cache
{
    protected static array $memory = [];
    protected static ?string $cacheDir = null;

    protected static function getCacheDir(): string
    {
        if (self::$cacheDir === null) {
            self::$cacheDir = defined('ROOT_PATH') ? ROOT_PATH . '/storage/cache' : __DIR__ . '/../../storage/cache';
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0755, true);
            }
        }
        return self::$cacheDir;
    }

    protected static function getFilePath(string $key): string
    {
        $hash = hash('sha256', $key);
        return self::getCacheDir() . '/' . $hash . '.cache';
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // 1. Check in-memory memoization
        if (array_key_exists($key, self::$memory)) {
            $item = self::$memory[$key];
            if ($item['expires_at'] === 0 || $item['expires_at'] > time()) {
                return $item['value'];
            }
            unset(self::$memory[$key]);
        }

        // 2. Check disk cache
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $data = @json_decode($content, true);
                if (is_array($data) && isset($data['expires_at'], $data['value'])) {
                    if ($data['expires_at'] === 0 || $data['expires_at'] > time()) {
                        self::$memory[$key] = $data;
                        return $data['value'];
                    }
                    @unlink($file);
                }
            }
        }

        return $default;
    }

    public static function set(string $key, mixed $value, int $ttlSeconds = 3600): bool
    {
        $expiresAt = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
        $data = [
            'key'        => $key,
            'expires_at' => $expiresAt,
            'value'      => $value,
        ];

        self::$memory[$key] = $data;

        $file = self::getFilePath($key);
        return (bool)@file_put_contents($file, json_encode($data, JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        self::set($key, $value, $ttlSeconds);
        return $value;
    }

    public static function forget(string $key): bool
    {
        unset(self::$memory[$key]);
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    public static function flush(): bool
    {
        self::$memory = [];
        $dir = self::getCacheDir();
        $files = glob($dir . '/*.cache');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        return true;
    }
}
