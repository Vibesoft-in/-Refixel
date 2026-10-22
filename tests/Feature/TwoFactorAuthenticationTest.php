<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Encrypter;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Totp;
use App\Core\View;
use App\Middleware\RequireRole;
use App\Models\User;
use App\Controllers\AuthController;
use App\Controllers\SuperAdmin\DashboardController;

class TwoFactorAuthenticationTest
{
    private array $createdUserIds = [];

    public function runAll(): void
    {
        echo "========================================================\n";
        echo "   TEST SUITE: TWO-FACTOR AUTHENTICATION (2FA / TOTP)   \n";
        echo "========================================================\n\n";

        try {
            $this->testTotpGenerationAndRfcTestVectors();
            $this->testTotpReplayAttackRejection();
            $this->testRecoveryCodesSingleUse();
            $this->testMissingAppKeyFailsLoudly();
            $this->testAes256GcmSecretEncryption();
            $this->testLoginWith2faPendingAndVerification();
            $this->testAdminAllowedToAccessDashboardWhen2faNotEnrolled();
            $this->testAdminDashboardRendersOptional2faNoticeWhenNotEnrolled();
            $this->testSuperAdminReset2faReAuthentication();
            $this->testCliResetScript();

            echo "\n========================================================\n";
            echo "   ALL TWO-FACTOR AUTHENTICATION TESTS PASSED! ✓        \n";
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
     * Test 1: Verify RFC 6238 HMAC-SHA1 TOTP generation and official test vector
     */
    public function testTotpGenerationAndRfcTestVectors(): void
    {
        echo "[TEST 1] Testing TOTP generation and RFC 6238 compliance...\n";

        // RFC 6238 Appendix B test vector:
        // Secret in ASCII: "12345678901234567890" -> Base32: GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ
        $rfcSecret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        // Time 59 (step 1) -> 287082
        $code1 = Totp::generateCode($rfcSecret, 1);
        $this->assert($code1 === '287082', "RFC 6238 step 1 code must match 287082 (got {$code1})");

        // Time 1111111109 (step 37037036) -> 081804
        $code2 = Totp::generateCode($rfcSecret, 37037036);
        $this->assert($code2 === '081804', "RFC 6238 step 37037036 code must match 081804 (got {$code2})");

        // Verification of valid current code
        $secret = Totp::generateSecret(16);
        $currentStep = (int)floor(time() / 30);
        $code = Totp::generateCode($secret, $currentStep);
        $matched = Totp::verify($secret, $code, null, 1);
        $this->assert($matched === $currentStep, "Valid TOTP code verified successfully for current step");
    }

    /**
     * Test 2: Verify TOTP Replay attack rejection
     */
    public function testTotpReplayAttackRejection(): void
    {
        echo "\n[TEST 2] Testing TOTP replay attack rejection...\n";

        $secret = Totp::generateSecret(16);
        $currentStep = (int)floor(time() / 30);
        $code = Totp::generateCode($secret, $currentStep);

        // First verification without prior step: must succeed
        $firstMatch = Totp::verify($secret, $code, null, 1);
        $this->assert($firstMatch === $currentStep, "Initial TOTP verification accepted");

        // Second verification with lastStep recorded: must be REJECTED!
        $replayedMatch = Totp::verify($secret, $code, $currentStep, 1);
        $this->assert($replayedMatch === null, "Replayed TOTP code rejected because step <= lastStep");

        // Older step: must also be rejected
        $olderStep = $currentStep - 1;
        $olderCode = Totp::generateCode($secret, $olderStep);
        $olderMatch = Totp::verify($secret, $olderCode, $currentStep, 1);
        $this->assert($olderMatch === null, "Older TOTP code rejected because step <= lastStep");
    }

    /**
     * Test 3: Emergency recovery codes generation and single-use invalidation
     */
    public function testRecoveryCodesSingleUse(): void
    {
        echo "\n[TEST 3] Testing emergency recovery codes generation and single-use...\n";

        $recovery = Totp::generateRecoveryCodes(8);
        $plainCodes = $recovery['plain'];
        $hashedCodes = $recovery['hashed'];

        $this->assert(count($plainCodes) === 8, "Generated 8 emergency recovery codes");
        $this->assert(count($hashedCodes) === 8, "Generated 8 hashed recovery codes");

        // First use of code 0: must match index 0
        $usedCode = $plainCodes[0];
        $matchedIndex = Totp::verifyRecoveryCode($usedCode, $hashedCodes);
        $this->assert($matchedIndex === 0, "Recovery code matched correct index");

        // Simulate single-use deletion from database array
        unset($hashedCodes[$matchedIndex]);
        $hashedCodes = array_values($hashedCodes);
        $this->assert(count($hashedCodes) === 7, "Remaining recovery codes count is 7");

        // Reusing the same recovery code: must fail
        $reusedIndex = Totp::verifyRecoveryCode($usedCode, $hashedCodes);
        $this->assert($reusedIndex === null, "Used recovery code cannot be reused (single-use enforced)");
    }

    /**
     * Test 4: Verify that missing APP_KEY fails loudly and never falls back silently
     */
    public function testMissingAppKeyFailsLoudly(): void
    {
        echo "\n[TEST 4] Testing missing APP_KEY fails loudly without silent plaintext fallback...\n";

        $currentKey = Env::get('APP_KEY');
        try {
            // Unset APP_KEY in Env
            Env::set('APP_KEY', '');

            $threwException = false;
            try {
                Encrypter::encrypt('MY_SECRET_KEY');
            } catch (\RuntimeException $e) {
                $threwException = true;
                $this->assert(str_contains($e->getMessage(), 'APP_KEY environment variable is not configured'), "Encrypter throws loud RuntimeException when APP_KEY is missing");
            }
            $this->assert($threwException, "Encryption did NOT proceed silently when APP_KEY was missing");

            $this->assert(Encrypter::hasKey() === false, "Encrypter::hasKey() returns false when APP_KEY is missing");
        } finally {
            // Restore APP_KEY
            if ($currentKey !== null) {
                Env::set('APP_KEY', $currentKey);
            }
        }
    }

    /**
     * Test 5: Verify AES-256-GCM encryption & decryption
     */
    public function testAes256GcmSecretEncryption(): void
    {
        echo "\n[TEST 5] Testing AES-256-GCM encryption and decryption...\n";

        $secret = 'JBSWY3DPEHPK3PXP';
        $encrypted = Encrypter::encrypt($secret);

        $this->assert(!empty($encrypted), "Encrypted payload is non-empty");
        $this->assert($encrypted !== $secret, "Encrypted payload is not plain text");

        $decrypted = Encrypter::decrypt($encrypted);
        $this->assert($decrypted === $secret, "Decrypted payload exactly matches original secret");

        // Tamper with encrypted payload (modify ciphertext so GCM auth tag fails)
        $data = json_decode(base64_decode($encrypted, true), true);
        $rawCipher = base64_decode($data['value']);
        $rawCipher[0] = $rawCipher[0] === 'A' ? 'B' : 'A';
        $data['value'] = base64_encode($rawCipher);
        $tampered = base64_encode(json_encode($data));

        $tamperedDecrypted = Encrypter::decrypt($tampered);
        $this->assert($tamperedDecrypted === null, "Tampered ciphertext fails GCM authentication tag and returns null");
    }

    /**
     * Test 6: Login flow with 2FA enabled: 2fa_pending interception, verification, and session regeneration
     */
    public function testLoginWith2faPendingAndVerification(): void
    {
        echo "\n[TEST 6] Testing login flow with 2FA pending challenge and session regeneration...\n";

        $user = $this->createTestUser([
            'role'         => 'admin',
            'totp_enabled' => 1,
            'totp_secret'  => Encrypter::encrypt('JBSWY3DPEHPK3PXP'),
            'totp_last_step' => 100,
        ]);

        $_SESSION = [];
        $controller = new AuthController();

        // 1. Submit password
        $req = new Request([], [
            'identifier' => $user['email'],
            'password'   => 'SecretPassword123!',
        ], [], [], [], [
            'REQUEST_METHOD' => 'POST',
            'REMOTE_ADDR'    => '127.0.0.1',
        ]);

        $res = $controller->login($req);
        $loc = (string)$res->getHeader('Location');
        $this->assert($res->getStatusCode() === 302, "Login returns 302 redirect");
        $this->assert(str_ends_with($loc, '/login/2fa'), "User with 2FA redirected to /login/2fa (got {$loc})");
        $this->assert(!empty($_SESSION['2fa_pending']), "2fa_pending flag stored in session");
        $this->assert(empty($_SESSION['user']), "User is NOT logged in yet before 2FA code verification");

        // 2. Submit wrong TOTP code
        $verifyReqBad = new Request([], ['code' => '000000'], [], [], [], [
            'REQUEST_METHOD' => 'POST',
            'REMOTE_ADDR'    => '127.0.0.1',
        ]);
        $resBad = $controller->verify2fa($verifyReqBad);
        $this->assert(str_ends_with((string)$resBad->getHeader('Location'), '/login/2fa'), "Wrong TOTP code redirected back to /login/2fa");
        $this->assert(empty($_SESSION['user']), "User still NOT logged in after wrong code");

        // 3. Submit valid TOTP code
        $validCode = Totp::generateCode('JBSWY3DPEHPK3PXP');
        $verifyReqGood = new Request([], ['code' => $validCode], [], [], [], [
            'REQUEST_METHOD' => 'POST',
            'REMOTE_ADDR'    => '127.0.0.1',
        ]);
        $resGood = $controller->verify2fa($verifyReqGood);
        $this->assert($resGood->getStatusCode() === 302, "Valid code verification returns 302 redirect");
        $this->assert(!empty($_SESSION['user']['id']), "User is now fully logged in after valid 2FA code");
        $this->assert(empty($_SESSION['2fa_pending']), "2fa_pending session flag cleared after successful verification");

        // Verify replay prevention updated in DB
        $updatedUser = User::find((int)$user['id']);
        $this->assert(((int)$updatedUser['totp_last_step']) > 100, "totp_last_step updated in database to prevent replay");
    }

    /**
     * Test 7: Verify Admin without 2FA can freely access dashboard (non-blocking voluntary 2FA)
     */
    public function testAdminAllowedToAccessDashboardWhen2faNotEnrolled(): void
    {
        echo "\n[TEST 7] Testing admin allowed to freely access dashboard when 2FA is not enrolled...\n";

        $admin = $this->createTestUser([
            'role'         => 'admin',
            'totp_enabled' => 0,
        ]);

        Auth::login($admin);
        $this->assert(Auth::check() === true, "Admin logged into session");

        $middleware = new RequireRole();
        $req = new Request([], [], [], [], [], [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/admin/dashboard',
        ]);

        $res = $middleware->handle($req, 'admin');
        $this->assert($res === null, "RequireRole does NOT block admin without 2FA (passes through freely)");
    }

    /**
     * Test: Verify Dashboard renders non-blocking 2FA notice for unenrolled admin
     */
    public function testAdminDashboardRendersOptional2faNoticeWhenNotEnrolled(): void
    {
        echo "\n[TEST 7b] Testing Dashboard renders non-blocking 2FA notice for unenrolled admin...\n";

        $admin = $this->createTestUser([
            'role'         => 'admin',
            'totp_enabled' => 0,
        ]);

        Auth::login($admin);

        $htmlUnenrolled = View::partial('2fa-notice');
        $this->assert(str_contains($htmlUnenrolled, 'Two-Factor Authentication (2FA) Recommended'), "Dashboard notice rendered when 2FA is not enabled");
        $this->assert(str_contains($htmlUnenrolled, '/2fa/setup'), "Dashboard notice contains link to 2FA setup");

        // When 2FA is active, notice must NOT render
        $_SESSION['user']['totp_enabled'] = true;
        $htmlEnrolled = View::partial('2fa-notice');
        $this->assert(empty(trim($htmlEnrolled)), "Dashboard notice is hidden when 2FA is active");
    }

    /**
     * Test 8: Super Admin Reset 2FA with mandatory Password Re-authentication
     */
    public function testSuperAdminReset2faReAuthentication(): void
    {
        echo "\n[TEST 8] Testing Super Admin 2FA reset requires password re-authentication...\n";

        // Create Super Admin
        $superAdmin = $this->createTestUser([
            'role' => 'super_admin',
            'password_hash' => password_hash('SuperAdminSecret123!', PASSWORD_BCRYPT),
        ]);

        // Create Admin with active 2FA
        $admin = $this->createTestUser([
            'role'         => 'admin',
            'totp_enabled' => 1,
            'totp_secret'  => Encrypter::encrypt('SECRET123'),
        ]);

        Auth::login($superAdmin);

        $controller = new DashboardController();

        // 1. Try to reset 2FA with WRONG superadmin password
        $badReq = new Request([], [
            'superadmin_password' => 'WrongPassword',
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $resBad = $controller->resetAdmin2fa($badReq, (string)$admin['id']);
        $this->assert($resBad->getStatusCode() === 302, "Reset returns 302 redirect");

        $adminAfterBad = User::find((int)$admin['id']);
        $this->assert((int)$adminAfterBad['totp_enabled'] === 1, "2FA remains enabled when super admin password is wrong");

        // 2. Try to reset 2FA with CORRECT superadmin password
        $goodReq = new Request([], [
            'superadmin_password' => 'SuperAdminSecret123!',
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);

        $resGood = $controller->resetAdmin2fa($goodReq, (string)$admin['id']);
        $this->assert($resGood->getStatusCode() === 302, "Reset returns 302 redirect");

        $adminAfterGood = User::find((int)$admin['id']);
        $this->assert((int)$adminAfterGood['totp_enabled'] === 0, "2FA is now DISABLED for target admin after re-authentication");
        $this->assert(empty($adminAfterGood['totp_secret']), "totp_secret wiped from database");
    }

    /**
     * Test 9: CLI reset script scripts/reset_2fa.php
     */
    public function testCliResetScript(): void
    {
        echo "\n[TEST 9] Testing CLI emergency reset script scripts/reset_2fa.php...\n";

        $admin = $this->createTestUser([
            'role'         => 'admin',
            'totp_enabled' => 1,
            'totp_secret'  => Encrypter::encrypt('TESTCLISECRET'),
        ]);

        $cmd = "php " . escapeshellarg(__DIR__ . '/../../scripts/reset_2fa.php') . " " . escapeshellarg((string)$admin['id']);
        $output = shell_exec($cmd);

        $this->assert(str_contains($output, 'SUCCESS: Two-Factor Authentication (2FA) has been RESET'), "CLI output confirms successful 2FA reset");

        $updated = User::find((int)$admin['id']);
        $this->assert((int)$updated['totp_enabled'] === 0, "Database reflects 2FA disabled after CLI script run");
        $this->assert(empty($updated['totp_secret']), "Database reflects totp_secret wiped after CLI script run");
    }

    private function createTestUser(array $overrides = []): array
    {
        $id = random_int(100000, 999999);
        $data = array_merge([
            'name'          => '2FA Test User ' . $id,
            'email'         => "2fa_test_{$id}@refixel.test",
            'phone'         => '98' . str_pad((string)$id, 8, '0', STR_PAD_LEFT),
            'role'          => 'admin',
            'status'        => 'active',
            'password_hash' => password_hash('SecretPassword123!', PASSWORD_BCRYPT),
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
$test = new TwoFactorAuthenticationTest();
$test->runAll();
