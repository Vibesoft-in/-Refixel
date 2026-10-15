<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class StaffEarning extends Model
{
    protected static string $table = 'staff_earnings';

    public static function normalizeEarningBreakdown(array &$item): void
    {
        $gross = (float)($item['gross_amount'] ?? 0);
        if ($gross <= 0) {
            $gross = (float)(($item['booking_total'] ?? 0) > 0 ? $item['booking_total'] : ($item['starting_price'] ?? 0));
            if ($gross <= 0) {
                $gross = (float)($item['amount'] ?? 0);
            }
            $item['gross_amount'] = $gross;
        }

        $adminFee = (float)($item['admin_fee'] ?? 0);
        $amount = (float)($item['amount'] ?? 0);
        $materialCost = (float)($item['material_cost'] ?? 0);

        // If the record was saved with the old flat 49 fee or zero fee or amount >= gross,
        // compute the actual rule-based company vs technician split
        if ($adminFee == 49.00 || $adminFee <= 0 || ($gross > 0 && $amount >= $gross)) {
            $split = CommissionRule::calculateSplit(
                $gross,
                $materialCost,
                (int)($item['service_id'] ?? 0),
                (int)($item['category_id'] ?? 0)
            );
            $amount   = (float)$split['worker_total_payout'];
            $adminFee = (float)$split['admin_share'];
            $item['amount']       = $amount;
            $item['admin_fee']    = $adminFee;
            $item['gross_amount'] = $gross;

            if (!empty($item['id'])) {
                try {
                    Database::query(
                        "UPDATE staff_earnings SET amount = :amt, gross_amount = :gross, admin_fee = :admin WHERE id = :id",
                        ['amt' => $amount, 'gross' => $gross, 'admin' => $adminFee, 'id' => (int)$item['id']]
                    );
                } catch (\Throwable $e) {}
            }
        }

        $adminPct = ($gross > 0) ? round(($adminFee / $gross) * 100, 1) : 25.0;
        $workerPct = ($gross > 0) ? round(($amount / $gross) * 100, 1) : 75.0;

        $item['admin_percent']    = $adminPct;
        $item['worker_percent']   = $workerPct;
        $item['company_split']    = $adminFee;
        $item['technician_share'] = $amount;
    }

    public static function getByStaff(int $staffId): array
    {
        $rows = Database::fetchAll(
            "SELECT se.*, j.id as job_id, b.booking_no, b.total_amount as booking_total,
                    s.name as service_name, s.starting_price, s.id as service_id, s.category_id,
                    c.name as category_name
             FROM staff_earnings se
             JOIN jobs j ON se.job_id = j.id
             JOIN bookings b ON j.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             LEFT JOIN categories c ON s.category_id = c.id
             WHERE se.staff_id = :sid
             ORDER BY se.created_at DESC",
            ['sid' => $staffId]
        );

        foreach ($rows as &$item) {
            self::normalizeEarningBreakdown($item);
        }

        return $rows;
    }

    public static function getSummary(int $staffId): array
    {
        $all = self::getByStaff($staffId);

        $currentMonth = date('Y-m');
        $total = 0.0;
        $thisMonth = 0.0;
        $settled = 0.0;
        $pending = 0.0;

        foreach ($all as $row) {
            $amt = (float)$row['amount'];
            $total += $amt;
            if (($row['month'] ?? '') === $currentMonth) {
                $thisMonth += $amt;
            }
            if (!empty($row['is_settled'])) {
                $settled += $amt;
            } else {
                $pending += $amt;
            }
        }

        return [
            'total_earned' => (float)$total,
            'this_month'   => (float)$thisMonth,
            'settled'      => (float)$settled,
            'pending'      => (float)$pending,
        ];
    }

    public static function recordJobCommission(
        int $jobId, 
        int $staffId, 
        float $amount, 
        ?string $month = null,
        float $grossAmount = 0.0,
        float $materialCost = 0.0,
        float $adminFee = 0.0
    ): void {
        $existing = Database::fetchOne(
            "SELECT id FROM staff_earnings WHERE job_id = :jid AND staff_id = :sid LIMIT 1",
            ['jid' => $jobId, 'sid' => $staffId]
        );

        if ($existing) {
            return; // Already recorded
        }

        $month = $month ?? date('Y-m');

        Database::query(
            "INSERT INTO staff_earnings (job_id, staff_id, amount, gross_amount, material_cost, admin_fee, month, is_settled, created_at)
             VALUES (:job_id, :staff_id, :amount, :gross_amount, :material_cost, :admin_fee, :month, 0, NOW())",
            [
                'job_id'        => $jobId,
                'staff_id'      => $staffId,
                'amount'        => $amount,
                'gross_amount'  => $grossAmount,
                'material_cost' => $materialCost,
                'admin_fee'     => $adminFee,
                'month'         => $month,
            ]
        );
    }
}
