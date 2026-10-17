<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Report;
use App\Core\Request;
use App\Core\Response;

use App\Services\AutoDispatchService;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all');
        $summary = Report::getAdminSummary($filter);
        $chartData = Report::getMonthlyRevenueChartData();
        $statusDist = Report::getJobStatusDistribution();
        $isBusyMode = AutoDispatchService::isBusyModeEnabled();

        // Recent Bookings with customer, service, and staff info
        $recentBookings = Database::fetchAll(
            "SELECT b.*, s.name as service_name, s.starting_price, u.name as customer_name,
                    st.name as staff_name, j.status as job_status, j.id as job_id
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             LEFT JOIN users u ON b.customer_id = u.id
             LEFT JOIN jobs j ON b.id = j.booking_id
             LEFT JOIN users st ON j.staff_id = st.id
             ORDER BY b.id DESC
             LIMIT 8"
        );

        $topServices = Report::getTopServices(4);
        $topStaff = Report::getStaffLeaderboard(4);

        return $this->render('admin.dashboard', [
            'title'          => 'Admin Operations Dashboard | REFIXEL',
            'summary'        => $summary,
            'chartData'      => $chartData,
            'statusDist'     => $statusDist,
            'recentBookings' => $recentBookings,
            'topServices'    => $topServices,
            'topStaff'       => $topStaff,
            'filter'         => $filter,
            'isBusyMode'     => $isBusyMode,
        ], 'admin');
    }

    /**
     * AJAX endpoint to toggle Admin Busy Mode and trigger auto-assignment of pending enquiries.
     */
    public function toggleBusyMode(Request $request): Response
    {
        $enabled = filter_var($request->input('enabled', false), FILTER_VALIDATE_BOOLEAN);
        AutoDispatchService::setBusyMode($enabled);

        $assignedData = [
            'assigned_count'   => 0,
            'assigned'         => [],
            'unassigned_count' => 0,
            'unassigned'       => [],
        ];

        if ($enabled) {
            $assignedData = AutoDispatchService::autoAssignPendingEnquiries($this->userId());
        }

        $count = $assignedData['assigned_count'];
        if ($enabled) {
            $message = ($count > 0)
                ? "Admin Busy Mode ON: {$count} pending enquiry(s) automatically assigned to available technician(s) in their categories."
                : "Admin Busy Mode ON: Any new incoming enquiries will be automatically assigned to available technicians in real-time.";
        } else {
            $message = "Admin Busy Mode OFF: Enquiries and bookings will be dispatched manually.";
        }

        return Response::json([
            'success'          => true,
            'busy_mode'        => $enabled,
            'assigned_count'   => $count,
            'assigned'         => $assignedData['assigned'],
            'unassigned_count' => $assignedData['unassigned_count'],
            'unassigned'       => $assignedData['unassigned'],
            'message'          => $message,
        ]);
    }

    /**
     * AJAX endpoint to check current Admin Busy Mode status.
     */
    public function getBusyModeStatus(Request $request): Response
    {
        return Response::json([
            'success'   => true,
            'busy_mode' => AutoDispatchService::isBusyModeEnabled(),
        ]);
    }
}

