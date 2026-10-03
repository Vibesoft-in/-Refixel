<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Category;
use App\Models\StaffEarning;
use App\Models\StaffProfile;
use App\Models\User;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $staff = Database::fetchAll(
            "SELECT u.*, sp.rating_avg, sp.rating_count, sp.is_available, sp.availability_note,
                    COUNT(j.id) as total_jobs,
                    SUM(CASE WHEN j.status IN ('assigned', 'accepted', 'in_progress') THEN 1 ELSE 0 END) as active_jobs
             FROM users u
             LEFT JOIN staff_profiles sp ON u.id = sp.user_id
             LEFT JOIN jobs j ON u.id = j.staff_id
             WHERE u.role = 'staff'
             GROUP BY u.id
             ORDER BY u.status ASC, u.name ASC"
        );

        return $this->render('admin.staff.index', [
            'title' => 'Field Workforce & Technicians | Primodomus Admin',
            'staff' => $staff,
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        $categories = Category::all('name ASC');
        return $this->render('admin.staff.create', [
            'title'      => 'Register Field Technician | Primodomus Admin',
            'categories' => $categories,
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $validator = $this->validate($request, [
            'name'     => 'required|min:2',
            'phone'    => 'required|phone',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            return $this->redirect('/admin/staff/create');
        }

        $phone = preg_replace('/\D/', '', (string)$request->input('phone'));
        $email = $request->input('email') ? trim((string)$request->input('email')) : null;

        // Check duplicates
        $exists = Database::fetchOne("SELECT id FROM users WHERE phone = :p OR (email = :e AND email IS NOT NULL)", [
            'p' => $phone,
            'e' => $email,
        ]);

        if ($exists) {
            View::setFlash('error', 'A user with this phone or email already exists in the system.');
            return $this->redirect('/admin/staff/create');
        }

        $userId = User::create([
            'role'                 => 'staff',
            'name'                 => trim((string)$request->input('name')),
            'phone'                => $phone,
            'email'                => $email,
            'password_hash'        => password_hash((string)$request->input('password'), PASSWORD_BCRYPT),
            'must_change_password' => 1,
            'status'               => 'active',
        ]);

        // Initialize profile
        Database::query(
            "INSERT INTO staff_profiles (user_id, rating_avg, rating_count, is_available, availability_note, created_at)
             VALUES (:uid, 5.00, 0, 1, 'Available for dispatch', NOW())",
            ['uid' => $userId]
        );

        // Assign initial skills
        $skillCategoryIds = $request->input('skills', []);
        if (is_array($skillCategoryIds)) {
            foreach ($skillCategoryIds as $catId) {
                Database::query(
                    "INSERT IGNORE INTO staff_skills (staff_id, category_id, created_at) VALUES (:sid, :cid, NOW())",
                    ['sid' => $userId, 'cid' => (int)$catId]
                );
            }
        }

        View::setFlash('success', "Technician created successfully with temporary password. First login will require password change.");
        return $this->redirect('/admin/staff/' . $userId);
    }

    public function show(Request $request, string $id): Response
    {
        $user = User::find((int)$id);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Technician record not found.');
            return $this->redirect('/admin/staff');
        }

        $profile = StaffProfile::find((int)$id);
        $skills = StaffProfile::getSkills((int)$id);
        $allCategories = Category::all('name ASC');

        $jobs = Database::fetchAll(
            "SELECT j.*, b.booking_no, b.name as customer_name, b.phone as customer_phone,
                    b.preferred_date, s.name as service_name, s.starting_price
             FROM jobs j
             JOIN bookings b ON j.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE j.staff_id = :id
             ORDER BY j.id DESC",
            ['id' => $id]
        );

        $earnings = StaffEarning::getByStaff((int)$id);
        $earningsSummary = StaffEarning::getSummary((int)$id);

        return $this->render('admin.staff.show', [
            'title'           => "Technician: {$user['name']} | Primodomus Admin",
            'user'            => $user,
            'profile'         => $profile,
            'skills'          => $skills,
            'allCategories'   => $allCategories,
            'jobs'            => $jobs,
            'earnings'        => $earnings,
            'earningsSummary' => $earningsSummary,
        ], 'admin');
    }

    public function toggleStatus(Request $request, string $id): Response
    {
        $user = User::find((int)$id);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Technician not found.');
            return $this->redirect('/admin/staff');
        }

        $newStatus = ($user['status'] === 'active') ? 'disabled' : 'active';
        User::update((int)$id, ['status' => $newStatus]);

        View::setFlash('success', "Technician {$user['name']} has been " . ($newStatus === 'active' ? 'activated' : 'deactivated') . ".");
        return $this->redirect('/admin/staff/' . $id);
    }

    public function updateSkills(Request $request, string $id): Response
    {
        $user = User::find((int)$id);
        if (!$user || $user['role'] !== 'staff') {
            View::setFlash('error', 'Technician not found.');
            return $this->redirect('/admin/staff');
        }

        $skillCategoryIds = $request->input('skills', []);
        Database::query("DELETE FROM staff_skills WHERE staff_id = :sid", ['sid' => $id]);

        if (is_array($skillCategoryIds)) {
            foreach ($skillCategoryIds as $catId) {
                Database::query(
                    "INSERT INTO staff_skills (staff_id, category_id, created_at) VALUES (:sid, :cid, NOW())",
                    ['sid' => $id, 'cid' => (int)$catId]
                );
            }
        }

        View::setFlash('success', "Skills updated successfully for {$user['name']}.");
        return $this->redirect('/admin/staff/' . $id);
    }
}
