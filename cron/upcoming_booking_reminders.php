<?php
declare(strict_types=1);

/**
 * REFIXEL Cron: Upcoming Booking Reminders
 * Dispatches reminders to customers and technicians for appointments scheduled in the next 24-48 hours.
 * Usage: php cron/upcoming_booking_reminders.php
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;
use App\Core\Notifier;

echo "[CRON] Starting upcoming booking reminder process...\n";

// Target date: tomorrow (next 24-48 hours window)
$targetDate = date('Y-m-d', strtotime('+1 day'));

$bookings = Database::fetchAll(
    "SELECT b.*, s.name as service_name
     FROM bookings b
     JOIN services s ON b.service_id = s.id
     WHERE b.preferred_date = :target_date
       AND b.status IN ('new', 'assigned', 'accepted', 'rescheduled')
     ORDER BY b.id ASC",
    ['target_date' => $targetDate]
);

$count = 0;
foreach ($bookings as $b) {
    // Check if reminder was already sent
    $alreadyReminded = Database::fetchOne(
        "SELECT id FROM notifications 
         WHERE template IN ('booking_reminder', 'booking_reminder_sms') 
           AND payload LIKE :bno_pattern 
           AND created_at >= CURDATE() - INTERVAL 1 DAY
         LIMIT 1",
        ['bno_pattern' => '%' . $b['booking_no'] . '%']
    );

    if ($alreadyReminded) {
        continue;
    }

    try {
        Notifier::notifyUpcomingReminder($b);
        $count++;
        echo " - Sent reminder for booking #{$b['booking_no']} to {$b['name']} ({$b['preferred_date']})\n";
    } catch (\Throwable $e) {
        Logger::error("Failed to send reminder for booking #{$b['booking_no']}: " . $e->getMessage());
    }
}

echo "[CRON] Finished upcoming booking reminders. Total dispatched: {$count}\n";

