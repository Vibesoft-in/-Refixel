<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Models\Booking;
use App\Models\Job;
use App\Models\Service;
use App\Models\User;
use App\Services\AutoDispatchService;
use App\Controllers\Admin\DashboardController;
use App\Controllers\BookingController;

echo "=== RUNNING ADMIN BUSY MODE & AUTO-ASSIGN FEATURE TESTS ===\n";

// Ensure clean slate
AutoDispatchService::setBusyMode(false);
assert(AutoDispatchService::isBusyModeEnabled() === false, "Expected Busy Mode to be disabled initially");

// Setup test technicians with known categories
$cleaningCat = Database::fetchOne("SELECT id FROM categories WHERE slug = 'cleaning' OR name LIKE '%Clean%'")['id'] ?? 1;
$electricianCat = Database::fetchOne("SELECT id FROM categories WHERE slug = 'electrician' OR name LIKE '%Electric%'")['id'] ?? 7;

// Find or create test staff user for category
$staffEmail = 'test.electrician.' . time() . '@refixel.com';
Database::query("INSERT INTO users (name, email, phone, password_hash, role, status, created_at)
                 VALUES ('Test Electrician Free', :email, '9876543210', 'hash', 'staff', 'active', NOW())",
                 ['email' => $staffEmail]);
$staffId = (int)Database::lastInsertId();

Database::query("INSERT INTO staff_profiles (user_id, is_available, availability_note, rating_avg, rating_count, created_at)
                 VALUES (:uid, 1, 'Free for assignment', 4.9, 10, NOW())
                 ON DUPLICATE KEY UPDATE is_available = 1", ['uid' => $staffId]);

Database::query("INSERT INTO staff_skills (staff_id, category_id, created_at)
                 VALUES (:sid, :cid, NOW())
                 ON DUPLICATE KEY UPDATE category_id = :cid2",
                 ['sid' => $staffId, 'cid' => $electricianCat, 'cid2' => $electricianCat]);

// Test 1: Category Matching & Availability
$matchedTech = AutoDispatchService::findAvailableTechnicianForCategory((int)$electricianCat);
assert($matchedTech !== null, "Test 1 Failed: Expected a technician for electrician category");
assert((int)$matchedTech['is_available'] === 1, "Test 1 Failed: Expected technician to be available");
echo "Test 1: Available Technician for Category successfully resolved (Technician #{$matchedTech['id']} - {$matchedTech['name']}) - PASSED\n";

// Test 2: Auto-assigning pending enquiries when Admin Busy Mode is toggled ON
$electricianService = Database::fetchOne("SELECT id FROM services WHERE category_id = :cid AND is_active = 1", ['cid' => $electricianCat]);
if (!$electricianService) {
    $electricianService = ['id' => 1];
}

$pendingBookingNo = 'BK-TEST-BUSY-' . rand(1000, 9999);
$pendingBookingId = Booking::create([
    'booking_no'     => $pendingBookingNo,
    'customer_id'    => null,
    'service_id'     => (int)$electricianService['id'],
    'name'           => 'Test Customer',
    'phone'          => '9876543210',
    'email'          => 'test.cust@example.com',
    'address'        => 'Test Location, Kashipur',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '10:00 AM - 12:00 PM',
    'status'         => 'new',
    'priority'       => 'high',
]);

// Turn ON busy mode and auto-assign
AutoDispatchService::setBusyMode(true);
assert(AutoDispatchService::isBusyModeEnabled() === true, "Expected Busy Mode to be enabled");

$autoAssignRes = AutoDispatchService::autoAssignPendingEnquiries(1);
assert($autoAssignRes['assigned_count'] >= 1, "Test 2 Failed: Expected at least 1 pending enquiry to be assigned");

$updatedBooking = Booking::find($pendingBookingId);
assert($updatedBooking['status'] === 'assigned', "Test 2 Failed: Expected booking status to be 'assigned', got '{$updatedBooking['status']}'");

$job = Database::fetchOne("SELECT * FROM jobs WHERE booking_id = :bid", ['bid' => $pendingBookingId]);
assert($job !== null, "Test 2 Failed: Expected job to be created in jobs table");
assert((int)$job['staff_id'] > 0, "Test 2 Failed: Expected job staff_id to be set");
assert($job['status'] === 'assigned', "Test 2 Failed: Expected job status to be 'assigned'");

echo "Test 2: Auto-assignment of pending enquiries on Admin Busy toggle - PASSED\n";

// Test 3: Newly arriving booking auto-assigned in real-time while Busy Mode is ON
$newBookingNo = 'BK-TEST-LIVE-' . rand(1000, 9999);
$newBookingId = Booking::create([
    'booking_no'     => $newBookingNo,
    'customer_id'    => null,
    'service_id'     => (int)$electricianService['id'],
    'name'           => 'Live Customer',
    'phone'          => '9876543210',
    'email'          => 'live.cust@example.com',
    'address'        => 'Live Location, Kashipur',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '02:00 PM - 04:00 PM',
    'status'         => 'new',
    'priority'       => 'high',
]);

// Trigger handleNewBooking (simulating BookingController creation)
$liveAssigned = AutoDispatchService::handleNewBooking($newBookingId);
assert($liveAssigned === true, "Test 3 Failed: Expected new booking to be auto-assigned while Busy Mode is ON");

$liveBooking = Booking::find($newBookingId);
assert($liveBooking['status'] === 'assigned', "Test 3 Failed: Expected live booking status to be 'assigned'");

echo "Test 3: Live incoming enquiry auto-assignment while Busy Mode is active - PASSED\n";

// Test 4: DashboardController toggleBusyMode API endpoint
$adminUser = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if ($adminUser) {
    Auth::login($adminUser);
}

$dashboardController = new DashboardController();

// Toggle OFF
$reqOff = new Request([], ['enabled' => '0']);
$resOff = $dashboardController->toggleBusyMode($reqOff);
$dataOff = json_decode($resOff->getContent(), true);
assert($dataOff['success'] === true, "Test 4 Failed: Expected success on toggle OFF");
assert($dataOff['busy_mode'] === false, "Test 4 Failed: Expected busy_mode to be false");
assert(AutoDispatchService::isBusyModeEnabled() === false, "Test 4 Failed: Expected setting to be false in DB");

// Toggle ON
$reqOn = new Request([], ['enabled' => '1']);
$resOn = $dashboardController->toggleBusyMode($reqOn);
$dataOn = json_decode($resOn->getContent(), true);
assert($dataOn['success'] === true, "Test 4 Failed: Expected success on toggle ON");
assert($dataOn['busy_mode'] === true, "Test 4 Failed: Expected busy_mode to be true");
assert(AutoDispatchService::isBusyModeEnabled() === true, "Test 4 Failed: Expected setting to be true in DB");

echo "Test 4: DashboardController toggleBusyMode AJAX API - PASSED\n";

// Test 5: Verify Admin Dashboard renders toggle button and script
$dashView = file_get_contents(__DIR__ . '/../../app/views/admin/dashboard.php');
assert(strpos($dashView, 'id="adminBusyToggle"') !== false, "Test 5 Failed: Expected adminBusyToggle in dashboard.php");
assert(strpos($dashView, 'id="dispatchModeBadge"') !== false, "Test 5 Failed: Expected dispatchModeBadge in dashboard.php");
assert(strpos($dashView, 'admin/api/toggle-busy-mode') !== false, "Test 5 Failed: Expected toggle API url in dashboard.php");

echo "Test 5: Dashboard View UI elements & interactive toggle verification - PASSED\n";

// Clean up
AutoDispatchService::setBusyMode(false);
Database::query("DELETE FROM status_history WHERE job_id IN (SELECT id FROM jobs WHERE booking_id IN (:b1, :b2))", ['b1' => $pendingBookingId, 'b2' => $newBookingId]);
Database::query("DELETE FROM jobs WHERE booking_id IN (:b1, :b2)", ['b1' => $pendingBookingId, 'b2' => $newBookingId]);
Database::query("DELETE FROM bookings WHERE id IN (:b1, :b2)", ['b1' => $pendingBookingId, 'b2' => $newBookingId]);
Database::query("DELETE FROM staff_skills WHERE staff_id = :sid", ['sid' => $staffId]);
Database::query("DELETE FROM staff_profiles WHERE user_id = :sid", ['sid' => $staffId]);
Database::query("DELETE FROM users WHERE id = :sid", ['sid' => $staffId]);

echo "\n=== ALL 5 ADMIN BUSY MODE & AUTO-ASSIGN TESTS PASSED SUCCESSFULLY! ===\n";
