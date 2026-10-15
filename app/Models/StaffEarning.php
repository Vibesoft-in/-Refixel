<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class StaffEarning extends Model
{
    protected static string $table = 'staff_earnings';

    public static function ensureSettlementSchema(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $cols = Database::fetchAll("SHOW COLUMNS FROM staff_earnings");
            $colNames = array_column($cols, 'Field');
            if (!in_array('settled_method', $colNames, true)) {
                Database::query("ALTER TABLE staff_earnings ADD COLUMN settled_method VARCHAR(50) NULL AFTER is_settled");
            }
            if (!in_array('settled_ref', $colNames, true)) {
                Database::query("ALTER TABLE staff_earnings ADD COLUMN settled_ref VARCHAR(100) NULL AFTER settled_method");
            }
            if (!in_array('settled_notes', $colNames, true)) {
                Database::query("ALTER TABLE staff_earnings ADD COLUMN settled_notes VARCHAR(255) NULL AFTER settled_ref");
            }
        } catch (\Throwable $e) {}
    }

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
        self::ensureSettlementSchema();
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

    /**
     * Fetch all payout records with technician and booking details for Admin settlement section
     */
    public static function getAllWithDetails(?string $status = null, ?int $staffId = null, ?string $month = null, ?string $search = null): array
    {
        self::ensureSettlementSchema();
        $sql = "
            SELECT se.*, 
                   u.name as staff_name, u.phone as staff_phone, u.email as staff_email,
                   j.id as job_id, j.status as job_status, j.completed_at as job_completed_at,
                   b.booking_no, b.name as customer_name, b.phone as customer_phone, b.total_amount as booking_total,
                   s.name as service_name, s.starting_price, s.id as service_id, s.category_id,
                   c.name as category_name
            FROM staff_earnings se
            JOIN users u ON se.staff_id = u.id
            JOIN jobs j ON se.job_id = j.id
            JOIN bookings b ON j.booking_id = b.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN categories c ON s.category_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if ($status === 'pending') {
            $sql .= " AND se.is_settled = 0";
        } elseif ($status === 'settled') {
            $sql .= " AND se.is_settled = 1";
        }

        if ($staffId && $staffId > 0) {
            $sql .= " AND se.staff_id = :sid";
            $params['sid'] = $staffId;
        }

        if (!empty($month)) {
            $sql .= " AND se.month = :month";
            $params['month'] = $month;
        }

        if (!empty($search)) {
            $sql .= " AND (b.booking_no LIKE :q OR u.name LIKE :q OR u.phone LIKE :q OR s.name LIKE :q OR se.settled_ref LIKE :q)";
            $params['q'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY se.is_settled ASC, se.id DESC";

        $rows = Database::fetchAll($sql, $params);
        foreach ($rows as &$item) {
            self::normalizeEarningBreakdown($item);
        }
        return $rows;
    }

    /**
     * Compute comprehensive admin payout stats and aggregate balances per technician
     */
    public static function getAdminPayoutStats(): array
    {
        self::ensureSettlementSchema();
        $all = self::getAllWithDetails('all');

        $totalPending = 0.0;
        $pendingCount = 0;
        $totalSettled = 0.0;
        $settledCount = 0;
        $totalGross = 0.0;
        $totalCompanySplit = 0.0;

        $techPendingMap = [];

        foreach ($all as $item) {
            $amt = (float)($item['amount'] ?? 0);
            $gross = (float)($item['gross_amount'] ?? 0);
            $adminFee = (float)($item['admin_fee'] ?? 0);

            $totalGross += $gross;
            $totalCompanySplit += $adminFee;

            if (empty($item['is_settled'])) {
                $totalPending += $amt;
                $pendingCount++;

                $sId = (int)$item['staff_id'];
                if (!isset($techPendingMap[$sId])) {
                    $techPendingMap[$sId] = [
                        'staff_id'     => $sId,
                        'name'         => $item['staff_name'] ?? 'Technician #' . $sId,
                        'phone'        => $item['staff_phone'] ?? '',
                        'email'        => $item['staff_email'] ?? '',
                        'pending_sum'  => 0.0,
                        'pending_jobs' => 0,
                    ];
                }
                $techPendingMap[$sId]['pending_sum'] += $amt;
                $techPendingMap[$sId]['pending_jobs']++;
            } else {
                $totalSettled += $amt;
                $settledCount++;
            }
        }

        // Sort technicians by pending amount descending
        usort($techPendingMap, fn($a, $b) => $b['pending_sum'] <=> $a['pending_sum']);

        return [
            'total_pending_amount'    => round($totalPending, 2),
            'pending_count'           => $pendingCount,
            'total_settled_amount'    => round($totalSettled, 2),
            'settled_count'           => $settledCount,
            'total_gross'             => round($totalGross, 2),
            'total_company_split'     => round($totalCompanySplit, 2),
            'technicians_with_pending'=> array_values($techPendingMap),
        ];
    }

    /**
     * Settle a single technician earning record with payment method and reference
     */
    public static function settle(int $earningId, string $method = 'upi', ?string $ref = null, ?string $notes = null): bool
    {
        self::ensureSettlementSchema();
        $record = Database::fetchOne("SELECT * FROM staff_earnings WHERE id = :id LIMIT 1", ['id' => $earningId]);
        if (!$record) {
            return false;
        }

        Database::query(
            "UPDATE staff_earnings 
             SET is_settled = 1, settled_at = NOW(), settled_method = :method, settled_ref = :ref, settled_notes = :notes 
             WHERE id = :id",
            [
                'id'     => $earningId,
                'method' => trim($method) ?: 'upi',
                'ref'    => trim((string)$ref) ?: null,
                'notes'  => trim((string)$notes) ?: null,
            ]
        );
        return true;
    }

    /**
     * Settle all pending earnings for a specific technician in one go
     */
    public static function settleAllForTechnician(int $staffId, string $method = 'upi', ?string $ref = null, ?string $notes = null): int
    {
        self::ensureSettlementSchema();
        $pending = Database::fetchAll(
            "SELECT id FROM staff_earnings WHERE staff_id = :sid AND is_settled = 0",
            ['sid' => $staffId]
        );

        if (empty($pending)) {
            return 0;
        }

        Database::query(
            "UPDATE staff_earnings 
             SET is_settled = 1, settled_at = NOW(), settled_method = :method, settled_ref = :ref, settled_notes = :notes 
             WHERE staff_id = :sid AND is_settled = 0",
            [
                'sid'    => $staffId,
                'method' => trim($method) ?: 'upi',
                'ref'    => trim((string)$ref) ?: null,
                'notes'  => trim((string)$notes) ?: null,
            ]
        );

        return count($pending);
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
        self::ensureSettlementSchema();
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
