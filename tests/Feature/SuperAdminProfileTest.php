<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Controllers\SuperAdmin\DashboardController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Models\User;

class SuperAdminProfileTest
{
    private array $testUserIds = [];

    public function runAll(): void
    {
        echo "========================================================\n";
        echo "   TEST SUITE: SUPER ADMIN PROFILE & SECURITY SETTINGS  \n";
        echo "========================================================\n\n";

        try {
            $this->testSuperAdminCanAccessProfilePage();
            $this->testSuperAdminCanUpdateProfileDetails();
            $this->testSuperAdminPasswordValidationFailures();
            $this->testSuperAdminPasswordChangeSuccess();
            $this->testSuperAdminDisableMy2fa();

            echo "\n========================================================\n";
            echo "   ALL SUPER ADMIN PROFILE TESTS PASSED! ✓              \n";
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

    private function createSuperAdmin(array $overrides = []): array
    {
        $unique = bin2hex(random_bytes(4));
        $data = array_merge([
            'role'                 => 'super_admin',
            'name'                 => 'Test Super Admin ' . $unique,
            'email'                => "sa.{$unique}@example.com",
            'phone'                => '98' . random_int(10000000, 99999999),
            'password_hash'        => password_hash('SuperSecretPassword123!', PASSWORD_BCRYPT),
            'password_version'     => 1,
            'status'               => 'active',
            'must_change_password' => 0,
            'totp_enabled'         => 0,
        ], $overrides);

        $id = User::create($data);
        $this->testUserIds[] = (int)$id;
        return User::find((int)$id);
    }

    private function cleanup(): void
    {
        if (!empty($this->testUserIds)) {
            $placeholders = implode(',', array_fill(0, count($this->testUserIds), '?'));
            Database::query("DELETE FROM users WHERE id IN ($placeholders)", $this->testUserIds);
        }
        Auth::logout();
    }

    public function testSuperAdminCanAccessProfilePage(): void
    {
        echo "[TEST 1] Testing Super Admin can view Profile & Security page...\n";

        $sa = $this->createSuperAdmin();
        Auth::login($sa);

        $controller = new DashboardController();
        $req = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/superadmin/profile']);

        $res = $controller->profile($req);
        $this->assert($res->getStatusCode() === 200, "Profile page returns HTTP 200");
        $body = $res->getBody();
        $this->assert(str_contains($body, 'Profile & Security Controls'), "Page renders Profile header");
        $this->assert(str_contains($body, $sa['name']), "Page displays current admin name");
        $this->assert(str_contains($body, $sa['email']), "Page displays current admin email");
        $this->assert(str_contains($body, 'Change Super Admin Password'), "Page contains change password form");
    }

    public function testSuperAdminCanUpdateProfileDetails(): void
    {
        echo "\n[TEST 2] Testing Super Admin profile details update...\n";

        $sa = $this->createSuperAdmin();
        Auth::login($sa);

        $controller = new DashboardController();
        $newName = 'Updated Root Admin ' . bin2hex(random_bytes(2));
        $newEmail = 'updated.root.' . bin2hex(random_bytes(3)) . '@example.com';
        $newPhone = '97' . random_int(10000000, 99999999);

        $req = new Request([], [
            'name'  => $newName,
            'email' => $newEmail,
            'phone' => $newPhone,
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile']);

        $res = $controller->updateProfile($req);
        $this->assert($res->getStatusCode() === 302, "Profile update returns 302 redirect");

        $updated = User::find((int)$sa['id']);
        $this->assert($updated['name'] === $newName, "User name updated in database");
        $this->assert($updated['email'] === $newEmail, "User email updated in database");
        $this->assert($updated['phone'] === $newPhone, "User phone updated in database");
        $this->assert($_SESSION['user']['name'] === $newName, "Session name synchronized");
        $this->assert($_SESSION['user']['email'] === $newEmail, "Session email synchronized");
    }

    public function testSuperAdminPasswordValidationFailures(): void
    {
        echo "\n[TEST 3] Testing password change validation error handling...\n";

        $sa = $this->createSuperAdmin([
            'password_hash' => password_hash('CorrectPassword123!', PASSWORD_BCRYPT),
        ]);
        Auth::login($sa);

        $controller = new DashboardController();

        // 1. Wrong current password
        $req1 = new Request([], [
            'current_password'          => 'WrongPassword',
            'new_password'              => 'BrandNewPassword123!',
            'new_password_confirmation' => 'BrandNewPassword123!',
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile/password']);

        $res1 = $controller->updatePassword($req1);
        $this->assert($res1->getStatusCode() === 302, "Returns 302 on wrong current password");
        $this->assert(password_verify('CorrectPassword123!', User::find((int)$sa['id'])['password_hash']), "Password remained unchanged");

        // 2. Mismatched confirmation
        $req2 = new Request([], [
            'current_password'          => 'CorrectPassword123!',
            'new_password'              => 'BrandNewPassword123!',
            'new_password_confirmation' => 'DifferentPassword123!',
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile/password']);

        $res2 = $controller->updatePassword($req2);
        $this->assert($res2->getStatusCode() === 302, "Returns 302 on mismatched confirmation");

        // 3. Password too short (<6 characters)
        $req3 = new Request([], [
            'current_password'          => 'CorrectPassword123!',
            'new_password'              => '12345',
            'new_password_confirmation' => '12345',
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile/password']);

        $res3 = $controller->updatePassword($req3);
        $this->assert($res3->getStatusCode() === 302, "Returns 302 on password under 6 characters");
    }

    public function testSuperAdminPasswordChangeSuccess(): void
    {
        echo "\n[TEST 4] Testing successful password change and session sync...\n";

        $sa = $this->createSuperAdmin([
            'password_hash'    => password_hash('MyOriginalPass123!', PASSWORD_BCRYPT),
            'password_version' => 1,
        ]);
        Auth::login($sa);

        $controller = new DashboardController();
        $req = new Request([], [
            'current_password'          => 'MyOriginalPass123!',
            'new_password'              => 'MyNewSecurePass999!',
            'new_password_confirmation' => 'MyNewSecurePass999!',
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile/password']);

        $res = $controller->updatePassword($req);
        $this->assert($res->getStatusCode() === 302, "Successful change returns 302 redirect");

        $updated = User::find((int)$sa['id']);
        $this->assert(password_verify('MyNewSecurePass999!', $updated['password_hash']), "New password verifies with bcrypt");
        $this->assert((int)$updated['password_version'] === 2, "Password version incremented to 2");
        $this->assert((int)$_SESSION['user']['password_version'] === 2, "Session password version updated");
    }

    public function testSuperAdminDisableMy2fa(): void
    {
        echo "\n[TEST 5] Testing Super Admin disabling their own 2FA with password authorization...\n";

        $sa = $this->createSuperAdmin([
            'password_hash'     => password_hash('SuperPass123!', PASSWORD_BCRYPT),
            'totp_enabled'      => 1,
            'totp_secret'       => 'ENCRYPTED_SECRET',
            'totp_confirmed_at' => date('Y-m-d H:i:s'),
        ]);
        Auth::login($sa);

        $controller = new DashboardController();

        // 1. Wrong password cannot disable 2FA
        $reqBad = new Request([], [
            'password' => 'WrongPass',
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile/disable-2fa']);

        $resBad = $controller->disableMy2fa($reqBad);
        $this->assert($resBad->getStatusCode() === 302, "Returns 302 redirect");
        $this->assert((int)User::find((int)$sa['id'])['totp_enabled'] === 1, "2FA remains enabled when password wrong");

        // 2. Correct password disables 2FA
        $reqGood = new Request([], [
            'password' => 'SuperPass123!',
        ], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/profile/disable-2fa']);

        $resGood = $controller->disableMy2fa($reqGood);
        $this->assert($resGood->getStatusCode() === 302, "Returns 302 redirect");
        $updated = User::find((int)$sa['id']);
        $this->assert((int)$updated['totp_enabled'] === 0, "2FA is disabled in database");
        $this->assert($updated['totp_secret'] === null, "totp_secret cleared from database");
        $this->assert($_SESSION['user']['totp_enabled'] === false, "Session totp_enabled updated to false");
    }
}

// Run if executed directly
$test = new SuperAdminProfileTest();
$test->runAll();
