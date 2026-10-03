<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ServiceArea extends Model
{
    protected static string $table = 'service_areas';

    public static function getActiveCities(): array
    {
        return Database::fetchAll("SELECT DISTINCT city FROM service_areas WHERE is_active = 1 ORDER BY city ASC");
    }

    public static function getByCity(string $city): array
    {
        return Database::fetchAll(
            "SELECT * FROM service_areas WHERE (city = :c1 OR city LIKE :c2) AND is_active = 1 ORDER BY area_name ASC",
            ['c1' => $city, 'c2' => '%' . $city . '%']
        );
    }

    public static function isPincodeServed(string $pincode): bool
    {
        $res = Database::fetchOne("SELECT id FROM service_areas WHERE pincode = :pin AND is_active = 1 LIMIT 1", ['pin' => $pincode]);
        return $res !== null;
    }
}

