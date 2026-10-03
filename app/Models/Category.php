<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Category extends Model
{
    protected static string $table = 'categories';

    public static function getActive(): array
    {
        return Database::fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetchOne("SELECT * FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1", ['slug' => $slug]);
    }

    public static function getRelated(int $currentCategoryId, int $limit = 4): array
    {
        return Database::fetchAll(
            "SELECT * FROM categories WHERE id != :cid AND is_active = 1 ORDER BY sort_order ASC LIMIT {$limit}",
            ['cid' => $currentCategoryId]
        );
    }
}

