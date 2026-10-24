<?php
declare(strict_types=1);

require_once __DIR__ . '/../tests/bootstrap.php';

use App\Core\Database;

try {
    echo "=== Adding Comprehensive Services Across All Categories ===\n";

    $servicesToSeed = [
        // -------------------------------------------------------------
        // CATEGORY 4: Plumbers
        // -------------------------------------------------------------
        [
            'category_id'      => 1, // Category 1: Cleaning
            'name'             => 'Water Tank Deep Cleaning & Disinfection',
            'slug'             => 'water-tank-cleaning',
            'description'      => 'Complete mechanized draining, sludge extraction, high-pressure jet wash, and antibacterial UV/potassium permanganate tank treatment.',
            'starting_price'   => 299.00,
            'duration_minutes' => 90,
            'image'            => 'refixel-water-tank-cleaning.jpg',
            'inclusions'       => [
                'Complete mechanized sludge vacuuming and de-watering',
                'High-pressure rotary jet spray cleaning of interior walls',
                'Antibacterial chemical wash and UV / bleaching powder disinfection',
                'Inlet/outlet pipe debris clearing and lid seal check',
            ],
            'exclusions'       => [
                'Water tank structural crack repair or concrete civil work',
            ],
        ],
        [
            'category_id'      => 4,
            'name'             => 'Toilet & Sanitary Fitting Installation',
            'slug'             => 'toilet-sanitary-fitting',
            'description'      => 'Installation and leak repair for western commode, flush cistern tank, health faucet, angle valve, and jet spray.',
            'starting_price'   => 399.00,
            'duration_minutes' => 60,
            'image'            => 'service-plumber.jpg',
            'inclusions'       => [
                'Western / Indian commode seat and lid mounting',
                'PVC / dual-flush cistern tank overhaul and siphon fitting',
                'Health faucet, jet spray, and brass angle valve connection',
                'Anti-leak silicone seal around toilet base',
            ],
            'exclusions'       => [
                'New underground sewage pipeline laying or tile floor cutting',
            ],
        ],
        [
            'category_id'      => 4,
            'name'             => 'Wash Basin & Kitchen Sink Drainage Repair',
            'slug'             => 'washbasin-sink-drainage-repair',
            'description'      => 'Fixing choked waste pipes, bottle trap replacement, silicone sealant waterproofing and heavy-duty sink coupler fitting.',
            'starting_price'   => 299.00,
            'duration_minutes' => 45,
            'image'            => 'refixel-plumber.jpg',
            'inclusions'       => [
                'Sink waste pipe blockage removal and trap descaling',
                'Brass/PVC bottle trap replacement and tight coupling',
                'Waterproof silicone sealing to eliminate cabinet dampness',
                'Hot & cold mixer tap fitting and water flow calibration',
            ],
            'exclusions'       => [
                'Replacement basin or sink unit (client supplies hardware or billed at MRP)',
            ],
        ],
        [
            'category_id'      => 4,
            'name'             => 'Water Motor & Jet Pump Repair / Fitting',
            'slug'             => 'water-motor-pump-repair',
            'description'      => 'Submersible and monoblock water pump electrical wiring check, start capacitor replacement, pipe joint fitting and motor priming.',
            'starting_price'   => 449.00,
            'duration_minutes' => 60,
            'image'            => 'service-plumber.jpg',
            'inclusions'       => [
                'Motor coil resistance and run capacitor testing',
                'Air lock release, suction pipe check, and mechanical priming',
                'Check valve (foot valve) inspection and union joint tightening',
                'Automatic water level controller sensor wiring verification',
            ],
            'exclusions'       => [
                'Full motor winding burnout rewinding (carried out in workshop if required)',
            ],
        ],

        // -------------------------------------------------------------
        // CATEGORY 5: Carpenter
        // -------------------------------------------------------------
        [
            'category_id'      => 5,
            'name'             => 'Door Lock, Handle & Latch Installation',
            'slug'             => 'door-lock-handle-repair',
            'description'      => 'Precision installation and repair of main door mortise locks, euro cylinders, handles, magnetic door catchers, and tower bolts.',
            'starting_price'   => 249.00,
            'duration_minutes' => 45,
            'image'            => 'refixel-carpenter.jpg',
            'inclusions'       => [
                'Wood mortising, chisel alignment, and strike plate positioning',
                'Deadbolt cylinder, handle lever, and keyhole alignment',
                'Tower bolt, magnetic door stopper, and peephole fitting',
                'Smooth latch test without door friction or dragging',
            ],
            'exclusions'       => [
                'Cost of new branded locks/handles (client provides or billed with invoice)',
            ],
        ],
        [
            'category_id'      => 5,
            'name'             => 'Modular Kitchen Drawer & Cabinet Repair',
            'slug'             => 'kitchen-drawer-channel-cabinet-repair',
            'description'      => 'Heavy-duty soft-close telescopic channel replacement, hydraulic cabinet hinges alignment, loose shutter fix and profile handle fitting.',
            'starting_price'   => 349.00,
            'duration_minutes' => 60,
            'image'            => 'service-carpenter.jpg',
            'inclusions'       => [
                'Telescopic ball-bearing drawer channel replacement & lubrication',
                '3D hydraulic soft-close hinge realignment to level shutters',
                'Overhead cabinet lift-up gas spring stay rod replacement',
                'Tightening loose wooden joints and laminate edge band gluing',
            ],
            'exclusions'       => [
                'New granite countertop cutting or deep structural rebuilds',
            ],
        ],
        [
            'category_id'      => 5,
            'name'             => 'Window & Balcony Mosquito Mesh Installation',
            'slug'             => 'mosquito-mesh-net-installation',
            'description'      => 'Heavy-gauge aluminum framed or velcro-mounted fiberglass mosquito mesh installation for windows and sliding balcony doors.',
            'starting_price'   => 499.00,
            'duration_minutes' => 75,
            'image'            => 'refixel-carpenter.jpg',
            'inclusions'       => [
                'Laser measurement of window frames and sliding track inspection',
                'High-tensile SS 304 or fiberglass insect net fitting',
                'Smooth roller wheel alignment on sliding screen tracks',
                'Tight border sealing to prevent insects, mosquitoes, and pests',
            ],
            'exclusions'       => [
                'Balcony metal safety grill fabrication or welding',
            ],
        ],
        [
            'category_id'      => 5,
            'name'             => 'Wooden Furniture Polish & Surface Restoration',
            'slug'             => 'furniture-polish-surface-restoration',
            'description'      => 'Melamine / PU matte or gloss wood polishing, scratch buffing, anti-termite sealant coat, and natural grain color revitalization.',
            'starting_price'   => 899.00,
            'duration_minutes' => 120,
            'image'            => 'service-carpenter.jpg',
            'inclusions'       => [
                'Surface sanding, wood pore filler application, and stain leveling',
                'Protective clear wood lacquer / melamine sealer topcoat',
                'Surface scratch touch-up and wax buffing to restore shine',
                'Wood preservative anti-termite base treatment',
            ],
            'exclusions'       => [
                'Complete fabric upholstery reupholstering (billed separately)',
            ],
        ],

        // -------------------------------------------------------------
        // CATEGORY 6: AC Service & Repair
        // -------------------------------------------------------------
        [
            'category_id'      => 6,
            'name'             => 'AC Gas Leakage Fix & Complete Gas Refill',
            'slug'             => 'ac-gas-leakage-refill',
            'description'      => 'Nitrogen high-pressure leak testing, brazing punctured copper coils, complete vacuum pumping, and 100% pure R32/R410A gas recharging.',
            'starting_price'   => 1499.00,
            'duration_minutes' => 90,
            'image'            => 'ac-repair.jpg',
            'inclusions'       => [
                'Nitrogen pressure hold test to pinpoint microscopic coil leaks',
                'Silver brazing / oxygen-acetylene copper joint sealing',
                'Deep vacuum pump evacuation to remove moisture from lines',
                'Digital weight-scale refrigerant gas recharge (R32 / R410A / R22)',
                'Operating amp reading, grill temperature test, and cooling verification',
            ],
            'exclusions'       => [
                'Burnt compressor motor replacement (quoted separately if required)',
            ],
        ],
        [
            'category_id'      => 6,
            'name'             => 'Split AC Installation & Wall Mounting',
            'slug'             => 'split-ac-installation-mounting',
            'description'      => 'Indoor bracket laser leveling, outdoor heavy-duty iron stand mounting, copper pipe flare joint fitting, and safe electrical connection.',
            'starting_price'   => 999.00,
            'duration_minutes' => 90,
            'image'            => 'refixel-ac-service.jpg',
            'inclusions'       => [
                'Indoor backplate level mounting on RCC/brick wall',
                'Core hole drilling with dust bag for copper line passage',
                'Outdoor condenser bracket mounting with vibration dampers',
                'Copper line flare flanging, nitrogen test, and drain pipe routing',
            ],
            'exclusions'       => [
                'Extra copper piping beyond 3 meters or concealed core cutting',
            ],
        ],
        [
            'category_id'      => 6,
            'name'             => 'AC Uninstallation & Safe Packing',
            'slug'             => 'ac-uninstallation-safe-packing',
            'description'      => 'Safe refrigerant pump-down into outdoor compressor, copper line disconnect, electrical cable roll-up and safe unit dismounting.',
            'starting_price'   => 499.00,
            'duration_minutes' => 60,
            'image'            => 'service-ac-repair.jpg',
            'inclusions'       => [
                'Refrigerant gas pump-down to lock 100% gas inside compressor',
                'Flaring nuts capped and insulated to prevent line contamination',
                'Safe unmounting of indoor evaporator unit and wall bracket',
                'Outdoor unit dismounting from brackets and power cord packing',
            ],
            'exclusions'       => [
                'Transportation or shifting to new premises',
            ],
        ],
        [
            'category_id'      => 6,
            'name'             => 'AC PCB Board & Capacitor Repair',
            'slug'             => 'ac-pcb-electrical-capacitor-repair',
            'description'      => 'Inverter microcontroller PCB diagnostic, start/run fan capacitor replacement, sensor calibration, relay test and wiring overhaul.',
            'starting_price'   => 399.00,
            'duration_minutes' => 45,
            'image'            => 'ac-repair.jpg',
            'inclusions'       => [
                'Digital multimeter testing of start/run capacitor (uF rating)',
                'Ambient & coil temperature sensor resistance calibration',
                'Power supply fuse, MOV surge protector, and relay check',
                'Indoor blower motor speed control signal verification',
            ],
            'exclusions'       => [
                'Replacement of burnt outdoor inverter motherboards',
            ],
        ],

        // -------------------------------------------------------------
        // CATEGORY 8: Appliance Repair
        // -------------------------------------------------------------
        [
            'category_id'      => 8,
            'name'             => 'Washing Machine Repair & Service',
            'slug'             => 'washing-machine-repair-service',
            'description'      => 'Expert doorstep repair for front-load and top-load washing machines. Fixes drum spinning noise, drain motor, inlet valve, and error codes.',
            'starting_price'   => 349.00,
            'duration_minutes' => 60,
            'image'            => 'service-appliance-repair.jpg',
            'inclusions'       => [
                'Drum balance calibration and high-speed spin vibration check',
                'Drain pump lint filter clearing and clogged valve flush',
                'Water inlet solenoid valve and pressure switch diagnostic',
                'Drive belt tension adjustment and motor carbon brush check',
            ],
            'exclusions'       => [
                'Replacement drum tub or major metal spider casting',
            ],
        ],
        [
            'category_id'      => 8,
            'name'             => 'Refrigerator Single / Double Door Repair',
            'slug'             => 'refrigerator-fridge-repair-service',
            'description'      => 'Fixes cooling loss, compressor tripping, deep freezer frost accumulation, thermostat failure, rubber door gasket sealing, and gas fill.',
            'starting_price'   => 399.00,
            'duration_minutes' => 60,
            'image'            => 'refixel-appliance-repair.jpg',
            'inclusions'       => [
                'Compressor relay & OLP (overload protector) electrical testing',
                'Defrost timer, bimetal sensor, and glass heater continuity check',
                'Thermostat temperature dial regulation & airflow flap check',
                'Condenser coil cleaning and door magnetic gasket seal inspection',
            ],
            'exclusions'       => [
                'Internal cabinet body foaming or cabinet puncture repairs',
            ],
        ],
        [
            'category_id'      => 8,
            'name'             => 'RO Water Purifier Service & Filter Replacement',
            'slug'             => 'ro-water-purifier-service-filter',
            'description'      => 'Sediment and pre-carbon filter replacement, high-pressure RO membrane flush, digital TDS calibration, UV lamp check, and pump leak fix.',
            'starting_price'   => 399.00,
            'duration_minutes' => 60,
            'image'            => 'service-appliance-repair.jpg',
            'inclusions'       => [
                'Raw water vs purified water digital TDS meter comparison',
                'Sediment filter (spun candle) and pre-carbon cartridge check',
                'Booster pump pressure check (min 80-100 PSI for reverse osmosis)',
                'UV chamber sanitization, auto-cut-off float switch testing',
            ],
            'exclusions'       => [
                'Cost of new branded RO membrane/filters (billed on actuals at MRP)',
            ],
        ],
        [
            'category_id'      => 8,
            'name'             => 'Microwave & OTG Oven Heating Repair',
            'slug'             => 'microwave-otg-heating-repair',
            'description'      => 'Precision fix for heating failure, spark in cavity, touch keypad unresponsive, high-voltage diode replacement, and turntable motor alignment.',
            'starting_price'   => 349.00,
            'duration_minutes' => 45,
            'image'            => 'refixel-appliance-repair.jpg',
            'inclusions'       => [
                'High-voltage capacitor discharge and safety diode check',
                'Magnetron filament resistance and microwave emission test',
                'Mica waveguide cover replacement if burned or sparking',
                'Door interlock micro-switch safety timing verification',
            ],
            'exclusions'       => [
                'Replacement glass door panels or oven exterior body casing',
            ],
        ],

        // -------------------------------------------------------------
        // CATEGORY 2: Painting Services
        // -------------------------------------------------------------
        [
            'category_id'      => 2,
            'name'             => 'Exterior Weatherproof Wall Painting',
            'slug'             => 'exterior-weatherproof-painting',
            'description'      => 'High-pressure wash of exterior facade, anti-fungal algae treatment, crack-bridging primer, and double-coat high-sheen weatherproof paint.',
            'starting_price'   => 5999.00,
            'duration_minutes' => 480,
            'image'            => 'service-painting.jpg',
            'inclusions'       => [
                'Pressure washing exterior walls to strip loose paint and moss',
                'Filling hairline wall cracks with polymer crack-seal compound',
                '1 full coat of anti-fungal exterior penetrative primer',
                '2 coats of premium weatherproof exterior emulsion paint',
            ],
            'exclusions'       => [
                'Scaffolding beyond 2 floors (arranged separately if required)',
            ],
        ],
        [
            'category_id'      => 2,
            'name'             => 'Waterproofing & Damp Wall Seepage Treatment',
            'slug'             => 'wall-waterproofing-dampness-treatment',
            'description'      => 'Polymer-modified cementitious injection, crystalline slurry waterproofing barrier, and anti-efflorescence damp cure plaster coating.',
            'starting_price'   => 1499.00,
            'duration_minutes' => 180,
            'image'            => 'refixel-painting.jpg',
            'inclusions'       => [
                'Scraping damp, flaking plaster down to solid masonry substrate',
                'Applying chemical anti-efflorescence salt neutralizer',
                '2 coats of heavy-duty elastomeric waterproofing membrane',
                'Waterproof fiber mesh tape over joint junctions',
            ],
            'exclusions'       => [
                'Underground structural foundation repairs or plumbing pipe rerouting',
            ],
        ],
        [
            'category_id'      => 2,
            'name'             => 'Designer Accent Texture & Metallic Wall Painting',
            'slug'             => 'designer-accent-texture-wall-painting',
            'description'      => 'Specialty designer stencils, Venetian plaster, crushed stone or metallic dual-tone texture painting for living room and bedroom feature walls.',
            'starting_price'   => 1999.00,
            'duration_minutes' => 240,
            'image'            => 'painting.jpg',
            'inclusions'       => [
                'Ultra-smooth putty base sanding with orbital sander',
                'Application of premium tinted acrylic texture paste',
                'Pattern roller / spatula / trowel customized artistic embossing',
                'Protective clear glaze protective sealer coat',
            ],
            'exclusions'       => [
                'Full room civil ceiling replastering',
            ],
        ],

        // -------------------------------------------------------------
        // CATEGORY 9: Pest Control
        // -------------------------------------------------------------
        [
            'category_id'      => 9,
            'name'             => 'Termite (Deemak) Drill, Fill & Seal Treatment',
            'slug'             => 'termite-deemak-control-treatment',
            'description'      => 'Chemical barrier drill-fill-seal technology around wooden doorframes, baseboards, and wall junctions with odorless chemical and warranty.',
            'starting_price'   => 1899.00,
            'duration_minutes' => 180,
            'image'            => 'refixel-pest-control.jpg',
            'inclusions'       => [
                'Drilling 12mm holes at 45-degree angle along skirting and frames',
                'Pressure injection of non-repellent termiticide emulsion',
                'Sealing drilled holes with color-matched cement filler',
                'Surface chemical spray on wooden wardrobes and door frames',
            ],
            'exclusions'       => [
                'Replacing already hollowed or destroyed wooden panels',
            ],
        ],
        [
            'category_id'      => 9,
            'name'             => 'Bed Bug (Khatmal) Intensive Dual-Session Treatment',
            'slug'             => 'bed-bug-khatmal-intensive-treatment',
            'description'      => 'High-temperature dry steam extraction, mattress seam deep injection, and non-staining contact residual chemical spraying across bedroom.',
            'starting_price'   => 1199.00,
            'duration_minutes' => 120,
            'image'            => 'service-pest-control.jpg',
            'inclusions'       => [
                'Dry steam heat treatment to eradicate bed bug eggs in mattress seams',
                'Micro-emulsion residual chemical spraying in bed frame joints and headboard',
                'Baseboard, switch plate, and wardrobe crevice treatment',
                'Includes scheduled follow-up session within 15 days to break lifecycle',
            ],
            'exclusions'       => [
                'Laundry washing of bedsheets and clothes (client must hot-wash)',
            ],
        ],
        [
            'category_id'      => 9,
            'name'             => 'Mosquito, Fly & General Pest Fogging Service',
            'slug'             => 'mosquito-fly-general-pest-fogging',
            'description'      => 'Thermal fogging across common corridors, gardens, balconies, drainage shafts and dark corners for instant insect knock-down.',
            'starting_price'   => 699.00,
            'duration_minutes' => 60,
            'image'            => 'pest-control.jpg',
            'inclusions'       => [
                'Thermal fogging with WHO-recommended bio-degradable chemical',
                'Larvicide treatment in standing water, drains, and plant pots',
                'Window screen and door periphery repellent mist application',
                'Immediate knock-down of flying mosquitoes, flies, and gnats',
            ],
            'exclusions'       => [
                'Agricultural farm/orchard large-scale spraying',
            ],
        ],

        // -------------------------------------------------------------
        // CATEGORY 3: Fall Ceiling
        // -------------------------------------------------------------
        [
            'category_id'      => 3,
            'name'             => 'Grid Ceiling & Commercial Acoustic Tile Installation',
            'slug'             => 'grid-ceiling-acoustic-tile-installation',
            'description'      => 'T-grid metal framing installation with mineral fiber acoustic soundproof tiles for offices, clinics, retail shops and recording studios.',
            'starting_price'   => 1299.00,
            'duration_minutes' => 180,
            'image'            => 'refixel-fall-ceiling.jpg',
            'inclusions'       => [
                'Galvanized steel T-runner main and cross grid hanging structure',
                'Precision laser leveling to ensure 100% horizontal flatness',
                'Laying moisture-resistant acoustic mineral fiber ceiling tiles',
                'Custom cut-outs for 2x2 LED panel lights and AC diffuser vents',
            ],
            'exclusions'       => [
                'Heavy industrial HVAC duct fabrication',
            ],
        ],
    ];

    $addedCount = 0;
    foreach ($servicesToSeed as $svc) {
        $existing = Database::fetchOne("SELECT id FROM services WHERE slug = :slug", ['slug' => $svc['slug']]);
        $serviceId = 0;

        if ($existing) {
            $serviceId = (int)$existing['id'];
            Database::execute("
                UPDATE services 
                SET category_id = :cid, name = :name, description = :desc, starting_price = :price, duration_minutes = :duration, image = :img, is_active = 1, is_addon = 0
                WHERE id = :id
            ", [
                'cid'      => $svc['category_id'],
                'name'     => $svc['name'],
                'desc'     => $svc['description'],
                'price'    => $svc['starting_price'],
                'duration' => $svc['duration_minutes'],
                'img'      => $svc['image'],
                'id'       => $serviceId,
            ]);
            echo "Updated existing service: {$svc['name']} (ID: {$serviceId})\n";
        } else {
            Database::execute("
                INSERT INTO services (category_id, name, slug, description, starting_price, duration_minutes, image, is_active, is_addon, is_coming_soon, created_at)
                VALUES (:cid, :name, :slug, :desc, :price, :duration, :img, 1, 0, 0, NOW())
            ", [
                'cid'      => $svc['category_id'],
                'name'     => $svc['name'],
                'slug'     => $svc['slug'],
                'desc'     => $svc['description'],
                'price'    => $svc['starting_price'],
                'duration' => $svc['duration_minutes'],
                'img'      => $svc['image'],
            ]);
            $serviceId = (int)Database::lastInsertId();
            $addedCount++;
            echo "Added new service: {$svc['name']} (ID: {$serviceId})\n";
        }

        // Add checklist items (inclusions & exclusions)
        if ($serviceId > 0) {
            Database::execute("DELETE FROM service_checklist_items WHERE service_id = :sid", ['sid' => $serviceId]);

            $order = 1;
            foreach ($svc['inclusions'] as $inc) {
                Database::execute("
                    INSERT INTO service_checklist_items (service_id, label, is_included, sort_order)
                    VALUES (:sid, :label, 1, :ord)
                ", [
                    'sid'   => $serviceId,
                    'label' => $inc,
                    'ord'   => $order++,
                ]);
            }
            foreach ($svc['exclusions'] as $exc) {
                Database::execute("
                    INSERT INTO service_checklist_items (service_id, label, is_included, sort_order)
                    VALUES (:sid, :label, 0, :ord)
                ", [
                    'sid'   => $serviceId,
                    'label' => $exc,
                    'ord'   => $order++,
                ]);
            }
        }
    }

    return $addedCount;
}
}

if (php_sapi_name() === 'cli' && isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    require_once __DIR__ . '/../tests/bootstrap.php';
    try {
        echo "=== Adding Comprehensive Services Across All Categories ===\n";
        $count = seedComprehensiveCategoryServices();
        echo "\n=== Successfully Seeded {$count} New Services and Updated Existing Catalog! ===\n";
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

