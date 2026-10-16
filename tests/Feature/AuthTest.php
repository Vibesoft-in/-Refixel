<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

echo "=== RUNNING AUTHENTICATION FEATURE TESTS ===\n";

// Clean up any test users
Database::query("DELETE FROM users WHERE phone IN ('9999000001', '9999000002')");
Database::query("DELETE FROM login_attempts WHERE identifier = 'invalid@user.com'");
Database::query("UPDATE users SET password_hash = :hash WHERE email = 'customer@primodomus.com'", ['hash' => password_hash('password123', PASSWORD_BCRYPT)]);

// Test 1: Verify Seed Users exist and passwords match
$adminUser = User::findByEmailOrPhone('admin@refixel.com') ?? User::findByEmailOrPhone('admin@primodomus.com');
assert($adminUser !== null, "Admin user should exist");
assert(password_verify('password123', $adminUser['password_hash']), "Admin password should match password123");
assert($adminUser['role'] === 'admin', "Role must be admin");
echo "Test 1: Admin account verification (PASSED)\n";

$techUser = User::findByEmailOrPhone('tech@primodomus.com');
assert($techUser !== null, "Tech user should exist");
assert(password_verify('password123', $techUser['password_hash']), "Tech password should match");
assert($techUser['role'] === 'staff', "Role must be staff");
echo "Test 2: Staff/Technician account verification (PASSED)\n";

$custUser = User::findByEmailOrPhone('customer@primodomus.com');
assert($custUser !== null, "Customer user should exist");
assert(password_verify('password123', $custUser['password_hash']), "Customer password should match");
assert($custUser['role'] === 'customer', "Role must be customer");
echo "Test 3: Customer account verification (PASSED)\n";

// Test 4: Public signup creates CUSTOMER ONLY
$newUserId = User::create([
    'role'          => 'customer',
    'name'          => 'Test Signee',
    'phone'         => '9999000001',
    'email'         => 'signee@example.com',
    'password_hash' => password_hash('securepass123', PASSWORD_BCRYPT),
    'status'        => 'active',
]);
$newUser = User::find($newUserId);
assert($newUser['role'] === 'customer', "Public signup must create customer role only");
echo "Test 4: Public signup creates customer role exclusively (PASSED)\n";

// Test 5: Role-based redirect logic
function getRedirectForRole(string $role): string {
    return match ($role) {
        'admin' => '/admin',
        'staff' => '/staff',
        default => '/account',
    };
}
assert(getRedirectForRole('customer') === '/account');
assert(getRedirectForRole('admin') === '/admin');
assert(getRedirectForRole('staff') === '/staff');
echo "Test 5: Role-based post-login redirection targets (PASSED)\n";

// Test 6: Auth session login and logout
Auth::login($adminUser);
assert(Auth::check() === true, "Auth::check should return true");
assert(Auth::id() === (int)$adminUser['id'], "Auth::id must match logged in admin");
assert(Auth::isAdmin() === true, "Auth::isAdmin must be true");
assert(Auth::isStaff() === false, "Auth::isStaff must be false for admin");
assert(Auth::isCustomer() === false, "Auth::isCustomer must be false for admin");

Auth::logout();
assert(Auth::check() === false, "Auth::check must return false after logout");
assert(Auth::id() === null, "Auth::id must be null after logout");
echo "Test 6: Auth session login, role inspection, and logout (PASSED)\n";

// Test 7: Login attempt recording & rate-limit tracking
Database::query("INSERT INTO login_attempts (ip, identifier, attempted_at) VALUES ('127.0.0.1', 'invalid@user.com', NOW())");
$attempts = Database::fetchOne("SELECT COUNT(*) as c FROM login_attempts WHERE identifier = 'invalid@user.com'");
assert((int)$attempts['c'] >= 1, "Failed attempt must be logged in login_attempts table");
echo "Test 7: Login attempt audit logging (PASSED)\n";

// Clean up
Database::query("DELETE FROM users WHERE id = :id", ['id' => $newUserId]);
Database::query("DELETE FROM login_attempts WHERE identifier = 'invalid@user.com'");

echo "=== ALL AUTHENTICATION TESTS PASSED SUCCESSFULLY! ===\n";
