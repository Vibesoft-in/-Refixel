<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Workflow
{
    public const STATUS_NEW              = 'new';
    public const STATUS_ASSIGNED         = 'assigned';
    public const STATUS_ACCEPTED         = 'accepted';
    public const STATUS_IN_PROGRESS      = 'in_progress';
    public const STATUS_COMPLETED        = 'completed';
    public const STATUS_INVOICED         = 'invoiced';
    public const STATUS_REVIEWED         = 'reviewed';
    public const STATUS_CLOSED           = 'closed';
    public const STATUS_CANCELLED        = 'cancelled';
    public const STATUS_RESCHEDULED      = 'rescheduled';
    public const STATUS_REFUND_REQUESTED = 'refund_requested';
    public const STATUS_REFUNDED         = 'refunded';

    /**
     * Allowed forward status transitions.
     */
    protected const ALLOWED_TRANSITIONS = [
        self::STATUS_NEW => [
            self::STATUS_ASSIGNED,
            self::STATUS_RESCHEDULED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_RESCHEDULED => [
            self::STATUS_ASSIGNED,
            self::STATUS_RESCHEDULED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_ASSIGNED => [
            self::STATUS_ACCEPTED,
            self::STATUS_ASSIGNED, // reassignment
            self::STATUS_RESCHEDULED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_ACCEPTED => [
            self::STATUS_IN_PROGRESS,
            self::STATUS_ASSIGNED, // technician declined / reassigned
            self::STATUS_RESCHEDULED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_IN_PROGRESS => [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED, // emergency cancel
        ],
        self::STATUS_COMPLETED => [
            self::STATUS_INVOICED,
            self::STATUS_REVIEWED,
            self::STATUS_CLOSED,
        ],
        self::STATUS_INVOICED => [
            self::STATUS_REVIEWED,
            self::STATUS_CLOSED,
        ],
        self::STATUS_REVIEWED => [
            self::STATUS_CLOSED,
        ],
        self::STATUS_CANCELLED => [
            self::STATUS_REFUND_REQUESTED,
            self::STATUS_REFUNDED,
            self::STATUS_CLOSED,
        ],
        self::STATUS_REFUND_REQUESTED => [
            self::STATUS_REFUNDED,
            self::STATUS_CLOSED,
        ],
        self::STATUS_REFUNDED => [
            self::STATUS_CLOSED,
        ],
        self::STATUS_CLOSED => [],
    ];

    public static function canTransition(string $currentStatus, string $newStatus, string $role): bool
    {
        if (!isset(self::ALLOWED_TRANSITIONS[$currentStatus])) {
            return false;
        }

        if (!in_array($newStatus, self::ALLOWED_TRANSITIONS[$currentStatus], true)) {
            return false;
        }

        // Role-based authorization rules
        return match ($role) {
            'admin' => true, // Admin can execute all valid state machine transitions
            'staff' => match ($newStatus) {
                self::STATUS_ACCEPTED,
                self::STATUS_IN_PROGRESS,
                self::STATUS_COMPLETED => true,
                default => false,
            },
            'customer' => match ($newStatus) {
                self::STATUS_CANCELLED => in_array($currentStatus, [self::STATUS_NEW, self::STATUS_ASSIGNED, self::STATUS_RESCHEDULED], true),
                self::STATUS_RESCHEDULED => in_array($currentStatus, [self::STATUS_NEW, self::STATUS_ASSIGNED, self::STATUS_RESCHEDULED], true),
                self::STATUS_REFUND_REQUESTED => in_array($currentStatus, [self::STATUS_CANCELLED], true),
                self::STATUS_REVIEWED => in_array($currentStatus, [self::STATUS_COMPLETED, self::STATUS_INVOICED], true),
                default => false,
            },
            default => false,
        };
    }

    public static function transition(
        int $jobId,
        string $newStatus,
        int $changedByUserId,
        string $role,
        ?string $notes = null
    ): void {
        $job = Database::fetchOne("SELECT id, status, booking_id FROM jobs WHERE id = :id", ['id' => $jobId]);
        if (!$job) {
            throw new RuntimeException("Job #{$jobId} not found.");
        }

        $currentStatus = (string)$job['status'];

        if (!self::canTransition($currentStatus, $newStatus, $role)) {
            throw new RuntimeException("Invalid status transition from '{$currentStatus}' to '{$newStatus}' for role '{$role}'.");
        }

        Database::beginTransaction();
        try {
            // Build dynamic jobs update based on new status
            $extraSet = "";
            $extraParams = [];

            if ($newStatus === self::STATUS_ACCEPTED) {
                $extraSet = ", accepted_at = IFNULL(accepted_at, NOW())";
            } elseif ($newStatus === self::STATUS_IN_PROGRESS) {
                $extraSet = ", started_at = IFNULL(started_at, NOW())";
            } elseif ($newStatus === self::STATUS_COMPLETED) {
                $extraSet = ", completed_at = IFNULL(completed_at, NOW())";
            }

            Database::query(
                "UPDATE jobs SET status = :status, updated_at = NOW(){$extraSet} WHERE id = :id",
                array_merge(['status' => in_array($newStatus, ['assigned', 'accepted', 'in_progress', 'completed'], true) ? $newStatus : $job['status'], 'id' => $jobId], $extraParams)
            );

            // Sync booking status with job status
            if (!empty($job['booking_id'])) {
                Database::query(
                    "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :bid",
                    ['status' => $newStatus, 'bid' => $job['booking_id']]
                );
            }

            // Record in status_history
            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:job_id, :from_status, :to_status, :changed_by, :notes, NOW())",
                [
                    'job_id'      => $jobId,
                    'from_status' => $currentStatus,
                    'to_status'   => $newStatus,
                    'changed_by'  => $changedByUserId,
                    'notes'       => $notes,
                ]
            );

            Database::commit();

            // Dispatch lifecycle notifications
            try {
                if (!empty($job['booking_id'])) {
                    $bDetails = \App\Models\Booking::findWithDetails((int)$job['booking_id']);
                    if ($bDetails) {
                        if ($newStatus === self::STATUS_COMPLETED) {
                            $sDetails = !empty($bDetails['staff_name']) ? ['name' => $bDetails['staff_name']] : null;
                            Notifier::notifyJobCompleted($bDetails, $sDetails);
                        } else {
                            Notifier::notifyBookingStatusChange($bDetails, $newStatus, $notes);
                        }
                    }
                }
            } catch (\Throwable $ne) {
                Logger::error("Job transition notification skipped: " . $ne->getMessage());
            }
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function transitionBooking(
        int $bookingId,
        string $newStatus,
        int $changedByUserId,
        string $role,
        ?string $notes = null
    ): void {
        $booking = Database::fetchOne("SELECT id, status FROM bookings WHERE id = :id", ['id' => $bookingId]);
        if (!$booking) {
            throw new RuntimeException("Booking #{$bookingId} not found.");
        }

        $currentStatus = (string)$booking['status'];

        if (!self::canTransition($currentStatus, $newStatus, $role)) {
            throw new RuntimeException("Invalid status transition from '{$currentStatus}' to '{$newStatus}' for role '{$role}'.");
        }

        Database::beginTransaction();
        try {
            Database::query(
                "UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id",
                ['status' => $newStatus, 'id' => $bookingId]
            );

            $job = Database::fetchOne("SELECT id, status FROM jobs WHERE booking_id = :bid", ['bid' => $bookingId]);
            if ($job) {
                if (in_array($newStatus, ['assigned', 'accepted', 'in_progress', 'completed'], true)) {
                    Database::query("UPDATE jobs SET status = :st, updated_at = NOW() WHERE id = :jid", ['st' => $newStatus, 'jid' => $job['id']]);
                }

                Database::query(
                    "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                     VALUES (:job_id, :from_status, :to_status, :changed_by, :notes, NOW())",
                    [
                        'job_id'      => $job['id'],
                        'from_status' => $currentStatus,
                        'to_status'   => $newStatus,
                        'changed_by'  => $changedByUserId,
                        'notes'       => $notes,
                    ]
                );
            }

            Database::commit();

            // Dispatch lifecycle notifications
            try {
                $bDetails = \App\Models\Booking::findWithDetails($bookingId);
                if ($bDetails) {
                    if ($newStatus === self::STATUS_COMPLETED) {
                        $sDetails = !empty($bDetails['staff_name']) ? ['name' => $bDetails['staff_name']] : null;
                        Notifier::notifyJobCompleted($bDetails, $sDetails);
                    } else {
                        Notifier::notifyBookingStatusChange($bDetails, $newStatus, $notes);
                    }
                }
            } catch (\Throwable $ne) {
                Logger::error("Booking transition notification skipped: " . $ne->getMessage());
            }
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public static function recordSubAction(
        int $jobId,
        string $subAction,
        int $changedByUserId,
        ?string $notes = null
    ): void {
        $job = Database::fetchOne("SELECT id, status FROM jobs WHERE id = :id", ['id' => $jobId]);
        if (!$job) {
            throw new RuntimeException("Job #{$jobId} not found.");
        }

        Database::query(
            "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
             VALUES (:job_id, :from_status, :to_status, :changed_by, :notes, NOW())",
            [
                'job_id'      => $jobId,
                'from_status' => $job['status'],
                'to_status'   => $subAction,
                'changed_by'  => $changedByUserId,
                'notes'       => $notes,
            ]
        );
    }
}
