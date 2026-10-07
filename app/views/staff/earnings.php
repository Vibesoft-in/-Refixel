<?php
use App\Core\View;
$summary = $summary ?? ['total_earned' => 0, 'this_month' => 0, 'settled' => 0, 'pending' => 0];
$earnings = $earnings ?? [];
$selectedMonth = $selectedMonth ?? '';
$availableMonths = $availableMonths ?? [];
?>

<div class="staff-earnings-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="font-weight-bold mb-0 text-dark">Earnings & Payouts</h5>
    <span class="badge badge-success px-2 py-1">Direct Commission</span>
  </div>

  <!-- Financial Metrics Grid -->
  <div class="card border-0 shadow-sm rounded-lg mb-3" style="background: linear-gradient(135deg, #f25b29 0%, #d44d20 100%); color: #fff;">
    <div class="card-body p-3">
      <div class="text-white-50 small text-uppercase font-weight-bold">Total Earnings</div>
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
      <div class="d-flex align-items-center justify-content-between">
        <span class="small font-weight-bold text-muted"><i class="fa fa-filter mr-1"></i>Filter by Month:</span>
        <div class="d-flex gap-1">
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
  <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-list text-muted mr-1"></i>Payout Records</h6>

  <?php if (empty($earnings)): ?>
    <div class="card border-0 shadow-sm rounded-lg text-center p-4 bg-white">
      <i class="fa fa-money fa-2x text-muted mb-2" style="opacity: 0.35;"></i>
      <h6 class="font-weight-bold text-dark">No earnings recorded yet</h6>
      <p class="text-muted small mb-0">Payout records will appear here as soon as you complete your assigned jobs.</p>
    </div>
  <?php else: ?>
    <?php foreach ($earnings as $item): ?>
      <div class="job-card py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="badge badge-light border text-dark font-weight-bold mb-1">#<?= View::e($item['booking_no']) ?></span>
            <h6 class="font-weight-bold text-dark mb-1"><?= View::e($item['service_name']) ?></h6>
            <div class="text-muted small">
              <i class="fa fa-calendar-o mr-1"></i><?= View::e(substr((string)$item['created_at'], 0, 10)) ?> &bull; Month: <?= View::e($item['month']) ?>
            </div>
          </div>
          <div class="text-right">
            <h5 class="font-weight-bold text-success mb-1">₹<?= number_format((float)$item['amount'], 2) ?></h5>
            <?php if (!empty($item['is_settled'])): ?>
              <span class="badge badge-success"><i class="fa fa-check mr-1"></i>Settled</span>
            <?php else: ?>
              <span class="badge badge-warning text-dark"><i class="fa fa-clock-o mr-1"></i>Pending</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
