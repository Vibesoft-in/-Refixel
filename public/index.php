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
        try {
            $resp = \App\Core\View::render('partials.500', ['title' => 'System Error | REFIXEL'], 'customer');
            echo $resp->getBody();
        } catch (\Throwable $renderErr) {
            echo "<!DOCTYPE html><html><head><title>System Error</title><style>body{font-family:sans-serif;padding:40px;color:#333;text-align:center;}h1{color:#e53e3e;}</style></head><body><h1>Something went wrong</h1><p>Our engineering team has been notified. Please try again shortly.</p></body></html>";
        }
    }
    exit;
});

// Remove server fingerprinting headers
@ini_set('expose_php', '0');
if (function_exists('header_remove')) {
    @header_remove('X-Powered-By');
}

// 301 HTTPS Enforcement (respects TRUSTED_PROXIES to avoid redirect loops)
if ((bool)\App\Core\Env::get('FORCE_HTTPS', false) && !\App\Core\Request::isHttpsConnection()) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $target = "https://{$host}{$uri}";
    http_response_code(301);
    header("Location: {$target}");
    exit;
}

// Apply Baseline Security Headers
if (!headers_sent()) {
    foreach (\App\Core\Response::buildSecurityHeaders() as $name => $value) {
        header("{$name}: {$value}");
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

