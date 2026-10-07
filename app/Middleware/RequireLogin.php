<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class RequireLogin
{
    public function handle(Request $request, ?string $arg = null): ?Response
    {
        if (!Auth::check()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'status'  => 'error',
                    'message' => 'Authentication required.',
                ], 401);
            }

            View::setFlash('error', 'Please log in to access this page.');
            return Response::redirect('/login');
        }

        return null;
    }
}
