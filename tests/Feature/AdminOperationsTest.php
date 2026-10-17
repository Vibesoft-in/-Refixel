<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Middleware\RequireRole;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\EnquiryController;
use App\Controllers\Admin\BookingController;
use App\Controllers\Admin\StaffController;
use App\Controllers\Admin\ServiceController;
use App\Controllers\Admin\PaymentController;
use App\Controllers\Admin\ReportController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\SettingsController;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;

echo "=== RUNNING ADMIN OPERATIONS & DASHBOARD FEATURE TESTS ===\n";

$dashCtrl = new DashboardController();
$enqCtrl = new EnquiryController();
$bookCtrl = new BookingController();
$staffCtrl = new StaffController();
$svcCtrl = new ServiceController();
$payCtrl = new PaymentController();
$repCtrl = new ReportController();
$cmsCtrl = new ContentController();
$setCtrl = new SettingsController();
$requireRole = new RequireRole();

// Setup Admin user
$admin = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
assert($admin !== null, "Admin user must exist");
$adminId = (int)$admin['id'];

// Test 1: Role protection - Guest, Customer, Staff blocked from /admin
Auth::logout();
$reqGuest = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin']);
assert($requireRole->handle($reqGuest, 'admin') !== null, "Guest must be blocked from /admin");

Auth::login(['id' => 3, 'name' => 'Customer', 'role' => 'customer', 'must_change_password' => 0]);
assert($requireRole->handle($reqGuest, 'admin') !== null, "Customer must be blocked from /admin");

Auth::login(['id' => 2, 'name' => 'Technician', 'role' => 'staff', 'must_change_password' => 0]);
assert($requireRole->handle($reqGuest, 'admin') !== null, "Technician must be blocked from /admin");
echo "Test 1: Role protection and access barriers - PASSED\n";

// Authenticate as Admin for all subsequent tests
Auth::login(['id' => $adminId, 'name' => 'Super Admin', 'email' => 'admin@refixel.com', 'role' => 'admin', 'must_change_password' => 0]);

// Test 2: Admin Dashboard rendering & KPI metrics
$dashReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin']);
$dashResp = $dashCtrl->index($dashReq);
assert($dashResp->getStatusCode() === 200, "Expected 200 for /admin");
$dashHtml = $dashResp->getContent();
assert(str_contains($dashHtml, 'Operations & Dispatch Dashboard'), "Dashboard title missing");
assert(str_contains($dashHtml, 'New Enquiries'), "New enquiries KPI missing");
assert(str_contains($dashHtml, 'Assigned Jobs'), "Assigned jobs KPI missing");
assert(str_contains($dashHtml, 'Revenue'), "Revenue collected KPI missing");
assert(str_contains($dashHtml, 'Technicians'), "Technicians KPI missing");
echo "Test 2: Dashboard KPI matrix and view rendering - PASSED\n";

// Test 3: Enquiries list, details, and priority/notes update
$enqListReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/enquiries']);
$enqListResp = $enqCtrl->index($enqListReq);
assert($enqListResp->getStatusCode() === 200, "Expected 200 for /admin/enquiries");

// Ensure an enquiry exists
$testBooking = Database::fetchOne("SELECT * FROM bookings ORDER BY id DESC LIMIT 1");
assert($testBooking !== null, "A booking record must exist");
$bid = (int)$testBooking['id'];

$enqShowReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/admin/enquiries/{$bid}"]);
$enqShowResp = $enqCtrl->show($enqShowReq, (string)$bid);
assert($enqShowResp->getStatusCode() === 200, "Expected 200 for /admin/enquiries/{id}");

// Update notes & priority
$updateEnqReq = new Request([], [
    '_csrf'       => Csrf::token(),
    'priority'    => 'urgent',
    'admin_notes' => 'Customer requested expedited deep cleaning service.',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/enquiries/{$bid}/update"]);
$updateEnqResp = $enqCtrl->update($updateEnqReq, (string)$bid);
assert($updateEnqResp->getStatusCode() === 302, "Expected 302 redirect after updating enquiry");

$updatedB = Booking::find($bid);
assert($updatedB['priority'] === 'urgent', "Priority must be updated to 'urgent'");
assert(str_contains($updatedB['admin_notes'], 'expedited deep cleaning'), "Admin notes must be saved");
echo "Test 3: Enquiry view and priority/notes operations - PASSED\n";

// Test 4: Booking Assignment & Reassignment
$testStaff = Database::fetchOne("SELECT id FROM users WHERE role = 'staff' LIMIT 1");
assert($testStaff !== null, "A staff user must exist for assignment test");
$testStaffId = (int)$testStaff['id'];

$assignReq = new Request([], [
    '_csrf'        => Csrf::token(),
    'staff_id'     => (string)$testStaffId,
    'scheduled_at' => date('Y-m-d\T14:00'),
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/bookings/{$bid}/assign"]);
$assignResp = $bookCtrl->assignStaff($assignReq, (string)$bid);
assert($assignResp->getStatusCode() === 302, "Expected 302 after assigning staff");

$assignedBooking = Booking::find($bid);
assert($assignedBooking['status'] === 'assigned', "Booking status must be 'assigned'");
$assignedJob = Database::fetchOne("SELECT * FROM jobs WHERE booking_id = :bid", ['bid' => $bid]);
assert($assignedJob !== null && (int)$assignedJob['staff_id'] === $testStaffId, "Job must be assigned to staff");
echo "Test 4: Booking staff assignment & job dispatch - PASSED\n";

// Test 5: Rescheduling Booking
$reschedDate = date('Y-m-d', strtotime('+3 days'));
$reschedReq = new Request([], [
    '_csrf'          => Csrf::token(),
    'preferred_date' => $reschedDate,
    'preferred_time' => '03:00 PM - 06:00 PM',
    'reason'         => 'Customer holiday reschedule',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/bookings/{$bid}/reschedule"]);
$reschedResp = $bookCtrl->reschedule($reschedReq, (string)$bid);
assert($reschedResp->getStatusCode() === 302, "Expected 302 after rescheduling");

$reschedB = Booking::find($bid);
assert($reschedB['preferred_date'] === $reschedDate, "preferred_date must be updated");
assert($reschedB['preferred_time'] === '03:00 PM - 06:00 PM', "preferred_time must be updated");
echo "Test 5: Rescheduling data model and timeline update - PASSED\n";

// Test 6: Dispatch Calendar view
$calReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/calendar']);
$calResp = $bookCtrl->calendar($calReq);
assert($calResp->getStatusCode() === 200, "Expected 200 for /admin/calendar");
assert(str_contains($calResp->getContent(), 'Dispatch & Field Operations Calendar'), "Calendar title missing");
echo "Test 6: Dispatch Calendar rendering - PASSED\n";

// Test 7: Staff Management - Create, Toggle, Skills
Database::query("DELETE FROM users WHERE phone = '9888877777'");
$createStaffReq = new Request([], [
    '_csrf'    => Csrf::token(),
    'name'     => 'Sunil Kumar',
    'phone'    => '9888877777',
    'email'    => 'sunil.tech@refixel.com',
    'password' => 'secret123',
    'skills'   => ['1', '2'],
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/staff']);
$createStaffResp = $staffCtrl->store($createStaffReq);
assert($createStaffResp->getStatusCode() === 302, "Expected 302 after staff creation");

$newStaff = Database::fetchOne("SELECT * FROM users WHERE phone = '9888877777'");
assert($newStaff !== null, "New staff user must be created");
assert($newStaff['role'] === 'staff', "Role must be 'staff'");
assert($newStaff['must_change_password'] == 1, "must_change_password must be 1");
$newStaffId = (int)$newStaff['id'];

// Toggle status to disabled and back to active
$toggleReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/staff/{$newStaffId}/toggle-status"]);
$toggleResp = $staffCtrl->toggleStatus($toggleReq, (string)$newStaffId);
assert($toggleResp->getStatusCode() === 302, "Expected 302 after toggle status");
$toggledStaff = User::find($newStaffId);
assert($toggledStaff['status'] === 'disabled', "Staff status must be disabled");

// Update skills
$skillsReq = new Request([], [
    '_csrf'   => Csrf::token(),
    'skills'  => ['1', '3', '4'],
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/staff/{$newStaffId}/skills"]);
$skillsResp = $staffCtrl->updateSkills($skillsReq, (string)$newStaffId);
assert($skillsResp->getStatusCode() === 302, "Expected 302 after updating skills");

$currentSkills = Database::fetchAll("SELECT category_id FROM staff_skills WHERE staff_id = :sid", ['sid' => $newStaffId]);
assert(count($currentSkills) === 3, "Expected 3 skill mappings");
echo "Test 7: Staff creation, status toggling, and skill assignment - PASSED\n";

// Test 8: Services & Categories Management
Database::query("DELETE FROM bookings WHERE service_id IN (SELECT id FROM services WHERE slug = 'balcony-deep-pressure-wash')");
Database::query("DELETE FROM services WHERE slug = 'balcony-deep-pressure-wash'");
$createSvcReq = new Request([], [
    '_csrf'          => Csrf::token(),
    'name'           => 'Balcony Deep Pressure Wash',
    'category_id'    => '1',
    'starting_price' => '899.00',
    'description'    => 'High-pressure water jet cleaning for balconies and railings.',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/services']);
$createSvcResp = $svcCtrl->storeService($createSvcReq);
assert($createSvcResp->getStatusCode() === 302, "Expected 302 after creating service");

$newSvc = Database::fetchOne("SELECT * FROM services WHERE name = 'Balcony Deep Pressure Wash'");
assert($newSvc !== null, "Service must be created in database");
assert((float)$newSvc['starting_price'] === 899.00, "Starting price must be 899.00");
echo "Test 8: Service catalogue management - PASSED\n";

// Test 9: Payment recording & GST invoice generation
$payReq = new Request([], [
    '_csrf'           => Csrf::token(),
    'booking_id'      => (string)$bid,
    'amount'          => '2499.00',
    'method'          => 'upi',
    'transaction_ref' => 'UPI-9876543210',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/payments']);
$payResp = $payCtrl->recordPayment($payReq);
assert($payResp->getStatusCode() === 302, "Expected 302 after recording payment");

$recordedPay = Database::fetchOne("SELECT * FROM payments WHERE booking_id = :bid AND transaction_ref = 'UPI-9876543210'", ['bid' => $bid]);
assert($recordedPay !== null, "Payment must be recorded in payments table");
assert($recordedPay['status'] === 'paid', "Payment status must be 'paid'");

$generatedInv = Database::fetchOne("SELECT * FROM invoices WHERE payment_id = :pid", ['pid' => $recordedPay['id']]);
assert($generatedInv !== null, "GST Invoice must be generated automatically");
assert(str_starts_with($generatedInv['invoice_no'], 'INV-'), "Invoice number must start with INV-");
assert((float)$generatedInv['subtotal'] === 2499.00, "Invoice subtotal must match payment amount");
assert((float)$generatedInv['gst_rate'] === 18.00, "GST rate must be 18.00%");

// View printable invoice
$invReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => "/admin/invoices/{$generatedInv['id']}"]);
$invResp = $payCtrl->showInvoice($invReq, (string)$generatedInv['id']);
assert($invResp->getStatusCode() === 200, "Expected 200 for invoice view");
assert(str_contains($invResp->getContent(), 'TAX INVOICE'), "Invoice must display TAX INVOICE header");
echo "Test 9: Payment recording, 18% GST calculation, and printable invoice - PASSED\n";

// Test 10: Reports & Analytics view
$repReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/reports']);
$repResp = $repCtrl->index($repReq);
assert($repResp->getStatusCode() === 200, "Expected 200 for /admin/reports");
$repHtml = $repResp->getContent();
assert(str_contains($repHtml, 'Analytics & Operations Reports'), "Report title missing");
assert(str_contains($repHtml, 'Revenue Trend'), "Revenue trend chart missing");
assert(str_contains($repHtml, 'Top Services by Booking Demand'), "Top services table missing");
echo "Test 10: Reports and operations analytics - PASSED\n";

// Test 11: CMS Content Operations (FAQs, Review approval, Areas)
// Create FAQ
$faqReq = new Request([], [
    '_csrf'      => Csrf::token(),
    'question'   => 'Are Refixel technicians verified and background checked?',
    'answer'     => 'Yes, 100% of our field technicians undergo strict background and criminal checks.',
    'service_id' => '0',
    'sort_order' => '1',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/content/faqs']);
$faqResp = $cmsCtrl->storeFaq($faqReq);
assert($faqResp->getStatusCode() === 302, "Expected 302 after saving FAQ");

// Review moderation - insert sample review and approve it
$testCust = Database::fetchOne("SELECT id FROM users WHERE role = 'customer' LIMIT 1");
$custDbId = $testCust ? (int)$testCust['id'] : $adminId;
Database::query("DELETE FROM reviews WHERE booking_id = :bid", ['bid' => $bid]);
Database::query(
    "INSERT INTO reviews (booking_id, customer_id, rating, comment, is_approved, created_at)
     VALUES (:bid, :cid, 5, 'Exceptional deep cleaning service!', 0, NOW())",
    ['bid' => $bid, 'cid' => $custDbId]
);
$revId = (int)Database::lastInsertId();

$approveRevReq = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/content/reviews/{$revId}/approve"]);
$approveRevResp = $cmsCtrl->approveReview($approveRevReq, (string)$revId);
assert($approveRevResp->getStatusCode() === 302, "Expected 302 after approving review");

$approvedRev = Database::fetchOne("SELECT is_approved FROM reviews WHERE id = :id", ['id' => $revId]);
assert((int)$approvedRev['is_approved'] === 1, "Review must be marked approved for live display");

// Add service area
Database::query("DELETE FROM service_areas WHERE pincode = '244713' AND area_name = 'Bazpur Road Industrial Zone'");
$areaReq = new Request([], [
    '_csrf'   => Csrf::token(),
    'city'    => 'Kashipur',
    'pincode' => '244713',
    'name'    => 'Bazpur Road Industrial Zone',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/content/areas']);
$areaResp = $cmsCtrl->storeArea($areaReq);
assert($areaResp->getStatusCode() === 302, "Expected 302 after adding area");
echo "Test 11: CMS content operations (FAQs, review approval, service areas) - PASSED\n";

// Test 12: Business & Notification Settings persistence
$setReq = new Request([], [
    '_csrf'                     => Csrf::token(),
    'business_name'             => 'REFIXEL Facility Services Pvt Ltd',
    'support_phone'             => '+91 98765 00000',
    'admin_notification_email'  => 'dispatch@refixel.com',
    'notify_email_enabled'      => '1',
    'notify_whatsapp_enabled'   => '1',
], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/settings']);
$setResp = $setCtrl->save($setReq);
assert($setResp->getStatusCode() === 302, "Expected 302 after saving settings");

$savedBiz = \App\Models\Setting::get('business_name');
assert($savedBiz === 'REFIXEL Facility Services Pvt Ltd', "Setting 'business_name' must be saved");
$savedMail = \App\Models\Setting::get('admin_notification_email');
assert($savedMail === 'dispatch@refixel.com', "Setting 'admin_notification_email' must be saved");
echo "Test 12: Business metadata & notification settings persistence - PASSED\n";

echo "\n=== ALL 12 ADMIN OPERATIONS FEATURE TESTS PASSED! ===\n";
