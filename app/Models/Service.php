<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Service extends Model
{
    protected static string $table = 'services';

    public static function getActive(): array
    {
        return Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1 
             ORDER BY c.sort_order ASC, s.name ASC"
        );
    }

    public static function getActiveByCategory(int $categoryId): array
    {
        return Database::fetchAll(
            "SELECT * FROM services WHERE category_id = :cid AND is_active = 1 ORDER BY name ASC",
            ['cid' => $categoryId]
        );
    }


    public static function findBySlug(string $slug): ?array
    {
        return Database::fetchOne(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             LEFT JOIN categories c ON s.category_id = c.id 
             WHERE s.slug = :slug AND s.is_active = 1 
             LIMIT 1",
            ['slug' => $slug]
        );
    }

    public static function search(string $query, ?int $limit = 10): array
    {
        $term = '%' . $query . '%';
        return Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE (s.name LIKE :q1 OR s.description LIKE :q2 OR c.name LIKE :q3) AND s.is_active = 1 
             LIMIT {$limit}",
            ['q1' => $term, 'q2' => $term, 'q3' => $term]
        );
    }
}
