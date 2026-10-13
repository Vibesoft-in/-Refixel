<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class PaymentGateway extends Model
{
    protected static string $table = 'payment_gateways';

    public static function getAll(): array
    {
        return Database::fetchAll("SELECT * FROM payment_gateways ORDER BY display_order ASC, id ASC");
    }

    public static function getActiveGateways(): array
    {
        return Database::fetchAll("SELECT * FROM payment_gateways WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
    }

    public static function getByCode(string $code): ?array
    {
        return Database::fetchOne("SELECT * FROM payment_gateways WHERE code = :code LIMIT 1", ['code' => $code]);
    }

    public static function syncToSettings(array $gateway): void
    {
        if ($gateway['code'] === 'razorpay') {
            Setting::set('razorpay_key_id', (string)($gateway['key_id'] ?? ''));
            Setting::set('razorpay_key_secret', (string)($gateway['key_secret'] ?? ''));
            Setting::set('razorpay_environment', (string)($gateway['environment'] ?? 'live'));
            Setting::set('razorpay_is_active', !empty($gateway['is_active']) ? '1' : '0');
        }
    }
}
