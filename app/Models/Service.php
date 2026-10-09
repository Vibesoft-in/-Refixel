<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Service extends Model
{
    protected static string $table = 'services';

    public static function resolveImage(string $slug, ?string $currentImage = null): string
    {
        $map = [
            'balcony-deep-pressure-wash'         => 'refixel-balcony-pressure-wash.png',
            'full-home-cleaning'                 => 'refixel-cleaning.jpg',
            'bathroom-deep-cleaning'             => 'refixel-bathroom-cleaning.jpg',
            'kitchen-deep-cleaning'              => 'refixel-kitchen-cleaning.jpg',
            'sofa-cleaning'                      => 'refixel-sofa-cleaning.jpg',
            'office-cleaning'                    => 'refixel-office-cleaning.jpg',
            'interior-painting'                  => 'refixel-painting.jpg',
            'cockroach-pest-control'             => 'service-pest-control.jpg',
            'tap-leak-repair'                    => 'refixel-plumber.jpg',
            'furniture-assembly'                 => 'refixel-carpenter.jpg',
            'ac-jet-service'                     => 'refixel-ac-service.jpg',
            'fan-switchboard-repair'             => 'refixel-electrician.jpg',
            'appliance-diagnostic-repair'        => 'refixel-appliance-repair.jpg',
            'fall-ceiling-installation'          => 'refixel-fall-ceiling.jpg',
            'fall-ceiling-repair-modification'   => 'refixel-fall-ceiling.jpg',
        ];

        $cur = trim((string)$currentImage);
        if (empty($cur) || $cur === 'Full-home-clean.jpg' || $cur === '5_1_Full-home-clean.jpg') {
            return $map[$slug] ?? ($cur ?: 'refixel-cleaning.jpg');
        }

        return $cur;
    }

    public static function getActive(): array
    {
        $rows = Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1 
             ORDER BY c.sort_order ASC, s.name ASC"
        );
        foreach ($rows as &$row) {
            $row['image'] = self::resolveImage($row['slug'] ?? '', $row['image'] ?? null);
        }
        return $rows;
    }

    public static function getActiveByCategory(int $categoryId): array
    {
        $rows = Database::fetchAll(
            "SELECT * FROM services WHERE category_id = :cid AND is_active = 1 ORDER BY name ASC",
            ['cid' => $categoryId]
        );
        foreach ($rows as &$row) {
            $row['image'] = self::resolveImage($row['slug'] ?? '', $row['image'] ?? null);
        }
        return $rows;
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = Database::fetchOne(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             LEFT JOIN categories c ON s.category_id = c.id 
             WHERE s.slug = :slug AND s.is_active = 1 
             LIMIT 1",
            ['slug' => $slug]
        );
        if ($row) {
            $row['image'] = self::resolveImage($row['slug'] ?? '', $row['image'] ?? null);
        }
        return $row;
    }

    public static function search(string $query, ?int $limit = 10): array
    {
        $term = '%' . $query . '%';
        $rows = Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE (s.name LIKE :q1 OR s.description LIKE :q2 OR c.name LIKE :q3) AND s.is_active = 1 
             LIMIT {$limit}",
            ['q1' => $term, 'q2' => $term, 'q3' => $term]
        );
        foreach ($rows as &$row) {
            $row['image'] = self::resolveImage($row['slug'] ?? '', $row['image'] ?? null);
        }
        return $rows;
    }
}
