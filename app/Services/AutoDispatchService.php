<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Notifier;
use App\Models\Booking;
use App\Models\Job;
use App\Models\Setting;

class AutoDispatchService
{
    public const SETTING_KEY = 'admin_busy_auto_assign';

    /**
     * Check if Admin Busy Mode (Auto-Dispatch) is currently active.
     */
    public static function isBusyModeEnabled(): bool
    {
        return Setting::get(self::SETTING_KEY, '0') === '1';
    }

    /**
     * Enable or disable Admin Busy Mode.
     */
    public static function setBusyMode(bool $enabled): void
    {
        Setting::set(self::SETTING_KEY, $enabled ? '1' : '0');
        Logger::info("Admin Busy Mode set to: " . ($enabled ? 'ENABLED' : 'DISABLED'));
    }

    /**
     * Find the best available ('khali') technician for the given category.
     * 
     * Selection criteria:
     * 1. Role is 'staff' and status is 'active'
     * 2. Marked available in staff_profiles (is_available = 1)
     * 3. Has the skill for the specified category (staff_skills.category_id = :cat_id)
     * 4. Lowest count of active jobs ('assigned', 'accepted', 'in_progress')
     * 5. Tie-breakers: Highest rating_avg, then oldest registered
     */
    public static function findAvailableTechnicianForCategory(int $categoryId): ?array
    {
        $sql = "SELECT u.id, u.name, u.email, u.phone,
                       COALESCE(sp.is_available, 1) as is_available,
                       COALESCE(sp.rating_avg, 5.0) as rating_avg,
                       COUNT(DISTINCT CASE WHEN j.status IN ('assigned', 'accepted', 'in_progress') THEN j.id ELSE NULL END) as active_jobs
                FROM users u
                JOIN staff_skills ss ON u.id = ss.staff_id
                LEFT JOIN staff_profiles sp ON u.id = sp.user_id
                LEFT JOIN jobs j ON u.id = j.staff_id
                WHERE u.role = 'staff'
                  AND u.status = 'active'
                  AND ss.category_id = :category_id
                  AND COALESCE(sp.is_available, 1) = 1
                GROUP BY u.id
                ORDER BY active_jobs ASC, rating_avg DESC, u.id ASC
                LIMIT 1";

        $tech = Database::fetchOne($sql, ['category_id' => $categoryId]);

        // Fallback: If no technician has the specific category skill registered,
        // find any active and available technician with the lowest active jobs
        if (!$tech) {
            $fallbackSql = "SELECT u.id, u.name, u.email, u.phone,
                                   COALESCE(sp.is_available, 1) as is_available,
                                   COALESCE(sp.rating_avg, 5.0) as rating_avg,
                                   COUNT(DISTINCT CASE WHEN j.status IN ('assigned', 'accepted', 'in_progress') THEN j.id ELSE NULL END) as active_jobs
                            FROM users u
                            LEFT JOIN staff_profiles sp ON u.id = sp.user_id
                            LEFT JOIN jobs j ON u.id = j.staff_id
                            WHERE u.role = 'staff'
                              AND u.status = 'active'
                              AND COALESCE(sp.is_available, 1) = 1
                            GROUP BY u.id
                            ORDER BY active_jobs ASC, rating_avg DESC, u.id ASC
                            LIMIT 1";
            $tech = Database::fetchOne($fallbackSql);
        }

        return $tech ?: null;
    }

    /**
     * Assign a booking/enquiry to a technician, create/update job, and send notifications.
     */
    public static function assignBooking(int $bookingId, array $technician, ?int $changedByUserId = null): bool
    {
        $booking = Booking::find($bookingId);
        if (!$booking || $booking['status'] !== 'new') {
            return false;
        }

        $staffId = (int)$technician['id'];
        $staffName = $technician['name'] ?? 'Technician #' . $staffId;
        $scheduledAt = date('Y-m-d H:i:s');

        // Check if job already exists
        $job = Database::fetchOne("SELECT id, status FROM jobs WHERE booking_id = :bid", ['bid' => $bookingId]);

        if ($job) {
            Database::query(
                "UPDATE jobs SET staff_id = :sid, scheduled_at = :sched, status = 'assigned', updated_at = NOW() WHERE id = :jid",
                ['sid' => $staffId, 'sched' => $scheduledAt, 'jid' => $job['id']]
            );
            $jobId = (int)$job['id'];
        } else {
            Job::create([
                'booking_id'   => $bookingId,
                'staff_id'     => $staffId,
                'status'       => 'assigned',
                'scheduled_at' => $scheduledAt,
            ]);
            $jobId = (int)Database::lastInsertId();
        }

        // Record in status_history
        try {
            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:jid, 'new', 'assigned', :uid, :notes, NOW())",
                [
                    'jid'   => $jobId,
                    'uid'   => $changedByUserId ?: 1,
                    'notes' => "Auto-assigned to technician {$staffName} (Admin Busy Mode active)",
                ]
            );
        } catch (\Throwable $e) {
            Logger::warning("AutoDispatch status_history skipped: " . $e->getMessage());
        }

        // Update booking status
        Booking::update($bookingId, ['status' => 'assigned']);

        // Dispatch notifications
        try {
            $bookingWithDetails = Booking::findWithDetails($bookingId);
            $staffUser = Database::fetchOne("SELECT id, name, email, phone FROM users WHERE id = :sid", ['sid' => $staffId]);
            if ($staffUser && $bookingWithDetails) {
                Notifier::notifyStaffAssigned($bookingWithDetails, $staffUser);
                Notifier::notifyTechnicianAssignedCustomer($bookingWithDetails, $staffUser);
            }
        } catch (\Throwable $e) {
            Logger::error("AutoDispatch notification error for booking #{$bookingId}: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Scan and auto-assign all pending enquiries/bookings (status = 'new')
     * to available technicians in their respective categories.
     */
    public static function autoAssignPendingEnquiries(?int $adminUserId = null): array
    {
        $pending = Database::fetchAll(
            "SELECT b.id, b.booking_no, b.service_id, s.category_id, s.name as service_name, c.name as category_name
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             LEFT JOIN categories c ON s.category_id = c.id
             WHERE b.status = 'new'
             ORDER BY b.id ASC"
        );

        $assigned = [];
        $unassigned = [];

        foreach ($pending as $b) {
            $catId = (int)($b['category_id'] ?? 0);
            $tech = self::findAvailableTechnicianForCategory($catId);

            if ($tech) {
                $ok = self::assignBooking((int)$b['id'], $tech, $adminUserId);
                if ($ok) {
                    $assigned[] = [
                        'booking_id'    => (int)$b['id'],
                        'booking_no'    => $b['booking_no'],
                        'service_name'  => $b['service_name'],
                        'category_name' => $b['category_name'] ?? 'General',
                        'technician_id' => (int)$tech['id'],
                        'technician'    => $tech['name'],
                    ];
                    continue;
                }
            }

            $unassigned[] = [
                'booking_id'    => (int)$b['id'],
                'booking_no'    => $b['booking_no'],
                'service_name'  => $b['service_name'],
                'category_name' => $b['category_name'] ?? 'General',
                'reason'        => 'No free technician found in category ' . ($b['category_name'] ?? 'General'),
            ];
        }

        return [
            'assigned_count'   => count($assigned),
            'assigned'         => $assigned,
            'unassigned_count' => count($unassigned),
            'unassigned'       => $unassigned,
        ];
    }

    /**
     * Check if Admin Busy Mode is on; if so, immediately auto-assign a freshly created booking/enquiry.
     */
    public static function handleNewBooking(int $bookingId): bool
    {
        if (!self::isBusyModeEnabled()) {
            return false;
        }

        $b = Database::fetchOne(
            "SELECT b.id, b.booking_no, b.service_id, s.category_id, s.name as service_name, c.name as category_name
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             LEFT JOIN categories c ON s.category_id = c.id
             WHERE b.id = :id AND b.status = 'new'",
            ['id' => $bookingId]
        );

        if (!$b) {
            return false;
        }

        $catId = (int)($b['category_id'] ?? 0);
        $tech = self::findAvailableTechnicianForCategory($catId);

        if ($tech) {
            $assigned = self::assignBooking((int)$b['id'], $tech);
            if ($assigned) {
                Logger::info("AutoDispatch: Booking #{$b['booking_no']} auto-assigned to technician {$tech['name']} (Category: {$b['category_name']})");
                return true;
            }
        } else {
            Logger::warning("AutoDispatch: No free technician available in category '{$b['category_name']}' for booking #{$b['booking_no']}");
        }

        return false;
    }
}
