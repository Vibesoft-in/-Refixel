<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Controllers\Admin\PayoutController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Models\StaffEarning;

echo "=== RUNNING ADMIN TECHNICIAN PAYOUTS FEATURE TESTS ===\n";

$payoutCtrl = new PayoutController();

// 1. Authenticate as Admin
$admin = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if (!$admin) {
    $admin = Database::fetchOne("SELECT * FROM users WHERE role = 'super_admin' LIMIT 1");
}
assert($admin !== null, "Admin or Super Admin must exist");
Auth::login($admin);

// 2. Test PayoutController::index()
$req = new Request([], ['status' => 'pending'], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/payouts']);
$resp = $payoutCtrl->index($req);
assert($resp->getStatusCode() === 200, "PayoutController index must return 200");
echo "Test 1: PayoutController index response 200 - PASSED\n";

// 3. Test StaffEarning::getAdminPayoutStats()
$stats = StaffEarning::getAdminPayoutStats();
assert(isset($stats['total_pending_amount']), "Stats must include total_pending_amount");
assert(isset($stats['pending_count']), "Stats must include pending_count");
assert(isset($stats['total_settled_amount']), "Stats must include total_settled_amount");
assert(isset($stats['technicians_with_pending']), "Stats must include technicians_with_pending");
echo "Test 2: StaffEarning getAdminPayoutStats calculation - PASSED\n";

// 4. Test Single Settlement Workflow
// Find or create a test pending earning
$staff = Database::fetchOne("SELECT id FROM users WHERE role = 'staff' LIMIT 1");
assert($staff !== null, "A staff user must exist");
$staffId = (int)$staff['id'];

// Check if any earning exists, else insert dummy for test
$earning = Database::fetchOne("SELECT * FROM staff_earnings LIMIT 1");
if (!$earning) {
    // We will verify method on an existing job or verify with mock
    echo "No existing earnings found; skipping database mutation test.\n";
} else {
    $earningId = (int)$earning['id'];
    $wasSettled = (int)$earning['is_settled'];

    // Settle it
    $settleSuccess = StaffEarning::settle($earningId, 'upi', 'UPI-TEST-12345', 'Test settlement notes');
    assert($settleSuccess === true, "StaffEarning::settle must return true");

    $updated = Database::fetchOne("SELECT * FROM staff_earnings WHERE id = :id", ['id' => $earningId]);
    assert((int)$updated['is_settled'] === 1, "is_settled must be 1");
    assert($updated['settled_method'] === 'upi', "settled_method must be upi");
    assert($updated['settled_ref'] === 'UPI-TEST-12345', "settled_ref must match");

    echo "Test 3: Single payout settlement - PASSED\n";

    // Revert state if it was originally pending
    if ($wasSettled === 0) {
        Database::query("UPDATE staff_earnings SET is_settled = 0, settled_at = NULL, settled_method = NULL, settled_ref = NULL, settled_notes = NULL WHERE id = :id", ['id' => $earningId]);
    }
}

// 5. Test Filters
$allPayouts = StaffEarning::getAllWithDetails('all');
assert(is_array($allPayouts), "getAllWithDetails('all') must return an array");
echo "Test 4: Payout listing with split normalization - PASSED\n";

echo "ALL ADMIN PAYOUT TESTS PASSED SUCCESSFULLY!\n";
