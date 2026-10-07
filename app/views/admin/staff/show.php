<?php
use App\Core\View;
$user = $user ?? [];
$profile = $profile ?? [];
$skills = $skills ?? [];
$allCategories = $allCategories ?? [];
$jobs = $jobs ?? [];
$earnings = $earnings ?? [];
$earningsSummary = $earningsSummary ?? ['total_earned' => 0, 'this_month' => 0, 'settled' => 0, 'pending' => 0];

$skillIds = array_column($skills, 'id');
?>

<div class="admin-staff-show">
  <!-- Back navigation -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <a href="<?= View::url('/admin/staff') ?>" class="text-muted small font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i>Back to Staff Directory
      </a>
      <h4 class="font-weight-bold text-dark mb-0"><?= View::e($user['name']) ?></h4>
    </div>

    <!-- Toggle Status Button -->
    <form method="POST" action="<?= View::url('/admin/staff/' . $user['id'] . '/toggle-status') ?>">
      <?= View::csrfField() ?>
      <?php if ($user['status'] === 'active'): ?>
        <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold" onclick="return confirm('Are you sure you want to deactivate this technician?')">
          <i class="fa fa-ban mr-1"></i>Deactivate Account
        </button>
      <?php else: ?>
        <button type="submit" class="btn btn-sm btn-success font-weight-bold">
          <i class="fa fa-check mr-1"></i>Activate Account
        </button>
      <?php endif; ?>
    </form>
  </div>

  <div class="row">
    <!-- Left Column: Profile Card & Skills -->
    <div class="col-lg-4">
      <!-- Profile Card -->
      <div class="stat-card mb-3 text-center">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 text-primary font-weight-bold" style="width: 72px; height: 72px; font-size: 28px; border: 2.5px solid #f25b29;">
          <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <h5 class="font-weight-bold text-dark mb-1"><?= View::e($user['name']) ?></h5>
        <div class="text-muted small mb-2"><i class="fa fa-phone mr-1"></i><?= View::e($user['phone']) ?></div>
        <?php if (!empty($user['email'])): ?>
          <div class="text-muted small mb-3"><i class="fa fa-envelope mr-1"></i><?= View::e($user['email']) ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-center gap-2 mb-3">
          <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold">
            <i class="fa fa-star text-warning"></i> <?= number_format((float)($profile['rating_avg'] ?? 5.0), 1) ?> (<?= (int)($profile['rating_count'] ?? 0) ?>)
          </span>
          <?php if (!empty($profile['is_available'])): ?>
            <span class="badge badge-success px-3 py-1 font-weight-bold">🟢 Available</span>
          <?php else: ?>
            <span class="badge badge-secondary px-3 py-1 font-weight-bold">🔴 Off Duty</span>
          <?php endif; ?>
        </div>

        <div class="p-2 bg-light rounded small text-muted text-left">
          <strong>Duty Note:</strong> <?= View::e($profile['availability_note'] ?? 'No note') ?>
        </div>
      </div>

      <!-- Skills Assignment Form -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-wrench text-primary mr-2"></i>Authorized Trade Skills</h6>
        
        <form method="POST" action="<?= View::url('/admin/staff/' . $user['id'] . '/skills') ?>">
          <?= View::csrfField() ?>

          <div class="mb-3">
            <?php foreach ($allCategories as $cat): ?>
              <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" name="skills[]" value="<?= $cat['id'] ?>" class="custom-control-input" id="cat_<?= $cat['id'] ?>" <?= in_array($cat['id'], $skillIds, true) ? 'checked' : '' ?>>
                <label class="custom-control-label small font-weight-bold text-dark" for="cat_<?= $cat['id'] ?>">
                  <?= View::e($cat['name']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>

          <button type="submit" class="btn btn-sm btn-brand btn-block font-weight-bold">
            <i class="fa fa-save mr-1"></i>Update Trade Skills
          </button>
        </form>
      </div>

      <!-- Financial Overview -->
      <div class="stat-card">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-money text-success mr-2"></i>Earnings & Payouts</h6>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Total Commission Earned:</span>
          <strong class="text-success">₹<?= number_format((float)$earningsSummary['total_earned'], 2) ?></strong>
        </div>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Current Month (<?= date('M Y') ?>):</span>
          <strong class="text-dark">₹<?= number_format((float)$earningsSummary['this_month'], 2) ?></strong>
        </div>
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Settled Payouts:</span>
          <strong class="text-dark">₹<?= number_format((float)$earningsSummary['settled'], 2) ?></strong>
        </div>
        <div class="d-flex justify-content-between py-1">
          <span class="text-muted small">Pending Settlement:</span>
          <strong class="text-warning">₹<?= number_format((float)$earningsSummary['pending'], 2) ?></strong>
        </div>
      </div>
    </div>

    <!-- Right Column: Assigned Jobs History -->
    <div class="col-lg-8">
      <div class="stat-card p-0 overflow-hidden mb-3">
        <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-briefcase text-muted mr-2"></i>Assigned Jobs History (<?= count($jobs) ?>)</h6>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Booking #</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Schedule</th>
                <th>Status</th>
                <th class="text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($jobs)): ?>
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">No jobs assigned to this technician yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($jobs as $j): ?>
                  <tr>
                    <td>
                      <a href="<?= View::url('/admin/bookings/' . $j['booking_id']) ?>" class="font-weight-bold text-dark">
                        #<?= View::e($j['booking_no']) ?>
                      </a>
                    </td>
                    <td>
                      <div class="font-weight-bold text-dark small"><?= View::e($j['customer_name']) ?></div>
                      <div class="text-muted small"><?= View::e($j['customer_phone']) ?></div>
                    </td>
                    <td>
                      <div class="small font-weight-bold"><?= View::e($j['service_name']) ?></div>
                    </td>
                    <td>
                      <div class="small font-weight-bold text-dark"><?= View::e($j['preferred_date'] ?? 'Flexible') ?></div>
                    </td>
                    <td>
                      <span class="badge badge-status-<?= View::e($j['status']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $j['status'])) ?>
                      </span>
                    </td>
                    <td class="text-right">
                      <a href="<?= View::url('/admin/bookings/' . $j['booking_id']) ?>" class="btn btn-sm btn-brand-outline px-2 py-1">
                        View &rarr;
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

