<?php
use App\Core\View;
$profile = $profile ?? [];
$stats = $stats ?? ['today' => 0, 'in_progress' => 0, 'completed' => 0, 'total' => 0];
$earningsSummary = $earningsSummary ?? ['this_month' => 0, 'total_earned' => 0, 'pending' => 0];
$todayJobs = $todayJobs ?? [];
$upcomingJobs = $upcomingJobs ?? [];
$inProgressJobs = $inProgressJobs ?? [];
$isAvailable = !empty($profile['is_available']);
?>

<div class="staff-dashboard">
  <!-- Profile & Status Banner -->
  <div class="card border-0 shadow-sm rounded-lg mb-3" style="background: linear-gradient(135deg, #f25b29 0%, #d44d20 100%); color: #fff;">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="text-white-50 small text-uppercase font-weight-bold tracking-wide">Technician Portal</span>
          <h5 class="mb-1 font-weight-bold text-white"><?= View::e($user['name'] ?? 'Technician') ?></h5>
          <div class="d-flex align-items-center small text-white-50">
            <span class="badge <?= $isAvailable ? 'badge-success' : 'badge-secondary' ?> mr-2">
              <i class="fa fa-circle mr-1" style="font-size: 8px;"></i><?= $isAvailable ? 'Available for Jobs' : 'Unavailable / Off Duty' ?>
            </span>
            <span><i class="fa fa-star text-warning mr-1"></i><?= number_format((float)($profile['rating_avg'] ?? 5.0), 1) ?> (<?= (int)($profile['rating_count'] ?? 0) ?> reviews)</span>
          </div>
        </div>
        <div class="text-right">
          <a href="<?= View::url('/staff/profile') ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="fa fa-sliders mr-1"></i>Status
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Key Metrics 4-Box Grid -->
  <div class="row no-gutters mx-n1 mb-3">
    <div class="col-6 p-1">
      <div class="card border-0 shadow-sm rounded-lg h-100 p-3 bg-white text-center">
        <span class="text-muted small font-weight-bold">TODAY'S JOBS</span>
        <h3 class="font-weight-bold text-dark my-1"><?= (int)$stats['today'] ?></h3>
        <a href="<?= View::url('/staff/jobs?filter=today') ?>" class="small font-weight-bold text-success">View Today's &rarr;</a>
      </div>
    </div>
    <div class="col-6 p-1">
      <div class="card border-0 shadow-sm rounded-lg h-100 p-3 bg-white text-center">
        <span class="text-muted small font-weight-bold">IN PROGRESS</span>
        <h3 class="font-weight-bold text-primary my-1"><?= (int)$stats['in_progress'] ?></h3>
        <a href="<?= View::url('/staff/jobs?filter=in_progress') ?>" class="small font-weight-bold text-primary">View Active &rarr;</a>
      </div>
    </div>
    <div class="col-6 p-1">
      <div class="card border-0 shadow-sm rounded-lg h-100 p-3 bg-white text-center">
        <span class="text-muted small font-weight-bold">THIS MONTH</span>
        <h3 class="font-weight-bold text-dark my-1">₹<?= number_format((float)$earningsSummary['this_month'], 0) ?></h3>
        <a href="<?= View::url('/staff/earnings') ?>" class="small font-weight-bold text-success">Payouts &rarr;</a>
      </div>
    </div>
    <div class="col-6 p-1">
      <div class="card border-0 shadow-sm rounded-lg h-100 p-3 bg-white text-center">
        <span class="text-muted small font-weight-bold">COMPLETED</span>
        <h3 class="font-weight-bold text-dark my-1"><?= (int)$stats['completed'] ?></h3>
        <a href="<?= View::url('/staff/jobs?filter=completed') ?>" class="small font-weight-bold text-muted">History &rarr;</a>
      </div>
    </div>
  </div>

  <!-- Active Job In Progress Alert / Card (if any) -->
  <?php if (!empty($inProgressJobs)): ?>
    <?php $activeJob = $inProgressJobs[0]; ?>
    <div class="card border-0 shadow-sm rounded-lg mb-3" style="border-left: 4px solid #1d4ed8 !important; background: #eff6ff;">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge badge-status-in_progress"><i class="fa fa-play mr-1"></i>Active Job In Progress</span>
          <span class="font-weight-bold text-primary">#<?= View::e($activeJob['booking_no']) ?></span>
        </div>
        <h6 class="font-weight-bold mb-1 text-dark"><?= View::e($activeJob['service_name']) ?></h6>
        <p class="text-muted small mb-2"><i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($activeJob['address']) ?></p>
        <div class="d-flex gap-2">
          <a href="<?= View::url('/staff/jobs/' . $activeJob['id']) ?>" class="btn btn-sm btn-primary flex-fill mr-1 font-weight-bold">
            <i class="fa fa-eye mr-1"></i>Open Job Details
          </a>
          <a href="<?= View::url('/staff/jobs/' . $activeJob['id'] . '/complete') ?>" class="btn btn-sm btn-success flex-fill font-weight-bold">
            <i class="fa fa-check-circle mr-1"></i>Complete Job
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Today's Assigned Schedule -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="font-weight-bold mb-0 text-dark">
      <i class="fa fa-calendar-check-o text-success mr-1"></i>Today's Assigned Schedule
    </h6>
    <a href="<?= View::url('/staff/jobs?filter=today') ?>" class="small font-weight-bold text-success">See all (<?= count($todayJobs) ?>)</a>
  </div>

  <?php if (empty($todayJobs)): ?>
    <div class="job-card text-center py-4 text-muted">
      <i class="fa fa-calendar-o fa-2x mb-2 text-muted" style="opacity: 0.4;"></i>
      <p class="mb-0 small">No jobs scheduled for today yet.</p>
    </div>
  <?php else: ?>
    <?php foreach ($todayJobs as $job): ?>
      <div class="job-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="font-weight-bold text-dark">#<?= View::e($job['booking_no']) ?></span>
            <span class="badge badge-status-<?= View::e($job['status']) ?> ml-1"><?= ucfirst(str_replace('_', ' ', $job['status'])) ?></span>
          </div>
          <div class="text-right">
            <span class="small font-weight-bold text-dark"><i class="fa fa-clock-o text-muted mr-1"></i><?= View::e($job['preferred_time'] ?? 'Flexible') ?></span>
          </div>
        </div>

        <h6 class="font-weight-bold text-dark mb-1"><?= View::e($job['service_name']) ?></h6>
        <div class="text-muted small mb-2">
          <div><i class="fa fa-user-circle-o mr-1"></i><strong><?= View::e($job['customer_name']) ?></strong> &bull; <?= View::e($job['customer_phone']) ?></div>
          <div><i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($job['address']) ?><?= !empty($job['pincode']) ? ' - ' . View::e($job['pincode']) : '' ?></div>
        </div>

        <?php if (!empty($job['issue_details'])): ?>
          <div class="bg-light p-2 rounded small text-dark mb-2">
            <span class="text-muted font-weight-bold">Note:</span> <?= View::e($job['issue_details']) ?>
          </div>
        <?php endif; ?>

        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
          <span class="text-muted small">Est. ₹<?= number_format((float)($job['starting_price'] ?? 0), 0) ?></span>
          <div>
            <a href="<?= View::url('/staff/jobs/' . $job['id']) ?>" class="btn btn-sm btn-brand px-3">
              View Details <i class="fa fa-arrow-right ml-1"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Upcoming Schedule Preview -->
  <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
    <h6 class="font-weight-bold mb-0 text-dark">
      <i class="fa fa-clock-o text-info mr-1"></i>Upcoming Schedule
    </h6>
    <a href="<?= View::url('/staff/jobs?filter=upcoming') ?>" class="small font-weight-bold text-success">See all (<?= count($upcomingJobs) ?>)</a>
  </div>

  <?php if (empty($upcomingJobs)): ?>
    <div class="job-card text-center py-3 text-muted">
      <p class="mb-0 small">No upcoming future jobs queued.</p>
    </div>
  <?php else: ?>
    <?php foreach (array_slice($upcomingJobs, 0, 3) as $job): ?>
      <div class="job-card py-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="font-weight-bold text-dark small"><?= View::e($job['service_name']) ?></div>
            <div class="text-muted" style="font-size: 11px;">
              <i class="fa fa-calendar mr-1"></i><?= View::e($job['preferred_date'] ?? 'Upcoming') ?> &bull; <?= View::e($job['preferred_time'] ?? '') ?>
            </div>
          </div>
          <div>
            <a href="<?= View::url('/staff/jobs/' . $job['id']) ?>" class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size: 11px;">
              Details
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
