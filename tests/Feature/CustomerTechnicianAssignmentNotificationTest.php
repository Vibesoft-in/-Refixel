<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Workflow;
use App\Models\Booking;
use App\Models\Job;
use App\Controllers\Admin\BookingController;

echo "=== RUNNING CUSTOMER TECHNICIAN ASSIGNMENT NOTIFICATION TESTS ===\n";

// 1. Setup test customer and technician
Database::query("DELETE FROM notifications WHERE to_address LIKE '%assign_test%'");
Database::query("DELETE FROM status_history WHERE notes LIKE '%Test Assignment%'");
Database::query("DELETE FROM jobs WHERE booking_id IN (SELECT id FROM bookings WHERE booking_no LIKE 'TEST-ASSIGN-%')");
Database::query("DELETE FROM bookings WHERE booking_no LIKE 'TEST-ASSIGN-%'");
Database::query("DELETE FROM users WHERE email IN ('cust.assign_test@refixel.com', 'tech.assign_test@refixel.com')");

// Create test customer
Database::query("INSERT INTO users (role, name, email, phone, password_hash, status) 
                 VALUES ('customer', 'Ramesh Kumar', 'cust.assign_test@refixel.com', '9898000001', 'hash', 'active')");
$customerId = (int)Database::lastInsertId();

// Create test technician
Database::query("INSERT INTO users (role, name, email, phone, password_hash, status) 
                 VALUES ('staff', 'Mohan Lal (Certified AC Tech)', 'tech.assign_test@refixel.com', '9898000002', 'hash', 'active')");
$techId = (int)Database::lastInsertId();

Database::query("INSERT INTO staff_profiles (user_id, rating_avg, is_available) VALUES (:uid, 4.9, 1)", ['uid' => $techId]);

// Create test booking
Database::query("INSERT INTO bookings (booking_no, customer_id, service_id, name, email, phone, address, preferred_date, preferred_time, status, total_amount, created_at)
                 VALUES ('TEST-ASSIGN-001', :cid, 1, 'Ramesh Kumar', 'cust.assign_test@refixel.com', '9898000001', 'House 42, Civil Lines, Kashipur', '2026-10-20', '10:00 AM - 12:00 PM', 'new', 1499.00, NOW())",
                 ['cid' => $customerId]);
$bookingId = (int)Database::lastInsertId();

echo "[PASS] Test Setup: Created test customer (#{$customerId}), technician (#{$techId}), and booking (#{$bookingId})\n";

// 2. Test BookingController::assignStaff dispatches notification to customer & technician
$adminUser = Database::fetchOne("SELECT * FROM users WHERE role IN ('admin', 'super_admin') LIMIT 1");
Auth::login($adminUser);

$controller = new BookingController();
$assignReq = new Request([], [
    '_csrf'        => Csrf::token(),
    'staff_id'     => $techId,
    'priority'     => 'urgent',
    'scheduled_at' => '2026-10-20T11:00',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/bookings/{$bookingId}/assign"]);

$resp = $controller->assignStaff($assignReq, (string)$bookingId);
assert($resp->getStatusCode() === 302, "Expected 302 redirect after assignStaff");

// 3. Verify Database state
$updatedBooking = Database::fetchOne("SELECT * FROM bookings WHERE id = :id", ['id' => $bookingId]);
assert($updatedBooking['status'] === 'assigned', "Booking status must be 'assigned'");
assert($updatedBooking['priority'] === 'urgent', "Booking priority must be 'urgent'");

$job = Database::fetchOne("SELECT * FROM jobs WHERE booking_id = :id", ['id' => $bookingId]);
assert($job !== null, "Job record must be created for assigned booking");
assert((int)$job['staff_id'] === $techId, "Job staff_id must match assigned technician");
assert($job['status'] === 'assigned', "Job status must be 'assigned'");
echo "[PASS] Test 1: Booking & Job updated to 'assigned' with correct technician and priority\n";

// 4. Verify Customer Notification in Database
$customerNotif = Database::fetchOne("SELECT * FROM notifications 
                                     WHERE to_address = 'cust.assign_test@refixel.com' 
                                     AND template = 'technician_assigned_customer' 
                                     ORDER BY id DESC LIMIT 1");

assert($customerNotif !== null, "Customer notification record MUST exist in notifications table!");
assert($customerNotif['channel'] === 'email', "Customer notification must be sent via email channel");
$payload = json_decode($customerNotif['payload'], true);
assert($payload['customer_name'] === 'Ramesh Kumar', "Payload must include customer name");
assert($payload['staff_name'] === 'Mohan Lal (Certified AC Tech)', "Payload must include assigned technician name");
assert($payload['staff_phone'] === '9898000002', "Payload must include technician phone");
assert($payload['booking_no'] === 'TEST-ASSIGN-001', "Payload must include booking number");
assert($payload['service_name'] === 'Professional Full Home Cleaning', "Payload must include correct service name");
echo "[PASS] Test 2: Customer received technician assignment notification with complete technician details\n";

// 5. Verify Staff Notification in Database
$staffNotif = Database::fetchOne("SELECT * FROM notifications 
                                  WHERE to_address = 'tech.assign_test@refixel.com' 
                                  AND template = 'staff_assigned' 
                                  ORDER BY id DESC LIMIT 1");
assert($staffNotif !== null, "Staff notification record must also exist in notifications table!");
echo "[PASS] Test 3: Technician simultaneously received new job assignment alert\n";

// 6. Test Workflow::transitionBooking to assigned also notifies customer
// Create second booking
Database::query("INSERT INTO bookings (booking_no, customer_id, service_id, name, email, phone, address, preferred_date, preferred_time, status, total_amount, created_at)
                 VALUES ('TEST-ASSIGN-002', :cid, 1, 'Ramesh Kumar', 'cust.assign_test@refixel.com', '9898000001', 'House 42, Civil Lines', '2026-10-21', '02:00 PM', 'new', 999.00, NOW())",
                 ['cid' => $customerId]);
$b2Id = (int)Database::lastInsertId();

Database::query("INSERT INTO jobs (booking_id, staff_id, status, scheduled_at, created_at, updated_at) 
                 VALUES (:bid, :sid, 'assigned', NOW(), NOW(), NOW())", ['bid' => $b2Id, 'sid' => $techId]);

Workflow::transitionBooking($b2Id, Workflow::STATUS_ASSIGNED, (int)$adminUser['id'], 'admin', 'Assigning technician via workflow');

$wfNotif = Database::fetchOne("SELECT * FROM notifications 
                               WHERE to_address = 'cust.assign_test@refixel.com' 
                               AND template = 'technician_assigned_customer' 
                               ORDER BY id DESC LIMIT 1");
assert($wfNotif !== null, "Workflow state machine must also notify customer on STATUS_ASSIGNED");
echo "[PASS] Test 4: Workflow transition to STATUS_ASSIGNED successfully dispatches customer notification\n";

// Cleanup
Database::query("DELETE FROM notifications WHERE to_address LIKE '%assign_test%'");
Database::query("DELETE FROM status_history WHERE notes LIKE '%Test Assignment%' OR notes LIKE '%Assigning technician via workflow%'");
Database::query("DELETE FROM jobs WHERE booking_id IN ({$bookingId}, {$b2Id})");
Database::query("DELETE FROM bookings WHERE id IN ({$bookingId}, {$b2Id})");
Database::query("DELETE FROM staff_profiles WHERE user_id = {$techId}");
Database::query("DELETE FROM users WHERE id IN ({$customerId}, {$techId})");

echo "\n=== ALL 4 CUSTOMER ASSIGNMENT NOTIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
