<?php
declare(strict_types=1);

namespace App\Controllers\Staff;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\StaffProfile;

class ProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $profile = StaffProfile::find($user['id']);
        $skills = StaffProfile::getSkills($user['id']);

        return $this->render('staff.profile', [
            'title'   => 'My Profile | Primodomus Staff',
            'user'    => $user,
            'profile' => $profile,
            'skills'  => $skills,
        ], 'staff');
    }

    public function update(Request $request): Response
    {
        $userId = Auth::id();
        $isAvailable = $request->input('is_available') === '1';
        $note = trim((string)$request->input('availability_note'));

        try {
            StaffProfile::updateAvailability($userId, $isAvailable, $note);
            View::setFlash('success', 'Your availability status has been updated.');
        } catch (\Throwable $e) {
            View::setFlash('error', 'Failed to update availability: ' . $e->getMessage());
        }

        return $this->redirect('/staff/profile');
    }
}
