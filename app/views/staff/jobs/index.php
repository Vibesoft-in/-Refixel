<?php
use App\Core\View;
$filter = $filter ?? 'today';
$jobs = $jobs ?? [];
$counts = $counts ?? ['today' => 0, 'upcoming' => 0, 'in_progress' => 0, 'completed' => 0, 'all' => 0];
?>

<div class="staff-jobs-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="font-weight-bold mb-0 text-dark">My Assigned Jobs</h5>
    <span class="badge badge-light border text-muted px-2 py-1"><?= count($jobs) ?> total</span>
  </div>

  <!-- Filter Navigation Tabs -->
  <div class="d-flex overflow-auto pb-2 mb-3" style="gap: 6px; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
    <a href="<?= View::url('/staff/jobs?filter=today') ?>" 
       class="btn btn-sm rounded-pill font-weight-bold px-3 text-nowrap <?= $filter === 'today' ? 'btn-brand' : 'btn-light border text-muted' ?>">
      Today <span class="badge <?= $filter === 'today' ? 'badge-light text-dark' : 'badge-secondary' ?> ml-1"><?= (int)$counts['today'] ?></span>
    </a>
    <a href="<?= View::url('/staff/jobs?filter=in_progress') ?>" 
       class="btn btn-sm rounded-pill font-weight-bold px-3 text-nowrap <?= $filter === 'in_progress' ? 'btn-primary' : 'btn-light border text-muted' ?>">
      Active <span class="badge <?= $filter === 'in_progress' ? 'badge-light text-dark' : 'badge-secondary' ?> ml-1"><?= (int)$counts['in_progress'] ?></span>
    </a>
    <a href="<?= View::url('/staff/jobs?filter=upcoming') ?>" 
       class="btn btn-sm rounded-pill font-weight-bold px-3 text-nowrap <?= $filter === 'upcoming' ? 'btn-brand' : 'btn-light border text-muted' ?>">
      Upcoming <span class="badge <?= $filter === 'upcoming' ? 'badge-light text-dark' : 'badge-secondary' ?> ml-1"><?= (int)$counts['upcoming'] ?></span>
    </a>
    <a href="<?= View::url('/staff/jobs?filter=completed') ?>" 
       class="btn btn-sm rounded-pill font-weight-bold px-3 text-nowrap <?= $filter === 'completed' ? 'btn-brand' : 'btn-light border text-muted' ?>">
      Completed <span class="badge <?= $filter === 'completed' ? 'badge-light text-dark' : 'badge-secondary' ?> ml-1"><?= (int)$counts['completed'] ?></span>
    </a>
    <a href="<?= View::url('/staff/jobs?filter=all') ?>" 
       class="btn btn-sm rounded-pill font-weight-bold px-3 text-nowrap <?= $filter === 'all' ? 'btn-brand' : 'btn-light border text-muted' ?>">
      All (<?= (int)$counts['all'] ?>)
    </a>
  </div>

  <?php if (empty($jobs)): ?>
    <div class="card border-0 shadow-sm rounded-lg text-center p-5 bg-white">
      <div class="mb-3">
        <i class="fa fa-folder-open-o fa-3x text-muted" style="opacity: 0.35;"></i>
      </div>
      <h6 class="font-weight-bold text-dark">No jobs found</h6>
      <p class="text-muted small mb-3">You do not have any jobs under the "<?= ucfirst($filter) ?>" tab right now.</p>
      <div>
        <a href="<?= View::url('/staff/jobs?filter=all') ?>" class="btn btn-sm btn-brand px-3">
          View All Jobs
        </a>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($jobs as $job): ?>
      <div class="job-card position-relative">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="font-weight-bold text-dark" style="font-size: 15px;">#<?= View::e($job['booking_no']) ?></span>
            <span class="badge badge-status-<?= View::e($job['status']) ?> ml-1">
              <?= ucfirst(str_replace('_', ' ', $job['status'])) ?>
            </span>
          </div>
          <div class="text-right small text-muted font-weight-bold">
            <i class="fa fa-calendar mr-1"></i><?= View::e($job['preferred_date'] ?? date('Y-m-d')) ?>
          </div>
        </div>

        <h6 class="font-weight-bold text-dark mb-1" style="font-size: 15px;"><?= View::e($job['service_name']) ?></h6>

        <div class="text-muted small mb-2">
          <div class="mb-1"><i class="fa fa-user-circle-o mr-1 text-secondary"></i><strong><?= View::e($job['customer_name']) ?></strong> &bull; <?= View::e($job['customer_phone']) ?></div>
          <div><i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($job['address']) ?><?= !empty($job['pincode']) ? ' - ' . View::e($job['pincode']) : '' ?></div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2 flex-wrap" style="gap: 8px;">
          <div>
            <div class="small text-muted mb-1">
              <i class="fa fa-clock-o mr-1"></i>Slot: <strong><?= View::e($job['preferred_time'] ?? 'Flexible') ?></strong>
            </div>
            <div class="d-inline-flex align-items-center px-2 py-1 rounded" style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3);">
              <i class="fa fa-money text-success mr-1"></i>
              <span class="small text-muted mr-1">Labour Income:</span>
              <strong class="text-success font-weight-bold" style="font-size: 15px;">₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?></strong>
            </div>
          </div>

          <div class="d-flex gap-2">
            <?php if ($job['status'] === 'assigned'): ?>
              <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/accept') ?>" class="d-inline mr-1">
                <?= View::csrfField() ?>
                <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3 shadow-sm">
                  <i class="fa fa-check mr-1"></i>Accept (₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?>)
                </button>
              </form>
            <?php elseif ($job['status'] === 'accepted'): ?>
              <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/start') ?>" class="d-inline mr-1">
                <?= View::csrfField() ?>
                <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
                  <i class="fa fa-play mr-1"></i>Start
                </button>
              </form>
            <?php elseif ($job['status'] === 'in_progress'): ?>
              <a href="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" class="btn btn-sm btn-success font-weight-bold px-3 mr-1">
                <i class="fa fa-check-circle mr-1"></i>Complete
              </a>
            <?php endif; ?>

            <a href="<?= View::url('/staff/jobs/' . $job['id']) ?>" class="btn btn-sm btn-brand-outline px-3">
              Details <i class="fa fa-angle-right ml-1"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
