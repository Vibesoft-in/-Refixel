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
        $q = trim(strtolower($query));
        if (strlen($q) < 2) {
            return [];
        }

        $allServices = Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1"
        );

        $isShort = strlen($q) <= 3;
        $scored = [];

        foreach ($allServices as $row) {
            $name = strtolower($row['name'] ?? '');
            $catName = strtolower($row['category_name'] ?? '');
            $slug = strtolower($row['slug'] ?? '');
            $catSlug = strtolower($row['category_slug'] ?? '');
            $desc = strtolower($row['description'] ?? '');

            $score = 0;

            // 1. Exact matches (highest priority)
            if ($name === $q) $score += 1000;
            if ($catName === $q) $score += 850;
            if ($slug === $q) $score += 900;

            // 2. Starts with (service name or category name)
            if (str_starts_with($name, $q)) $score += 600;
            if (str_starts_with($catName, $q)) $score += 450;

            // 3. Whole word boundary match in service name
            if (preg_match('/\b' . preg_quote($q, '/') . '\b/i', $name)) {
                $score += 400;
            } elseif (!$isShort && str_contains($name, $q)) {
                $score += 200;
            }

            // 4. Whole word boundary match in category name
            if (preg_match('/\b' . preg_quote($q, '/') . '\b/i', $catName)) {
                $score += 300;
            } elseif (!$isShort && str_contains($catName, $q)) {
                $score += 150;
            }

            // 5. Slug token match (e.g. "ac" in "ac-jet-service" or "kitchen" in "kitchen-deep-cleaning")
            $slugTokens = explode('-', $slug);
            $catSlugTokens = explode('-', $catSlug);
            if (in_array($q, $slugTokens, true) || in_array($q, $catSlugTokens, true)) {
                $score += 250;
            } elseif (!$isShort && (str_contains($slug, $q) || str_contains($catSlug, $q))) {
                $score += 100;
            }

            // 6. Description match (excluded for short queries <= 3 chars to prevent false positives like 'ac' in 'space')
            if (!$isShort) {
                if (preg_match('/\b' . preg_quote($q, '/') . '\b/i', $desc)) {
                    $score += 40;
                } elseif (str_contains($desc, $q)) {
                    $score += 10;
                }
            }

            if ($score > 0) {
                $row['image'] = self::resolveImage($row['slug'] ?? '', $row['image'] ?? null);
                $row['search_score'] = $score;
                $scored[] = $row;
            }
        }

        // Sort descending by score, then alphabetically
        usort($scored, function ($a, $b) {
            if ($b['search_score'] !== $a['search_score']) {
                return $b['search_score'] <=> $a['search_score'];
            }
            return strcmp($a['name'], $b['name']);
        });

        return array_slice($scored, 0, $limit ?? 10);
    }
}
