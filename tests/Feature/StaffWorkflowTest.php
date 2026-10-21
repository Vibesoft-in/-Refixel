<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Workflow;
use App\Controllers\Staff\DashboardController;
use App\Controllers\Staff\JobController;
use App\Controllers\Staff\EarningsController;
use App\Controllers\Staff\ProfileController;
use App\Middleware\RequireRole;
use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\StaffEarning;
use App\Models\StaffProfile;
use App\Models\StatusHistory;

echo "=== RUNNING TECHNICIAN / STAFF WORKFLOW & PORTAL TESTS ===\n";

$dashCtrl = new DashboardController();
$jobCtrl = new JobController();
$earnCtrl = new EarningsController();
$profCtrl = new ProfileController();
$requireRole = new RequireRole();

// Setup: Ensure Tech 1 (tech@refixel.com) exists and is active
$tech = Database::fetchOne("SELECT * FROM users WHERE role = 'staff' AND email = 'tech@refixel.com'");
if (!$tech) {
    Database::execute(
        "INSERT INTO users (role, name, email, phone, password_hash, status, must_change_password)
         VALUES ('staff', 'Technician One', 'tech@refixel.com', '9812345678', :hash, 'active', 0)",
        ['hash' => password_hash('TechPassword#2026', PASSWORD_BCRYPT)]
    );
    $tech = Database::fetchOne("SELECT * FROM users WHERE role = 'staff' AND email = 'tech@refixel.com'");
}
assert($tech !== null, "Staff user tech@refixel.com must exist");
$techId = (int)$tech['id'];

$staffProf = Database::fetchOne("SELECT id FROM staff_profiles WHERE user_id = :uid", ['uid' => $techId]);
if (!$staffProf) {
    Database::execute("INSERT INTO staff_profiles (user_id, rating_avg, is_available) VALUES (:uid, 4.9, 1)", ['uid' => $techId]);
}

// Test 1: Role protection & unauthenticated blocking
Auth::logout();
$reqGuest = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/staff']);
$blockGuest = $requireRole->handle($reqGuest, 'staff');
assert($blockGuest !== null && $blockGuest->getStatusCode() === 302, "Guest must be redirected to /login");
echo "Test 1: Unauthenticated access blocked - PASSED\n";

// Test 2: Customer blocked from /staff
Auth::login(['id' => 3, 'name' => 'Customer', 'email' => 'customer@refixel.com', 'role' => 'customer', 'must_change_password' => 0]);
$reqCustomer = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/staff']);
$blockCustomer = $requireRole->handle($reqCustomer, 'staff');
assert($blockCustomer !== null && $blockCustomer->getStatusCode() === 302, "Customer must be blocked from /staff");
echo "Test 2: Customer role blocked from /staff - PASSED\n";

// Test 3: Forced password change enforcement for first-time login
Auth::login(['id' => $techId, 'name' => 'Technician', 'email' => 'tech@refixel.com', 'role' => 'staff', 'must_change_password' => 1]);
$reqForcePwd = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/staff']);
$blockForce = $requireRole->handle($reqForcePwd, 'staff');
assert($blockForce !== null && $blockForce->getStatusCode() === 302, "must_change_password must redirect to /change-password");
echo "Test 3: First-login forced password change enforcement - PASSED\n";

// Test 4: Technician Dashboard rendering
Auth::login(['id' => $techId, 'name' => 'Technician', 'email' => 'tech@refixel.com', 'role' => 'staff', 'must_change_password' => 0]);
$dashReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/staff']);
$dashResp = $dashCtrl->index($dashReq);
assert($dashResp->getStatusCode() === 200, "Expected 200 for /staff");
$dashContent = $dashResp->getContent();
assert(str_contains($dashContent, 'Technician Portal'), "Dashboard must show technician header");
assert(str_contains($dashContent, 'TODAY\'S JOBS'), "Dashboard must show today's jobs stat");
assert(str_contains($dashContent, 'THIS MONTH'), "Dashboard must show this month's earnings stat");
echo "Test 4: Technician Dashboard rendering - PASSED\n";

// Setup Test Jobs for Workflow
// Ensure we have a dedicated clean test job for technician 2
$existingTestJob = Database::fetchOne("SELECT id FROM jobs WHERE staff_id = :sid ORDER BY id ASC LIMIT 1", ['sid' => $techId]);
if (!$existingTestJob) {
    Database::query("DELETE FROM jobs WHERE booking_id IN (SELECT id FROM bookings WHERE booking_no = 'BK-TEST-WF-01')");
    Database::query("DELETE FROM bookings WHERE booking_no = 'BK-TEST-WF-01'");
    Database::query("INSERT INTO bookings (booking_no, service_id, name, phone, address, status, total_amount, due_amount, advance_amount, created_at) VALUES ('BK-TEST-WF-01', 1, 'Workflow Cust', '9876543210', '123 Test Rd', 'assigned', 899.00, 899.00, 0.00, NOW())");
    $bId = (int)Database::lastInsertId();
    Database::query("INSERT INTO jobs (booking_id, staff_id, status, created_at) VALUES (:bid, :sid, 'assigned', NOW())", ['bid' => $bId, 'sid' => $techId]);
    $existingTestJob = Database::fetchOne("SELECT id FROM jobs WHERE staff_id = :sid ORDER BY id ASC LIMIT 1", ['sid' => $techId]);
}
$testJobId = (int)$existingTestJob['id'];

// Reset test job status to 'assigned' for deterministic testing
Database::query("UPDATE jobs SET status = 'assigned', accepted_at = NULL, started_at = NULL, completed_at = NULL, work_summary = NULL, final_notes = NULL WHERE id = :id", ['id' => $testJobId]);
Database::query("UPDATE bookings SET status = 'assigned', total_amount = 899.00, due_amount = 899.00, advance_amount = 0.00 WHERE id = (SELECT booking_id FROM jobs WHERE id = :id)", ['id' => $testJobId]);

// Test 5: Strict query isolation (WHERE staff_id = current_user_id)
// Create a separate staff user (id: 9999) and assign a mock job
Database::query("DELETE FROM users WHERE email = 'othertech@refixel.com'");
Database::query(
    "INSERT INTO users (role, name, email, phone, password_hash, status, created_at) 
     VALUES ('staff', 'Other Tech', 'othertech@refixel.com', '9999988888', 'dummy', 'active', NOW())"
);
$otherTechId = (int)Database::lastInsertId();

// Create booking and job for other tech
Database::query("DELETE FROM bookings WHERE booking_no = 'BK-OTHER-999'");
Database::query(
    "INSERT INTO bookings (booking_no, service_id, name, phone, address, status, created_at)
     VALUES ('BK-OTHER-999', 1, 'Other Customer', '9111122222', '123 Other Street', 'assigned', NOW())"
);
$otherBookingId = (int)Database::lastInsertId();

Database::query(
    "INSERT INTO jobs (booking_id, staff_id, status, created_at)
     VALUES (:bid, :sid, 'assigned', NOW())",
    ['bid' => $otherBookingId, 'sid' => $otherTechId]
);
$otherJobId = (int)Database::lastInsertId();

// When logged in as $techId, viewing $otherJobId MUST fail (redirect or 404)
$isolateReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/staff/jobs/{$otherJobId}"]);
$isolateResp = $jobCtrl->show($isolateReq, (string)$otherJobId);
assert($isolateResp->getStatusCode() === 302, "Expected 302 redirect for job not belonging to technician");
echo "Test 5: Strict technician job query isolation (WHERE staff_id = current_user_id) - PASSED\n";

// Clean up other tech test records
Database::query("DELETE FROM jobs WHERE id = :id", ['id' => $otherJobId]);
Database::query("DELETE FROM bookings WHERE id = :id", ['id' => $otherBookingId]);
Database::query("DELETE FROM users WHERE id = :id", ['id' => $otherTechId]);

// Test 6: Workflow transition: assigned -> accepted
$acceptReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/staff/jobs/{$testJobId}/accept"]);
$acceptResp = $jobCtrl->accept($acceptReq, (string)$testJobId);
assert($acceptResp->getStatusCode() === 302, "Expected 302 after accept");

$jobAccepted = Database::fetchOne("SELECT * FROM jobs WHERE id = :id", ['id' => $testJobId]);
assert($jobAccepted['status'] === 'accepted', "Job status must be 'accepted'");
assert(!empty($jobAccepted['accepted_at']), "accepted_at timestamp must be populated");
$bookingAccepted = Database::fetchOne("SELECT status FROM bookings WHERE id = :bid", ['bid' => $jobAccepted['booking_id']]);
assert($bookingAccepted['status'] === 'accepted', "Booking status must be synced to 'accepted'");
echo "Test 6: Status workflow: assigned -> accepted with timestamp sync - PASSED\n";

// Test 7: In-progress sub-action: on-the-way
$otwReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/staff/jobs/{$testJobId}/on-the-way"]);
$otwResp = $jobCtrl->onTheWay($otwReq, (string)$testJobId);
assert($otwResp->getStatusCode() === 302, "Expected 302 after on-the-way");

$historyOtw = Database::fetchOne("SELECT * FROM status_history WHERE job_id = :jid AND to_status = 'on_the_way' ORDER BY id DESC LIMIT 1", ['jid' => $testJobId]);
assert($historyOtw !== null, "status_history must record on_the_way sub-action");
assert(str_contains($historyOtw['notes'], 'en route'), "status_history note must describe travel");
echo "Test 7: In-progress sub-action: on-the-way audit entry - PASSED\n";

// Test 8: Status workflow: accepted -> in_progress
$startReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/staff/jobs/{$testJobId}/start"]);
$startResp = $jobCtrl->start($startReq, (string)$testJobId);
assert($startResp->getStatusCode() === 302, "Expected 302 after start");

$jobStarted = Database::fetchOne("SELECT * FROM jobs WHERE id = :id", ['id' => $testJobId]);
assert($jobStarted['status'] === 'in_progress', "Job status must be 'in_progress'");
assert(!empty($jobStarted['started_at']), "started_at timestamp must be populated");
$bookingStarted = Database::fetchOne("SELECT status FROM bookings WHERE id = :bid", ['bid' => $jobStarted['booking_id']]);
assert($bookingStarted['status'] === 'in_progress', "Booking status must be synced to 'in_progress'");
echo "Test 8: Status workflow: accepted -> in_progress with started_at timestamp - PASSED\n";

// Test 9: Photo capture & upload metadata storage (Before & After)
$beforePhotoId = JobPhoto::addPhoto($testJobId, 'before', 'uploads/jobs/test_before_cleaning.jpg');
assert($beforePhotoId > 0, "Before photo record must be created");
$afterPhotoId = JobPhoto::addPhoto($testJobId, 'after', 'uploads/jobs/test_after_cleaning.jpg');
assert($afterPhotoId > 0, "After photo record must be created");

$jobPhotos = JobPhoto::getByJob($testJobId);
assert(count($jobPhotos) >= 2, "Job photos must contain at least 2 photos");
echo "Test 9: Before & after photo capture & metadata storage - PASSED\n";

// Test 10: Status workflow: in_progress -> completed with work summary & auto-commission
// Clear any prior earnings for this test job
Database::query("DELETE FROM staff_earnings WHERE job_id = :jid", ['jid' => $testJobId]);

$completeReq = new Request([], [
    '_csrf'                 => Csrf::token(),
    'work_summary'          => 'Comprehensive 3-BHK deep cleaning executed with hospital-grade disinfectant.',
    'final_notes'           => 'Customer advised to keep doors ventilated for 1 hour.',
    'settle_payment_method' => 'cash',
    'settle_payment_amount' => 899,
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/staff/jobs/{$testJobId}/complete"]);

$completeResp = $jobCtrl->processComplete($completeReq, (string)$testJobId);
assert($completeResp->getStatusCode() === 302, "Expected 302 redirect after completion");

$jobCompleted = Database::fetchOne("SELECT * FROM jobs WHERE id = :id", ['id' => $testJobId]);
assert($jobCompleted['status'] === 'completed', "Job status must be 'completed'");
assert(!empty($jobCompleted['completed_at']), "completed_at timestamp must be populated");
assert(str_contains($jobCompleted['work_summary'], 'Comprehensive 3-BHK'), "Work summary must be saved in jobs table");
assert(str_contains($jobCompleted['final_notes'], 'ventilated'), "Final notes must be saved in jobs table");

$bookingCompleted = Database::fetchOne("SELECT status FROM bookings WHERE id = :bid", ['bid' => $jobCompleted['booking_id']]);
assert($bookingCompleted['status'] === 'completed', "Booking status must be synced to 'completed'");

// Verify automatic commission calculation and recording in staff_earnings
$earning = Database::fetchOne("SELECT * FROM staff_earnings WHERE job_id = :jid AND staff_id = :sid", ['jid' => $testJobId, 'sid' => $techId]);
assert($earning !== null, "Staff earning commission must be recorded");
assert((float)$earning['amount'] > 0, "Earning amount must be greater than 0");
assert($earning['month'] === date('Y-m'), "Earning month must match current Y-m");
assert($earning['is_settled'] == 0, "New earning must default to unsettled (is_settled = 0)");
echo "Test 10: Status workflow: in_progress -> completed with notes & auto-commission - PASSED\n";

// Test 11: Illegal status transition rejection
// Cannot transition from completed back to assigned
$illegalAttempt = false;
try {
    Workflow::transition($testJobId, 'assigned', $techId, 'staff', 'Illegal rollback attempt');
} catch (\RuntimeException $e) {
    $illegalAttempt = true;
}
assert($illegalAttempt === true, "Workflow must reject illegal rollback transition from completed to assigned");
echo "Test 11: Illegal status transition rejection - PASSED\n";

// Test 12: Availability toggle and profile management
$profUpdateReq = new Request([], [
    '_csrf'             => Csrf::token(),
    'is_available'      => '1',
    'availability_note' => 'Available for emergency evening calls',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/staff/profile']);

$profUpdateResp = $profCtrl->update($profUpdateReq);
assert($profUpdateResp->getStatusCode() === 302, "Expected 302 after profile update");

$updatedProfile = StaffProfile::find($techId);
assert((int)$updatedProfile['is_available'] === 1, "is_available must be 1");
assert($updatedProfile['availability_note'] === 'Available for emergency evening calls', "availability_note must be updated");
echo "Test 12: Availability status and duty note updates - PASSED\n";

// Test 13: Staff Earnings view rendering
$earnReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/staff/earnings']);
$earnResp = $earnCtrl->index($earnReq);
assert($earnResp->getStatusCode() === 200, "Expected 200 for /staff/earnings");
$earnContent = $earnResp->getContent();
assert(str_contains($earnContent, 'Earnings & Payouts'), "Earnings view must display title");
assert(str_contains($earnContent, 'Total Earnings'), "Earnings view must display total earnings");
assert(str_contains($earnContent, 'Payout Records'), "Earnings view must display payout records");
echo "Test 13: Staff Earnings view and financial breakdown - PASSED\n";

echo "\n=== ALL 13 TECHNICIAN / STAFF WORKFLOW TESTS PASSED! ===\n";
