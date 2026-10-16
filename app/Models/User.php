<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmailOrPhone(string $identifier): ?array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        // 1. Direct match on email (case-insensitive) or phone
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE LOWER(email) = LOWER(:id1) OR phone = :id2 LIMIT 1",
            ['id1' => $identifier, 'id2' => $identifier]
        );
        if ($user) {
            return $user;
        }

        // 2. Normalized phone lookup (strip spaces, dashes, +91, 91, or leading 0)
        $digits = preg_replace('/\D/', '', $identifier);
        if (!empty($digits)) {
            $tenDigit = $digits;
            if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
                $tenDigit = substr($digits, 2);
            } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $tenDigit = substr($digits, 1);
            }

            $user = Database::fetchOne(
                "SELECT * FROM users WHERE phone = :d1 OR phone = :d2 OR phone = :d3 OR phone = :d4 LIMIT 1",
                [
                    'd1' => $tenDigit,
                    'd2' => '91' . $tenDigit,
                    'd3' => '+91' . $tenDigit,
                    'd4' => $digits,
                ]
            );
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    public static function findByPhone(string $phone): ?array
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        $tenDigit = $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $tenDigit = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $tenDigit = substr($digits, 1);
        }

        return Database::fetchOne(
            "SELECT * FROM users WHERE phone = :d1 OR phone = :d2 OR phone = :d3 OR phone = :d4 LIMIT 1",
            [
                'd1' => $tenDigit,
                'd2' => '91' . $tenDigit,
                'd3' => '+91' . $tenDigit,
                'd4' => $digits,
            ]
        );
    }
}
