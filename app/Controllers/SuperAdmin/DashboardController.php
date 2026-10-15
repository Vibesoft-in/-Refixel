<?php
declare(strict_types=1);

namespace App\Controllers\SuperAdmin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Super Admin Main Control Center
     */
    public function index(Request $request): Response
    {
        // 1. Gather Platform Overview Metrics
        $enquiriesCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new'")['c'] ?? 0);
        $totalBookings  = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0);
        $calendarCount  = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE preferred_date IS NOT NULL AND preferred_date != ''")['c'] ?? 0);
        $techCount       = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'staff'")['c'] ?? 0);
        $customerCount  = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'customer'")['c'] ?? 0);
        $adminCount     = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'admin'")['c'] ?? 0);
        $activeGateways = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM payment_gateways WHERE is_active = 1")['c'] ?? 0);
        $commRulesCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM commission_rules")['c'] ?? 0);

        $totalRevenue = (float)(Database::fetchOne("SELECT SUM(amount) as s FROM payments WHERE status = 'paid'")['s'] ?? 0);

        // Recent enquiries preview
        $recentEnquiries = Database::fetchAll(
            "SELECT b.id, b.booking_no, b.status, b.total_amount, b.name as lead_name, b.phone as lead_phone, b.created_at, 
                    s.name as service_name, s.starting_price,
                    u.name as customer_name, u.phone as customer_phone
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             LEFT JOIN users u ON b.customer_id = u.id
             WHERE b.status = 'new'
             ORDER BY b.created_at DESC LIMIT 5"
        );

        return $this->render('superadmin.dashboard', [
            'title'          => 'Super Admin Control Center | REFIXEL',
            'enquiriesCount' => $enquiriesCount,
            'totalBookings'  => $totalBookings,
            'calendarCount'  => $calendarCount,
            'techCount'      => $techCount,
            'customerCount'  => $customerCount,
            'adminCount'     => $adminCount,
            'activeGateways' => $activeGateways,
            'commRulesCount' => $commRulesCount,
            'totalRevenue'   => $totalRevenue,
            'recentEnquiries'=> $recentEnquiries,
        ], 'superadmin');
    }

    // =========================================================================
    // 1. ENQUIRIES SECTION & BULK DELETE
    // =========================================================================

    public function enquiries(Request $request): Response
    {
        $status = $request->query('status', 'new');
        $sql = "SELECT b.*, s.name as service_name, s.starting_price, u.name as customer_name, u.phone as customer_phone, u.email as customer_email
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                LEFT JOIN users u ON b.customer_id = u.id";

        $params = [];
        if ($status !== 'all') {
            $sql .= " WHERE b.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY b.created_at DESC";

        $enquiries = Database::fetchAll($sql, $params);
        $counts = [
            'new' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new'")['c'] ?? 0),
            'all' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0),
        ];

        return $this->render('superadmin.enquiries.index', [
            'title'     => 'Manage Customer Enquiries | Super Admin',
            'enquiries' => $enquiries,
            'status'    => $status,
            'counts'    => $counts,
        ], 'superadmin');
    }

    public function deleteAllEnquiries(Request $request): Response
    {
        $scope = $request->post('scope', 'new'); // 'new' or 'all'

        try {
            Database::beginTransaction();

            if ($scope === 'all') {
                $count = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0);
                if ($count > 0) {
                    Database::query("DELETE FROM bookings");
                }
            } else {
                $count = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new'")['c'] ?? 0);
                if ($count > 0) {
                    Database::query("DELETE FROM bookings WHERE status = 'new'");
                }
            }

            Database::commit();

            Logger::info("Super Admin (ID: " . Auth::id() . ") deleted all enquiries (scope: {$scope}, count: {$count})");
            View::setFlash('success', "Success: {$count} enquiries have been permanently deleted in a single click.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error("Failed to delete all enquiries: " . $e->getMessage());
            View::setFlash('error', "Error deleting enquiries: " . $e->getMessage());
        }

        return $this->redirect('/superadmin/enquiries');
    }

    public function deleteEnquiry(Request $request, string $id): Response
    {
        $bookingId = (int)$id;
        try {
            Database::query("DELETE FROM bookings WHERE id = :id", ['id' => $bookingId]);
            View::setFlash('success', "Enquiry #{$bookingId} has been deleted.");
        } catch (\Throwable $e) {
            View::setFlash('error', "Failed to delete enquiry: " . $e->getMessage());
        }
        return $this->redirect('/superadmin/enquiries');
    }

    // =========================================================================
    // 2. TECHNICIANS / STAFF SECTION & BULK DELETE
    // =========================================================================

    public function staff(Request $request): Response
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                       sp.rating_avg, sp.rating_count, sp.is_available, sp.availability_note,
                       (SELECT COUNT(*) FROM jobs j WHERE j.staff_id = u.id) as jobs_count,
                       (SELECT COUNT(*) FROM jobs j WHERE j.staff_id = u.id AND j.status = 'completed') as completed_jobs_count
                FROM users u
                LEFT JOIN staff_profiles sp ON u.id = sp.user_id
                WHERE u.role = 'staff'
                ORDER BY u.created_at DESC";

        $staffMembers = Database::fetchAll($sql);
        $totalCount = count($staffMembers);

        return $this->render('superadmin.staff.index', [
            'title'        => 'Manage Technicians | Super Admin',
            'staffMembers' => $staffMembers,
            'totalCount'   => $totalCount,
        ], 'superadmin');
    }

    public function deleteAllStaff(Request $request): Response
    {
        try {
            Database::beginTransaction();

            $staffUsers = Database::fetchAll("SELECT id FROM users WHERE role = 'staff'");
            $count = count($staffUsers);

            if ($count > 0) {
                $staffIds = array_column($staffUsers, 'id');
                $placeholders = implode(',', array_fill(0, count($staffIds), '?'));

                // 1. Delete associated staff_earnings
                Database::query("DELETE FROM staff_earnings WHERE staff_id IN ({$placeholders})", $staffIds);

                // 2. Clear or reset jobs assigned to these staff members
                // Revert job bookings back to 'new' if not completed, or remove jobs
                Database::query("UPDATE bookings SET status = 'new' WHERE id IN (SELECT booking_id FROM jobs WHERE staff_id IN ({$placeholders}))", $staffIds);
                Database::query("DELETE FROM jobs WHERE staff_id IN ({$placeholders})", $staffIds);

                // 3. Delete staff availability & skills & unlink reviews
                Database::query("UPDATE reviews SET staff_id = NULL WHERE staff_id IN ({$placeholders})", $staffIds);
                Database::query("DELETE FROM staff_availability WHERE staff_id IN ({$placeholders})", $staffIds);
                Database::query("DELETE FROM staff_skills WHERE staff_id IN ({$placeholders})", $staffIds);
                Database::query("DELETE FROM staff_profiles WHERE user_id IN ({$placeholders})", $staffIds);

                // 4. Delete the staff users themselves
                Database::query("DELETE FROM users WHERE role = 'staff'");
            }

            Database::commit();

            Logger::info("Super Admin (ID: " . Auth::id() . ") deleted all technicians (count: {$count})");
            View::setFlash('success', "Success: {$count} technicians/staff members have been permanently deleted in a single click.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error("Failed to delete all technicians: " . $e->getMessage());
            View::setFlash('error', "Error deleting technicians: " . $e->getMessage());
        }

        return $this->redirect('/superadmin/staff');
    }

    public function deleteStaff(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        try {
            Database::beginTransaction();

            // Clean staff dependencies
            Database::query("DELETE FROM staff_earnings WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("UPDATE bookings SET status = 'new' WHERE id IN (SELECT booking_id FROM jobs WHERE staff_id = :id)", ['id' => $staffId]);
            Database::query("DELETE FROM jobs WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("UPDATE reviews SET staff_id = NULL WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM staff_availability WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM staff_skills WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM staff_profiles WHERE user_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM users WHERE id = :id AND role = 'staff'", ['id' => $staffId]);

            Database::commit();
            View::setFlash('success', "Technician #{$staffId} has been deleted.");
        } catch (\Throwable $e) {
            Database::rollBack();
            View::setFlash('error', "Failed to delete technician: " . $e->getMessage());
        }
        return $this->redirect('/superadmin/staff');
    }

    // =========================================================================
    // 3. CUSTOMERS SECTION & BULK DELETE
    // =========================================================================

    public function customers(Request $request): Response
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                       cp.city, cp.address, cp.pincode,
                       (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = u.id) as bookings_count,
                       (SELECT COUNT(*) FROM customer_addresses ca WHERE ca.user_id = u.id) as addresses_count
                FROM users u
                LEFT JOIN customer_profiles cp ON u.id = cp.user_id
                WHERE u.role = 'customer'
                ORDER BY u.created_at DESC";

        $customers = Database::fetchAll($sql);
        $totalCount = count($customers);

        return $this->render('superadmin.customers.index', [
            'title'      => 'Manage Customers | Super Admin',
            'customers'  => $customers,
            'totalCount' => $totalCount,
        ], 'superadmin');
    }

    public function deleteAllCustomers(Request $request): Response
    {
        try {
            Database::beginTransaction();

            $customerUsers = Database::fetchAll("SELECT id FROM users WHERE role = 'customer'");
            $count = count($customerUsers);

            if ($count > 0) {
                $custIds = array_column($customerUsers, 'id');
                $placeholders = implode(',', array_fill(0, count($custIds), '?'));

                // 1. Unlink bookings customer_id to avoid FK restriction
                Database::query("UPDATE bookings SET customer_id = NULL WHERE customer_id IN ({$placeholders})", $custIds);

                // 2. Remove customer profiles, addresses, consents
                try {
                    Database::query("DELETE FROM customer_addresses WHERE user_id IN ({$placeholders})", $custIds);
                } catch (\Throwable $e) {}
                Database::query("DELETE FROM customer_profiles WHERE user_id IN ({$placeholders})", $custIds);
                Database::query("DELETE FROM consents WHERE user_id IN ({$placeholders})", $custIds);
                Database::query("DELETE FROM reviews WHERE customer_id IN ({$placeholders})", $custIds);

                // 3. Delete customer users
                Database::query("DELETE FROM users WHERE role = 'customer'");
            }

            Database::commit();

            Logger::info("Super Admin (ID: " . Auth::id() . ") deleted all customers (count: {$count})");
            View::setFlash('success', "Success: {$count} customers have been permanently deleted in a single click.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error("Failed to delete all customers: " . $e->getMessage());
            View::setFlash('error', "Error deleting customers: " . $e->getMessage());
        }

        return $this->redirect('/superadmin/customers');
    }

    public function deleteCustomer(Request $request, string $id): Response
    {
        $custId = (int)$id;
        try {
            Database::beginTransaction();

            Database::query("UPDATE bookings SET customer_id = NULL WHERE customer_id = :id", ['id' => $custId]);
            try {
                Database::query("DELETE FROM customer_addresses WHERE user_id = :id", ['id' => $custId]);
            } catch (\Throwable $e) {}
            Database::query("DELETE FROM customer_profiles WHERE user_id = :id", ['id' => $custId]);
            Database::query("DELETE FROM consents WHERE user_id = :id", ['id' => $custId]);
            Database::query("DELETE FROM reviews WHERE customer_id = :id", ['id' => $custId]);
            Database::query("DELETE FROM users WHERE id = :id AND role = 'customer'", ['id' => $custId]);

            Database::commit();
            View::setFlash('success', "Customer #{$custId} has been deleted.");
        } catch (\Throwable $e) {
            Database::rollBack();
            View::setFlash('error', "Failed to delete customer: " . $e->getMessage());
        }
        return $this->redirect('/superadmin/customers');
    }

    // =========================================================================
    // 4. ADMIN ACCOUNTS GOVERNANCE
    // =========================================================================

    public function admins(Request $request): Response
    {
        $admins = Database::fetchAll(
            "SELECT id, role, name, email, phone, status, last_login_at, created_at 
             FROM users 
             WHERE role IN ('admin', 'super_admin') 
             ORDER BY (role = 'super_admin') DESC, id ASC"
        );

        return $this->render('superadmin.admins.index', [
            'title'  => 'Admin Accounts Governance | Super Admin',
            'admins' => $admins,
        ], 'superadmin');
    }

    public function storeAdmin(Request $request): Response
    {
        // Enforce strict platform security: ONLY Super Admin can create admin accounts
        if (!Auth::isSuperAdmin()) {
            View::setFlash('error', 'Security Policy Violation: Only Super Admin can provision administrative accounts.');
            return $this->redirect('/admin');
        }

        $name     = trim((string)$request->input('name', ''));
        $email    = trim((string)$request->input('email', ''));
        $phone    = preg_replace('/\D/', '', (string)$request->input('phone', ''));
        $password = trim((string)$request->input('password', ''));
        $role     = $request->input('role') === 'super_admin' ? 'super_admin' : 'admin';

        if (empty($name) || empty($password) || (empty($email) && empty($phone))) {
            View::setFlash('error', 'Name, password, and at least email or phone are required.');
            return $this->redirect('/superadmin/admins');
        }

        if (!empty($email) && User::findBy('email', $email)) {
            View::setFlash('error', 'An account with that email already exists.');
            return $this->redirect('/superadmin/admins');
        }

        if (!empty($phone) && User::findBy('phone', $phone)) {
            View::setFlash('error', 'An account with that phone already exists.');
            return $this->redirect('/superadmin/admins');
        }

        try {
            User::create([
                'role'          => $role,
                'name'          => $name,
                'email'         => !empty($email) ? $email : null,
                'phone'         => !empty($phone) ? $phone : null,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'status'        => 'active',
                'must_change_password' => 0,
            ]);

            View::setFlash('success', "New {$role} user '{$name}' created successfully!");
        } catch (\Throwable $e) {
            View::setFlash('error', "Failed to create admin: " . $e->getMessage());
        }

        return $this->redirect('/superadmin/admins');
    }

    public function deleteAdmin(Request $request, string $id): Response
    {
        // Enforce strict platform security: ONLY Super Admin can revoke admin accounts
        if (!Auth::isSuperAdmin()) {
            View::setFlash('error', 'Security Policy Violation: Only Super Admin can revoke administrative accounts.');
            return $this->redirect('/admin');
        }

        $adminId = (int)$id;

        // Prevent self deletion
        if ($adminId === Auth::id()) {
            View::setFlash('error', 'Cannot delete your own active Super Admin account.');
            return $this->redirect('/superadmin/admins');
        }

        try {
            Database::query("DELETE FROM users WHERE id = :id AND role = 'admin'", ['id' => $adminId]);
            View::setFlash('success', "Admin account #{$adminId} deleted successfully.");
        } catch (\Throwable $e) {
            View::setFlash('error', "Failed to delete admin: " . $e->getMessage());
        }

        return $this->redirect('/superadmin/admins');
    }

    // =========================================================================
    // 5. BOOKINGS & JOBS BULK PURGE
    // =========================================================================

    public function deleteAllBookings(Request $request): Response
    {
        try {
            Database::beginTransaction();

            $count = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0);
            if ($count > 0) {
                // Cascades handle jobs, job_photos, status_history, payments, invoices, staff_earnings, reviews, attachments
                Database::query("DELETE FROM bookings");
            }

            Database::commit();

            Logger::info("Super Admin (ID: " . Auth::id() . ") purged all bookings & jobs (count: {$count})");
            View::setFlash('success', "Success: All {$count} customer bookings, assigned technician jobs, and operations records deleted in a single click.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error("Failed to delete all bookings: " . $e->getMessage());
            View::setFlash('error', "Error deleting bookings: " . $e->getMessage());
        }

        $redirect = $request->post('redirect', '/admin/bookings');
        return $this->redirect($redirect);
    }

    public function deleteBooking(Request $request, string $id): Response
    {
        $bookingId = (int)$id;
        try {
            Database::query("DELETE FROM bookings WHERE id = :id", ['id' => $bookingId]);
            View::setFlash('success', "Booking #{$bookingId} and associated jobs deleted.");
        } catch (\Throwable $e) {
            View::setFlash('error', "Failed to delete booking: " . $e->getMessage());
        }
        $redirect = $request->post('redirect', '/admin/bookings');
        return $this->redirect($redirect);
    }

    // =========================================================================
    // 6. DISPATCH CALENDAR & APPOINTMENTS BULK PURGE
    // =========================================================================

    public function deleteAllCalendar(Request $request): Response
    {
        $month = $request->post('month', '');
        $scope = $request->post('scope', 'all');

        try {
            Database::beginTransaction();

            if ($scope === 'month' && !empty($month)) {
                $count = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE preferred_date LIKE :m", ['m' => "{$month}%"])['c'] ?? 0);
                if ($count > 0) {
                    Database::query("DELETE FROM bookings WHERE preferred_date LIKE :m", ['m' => "{$month}%"]);
                }
                $msg = "Success: {$count} dispatch calendar appointments for {$month} deleted in a single click.";
            } else {
                $count = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE preferred_date IS NOT NULL AND preferred_date != ''")['c'] ?? 0);
                if ($count > 0) {
                    Database::query("DELETE FROM bookings WHERE preferred_date IS NOT NULL AND preferred_date != ''");
                }
                $msg = "Success: All {$count} scheduled calendar appointments and dispatch jobs deleted in a single click.";
            }

            Database::commit();

            Logger::info("Super Admin (ID: " . Auth::id() . ") purged calendar dispatches (scope: {$scope}, month: {$month}, count: {$count})");
            View::setFlash('success', $msg);
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error("Failed to delete calendar dispatches: " . $e->getMessage());
            View::setFlash('error', "Error deleting calendar dispatches: " . $e->getMessage());
        }

        $redirect = $request->post('redirect', '/admin/calendar');
        return $this->redirect($redirect);
    }
}
