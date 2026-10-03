<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class RequireRole
{
    public function handle(Request $request, ?string $requiredRole = null): ?Response
    {
        if (!Auth::check()) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'status'  => 'error',
                    'message' => 'Authentication required.',
                ], 401);
            }
            View::setFlash('error', 'Please log in to continue.');
            return Response::redirect('/login');
        }

        $userRole = Auth::role();

        if ($requiredRole !== null && $userRole !== $requiredRole) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'status'  => 'error',
                    'message' => 'Forbidden: You do not have permission to perform this action.',
                ], 403);
            }

            // Redirect to appropriate portal based on actual role
            $redirectPath = match ($userRole) {
                'admin'    => '/admin',
                'staff'    => '/staff',
                'customer' => '/account',
                default    => '/login',
            };

            View::setFlash('error', 'Access denied. You do not have permission to view that page.');
            return Response::redirect($redirectPath);
        }

        if (!empty(Auth::user()['must_change_password']) && $request->path() !== '/change-password') {
            View::setFlash('warning', 'Please change your temporary password before accessing your account.');
            return Response::redirect('/change-password');
        }

        return null;
    }
}
