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

        // Safe methods do not require CSRF tokens
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return null;
        }

        $token = $request->post('_csrf')
            ?? ($request->getJsonData()['_csrf'] ?? null)
            ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::validate($token)) {
            $isAjax = $request->isAjax()
                || $request->isJson()
                || (string)$request->input('is_ajax') === '1'
                || (string)$request->post('is_ajax') === '1'
                || !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

            if ($isAjax) {
                return Response::json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => 'Your session has expired or security token mismatch. Please reload the page and try again.',
                ], 419);
            }

            View::setFlash('error', 'Your session expired. Please submit the form again.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            return Response::redirect($referer);
        }

        return null;
    }
}
