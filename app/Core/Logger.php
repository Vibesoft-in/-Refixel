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
        $scrubbedMessage = self::scrubString($message);
        $sanitizedContext = self::maskSensitive($context);
        $contextStr = !empty($sanitizedContext) ? ' ' . json_encode($sanitizedContext, JSON_UNESCAPED_SLASHES) : '';
        
        $logLine = sprintf("[%s] [%s] %s%s\n", $date, strtoupper($level), $scrubbedMessage, $contextStr);
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

    /**
     * Regex scrubber to mask sensitive values in free-form log messages and string context.
     */
    public static function scrubString(string $text): string
    {
        if (empty($text)) {
            return $text;
        }

        // 1. Credit card numbers (13-16 digits grouped with spaces/hyphens)
        $text = preg_replace('/\b(?:\d{4}[-\s]?){3}\d{4}\b/', '****-****-****-****', $text);

        // 2. CVV / CVC (3-4 digits after label)
        $text = preg_replace('/(\b(?:cvv|cvc|security[_\s-]?code)\s*[:=]\s*[\'"]?)(\d{3,4})([\'"]?)/i', '$1***$3', $text);

        // 3. Passwords (key: value or key=value)
        $text = preg_replace('/(\b(?:password|passwd|plain_password|user_password|pwd)\s*[:=]\s*[\'"]?)([^\s\'",;]+)([\'"]?)/i', '$1********$3', $text);

        // 4. OTPs & verification codes (4-8 digits after label)
        $text = preg_replace('/(\b(?:otp|debug_otp|verification[_\s-]?code|one[_\s-]?time[_\s-]?pin|auth[_\s-]?code)\s*[:=]\s*[\'"]?)(\d{4,8})([\'"]?)/i', '$1******$3', $text);

        // 5. Razorpay signatures & transaction tokens
        $text = preg_replace('/(\b(?:razorpay_signature|signature|razorpay_secret)\s*[:=]\s*[\'"]?)([a-zA-Z0-9_\-]{16,})([\'"]?)/i', '$1********$3', $text);

        // 6. Generic tokens, API keys, bearer tokens
        $text = preg_replace('/(\b(?:api[_\s-]?key|auth_token|access_token|refresh_token|private[_\s-]?key|secret[_\s-]?key)\s*[:=]\s*[\'"]?)([a-zA-Z0-9_\-\.]{12,})([\'"]?)/i', '$1********$3', $text);

        // 7. Authorization Bearer header
        $text = preg_replace('/(\bBearer\s+)([a-zA-Z0-9_\-\.]{16,})/i', '$1********', $text);

        // 8. Guest authorization tokens
        $text = preg_replace('/(\b(?:guest_token)\s*[:=]\s*[\'"]?)([a-zA-Z0-9_\-]{20,})([\'"]?)/i', '$1********$3', $text);

        return $text;
    }

    public static function maskSensitive(array $data): array
    {
        $sensitiveKeyPatterns = '/(?:password|secret|token|card|cvv|cvc|auth|cookie|session|otp|pin|key|signature|credential)/i';

        foreach ($data as $key => $value) {
            $keyStr = (string)$key;

            if (is_array($value)) {
                $data[$key] = self::maskSensitive($value);
            } elseif (preg_match($sensitiveKeyPatterns, $keyStr)) {
                $data[$key] = '********';
            } elseif (is_string($value)) {
                $data[$key] = self::scrubString($value);
            }
        }
        return $data;
    }
}
