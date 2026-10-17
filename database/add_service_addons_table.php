<?php
declare(strict_types=1);

/**
 * Migration: Create service_addons table, insert sensible add-on catalog items, and seed sensible pairings.
 */

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;
use App\Models\Setting;

echo "=== MIGRATION: SERVICE ADDONS MANAGEMENT & SENSIBLE PAIRINGS ===\n";

// 1. Create service_addons table
Database::query("
    CREATE TABLE IF NOT EXISTS service_addons (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        source_service_id INT UNSIGNED NULL,
        source_category_id INT UNSIGNED NULL,
        addon_service_id INT UNSIGNED NOT NULL,
        custom_badge VARCHAR(80) NULL DEFAULT '🔥 Popular Add-on',
        custom_price DECIMAL(10,2) NULL,
        custom_note VARCHAR(150) NULL DEFAULT 'Special Companion Rate',
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_source_service (source_service_id),
        INDEX idx_source_category (source_category_id),
        INDEX idx_addon_service (addon_service_id),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'service_addons' ensured.\n";

// 2. Ensure default setting for auto-sensible add-ons engine
if (Setting::get('addons_auto_sensible_enabled') === null) {
    Setting::set('addons_auto_sensible_enabled', '1');
    echo "✓ Default setting 'addons_auto_sensible_enabled' set to 1.\n";
}

// 3. Insert Sensible Add-on Services into `services` if they don't already exist
$sensibleServices = [
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Chimney & Exhaust Hood Degreasing',
        'slug'             => 'chimney-exhaust-degreasing',
        'description'      => 'High-temperature food-grade degreasing of chimney filters, baffles, motor casing, and exhaust ducting.',
        'starting_price'   => 299.00,
        'duration_minutes' => 30,
        'image'            => 'refixel-kitchen-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Microwave & Oven Interior Deep Clean',
        'slug'             => 'microwave-oven-deep-clean',
        'description'      => 'Steam sterilization, burnt carbon removal, and food-safe wipe of oven racks, glass turntables, and cavity.',
        'starting_price'   => 199.00,
        'duration_minutes' => 25,
        'image'            => 'refixel-kitchen-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Refrigerator Interior Sanitization',
        'slug'             => 'refrigerator-interior-sanitization',
        'description'      => 'De-frost scrub, shelf wash, antibacterial deodorization, and door gasket mould removal.',
        'starting_price'   => 249.00,
        'duration_minutes' => 35,
        'image'            => 'refixel-kitchen-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Bathroom Tiles Hard Water Stain Removal',
        'slug'             => 'bathroom-tile-stain-removal',
        'description'      => 'Specialized acid-free descaling paste treatment for wall tiles, glass partitions, and grout lines.',
        'starting_price'   => 249.00,
        'duration_minutes' => 30,
        'image'            => 'refixel-bathroom-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Tap & Shower Head Chrome Descaling Polish',
        'slug'             => 'tap-shower-chrome-descaling',
        'description'      => 'Deep scale dissolution, spray nozzle unclogging, and mirror chrome shine polish for all bathroom fittings.',
        'starting_price'   => 149.00,
        'duration_minutes' => 20,
        'image'            => 'refixel-bathroom-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Bathroom Exhaust Fan Deep Clean',
        'slug'             => 'bathroom-exhaust-fan-clean',
        'description'      => 'Removal of accumulated dust, moisture grime, blade degreasing, and motor lubrication.',
        'starting_price'   => 129.00,
        'duration_minutes' => 20,
        'image'            => 'refixel-bathroom-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Mattress Steam Deep Cleaning & Sanitization',
        'slug'             => 'mattress-steam-cleaning',
        'description'      => 'High-pressure steam vacuuming to eliminate dust mites, allergens, body sweat stains, and odor.',
        'starting_price'   => 499.00,
        'duration_minutes' => 45,
        'image'            => 'refixel-cleaning.jpg',
    ],
    [
        'category_id'      => 1, // Cleaning
        'name'             => 'Ceiling Fan & Chandelier Detailing',
        'slug'             => 'ceiling-fan-chandelier-detailing',
        'description'      => 'Static dust extraction, blade degreasing, crystal/glass wipe, and motor housing polish.',
        'starting_price'   => 199.00,
        'duration_minutes' => 25,
        'image'            => 'refixel-cleaning.jpg',
    ],
    [
        'category_id'      => 6, // AC Services
        'name'             => 'AC Anti-Bacterial Duct & Coil Foam Wash',
        'slug'             => 'ac-antibacterial-foam-wash',
        'description'      => 'Germicidal active foam treatment to eliminate indoor mold, bacteria, foul smell, and allergen buildup.',
        'starting_price'   => 199.00,
        'duration_minutes' => 25,
        'image'            => 'refixel-ac-service.jpg',
    ],
    [
        'category_id'      => 4, // Plumbers
        'name'             => 'Drain Unclogging & Blockage Removal',
        'slug'             => 'drain-unclogging-blockage-removal',
        'description'      => 'Heavy-duty mechanical spring snaking and organic enzyme flush to clear slow kitchen or bathroom drains.',
        'starting_price'   => 199.00,
        'duration_minutes' => 30,
        'image'            => 'refixel-plumber.jpg',
    ],
];

foreach ($sensibleServices as $s) {
    $exists = Database::fetchOne("SELECT id FROM services WHERE slug = :slug", ['slug' => $s['slug']]);
    if (!$exists) {
        Database::query(
            "INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active, created_at)
             VALUES (:category_id, :name, :slug, :description, :starting_price, :duration_minutes, :image, 1, NOW())",
            $s
        );
        echo "✓ Added service: {$s['name']} (slug: {$s['slug']})\n";
    }
}

// 4. Helper to resolve service ID by slug
$getServiceId = function(string $slug): ?int {
    $r = Database::fetchOne("SELECT id FROM services WHERE slug = :slug", ['slug' => $slug]);
    return $r ? (int)$r['id'] : null;
};

// 5. Seed default sensible pairings in `service_addons`
$pairings = [
    // --- Kitchen Deep Cleaning (Service slug: kitchen-deep-cleaning) ---
    [
        'source_slug'  => 'kitchen-deep-cleaning',
        'addon_slug'   => 'chimney-exhaust-degreasing',
        'badge'        => '🔥 Kitchen Must-Have',
        'note'         => 'Heavy oil & grease degreasing',
        'price'        => 249.00,
        'sort_order'   => 1,
    ],
    [
        'source_slug'  => 'kitchen-deep-cleaning',
        'addon_slug'   => 'microwave-oven-deep-clean',
        'badge'        => '⭐ Frequently Added',
        'note'         => 'Burnt carbon & steam scrub',
        'price'        => 179.00,
        'sort_order'   => 2,
    ],
    [
        'source_slug'  => 'kitchen-deep-cleaning',
        'addon_slug'   => 'refrigerator-interior-sanitization',
        'badge'        => '❄️ Pure Hygiene',
        'note'         => 'Anti-bacterial deodorization',
        'price'        => 199.00,
        'sort_order'   => 3,
    ],
    [
        'source_slug'  => 'kitchen-deep-cleaning',
        'addon_slug'   => 'cockroach-pest-control',
        'badge'        => '🛡️ Cabinet Protection',
        'note'         => 'Herbal pest gel baiting',
        'price'        => 299.00,
        'sort_order'   => 4,
    ],
    [
        'source_slug'  => 'kitchen-deep-cleaning',
        'addon_slug'   => 'drain-unclogging-blockage-removal',
        'badge'        => '⚡ Free Flow Sink',
        'note'         => 'Clears slow kitchen drain',
        'price'        => 149.00,
        'sort_order'   => 5,
    ],

    // --- Professional Bathroom Cleaning (Service slug: bathroom-deep-cleaning) ---
    [
        'source_slug'  => 'bathroom-deep-cleaning',
        'addon_slug'   => 'bathroom-tile-stain-removal',
        'badge'        => '🧼 Scale Remover',
        'note'         => 'Acid-free tile & glass descaling',
        'price'        => 199.00,
        'sort_order'   => 1,
    ],
    [
        'source_slug'  => 'bathroom-deep-cleaning',
        'addon_slug'   => 'tap-shower-chrome-descaling',
        'badge'        => '✨ Mirror Shine',
        'note'         => 'Deep chrome polish & nozzle unclog',
        'price'        => 129.00,
        'sort_order'   => 2,
    ],
    [
        'source_slug'  => 'bathroom-deep-cleaning',
        'addon_slug'   => 'bathroom-exhaust-fan-clean',
        'badge'        => '💨 Fresh Air',
        'note'         => 'Blade moisture & grime wipe',
        'price'        => 99.00,
        'sort_order'   => 3,
    ],
    [
        'source_slug'  => 'bathroom-deep-cleaning',
        'addon_slug'   => 'tap-leak-repair',
        'badge'        => '🔧 Tap Repair',
        'note'         => 'Stops dripping taps & pipes',
        'price'        => 149.00,
        'sort_order'   => 4,
    ],
    [
        'source_slug'  => 'bathroom-deep-cleaning',
        'addon_slug'   => 'drain-unclogging-blockage-removal',
        'badge'        => '⚡ Odor-Free Drain',
        'note'         => 'Enzyme flush & blockage removal',
        'price'        => 149.00,
        'sort_order'   => 5,
    ],

    // --- Professional Full Home Cleaning (Service slug: full-home-cleaning) ---
    [
        'source_slug'  => 'full-home-cleaning',
        'addon_slug'   => 'sofa-cleaning',
        'badge'        => '🛋️ Companion Special',
        'note'         => 'Deep foam shampooing',
        'price'        => 599.00,
        'sort_order'   => 1,
    ],
    [
        'source_slug'  => 'full-home-cleaning',
        'addon_slug'   => 'cockroach-pest-control',
        'badge'        => '🛡️ Complete Shield',
        'note'         => 'Whole house pest prevention',
        'price'        => 499.00,
        'sort_order'   => 2,
    ],
    [
        'source_slug'  => 'full-home-cleaning',
        'addon_slug'   => 'balcony-deep-pressure-wash',
        'badge'        => '🌊 Floor Wash',
        'note'         => 'Balcony pressure jet wash',
        'price'        => 299.00,
        'sort_order'   => 3,
    ],
    [
        'source_slug'  => 'full-home-cleaning',
        'addon_slug'   => 'bathroom-deep-cleaning',
        'badge'        => '✨ Extra Bathroom',
        'note'         => 'Intense tile & ceramic scrub',
        'price'        => 399.00,
        'sort_order'   => 4,
    ],
    [
        'source_slug'  => 'full-home-cleaning',
        'addon_slug'   => 'mattress-steam-cleaning',
        'badge'        => '🛏️ Deep Hygiene',
        'note'         => 'Anti-mite steam sanitization',
        'price'        => 399.00,
        'sort_order'   => 5,
    ],

    // --- Sofa & Upholstery Deep Cleaning (Service slug: sofa-cleaning) ---
    [
        'source_slug'  => 'sofa-cleaning',
        'addon_slug'   => 'mattress-steam-cleaning',
        'badge'        => '🛏️ Steam Sanitization',
        'note'         => 'Removes allergens & sweat marks',
        'price'        => 399.00,
        'sort_order'   => 1,
    ],
    [
        'source_slug'  => 'sofa-cleaning',
        'addon_slug'   => 'ceiling-fan-chandelier-detailing',
        'badge'        => '⚡ Dust-Free Living',
        'note'         => 'Ceiling fan & light cleaning',
        'price'        => 149.00,
        'sort_order'   => 2,
    ],
    [
        'source_slug'  => 'sofa-cleaning',
        'addon_slug'   => 'full-home-cleaning',
        'badge'        => '🏠 Complete Care',
        'note'         => 'Upgrade to full home clean',
        'price'        => 1299.00,
        'sort_order'   => 3,
    ],
    [
        'source_slug'  => 'sofa-cleaning',
        'addon_slug'   => 'cockroach-pest-control',
        'badge'        => '🛡️ Pest Defense',
        'note'         => 'Herbal pest spray for upholstery',
        'price'        => 399.00,
        'sort_order'   => 4,
    ],

    // --- AC High-Pressure Jet Service (Service slug: ac-jet-service) ---
    [
        'source_slug'  => 'ac-jet-service',
        'addon_slug'   => 'ac-antibacterial-foam-wash',
        'badge'        => '❄️ Pure Breath',
        'note'         => 'Active germicidal foam flush',
        'price'        => 149.00,
        'sort_order'   => 1,
    ],
    [
        'source_slug'  => 'ac-jet-service',
        'addon_slug'   => 'fan-switchboard-repair',
        'badge'        => '⚡ Power Safe',
        'note'         => 'MCB & AC plug test',
        'price'        => 99.00,
        'sort_order'   => 2,
    ],
    [
        'source_slug'  => 'ac-jet-service',
        'addon_slug'   => 'appliance-diagnostic-repair',
        'badge'        => '🔍 Health Check',
        'note'         => 'Electrical current draw check',
        'price'        => 199.00,
        'sort_order'   => 3,
    ],

    // --- Tap & Pipe Leak Repair (Service slug: tap-leak-repair) ---
    [
        'source_slug'  => 'tap-leak-repair',
        'addon_slug'   => 'drain-unclogging-blockage-removal',
        'badge'        => '⚡ Instant Flow',
        'note'         => 'Spring snaking blockage clearing',
        'price'        => 149.00,
        'sort_order'   => 1,
    ],
    [
        'source_slug'  => 'tap-leak-repair',
        'addon_slug'   => 'tap-shower-chrome-descaling',
        'badge'        => '✨ Scale Clean',
        'note'         => 'Mirror chrome polishing',
        'price'        => 129.00,
        'sort_order'   => 2,
    ],
    [
        'source_slug'  => 'tap-leak-repair',
        'addon_slug'   => 'bathroom-deep-cleaning',
        'badge'        => '🧼 Spotless Bath',
        'note'         => 'Ceramic & tile deep wash',
        'price'        => 399.00,
        'sort_order'   => 3,
    ],
];

$count = 0;
foreach ($pairings as $p) {
    $src = Database::fetchOne("SELECT id, category_id FROM services WHERE slug = :slug", ['slug' => $p['source_slug']]);
    $srcId = $src ? (int)$src['id'] : null;
    $srcCatId = $src ? (int)$src['category_id'] : null;
    $addId = $getServiceId($p['addon_slug']);

    if ($srcId && $addId) {
        $exists = Database::fetchOne(
            "SELECT id FROM service_addons WHERE source_service_id = :sid AND addon_service_id = :aid",
            ['sid' => $srcId, 'aid' => $addId]
        );
        if (!$exists) {
            Database::query(
                "INSERT INTO service_addons (source_service_id, source_category_id, addon_service_id, custom_badge, custom_price, custom_note, sort_order, is_active)
                 VALUES (:sid, :cid, :aid, :badge, :price, :note, :sort_order, 1)",
                [
                    'sid'        => $srcId,
                    'cid'        => $srcCatId,
                    'aid'        => $addId,
                    'badge'      => $p['badge'],
                    'price'      => $p['price'],
                    'note'       => $p['note'],
                    'sort_order' => $p['sort_order'],
                ]
            );
            $count++;
        }
    }
}

Database::query("UPDATE service_addons sa JOIN services s ON sa.source_service_id = s.id SET sa.source_category_id = s.category_id WHERE sa.source_category_id IS NULL");

echo "✓ Seeded {$count} default sensible add-on pairings.\n";
echo "=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
