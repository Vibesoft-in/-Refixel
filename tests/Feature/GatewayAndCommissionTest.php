<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Controllers\Admin\GatewayController;
use App\Controllers\Admin\CommissionController;
use App\Models\PaymentGateway;
use App\Models\CommissionRule;
use App\Models\StaffEarning;
use App\Models\Setting;

echo "=== RUNNING GATEWAY & COMMISSION MANAGEMENT TESTS ===\n";

// Set admin session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Test 1: Verify Gateways loaded with Razorpay credentials
echo "Test 1: Payment Gateways Registry & Razorpay Credentials... ";
$gateways = PaymentGateway::getAll();
assert(count($gateways) >= 2, "Expected at least 2 gateways");
$razorpay = PaymentGateway::getByCode('razorpay');
assert($razorpay !== null, "Razorpay gateway not found");
assert(!empty($razorpay['key_id']), "Key ID should not be empty");
assert(!empty($razorpay['key_secret']), "Key Secret should not be empty");
assert($razorpay['is_active'] == 1, "Razorpay should be active");
echo "PASSED\n";

// Test 2: Update Gateway Credentials via GatewayController
echo "Test 2: Gateway Controller Update & Sync to Settings... ";
$gwCtrl = new GatewayController();
$req = new Request([], [
    'name'           => 'Razorpay Secure Checkout',
    'environment'    => 'live',
    'is_active'      => '1',
    'key_id'         => 'rzp_live_R72df4G16qv1Rh',
    'key_secret'     => 'ZxuiIKL44pv12Ba5b8rwnwuH',
    'webhook_secret' => 'whsec_sample_token',
    'merchant_email' => 'billing@refixel.com',
    'merchant_name'  => 'REFIXEL India',
    'currency'       => 'INR',
    'description'    => 'UPI and Card Processing',
]);
$res = $gwCtrl->update($req, (string)$razorpay['id']);
assert($res->getStatusCode() === 302, "Expected redirect response");
$updatedRzp = PaymentGateway::getByCode('razorpay');
assert($updatedRzp['name'] === 'Razorpay Secure Checkout', "Gateway name was not updated");
assert(Setting::get('razorpay_key_id') === 'rzp_live_R72df4G16qv1Rh', "Setting key_id out of sync");
echo "PASSED\n";

// Test 3: Commission Calculation Math
// Example: Gross ₹2000, Material ₹400 -> Net ₹1600. Company 25% = ₹400, Labour 75% = ₹1200 + ₹400 mat = ₹1600
echo "Test 3: Commission Calculation Math (Gross - Material Split)... ";
$split = CommissionRule::calculateSplit(2000.0, 400.0, null, null, 'standard', 'worker');
assert($split['gross_charge'] === 2000.0, "Gross charge should be 2000");
assert($split['material_cost'] === 400.0, "Material cost should be 400");
assert($split['net_split_base'] === 1600.0, "Net split base should be 1600");
assert($split['admin_percent'] === 25.0, "Admin percent should be 25%");
assert($split['worker_percent'] === 75.0, "Worker percent should be 75%");
assert($split['admin_share'] === 400.0, "Company share should be ₹400.00");
assert($split['worker_share'] === 1200.0, "Labour share should be ₹1200.00");
assert($split['worker_total_payout'] === 1600.0, "Labour payout with material reimbursement should be ₹1600.00");
echo "PASSED\n";

// Test 4: Commission Calculation when Company supplies materials
echo "Test 4: Commission when Company supplies material inventory... ";
$splitCompanyMat = CommissionRule::calculateSplit(2000.0, 400.0, null, null, 'standard', 'company');
assert($splitCompanyMat['worker_total_payout'] === 1200.0, "Labour should receive pure labour share of ₹1200.00");
assert($splitCompanyMat['company_retained'] === 800.0, "Company should retain ₹400 margin + ₹400 material cost = ₹800.00");
echo "PASSED\n";

// Test 5: Custom Tier Override Rule (Senior Specialist 80% / 20%)
echo "Test 5: Worker Tier Override Rule... ";
$splitSenior = CommissionRule::calculateSplit(1000.0, 0.0, null, null, 'senior', 'worker');
assert($splitSenior['admin_percent'] === 20.0, "Senior tier admin % should be 20%");
assert($splitSenior['worker_percent'] === 80.0, "Senior tier worker % should be 80%");
assert($splitSenior['worker_total_payout'] === 800.0, "Senior worker payout should be ₹800");
assert($splitSenior['admin_share'] === 200.0, "Company share should be ₹200");
echo "PASSED\n";

// Test 6: Global Policy Settings Persistence
echo "Test 6: Global Policy Settings Persistence... ";
$commCtrl = new CommissionController();
$globalReq = new Request([], [
    'global_admin_commission_pct'     => '30',
    'global_material_deduction_rule'  => 'deduct_before_split',
    'global_min_worker_payout'        => '300',
]);
$commRes = $commCtrl->saveGlobal($globalReq);
assert($commRes->getStatusCode() === 302, "Expected redirect");
assert((float)Setting::get('global_admin_commission_pct') === 30.0, "Admin commission not saved in Setting");
assert((float)Setting::get('global_worker_commission_pct') === 70.0, "Worker commission not calculated");

// Restore back to 25 / 75 default
Setting::set('global_admin_commission_pct', '25');
Setting::set('global_worker_commission_pct', '75');
echo "PASSED\n";

// Test 7: Recording Staff Earning with Gross, Material, and Admin Fee Breakdown
echo "Test 7: Staff Earnings Detailed Breakdown Audit Recording... ";
$validJob = Database::fetchOne("SELECT id, staff_id FROM jobs LIMIT 1");
assert($validJob !== null, "Need at least one job in database");
$testJobId = (int)$validJob['id'];
$testStaffId = (int)$validJob['staff_id'];

// Clean up any previous test record for this test job
Database::query("DELETE FROM staff_earnings WHERE job_id = :jid", ['jid' => $testJobId]);

StaffEarning::recordJobCommission(
    $testJobId,
    $testStaffId,
    1600.00,
    date('Y-m'),
    2000.00,
    400.00,
    400.00
);

$record = Database::fetchOne("SELECT * FROM staff_earnings WHERE job_id = :jid LIMIT 1", ['jid' => $testJobId]);
assert($record !== null, "Earning record was not created");
assert((float)$record['amount'] === 1600.00, "Amount mismatch");
assert((float)$record['gross_amount'] === 2000.00, "Gross amount mismatch");
assert((float)$record['material_cost'] === 400.00, "Material cost mismatch");
assert((float)$record['admin_fee'] === 400.00, "Admin fee mismatch");
assert($record['is_settled'] == 0, "Should be initially unsettled");

// Clean up
Database::query("DELETE FROM staff_earnings WHERE job_id = :jid", ['jid' => $testJobId]);
echo "PASSED\n";

echo "\n=== ALL 7 GATEWAY & COMMISSION TESTS PASSED PERFECTLY! ===\n";
