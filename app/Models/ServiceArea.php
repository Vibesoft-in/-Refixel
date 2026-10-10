<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ServiceArea extends Model
{
    protected static string $table = 'service_areas';

    public const ALLOWED_CITIES = ['Kashipur', 'Jaspur', 'Thakurdwara'];
    public const DEFAULT_CITY = 'Kashipur';

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

    public static function isValidCity(string $city): bool
    {
        $trimmed = trim($city);
        foreach (self::ALLOWED_CITIES as $allowed) {
            if (strcasecmp($allowed, $trimmed) === 0) {
                return true;
            }
        }
        return false;
    }

    public static function normalizeCity(?string $city): string
    {
        if (empty($city)) {
            return self::DEFAULT_CITY;
        }
        $trimmed = trim($city);
        foreach (self::ALLOWED_CITIES as $allowed) {
            if (strcasecmp($allowed, $trimmed) === 0) {
                return $allowed;
            }
        }
        return self::DEFAULT_CITY;
    }

    public static function getSessionCity(): string
    {
        $city = $_SESSION['selected_city'] ?? null;
        $normalized = self::normalizeCity($city);
        $_SESSION['selected_city'] = $normalized;
        return $normalized;
    }

    public static function setSessionCity(string $city): string
    {
        $normalized = self::normalizeCity($city);
        $_SESSION['selected_city'] = $normalized;
        return $normalized;
    }
}

