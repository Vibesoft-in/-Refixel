<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ServiceVariant extends Model
{
    protected static string $table = 'service_variants';
    protected static string $primaryKey = 'id';

    /**
     * Ensure service_variants table exists and has seed data
     */
    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;

        try {
            Database::execute("
                CREATE TABLE IF NOT EXISTS service_variants (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    service_id INT UNSIGNED NOT NULL,
                    group_name VARCHAR(80) NOT NULL DEFAULT 'Option',
                    name VARCHAR(100) NOT NULL,
                    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    duration_minutes SMALLINT UNSIGNED NULL DEFAULT 60,
                    badge VARCHAR(60) NULL,
                    is_default TINYINT(1) NOT NULL DEFAULT 0,
                    sort_order INT NOT NULL DEFAULT 0,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_svc_group (service_id, group_name, is_active),
                    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Throwable $e) {}

        // Seed default initial variants if table is empty
        try {
            $row = Database::fetchOne("SELECT COUNT(*) as cnt FROM service_variants");
            $count = (int)($row['cnt'] ?? 0);
            if ($count === 0) {
                self::seedInitialVariants();
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Seed initial variants for Water Tank, Sofa, Kitchen, and Full Home Cleaning
     */
    public static function seedInitialVariants(): void
    {
        // 1. Water Tank Cleaning (slug: water-tank-cleaning)
        $tank = Database::fetchOne("SELECT id FROM services WHERE slug = 'water-tank-cleaning' OR name LIKE '%Tank%' LIMIT 1");
        if ($tank) {
            $tankId = (int)$tank['id'];
            $tankVariants = [
                ['group_name' => 'Capacity', 'name' => '250 Litres', 'price' => 299.00, 'duration_minutes' => 45, 'badge' => null, 'is_default' => 0, 'sort_order' => 1],
                ['group_name' => 'Capacity', 'name' => '500 Litres', 'price' => 400.00, 'duration_minutes' => 60, 'badge' => 'Popular', 'is_default' => 1, 'sort_order' => 2],
                ['group_name' => 'Capacity', 'name' => '1000 Litres', 'price' => 800.00, 'duration_minutes' => 90, 'badge' => 'Most Booked', 'is_default' => 0, 'sort_order' => 3],
                ['group_name' => 'Capacity', 'name' => '2000 Litres', 'price' => 1500.00, 'duration_minutes' => 120, 'badge' => 'Best Value', 'is_default' => 0, 'sort_order' => 4],
            ];
            foreach ($tankVariants as $v) {
                self::insertVariantIfNotExists($tankId, $v);
            }
            Database::execute("UPDATE services SET starting_price = 299.00, image = 'refixel-water-tank-cleaning.jpg' WHERE id = ?", [$tankId]);
        }

        // 2. Sofa Cleaning (slug: sofa-upholstery-deep-cleaning or sofa-cleaning)
        $sofa = Database::fetchOne("SELECT id FROM services WHERE slug IN ('sofa-upholstery-deep-cleaning', 'sofa-cleaning') OR name LIKE '%Sofa%' LIMIT 1");
        if ($sofa) {
            $sofaId = (int)$sofa['id'];
            $sofaVariants = [
                ['group_name' => 'Seats', 'name' => '3 Seater', 'price' => 399.00, 'duration_minutes' => 60, 'badge' => null, 'is_default' => 0, 'sort_order' => 1],
                ['group_name' => 'Seats', 'name' => '4 Seater', 'price' => 499.00, 'duration_minutes' => 75, 'badge' => null, 'is_default' => 0, 'sort_order' => 2],
                ['group_name' => 'Seats', 'name' => '5 Seater', 'price' => 599.00, 'duration_minutes' => 90, 'badge' => 'Popular', 'is_default' => 1, 'sort_order' => 3],
                ['group_name' => 'Seats', 'name' => '6 Seater', 'price' => 699.00, 'duration_minutes' => 105, 'badge' => 'Best Value', 'is_default' => 0, 'sort_order' => 4],
                ['group_name' => 'Seats', 'name' => '7 Seater', 'price' => 799.00, 'duration_minutes' => 120, 'badge' => null, 'is_default' => 0, 'sort_order' => 5],
            ];
            foreach ($sofaVariants as $v) {
                self::insertVariantIfNotExists($sofaId, $v);
            }
            Database::execute("UPDATE services SET starting_price = 399.00, image = 'refixel-sofa-cleaning.jpg' WHERE id = ?", [$sofaId]);
        }

        // 3. Kitchen Deep Cleaning (slug: kitchen-deep-cleaning)
        $kitchen = Database::fetchOne("SELECT id FROM services WHERE slug = 'kitchen-deep-cleaning' OR name LIKE '%Kitchen Deep%' LIMIT 1");
        if ($kitchen) {
            $kId = (int)$kitchen['id'];
            $kVariants = [
                // Group 1: Kitchen Size
                ['group_name' => 'Kitchen Size', 'name' => 'Small', 'price' => 499.00, 'duration_minutes' => 60, 'badge' => 'Upto 60 sqft', 'is_default' => 0, 'sort_order' => 1],
                ['group_name' => 'Kitchen Size', 'name' => 'Medium', 'price' => 699.00, 'duration_minutes' => 90, 'badge' => 'Popular (60-120 sqft)', 'is_default' => 1, 'sort_order' => 2],
                ['group_name' => 'Kitchen Size', 'name' => 'Large', 'price' => 899.00, 'duration_minutes' => 120, 'badge' => '120+ sqft', 'is_default' => 0, 'sort_order' => 3],
                // Group 2: Cleaning Type
                ['group_name' => 'Cleaning Type', 'name' => 'General Cleaning', 'price' => 0.00, 'duration_minutes' => 0, 'badge' => 'Standard', 'is_default' => 0, 'sort_order' => 1],
                ['group_name' => 'Cleaning Type', 'name' => 'Deep Cleaning', 'price' => 300.00, 'duration_minutes' => 30, 'badge' => 'Recommended (+₹300)', 'is_default' => 1, 'sort_order' => 2],
                ['group_name' => 'Cleaning Type', 'name' => 'Steam Cleaning', 'price' => 500.00, 'duration_minutes' => 45, 'badge' => 'High Heat Sanitization (+₹500)', 'is_default' => 0, 'sort_order' => 3],
            ];
            foreach ($kVariants as $v) {
                self::insertVariantIfNotExists($kId, $v);
            }
            Database::execute("UPDATE services SET starting_price = 499.00, image = 'refixel-kitchen-cleaning.jpg' WHERE id = ?", [$kId]);
        }

        // 4. Professional Full Home Cleaning (slug: professional-full-home-cleaning or full-home-cleaning)
        $home = Database::fetchOne("SELECT id FROM services WHERE slug IN ('professional-full-home-cleaning', 'full-home-cleaning') OR name LIKE '%Full Home%' LIMIT 1");
        if ($home) {
            $hId = (int)$home['id'];
            $hVariants = [
                // Group 1: Home Size
                ['group_name' => 'Home Size', 'name' => '1 BHK', 'price' => 1499.00, 'duration_minutes' => 120, 'badge' => 'Compact', 'is_default' => 0, 'sort_order' => 1],
                ['group_name' => 'Home Size', 'name' => '2 BHK', 'price' => 1999.00, 'duration_minutes' => 180, 'badge' => 'Popular', 'is_default' => 1, 'sort_order' => 2],
                ['group_name' => 'Home Size', 'name' => '3 BHK', 'price' => 2599.00, 'duration_minutes' => 240, 'badge' => 'Spacious', 'is_default' => 0, 'sort_order' => 3],
                ['group_name' => 'Home Size', 'name' => '4 BHK / Villa', 'price' => 3499.00, 'duration_minutes' => 300, 'badge' => 'Luxury', 'is_default' => 0, 'sort_order' => 4],
                // Group 2: Cleaning Type
                ['group_name' => 'Cleaning Type', 'name' => 'General Cleaning', 'price' => 0.00, 'duration_minutes' => 0, 'badge' => 'Basic Dust & Mop', 'is_default' => 0, 'sort_order' => 1],
                ['group_name' => 'Cleaning Type', 'name' => 'Deep Cleaning', 'price' => 500.00, 'duration_minutes' => 60, 'badge' => 'Intensive Scrubbing (+₹500)', 'is_default' => 1, 'sort_order' => 2],
                ['group_name' => 'Cleaning Type', 'name' => 'Steam Cleaning', 'price' => 900.00, 'duration_minutes' => 90, 'badge' => 'Disinfection (+₹900)', 'is_default' => 0, 'sort_order' => 3],
            ];
            foreach ($hVariants as $v) {
                self::insertVariantIfNotExists($hId, $v);
            }
            Database::execute("UPDATE services SET starting_price = 1499.00, image = 'refixel-full-home-clean.jpg' WHERE id = ?", [$hId]);
        }
    }

    protected static function insertVariantIfNotExists(int $serviceId, array $v): void
    {
        $chk = Database::fetchOne(
            "SELECT id FROM service_variants WHERE service_id = ? AND group_name = ? AND name = ?",
            [$serviceId, $v['group_name'], $v['name']]
        );
        if (!$chk) {
            Database::execute(
                "INSERT INTO service_variants (service_id, group_name, name, price, duration_minutes, badge, is_default, sort_order, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())",
                [
                    $serviceId,
                    $v['group_name'],
                    $v['name'],
                    $v['price'],
                    $v['duration_minutes'] ?? 60,
                    $v['badge'] ?? null,
                    $v['is_default'] ?? 0,
                    $v['sort_order'] ?? 0,
                ]
            );
        }
    }

    /**
     * Get grouped active variants for a service
     * Returns: ['Group Name' => [variant1, variant2, ...]]
     */
    public static function getGroupedByService(int $serviceId): array
    {
        self::ensureSchema();

        $rows = Database::fetchAll(
            "SELECT * FROM service_variants 
             WHERE service_id = ? AND is_active = 1 
             ORDER BY sort_order ASC, id ASC",
            [$serviceId]
        );

        $grouped = [];
        foreach ($rows as $r) {
            $group = $r['group_name'] ?: 'Option';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $r;
        }

        return $grouped;
    }

    /**
     * Get flat list of variants for service
     */
    public static function forService(int $serviceId, bool $activeOnly = true): array
    {
        self::ensureSchema();
        $sql = "SELECT * FROM service_variants WHERE service_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY group_name ASC, sort_order ASC, id ASC";

        return Database::fetchAll($sql, [$serviceId]);
    }

    /**
     * Calculate price from chosen variant IDs for a service.
     * If no variants provided, defaults to default variants or base price.
     */
    public static function calculatePrice(int $serviceId, array $variantIds, float $fallbackBasePrice): array
    {
        self::ensureSchema();
        $grouped = self::getGroupedByService($serviceId);

        if (empty($grouped)) {
            return [
                'total_price'   => $fallbackBasePrice,
                'title_suffix'  => '',
                'duration'      => 60,
                'selected_ids'  => [],
                'variant_items' => [],
            ];
        }

        $chosenVariants = [];
        $selectedIds = [];
        $totalPrice = 0.0;
        $totalDuration = 0;

        $idMap = array_fill_keys(array_map('intval', $variantIds), true);

        // For each group, pick chosen variant OR fallback to is_default OR first variant
        foreach ($grouped as $groupName => $variants) {
            $picked = null;
            foreach ($variants as $v) {
                if (isset($idMap[(int)$v['id']])) {
                    $picked = $v;
                    break;
                }
            }
            if (!$picked) {
                // Find default
                foreach ($variants as $v) {
                    if (!empty($v['is_default'])) {
                        $picked = $v;
                        break;
                    }
                }
            }
            if (!$picked && !empty($variants)) {
                $picked = $variants[0];
            }

            if ($picked) {
                $chosenVariants[] = $picked;
                $selectedIds[] = (int)$picked['id'];
                $totalPrice += (float)$picked['price'];
                $totalDuration += (int)($picked['duration_minutes'] ?? 0);
            }
        }

        $nameParts = array_column($chosenVariants, 'name');
        $titleSuffix = !empty($nameParts) ? (' (' . implode(' · ', $nameParts) . ')') : '';

        return [
            'total_price'   => max(0.00, $totalPrice),
            'title_suffix'  => $titleSuffix,
            'duration'      => max(30, $totalDuration),
            'selected_ids'  => $selectedIds,
            'variant_items' => $chosenVariants,
        ];
    }
}
