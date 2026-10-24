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
            'balcony-deep-pressure-wash'                 => 'refixel-balcony-pressure-wash.png',
            'full-home-cleaning'                         => 'refixel-full-home-clean.jpg',
            'professional-full-home-cleaning'            => 'refixel-full-home-clean.jpg',
            'bathroom-deep-cleaning'                     => 'refixel-bathroom-cleaning.jpg',
            'kitchen-deep-cleaning'                      => 'refixel-kitchen-cleaning.jpg',
            'sofa-cleaning'                              => 'refixel-sofa-cleaning.jpg',
            'sofa-upholstery-deep-cleaning'              => 'refixel-sofa-cleaning.jpg',
            'office-cleaning'                            => 'refixel-office-cleaning.jpg',
            'interior-painting'                          => 'refixel-painting.jpg',
            'cockroach-pest-control'                     => 'service-pest-control.jpg',
            'tap-leak-repair'                            => 'refixel-plumber.jpg',
            'furniture-assembly'                         => 'refixel-carpenter.jpg',
            'ac-jet-service'                             => 'refixel-ac-service.jpg',
            'fan-switchboard-repair'                     => 'service-fan-switchboard-repair.jpg',
            'light-fitting-installation'                 => 'service-light-fitting-installation.jpg',
            'wiring-mcb-repair'                          => 'service-wiring-mcb-repair.jpg',
            'appliance-installation'                     => 'service-appliance-installation.jpg',
            'appliance-diagnostic-repair'                => 'refixel-appliance-repair.jpg',
            'fall-ceiling-installation'                  => 'refixel-fall-ceiling.jpg',
            'fall-ceiling-repair-modification'           => 'refixel-fall-ceiling.jpg',
            // Plumbers
            'water-tank-cleaning'                        => 'refixel-water-tank-cleaning.jpg',
            'toilet-sanitary-fitting'                    => 'service-plumber.jpg',
            'washbasin-sink-drainage-repair'             => 'refixel-plumber.jpg',
            'water-motor-pump-repair'                    => 'service-plumber.jpg',
            // Carpenter
            'door-lock-handle-repair'                    => 'refixel-carpenter.jpg',
            'kitchen-drawer-channel-cabinet-repair'      => 'service-carpenter.jpg',
            'mosquito-mesh-net-installation'             => 'refixel-carpenter.jpg',
            'furniture-polish-surface-restoration'       => 'service-carpenter.jpg',
            // AC Service & Repair
            'ac-gas-leakage-refill'                      => 'ac-repair.jpg',
            'split-ac-installation-mounting'             => 'refixel-ac-service.jpg',
            'ac-uninstallation-safe-packing'             => 'service-ac-repair.jpg',
            'ac-pcb-electrical-capacitor-repair'         => 'ac-repair.jpg',
            // Appliance Repair
            'washing-machine-repair-service'             => 'service-appliance-repair.jpg',
            'refrigerator-fridge-repair-service'         => 'refixel-appliance-repair.jpg',
            'ro-water-purifier-service-filter'           => 'service-appliance-repair.jpg',
            'microwave-otg-heating-repair'               => 'refixel-appliance-repair.jpg',
            // Painting Services
            'exterior-weatherproof-painting'             => 'service-painting.jpg',
            'wall-waterproofing-dampness-treatment'      => 'refixel-painting.jpg',
            'designer-accent-texture-wall-painting'      => 'painting.jpg',
            // Pest Control
            'termite-deemak-control-treatment'           => 'refixel-pest-control.jpg',
            'bed-bug-khatmal-intensive-treatment'        => 'service-pest-control.jpg',
            'mosquito-fly-general-pest-fogging'          => 'pest-control.jpg',
            // Fall Ceiling
            'grid-ceiling-acoustic-tile-installation'    => 'refixel-fall-ceiling.jpg',
            // Distinct Add-on Services
            'chimney-exhaust-degreasing'                 => 'addon-chimney-degreasing.jpg',
            'kitchen-chimney-degreasing'                 => 'addon-chimney-degreasing.jpg',
            'microwave-oven-deep-clean'                  => 'addon-microwave-oven-deep-clean.jpg',
            'refrigerator-interior-sanitization'         => 'addon-refrigerator-interior-sanitization.jpg',
            'bathroom-tile-stain-removal'                => 'addon-bathroom-tile-stain-removal.jpg',
            'tap-shower-chrome-descaling'                => 'addon-tap-shower-chrome-descaling.jpg',
            'bathroom-exhaust-fan-clean'                 => 'addon-bathroom-exhaust-fan-clean.jpg',
            'bathroom-exhaust-fan-detailing'             => 'addon-bathroom-exhaust-fan-clean.jpg',
            'mattress-steam-cleaning'                    => 'addon-mattress-steam-cleaning.jpg',
            'ceiling-fan-chandelier-detailing'           => 'addon-ceiling-fan-chandelier-detailing.jpg',
            'ac-antibacterial-foam-wash'                 => 'addon-ac-antibacterial-foam-wash.jpg',
            'drain-unclogging-blockage-removal'          => 'addon-drain-unclogging-blockage-removal.jpg',
            'fan-cleaning-addon'                         => 'addon-ceiling-fan-chandelier-detailing.jpg',
            'wash-basin-cleaning-addon'                  => 'addon-tap-shower-chrome-descaling.jpg',
            'glass-cleaning-upto-30sqft'                 => 'addon-window-glass-cleaning.jpg',
            'single-bed-mattress-cleaning'               => 'addon-mattress-steam-cleaning.jpg',
            'double-bed-mattress-cleaning'               => 'addon-mattress-steam-cleaning.jpg',
            'small-kitchen-appliances-cleaning'          => 'addon-small-kitchen-appliances.jpg',
            'gas-stove-burner-degreasing'                => 'addon-chimney-degreasing.jpg',
            'kitchen-slab-tile-degreasing'               => 'addon-chimney-degreasing.jpg',
            'balcony-railing-grill-dusting'              => 'refixel-balcony-pressure-wash.png',
        ];

        $cur = trim((string)$currentImage);
        $genericImages = [
            'refixel-cleaning.jpg',
            'refixel-kitchen-cleaning.jpg',
            'refixel-bathroom-cleaning.jpg',
            'refixel-ac-service.jpg',
            'refixel-plumber.jpg',
            'Full-home-clean.jpg',
            '5_1_Full-home-clean.jpg',
        ];

        if (isset($map[$slug])) {
            if (empty($cur) || (in_array($cur, $genericImages, true) && !in_array($slug, ['full-home-cleaning', 'kitchen-deep-cleaning', 'bathroom-deep-cleaning', 'ac-jet-service', 'tap-leak-repair'], true))) {
                return $map[$slug];
            }
        }

        if (!empty($cur)) {
            return $cur;
        }

        return $map[$slug] ?? 'refixel-cleaning.jpg';
    }

    public static function ensureServiceSchema(): void
    {
        static $ensured = false;
        if ($ensured) return;
        $ensured = true;

        try {
            $cols = Database::fetchAll("SHOW COLUMNS FROM services");
            $fields = array_column($cols, 'Field');
            if (!in_array('is_addon', $fields, true)) {
                Database::execute("ALTER TABLE services ADD COLUMN is_addon TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
            }
            if (!in_array('is_coming_soon', $fields, true)) {
                Database::execute("ALTER TABLE services ADD COLUMN is_coming_soon TINYINT(1) NOT NULL DEFAULT 0 AFTER is_addon");
            }
        } catch (\Throwable $e) {}

        try {
            Database::execute("UPDATE services SET is_addon = 1 WHERE slug IN (
                'chimney-exhaust-degreasing',
                'kitchen-chimney-degreasing',
                'microwave-oven-deep-clean',
                'refrigerator-interior-sanitization',
                'bathroom-tile-stain-removal',
                'tap-shower-chrome-descaling',
                'bathroom-exhaust-fan-clean',
                'bathroom-exhaust-fan-detailing',
                'mattress-steam-cleaning',
                'ceiling-fan-chandelier-detailing',
                'ac-antibacterial-foam-wash',
                'drain-unclogging-blockage-removal',
                'fan-cleaning-addon',
                'wash-basin-cleaning-addon',
                'glass-cleaning-upto-30sqft',
                'single-bed-mattress-cleaning',
                'double-bed-mattress-cleaning',
                'small-kitchen-appliances-cleaning',
                'gas-stove-burner-degreasing',
                'kitchen-slab-tile-degreasing',
                'balcony-railing-grill-dusting'
            ) OR id IN (52, 53, 54, 55, 56, 57, 58, 59, 60, 61)");
        } catch (\Throwable $e) {}

        try {
            $addonImageUpdates = [
                'addon-chimney-degreasing.jpg'                 => ['chimney-exhaust-degreasing', 'kitchen-chimney-degreasing'],
                'addon-microwave-oven-deep-clean.jpg'          => ['microwave-oven-deep-clean'],
                'addon-refrigerator-interior-sanitization.jpg' => ['refrigerator-interior-sanitization'],
                'addon-bathroom-tile-stain-removal.jpg'        => ['bathroom-tile-stain-removal'],
                'addon-tap-shower-chrome-descaling.jpg'        => ['tap-shower-chrome-descaling'],
                'addon-bathroom-exhaust-fan-clean.jpg'         => ['bathroom-exhaust-fan-clean', 'bathroom-exhaust-fan-detailing'],
                'addon-mattress-steam-cleaning.jpg'            => ['mattress-steam-cleaning'],
                'addon-ceiling-fan-chandelier-detailing.jpg'   => ['ceiling-fan-chandelier-detailing'],
                'addon-ac-antibacterial-foam-wash.jpg'         => ['ac-antibacterial-foam-wash'],
                'addon-drain-unclogging-blockage-removal.jpg'  => ['drain-unclogging-blockage-removal'],
            ];
            foreach ($addonImageUpdates as $img => $slugs) {
                $in = "'" . implode("','", $slugs) . "'";
                Database::execute("UPDATE services SET image = '{$img}' WHERE slug IN ({$in})");
            }
        } catch (\Throwable $e) {}

        try {
            $elecServices = [
                [
                    'category_id'      => 7,
                    'name'             => 'Light & Fitting Installation',
                    'slug'             => 'light-fitting-installation',
                    'description'      => 'Bulb, tube light, LED panel, chandelier, wall light fitting, holder aur wiring fix',
                    'starting_price'   => 149.00,
                    'duration_minutes' => 45,
                    'image'            => 'service-light-fitting-installation.jpg',
                ],
                [
                    'category_id'      => 7,
                    'name'             => 'Wiring & MCB Repair',
                    'slug'             => 'wiring-mcb-repair',
                    'description'      => 'Short circuit fix, MCB / fuse / DB box change, burnt wire replace, loose connection check, earthing check',
                    'starting_price'   => 249.00,
                    'duration_minutes' => 60,
                    'image'            => 'service-wiring-mcb-repair.jpg',
                ],
                [
                    'category_id'      => 7,
                    'name'             => 'Appliance Installation',
                    'slug'             => 'appliance-installation',
                    'description'      => 'Geyser, AC electrical point, exhaust fan, cooler, water pump aur washing machine ka connection',
                    'starting_price'   => 299.00,
                    'duration_minutes' => 60,
                    'image'            => 'service-appliance-installation.jpg',
                ],
            ];
            foreach ($elecServices as $es) {
                $chk = Database::fetchOne("SELECT id FROM services WHERE slug = :s", ['s' => $es['slug']]);
                if (!$chk) {
                    Database::execute(
                        "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active, is_addon, created_at)
                         VALUES (:cid, :name, :slug, :desc, :price, :duration, :img, 1, 0, NOW())",
                        [
                            'cid'      => $es['category_id'],
                            'name'     => $es['name'],
                            'slug'     => $es['slug'],
                            'desc'     => $es['description'],
                            'price'    => $es['starting_price'],
                            'duration' => $es['duration_minutes'],
                            'img'      => $es['image'],
                        ]
                    );
                } else {
                    Database::execute(
                        "UPDATE services SET image = :img, is_addon = 0, is_active = 1 WHERE slug = :s",
                        ['img' => $es['image'], 's' => $es['slug']]
                    );
                }
            }
            Database::execute("UPDATE services SET image = 'service-fan-switchboard-repair.jpg' WHERE slug = 'fan-switchboard-repair' AND (image IS NULL OR image = '' OR image = 'refixel-electrician.jpg')");
        } catch (\Throwable $e) {}

        try {
            $hasNewServices = Database::fetchOne("SELECT id FROM services WHERE slug = 'water-tank-cleaning'");
            if (!$hasNewServices) {
                $seederFile = dirname(__DIR__, 2) . '/database/add_comprehensive_category_services.php';
                if (file_exists($seederFile)) {
                    require_once $seederFile;
                    if (function_exists('seedComprehensiveCategoryServices')) {
                        seedComprehensiveCategoryServices();
                    }
                }
            }
            // Ensure Water Tank Cleaning is strictly categorized under Cleaning (id: 1), not Plumbers (id: 4)
            Database::execute("UPDATE services SET category_id = 1 WHERE slug = 'water-tank-cleaning' OR name LIKE '%Tank Cleaning%'");
        } catch (\Throwable $e) {}

        try {
            $hasDiscountAddons = Database::fetchOne("SELECT id FROM services WHERE slug = 'fan-cleaning-addon'");
            if (!$hasDiscountAddons) {
                $discountSeeder = dirname(__DIR__, 2) . '/database/add_image_addons_and_discount_pricing.php';
                if (file_exists($discountSeeder)) {
                    require_once $discountSeeder;
                    if (function_exists('seedImageAddonsAndDiscountPricing')) {
                        seedImageAddonsAndDiscountPricing();
                    }
                }
            }
        } catch (\Throwable $e) {}
    }

    public static function getActive(bool $includeAddons = false): array
    {
        self::ensureServiceSchema();
        $addonCondition = $includeAddons ? "" : " AND (s.is_addon = 0 OR s.is_addon IS NULL)";
        $rows = Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1 {$addonCondition}
             ORDER BY c.sort_order ASC, s.name ASC"
        );
        foreach ($rows as &$row) {
            $row['image'] = self::resolveImage($row['slug'] ?? '', $row['image'] ?? null);
        }
        return $rows;
    }

    public static function getActiveByCategory(int $categoryId, bool $includeAddons = false): array
    {
        self::ensureServiceSchema();
        $addonCondition = $includeAddons ? "" : " AND (is_addon = 0 OR is_addon IS NULL)";
        $rows = Database::fetchAll(
            "SELECT * FROM services WHERE category_id = :cid AND is_active = 1 {$addonCondition} ORDER BY name ASC",
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

    public static function search(string $query, ?int $limit = 10, bool $includeAddons = false): array
    {
        self::ensureServiceSchema();
        $q = trim(strtolower($query));
        if (strlen($q) < 2) {
            return [];
        }

        $addonCondition = $includeAddons ? "" : " AND (s.is_addon = 0 OR s.is_addon IS NULL)";
        $allServices = Database::fetchAll(
            "SELECT s.*, c.name as category_name, c.slug as category_slug 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1 {$addonCondition}"
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

    /**
     * Curated high-conversion complementary service pairings.
     */
    protected static array $curatedAddonMap = [
        1  => [4, 7, 48, 2],    // Full Home Cleaning -> Sofa Cleaning, Pest Control, Balcony Wash, Bathroom Cleaning
        2  => [8, 3, 7, 11],    // Bathroom Cleaning -> Tap Leak, Kitchen Cleaning, Pest Control, Switchboard Repair
        3  => [7, 8, 2, 4],     // Kitchen Deep Cleaning -> Pest Control, Tap Leak, Bathroom Cleaning, Sofa Cleaning
        4  => [1, 48, 7, 2],    // Sofa Cleaning -> Full Home Clean, Balcony Wash, Pest Control, Bathroom Cleaning
        48 => [1, 4, 2, 8],     // Balcony Pressure Wash -> Full Home Clean, Sofa Cleaning, Bathroom Cleaning, Tap Leak
        5  => [4, 10, 7, 11],   // Commercial Space -> Sofa Cleaning, AC Jet Service, Pest Control, Switchboard Repair
        6  => [14, 11, 1, 13],  // Interior Painting -> Fall Ceiling Repair, Switchboard Repair, Full Home Clean, Fall Ceiling Install
        13 => [11, 6, 14, 1],   // Fall Ceiling Install -> Switchboard Repair, Painting, Cove Light, Full Home Clean
        14 => [11, 13, 6, 1],   // Fall Ceiling Repair -> Switchboard Repair, Fall Ceiling Install, Painting, Full Home Clean
        8  => [2, 3, 11, 12],   // Tap Leak Repair -> Bathroom Cleaning, Kitchen Cleaning, Switchboard Repair, Appliance Diagnostic
        9  => [11, 4, 12, 8],   // Furniture Assembly -> Switchboard Repair, Sofa Cleaning, Appliance Diagnostic, Tap Leak
        10 => [11, 12, 2, 4],   // AC Jet Service -> Switchboard Repair, Appliance Diagnostic, Bathroom Cleaning, Sofa Cleaning
        11 => [10, 12, 8, 2],   // Switchboard Repair -> AC Jet Service, Appliance Diagnostic, Tap Leak, Bathroom Cleaning
        12 => [11, 10, 8, 3],   // Appliance Diagnostic -> Switchboard Repair, AC Jet Service, Tap Leak, Kitchen Cleaning
        7  => [3, 2, 1, 48],    // Cockroach & Pest Control -> Kitchen Cleaning, Bathroom Cleaning, Full Home Clean, Balcony Wash
    ];

    /**
     * Get smart addon recommendations for a single service.
     */
    public static function getAddonRecommendations(int $serviceId, array $excludeIds = [], int $limit = 3): array
    {
        return ServiceAddon::forService($serviceId, $excludeIds, $limit);
    }

    /**
     * Get aggregate addon recommendations for the entire cart.
     */
    public static function getCartAddonRecommendations(array $cartServiceIds, int $limit = 4): array
    {
        $excludeMap = array_fill_keys(array_map('intval', $cartServiceIds), true);
        $candidateScores = [];
        $candidateItems = [];

        foreach ($cartServiceIds as $sid) {
            $sid = (int)$sid;
            $items = ServiceAddon::forService($sid, $cartServiceIds, $limit);
            $weight = count($items);
            foreach ($items as $item) {
                $aid = (int)$item['id'];
                if (isset($excludeMap[$aid])) {
                    continue;
                }
                $candidateScores[$aid] = ($candidateScores[$aid] ?? 0) + $weight;
                if (!isset($candidateItems[$aid])) {
                    $candidateItems[$aid] = $item;
                }
                $weight--;
            }
        }

        arsort($candidateScores);

        $results = [];
        foreach (array_keys($candidateScores) as $aid) {
            if (isset($candidateItems[$aid])) {
                $results[] = $candidateItems[$aid];
                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        // Fallback if cart was empty or had no pairings
        if (count($results) < $limit) {
            $fallback = ServiceAddon::forService(1, $cartServiceIds, $limit - count($results));
            foreach ($fallback as $fb) {
                $aid = (int)$fb['id'];
                if (!isset($excludeMap[$aid]) && !isset($candidateScores[$aid])) {
                    $results[] = $fb;
                    $candidateScores[$aid] = 1;
                    if (count($results) >= $limit) break;
                }
            }
        }

        return array_slice($results, 0, $limit);
    }
}
