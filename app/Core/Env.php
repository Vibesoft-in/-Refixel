<?php
declare(strict_types=1);

namespace App\Core;

class Env
{
    protected static array $variables = [];

    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip surrounding quotes
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Parse special types
            $parsed = match (strtolower($value)) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)', 'null', '(null)' => null,
                default => $value,
            };

            self::$variables[$key] = $parsed;
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $parsed;
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $parsed;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
