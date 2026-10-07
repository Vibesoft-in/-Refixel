<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Faq extends Model
{
    protected static string $table = 'faqs';

    public static function getGlobal(): array
    {
        return Database::fetchAll(
            "SELECT * FROM faqs WHERE service_id IS NULL AND is_active = 1 ORDER BY sort_order ASC, id ASC"
        );
    }

    public static function getByService(int $serviceId): array
    {
        return Database::fetchAll(
            "SELECT * FROM faqs WHERE (service_id = :sid OR service_id IS NULL) AND is_active = 1 ORDER BY sort_order ASC",
            ['sid' => $serviceId]
        );
    }
}
