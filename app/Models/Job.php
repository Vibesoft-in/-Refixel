<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Job extends Model
{
    protected static string $table = 'jobs';

    public static function findByStaff(int $staffId, ?string $filter = null): array
    {
        $sql = "SELECT j.*, b.booking_no, b.address, b.pincode, b.preferred_date, b.preferred_time,
                       b.issue_details, b.name as customer_name, b.phone as customer_phone,
                       s.name as service_name, s.starting_price
                FROM jobs j
                JOIN bookings b ON j.booking_id = b.id
                JOIN services s ON b.service_id = s.id
                WHERE j.staff_id = :staff_id";

        $params = ['staff_id' => $staffId];

        if ($filter === 'today') {
            $sql .= " AND (b.preferred_date = CURDATE() OR DATE(j.scheduled_at) = CURDATE() OR (j.status IN ('assigned','accepted','in_progress') AND (b.preferred_date <= CURDATE() OR b.preferred_date IS NULL)))";
        } elseif ($filter === 'upcoming') {
            $sql .= " AND (b.preferred_date > CURDATE() OR DATE(j.scheduled_at) > CURDATE()) AND j.status != 'completed'";
        } elseif ($filter === 'in_progress') {
            $sql .= " AND j.status = 'in_progress'";
        } elseif ($filter === 'completed') {
            $sql .= " AND j.status = 'completed'";
        }

        $sql .= " ORDER BY j.scheduled_at DESC, j.id DESC";

        return Database::fetchAll($sql, $params);
    }

    public static function findWithDetailsForStaff(int $jobId, int $staffId): ?array
    {
        return Database::fetchOne(
            "SELECT j.*, b.booking_no, b.address, b.pincode, b.preferred_date, b.preferred_time,
                    b.issue_details, b.name as customer_name, b.phone as customer_phone, b.email as customer_email,
                    s.name as service_name, s.starting_price, s.description as service_description
             FROM jobs j
             JOIN bookings b ON j.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE j.id = :jid AND j.staff_id = :sid
             LIMIT 1",
            ['jid' => $jobId, 'sid' => $staffId]
        );
    }

    public static function getCustomerAttachments(int $bookingId): array
    {
        return Database::fetchAll(
            "SELECT * FROM booking_attachments WHERE booking_id = :bid ORDER BY id ASC",
            ['bid' => $bookingId]
        );
    }

    public static function getStaffDashboardStats(int $staffId): array
    {
        $today = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM jobs j 
             JOIN bookings b ON j.booking_id = b.id
             WHERE j.staff_id = :sid 
               AND (b.preferred_date = CURDATE() OR DATE(j.scheduled_at) = CURDATE() OR (j.status IN ('assigned','accepted','in_progress') AND (b.preferred_date <= CURDATE() OR b.preferred_date IS NULL)))",
            ['sid' => $staffId]
        )['cnt'] ?? 0;

        $inProgress = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM jobs WHERE staff_id = :sid AND status = 'in_progress'",
            ['sid' => $staffId]
        )['cnt'] ?? 0;

        $completed = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM jobs WHERE staff_id = :sid AND status = 'completed'",
            ['sid' => $staffId]
        )['cnt'] ?? 0;

        $total = Database::fetchOne(
            "SELECT COUNT(*) as cnt FROM jobs WHERE staff_id = :sid",
            ['sid' => $staffId]
        )['cnt'] ?? 0;

        return [
            'today'       => (int)$today,
            'in_progress' => (int)$inProgress,
            'completed'   => (int)$completed,
            'total'       => (int)$total,
        ];
    }

    public static function saveCompletion(int $jobId, int $staffId, string $workSummary, ?string $finalNotes = null): void
    {
        Database::query(
            "UPDATE jobs SET work_summary = :work_summary, final_notes = :final_notes, completed_at = NOW(), updated_at = NOW() 
             WHERE id = :id AND staff_id = :sid",
            [
                'work_summary' => $workSummary,
                'final_notes'  => $finalNotes,
                'id'           => $jobId,
                'sid'          => $staffId,
            ]
        );
    }
}
