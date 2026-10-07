<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class RateLimit
{
    public function handle(Request $request, ?string $arg = null): ?Response
    {
        [$maxAttempts, $decaySeconds] = array_pad(
            explode(',', $arg ?? '10,60'),
            2,
            60
        );

        $maxAttempts  = (int)$maxAttempts;
        $decaySeconds = (int)$decaySeconds;

        $ip = $request->getIp();
        $key = 'rate_limit_' . md5($ip . '_' . $request->getPath());

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $now = time();
        $data = $_SESSION[$key] ?? ['attempts' => 0, 'reset_at' => $now + $decaySeconds];

        if ($now > $data['reset_at']) {
            $data = ['attempts' => 1, 'reset_at' => $now + $decaySeconds];
        } else {
            $data['attempts']++;
        }

        $_SESSION[$key] = $data;

        if ($data['attempts'] > $maxAttempts) {
            $retryAfter = $data['reset_at'] - $now;

            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'status'  => 'error',
                    'message' => 'Too many requests. Please slow down and try again later.',
                    'retry_after' => $retryAfter,
                ], 429)->setHeader('Retry-After', (string)$retryAfter);
            }

            View::setFlash('error', "Too many requests. Please wait {$retryAfter} seconds before trying again.");
            return Response::html(
                "<h1>429 Too Many Requests</h1><p>Please wait {$retryAfter} seconds before retrying.</p>",
                429
            )->setHeader('Retry-After', (string)$retryAfter);
        }

        return null;
    }
}
