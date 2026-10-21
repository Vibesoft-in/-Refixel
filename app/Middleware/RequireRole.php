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

        // Mandatory 2FA Enforcement for Admin & Super Admin accounts
        if (in_array($userRole, ['admin', 'super_admin'], true)) {
            $userSession = Auth::user();
            $totpEnabled = !empty($userSession['totp_enabled']);
            
            // If session flag says not enabled, double check DB once in case just enabled/disabled
            if (!$totpEnabled) {
                $dbUser = \App\Models\User::find((int)Auth::id());
                if ($dbUser && !empty($dbUser['totp_enabled'])) {
                    $_SESSION['user']['totp_enabled'] = true;
                    $totpEnabled = true;
                }
            }

            if (!$totpEnabled && !in_array($request->path(), ['/2fa/setup', '/logout'], true)) {
                View::setFlash('warning', 'Two-Factor Authentication (2FA) is mandatory for administrator accounts. Please complete 2FA setup to continue.');
                return Response::redirect('/2fa/setup');
            }
        }

        return null;
    }
}
