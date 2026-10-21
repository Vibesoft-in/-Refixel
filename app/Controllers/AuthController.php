<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Encrypter;
use App\Core\Logger;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\Totp;
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

        if ($user && in_array($user['status'] ?? '', ['pending', 'under_review', 'under_process'])) {
            View::setFlash('warning', 'Your partner application is currently under process & review by our admin team. Please be patient. You will receive an approval email with your login credentials once verified.');
            return $this->redirect('/login');
        }

        $passwordMatches = false;
        if ($user && !empty($user['password_hash'])) {
            if (password_verify($password, $user['password_hash'])) {
                $passwordMatches = true;
                if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
                    User::update((int)$user['id'], [
                        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    ]);
                }
            }
        }

        if (!$user || !$passwordMatches) {
            // Log failed attempt
            $this->recordFailedAttempt($ip, $identifier);
            View::setFlash('error', 'Invalid login credentials. Please check your mobile number or email and password.');
            return $this->redirect('/login');
        }

        if (($user['status'] ?? 'active') !== 'active') {
            View::setFlash('error', 'Your account has been deactivated. Please contact support.');
            return $this->redirect('/login');
        }

        // Clear failed attempts on successful login
        $this->clearFailedAttempts($ip, $identifier);

        $redirectUrl = (string)($request->input('redirect') ?? $request->query('redirect') ?? '');

        // 2FA Challenge Check
        if (!empty($user['totp_enabled'])) {
            $_SESSION['2fa_pending'] = [
                'user_id'    => (int)$user['id'],
                'redirect'   => $redirectUrl,
                'ip'         => $ip,
                'created_at' => time(),
            ];
            return $this->redirect('/login/2fa');
        }

        Auth::login($user);

        if (!empty($user['must_change_password'])) {
            return $this->redirect('/change-password');
        }

        if (!empty($redirectUrl) && (str_starts_with($redirectUrl, '/') || str_starts_with($redirectUrl, View::url('/')))) {
            return $this->redirect($redirectUrl);
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
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        } elseif (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        $email = $request->input('email') ? trim(strtolower((string)$request->input('email'))) : null;
        $plainPass = (string)$request->input('password');

        // Check if phone or email already registered
        if (User::findByPhone($phone)) {
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
            'password_hash' => password_hash($plainPass, PASSWORD_BCRYPT),
            'status'        => 'active',
        ]);

        // Seed default customer profile
        try {
            Database::execute(
                "INSERT INTO customer_profiles (user_id, address, city) VALUES (?, 'Doorstep Service Area', 'Kashipur')",
                [$userId]
            );
        } catch (\Throwable $e) {}

        $user = User::find($userId);
        
        // Record DPDP Act consent
        \App\Models\Consent::record($userId, 'terms_and_privacy', $request->getIp());

        Auth::login($user);

        View::setFlash('success', 'Account created successfully! Welcome to REFIXEL.');

        $redirectUrl = (string)($request->input('redirect') ?? $request->query('redirect') ?? '');
        if (!empty($redirectUrl) && (str_starts_with($redirectUrl, '/') || str_starts_with($redirectUrl, View::url('/')))) {
            return $this->redirect($redirectUrl);
        }

        return $this->redirect('/account');
    }

    public function showForgotPassword(Request $request): Response
    {
        return $this->render('auth.forgot-password', ['title' => 'Reset Password | REFIXEL'], 'auth');
    }

    public function forgotPassword(Request $request): Response
    {
        $identifier = trim((string)$request->input('identifier', ''));
        if (empty($identifier)) {
            View::setFlash('error', 'Please enter your email or mobile number.');
            return $this->redirect('/forgot-password');
        }

        // Rate limiting: Allow max 3 reset requests per 5 minutes per identifier
        $recentRow = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM password_resets 
             WHERE email_or_phone = :ident AND created_at > (NOW() - INTERVAL 5 MINUTE)",
            ['ident' => $identifier]
        );
        if ((int)($recentRow['cnt'] ?? 0) >= 3) {
            View::setFlash('warning', 'Too many password reset requests. Please wait 5 minutes before trying again.');
            return $this->redirect('/forgot-password');
        }

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

        $updateData = [
            'password_hash'        => password_hash((string)$request->input('new_password'), PASSWORD_BCRYPT),
            'must_change_password' => 0,
        ];

        User::update($user['id'], $updateData);

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
            'super_admin' => $this->redirect('/superadmin'),
            'admin'       => $this->redirect('/admin'),
            'staff'       => $this->redirect('/staff'),
            default       => $this->redirect('/account'),
        };
    }

    protected function isLockedOut(string $ip, string $identifier): bool
    {
        $since = date('Y-m-d H:i:s', time() - (self::LOCKOUT_MINUTES * 60));
        // Combination of IP + Account prevents an attacker from locking out a victim's account from a different IP
        $res = Database::fetchOne(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE ip = :ip AND identifier = :ident AND attempted_at > :since",
            ['ip' => $ip, 'ident' => $identifier, 'since' => $since]
        );

        if (((int)($res['attempts'] ?? 0)) >= self::MAX_LOGIN_ATTEMPTS) {
            return true;
        }

        // Global IP threshold (brute force spraying across many accounts from same IP)
        $ipRes = Database::fetchOne(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE ip = :ip AND attempted_at > :since",
            ['ip' => $ip, 'since' => $since]
        );

        return ((int)($ipRes['attempts'] ?? 0)) >= (self::MAX_LOGIN_ATTEMPTS * 5);
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
            "DELETE FROM login_attempts WHERE ip = :ip AND identifier = :ident",
            ['ip' => $ip, 'ident' => $identifier]
        );
    }

    public function show2fa(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin(Auth::role());
        }

        if (empty($_SESSION['2fa_pending']['user_id'])) {
            return $this->redirect('/login');
        }

        return $this->render('auth.2fa', ['title' => 'Two-Factor Authentication | REFIXEL'], 'auth');
    }

    public function verify2fa(Request $request): Response
    {
        if (empty($_SESSION['2fa_pending']['user_id'])) {
            View::setFlash('error', 'Authentication session expired. Please log in again.');
            return $this->redirect('/login');
        }

        $userId = (int)$_SESSION['2fa_pending']['user_id'];
        $user = User::find($userId);
        if (!$user || empty($user['totp_enabled'])) {
            unset($_SESSION['2fa_pending']);
            return $this->redirect('/login');
        }

        $ip = $request->getIp();
        $rateKey = '2fa_' . $userId;
        if ($this->isLockedOut($ip, $rateKey)) {
            View::setFlash('error', 'Too many failed 2FA verification attempts. Please wait 15 minutes before trying again.');
            return $this->redirect('/login/2fa');
        }

        $recoveryCode = trim((string)$request->input('recovery_code', ''));
        $totpCode = trim((string)$request->input('code', ''));

        // Handle recovery code attempt
        if (!empty($recoveryCode)) {
            $rawCodes = json_decode((string)($user['totp_recovery_codes'] ?? '[]'), true);
            $hashedCodes = is_array($rawCodes) ? $rawCodes : [];
            $matchedIndex = Totp::verifyRecoveryCode($recoveryCode, $hashedCodes);

            if ($matchedIndex === null) {
                $this->recordFailedAttempt($ip, $rateKey);
                View::setFlash('error', 'Invalid or already used recovery code.');
                return $this->redirect('/login/2fa');
            }

            // Remove used recovery code (single use)
            unset($hashedCodes[$matchedIndex]);
            $hashedCodes = array_values($hashedCodes);

            User::update($userId, [
                'totp_recovery_codes' => json_encode($hashedCodes),
            ]);

            $this->clearFailedAttempts($ip, $rateKey);
            $redirectUrl = (string)($_SESSION['2fa_pending']['redirect'] ?? '');
            unset($_SESSION['2fa_pending']);

            // Session ID regeneration upon 2FA verification
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                session_regenerate_id(true);
            }

            Auth::login($user);
            View::setFlash('warning', 'Logged in using an emergency recovery code. You have ' . count($hashedCodes) . ' recovery codes remaining.');

            if (!empty($redirectUrl) && (str_starts_with($redirectUrl, '/') || str_starts_with($redirectUrl, View::url('/')))) {
                return $this->redirect($redirectUrl);
            }
            return $this->redirectAfterLogin($user['role']);
        }

        // Handle standard 6-digit TOTP code
        if (empty($totpCode)) {
            View::setFlash('error', 'Please enter your 6-digit security code or an emergency recovery code.');
            return $this->redirect('/login/2fa');
        }

        $encryptedSecret = (string)($user['totp_secret'] ?? '');
        $secret = Encrypter::decrypt($encryptedSecret);

        if ($secret === null) {
            Logger::error("2FA Decryption failed for user ID {$userId}. APP_KEY may have changed or record is corrupt.");
            View::setFlash('error', '2FA security error: unable to decrypt secret. Please contact system administrator.');
            return $this->redirect('/login');
        }

        $lastStep = !empty($user['totp_last_step']) ? (int)$user['totp_last_step'] : null;
        $matchedStep = Totp::verify($secret, $totpCode, $lastStep);

        if ($matchedStep === null) {
            $this->recordFailedAttempt($ip, $rateKey);
            View::setFlash('error', 'Invalid, expired, or replayed security code. Please check your authenticator clock.');
            return $this->redirect('/login/2fa');
        }

        // Update last used step (Replay protection)
        User::update($userId, [
            'totp_last_step' => $matchedStep,
        ]);

        $this->clearFailedAttempts($ip, $rateKey);
        $redirectUrl = (string)($_SESSION['2fa_pending']['redirect'] ?? '');
        unset($_SESSION['2fa_pending']);

        // Session ID regeneration upon 2FA verification
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        Auth::login($user);

        if (!empty($redirectUrl) && (str_starts_with($redirectUrl, '/') || str_starts_with($redirectUrl, View::url('/')))) {
            return $this->redirect($redirectUrl);
        }
        return $this->redirectAfterLogin($user['role']);
    }

    public function show2faSetup(Request $request): Response
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        // Fails loudly if APP_KEY is missing
        if (!Encrypter::hasKey()) {
            View::setFlash('error', 'Security Policy Error: APP_KEY is not configured on the server. 2FA setup cannot proceed without an encryption key.');
            return $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }

        $user = User::find((int)Auth::id());
        if (!$user) {
            return $this->redirect('/login');
        }

        if (!empty($user['totp_enabled'])) {
            View::setFlash('info', 'Two-Factor Authentication is already active on your account.');
            return $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }

        if (empty($_SESSION['2fa_setup_secret']) || empty($_SESSION['2fa_setup_recovery_codes'])) {
            $_SESSION['2fa_setup_secret'] = Totp::generateSecret(16);
            $_SESSION['2fa_setup_recovery_codes'] = Totp::generateRecoveryCodes(8);
        }

        $secret = (string)$_SESSION['2fa_setup_secret'];
        $recoveryCodes = $_SESSION['2fa_setup_recovery_codes']['plain'];
        $otpauthUri = Totp::getOtpAuthUri('REFIXEL', $user['email'] ?? $user['phone'] ?? 'Admin', $secret);

        return $this->render('auth.2fa-setup', [
            'title'         => 'Set Up Two-Factor Authentication | REFIXEL',
            'user'          => $user,
            'secret'        => $secret,
            'recoveryCodes' => $recoveryCodes,
            'otpauthUri'    => $otpauthUri,
        ], 'auth');
    }

    public function confirm2faSetup(Request $request): Response
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        // Fails loudly if APP_KEY is missing
        if (!Encrypter::hasKey()) {
            View::setFlash('error', 'Security Policy Error: APP_KEY is not configured on the server.');
            return $this->redirect('/2fa/setup');
        }

        $secret = (string)($_SESSION['2fa_setup_secret'] ?? '');
        $recoveryData = $_SESSION['2fa_setup_recovery_codes'] ?? null;

        if (empty($secret) || empty($recoveryData['hashed'])) {
            View::setFlash('error', 'Setup session expired. Please start 2FA configuration again.');
            return $this->redirect('/2fa/setup');
        }

        $code = trim((string)$request->input('code', ''));
        $matchedStep = Totp::verify($secret, $code);

        if ($matchedStep === null) {
            View::setFlash('error', 'Invalid verification code. Please make sure the code matches your authenticator app and your phone clock is synced.');
            return $this->redirect('/2fa/setup');
        }

        // Encrypt secret with AES-256-GCM
        $encryptedSecret = Encrypter::encrypt($secret);

        User::update((int)Auth::id(), [
            'totp_secret'         => $encryptedSecret,
            'totp_enabled'        => 1,
            'totp_recovery_codes' => json_encode($recoveryData['hashed']),
            'totp_last_step'      => $matchedStep,
            'totp_confirmed_at'   => date('Y-m-d H:i:s'),
        ]);

        unset($_SESSION['2fa_setup_secret'], $_SESSION['2fa_setup_recovery_codes']);

        // Regenerate session ID on 2FA elevation
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user']['totp_enabled'] = true;

        Logger::info("User ID " . Auth::id() . " successfully enrolled in Two-Factor Authentication (2FA).");
        View::setFlash('success', 'Two-Factor Authentication (2FA) is now ACTIVE! Your account is secured.');

        return $this->redirectAfterLogin(Auth::role());
    }
}

