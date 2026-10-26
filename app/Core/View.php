<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    protected static string $viewPath = __DIR__ . '/../views';

    public static function render(string $template, array $data = [], ?string $layout = 'customer'): Response
    {
        $content = self::renderTemplate($template, $data);

        if ($layout !== null) {
            $layoutPath = self::$viewPath . "/layouts/{$layout}.php";
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout view [{$layout}] not found at {$layoutPath}");
            }
            $data['content'] = $content;
            $content = self::renderFile($layoutPath, $data);
        }

        return Response::html($content);
    }

    public static function renderTemplate(string $template, array $data = []): string
    {
        $cleanTemplate = str_replace('.', '/', $template);
        $file = self::$viewPath . "/{$cleanTemplate}.php";

        if (!file_exists($file)) {
            throw new \RuntimeException("View template [{$template}] not found at {$file}");
        }

        return self::renderFile($file, $data);
    }

    public static function partial(string $name, array $data = []): string
    {
        $cleanName = str_replace('.', '/', $name);
        $file = self::$viewPath . "/partials/{$cleanName}.php";

        if (!file_exists($file)) {
            // Also check root views if partial not found
            $file = self::$viewPath . "/{$cleanName}.php";
            if (!file_exists($file)) {
                return "<!-- Partial [{$name}] not found -->";
            }
        }

        return self::renderFile($file, $data);
    }

    protected static function renderFile(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean() ?: '';
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public static function url(string $path = '/'): string
    {
        $appUrl = rtrim((string)Env::get('APP_URL', ''), '/');
        $basePath = parse_url($appUrl, PHP_URL_PATH) ?? '';

        if ($path === '') {
            return rtrim($basePath, '/');
        }

        $cleanPath = '/' . ltrim($path, '/');

        if ($basePath !== '' && !str_starts_with($cleanPath, $basePath)) {
            return rtrim($basePath, '/') . $cleanPath;
        }

        return $cleanPath;
    }

    public static function baseUrl(): string
    {
        $appUrl = rtrim((string)Env::get('APP_URL', ''), '/');
        $basePath = parse_url($appUrl, PHP_URL_PATH) ?? '';
        return rtrim($basePath, '/');
    }

    public static function fullUrl(string $path = '/'): string
    {
        $appUrl = rtrim((string)Env::get('APP_URL', 'https://refixel.com'), '/');
        $cleanPath = '/' . ltrim($path, '/');
        $basePath = parse_url($appUrl, PHP_URL_PATH) ?? '';

        if ($basePath !== '' && str_starts_with($cleanPath, $basePath)) {
            $rootDomain = (string)preg_replace('#' . preg_quote($basePath, '#') . '$#', '', $appUrl);
            return $rootDomain . $cleanPath;
        }

        return $appUrl . $cleanPath;
    }

    public static function asset(string $path): string
    {
        $path = trim($path);
        if (empty($path)) {
            return self::url('assets/img/Full-home-clean.jpg');
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
            return self::url($path);
        }
        if (str_starts_with($path, 'img/') || str_starts_with($path, 'icons/')) {
            return self::url('assets/' . $path);
        }
        // If image filename without directory prefix, default to assets/img/
        if (preg_match('/\.(jpg|jpeg|png|webp|svg|gif)$/i', $path)) {
            return self::url('assets/img/' . $path);
        }
        return self::url('assets/' . $path);
    }

    public static function csrfField(): string
    {
        $token = Csrf::token();
        return '<input type="hidden" name="_csrf" value="' . self::e($token) . '">';
    }

    public static function csrf(): string
    {
        return self::csrfField();
    }

    public static function csrfToken(): string
    {
        return Csrf::token();
    }


    public static function flash(string $key): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }

    public static function setFlash(string $key, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['_flash'][$key] = $message;
    }

    public static function hasFlash(string $key): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        return isset($_SESSION['_flash'][$key]) || isset($_SESSION['flash_' . $key]);
    }
}
