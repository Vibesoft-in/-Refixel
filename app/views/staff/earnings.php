<?php
use App\Core\View;
$summary = $summary ?? ['total_earned' => 0, 'this_month' => 0, 'settled' => 0, 'pending' => 0];
$earnings = $earnings ?? [];
$selectedMonth = $selectedMonth ?? '';
$availableMonths = $availableMonths ?? [];
?>

<div class="staff-earnings-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h5 class="font-weight-bold mb-0 text-dark">Earnings & Payouts</h5>
      <p class="text-muted small mb-0">Detailed breakdown of company split and technician net earnings.</p>
    </div>
    <span class="badge badge-success px-2 py-1 font-weight-bold">
      <i class="fa fa-check-circle mr-1"></i>Direct Commission
    </span>
  </div>

  <!-- Financial Metrics Grid -->
  <div class="card border-0 shadow-sm rounded-lg mb-3" style="background: linear-gradient(135deg, #f25b29 0%, #d44d20 100%); color: #fff;">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="text-white-50 small text-uppercase font-weight-bold">Total Earnings</div>
        <span class="badge badge-light text-dark font-weight-bold" style="font-size: 10px; opacity: 0.95;">
          Net After Company Split
        </span>
      </div>
      <h2 class="font-weight-bold text-white mb-2">₹<?= number_format((float)$summary['total_earned'], 2) ?></h2>

      <div class="row pt-2 border-top border-white-50 text-white-50">
        <div class="col-4">
          <div style="font-size: 11px;">THIS MONTH</div>
          <div class="text-white font-weight-bold">₹<?= number_format((float)$summary['this_month'], 0) ?></div>
        </div>
        <div class="col-4">
          <div style="font-size: 11px;">SETTLED</div>
          <div class="text-white font-weight-bold">₹<?= number_format((float)$summary['settled'], 0) ?></div>
        </div>
        <div class="col-4">
          <div style="font-size: 11px;">PENDING</div>
          <div class="text-warning font-weight-bold">₹<?= number_format((float)$summary['pending'], 0) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Month Filter (if multiple months exist) -->
  <?php if (!empty($availableMonths)): ?>
    <div class="job-card py-2 mb-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
        <span class="small font-weight-bold text-muted"><i class="fa fa-filter mr-1"></i>Filter by Month:</span>
        <div class="d-flex gap-1 flex-wrap">
          <a href="<?= View::url('/staff/earnings') ?>" class="btn btn-sm <?= empty($selectedMonth) ? 'btn-brand' : 'btn-light border' ?> py-1 px-2 small">
            All
          </a>
          <?php foreach ($availableMonths as $m): ?>
            <a href="<?= View::url('/staff/earnings?month=' . $m) ?>" class="btn btn-sm <?= $selectedMonth === $m ? 'btn-brand' : 'btn-light border' ?> py-1 px-2 small">
              <?= View::e($m) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Earnings History List -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-list text-muted mr-1"></i>Payout Records</h6>
    <span class="small text-muted font-weight-bold"><?= count($earnings) ?> records</span>
  </div>

  <?php if (empty($earnings)): ?>
    <div class="card border-0 shadow-sm rounded-lg text-center p-4 bg-white">
      <i class="fa fa-money fa-2x text-muted mb-2" style="opacity: 0.35;"></i>
      <h6 class="font-weight-bold text-dark">No earnings recorded yet</h6>
      <p class="text-muted small mb-0">Payout records will appear here as soon as you complete your assigned jobs.</p>
    </div>
  <?php else: ?>
    <?php foreach ($earnings as $item): ?>
      <?php
        $gross = (float)($item['gross_amount'] ?? 0);
        $adminFee = (float)($item['admin_fee'] ?? 0);
        $techAmount = (float)($item['amount'] ?? 0);
        $adminPct = (float)($item['admin_percent'] ?? 25.0);
        $workerPct = (float)($item['worker_percent'] ?? 75.0);
        $matCost = (float)($item['material_cost'] ?? 0);
      ?>
      <div class="job-card py-3 mb-3">
        <!-- Top Row: Booking info and Net Payout -->
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="badge badge-light border text-dark font-weight-bold mb-1">#<?= View::e($item['booking_no']) ?></span>
            <h6 class="font-weight-bold text-dark mb-1" style="font-size: 15.5px;"><?= View::e($item['service_name']) ?></h6>
            <div class="text-muted small">
              <i class="fa fa-calendar-o mr-1"></i><?= View::e(substr((string)$item['created_at'], 0, 10)) ?> &bull; Month: <?= View::e($item['month']) ?>
            </div>
          </div>
          <div class="text-right">
            <div class="text-muted small font-weight-bold" style="font-size: 11px;">YOUR NET PAYOUT</div>
            <h4 class="font-weight-bold text-success mb-1" style="font-size: 19px;">₹<?= number_format($techAmount, 2) ?></h4>
            <?php if (!empty($item['is_settled'])): ?>
              <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>Settled</span>
            <?php else: ?>
              <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o mr-1"></i>Pending Payout</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Service Split Breakdown: Company vs Technician Share -->
        <div class="mt-2 p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 12px;">
          <div class="d-flex justify-content-between align-items-center mb-1 text-muted">
            <span>Total Service Amount:</span>
            <strong class="text-dark">₹<?= number_format($gross, 2) ?></strong>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-1 text-muted">
            <span>
              <i class="fa fa-building-o mr-1 text-danger"></i>Company Split (Refixel <?= $adminPct ?>%):
            </span>
            <strong class="text-danger">- ₹<?= number_format($adminFee, 2) ?></strong>
          </div>
          <?php if ($matCost > 0): ?>
            <div class="d-flex justify-content-between align-items-center mb-1 text-muted">
              <span><i class="fa fa-wrench mr-1 text-info"></i>Material Reimbursement:</span>
              <strong class="text-info">+ ₹<?= number_format($matCost, 2) ?></strong>
            </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between align-items-center pt-1 border-top" style="color: #166534;">
            <strong style="font-size: 12px;">
              <i class="fa fa-user-circle mr-1 text-success"></i>Technician Share (<?= $workerPct ?>%):
            </strong>
            <strong class="text-success" style="font-size: 13.5px;">₹<?= number_format($techAmount, 2) ?></strong>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
