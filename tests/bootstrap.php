<?php
declare(strict_types=1);

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
\App\Core\Env::set('APP_ENV', 'test');
\App\Core\Env::set('MAIL_MAILER', 'mock');

// Set Timezone
date_default_timezone_set(\App\Core\Env::get('APP_TIMEZONE', 'Asia/Kolkata'));
