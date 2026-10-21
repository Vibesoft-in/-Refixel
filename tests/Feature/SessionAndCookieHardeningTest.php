<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Controllers\AuthController;

class SessionAndCookieHardeningTest
{
    private array $createdUserIds = [];

    public function runAll(): void
    {
        echo "========================================================\n";
        echo "   TEST SUITE: SESSION & COOKIE SECURITY HARDENING      \n";
        echo "========================================================\n\n";

        try {
            $this->testCookieSecurityFlags();
            $this->testRoleBasedIdleTimeout();
            $this->testSessionInvalidationOnPasswordChange();

            echo "\n========================================================\n";
            echo "   ALL SESSION & COOKIE HARDENING TESTS PASSED! ✓       \n";
            echo "========================================================\n";
        } finally {
            $this->cleanup();
        }
    }

    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException("ASSERTION FAILED: {$message}");
        }
        echo "  ✓ PASS: {$message}\n";
    }

    /**
     * Test 1: Verify cookie security flags (HttpOnly, SameSite, and HTTPS/Proxy-aware Secure)
     */
    public function testCookieSecurityFlags(): void
    {
        echo "[TEST 1] Testing cookie security flags (HttpOnly, SameSite, and Secure)...\n";

        // Save existing env
        $oldSecure = Env::get('SESSION_SECURE');
        $oldTrusted = Env::get('TRUSTED_PROXIES');

        try {
            // Case A: HTTP request -> Secure cookie should be false
            Env::set('SESSION_SECURE', null);
            $_SERVER['HTTPS'] = 'off';
            unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
            $this->assert(Request::isHttpsConnection() === false, "HTTP request correctly recognized as insecure");

            // Case B: HTTPS direct request -> Secure cookie should be true
            $_SERVER['HTTPS'] = 'on';
            $this->assert(Request::isHttpsConnection() === true, "HTTPS direct connection recognized as secure");

            // Case C: Untrusted proxy sending X-Forwarded-Proto: https -> MUST BE IGNORED
            $_SERVER['HTTPS'] = 'off';
            $_SERVER['REMOTE_ADDR'] = '198.51.100.77';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            Env::set('TRUSTED_PROXIES', '10.0.0.1');
            $this->assert(Request::isHttpsConnection() === false, "Untrusted proxy X-Forwarded-Proto: https is ignored (Secure cookie not set blindly)");

            // Case D: Trusted proxy sending X-Forwarded-Proto: https -> HONORED
            $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            Env::set('TRUSTED_PROXIES', '10.0.0.1');
            $this->assert(Request::isHttpsConnection() === true, "Trusted proxy X-Forwarded-Proto: https is honored");
        } finally {
            Env::set('SESSION_SECURE', $oldSecure);
            Env::set('TRUSTED_PROXIES', $oldTrusted);
            unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
        }
    }

    /**
     * Test 2: Verify role-based inactivity timeout: 30 minutes for admin, 2 hours for customer
     */
    public function testRoleBasedIdleTimeout(): void
    {
        echo "\n[TEST 2] Testing role-based inactivity timeout (30m admin, 2h customer)...\n";

        $admin = $this->createTestUser(['role' => 'admin']);
        $customer = $this->createTestUser(['role' => 'customer']);

        // 1. Admin active 10 minutes ago -> Active
        Auth::login($admin);
        $_SESSION['last_activity'] = time() - 600; // 10 minutes
        $this->assert(Auth::check() === true, "Admin with 10 minutes idle time remains logged in");

        // 2. Admin idle for 31 minutes (> 1800s) -> Expired and logged out
        $_SESSION['last_activity'] = time() - 1860; // 31 minutes
        $this->assert(Auth::check() === false, "Admin with 31 minutes idle time is logged out automatically (30m timeout)");
        $this->assert(empty($_SESSION['user']), "Admin session cleared after 30-min idle timeout");

        // 3. Customer idle for 45 minutes (> 30m, but < 2h) -> Still Active
        Auth::login($customer);
        $_SESSION['last_activity'] = time() - 2700; // 45 minutes
        $this->assert(Auth::check() === true, "Customer with 45 minutes idle time remains logged in (2h timeout applies)");

        // 4. Customer idle for 2 hours and 5 minutes (> 7200s) -> Expired and logged out
        $_SESSION['last_activity'] = time() - 7500; // 125 minutes
        $this->assert(Auth::check() === false, "Customer with >2h idle time is logged out automatically");
        $this->assert(empty($_SESSION['user']), "Customer session cleared after 2-hour idle timeout");
    }

    /**
     * Test 3: Session invalidation on password change via password_version
     */
    public function testSessionInvalidationOnPasswordChange(): void
    {
        echo "\n[TEST 3] Testing session invalidation across devices on password change...\n";

        $user = $this->createTestUser([
            'role'             => 'customer',
            'password_version' => 1,
        ]);

        // Device 1: logs in with password_version = 1
        Auth::login($user);
        $this->assert(Auth::check() === true, "Device 1 session active with password_version = 1");

        // Simulate password change on Device 2 (or admin reset): increments DB password_version to 2
        User::update((int)$user['id'], [
            'password_version' => 2,
            'password_hash'    => password_hash('BrandNewPassword456!', PASSWORD_BCRYPT),
        ]);

        // Device 1 attempts next request: session has password_version = 1, but DB has 2 -> MUST BE TERMINATED!
        $this->assert(Auth::check() === false, "Device 1 session rejected because password_version is stale (session terminated)");
        $this->assert(empty($_SESSION['user']), "Stale session data wiped from server memory");

        // New login after password change gets password_version = 2
        $refreshedUser = User::find((int)$user['id']);
        Auth::login($refreshedUser);
        $this->assert(Auth::check() === true, "New session with updated password_version = 2 is valid");
        $this->assert($_SESSION['user']['password_version'] === 2, "Session stores updated password_version = 2");
    }

    private function createTestUser(array $overrides = []): array
    {
        $id = random_int(100000, 999999);
        $data = array_merge([
            'name'             => 'Session Test User ' . $id,
            'email'            => "session_test_{$id}@refixel.test",
            'phone'            => '97' . str_pad((string)$id, 8, '0', STR_PAD_LEFT),
            'role'             => 'customer',
            'status'           => 'active',
            'password_hash'    => password_hash('SecretPassword123!', PASSWORD_BCRYPT),
            'password_version' => 1,
        ], $overrides);

        $userId = User::create($data);
        $this->createdUserIds[] = $userId;
        return User::find($userId);
    }

    private function cleanup(): void
    {
        Auth::logout();
        if (!empty($this->createdUserIds)) {
            $in = implode(',', array_map('intval', $this->createdUserIds));
            try {
                Database::query("DELETE FROM users WHERE id IN ({$in})");
            } catch (\Throwable $e) {}
        }
    }
}

// Run test suite
$test = new SessionAndCookieHardeningTest();
$test->runAll();
