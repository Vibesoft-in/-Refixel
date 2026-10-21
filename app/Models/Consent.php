<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Consent extends Model
{
    protected static string $table = 'consents';

    /**
     * Record a user or guest consent event for DPDP Act compliance.
     */
    public static function record(?int $userId, string $purpose, string $ip, ?string $identifier = null, ?string $userAgent = null): int
    {
        $now = date('Y-m-d H:i:s');
        return self::create([
            'user_id'      => $userId,
            'identifier'   => $identifier,
            'purpose'      => $purpose,
            'consent_type' => $purpose,
            'ip'           => $ip,
            'ip_address'   => $ip,
            'user_agent'   => $userAgent ? substr($userAgent, 0, 255) : null,
            'granted_at'   => $now,
            'consented_at' => $now,
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
            "SELECT id FROM consents WHERE user_id = :uid AND (purpose = :p1 OR consent_type = :p2) LIMIT 1",
            ['uid' => $userId, 'p1' => $purpose, 'p2' => $purpose]
        );
        return !empty($res);
    }
}
