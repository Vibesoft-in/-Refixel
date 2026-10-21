<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class VerifyCsrf
{
    public function handle(Request $request, ?string $arg = null): ?Response
    {
        $method = $request->getMethod();

        // Safe HTTP methods do not require CSRF token validation
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return null;
        }

        // Webhook endpoints with signature verification are exempt
        if ($request->getPath() === '/webhook/razorpay') {
            return null;
        }

        // Extract token from POST body, JSON payload, or custom request headers
        $token = $request->post('_csrf')
            ?? ($request->getJsonData()['_csrf'] ?? null)
            ?? $request->getHeader('X-CSRF-TOKEN')
            ?? $request->getHeader('X-XSRF-TOKEN')
            ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::validate($token)) {
            $isAjax = $request->isAjax()
                || $request->isJson()
                || (string)$request->input('is_ajax') === '1'
                || (string)$request->post('is_ajax') === '1'
                || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                || $request->getHeader('X-CSRF-TOKEN') !== null;

            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => 'Your session has expired or security token mismatch. Please reload the page and try again.',
                ], 419)->setHeader('Content-Type', 'application/json');
            }

            View::setFlash('error', 'Your session expired or security token was invalid. Please submit the form again.');
            return Response::html(
                "<!DOCTYPE html><html><head><title>419 Page Expired</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h1>419 Page Expired</h1><p>Your session has expired or the security token did not match. Please reload the page and try again.</p><p><a href='/'>Return to Home</a></p></body></html>",
                419
            );
        }

        return null;
    }
}
