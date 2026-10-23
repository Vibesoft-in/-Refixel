<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class AbandonedCheckout extends Model
{
    protected static string $table = 'abandoned_checkouts';

    /**
     * Record a new drop-off or update an existing one by booking_id/phone
     */
    public static function record(array $data): int
    {
        $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : null;
        $phone     = !empty($data['phone']) ? preg_replace('/\D/', '', (string)$data['phone']) : null;

        if ($bookingId) {
            $existing = Database::fetchOne("SELECT id FROM abandoned_checkouts WHERE booking_id = :bid LIMIT 1", ['bid' => $bookingId]);
            if ($existing) {
                Database::query(
                    "UPDATE abandoned_checkouts SET step = :step, notes = :notes, updated_at = NOW() WHERE id = :id",
                    [
                        'step'  => $data['step'] ?? 'payment_dropped',
                        'notes' => $data['notes'] ?? null,
                        'id'    => (int)$existing['id'],
                    ]
                );
                return (int)$existing['id'];
            }
        }

        return (int)self::create([
            'booking_id'   => $bookingId,
            'customer_id'  => !empty($data['customer_id']) ? (int)$data['customer_id'] : null,
            'service_id'   => !empty($data['service_id']) ? (int)$data['service_id'] : null,
            'name'         => $data['name'] ?? 'Unknown Customer',
            'phone'        => $phone,
            'email'        => $data['email'] ?? null,
            'city'         => $data['city'] ?? null,
            'address'      => $data['address'] ?? null,
            'service_name' => $data['service_name'] ?? null,
            'amount'       => (float)($data['amount'] ?? 0.00),
            'step'         => $data['step'] ?? 'payment_dropped',
            'notes'        => $data['notes'] ?? null,
            'is_recovered' => 0,
        ]);
    }

    /**
     * Return all dropped/abandoned checkouts combined with unconfirmed online bookings
     */
    public static function getDropoffs(string $filter = 'all', string $search = ''): array
    {
        // 1. Fetch from abandoned_checkouts table
        $sql = "SELECT ac.id, ac.booking_id, ac.name, ac.phone, ac.email, ac.city, ac.address,
                       ac.service_name, ac.amount, ac.step, ac.notes, ac.is_recovered, ac.recovered_at, ac.created_at,
                       b.booking_no, b.status as booking_status, b.gateway_order_id
                FROM abandoned_checkouts ac
                LEFT JOIN bookings b ON ac.booking_id = b.id
                WHERE 1=1";
        $params = [];

        if ($filter === 'recovered') {
            $sql .= " AND ac.is_recovered = 1";
        } elseif ($filter === 'unrecovered') {
            $sql .= " AND ac.is_recovered = 0";
        } elseif ($filter === 'payment_failed') {
            $sql .= " AND (ac.step LIKE '%fail%' OR ac.step LIKE '%error%')";
        }

        if (!empty($search)) {
            $sql .= " AND (ac.name LIKE :q OR ac.phone LIKE :q OR ac.email LIKE :q OR ac.service_name LIKE :q)";
            $params['q'] = "%{$search}%";
        }

        $sql .= " ORDER BY ac.id DESC";
        $records = Database::fetchAll($sql, $params);

        // Also check if any booking with online payment has been pending for over 10 minutes and not yet in abandoned_checkouts
        try {
            $onlineOrphans = Database::fetchAll("
                SELECT b.id as booking_id, b.booking_no, b.name, b.phone, b.email, b.address,
                       b.total_amount as amount, b.status as booking_status, b.gateway_order_id, b.created_at,
                       s.name as service_name
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'paid'
                WHERE (b.payment_type = 'online' OR b.gateway_order_id IS NOT NULL)
                  AND b.status = 'new'
                  AND p.id IS NULL
                  AND b.id NOT IN (SELECT COALESCE(booking_id, 0) FROM abandoned_checkouts)
                ORDER BY b.id DESC
            ");

            foreach ($onlineOrphans as $orp) {
                // Auto-register orphan online bookings into abandoned_checkouts so they stay tracked
                $newId = self::record([
                    'booking_id'   => (int)$orp['booking_id'],
                    'name'         => $orp['name'],
                    'phone'        => $orp['phone'],
                    'email'        => $orp['email'],
                    'address'      => $orp['address'],
                    'service_name' => $orp['service_name'],
                    'amount'       => (float)$orp['amount'],
                    'step'         => 'payment_modal_dismissed',
                    'notes'        => 'Online payment initiated via Razorpay (Order: ' . ($orp['gateway_order_id'] ?? 'N/A') . ') but payment was never completed.',
                ]);

                if ($filter === 'all' || $filter === 'unrecovered') {
                    $records[] = [
                        'id'               => $newId,
                        'booking_id'       => (int)$orp['booking_id'],
                        'booking_no'       => $orp['booking_no'],
                        'name'             => $orp['name'],
                        'phone'            => $orp['phone'],
                        'email'            => $orp['email'],
                        'city'             => 'Kashipur',
                        'address'          => $orp['address'],
                        'service_name'     => $orp['service_name'],
                        'amount'           => (float)$orp['amount'],
                        'step'             => 'payment_modal_dismissed',
                        'notes'            => 'Online payment initiated but left uncompleted.',
                        'is_recovered'     => 0,
                        'recovered_at'     => null,
                        'created_at'       => $orp['created_at'],
                        'booking_status'   => $orp['booking_status'],
                        'gateway_order_id' => $orp['gateway_order_id'],
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return $records;
    }

    /**
     * Get summary KPI statistics
     */
    public static function getStats(): array
    {
        $totalDropoffs = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM abandoned_checkouts")['c'] ?? 0);
        $totalLostVal  = (float)(Database::fetchOne("SELECT SUM(amount) as s FROM abandoned_checkouts WHERE is_recovered = 0")['s'] ?? 0.0);
        $recoveredCount= (int)(Database::fetchOne("SELECT COUNT(*) as c FROM abandoned_checkouts WHERE is_recovered = 1")['c'] ?? 0);
        $pendingCount  = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM abandoned_checkouts WHERE is_recovered = 0")['c'] ?? 0);
        $failedCount   = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM abandoned_checkouts WHERE step LIKE '%fail%' OR step LIKE '%error%'")['c'] ?? 0);

        return [
            'total'          => $totalDropoffs,
            'pending'        => $pendingCount,
            'recovered'      => $recoveredCount,
            'failed'         => $failedCount,
            'lost_value'     => $totalLostVal,
            'recovery_rate'  => $totalDropoffs > 0 ? round(($recoveredCount / $totalDropoffs) * 100, 1) : 0,
        ];
    }
}
