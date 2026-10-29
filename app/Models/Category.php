<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Category extends Model
{
    protected static string $table = 'categories';

    public static function ensureSchema(): void
    {
        static $ensured = false;
        if ($ensured) return;
        $ensured = true;

        try {
            $cols = Database::fetchAll("SHOW COLUMNS FROM categories");
            $fields = array_column($cols, 'Field');
            if (!in_array('is_coming_soon', $fields, true)) {
                Database::execute("ALTER TABLE categories ADD COLUMN is_coming_soon TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
            }
        } catch (\Throwable $e) {}
    }

    public static function getActive(): array
    {
        self::ensureSchema();
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

    public static function resolveImage(string $slug, ?string $currentImage = null): string
    {
        $map = [
            'cleaning'              => 'refixel-cleaning.jpg',
            'painting-services'     => 'refixel-painting.jpg',
            'fall-ceiling-services' => 'refixel-fall-ceiling.jpg',
            'fall-ceiling'          => 'refixel-fall-ceiling.jpg',
            'masonry-services'      => 'refixel-masonry.jpg',
            'plumbers'              => 'refixel-plumber.jpg',
            'carpenter'             => 'refixel-carpenter.jpg',
            'ac-services'           => 'refixel-ac-service.jpg',
            'electrician'           => 'refixel-electrician.jpg',
            'appliance-repair'      => 'refixel-appliance-repair.jpg',
            'pest-control'          => 'refixel-pest-control.jpg',
        ];

        $cur = trim((string)$currentImage);
        if (!empty($cur) && !str_contains($cur, 'icon') && !str_ends_with($cur, '.svg')) {
            return $cur;
        }

        return $map[$slug] ?? 'refixel-cleaning.jpg';
    }
}

