<?php
declare(strict_types=1);

/**
 * REFIXEL Cron: System Maintenance & Security Cleanup
 * Purges expired password reset tokens and stale rate limiting attempt logs.
 * Usage: php cron/cleanup_tasks.php
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;

echo "[CRON] Starting system cleanup tasks...\n";

// 1. Purge expired password resets
$resetsDeleted = Database::query("DELETE FROM password_resets WHERE expires_at < NOW()");
echo " - Purged expired password reset records.\n";

// 2. Purge stale login attempts (older than 30 days)
$attemptsDeleted = Database::query("DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 30 DAY");
echo " - Purged stale rate limiting / login attempt logs older than 30 days.\n";

// 3. Purge orphaned sessions or temp files if storage/cache exists
$cacheDir = __DIR__ . '/../storage/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*');
    $purgedCache = 0;
    foreach ($files as $file) {
        if (is_file($file) && (time() - filemtime($file) > 86400 * 7)) {
            @unlink($file);
            $purgedCache++;
        }
    }
    echo " - Purged {$purgedCache} expired cache files.\n";
}

echo "[CRON] System cleanup finished successfully.\n";

