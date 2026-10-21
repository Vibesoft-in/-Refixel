<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Cache;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class RateLimit
{
    /**
     * Handle incoming request rate limiting.
     * 
     * @param Request $request
     * @param string|null $arg Format: 'maxAttempts,decaySeconds' (default '10,60')
     * @return Response|null
     */
    public function handle(Request $request, ?string $arg = null): ?Response
    {
        // 1. Webhook routes are explicitly exempt from rate limits
        if ($request->getPath() === '/webhook/razorpay') {
            return null;
        }

        [$maxAttempts, $decaySeconds] = array_pad(
            explode(',', $arg ?? '10,60'),
            2,
            60
        );

        $maxAttempts  = (int)$maxAttempts;
        $decaySeconds = (int)$decaySeconds;

        $ip = $request->getIp();
        $path = $request->getPath();

        // 2. Generate key: For login routes, combine IP with identifier (account lockout protection)
        $identifier = '';
        if (str_contains($path, 'login')) {
            $identifier = strtolower(trim((string)($request->post('email') ?? $request->post('phone') ?? '')));
        }

        $keyPayload = $identifier !== '' ? "{$ip}_{$identifier}_{$path}" : "{$ip}_{$path}";
        $key = 'rl_' . hash('sha256', $keyPayload);

        // 3. Probabilistic cleanup of expired rate limits (1% chance per request)
        if (random_int(1, 100) === 1) {
            self::cleanupExpired();
        }

        $now = time();
        $resetAt = $now + $decaySeconds;
        $attempts = 1;

        // 4. Atomic counter via database with fallback to file-backed cache
        try {
            $row = Database::fetchOne(
                "SELECT attempts, reset_at FROM rate_limits WHERE rate_key = :k",
                ['k' => $key]
            );

            if (!$row || $now >= (int)$row['reset_at']) {
                Database::query(
                    "INSERT INTO rate_limits (rate_key, attempts, reset_at) VALUES (:k, 1, :r)
                     ON DUPLICATE KEY UPDATE attempts = 1, reset_at = :r2",
                    ['k' => $key, 'r' => $resetAt, 'r2' => $resetAt]
                );
                $attempts = 1;
            } else {
                $attempts = (int)$row['attempts'] + 1;
                $resetAt = (int)$row['reset_at'];
                Database::query(
                    "UPDATE rate_limits SET attempts = attempts + 1 WHERE rate_key = :k",
                    ['k' => $key]
                );
            }
        } catch (\Throwable $e) {
            // Graceful fallback to file cache
            $cacheData = Cache::get($key, ['attempts' => 0, 'reset_at' => $now + $decaySeconds]);
            if ($now >= (int)($cacheData['reset_at'] ?? 0)) {
                $cacheData = ['attempts' => 1, 'reset_at' => $now + $decaySeconds];
            } else {
                $cacheData['attempts'] = (int)($cacheData['attempts'] ?? 0) + 1;
            }
            Cache::set($key, $cacheData, $decaySeconds);
            $attempts = $cacheData['attempts'];
            $resetAt = $cacheData['reset_at'];
        }

        // 5. Enforce Rate Limit Threshold
        if ($attempts > $maxAttempts) {
            $retryAfter = max(1, $resetAt - $now);

            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'status'      => 'error',
                    'message'     => 'Too many requests. Please slow down and try again later.',
                    'retry_after' => $retryAfter,
                ], 429)->setHeader('Retry-After', (string)$retryAfter);
            }

            View::setFlash('error', "Too many requests. Please wait {$retryAfter} seconds before trying again.");
            return Response::html(
                "<!DOCTYPE html><html><head><title>429 Too Many Requests</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>429 Too Many Requests</h1><p>You have exceeded the request rate limit. Please wait {$retryAfter} seconds before retrying.</p></body></html>",
                429
            )->setHeader('Retry-After', (string)$retryAfter);
        }

        return null;
    }

    /**
     * Purge expired rate limits from the database.
     * 
     * @param int|null $now
     * @return int Number of deleted records
     */
    public static function cleanupExpired(?int $now = null): int
    {
        $now = $now ?? time();
        try {
            $stmt = Database::query("DELETE FROM rate_limits WHERE reset_at < :now", ['now' => $now]);
            return $stmt->rowCount();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
