<?php
declare(strict_types=1);

/**
 * REFIXEL Service Platform - Front Controller
 */

define('APP_START', microtime(true));
define('ROOT_PATH', dirname(__DIR__));

// PSR-4 Autoloader
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Environment Configuration
\App\Core\Env::load(ROOT_PATH . '/.env');

// Set Timezone (Default IST)
$timezone = \App\Core\Env::get('APP_TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set($timezone);

// Configure Error Handling
$isDebug = (bool)\App\Core\Env::get('APP_DEBUG', false);
if ($isDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// Global Exception & Error Logger
set_exception_handler(function (\Throwable $e) use ($isDebug) {
    \App\Core\Logger::error($e->getMessage(), [
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    if (!headers_sent()) {
        http_response_code(500);
    }

    if ($isDebug) {
        echo "<h1>Application Error (Debug Mode)</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<!DOCTYPE html><html><head><title>System Error</title><style>body{font-family:sans-serif;padding:40px;color:#333;text-align:center;}h1{color:#e53e3e;}</style></head><body><h1>Something went wrong</h1><p>Our engineering team has been notified. Please try again shortly.</p></body></html>";
    }
    exit;
});

// Apply Baseline Security Headers
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https: data:; frame-src 'self' https://maps.google.com https://www.google.com https://*.google.com https:; frame-ancestors 'self';");
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Initialize Session
\App\Core\Auth::init();

// Load Application Routes
require_once ROOT_PATH . '/config/routes.php';

// Dispatch Request
$request = new \App\Core\Request();
$response = \App\Core\Router::dispatch($request);
$response->send();

