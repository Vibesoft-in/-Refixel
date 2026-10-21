<?php
declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = (int)Env::get('SESSION_LIFETIME', 7200);
            
            // Derive secure flag: honor explicit SESSION_SECURE, or automatically check Request::isHttps() (which verifies TRUSTED_PROXIES)
            $isHttps   = Request::isHttpsConnection();
            $secureEnv = Env::get('SESSION_SECURE');
            $secure    = $secureEnv !== null ? (bool)$secureEnv : $isHttps;

            $httpOnly = (bool)Env::get('SESSION_HTTPONLY', true);
            $sameSite = (string)Env::get('SESSION_SAMESITE', 'Lax');

            if (!headers_sent()) {
                @ini_set('session.cookie_httponly', '1');
                @ini_set('session.use_only_cookies', '1');
                @ini_set('session.use_strict_mode', '1');

                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => $secure,
                    'httponly' => $httpOnly,
                    'samesite' => $sameSite,
                ]);
            }

            @session_start();
        }
    }

    public static function login(array $user): void
    {
        self::init();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id'                   => (int)$user['id'],
            'role'                 => (string)$user['role'],
            'name'                 => (string)$user['name'],
            'email'                => $user['email'] ?? null,
            'phone'                => $user['phone'] ?? null,
            'totp_enabled'         => (bool)($user['totp_enabled'] ?? false),
            'password_version'     => (int)($user['password_version'] ?? 1),
            'must_change_password' => (bool)($user['must_change_password'] ?? false),
        ];

        $_SESSION['last_activity'] = time();

        // Update last_login_at in database
        try {
            Database::query("UPDATE users SET last_login_at = NOW() WHERE id = :id", ['id' => $user['id']]);
        } catch (\Throwable $e) {
            Logger::error('Failed to update last_login_at: ' . $e->getMessage());
        }
    }

    public static function logout(): void
    {
        self::init();
        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function check(): bool
    {
        self::init();
        if (empty($_SESSION['user']['id'])) {
            return false;
        }

        // 1. Role-based Inactivity Timeout: 30 minutes for admin/superadmin, 2 hours for customer/staff
        $role = (string)($_SESSION['user']['role'] ?? 'customer');
        $maxLifetime = in_array($role, ['admin', 'super_admin'], true) ? 1800 : (int)Env::get('SESSION_LIFETIME', 7200);

        if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxLifetime)) {
            self::logout();
            return false;
        }

        // 2. Validate password_version & active status against database (invalidates other sessions upon password change)
        $userId = (int)$_SESSION['user']['id'];
        $sessionPasswordVersion = (int)($_SESSION['user']['password_version'] ?? 1);

        try {
            $dbUser = Database::fetchOne("SELECT status, password_version FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
            if (!$dbUser || ($dbUser['status'] ?? 'active') !== 'active' || (int)($dbUser['password_version'] ?? 1) !== $sessionPasswordVersion) {
                self::logout();
                return false;
            }
        } catch (\Throwable $e) {
            Logger::error("Error checking user password_version: " . $e->getMessage());
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function user(): ?array
    {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        self::init();
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    public static function role(): ?string
    {
        self::init();
        return $_SESSION['user']['role'] ?? null;
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    public static function isSuperAdmin(): bool
    {
        return self::hasRole('super_admin');
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('admin') || self::hasRole('super_admin');
    }

    public static function isStaff(): bool
    {
        return self::hasRole('staff');
    }

    public static function isCustomer(): bool
    {
        return self::hasRole('customer');
    }
}
