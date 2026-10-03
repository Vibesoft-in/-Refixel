<?php
declare(strict_types=1);

namespace App\Controllers\Staff;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\StaffEarning;
use App\Models\StaffProfile;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $staffId = Auth::id();
        $user = Auth::user();
        $todayJobs = Job::findByStaff($staffId, 'today');
        $upcomingJobs = Job::findByStaff($staffId, 'upcoming');
        $inProgressJobs = Job::findByStaff($staffId, 'in_progress');
        $stats = Job::getStaffDashboardStats($staffId);
        $profile = StaffProfile::find($staffId);
        $earningsSummary = StaffEarning::getSummary($staffId);
        $recentEarnings = array_slice(StaffEarning::getByStaff($staffId), 0, 3);

        return $this->render('staff.dashboard', [
            'title'           => 'Technician Portal | Primodomus',
            'user'            => $user,
            'todayJobs'       => $todayJobs,
            'upcomingJobs'    => $upcomingJobs,
            'inProgressJobs'  => $inProgressJobs,
            'stats'           => $stats,
            'profile'         => $profile,
            'earningsSummary' => $earningsSummary,
            'recentEarnings'  => $recentEarnings,
        ], 'staff');
    }
}
