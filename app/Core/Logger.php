<?php
declare(strict_types=1);

namespace App\Core;

class Logger
{
    protected static string $logDir = __DIR__ . '/../../storage/logs';

    public static function log(string $level, string $message, array $context = []): void
    {
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0755, true);
        }

        $date = date('Y-m-d H:i:s');
        $sanitizedContext = self::maskSensitive($context);
        $contextStr = !empty($sanitizedContext) ? ' ' . json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES) : '';
        
        $logLine = sprintf("[%s] [%s] %s%s\n", $date, strtoupper($level), $message, $contextStr);
        $logFile = self::$logDir . '/app.log';

        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (Env::get('APP_DEBUG', false)) {
            self::log('debug', $message, $context);
        }
    }

    protected static function maskSensitive(array $data): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'secret', 'card', 'cvv',
            'auth_token', 'api_key', 'razorpay_secret', 'authorization', 'cookie',
            'session_id', 'otp', 'pin', 'card_number', 'private_key', 'access_token',
            'refresh_token', 'smtp_pass', 'db_pass', 'password_hash'
        ];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::maskSensitive($value);
            } elseif (in_array(strtolower((string)$key), $sensitiveKeys, true)) {
                $data[$key] = '********';
            }
        }
        return $data;
    }
}
