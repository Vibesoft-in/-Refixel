<?php
declare(strict_types=1);

require_once __DIR__ . '/../public/index.php';

use App\Core\Database;

try {
    $cols = Database::fetchAll("SHOW COLUMNS FROM categories LIKE 'image'");
    if (empty($cols)) {
        Database::query("ALTER TABLE categories ADD COLUMN image VARCHAR(255) NULL AFTER description");
        echo "Added column 'image' to categories table.\n";
    } else {
        echo "Column 'image' already exists.\n";
    }

    $data = [
        1 => ['image' => 'refixel-cleaning.jpg', 'icon' => 'icons/cleaning icon.png', 'desc' => 'Home, Office, Deep Cleaning Sofa, Carpet, Kitchen, Bathroom'],
        2 => ['image' => 'refixel-painting.jpg', 'icon' => 'icons/painting icon.png', 'desc' => 'Interior & Exterior Wall Painting, Waterproofing, Texture & Stencil'],
        3 => ['image' => 'refixel-fall-ceiling.jpg', 'icon' => 'icons/fall ceiling icon.png', 'desc' => 'POP Ceiling, Gypsum Board, Cove Lighting & False Ceiling Design'],
        4 => ['image' => 'refixel-plumber.jpg', 'icon' => 'icons/plumber icon.png', 'desc' => 'Leak Repair, Pipe Fitting, Taps, Flush, Drainage Bathroom & Kitchen Plumbing'],
        5 => ['image' => 'refixel-carpenter.jpg', 'icon' => 'icons/carpenter icon.png', 'desc' => 'Furniture Repair, Door Lock, Hinges, Modular Kitchen & Woodwork'],
        6 => ['image' => 'refixel-ac-service.jpg', 'icon' => 'icons/ac repair icon.png', 'desc' => 'AC Installation, Uninstallation, Deep Jet Cleaning & Gas Refilling'],
        7 => ['image' => 'refixel-electrician.jpg', 'icon' => 'icons/electrician icon.png', 'desc' => 'Wiring, Switch, Fan, Light, Inverter, LED, Electrical Repairs'],
        8 => ['image' => 'refixel-appliance-repair.jpg', 'icon' => 'icons/appliance repair icon.png', 'desc' => 'TV, Fridge, Washing Machine, Microwave, Mixer & Home Appliances'],
        9 => ['image' => 'refixel-pest-control.jpg', 'icon' => 'icons/pest control icon.png', 'desc' => 'Cockroach, Termite, Bed Bug, Ant & Rodent Treatment with Odourless Spray'],
    ];

    foreach ($data as $id => $row) {
        Database::query(
            "UPDATE categories SET image = :img, icon = :ico, description = :dsc WHERE id = :id",
            ['img' => $row['image'], 'ico' => $row['icon'], 'dsc' => $row['desc'], 'id' => $id]
        );
    }

    echo "Categories updated successfully with image and icons!\n";
} catch (\Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
