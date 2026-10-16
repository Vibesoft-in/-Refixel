<?php
declare(strict_types=1);

namespace App\Controllers\SuperAdmin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Notifier;
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
    public function staff(Request $request): Response
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                       sp.rating_avg, sp.rating_count, sp.is_available, sp.availability_note,
                       (SELECT COUNT(*) FROM jobs j WHERE j.staff_id = u.id) as jobs_count,
                       (SELECT COUNT(*) FROM jobs j WHERE j.staff_id = u.id AND j.status = 'completed') as completed_jobs_count,
                       (SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
                        FROM staff_skills ss
                        JOIN categories c ON ss.category_id = c.id
                        WHERE ss.staff_id = u.id) as trade_skills
                FROM users u
                LEFT JOIN staff_profiles sp ON u.id = sp.user_id
                WHERE u.role = 'staff'
                ORDER BY CASE WHEN u.status = 'pending' THEN 0 ELSE 1 END, u.created_at DESC";

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

    public function approveStaff(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        $user = User::find($staffId);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Technician record not found.');
            return $this->redirect('/superadmin/staff');
        }

        $plainPassword = 'Refixel@' . random_int(1000, 9999);
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        User::update($staffId, [
            'status'               => 'active',
            'password_hash'        => $passwordHash,
            'must_change_password' => 1,
        ]);

        Database::query(
            "UPDATE staff_profiles 
             SET is_available = 1, 
                 availability_note = 'Approved by Super Admin, ready for dispatch', 
                 approved_at = NOW(), 
                 approved_by = :admin_id 
             WHERE user_id = :uid",
            [
                'admin_id' => Auth::id(),
                'uid'      => $staffId,
            ]
        );

        // Send approval email with login credentials
        $skill = Database::fetchOne("SELECT c.name FROM staff_skills ss JOIN categories c ON ss.category_id = c.id WHERE ss.staff_id = :uid LIMIT 1", ['uid' => $staffId]);
        $user['trade'] = $skill['name'] ?? 'Home Services Technician';
        $user['city'] = 'Refixel Service Area';
        Notifier::notifyPartnerApproved($user, $plainPassword);

        Logger::info("Super Admin (ID: " . Auth::id() . ") approved technician #{$staffId} ({$user['name']})");
        View::setFlash('success', "Technician '{$user['name']}' has been approved successfully! Credentials sent to {$user['email']}.");

        return $this->redirect('/superadmin/staff');
    }

    public function partnerApplications(Request $request): Response
    {
        $status = strtolower(trim((string)$request->query('status', 'pending')));
        if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $counts = [
            'pending'  => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'staff' AND status = 'pending'")['c'] ?? 0),
            'approved' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'staff' AND status = 'active'")['c'] ?? 0),
            'rejected' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'staff' AND status = 'rejected'")['c'] ?? 0),
            'all'      => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'staff'")['c'] ?? 0),
        ];

        $sql = "SELECT u.id, u.name, u.email, u.phone, u.status, u.created_at,
                       sp.availability_note, sp.approved_at, sp.approved_by, sp.rejection_reason,
                       admin_u.name as approved_by_name,
                       (SELECT c.name FROM staff_skills ss JOIN categories c ON ss.category_id = c.id WHERE ss.staff_id = u.id LIMIT 1) as trade_name
                FROM users u
                LEFT JOIN staff_profiles sp ON u.id = sp.user_id
                LEFT JOIN users admin_u ON sp.approved_by = admin_u.id
                WHERE u.role = 'staff'";

        $params = [];
        if ($status === 'pending') {
            $sql .= " AND u.status = 'pending'";
        } elseif ($status === 'approved') {
            $sql .= " AND u.status = 'active'";
        } elseif ($status === 'rejected') {
            $sql .= " AND u.status = 'rejected'";
        }
        $sql .= " ORDER BY u.created_at DESC";

        $applications = Database::fetchAll($sql, $params);

        foreach ($applications as &$app) {
            $note = (string)($app['availability_note'] ?? '');
            $app['city'] = '';
            $app['skill'] = $app['trade_name'] ?? '';
            $app['experience'] = '';

            if (str_contains($note, 'City:')) {
                $parts = explode('|', $note);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (str_starts_with($part, 'City:')) {
                        $app['city'] = trim(substr($part, 5));
                    } elseif (str_starts_with($part, 'Skill:')) {
                        if (empty($app['skill'])) {
                            $app['skill'] = trim(substr($part, 6));
                        }
                    } elseif (str_starts_with($part, 'Exp:')) {
                        $app['experience'] = trim(substr($part, 4));
                    }
                }
            }
        }
        unset($app);

        return $this->render('admin.partner_applications.index', [
            'title'        => 'Partner Applications & Review | Super Admin',
            'applications' => $applications,
            'currentTab'   => $status,
            'counts'       => $counts,
        ], 'superadmin');
    }

    public function approvePartnerApplication(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        $user = User::find($staffId);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Partner application record not found.');
            return $this->redirect('/superadmin/partner-applications');
        }

        $plainPassword = 'Refixel@' . random_int(1000, 9999);
        $passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        User::update($staffId, [
            'status'               => 'active',
            'password_hash'        => $passwordHash,
            'must_change_password' => 1,
        ]);

        Database::query(
            "UPDATE staff_profiles 
             SET is_available = 1, 
                 availability_note = 'Approved by Super Admin, ready for dispatch', 
                 approved_at = NOW(), 
                 approved_by = :admin_id,
                 rejection_reason = NULL
             WHERE user_id = :uid",
            [
                'admin_id' => Auth::id(),
                'uid'      => $staffId,
            ]
        );

        $skill = Database::fetchOne("SELECT c.name FROM staff_skills ss JOIN categories c ON ss.category_id = c.id WHERE ss.staff_id = :uid LIMIT 1", ['uid' => $staffId]);
        $user['trade'] = $skill['name'] ?? 'Home Services Technician';
        $user['city'] = 'Refixel Service Area';
        Notifier::notifyPartnerApproved($user, $plainPassword);

        Logger::info("Super Admin (ID: " . Auth::id() . ") approved technician application #{$staffId} ({$user['name']})");
        View::setFlash('success', "Technician '{$user['name']}' has been APPROVED! Account is now active in Workforce, and credentials have been emailed to {$user['email']}.");

        return $this->redirect('/superadmin/partner-applications?status=approved');
    }

    public function rejectPartnerApplication(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        $user = User::find($staffId);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Partner application record not found.');
            return $this->redirect('/superadmin/partner-applications');
        }

        $reason = trim((string)$request->input('reason', 'Application did not meet current network criteria or service coverage.'));
        if (empty($reason)) {
            $reason = 'Application did not meet current network criteria or service coverage.';
        }

        User::update($staffId, [
            'status' => 'rejected',
        ]);

        Database::query(
            "UPDATE staff_profiles 
             SET is_available = 0, 
                 rejection_reason = :reason,
                 approved_at = NULL,
                 approved_by = NULL
             WHERE user_id = :uid",
            [
                'reason' => $reason,
                'uid'    => $staffId,
            ]
        );

        Notifier::notifyPartnerRejected($user, $reason);

        Logger::info("Super Admin (ID: " . Auth::id() . ") rejected partner application #{$staffId} ({$user['name']}). Reason: {$reason}");
        View::setFlash('warning', "Partner application for '{$user['name']}' has been REJECTED. A notification email was sent to {$user['email']}.");

        return $this->redirect('/superadmin/partner-applications?status=rejected');
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
            "SELECT id, role, name, email, phone, plain_password, status, last_login_at, created_at 
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
                'plain_password'=> $password,
                'status'        => 'active',
                'must_change_password' => 0,
            ]);

            // Automatically email login credentials to newly created admin
            if (!empty($email)) {
                try {
                    \App\Core\Notifier::notifyAdminCreated([
                        'name'      => $name,
                        'email'     => $email,
                        'phone'     => $phone,
                        'role'      => $role,
                        'password'  => $password,
                        'login_url' => View::fullUrl('/login'),
                    ]);
                } catch (\Throwable $mailEx) {
                    \App\Core\Logger::error("Failed to send welcome credentials email to admin {$email}: " . $mailEx->getMessage());
                }
            }

            View::setFlash('success', "New {$role} user '{$name}' created successfully! Login credentials have been emailed to {$email}.");
        } catch (\Throwable $e) {
            View::setFlash('error', "Failed to create admin: " . $e->getMessage());
        }

        return $this->redirect('/superadmin/admins');
    }

    public function updateAdminPassword(Request $request, string $id): Response
    {
        // Enforce strict platform security: ONLY Super Admin can manage administrative credentials
        if (!Auth::isSuperAdmin()) {
            View::setFlash('error', 'Security Policy Violation: Only Super Admin can modify administrative credentials.');
            return $this->redirect('/admin');
        }

        $adminId = (int)$id;
        $admin = User::find($adminId);
        if (!$admin || !in_array($admin['role'], ['admin', 'super_admin'], true)) {
            View::setFlash('error', 'Administrative account not found.');
            return $this->redirect('/superadmin/admins');
        }

        // Only Super Admin can change another super admin password if they are that super admin
        if ($admin['role'] === 'super_admin' && $adminId !== Auth::id()) {
            View::setFlash('error', 'Cannot reset password for another Super Admin account.');
            return $this->redirect('/superadmin/admins');
        }

        $password = trim((string)$request->input('password', ''));
        if (strlen($password) < 6) {
            View::setFlash('error', 'Password must be at least 6 characters long.');
            return $this->redirect('/superadmin/admins');
        }

        try {
            User::update($adminId, [
                'password_hash'  => password_hash($password, PASSWORD_BCRYPT),
                'plain_password' => $password,
            ]);

            // Automatically email updated login credentials if email exists
            if (!empty($admin['email'])) {
                try {
                    \App\Core\Notifier::notifyAdminPasswordReset([
                        'name'      => $admin['name'],
                        'email'     => $admin['email'],
                        'phone'     => $admin['phone'],
                        'role'      => $admin['role'],
                        'password'  => $password,
                        'login_url' => View::fullUrl('/login'),
                    ]);
                } catch (\Throwable $mailEx) {
                    \App\Core\Logger::error("Failed to email reset credentials to admin {$admin['email']}: " . $mailEx->getMessage());
                }
            }

            View::setFlash('success', "Password for {$admin['name']} (#{$adminId}) updated successfully.");
        } catch (\Throwable $e) {
            View::setFlash('error', "Failed to update password: " . $e->getMessage());
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
