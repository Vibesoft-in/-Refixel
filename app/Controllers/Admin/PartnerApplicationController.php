<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\User;

class PartnerApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = strtolower(trim((string)$request->query('status', 'pending')));
        if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        // Query application counts
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

        // Parse profile notes into clean fields
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
            'title'        => 'Partner Applications & Review | REFIXEL Admin',
            'applications' => $applications,
            'currentTab'   => $status,
            'counts'       => $counts,
        ], 'admin');
    }

    public function approve(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        $user = User::find($staffId);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Partner application record not found.');
            return $this->redirect('/admin/partner-applications');
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
                 availability_note = 'Approved by admin, ready for dispatch', 
                 approved_at = NOW(), 
                 approved_by = :admin_id,
                 rejection_reason = NULL
             WHERE user_id = :uid",
            [
                'admin_id' => Auth::id(),
                'uid'      => $staffId,
            ]
        );

        // Fetch skills and profile details for approval email
        $skill = Database::fetchOne("SELECT c.name FROM staff_skills ss JOIN categories c ON ss.category_id = c.id WHERE ss.staff_id = :uid LIMIT 1", ['uid' => $staffId]);
        $user['trade'] = $skill['name'] ?? 'Home Services Technician';
        $user['city'] = 'Refixel Service Area';

        Notifier::notifyPartnerApproved($user, $plainPassword);

        Logger::info("Admin (ID: " . Auth::id() . ") approved partner application #{$staffId} ({$user['name']})");
        View::setFlash('success', "Technician '{$user['name']}' has been APPROVED! Account is now active in Staff & Techs, and login credentials have been emailed to {$user['email']}.");

        return $this->redirect('/admin/partner-applications?status=approved');
    }

    public function reject(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        $user = User::find($staffId);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Partner application record not found.');
            return $this->redirect('/admin/partner-applications');
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

        // Send polite rejection notice email
        Notifier::notifyPartnerRejected($user, $reason);

        Logger::info("Admin (ID: " . Auth::id() . ") rejected partner application #{$staffId} ({$user['name']}). Reason: {$reason}");
        View::setFlash('warning', "Partner application for '{$user['name']}' has been REJECTED. A notification email was sent to {$user['email']}.");

        return $this->redirect('/admin/partner-applications?status=rejected');
    }

    public function delete(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        try {
            Database::beginTransaction();
            Database::query("DELETE FROM staff_earnings WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM staff_availability WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM staff_skills WHERE staff_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM staff_profiles WHERE user_id = :id", ['id' => $staffId]);
            Database::query("DELETE FROM users WHERE id = :id AND role = 'staff'", ['id' => $staffId]);
            Database::commit();

            View::setFlash('success', "Application record #{$staffId} has been deleted.");
        } catch (\Throwable $e) {
            Database::rollBack();
            View::setFlash('error', "Failed to delete application: " . $e->getMessage());
        }

        return $this->redirect('/admin/partner-applications');
    }
}
