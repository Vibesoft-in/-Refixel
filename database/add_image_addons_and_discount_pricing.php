<?php
declare(strict_types=1);

use App\Core\Database;

if (!function_exists('seedImageAddonsAndDiscountPricing')) {
    function seedImageAddonsAndDiscountPricing(): array
    {
        $updatedCount = 0;
        $insertedCount = 0;

        // 1. Update existing add-ons mentioned in picture to be 5-10% lower than picture
        // - Exhaust Cleaning (Pic: ₹90 -> 10% less = ₹80.00)
        // - Refrigerator Cleaning (Pic: ₹200 -> 10% less = ₹180.00)
        // - Oven Cleaning (Pic: ₹100 -> 10% less = ₹90.00)
        $existingUpdates = [
            'bathroom-exhaust-fan-clean' => [
                'name'  => 'Exhaust Fan Deep Clean',
                'price' => 80.00,
                'pair_price' => 75.00,
            ],
            'refrigerator-interior-sanitization' => [
                'name'  => 'Refrigerator Interior Sanitization',
                'price' => 180.00,
                'pair_price' => 169.00,
            ],
            'microwave-oven-deep-clean' => [
                'name'  => 'Microwave & Oven Interior Deep Clean',
                'price' => 90.00,
                'pair_price' => 85.00,
            ],
        ];

        foreach ($existingUpdates as $slug => $info) {
            $row = Database::fetchOne("SELECT id FROM services WHERE slug = :s", ['s' => $slug]);
            if ($row) {
                Database::execute(
                    "UPDATE services SET starting_price = :p, is_addon = 1 WHERE id = :id",
                    ['p' => $info['price'], 'id' => $row['id']]
                );
                Database::execute(
                    "UPDATE service_addons SET custom_price = :cp WHERE addon_service_id = :aid",
                    ['cp' => $info['pair_price'], 'aid' => $row['id']]
                );
                $updatedCount++;
            }
        }

        // 2. New Add-ons from Picture (strictly 5-10% below picture prices) + companion additions
        $newAddons = [
            [
                'name'             => 'Fan Cleaning (Per Fan)',
                'slug'             => 'fan-cleaning-addon',
                'category_id'      => 1, // Cleaning
                'description'      => 'Ceiling fan or table fan blade wiping, degreasing, and static dust removal.',
                'starting_price'   => 45.00, // Pic ₹50 -> 10% lower = ₹45
                'pair_price'       => 39.00,
                'duration_minutes' => 15,
                'image'            => 'addon-ceiling-fan-chandelier-detailing.jpg',
            ],
            [
                'name'             => 'Wash Basin Deep Cleaning & Scale Removal',
                'slug'             => 'wash-basin-cleaning-addon',
                'category_id'      => 1, // Cleaning
                'description'      => 'Acid-free hard water scale removal, chrome faucet polish, and drainage ring sanitization.',
                'starting_price'   => 72.00, // Pic ₹80 -> 10% lower = ₹72
                'pair_price'       => 65.00,
                'duration_minutes' => 20,
                'image'            => 'addon-tap-shower-chrome-descaling.jpg',
            ],
            [
                'name'             => 'Window & Mirror Glass Cleaning (Upto 30 Sqft)',
                'slug'             => 'glass-cleaning-upto-30sqft',
                'category_id'      => 1, // Cleaning
                'description'      => 'Streak-free microfiber cleaning and grime removal for windows, mirrors, or glass partitions (up to 30 sq.ft).',
                'starting_price'   => 45.00, // Pic ₹50 -> 10% lower = ₹45
                'pair_price'       => 39.00,
                'duration_minutes' => 20,
                'image'            => 'addon-window-glass-cleaning.jpg',
            ],
            [
                'name'             => 'Single Bed Mattress Steam Cleaning',
                'slug'             => 'single-bed-mattress-cleaning',
                'category_id'      => 1, // Cleaning
                'description'      => 'High-temperature dry steam extraction and anti-dust mite sanitization for single bed mattress.',
                'starting_price'   => 360.00, // Pic ₹400 -> 10% lower = ₹360
                'pair_price'       => 329.00,
                'duration_minutes' => 30,
                'image'            => 'addon-mattress-steam-cleaning.jpg',
            ],
            [
                'name'             => 'Double Bed Mattress Steam Cleaning',
                'slug'             => 'double-bed-mattress-cleaning',
                'category_id'      => 1, // Cleaning
                'description'      => 'Full double / king-size mattress steam extraction, sweat stain treatment, and antibacterial deodorization.',
                'starting_price'   => 630.00, // Pic ₹700 -> 10% lower = ₹630
                'pair_price'       => 599.00,
                'duration_minutes' => 45,
                'image'            => 'addon-mattress-steam-cleaning.jpg',
            ],
            [
                'name'             => 'Small Kitchen Appliances Cleaning (Toaster, Air Fryer, Mixer)',
                'slug'             => 'small-kitchen-appliances-cleaning',
                'category_id'      => 1, // Cleaning
                'description'      => 'Degreasing and exterior/interior food-safe wipe of toaster, sandwich maker, air fryer, mixer, or electric kettle.',
                'starting_price'   => 63.00, // Pic ₹70 -> 10% lower = ₹63
                'pair_price'       => 59.00,
                'duration_minutes' => 20,
                'image'            => 'addon-small-kitchen-appliances.jpg',
            ],
            // Additional popular companions in the same style & budget
            [
                'name'             => 'Gas Stove & Burner Deep Degreasing',
                'slug'             => 'gas-stove-burner-degreasing',
                'category_id'      => 1, // Cleaning
                'description'      => 'Carbon removal, burner brass nozzle unclogging, grease scraping, and stainless steel / glass cooktop wipe.',
                'starting_price'   => 75.00,
                'pair_price'       => 69.00,
                'duration_minutes' => 20,
                'image'            => 'addon-chimney-degreasing.jpg',
            ],
            [
                'name'             => 'Kitchen Slab & Wall Tile Degreasing',
                'slug'             => 'kitchen-slab-tile-degreasing',
                'category_id'      => 1, // Cleaning
                'description'      => 'Oil splatter removal, granite countertop buffing, and tile grout cleaning behind the cooktop.',
                'starting_price'   => 85.00,
                'pair_price'       => 79.00,
                'duration_minutes' => 25,
                'image'            => 'addon-chimney-degreasing.jpg',
            ],
            [
                'name'             => 'Balcony Railing & Window Grill Dusting',
                'slug'             => 'balcony-railing-grill-dusting',
                'category_id'      => 1, // Cleaning
                'description'      => 'Dust extraction and wet wipe of balcony metal railings, window safety grills, and mesh frames.',
                'starting_price'   => 55.00,
                'pair_price'       => 49.00,
                'duration_minutes' => 20,
                'image'            => 'refixel-balcony-pressure-wash.png',
            ],
        ];

        foreach ($newAddons as $item) {
            $existing = Database::fetchOne("SELECT id FROM services WHERE slug = :s", ['s' => $item['slug']]);
            $serviceId = 0;
            if (!$existing) {
                Database::execute(
                    "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active, is_addon, is_coming_soon, created_at)
                     VALUES (:cid, :name, :slug, :desc, :price, :duration, :img, 1, 1, 0, NOW())",
                    [
                        'cid'      => $item['category_id'],
                        'name'     => $item['name'],
                        'slug'     => $item['slug'],
                        'desc'     => $item['description'],
                        'price'    => $item['starting_price'],
                        'duration' => $item['duration_minutes'],
                        'img'      => $item['image'],
                    ]
                );
                $serviceId = (int)Database::lastInsertId();
                $insertedCount++;
            } else {
                $serviceId = (int)$existing['id'];
                Database::execute(
                    "UPDATE services SET starting_price = :price, is_addon = 1, is_active = 1, image = :img WHERE id = :id",
                    [
                        'price' => $item['starting_price'],
                        'img'   => $item['image'],
                        'id'    => $serviceId,
                    ]
                );
            }
        }

        // Helper to get service id by slug
        $getId = function (string $slug): ?int {
            $r = Database::fetchOne("SELECT id FROM services WHERE slug = :s", ['s' => $slug]);
            return $r ? (int)$r['id'] : null;
        };

        // 3. Sensible pairings in `service_addons`
        $pairings = [
            // Kitchen pairings
            ['kitchen-deep-cleaning', 'small-kitchen-appliances-cleaning', '⭐ Popular Kitchen Add-on', 'Food-safe appliance scrub', 59.00, 6],
            ['kitchen-deep-cleaning', 'gas-stove-burner-degreasing', '🔥 Cooktop Special', 'Burner nozzle & grease clear', 69.00, 7],
            ['kitchen-deep-cleaning', 'kitchen-slab-tile-degreasing', '✨ Countertop Shine', 'Granite & backsplash oil-free', 79.00, 8],

            // Bathroom pairings
            ['bathroom-deep-cleaning', 'wash-basin-cleaning-addon', '🧼 Basin Descale', 'Mirror faucet & stain removal', 65.00, 6],

            // Full Home pairings
            ['full-home-cleaning', 'fan-cleaning-addon', '💨 Dust Free Fan', 'Static dust wipe per fan', 39.00, 5],
            ['full-home-cleaning', 'glass-cleaning-upto-30sqft', '🪟 Crystal Glass', 'Streak-free window wipe', 39.00, 6],
            ['full-home-cleaning', 'single-bed-mattress-cleaning', '🛏️ Single Mattress', 'Steam mite extraction', 329.00, 7],
            ['full-home-cleaning', 'double-bed-mattress-cleaning', '👑 King Mattress', 'Double bed steam clean', 599.00, 8],
            ['full-home-cleaning', 'balcony-railing-grill-dusting', '🌿 Railing & Grill', 'Balcony metal grill wet wipe', 49.00, 9],
            ['full-home-cleaning', 'wash-basin-cleaning-addon', '✨ Basin Descaling', 'Chrome polish & scale removal', 65.00, 10],

            // Sofa Cleaning pairings
            ['sofa-cleaning', 'single-bed-mattress-cleaning', '🛏️ Mattress Add-on', 'Single bed steam treatment', 329.00, 5],
            ['sofa-cleaning', 'double-bed-mattress-cleaning', '👑 Double Mattress', 'Double bed steam clean', 599.00, 6],
            ['sofa-cleaning', 'fan-cleaning-addon', '💨 Living Room Fan', 'Blade wipe & degreasing', 39.00, 7],

            // Balcony Pressure Wash pairings (maintains sofa, pest, bath, railing, glass)
            ['balcony-deep-pressure-wash', 'sofa-cleaning', '🔥 Top Pick', 'Upholstery deep extraction', 599.00, 1],
            ['balcony-deep-pressure-wash', 'cockroach-pest-control', '🛡️ Pest Shield', 'Anti-pest barrier', 399.00, 2],
            ['balcony-deep-pressure-wash', 'bathroom-deep-cleaning', '🧼 Bath Sanitization', 'Acid-free tile scrubbing', 399.00, 3],
            ['balcony-deep-pressure-wash', 'balcony-railing-grill-dusting', '🌿 Railing & Grill', 'Balcony metal grill wet wipe', 49.00, 4],
            ['balcony-deep-pressure-wash', 'glass-cleaning-upto-30sqft', '🪟 Glass Door Wipe', 'Sliding glass door polish', 39.00, 5],
        ];

        foreach ($pairings as $p) {
            [$sourceSlug, $addonSlug, $badge, $note, $pairPrice, $sortOrder] = $p;
            $sourceId = $getId($sourceSlug);
            $addonId = $getId($addonSlug);
            if ($sourceId && $addonId) {
                $chk = Database::fetchOne(
                    "SELECT id FROM service_addons WHERE source_service_id = :sid AND addon_service_id = :aid",
                    ['sid' => $sourceId, 'aid' => $addonId]
                );
                if (!$chk) {
                    Database::execute(
                        "INSERT INTO service_addons (source_service_id, addon_service_id, custom_badge, custom_note, custom_price, sort_order, is_active, created_at)
                         VALUES (:sid, :aid, :b, :n, :p, :so, 1, NOW())",
                        [
                            'sid' => $sourceId,
                            'aid' => $addonId,
                            'b'   => $badge,
                            'n'   => $note,
                            'p'   => $pairPrice,
                            'so'  => $sortOrder,
                        ]
                    );
                } else {
                    Database::execute(
                        "UPDATE service_addons SET custom_price = :p, custom_badge = :b, custom_note = :n, sort_order = :so, is_active = 1 WHERE id = :id",
                        [
                            'p'  => $pairPrice,
                            'b'  => $badge,
                            'n'  => $note,
                            'so' => $sortOrder,
                            'id' => $chk['id'],
                        ]
                    );
                }
            }
        }

        return [
            'updated'  => $updatedCount,
            'inserted' => $insertedCount,
        ];
    }
}

if (php_sapi_name() === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    require_once __DIR__ . '/../tests/bootstrap.php';
    try {
        echo "=== Seeding Image Addons and Discounted Pricing ===\n";
        $res = seedImageAddonsAndDiscountPricing();
        echo "Updated {$res['updated']} existing addons to discounted prices.\n";
        echo "Inserted {$res['inserted']} new add-on services and pairings.\n";
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
