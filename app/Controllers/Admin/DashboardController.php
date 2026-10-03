<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Report;
use App\Core\Request;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all');
        $summary = Report::getAdminSummary($filter);
        $chartData = Report::getMonthlyRevenueChartData();
        $statusDist = Report::getJobStatusDistribution();

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
            'title'          => 'Admin Operations Dashboard | Primodomus',
            'summary'        => $summary,
            'chartData'      => $chartData,
            'statusDist'     => $statusDist,
            'recentBookings' => $recentBookings,
            'topServices'    => $topServices,
            'topStaff'       => $topStaff,
            'filter'         => $filter,
        ], 'admin');
    }
}
