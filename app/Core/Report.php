<?php
declare(strict_types=1);

namespace App\Core;

class Report
{
    public static function getAdminSummary(?string $dateFilter = null): array
    {
        $dateCondition = "";
        $params = [];

        if ($dateFilter === 'today') {
            $dateCondition = " AND DATE(created_at) = CURDATE()";
        } elseif ($dateFilter === 'month') {
            $dateCondition = " AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        }

        $enquiriesCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new'{$dateCondition}")['c'] ?? 0);
        $assignedJobs = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM jobs WHERE status = 'assigned'{$dateCondition}")['c'] ?? 0);
        $activeJobs = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM jobs WHERE status IN ('assigned', 'accepted', 'in_progress'){$dateCondition}")['c'] ?? 0);
        $completedJobs = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM jobs WHERE status = 'completed'{$dateCondition}")['c'] ?? 0);
        
        $payDateCond = str_replace('created_at', 'paid_at', $dateCondition);
        $totalRevenue = (float)(Database::fetchOne("SELECT IFNULL(SUM(amount), 0) as s FROM payments WHERE status = 'paid'{$payDateCond}")['s'] ?? 0.0);
        $pendingPayments = (float)(Database::fetchOne("SELECT IFNULL(SUM(amount), 0) as s FROM payments WHERE status = 'pending'")['s'] ?? 0.0);
        
        $staffCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'staff' AND status = 'active'")['c'] ?? 0);
        $servicesCount = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM services WHERE is_active = 1")['c'] ?? 0);

        return [
            'enquiries_count'  => $enquiriesCount,
            'assigned_jobs'    => $assignedJobs,
            'active_jobs'      => $activeJobs,
            'completed_jobs'   => $completedJobs,
            'total_revenue'    => $totalRevenue,
            'pending_payments' => $pendingPayments,
            'staff_count'      => $staffCount,
            'services_count'   => $servicesCount,
        ];
    }

    public static function getMonthlyRevenueChartData(): array
    {
        $rows = Database::fetchAll(
            "SELECT DATE_FORMAT(paid_at, '%b %Y') as month, SUM(amount) as total
             FROM payments
             WHERE status = 'paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
             ORDER BY DATE_FORMAT(paid_at, '%Y-%m') ASC"
        );

        if (empty($rows)) {
            // Provide sensible fallback series if no historic payments exist yet
            return [
                'labels' => [date('M Y', strtotime('-2 months')), date('M Y', strtotime('-1 month')), date('M Y')],
                'data'   => [0, 0, 0],
            ];
        }

        $labels = [];
        $data = [];
        foreach ($rows as $r) {
            $labels[] = $r['month'];
            $data[] = (float)$r['total'];
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public static function getJobStatusDistribution(): array
    {
        $rows = Database::fetchAll("SELECT status, COUNT(*) as cnt FROM jobs GROUP BY status");
        $dist = [
            'assigned'    => 0,
            'accepted'    => 0,
            'in_progress' => 0,
            'completed'   => 0,
        ];

        foreach ($rows as $r) {
            $st = (string)$r['status'];
            if (isset($dist[$st])) {
                $dist[$st] = (int)$r['cnt'];
            }
        }

        return $dist;
    }

    public static function getTopServices(int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT s.id, s.name, c.name as category_name, COUNT(b.id) as booking_count, s.starting_price
             FROM services s
             JOIN categories c ON s.category_id = c.id
             LEFT JOIN bookings b ON s.id = b.service_id
             GROUP BY s.id
             ORDER BY booking_count DESC, s.name ASC
             LIMIT :lim",
            ['lim' => $limit]
        );
    }

    public static function getStaffLeaderboard(int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT u.id, u.name, u.phone, sp.rating_avg, sp.rating_count,
                    COUNT(j.id) as total_jobs,
                    SUM(CASE WHEN j.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs
             FROM users u
             LEFT JOIN staff_profiles sp ON u.id = sp.user_id
             LEFT JOIN jobs j ON u.id = j.staff_id
             WHERE u.role = 'staff' AND u.status = 'active'
             GROUP BY u.id
             ORDER BY completed_jobs DESC, sp.rating_avg DESC
             LIMIT :lim",
            ['lim' => $limit]
        );
    }
}
