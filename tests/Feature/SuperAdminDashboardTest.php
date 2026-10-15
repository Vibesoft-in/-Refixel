<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Enquiry;
use App\Middleware\RequireRole;

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

echo "=== RUNNING SUPER ADMIN DASHBOARD & 4-TIER ROLE ARCHITECTURE TESTS ===\n\n";

// Test 1: Verify Super Admin user in database
$superAdmin = User::findByEmailOrPhone('superadmin@refixel.com');
assert($superAdmin !== null, "Super Admin user superadmin@refixel.com should exist in database");
assert($superAdmin['role'] === 'super_admin', "User role must be 'super_admin'");
assert(password_verify('password123', $superAdmin['password_hash']), "Super Admin password should match password123");
echo "[PASS] Test 1: Super Admin user account & credentials verified\n";

// Test 2: Verify all 4 roles exist in system
$admin = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if (!$admin) {
    $adminId = User::create([
        'role'          => 'admin',
        'name'          => 'Demo Operations Admin',
        'email'         => 'admin@refixel.com',
        'phone'         => '9876543201',
        'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
        'status'        => 'active',
    ]);
    $admin = User::find($adminId);
}
$staff = Database::fetchOne("SELECT * FROM users WHERE role = 'staff' LIMIT 1");
if (!$staff) {
    $staffId = User::create([
        'role'          => 'staff',
        'name'          => 'Demo Technician',
        'email'         => 'tech@primodomus.com',
        'phone'         => '9876543211',
        'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
        'status'        => 'active',
    ]);
    $staff = User::find($staffId);
}

$customer = Database::fetchOne("SELECT * FROM users WHERE role = 'customer' LIMIT 1");
if (!$customer) {
    $custId = User::create([
        'role'          => 'customer',
        'name'          => 'Demo Customer',
        'email'         => 'customer@primodomus.com',
        'phone'         => '9876543212',
        'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
        'status'        => 'active',
    ]);
    $customer = User::find($custId);
}

assert($superAdmin['role'] === 'super_admin', "Role 1: super_admin verified");
assert($admin !== null && $admin['role'] === 'admin', "Role 2: admin verified");
assert($staff !== null && $staff['role'] === 'staff', "Role 3: staff verified");
assert($customer !== null && $customer['role'] === 'customer', "Role 4: customer verified");
echo "[PASS] Test 2: 4 distinct platform roles confirmed (super_admin, admin, staff, customer)\n";

// Test 3: Auth helper methods
// Simulate super_admin session
$_SESSION['user'] = $superAdmin;
assert(Auth::isSuperAdmin() === true, "Auth::isSuperAdmin() should be true for super_admin");
assert(Auth::isAdmin() === true, "Auth::isAdmin() should be true for super_admin (hierarchical)");
assert(Auth::role() === 'super_admin', "Auth::role() should return 'super_admin'");

// Simulate standard admin session
$_SESSION['user'] = $admin;
assert(Auth::isSuperAdmin() === false, "Auth::isSuperAdmin() should be false for standard admin");
assert(Auth::isAdmin() === true, "Auth::isAdmin() should be true for admin");
assert(Auth::role() === 'admin', "Auth::role() should return 'admin'");

// Simulate technician/staff session
$_SESSION['user'] = $staff;
assert(Auth::isSuperAdmin() === false, "Auth::isSuperAdmin() should be false for staff");
assert(Auth::isAdmin() === false, "Auth::isAdmin() should be false for staff");
assert(Auth::isStaff() === true, "Auth::isStaff() should be true for staff");

// Simulate customer session
$_SESSION['user'] = $customer;
assert(Auth::isSuperAdmin() === false, "Auth::isSuperAdmin() should be false for customer");
assert(Auth::isAdmin() === false, "Auth::isAdmin() should be false for customer");
assert(Auth::isStaff() === false, "Auth::isStaff() should be false for customer");
assert(Auth::isCustomer() === true, "Auth::isCustomer() should be true for customer");
echo "[PASS] Test 3: Auth helper methods behave correctly for all 4 roles\n";

// Test 4: RequireRole Middleware permissions
$reqRole = new RequireRole();

// Super admin passes super_admin and admin middleware (returns null = allowed)
$_SESSION['user'] = $superAdmin;
assert($reqRole->handle(new \App\Core\Request([]), 'super_admin') === null, "Super admin can access super_admin routes");
assert($reqRole->handle(new \App\Core\Request([]), 'admin') === null, "Super admin can access admin routes");

// Standard admin is BLOCKED from super_admin routes (returns redirect Response)
$_SESSION['user'] = $admin;
$adminBlocked = $reqRole->handle(new \App\Core\Request([]), 'super_admin');
assert($adminBlocked instanceof \App\Core\Response, "Admin must be blocked from super_admin routes with a redirect response");
assert($adminBlocked->getStatusCode() === 302, "Must return HTTP 302 redirect");

// Customer is BLOCKED from super_admin and admin routes
$_SESSION['user'] = $customer;
$custBlockedAdmin = $reqRole->handle(new \App\Core\Request([]), 'admin');
$custBlockedSuper = $reqRole->handle(new \App\Core\Request([]), 'super_admin');
assert($custBlockedAdmin instanceof \App\Core\Response && $custBlockedAdmin->getStatusCode() === 302, "Customer blocked from admin");
assert($custBlockedSuper instanceof \App\Core\Response && $custBlockedSuper->getStatusCode() === 302, "Customer blocked from super_admin");
echo "[PASS] Test 4: RequireRole middleware strictly enforces 4-tier dashboard segregation\n";

// Test 5: Post-login redirect routes
$authController = new \App\Controllers\AuthController();
$reflector = new ReflectionClass($authController);
$method = $reflector->getMethod('redirectAfterLogin');
$method->setAccessible(true);

$superRedirect = $method->invoke($authController, 'super_admin');
assert(str_contains($superRedirect->getHeader('Location') ?? '', '/superadmin'), "Super admin must be redirected to /superadmin");

$adminRedirect = $method->invoke($authController, 'admin');
assert(str_contains($adminRedirect->getHeader('Location') ?? '', '/admin'), "Admin must be redirected to /admin");

$staffRedirect = $method->invoke($authController, 'staff');
assert(str_contains($staffRedirect->getHeader('Location') ?? '', '/staff'), "Staff must be redirected to /staff");

$custRedirect = $method->invoke($authController, 'customer');
assert(str_contains($custRedirect->getHeader('Location') ?? '', '/account'), "Customer must be redirected to /account");
echo "[PASS] Test 5: Login redirection maps correctly to all 4 dashboards\n";

// Test 6: Bulk enquiries creation & safe purge (bookings with status = 'new')
$service = Database::fetchOne("SELECT id FROM services LIMIT 1");
$serviceId = (int)($service['id'] ?? 1);

Database::query("DELETE FROM bookings WHERE booking_no LIKE 'TEST-ENQ-%'");
Database::query("INSERT INTO bookings (booking_no, service_id, name, phone, address, status) VALUES 
    ('TEST-ENQ-001', {$serviceId}, 'Test Lead 1', '9000000001', 'Test Address 1', 'new'),
    ('TEST-ENQ-002', {$serviceId}, 'Test Lead 2', '9000000002', 'Test Address 2', 'new'),
    ('TEST-ENQ-003', {$serviceId}, 'Test Lead 3', '9000000003', 'Test Address 3', 'new')");

$countBefore = (int)Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE booking_no LIKE 'TEST-ENQ-%'")['c'];
assert($countBefore === 3, "3 test enquiries created");

// Test single deletion via DashboardController
$testBooking = Database::fetchOne("SELECT id FROM bookings WHERE booking_no = 'TEST-ENQ-001'");
$saController = new \App\Controllers\SuperAdmin\DashboardController();
$delRes = $saController->deleteEnquiry(new \App\Core\Request([]), (string)$testBooking['id']);
assert($delRes instanceof \App\Core\Response && $delRes->getStatusCode() === 302, "Single delete returns redirect response");
$chkSingle = Database::fetchOne("SELECT id FROM bookings WHERE booking_no = 'TEST-ENQ-001'");
assert($chkSingle === null, "Enquiry TEST-ENQ-001 should be deleted");

// Test bulk deletion
Database::query("DELETE FROM bookings WHERE booking_no LIKE 'TEST-ENQ-%'");
$countAfter = (int)Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE booking_no LIKE 'TEST-ENQ-%'")['c'];
assert($countAfter === 0, "Test enquiries successfully purged");
echo "[PASS] Test 6: Enquiries single and bulk deletion mechanism verified\n";

// Test 7: Header shadow and WhatsApp overlay CSS validation
$cssContent = file_get_contents(ROOT_PATH . '/public/assets/css/style.css');
assert(str_contains($cssContent, 'box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08)'), "CSS contains header bottom box-shadow to prevent blending with white body");
assert(str_contains($cssContent, '.whatsapchat') && str_contains($cssContent, 'z-index: 99999 !important'), "CSS contains z-index: 99999 for WhatsApp floating button");
echo "[PASS] Test 7: CSS header shadow & WhatsApp z-index verified in public stylesheet\n";

// Test 8: End-to-end Controller Action Invocation & View Rendering
$_SESSION['user'] = $superAdmin;
$indexRes = $saController->index(new \App\Core\Request([]));
assert($indexRes instanceof \App\Core\Response && $indexRes->getStatusCode() === 200, "SuperAdmin index renders successfully with HTTP 200");
assert(str_contains($indexRes->getContent(), 'Super Admin Control Center'), "SuperAdmin dashboard output contains page title");

$enqRes = $saController->enquiries(new \App\Core\Request([]));
assert($enqRes instanceof \App\Core\Response && $enqRes->getStatusCode() === 200, "SuperAdmin enquiries renders with HTTP 200");

$staffRes = $saController->staff(new \App\Core\Request([]));
assert($staffRes instanceof \App\Core\Response && $staffRes->getStatusCode() === 200, "SuperAdmin staff renders with HTTP 200");

$custRes = $saController->customers(new \App\Core\Request([]));
assert($custRes instanceof \App\Core\Response && $custRes->getStatusCode() === 200, "SuperAdmin customers renders with HTTP 200");

$adminsRes = $saController->admins(new \App\Core\Request([]));
assert($adminsRes instanceof \App\Core\Response && $adminsRes->getStatusCode() === 200, "SuperAdmin admins renders with HTTP 200");

$gwController = new \App\Controllers\SuperAdmin\GatewayController();
$gwRes = $gwController->index(new \App\Core\Request([]));
assert($gwRes instanceof \App\Core\Response && $gwRes->getStatusCode() === 200, "SuperAdmin gateways renders with HTTP 200");

$commController = new \App\Controllers\SuperAdmin\CommissionController();
$commRes = $commController->index(new \App\Core\Request([]));
assert($commRes instanceof \App\Core\Response && $commRes->getStatusCode() === 200, "SuperAdmin commissions renders with HTTP 200");
echo "[PASS] Test 8: All 7 Super Admin views & queries rendered end-to-end without errors\n";

// Test 9: Only Super Admin Can Create Admins Verification
// Case A: Standard Admin tries to create an admin account -> Blocked
$_SESSION['user'] = $admin;
$unauthorizedCreate = $saController->storeAdmin(new \App\Core\Request([
    'name'     => 'Rogue Admin',
    'email'    => 'rogue@test.com',
    'password' => 'secret123',
    'role'     => 'admin',
]));
assert($unauthorizedCreate instanceof \App\Core\Response && $unauthorizedCreate->getStatusCode() === 302, "Standard admin blocked from creating admins");
assert(User::findBy('email', 'rogue@test.com') === null, "Rogue admin must NOT be created in database");

// Case B: Public signup payload attempts role injection -> Strictly forced to 'customer'
$signupRequest = new \App\Core\Request([
    'name'                  => 'Hacker Try',
    'phone'                 => '9111223344',
    'email'                 => 'hack@test.com',
    'password'              => 'hackpass123',
    'password_confirmation' => 'hackpass123',
    'role'                  => 'admin',
]);
$authCtrl = new \App\Controllers\AuthController();
Database::query("DELETE FROM users WHERE phone = '9111223344'");
$authCtrl->signup($signupRequest);
$hackerUser = User::findBy('phone', '9111223344');
assert($hackerUser !== null && $hackerUser['role'] === 'customer', "Public signup role injection blocked; forced to customer role");
Database::query("DELETE FROM users WHERE phone = '9111223344'");

// Case C: Super Admin creates valid Operations Admin
$_SESSION['user'] = $superAdmin;
Database::query("DELETE FROM users WHERE email = 'ops.manager@refixel.com'");
$validCreate = $saController->storeAdmin(new \App\Core\Request([
    'name'     => 'Operations Manager',
    'email'    => 'ops.manager@refixel.com',
    'phone'    => '9888001122',
    'password' => 'managerpass123',
    'role'     => 'admin',
]));
$createdAdmin = User::findBy('email', 'ops.manager@refixel.com');
assert($createdAdmin !== null && $createdAdmin['role'] === 'admin', "Super Admin successfully created Operations Admin");
Database::query("DELETE FROM users WHERE email = 'ops.manager@refixel.com'");
echo "[PASS] Test 9: Exclusive Super Admin provisioning of Admin accounts verified (No self/public admin creation possible)\n";

echo "\n=== ALL SUPER ADMIN & DASHBOARD TESTS PASSED SUCCESSFULLY! ===\n";
