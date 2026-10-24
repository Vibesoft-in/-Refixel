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

    public static function isRazorpayActive(): bool
    {
        $gw = self::getByCode('razorpay');
        if ($gw) {
            return !empty($gw['is_active']);
        }
        return (string)Setting::get('razorpay_is_active', '1') === '1';
    }

    public static function isCodActive(): bool
    {
        $gw = self::getByCode('cod');
        if ($gw) {
            return !empty($gw['is_active']);
        }
        return (string)Setting::get('cod_is_active', '1') === '1';
    }

    public static function isOnlineActive(): bool
    {
        return self::isRazorpayActive();
    }

    public static function isAdvanceRequired(): bool
    {
        // Advance requires both the toggle to be ON and an online payment gateway (Razorpay) to be ACTIVE
        $enabled = (string)Setting::get('prebooking_advance_enabled', '0') === '1';
        return $enabled && self::isRazorpayActive();
    }

    public static function getAdvanceAmount(): float
    {
        return max(0.0, (float)Setting::get('prebooking_advance_amount', 49.00));
    }

    public static function syncToSettings(array $gateway): void
    {
        $code = (string)($gateway['code'] ?? '');
        if ($code === 'razorpay') {
            Setting::set('razorpay_key_id', (string)($gateway['key_id'] ?? ''));
            Setting::set('razorpay_key_secret', (string)($gateway['key_secret'] ?? ''));
            Setting::set('razorpay_environment', (string)($gateway['environment'] ?? 'live'));
            Setting::set('razorpay_is_active', !empty($gateway['is_active']) ? '1' : '0');
        } else {
            Setting::set($code . '_is_active', !empty($gateway['is_active']) ? '1' : '0');
        }
    }
}
