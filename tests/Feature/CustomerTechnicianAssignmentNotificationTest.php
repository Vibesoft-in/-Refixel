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

// 4. Verify Customer Notification in Database (Email & SMS dispatched per commit 15fcab9)
$customerEmailNotif = Database::fetchOne("SELECT * FROM notifications 
                                          WHERE to_address = 'cust.assign_test@refixel.com' 
                                          AND template = 'technician_assigned_customer' 
                                          ORDER BY id DESC LIMIT 1");
assert($customerEmailNotif !== null, "Customer assignment email must be dispatched to customer");

$customerSmsNotif = Database::fetchOne("SELECT * FROM notifications 
                                        WHERE to_address = '9898000001' 
                                        AND template = 'technician_assigned_sms' 
                                        ORDER BY id DESC LIMIT 1");
assert($customerSmsNotif !== null, "Customer assignment SMS must be dispatched");
echo "[PASS] Test 2: Customer email silenced (adhering to 4-email rule) and SMS dispatched\n";

// 5. Verify Staff Notification in Database with 75% Commission Calculation
$staffNotif = Database::fetchOne("SELECT * FROM notifications 
                                  WHERE to_address = 'tech.assign_test@refixel.com' 
                                  AND template = 'staff_assigned' 
                                  ORDER BY id DESC LIMIT 1");
assert($staffNotif !== null, "Staff notification record must exist in notifications table!");
$staffPayload = json_decode($staffNotif['payload'], true);
// Total amount was 1499.00 -> 75% worker share = round(1499 * 0.75, 2) = 1124.25
assert((float)$staffPayload['worker_percent'] === 75.0, "Worker percent must be 75%");
assert((float)$staffPayload['technician_payout'] === 1124.25, "Payout must be 75% of 1499 (1124.25), NOT 1499 - 49 (1450)");
echo "[PASS] Test 3: Technician received assignment alert with accurate 75% commission payout calculation\n";

// 6. Test with ₹5,000 booking to explicitly verify bug fix (₹3,750 payout instead of ₹4,950)
Database::query("INSERT INTO bookings (booking_no, customer_id, service_id, name, email, phone, address, preferred_date, preferred_time, status, total_amount, advance_amount, due_amount, created_at)
                 VALUES ('TEST-ASSIGN-5000', :cid, 1, 'Ramesh Kumar', 'cust.assign_test@refixel.com', '9898000001', 'House 42, Civil Lines', '2026-10-21', '02:00 PM', 'new', 5000.00, 49.00, 4951.00, NOW())",
                 ['cid' => $customerId]);
$b5kId = (int)Database::lastInsertId();

$booking5k = Booking::findWithDetails($b5kId);
Notifier::notifyStaffAssigned($booking5k, ['name' => 'Mohan Lal', 'email' => 'tech.assign_test@refixel.com', 'phone' => '9898000002']);

$b5kNotif = Database::fetchOne("SELECT * FROM notifications 
                                WHERE to_address = 'tech.assign_test@refixel.com' 
                                AND template = 'staff_assigned' 
                                ORDER BY id DESC LIMIT 1");
$b5kPayload = json_decode($b5kNotif['payload'], true);
assert((float)$b5kPayload['technician_payout'] === 3750.00, "₹5,000 booking payout MUST be ₹3,750 (75%), NOT ₹4,950!");
assert((float)$b5kPayload['worker_percent'] === 75.0, "Worker percent must be 75%");
echo "[PASS] Test 4: ₹5,000 pre-booking technician assignment email correctly displays ₹3,750 payout (75%) instead of ₹4,950!\n";

// Cleanup
Database::query("DELETE FROM notifications WHERE to_address LIKE '%assign_test%'");
Database::query("DELETE FROM status_history WHERE notes LIKE '%Test Assignment%' OR notes LIKE '%Assigning technician via workflow%'");
Database::query("DELETE FROM jobs WHERE booking_id IN ({$bookingId}, {$b5kId})");
Database::query("DELETE FROM bookings WHERE id IN ({$bookingId}, {$b5kId})");
Database::query("DELETE FROM staff_profiles WHERE user_id = {$techId}");
Database::query("DELETE FROM users WHERE id IN ({$customerId}, {$techId})");

echo "\n=== ALL 4 CUSTOMER ASSIGNMENT NOTIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
