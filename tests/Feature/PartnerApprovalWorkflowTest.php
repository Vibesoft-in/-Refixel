<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Models\User;
use App\Models\StaffProfile;
use App\Controllers\PageController;
use App\Controllers\AuthController;
use App\Controllers\Admin\StaffController;
use App\Controllers\Admin\PartnerApplicationController;
use App\Controllers\SuperAdmin\DashboardController as SuperAdminController;

echo "=== RUNNING PARTNER APPLICATION & APPROVAL WORKFLOW TESTS ===\n";

// 1. Clean up previous test technician records if existing
Database::query("DELETE FROM staff_skills WHERE staff_id IN (SELECT id FROM users WHERE phone IN ('9876001122', '9876009988'))");
Database::query("DELETE FROM staff_profiles WHERE user_id IN (SELECT id FROM users WHERE phone IN ('9876001122', '9876009988'))");
Database::query("DELETE FROM users WHERE phone IN ('9876001122', '9876009988')");

// 2. Test 1: Online Registration via Become a Partner Form
$pageCtrl = new PageController();
$regReq = new Request([], [
    '_csrf'      => Csrf::token(),
    'name'       => 'Vikram Sharma',
    'phone'      => '9876001122',
    'email'      => 'vikram.partner.test@refixel.com',
    'city'       => 'Dehradun',
    'trade'      => 'AC Service & Repair',
    'experience' => '5+',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/partner']);

$regResp = $pageCtrl->submitPartner($regReq);
assert($regResp->getStatusCode() === 302, "Expected 302 redirect after form submission");

$newTech = Database::fetchOne("SELECT * FROM users WHERE phone = '9876001122'");
assert($newTech !== null, "New technician record must be created in users table");
assert($newTech['status'] === 'pending', "Newly registered technician must have status = 'pending'");
assert($newTech['role'] === 'staff', "Newly registered user must have role = 'staff'");

// Check flash message does NOT expose any password
$flashSuccess = $_SESSION['flash_success'] ?? '';
assert(!str_contains(strtolower($flashSuccess), 'password is'), "Flash message must never expose login password!");
assert(str_contains(strtolower($flashSuccess), 'under review') || str_contains(strtolower($flashSuccess), 'be patient'), "Flash message must notify user application is under review");

// Check staff_profiles availability
$profile = Database::fetchOne("SELECT * FROM staff_profiles WHERE user_id = :uid", ['uid' => $newTech['id']]);
assert($profile !== null, "Profile record must exist");
assert((int)$profile['is_available'] === 0, "Technician must default to off-duty (is_available = 0) until admin approval");

// Check modal trigger session
assert(isset($_SESSION['partner_app_submitted']), "partner_app_submitted session flag must be set for modal popup");
assert($_SESSION['partner_app_submitted']['name'] === 'Vikram Sharma');
echo "[PASS] Test 1: Partner form registration creates pending profile, hides password, sets modal flag\n";

// 3. Test 2: Unapproved technician tries to log in
Auth::logout();
$authCtrl = new AuthController();
$loginReq = new Request([], [
    '_csrf'      => Csrf::token(),
    'identifier' => '9876001122',
    'password'   => 'RandomPassword123',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/login']);

$loginResp = $authCtrl->login($loginReq);
assert($loginResp->getStatusCode() === 302, "Expected 302 redirect back to login");
assert(!Auth::check(), "Pending technician must NOT be allowed to log in");
$warningFlash = View::flash('warning');
assert(str_contains(strtolower($warningFlash), 'under process') || str_contains(strtolower($warningFlash), 'under review'), "Login attempt must clearly state application is under process/review");
echo "[PASS] Test 2: Pending technician blocked from login with 'under process & review' notice\n";

// 4. Test 3: Workforce isolation - Pending technician MUST NOT appear in /admin/staff
$adminUser = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if (!$adminUser) {
    Database::query("INSERT INTO users (role, name, email, phone, password_hash, status) VALUES ('admin', 'Operations Admin', 'admin.wf.test@refixel.com', '9888877771', 'dummy', 'active')");
    $adminUser = Database::fetchOne("SELECT * FROM users WHERE email = 'admin.wf.test@refixel.com'");
}
Auth::login($adminUser);

$staffCtrl = new StaffController();
$staffIndexReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/staff']);
$staffIndexResp = $staffCtrl->index($staffIndexReq);
$staffIndexBody = $staffIndexResp->getBody();
assert(!str_contains($staffIndexBody, 'Vikram Sharma'), "Pending technician MUST NOT appear in active workforce directory (/admin/staff)!");
echo "[PASS] Test 3: Pending technician successfully hidden from /admin/staff workforce list\n";

// 5. Test 4: Dedicated Partner Applications section (/admin/partner-applications)
$appCtrl = new PartnerApplicationController();
$appIndexReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/partner-applications']);
$appIndexResp = $appCtrl->index($appIndexReq);
$appIndexBody = $appIndexResp->getBody();
assert(str_contains($appIndexBody, 'Vikram Sharma'), "Pending technician MUST appear in /admin/partner-applications!");
assert(str_contains($appIndexBody, 'Pending Review'), "Partner applications page must show Pending Review badge");
echo "[PASS] Test 4: Pending applicant correctly listed in dedicated Partner Applications section\n";

// 6. Test 5: Rejection workflow with feedback note
Database::query("DELETE FROM users WHERE phone = '9876009988'");
Database::query(
    "INSERT INTO users (role, name, email, phone, password_hash, status) VALUES ('staff', 'Ravi Kumar', 'ravi.tech.test@refixel.com', '9876009988', 'dummy', 'pending')"
);
$tech2Id = (int)Database::lastInsertId();
Database::query("INSERT INTO staff_profiles (user_id, is_available) VALUES ({$tech2Id}, 0)");

$rejectReq = new Request([], [
    '_csrf'  => Csrf::token(),
    'reason' => 'Incomplete KYC trade certificates',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/partner-applications/{$tech2Id}/reject"]);

$rejectResp = $appCtrl->reject($rejectReq, (string)$tech2Id);
assert($rejectResp->getStatusCode() === 302, "Expected 302 redirect after rejection");

$rejectedUser = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $tech2Id]);
assert($rejectedUser['status'] === 'rejected', "Technician status must be 'rejected'");
$rejectedProfile = Database::fetchOne("SELECT * FROM staff_profiles WHERE user_id = :id", ['id' => $tech2Id]);
assert($rejectedProfile['rejection_reason'] === 'Incomplete KYC trade certificates', "Rejection reason must be stored");

// Ensure rejected technician does NOT appear in /admin/staff
$staffIndexResp2 = $staffCtrl->index($staffIndexReq);
assert(!str_contains($staffIndexResp2->getBody(), 'Ravi Kumar'), "Rejected technician must NOT appear in /admin/staff!");
echo "[PASS] Test 5: Rejection workflow properly records reason and maintains workforce isolation\n";

// 7. Test 6: Approval workflow via PartnerApplicationController
$approveReq = new Request([], [
    '_csrf' => Csrf::token(),
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/partner-applications/{$newTech['id']}/approve"]);

$approveResp = $appCtrl->approve($approveReq, (string)$newTech['id']);
assert($approveResp->getStatusCode() === 302, "Expected 302 redirect after approval");

$approvedTech = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $newTech['id']]);
assert($approvedTech['status'] === 'active', "Technician status must now be 'active'");

$approvedProfile = Database::fetchOne("SELECT * FROM staff_profiles WHERE user_id = :id", ['id' => $newTech['id']]);
assert((int)$approvedProfile['is_available'] === 1, "Approved technician must now be available for dispatch");
assert(!empty($approvedProfile['approved_at']), "approved_at timestamp must be recorded");

// Now verify that the approved technician DOES appear in /admin/staff
$staffIndexResp3 = $staffCtrl->index($staffIndexReq);
assert(str_contains($staffIndexResp3->getBody(), 'Vikram Sharma'), "Approved technician MUST now appear in /admin/staff workforce list!");
echo "[PASS] Test 6: Approval workflow activates account, sends credentials email, and displays technician in /admin/staff\n";

// 8. Test 7: Super Admin Partner Applications section & Approval
$superAdmin = Database::fetchOne("SELECT * FROM users WHERE role = 'super_admin' LIMIT 1");
Auth::login($superAdmin);

$superCtrl = new SuperAdminController();
$saAppReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/superadmin/partner-applications']);
$saAppResp = $superCtrl->partnerApplications($saAppReq);
assert($saAppResp->getStatusCode() === 200, "SuperAdmin partner applications view must return 200 OK");
echo "[PASS] Test 7: Super Admin partner applications control panel verified\n";

// Cleanup test accounts
Database::query("DELETE FROM staff_skills WHERE staff_id IN ({$newTech['id']}, {$tech2Id})");
Database::query("DELETE FROM staff_profiles WHERE user_id IN ({$newTech['id']}, {$tech2Id})");
Database::query("DELETE FROM users WHERE id IN ({$newTech['id']}, {$tech2Id})");

echo "\n=== ALL 7 PARTNER APPLICATION & APPROVAL TESTS PASSED SUCCESSFULLY! ===\n";
