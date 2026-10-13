<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class CommissionRule extends Model
{
    protected static string $table = 'commission_rules';

    public static function getAllWithCategories(): array
    {
        return Database::fetchAll("
            SELECT cr.*, c.name as category_name, c.slug as category_slug
            FROM commission_rules cr
            LEFT JOIN categories c ON cr.category_id = c.id
            ORDER BY cr.category_id IS NULL ASC, cr.id ASC
        ");
    }

    public static function findApplicableRule(?int $categoryId = null, string $workerTier = 'all'): ?array
    {
        // 1. Try Category + Tier specific rule
        if ($categoryId) {
            $rule = Database::fetchOne("
                SELECT * FROM commission_rules 
                WHERE category_id = :cid AND (worker_tier = :tier OR worker_tier = 'all') AND is_active = 1
                ORDER BY (worker_tier = :tier2) DESC, id DESC LIMIT 1
            ", ['cid' => $categoryId, 'tier' => $workerTier, 'tier2' => $workerTier]);
            if ($rule) return $rule;
        }

        // 2. Try Tier specific rule
        if ($workerTier !== 'all') {
            $rule = Database::fetchOne("
                SELECT * FROM commission_rules 
                WHERE category_id IS NULL AND worker_tier = :tier AND is_active = 1
                LIMIT 1
            ", ['tier' => $workerTier]);
            if ($rule) return $rule;
        }

        // 3. Fallback to Global Rule
        return Database::fetchOne("
            SELECT * FROM commission_rules 
            WHERE category_id IS NULL AND worker_tier = 'all' AND is_active = 1
            LIMIT 1
        ");
    }

    /**
     * Compute comprehensive commission breakdown
     */
    public static function calculateSplit(
        float $grossCharge,
        float $materialCost = 0.0,
        ?int $categoryId = null,
        string $workerTier = 'standard',
        string $materialPayer = 'worker'
    ): array {
        $rule = self::findApplicableRule($categoryId, $workerTier);

        $adminPct = $rule ? (float)$rule['admin_percent'] : (float)Setting::get('global_admin_commission_pct', 25.0);
        $workerPct = $rule ? (float)$rule['worker_percent'] : (float)Setting::get('global_worker_commission_pct', 75.0);
        $minVisitCharge = $rule ? (float)$rule['min_visit_charge'] : (float)Setting::get('global_min_worker_payout', 200.0);
        $deductMaterialFirst = $rule ? (bool)$rule['deduct_material_first'] : true;

        $grossCharge = max(0.0, $grossCharge);
        $materialCost = max(0.0, $materialCost);

        // Deduct material cost before commission split
        if ($deductMaterialFirst) {
            $netSplitBase = max(0.0, $grossCharge - $materialCost);
        } else {
            $netSplitBase = $grossCharge;
        }

        $adminShare = round($netSplitBase * ($adminPct / 100.0), 2);
        $workerShare = round($netSplitBase * ($workerPct / 100.0), 2);

        // Material reimbursement logic
        if ($materialPayer === 'worker') {
            // Worker purchased material -> worker gets share + material reimbursement
            $workerTotalPayout = $workerShare + $materialCost;
            $companyRetained = $adminShare;
        } elseif ($materialPayer === 'company') {
            // Company supplied material -> company retains share + material cost
            $workerTotalPayout = $workerShare;
            $companyRetained = $adminShare + $materialCost;
        } else {
            // Customer provided material directly -> pure split
            $workerTotalPayout = $workerShare;
            $companyRetained = $adminShare;
        }

        // Apply minimum visit wage guarantee
        if ($workerTotalPayout < $minVisitCharge && $grossCharge >= $minVisitCharge) {
            $diff = $minVisitCharge - $workerTotalPayout;
            $workerTotalPayout = $minVisitCharge;
            $companyRetained = max(0.0, $companyRetained - $diff);
        }

        return [
            'gross_charge'        => $grossCharge,
            'material_cost'       => $materialCost,
            'net_split_base'      => $netSplitBase,
            'admin_percent'       => $adminPct,
            'worker_percent'      => $workerPct,
            'admin_share'         => $adminShare,
            'worker_share'        => $workerShare,
            'material_payer'      => $materialPayer,
            'worker_total_payout' => $workerTotalPayout,
            'company_retained'    => $companyRetained,
            'rule_applied'        => $rule ? ($rule['notes'] ?? 'Custom Commission Rule') : 'Global Standard Default (25% Admin / 75% Tech)',
            'rule_id'             => $rule['id'] ?? null,
        ];
    }
}
