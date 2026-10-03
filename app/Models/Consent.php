<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Consent extends Model
{
    protected static string $table = 'consents';

    public static function record(?int $userId, string $purpose, string $ip): int
    {
        return self::create([
            'user_id'    => $userId,
            'purpose'    => $purpose,
            'ip'         => $ip,
            'granted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getByUser(int $userId): array
    {
        return Database::fetchAll(
            "SELECT * FROM consents WHERE user_id = :uid ORDER BY granted_at DESC",
            ['uid' => $userId]
        );
    }

    public static function hasConsent(int $userId, string $purpose): bool
    {
        $res = Database::fetchOne(
            "SELECT id FROM consents WHERE user_id = :uid AND purpose = :purpose LIMIT 1",
            ['uid' => $userId, 'purpose' => $purpose]
        );
        return !empty($res);
    }
}

