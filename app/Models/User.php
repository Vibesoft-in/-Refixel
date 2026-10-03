<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmailOrPhone(string $identifier): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM users WHERE email = :id1 OR phone = :id2 LIMIT 1",
            ['id1' => $identifier, 'id2' => $identifier]
        );
    }
}
