<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ServiceChecklistItem extends Model
{
    protected static string $table = 'service_checklist_items';

    public static function getByService(int $serviceId): array
    {
        return Database::fetchAll(
            "SELECT * FROM service_checklist_items WHERE service_id = :sid ORDER BY is_included DESC, sort_order ASC",
            ['sid' => $serviceId]
        );
    }
}
