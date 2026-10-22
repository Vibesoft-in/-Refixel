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
            $targetUri = $request->uri();
            $redirectParam = (!empty($targetUri) && $targetUri !== '/') ? '?redirect=' . urlencode($targetUri) : '';
            return Response::redirect('/login' . $redirectParam);
        }

        $userRole = Auth::role();

        $isAllowed = true;
        if ($requiredRole !== null) {
            if ($requiredRole === 'admin') {
                $isAllowed = ($userRole === 'admin' || $userRole === 'super_admin');
            } elseif ($requiredRole === 'super_admin') {
                $isAllowed = ($userRole === 'super_admin');
            } else {
                $isAllowed = ($userRole === $requiredRole);
            }
        }

        if (!$isAllowed) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json([
                    'status'  => 'error',
                    'message' => 'Forbidden: You do not have permission to perform this action.',
                ], 403);
            }

            // Redirect to appropriate portal based on actual role
            $redirectPath = match ($userRole) {
                'super_admin' => '/superadmin',
                'admin'       => '/admin',
                'staff'       => '/staff',
                'customer'    => '/account',
                default       => '/login',
            };

            View::setFlash('error', 'Access denied. You do not have permission to view that page.');
            return Response::redirect($redirectPath);
        }

        if (!empty(Auth::user()['must_change_password']) && $request->path() !== '/change-password') {
            View::setFlash('warning', 'Please change your temporary password before accessing your account.');
            return Response::redirect('/change-password');
        }

        // Sync 2FA enrollment status in session for Admin & Super Admin (non-blocking, voluntary 2FA)
        if (in_array($userRole, ['admin', 'super_admin'], true)) {
            $userSession = Auth::user();
            $totpEnabled = !empty($userSession['totp_enabled']);
            
            // If session flag says not enabled, check DB to keep session in sync
            if (!$totpEnabled) {
                $dbUser = \App\Models\User::find((int)Auth::id());
                if ($dbUser && !empty($dbUser['totp_enabled'])) {
                    $_SESSION['user']['totp_enabled'] = true;
                }
            }
        }

        return null;
    }
}
