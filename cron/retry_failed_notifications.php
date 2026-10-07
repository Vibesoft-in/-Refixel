<?php
declare(strict_types=1);

/**
 * REFIXEL Cron: Retry Failed Notifications
 * Identifies notifications that failed during network glitches or provider downtimes and retries dispatch.
 * Usage: php cron/retry_failed_notifications.php
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;
use App\Core\Notifier;

echo "[CRON] Starting failed notifications retry sweep...\n";

// Fetch failed notifications from last 24 hours (max 30 per run)
$failedNotifications = Database::fetchAll(
    "SELECT * FROM notifications 
     WHERE status = 'failed' 
       AND created_at >= NOW() - INTERVAL 24 HOUR
     ORDER BY id ASC 
     LIMIT 30"
);

$retried = 0;
$recovered = 0;

foreach ($failedNotifications as $n) {
    $retried++;
    $payload = json_decode((string)$n['payload'], true) ?: [];

    try {
        $success = match ($n['channel']) {
            'email'    => Notifier::getEmailProvider()->sendTemplate($n['to_address'], $n['template'], $payload),
            'sms'      => Notifier::getSmsProvider()->sendTransactional($n['to_address'], $n['template'], $payload),
            'whatsapp' => Notifier::getWhatsAppProvider()->sendTemplateMessage($n['to_address'], $n['template'], array_values($payload)),
            default    => false,
        };

        if ($success) {
            $recovered++;
            Database::query(
                "UPDATE notifications 
                 SET status = 'sent', error_message = CONCAT(IFNULL(error_message, ''), ' [Recovered via cron retry at ', NOW(), ']')
                 WHERE id = :id",
                ['id' => $n['id']]
            );
            echo " - Notification #{$n['id']} ({$n['channel']} to {$n['to_address']}) successfully recovered.\n";
        } else {
            echo " - Notification #{$n['id']} retry still failed.\n";
        }
    } catch (\Throwable $e) {
        Logger::error("Cron retry error for notification #{$n['id']}: " . $e->getMessage());
    }
}

echo "[CRON] Retry sweep completed. Processed: {$retried}, Recovered: {$recovered}\n";

