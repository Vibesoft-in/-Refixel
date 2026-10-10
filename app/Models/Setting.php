<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Cache;
use App\Core\Database;

class Setting extends Model
{
    protected static string $table = 'settings';
    protected const CACHE_KEY = 'site_settings_map';

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::getAllKeyValue();
        $val = $all[$key] ?? $default;
        if ($key === 'site_logo' && ($val === 'img/logo.png' || $val === 'logo.png' || empty($val))) {
            return 'img/refixel-logo-horizontal.png';
        }
        return $val;
    }

    public static function set(string $key, string $value): void
    {
        Database::query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2",
            ['k' => $key, 'v' => $value, 'v2' => $value]
        );
        Cache::forget(self::CACHE_KEY);
    }

    public static function getAllKeyValue(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $rows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
            $map = [];
            foreach ($rows as $r) {
                $map[$r['setting_key']] = $r['setting_value'];
            }
            return $map;
        });
    }
}
