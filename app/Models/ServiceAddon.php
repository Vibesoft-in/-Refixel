<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Models\Setting;
use App\Models\Service;

class ServiceAddon extends Model
{
    protected static string $table = 'service_addons';
    protected static string $primaryKey = 'id';

    public const SETTING_AUTO_SENSIBLE = 'addons_auto_sensible_enabled';

    /**
     * Check if Auto-Sensible add-on engine is enabled.
     */
    public static function isAutoSensibleEnabled(): bool
    {
        return Setting::get(self::SETTING_AUTO_SENSIBLE, '1') === '1';
    }

    /**
     * Toggle Auto-Sensible add-on engine.
     */
    public static function setAutoSensibleEnabled(bool $enabled): void
    {
        Setting::set(self::SETTING_AUTO_SENSIBLE, $enabled ? '1' : '0');
    }

    /**
     * Get smart, sensible add-on recommendations for a given service.
     */
    public static function forService(int $serviceId, array $excludeIds = [], int $limit = 4): array
    {
        $excludeMap = array_fill_keys(array_map('intval', $excludeIds), true);
        $excludeMap[$serviceId] = true;

        $results = [];
        $seenAddonServiceIds = [];

        // 1. Direct explicit admin pairings for this specific service
        $directRows = Database::fetchAll(
            "SELECT sa.id as rule_id, sa.custom_badge, sa.custom_price, sa.custom_note, sa.custom_image, sa.sort_order,
                    s.id, s.name, s.slug, s.starting_price, s.duration_minutes, s.image, s.category_id,
                    c.name as category_name
             FROM service_addons sa
             JOIN services s ON sa.addon_service_id = s.id
             LEFT JOIN categories c ON s.category_id = c.id
             WHERE sa.source_service_id = :sid
               AND sa.is_active = 1
               AND s.is_active = 1
               AND (s.is_coming_soon = 0 OR s.is_coming_soon IS NULL)
             ORDER BY sa.sort_order ASC, sa.id ASC",
            ['sid' => $serviceId]
        );

        foreach ($directRows as $r) {
            $aid = (int)$r['id'];
            if (!isset($excludeMap[$aid]) && !isset($seenAddonServiceIds[$aid])) {
                $seenAddonServiceIds[$aid] = true;
                $results[] = self::formatAddonItem($r);
                if (count($results) >= $limit) {
                    return $results;
                }
            }
        }

        // Retrieve current service information
        $currentService = Service::find($serviceId);
        $catId = (int)($currentService['category_id'] ?? 0);
        $serviceSlug = strtolower((string)($currentService['slug'] ?? ''));
        $serviceName = strtolower((string)($currentService['name'] ?? ''));

        // 2. Category-level explicit admin pairings
        if ($catId > 0 && count($results) < $limit) {
            $catRows = Database::fetchAll(
                "SELECT sa.id as rule_id, sa.custom_badge, sa.custom_price, sa.custom_note, sa.custom_image, sa.sort_order,
                        s.id, s.name, s.slug, s.starting_price, s.duration_minutes, s.image, s.category_id,
                        c.name as category_name
                 FROM service_addons sa
                 JOIN services s ON sa.addon_service_id = s.id
                 LEFT JOIN categories c ON s.category_id = c.id
                 WHERE sa.source_category_id = :cid
                   AND sa.source_service_id IS NULL
                   AND sa.is_active = 1
                   AND s.is_active = 1
                   AND (s.is_coming_soon = 0 OR s.is_coming_soon IS NULL)
                 ORDER BY sa.sort_order ASC, sa.id ASC",
                ['cid' => $catId]
            );

            foreach ($catRows as $r) {
                $aid = (int)$r['id'];
                if (!isset($excludeMap[$aid]) && !isset($seenAddonServiceIds[$aid])) {
                    $seenAddonServiceIds[$aid] = true;
                    $results[] = self::formatAddonItem($r);
                    if (count($results) >= $limit) {
                        return $results;
                    }
                }
            }
        }

        // 3. Smart Sensible Keyword Engine (Active if Auto-Sensible is enabled)
        if (self::isAutoSensibleEnabled() && count($results) < $limit) {
            $sensibleCandidates = self::findSensibleCandidates($serviceSlug, $serviceName, $catId);

            foreach ($sensibleCandidates as $cand) {
                $aid = (int)$cand['id'];
                if (!isset($excludeMap[$aid]) && !isset($seenAddonServiceIds[$aid])) {
                    $seenAddonServiceIds[$aid] = true;
                    $results[] = self::formatAddonItem($cand);
                    if (count($results) >= $limit) {
                        return $results;
                    }
                }
            }
        }

        // 4. Ultimate Fallback: complementary budget items
        if (count($results) < $limit) {
            $allActive = Database::fetchAll(
                "SELECT s.id, s.name, s.slug, s.starting_price, s.duration_minutes, s.image, s.category_id,
                        c.name as category_name
                 FROM services s
                 LEFT JOIN categories c ON s.category_id = c.id
                 WHERE s.is_active = 1
                   AND (s.is_coming_soon = 0 OR s.is_coming_soon IS NULL)
                 ORDER BY s.starting_price ASC"
            );

            foreach ($allActive as $item) {
                $aid = (int)$item['id'];
                if ($aid !== $serviceId && !isset($excludeMap[$aid]) && !isset($seenAddonServiceIds[$aid])) {
                    $seenAddonServiceIds[$aid] = true;
                    $results[] = self::formatAddonItem($item);
                    if (count($results) >= $limit) {
                        break;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Find sensible contextual candidates based on service context (kitchen, bath, sofa/room, ac, etc.)
     */
    protected static function findSensibleCandidates(string $slug, string $name, int $categoryId): array
    {
        $context = $slug . ' ' . $name;
        $slugTerms = [];

        if (str_contains($context, 'kitchen')) {
            // Sensible kitchen addons
            $slugTerms = ['chimney', 'microwave', 'refrigerator', 'drain', 'cockroach', 'pest'];
        } elseif (str_contains($context, 'bath') || str_contains($context, 'washroom') || str_contains($context, 'toilet')) {
            // Sensible bathroom addons
            $slugTerms = ['bathroom-tile', 'tap-shower', 'exhaust-fan', 'tap-leak', 'drain'];
        } elseif (str_contains($context, 'sofa') || str_contains($context, 'couch') || str_contains($context, 'upholstery')) {
            // Sensible sofa / living room addons
            $slugTerms = ['mattress', 'ceiling-fan', 'balcony', 'full-home', 'cockroach'];
        } elseif (str_contains($context, 'balcony') || str_contains($context, 'terrace') || str_contains($context, 'patio')) {
            // Sensible balcony / terrace outdoor wash addons
            $slugTerms = ['sofa', 'cockroach', 'bathroom', 'ceiling-fan', 'full-home'];
        } elseif (str_contains($context, 'home') || str_contains($context, 'house') || str_contains($context, 'flat') || str_contains($context, 'room')) {
            // Sensible full home / room cleaning addons
            $slugTerms = ['sofa', 'cockroach', 'balcony', 'bathroom', 'mattress', 'ceiling-fan'];
        } elseif (str_contains($context, 'ac') || str_contains($context, 'air-conditioner') || str_contains($context, 'cooling')) {
            // Sensible AC addons
            $slugTerms = ['ac-anti', 'fan-switchboard', 'appliance-diagnostic'];
        } elseif (str_contains($context, 'plumb') || str_contains($context, 'tap') || str_contains($context, 'leak') || str_contains($context, 'pipe')) {
            // Sensible plumbing addons
            $slugTerms = ['drain', 'tap-shower', 'bathroom-deep-cleaning'];
        } elseif (str_contains($context, 'electric') || str_contains($context, 'switch') || str_contains($context, 'fan') || str_contains($context, 'light')) {
            // Sensible electrician addons
            $slugTerms = ['ceiling-fan', 'ac-anti', 'appliance-diagnostic'];
        } elseif (str_contains($context, 'pest') || str_contains($context, 'cockroach') || str_contains($context, 'insect')) {
            // Sensible pest control addons
            $slugTerms = ['kitchen-deep', 'bathroom-deep', 'drain', 'sofa'];
        }

        if (empty($slugTerms)) {
            return [];
        }

        // Query active services matching these keywords in slug or name
        $whereClauses = [];
        $params = [];
        foreach ($slugTerms as $idx => $term) {
            $whereClauses[] = "(s.slug LIKE :term{$idx} OR s.name LIKE :nterm{$idx})";
            $params["term{$idx}"] = "%{$term}%";
            $params["nterm{$idx}"] = "%{$term}%";
        }

        $sql = "SELECT s.id, s.name, s.slug, s.starting_price, s.duration_minutes, s.image, s.category_id,
                       c.name as category_name
                FROM services s
                LEFT JOIN categories c ON s.category_id = c.id
                WHERE s.is_active = 1
                  AND (s.is_coming_soon = 0 OR s.is_coming_soon IS NULL)
                  AND (" . implode(' OR ', $whereClauses) . ")
                ORDER BY s.starting_price ASC";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Standardize format of an addon item for client modals and APIs.
     */
    protected static function formatAddonItem(array $row): array
    {
        $customPrice = isset($row['custom_price']) && (float)$row['custom_price'] > 0 
            ? (float)$row['custom_price'] 
            : (float)($row['starting_price'] ?? 0);

        $defaultBadges = ['🔥 Popular Add-on', '⭐ Frequently Added', '✨ Best Match', '⚡ Quick Addon'];
        $badge = !empty($row['custom_badge']) ? $row['custom_badge'] : $defaultBadges[crc32($row['slug'] ?? '') % count($defaultBadges)];
        $savingsNote = !empty($row['custom_note']) ? $row['custom_note'] : 'Special Companion Rate';

        $effectiveImage = !empty($row['custom_image']) ? $row['custom_image'] : ($row['image'] ?? null);

        return [
            'id'               => (int)$row['id'],
            'rule_id'          => (int)($row['rule_id'] ?? 0),
            'name'             => (string)$row['name'],
            'slug'             => (string)$row['slug'],
            'starting_price'   => $customPrice,
            'base_price'       => (float)($row['starting_price'] ?? $customPrice),
            'duration_minutes' => (int)($row['duration_minutes'] ?? 30),
            'image'            => Service::resolveImage($row['slug'] ?? '', $effectiveImage),
            'category_name'    => (string)($row['category_name'] ?? 'Home Care'),
            'badge'            => $badge,
            'savings_note'     => $savingsNote,
        ];
    }

    /**
     * Retrieve all addon pairings with parent service & addon service info for the Admin view.
     */
    public static function allAdminList(?int $filterServiceId = null, ?int $filterCategoryId = null): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($filterServiceId !== null && $filterServiceId > 0) {
            $where .= " AND sa.source_service_id = :sid";
            $params['sid'] = $filterServiceId;
        }

        if ($filterCategoryId !== null && $filterCategoryId > 0) {
            $where .= " AND (sa.source_category_id = :cid OR src.category_id = :cid2)";
            $params['cid'] = $filterCategoryId;
            $params['cid2'] = $filterCategoryId;
        }

        $sql = "SELECT sa.*, 
                       COALESCE(sa.source_category_id, src.category_id) as effective_category_id,
                       COALESCE(cat.name, src_cat.name) as source_category_name,
                       src.name as source_service_name, src.slug as source_service_slug,
                       src.category_id as source_service_cat_id,
                       addon.name as addon_name, addon.slug as addon_slug, 
                       addon.starting_price as addon_base_price, addon.image as addon_image,
                       addon_cat.name as addon_category_name
                FROM service_addons sa
                LEFT JOIN services src ON sa.source_service_id = src.id
                LEFT JOIN categories src_cat ON src.category_id = src_cat.id
                LEFT JOIN categories cat ON sa.source_category_id = cat.id
                JOIN services addon ON sa.addon_service_id = addon.id
                LEFT JOIN categories addon_cat ON addon.category_id = addon_cat.id
                {$where}
                ORDER BY COALESCE(cat.sort_order, src_cat.sort_order, 99) ASC, 
                         COALESCE(cat.name, src_cat.name) ASC, 
                         src.name ASC, sa.sort_order ASC, sa.id ASC";

        return Database::fetchAll($sql, $params);
    }
}
