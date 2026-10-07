<?php
declare(strict_types=1);

namespace App\Controllers\Staff;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\StaffEarning;

class EarningsController extends Controller
{
    public function index(Request $request): Response
    {
        $staffId = Auth::id();
        $summary = StaffEarning::getSummary($staffId);
        $allEarnings = StaffEarning::getByStaff($staffId);

        $selectedMonth = $request->query('month');
        if ($selectedMonth) {
            $earnings = array_filter($allEarnings, fn($e) => $e['month'] === $selectedMonth);
        } else {
            $earnings = $allEarnings;
        }

        // Distinct months for filter dropdown
        $availableMonths = array_values(array_unique(array_column($allEarnings, 'month')));

        return $this->render('staff.earnings', [
            'title'           => 'My Earnings & Payouts | REFIXEL Staff',
            'summary'         => $summary,
            'earnings'        => $earnings,
            'selectedMonth'   => $selectedMonth,
            'availableMonths' => $availableMonths,
        ], 'staff');
    }
}

