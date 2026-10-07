<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class StaffEarning extends Model
{
    protected static string $table = 'staff_earnings';

    public static function getByStaff(int $staffId): array
    {
        return Database::fetchAll(
            "SELECT se.*, j.id as job_id, b.booking_no, s.name as service_name
             FROM staff_earnings se
             JOIN jobs j ON se.job_id = j.id
             JOIN bookings b ON j.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE se.staff_id = :sid
             ORDER BY se.created_at DESC",
            ['sid' => $staffId]
        );
    }

    public static function getSummary(int $staffId): array
    {
        $currentMonth = date('Y-m');
        $total = Database::fetchOne(
            "SELECT IFNULL(SUM(amount), 0) as amt FROM staff_earnings WHERE staff_id = :sid",
            ['sid' => $staffId]
        )['amt'] ?? 0;

        $thisMonth = Database::fetchOne(
            "SELECT IFNULL(SUM(amount), 0) as amt FROM staff_earnings WHERE staff_id = :sid AND month = :m",
            ['sid' => $staffId, 'm' => $currentMonth]
        )['amt'] ?? 0;

        $settled = Database::fetchOne(
            "SELECT IFNULL(SUM(amount), 0) as amt FROM staff_earnings WHERE staff_id = :sid AND is_settled = 1",
            ['sid' => $staffId]
        )['amt'] ?? 0;

        $pending = Database::fetchOne(
            "SELECT IFNULL(SUM(amount), 0) as amt FROM staff_earnings WHERE staff_id = :sid AND is_settled = 0",
            ['sid' => $staffId]
        )['amt'] ?? 0;

        return [
            'total_earned' => (float)$total,
            'this_month'   => (float)$thisMonth,
            'settled'      => (float)$settled,
            'pending'      => (float)$pending,
        ];
    }

    public static function recordJobCommission(int $jobId, int $staffId, float $amount, ?string $month = null): void
    {
        $existing = Database::fetchOne(
            "SELECT id FROM staff_earnings WHERE job_id = :jid AND staff_id = :sid LIMIT 1",
            ['jid' => $jobId, 'sid' => $staffId]
        );

        if ($existing) {
            return; // Already recorded
        }

        $month = $month ?? date('Y-m');

        Database::query(
            "INSERT INTO staff_earnings (job_id, staff_id, amount, month, is_settled, created_at)
             VALUES (:job_id, :staff_id, :amount, :month, 0, NOW())",
            [
                'job_id'   => $jobId,
                'staff_id' => $staffId,
                'amount'   => $amount,
                'month'    => $month,
            ]
        );
    }
}
