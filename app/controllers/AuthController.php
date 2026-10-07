<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\User;

class AuthController extends Controller
{
    protected const MAX_LOGIN_ATTEMPTS = 5;
    protected const LOCKOUT_MINUTES = 15;

    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin(Auth::role());
        }
        return $this->render('auth.login', ['title' => 'Login | REFIXEL'], 'auth');
    }

    public function login(Request $request): Response
    {
        $identifier = trim((string)$request->input('identifier', ''));
        $password   = (string)$request->input('password', '');
        $ip         = $request->getIp();

        // 1. Check brute force lockout
        if ($this->isLockedOut($ip, $identifier)) {
            View::setFlash('error', 'Too many failed login attempts. Please wait 15 minutes before trying again.');
            return $this->redirect('/login');
        }

        $validator = $this->validate($request, [
            'identifier' => 'required',
            'password'   => 'required',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            return $this->redirect('/login');
        }

        $user = User::findByEmailOrPhone($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Log failed attempt
            $this->recordFailedAttempt($ip, $identifier);
            View::setFlash('error', 'Invalid login credentials.');
            return $this->redirect('/login');
        }

        if (($user['status'] ?? 'active') !== 'active') {
            View::setFlash('error', 'Your account has been deactivated. Please contact support.');
            return $this->redirect('/login');
        }

        // Clear failed attempts on successful login
        $this->clearFailedAttempts($ip, $identifier);

        Auth::login($user);

        if (!empty($user['must_change_password'])) {
            return $this->redirect('/change-password');
        }

        return $this->redirectAfterLogin($user['role']);
    }

    public function showSignup(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin(Auth::role());
        }
        return $this->render('auth.signup', ['title' => 'Sign Up | REFIXEL'], 'auth');
    }

    public function signup(Request $request): Response
    {
        $validator = $this->validate($request, [
            'name'     => 'required|min:2',
            'phone'    => 'required|phone',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            return $this->redirect('/signup');
        }

        $phone = preg_replace('/\D/', '', (string)$request->input('phone'));
        $email = $request->input('email') ? trim((string)$request->input('email')) : null;

        // Check if phone or email already registered
        if (User::findBy('phone', $phone)) {
            View::setFlash('error', 'This mobile number is already registered. Please log in.');
            return $this->redirect('/login');
        }

        if ($email && User::findBy('email', $email)) {
            View::setFlash('error', 'This email address is already registered. Please log in.');
            return $this->redirect('/login');
        }

        // Public signup creates customers only - role cannot be overridden from request
        $userId = User::create([
            'role'          => 'customer',
            'name'          => trim((string)$request->input('name')),
            'phone'         => $phone,
            'email'         => $email,
            'password_hash' => password_hash((string)$request->input('password'), PASSWORD_BCRYPT),
            'status'        => 'active',
        ]);

        $user = User::find($userId);
        
        // Record DPDP Act consent
        \App\Models\Consent::record($userId, 'terms_and_privacy', $request->getIp());

        Auth::login($user);

        View::setFlash('success', 'Account created successfully! Welcome to REFIXEL.');
        return $this->redirect('/account');
    }

    public function showForgotPassword(Request $request): Response
    {
        return $this->render('auth.forgot-password', ['title' => 'Reset Password | REFIXEL'], 'auth');
    }

    public function forgotPassword(Request $request): Response
    {
        $identifier = trim((string)$request->input('identifier', ''));
        $user = User::findByEmailOrPhone($identifier);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            Database::query(
                "INSERT INTO password_resets (email_or_phone, token_hash, expires_at)
                 VALUES (:ident, :hash, :expires)",
                ['ident' => $identifier, 'hash' => $tokenHash, 'expires' => $expiresAt]
            );

            // Dispatch password reset email
            Notifier::notifyPasswordReset(
                $user['email'] ?? $identifier,
                View::url('/reset-password?token=' . $token)
            );

            // For testing convenience in development, store token in session flash
            View::setFlash('success', 'A password reset link has been dispatched.');
        } else {
            // Constant-time behavior: don't reveal if user exists
            View::setFlash('success', 'If an account exists with those details, a reset link has been sent.');
        }

        return $this->redirect('/login');
    }

    public function showResetPassword(Request $request): Response
    {
        $token = (string)$request->query('token', '');
        return $this->render('auth.reset-password', [
            'title' => 'Choose New Password | REFIXEL',
            'token' => $token,
        ], 'auth');
    }

    public function resetPassword(Request $request): Response
    {
        $token = (string)$request->input('token', '');
        $tokenHash = hash('sha256', $token);

        $record = Database::fetchOne(
            "SELECT * FROM password_resets WHERE token_hash = :hash AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            ['hash' => $tokenHash]
        );

        if (!$record) {
            View::setFlash('error', 'This reset link is invalid or has expired.');
            return $this->redirect('/forgot-password');
        }

        $validator = $this->validate($request, [
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            return $this->redirect('/reset-password?token=' . urlencode($token));
        }

        $user = User::findByEmailOrPhone($record['email_or_phone']);
        if ($user) {
            User::update((int)$user['id'], [
                'password_hash' => password_hash((string)$request->input('password'), PASSWORD_BCRYPT),
                'must_change_password' => 0,
            ]);

            // Invalidate used reset tokens for this user
            Database::query("DELETE FROM password_resets WHERE email_or_phone = :ident", ['ident' => $record['email_or_phone']]);

            View::setFlash('success', 'Your password has been reset successfully. Please log in.');
            return $this->redirect('/login');
        }

        View::setFlash('error', 'User account not found.');
        return $this->redirect('/login');
    }

    public function showChangePassword(Request $request): Response
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }
        return $this->render('auth.change-password', [
            'title' => 'Update Password | REFIXEL',
            'user'  => Auth::user(),
        ], 'auth');
    }

    public function changePassword(Request $request): Response
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $user = User::find(Auth::id());
        $isFirstLogin = !empty($user['must_change_password']);

        $rules = [
            'new_password' => 'required|min:6|confirmed',
        ];

        if (!$isFirstLogin) {
            $rules['current_password'] = 'required';
        }

        $validator = $this->validate($request, $rules);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            return $this->redirect('/change-password');
        }

        if (!$isFirstLogin) {
            if (!password_verify((string)$request->input('current_password'), $user['password_hash'])) {
                View::setFlash('error', 'Your current password was incorrect.');
                return $this->redirect('/change-password');
            }
        }

        User::update($user['id'], [
            'password_hash'        => password_hash((string)$request->input('new_password'), PASSWORD_BCRYPT),
            'must_change_password' => 0,
        ]);

        $_SESSION['user']['must_change_password'] = false;

        View::setFlash('success', 'Password updated successfully!');
        return $this->redirectAfterLogin($user['role']);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        return $this->redirect('/?logged_out=1');
    }

    protected function redirectAfterLogin(string $role): Response
    {
        return match ($role) {
            'admin' => $this->redirect('/admin'),
            'staff' => $this->redirect('/staff'),
            default => $this->redirect('/account'),
        };
    }

    protected function isLockedOut(string $ip, string $identifier): bool
    {
        $since = date('Y-m-d H:i:s', time() - (self::LOCKOUT_MINUTES * 60));
        $res = Database::fetchOne(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE (ip = :ip OR identifier = :ident) AND attempted_at > :since",
            ['ip' => $ip, 'ident' => $identifier, 'since' => $since]
        );

        return ((int)($res['attempts'] ?? 0)) >= self::MAX_LOGIN_ATTEMPTS;
    }

    protected function recordFailedAttempt(string $ip, string $identifier): void
    {
        Database::query(
            "INSERT INTO login_attempts (ip, identifier, attempted_at) VALUES (:ip, :ident, NOW())",
            ['ip' => $ip, 'ident' => $identifier]
        );
    }

    protected function clearFailedAttempts(string $ip, string $identifier): void
    {
        Database::query(
            "DELETE FROM login_attempts WHERE ip = :ip OR identifier = :ident",
            ['ip' => $ip, 'ident' => $identifier]
        );
    }
}

