<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Middleware\RequireRole;
use App\Models\Job;
use App\Models\User;

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

echo "=== RUNNING ROLE ACCESS & PERMISSION TESTS ===\n";

$adminUser = User::findByEmailOrPhone('admin@refixel.com');
$techUser  = User::findByEmailOrPhone('tech@refixel.com');
$custUser  = User::findByEmailOrPhone('customer@refixel.com');

$middleware = new RequireRole();
$request = new Request();

// Scenario 1: Customer attempts to access Admin route
Auth::login($custUser);
$res = $middleware->handle($request, 'admin');
assert($res !== null, "Customer accessing admin route must be intercepted");
assert($res->getStatusCode() === 302, "Expected redirect status");
echo "Scenario 1: Customer blocked from /admin (PASSED)\n";

// Scenario 2: Technician attempts to access Admin route
Auth::login($techUser);
$res = $middleware->handle($request, 'admin');
assert($res !== null, "Technician accessing admin route must be intercepted");
echo "Scenario 2: Technician blocked from /admin (PASSED)\n";

// Scenario 3: Customer attempts to access Staff route
Auth::login($custUser);
$res = $middleware->handle($request, 'staff');
assert($res !== null, "Customer accessing staff route must be intercepted");
echo "Scenario 3: Customer blocked from /staff (PASSED)\n";

// Scenario 4: Admin accessing Admin route -> Allowed (returns null)
Auth::login($adminUser);
$res = $middleware->handle($request, 'admin');
assert($res === null, "Admin must be permitted on admin route");
echo "Scenario 4: Admin authorized for /admin (PASSED)\n";

// Scenario 5: Staff accessing Staff route -> Allowed (returns null)
Auth::login($techUser);
$res = $middleware->handle($request, 'staff');
assert($res === null, "Staff must be permitted on staff route");
echo "Scenario 5: Staff authorized for /staff (PASSED)\n";

// Scenario 6: Technician query isolation (SQL MUST strictly filter by staff_id)
$techJobs = Job::findByStaff((int)$techUser['id']);
assert(is_array($techJobs), "Job list must return array");
foreach ($techJobs as $j) {
    assert((int)$j['staff_id'] === (int)$techUser['id'], "Job staff_id must match authenticated technician");
}
echo "Scenario 6: Technician query isolation strictly filtered by staff_id (PASSED)\n";

Auth::logout();

echo "=== ALL ROLE ACCESS TESTS PASSED SUCCESSFULLY! ===\n";
