<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Setting;

class CommissionController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::all('name ASC');
        $rules      = CommissionRule::getAllWithCategories();
        $settings   = Setting::getAllKeyValue();

        // Historical & Recent Earnings / Commission Ledger
        $ledger = Database::fetchAll("
            SELECT se.*, u.name as staff_name, u.phone as staff_phone,
                   b.booking_no, b.name as customer_name,
                   s.name as service_name, c.name as category_name
            FROM staff_earnings se
            JOIN users u ON se.staff_id = u.id
            JOIN jobs j ON se.job_id = j.id
            JOIN bookings b ON j.booking_id = b.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN categories c ON s.category_id = c.id
            ORDER BY se.id DESC LIMIT 50
        ");

        $totalPayouts = (float)(Database::fetchOne("SELECT IFNULL(SUM(amount), 0) as s FROM staff_earnings")['s'] ?? 0);
        $totalAdminFees = (float)(Database::fetchOne("SELECT IFNULL(SUM(admin_fee), 0) as s FROM staff_earnings")['s'] ?? 0);
        $pendingPayouts = (float)(Database::fetchOne("SELECT IFNULL(SUM(amount), 0) as s FROM staff_earnings WHERE is_settled = 0")['s'] ?? 0);

        return $this->render('admin.commissions.index', [
            'title'          => 'Commission & Profit Splits | REFIXEL Admin',
            'categories'     => $categories,
            'rules'          => $rules,
            'settings'       => $settings,
            'ledger'         => $ledger,
            'totalPayouts'   => $totalPayouts,
            'totalAdminFees' => $totalAdminFees,
            'pendingPayouts' => $pendingPayouts,
        ], 'admin');
    }

    public function saveGlobal(Request $request): Response
    {
        $adminPct   = max(0.0, min(100.0, (float)$request->input('global_admin_commission_pct', 25.0)));
        $workerPct  = 100.0 - $adminPct; // Automatically complements to 100%
        $materialRule = trim((string)$request->input('global_material_deduction_rule', 'deduct_before_split'));
        $minPayout  = max(0.0, (float)$request->input('global_min_worker_payout', 250.0));

        Setting::set('global_admin_commission_pct', (string)$adminPct);
        Setting::set('global_worker_commission_pct', (string)$workerPct);
        Setting::set('global_material_deduction_rule', $materialRule);
        Setting::set('global_min_worker_payout', (string)$minPayout);

        // Also sync default global rule in commission_rules table
        $globalRule = Database::fetchOne("SELECT id FROM commission_rules WHERE category_id IS NULL AND worker_tier = 'all' LIMIT 1");
        if ($globalRule) {
            CommissionRule::update((int)$globalRule['id'], [
                'admin_percent'  => $adminPct,
                'worker_percent' => $workerPct,
                'min_visit_charge' => $minPayout,
                'deduct_material_first' => ($materialRule === 'deduct_before_split') ? 1 : 0,
            ]);
        }

        View::setFlash('success', "Global commission split updated: Company {$adminPct}% / Labour {$workerPct}%.");
        return $this->redirect('/admin/commissions');
    }

    public function storeRule(Request $request): Response
    {
        $categoryId = $request->input('category_id') ? (int)$request->input('category_id') : null;
        $workerTier = (string)$request->input('worker_tier', 'all');
        $adminPct   = max(0.0, min(100.0, (float)$request->input('admin_percent', 25.0)));
        $workerPct  = max(0.0, min(100.0, (float)$request->input('worker_percent', 75.0)));
        $deductMat  = (int)($request->input('deduct_material_first', 1));
        $matPayer   = (string)$request->input('material_payer', 'worker');
        $minCharge  = max(0.0, (float)$request->input('min_visit_charge', 200.0));
        $notes      = trim((string)$request->input('notes', ''));

        CommissionRule::create([
            'category_id'           => $categoryId,
            'worker_tier'           => in_array($workerTier, ['all', 'senior', 'standard', 'trainee'], true) ? $workerTier : 'all',
            'admin_percent'         => $adminPct,
            'worker_percent'        => $workerPct,
            'deduct_material_first' => $deductMat,
            'material_payer'        => in_array($matPayer, ['worker', 'company', 'customer'], true) ? $matPayer : 'worker',
            'min_visit_charge'      => $minCharge,
            'notes'                 => $notes,
            'is_active'             => 1,
        ]);

        View::setFlash('success', 'Custom commission rule created successfully.');
        return $this->redirect('/admin/commissions');
    }

    public function updateRule(Request $request, string $id): Response
    {
        $ruleId = (int)$id;
        $rule = CommissionRule::find($ruleId);
        if (!$rule) {
            View::setFlash('error', 'Rule not found.');
            return $this->redirect('/admin/commissions');
        }

        $categoryId = $request->input('category_id') ? (int)$request->input('category_id') : null;
        $workerTier = (string)$request->input('worker_tier', 'all');
        $adminPct   = max(0.0, min(100.0, (float)$request->input('admin_percent', 25.0)));
        $workerPct  = max(0.0, min(100.0, (float)$request->input('worker_percent', 75.0)));
        $deductMat  = (int)($request->input('deduct_material_first', 1));
        $matPayer   = (string)$request->input('material_payer', 'worker');
        $minCharge  = max(0.0, (float)$request->input('min_visit_charge', 200.0));
        $notes      = trim((string)$request->input('notes', ''));
        $isActive   = (int)($request->input('is_active', 1));

        CommissionRule::update($ruleId, [
            'category_id'           => $categoryId,
            'worker_tier'           => in_array($workerTier, ['all', 'senior', 'standard', 'trainee'], true) ? $workerTier : 'all',
            'admin_percent'         => $adminPct,
            'worker_percent'        => $workerPct,
            'deduct_material_first' => $deductMat,
            'material_payer'        => in_array($matPayer, ['worker', 'company', 'customer'], true) ? $matPayer : 'worker',
            'min_visit_charge'      => $minCharge,
            'notes'                 => $notes,
            'is_active'             => $isActive,
        ]);

        View::setFlash('success', 'Commission rule updated successfully.');
        return $this->redirect('/admin/commissions');
    }

    public function deleteRule(Request $request, string $id): Response
    {
        $ruleId = (int)$id;
        $rule = CommissionRule::find($ruleId);
        if (!$rule) {
            View::setFlash('error', 'Rule not found.');
            return $this->redirect('/admin/commissions');
        }

        // Prevent deleting the base global rule
        if ($rule['category_id'] === null && $rule['worker_tier'] === 'all') {
            View::setFlash('error', 'Cannot delete the base global commission rule.');
            return $this->redirect('/admin/commissions');
        }

        CommissionRule::delete($ruleId);
        View::setFlash('success', 'Commission rule deleted successfully.');
        return $this->redirect('/admin/commissions');
    }

    public function settleEarning(Request $request, string $id): Response
    {
        $earningId = (int)$id;
        $row = Database::fetchOne("SELECT * FROM staff_earnings WHERE id = :id LIMIT 1", ['id' => $earningId]);

        if (!$row) {
            View::setFlash('error', 'Earning record not found.');
            return $this->redirect('/admin/commissions');
        }

        Database::query("UPDATE staff_earnings SET is_settled = 1, settled_at = NOW() WHERE id = :id", ['id' => $earningId]);
        View::setFlash('success', "Technician payout of ₹" . number_format((float)$row['amount'], 2) . " marked as SETTLED.");
        return $this->redirect('/admin/commissions');
    }

    public function simulate(Request $request): Response
    {
        $grossCharge   = (float)$request->input('gross_charge', 1500.0);
        $materialCost  = (float)$request->input('material_cost', 300.0);
        $categoryId    = $request->input('category_id') ? (int)$request->input('category_id') : null;
        $workerTier    = (string)$request->input('worker_tier', 'standard');
        $materialPayer = (string)$request->input('material_payer', 'worker');

        $split = CommissionRule::calculateSplit($grossCharge, $materialCost, $categoryId, $workerTier, $materialPayer);
        return $this->json($split);
    }
}
