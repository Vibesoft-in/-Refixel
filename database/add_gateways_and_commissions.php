<?php
declare(strict_types=1);

/**
 * Migration: Add Payment Gateways and Commission Rules tables and defaults
 */

require_once __DIR__ . '/../app/Core/Env.php';
\App\Core\Env::load(__DIR__ . '/../.env');
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Cache.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/Setting.php';

use App\Core\Database;
use App\Models\Setting;

echo "Starting migration: Payment Gateways and Commission Management...\n";

// 1. Create payment_gateways table
Database::query("
    CREATE TABLE IF NOT EXISTS payment_gateways (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(120) NOT NULL,
        environment ENUM('test','live') NOT NULL DEFAULT 'live',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        key_id VARCHAR(255) NULL,
        key_secret VARCHAR(255) NULL,
        webhook_secret VARCHAR(255) NULL,
        merchant_email VARCHAR(150) NULL,
        merchant_name VARCHAR(150) NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'INR',
        description TEXT NULL,
        display_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'payment_gateways' ensured.\n";

// 2. Create commission_rules table
Database::query("
    CREATE TABLE IF NOT EXISTS commission_rules (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT UNSIGNED NULL,
        worker_tier ENUM('all','senior','standard','trainee') NOT NULL DEFAULT 'all',
        admin_percent DECIMAL(5,2) NOT NULL DEFAULT 25.00,
        worker_percent DECIMAL(5,2) NOT NULL DEFAULT 75.00,
        deduct_material_first TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Deduct material cost before calculating split',
        material_payer ENUM('worker','company','customer') NOT NULL DEFAULT 'worker' COMMENT 'Party providing materials upfront',
        min_visit_charge DECIMAL(10,2) NOT NULL DEFAULT 200.00,
        notes VARCHAR(255) NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'commission_rules' ensured.\n";

// 3. Add breakdown columns to staff_earnings if not present
try {
    $cols = Database::fetchAll("SHOW COLUMNS FROM staff_earnings");
    $colNames = array_column($cols, 'Field');
    
    if (!in_array('gross_amount', $colNames, true)) {
        Database::query("ALTER TABLE staff_earnings ADD COLUMN gross_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER amount");
        echo "✓ Added 'gross_amount' to staff_earnings.\n";
    }
    if (!in_array('material_cost', $colNames, true)) {
        Database::query("ALTER TABLE staff_earnings ADD COLUMN material_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER gross_amount");
        echo "✓ Added 'material_cost' to staff_earnings.\n";
    }
    if (!in_array('admin_fee', $colNames, true)) {
        Database::query("ALTER TABLE staff_earnings ADD COLUMN admin_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER material_cost");
        echo "✓ Added 'admin_fee' to staff_earnings.\n";
    }
} catch (\Throwable $e) {
    echo "Notice on staff_earnings columns: " . $e->getMessage() . "\n";
}

// 4. Seed Payment Gateways with user provided Razorpay credentials
$gateways = [
    [
        'code'           => 'razorpay',
        'name'           => 'Razorpay Payment Gateway',
        'environment'    => 'live',
        'is_active'      => 1,
        'key_id'         => 'rzp_live_R72df4G16qv1Rh',
        'key_secret'     => 'ZxuiIKL44pv12Ba5b8rwnwuH',
        'webhook_secret' => '',
        'merchant_email' => 'payments@refixel.com',
        'merchant_name'  => 'REFIXEL Technologies',
        'currency'       => 'INR',
        'description'    => 'All-in-one payment gateway for Credit/Debit Cards, UPI (GPay, PhonePe, Paytm), NetBanking, and Wallets.',
        'display_order'  => 1
    ],
    [
        'code'           => 'cod',
        'name'           => 'Cash on Delivery (COD) / Direct Pay',
        'environment'    => 'live',
        'is_active'      => 1,
        'key_id'         => 'COD_DIRECT',
        'key_secret'     => 'N/A',
        'webhook_secret' => '',
        'merchant_email' => 'billing@refixel.com',
        'merchant_name'  => 'REFIXEL Doorstep Collection',
        'currency'       => 'INR',
        'description'    => 'Customer pays cash directly to the technician upon satisfactory job completion.',
        'display_order'  => 2
    ],
    [
        'code'           => 'phonepe',
        'name'           => 'PhonePe Merchant Gateway',
        'environment'    => 'test',
        'is_active'      => 0,
        'key_id'         => 'PGTESTPAYUAT',
        'key_secret'     => '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399',
        'webhook_secret' => '',
        'merchant_email' => 'support@refixel.com',
        'merchant_name'  => 'REFIXEL PhonePe',
        'currency'       => 'INR',
        'description'    => 'Direct UPI Intent and dynamic QR code processing powered by PhonePe Business.',
        'display_order'  => 3
    ],
    [
        'code'           => 'stripe',
        'name'           => 'Stripe Global Card Gateway',
        'environment'    => 'test',
        'is_active'      => 0,
        'key_id'         => 'pk_test_sample_stripe_key',
        'key_secret'     => 'sk_test_sample_stripe_secret',
        'webhook_secret' => '',
        'merchant_email' => 'finance@refixel.com',
        'merchant_name'  => 'REFIXEL Global',
        'currency'       => 'INR',
        'description'    => 'International credit cards and cross-border digital payments.',
        'display_order'  => 4
    ]
];

foreach ($gateways as $gw) {
    Database::query("
        INSERT INTO payment_gateways (code, name, environment, is_active, key_id, key_secret, webhook_secret, merchant_email, merchant_name, currency, description, display_order)
        VALUES (:code, :name, :environment, :is_active, :key_id, :key_secret, :webhook_secret, :merchant_email, :merchant_name, :currency, :description, :display_order)
        ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            environment = VALUES(environment),
            key_id = VALUES(key_id),
            key_secret = VALUES(key_secret),
            merchant_email = VALUES(merchant_email),
            merchant_name = VALUES(merchant_name),
            currency = VALUES(currency),
            description = VALUES(description)
    ", $gw);
}
echo "✓ Seeded default payment gateways (Razorpay active with live test credentials).\n";

// 5. Seed default commission rules
$existingRules = Database::fetchOne("SELECT COUNT(id) as c FROM commission_rules");
if ((int)($existingRules['c'] ?? 0) === 0) {
    // 1 Global Default Rule (All categories & tiers: 25% Company, 75% Technician)
    Database::query("
        INSERT INTO commission_rules (category_id, worker_tier, admin_percent, worker_percent, deduct_material_first, material_payer, min_visit_charge, notes, is_active)
        VALUES (NULL, 'all', 25.00, 75.00, 1, 'worker', 250.00, 'Global Standard Base Split: 25% Company, 75% Worker (Material cost deducted before split)', 1)
    ");
    
    // Tier specific rules
    Database::query("
        INSERT INTO commission_rules (category_id, worker_tier, admin_percent, worker_percent, deduct_material_first, material_payer, min_visit_charge, notes, is_active)
        VALUES 
        (NULL, 'senior', 20.00, 80.00, 1, 'worker', 300.00, 'Senior Specialist Tier: 20% Company, 80% Technician', 1),
        (NULL, 'standard', 25.00, 75.00, 1, 'worker', 250.00, 'Standard Verified Technician: 25% Company, 75% Technician', 1),
        (NULL, 'trainee', 35.00, 65.00, 1, 'worker', 180.00, 'Apprentice / Trainee Partner: 35% Company, 65% Technician', 1)
    ");
    echo "✓ Seeded default commission rules.\n";
}

// 6. Sync settings table for instant key retrieval
Setting::set('razorpay_key_id', 'rzp_live_R72df4G16qv1Rh');
Setting::set('razorpay_key_secret', 'ZxuiIKL44pv12Ba5b8rwnwuH');
Setting::set('razorpay_environment', 'live');
Setting::set('razorpay_is_active', '1');
Setting::set('global_admin_commission_pct', '25');
Setting::set('global_worker_commission_pct', '75');
Setting::set('global_material_deduction_rule', 'deduct_before_split');
Setting::set('global_min_worker_payout', '250');

echo "✓ Settings synchronized successfully.\n";
echo "Migration complete!\n";
