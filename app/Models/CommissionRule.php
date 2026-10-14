<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class CommissionRule extends Model
{
    protected static string $table = 'commission_rules';

    public static function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            Database::query("ALTER TABLE commission_rules ADD COLUMN service_id INT(10) UNSIGNED NULL AFTER category_id, ADD INDEX idx_service_id (service_id)");
        } catch (\Throwable $e) {}
    }

    public static function getAllWithCategories(): array
    {
        self::ensureSchema();
        return Database::fetchAll("
            SELECT cr.*, 
                   c.name as category_name, c.slug as category_slug,
                   s.name as service_name, s.starting_price as service_price
            FROM commission_rules cr
            LEFT JOIN categories c ON cr.category_id = c.id
            LEFT JOIN services s ON cr.service_id = s.id
            ORDER BY cr.service_id IS NOT NULL DESC, cr.category_id IS NOT NULL DESC, cr.id DESC
        ");
    }

    public static function findApplicableRule(?int $serviceId = null, ?int $categoryId = null, string $workerTier = 'all'): ?array
    {
        self::ensureSchema();

        // 1. Try Service-specific rule (Highest Priority)
        if ($serviceId) {
            $rule = Database::fetchOne("
                SELECT cr.*, s.name as service_name, s.starting_price as service_price, c.name as category_name
                FROM commission_rules cr
                LEFT JOIN services s ON cr.service_id = s.id
                LEFT JOIN categories c ON s.category_id = c.id
                WHERE cr.service_id = :sid AND (cr.worker_tier = :tier OR cr.worker_tier = 'all') AND cr.is_active = 1
                ORDER BY (cr.worker_tier = :tier2) DESC, cr.id DESC LIMIT 1
            ", ['sid' => $serviceId, 'tier' => $workerTier, 'tier2' => $workerTier]);
            if ($rule) return $rule;

            // If categoryId wasn't passed, find category from service
            if (!$categoryId) {
                $svc = Database::fetchOne("SELECT category_id FROM services WHERE id = :sid", ['sid' => $serviceId]);
                if ($svc && !empty($svc['category_id'])) {
                    $categoryId = (int)$svc['category_id'];
                }
            }
        }

        // 2. Try Category + Tier specific rule
        if ($categoryId) {
            $rule = Database::fetchOne("
                SELECT cr.*, c.name as category_name
                FROM commission_rules cr
                LEFT JOIN categories c ON cr.category_id = c.id
                WHERE cr.category_id = :cid AND cr.service_id IS NULL AND (cr.worker_tier = :tier OR cr.worker_tier = 'all') AND cr.is_active = 1
                ORDER BY (cr.worker_tier = :tier2) DESC, cr.id DESC LIMIT 1
            ", ['cid' => $categoryId, 'tier' => $workerTier, 'tier2' => $workerTier]);
            if ($rule) return $rule;
        }

        // 3. Try Tier specific rule
        if ($workerTier !== 'all') {
            $rule = Database::fetchOne("
                SELECT * FROM commission_rules 
                WHERE service_id IS NULL AND category_id IS NULL AND worker_tier = :tier AND is_active = 1
                LIMIT 1
            ", ['tier' => $workerTier]);
            if ($rule) return $rule;
        }

        // 4. Fallback to Global Rule
        return Database::fetchOne("
            SELECT * FROM commission_rules 
            WHERE service_id IS NULL AND category_id IS NULL AND worker_tier = 'all' AND is_active = 1
            LIMIT 1
        ");
    }

    /**
     * Compute comprehensive commission breakdown
     */
    public static function calculateSplit(
        float $grossCharge,
        float $materialCost = 0.0,
        ?int $serviceId = null,
        ?int $categoryId = null,
        string $workerTier = 'standard',
        string $materialPayer = 'worker'
    ): array {
        $rule = self::findApplicableRule($serviceId, $categoryId, $workerTier);

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

        $ruleApplied = 'Global Standard Default (' . $adminPct . '% Admin / ' . $workerPct . '% Tech)';
        $ruleScope = 'global';
        if ($rule) {
            if (!empty($rule['service_id'])) {
                $ruleScope = 'service';
                $ruleApplied = 'Service Rule: ' . ($rule['service_name'] ?? 'Service #' . $rule['service_id']) . ' (' . $adminPct . '% Admin / ' . $workerPct . '% Tech)';
            } elseif (!empty($rule['category_id'])) {
                $ruleScope = 'category';
                $ruleApplied = 'Category Rule: ' . ($rule['category_name'] ?? 'Category #' . $rule['category_id']) . ' (' . $adminPct . '% Admin / ' . $workerPct . '% Tech)';
            } elseif (!empty($rule['notes'])) {
                $ruleScope = 'custom';
                $ruleApplied = $rule['notes'];
            }
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
            'rule_applied'        => $ruleApplied,
            'rule_id'             => $rule['id'] ?? null,
            'rule_scope'          => $ruleScope,
        ];
    }
}
