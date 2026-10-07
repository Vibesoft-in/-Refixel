<?php
use App\Core\View;
$user = $user ?? [];
$profile = $profile ?? [];
$skills = $skills ?? [];
$isAvailable = !empty($profile['is_available']);
?>

<div class="staff-profile-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="font-weight-bold mb-0 text-dark">Technician Profile</h5>
    <span class="badge badge-light border text-muted">ID: #<?= (int)($user['id'] ?? 0) ?></span>
  </div>

  <!-- Profile Card -->
  <div class="job-card mb-3 text-center py-4">
    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px; border: 2px solid #f25b29;">
      <i class="fa fa-user-circle-o fa-3x" style="color: #f25b29;"></i>
    </div>
    <h5 class="font-weight-bold text-dark mb-1"><?= View::e($user['name'] ?? 'Technician') ?></h5>
    <p class="text-muted small mb-2"><?= View::e($user['email'] ?? '') ?> &bull; <?= View::e($user['phone'] ?? '') ?></p>

    <div class="d-inline-flex align-items-center px-3 py-1 rounded-pill bg-light border">
      <span class="text-warning font-weight-bold mr-1">
        <i class="fa fa-star"></i> <?= number_format((float)($profile['rating_avg'] ?? 5.0), 1) ?>
      </span>
      <span class="text-muted small">(<?= (int)($profile['rating_count'] ?? 0) ?> verified ratings)</span>
    </div>
  </div>

  <!-- Availability & Duty Status Form -->
  <div class="job-card mb-3">
    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-power-off text-success mr-2"></i>Duty & Dispatch Status</h6>

    <form method="POST" action="<?= View::url('/staff/profile') ?>">
      <?= View::csrfField() ?>

      <div class="form-group mb-3">
        <label class="font-weight-bold small text-dark">Current Work Status</label>
        <select name="is_available" class="form-control">
          <option value="1" <?= $isAvailable ? 'selected' : '' ?>>🟢 Available - Ready for Job Assignment</option>
          <option value="0" <?= !$isAvailable ? 'selected' : '' ?>>🔴 Off Duty / On Leave / Busy</option>
        </select>
        <small class="form-text text-muted">Dispatchers can see your current availability when assigning new incoming bookings.</small>
      </div>

      <div class="form-group mb-3">
        <label class="font-weight-bold small text-dark">Status Note (Optional)</label>
        <input type="text" name="availability_note" class="form-control" value="<?= View::e($profile['availability_note'] ?? 'Available') ?>" placeholder="e.g. On field duty, Available after 2 PM...">
      </div>

      <button type="submit" class="btn btn-brand btn-block font-weight-bold py-2">
        <i class="fa fa-save mr-1"></i>Update Status
      </button>
    </form>
  </div>

  <!-- Skills & Trade Categories -->
  <div class="job-card mb-3">
    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-wrench text-primary mr-2"></i>Registered Trade Skills</h6>
    <p class="small text-muted mb-3">Service categories authorized by admin for direct dispatch to you.</p>

    <?php if (empty($skills)): ?>
      <p class="text-muted small font-italic mb-0">No specific trade skill mapped yet. Contact admin to assign categories.</p>
    <?php else: ?>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($skills as $skill): ?>
          <span class="badge badge-light border text-dark px-3 py-2 mr-2 mb-2 font-weight-bold" style="font-size: 13px;">
            <i class="fa <?= View::e($skill['icon'] ?? 'fa-check') ?> text-success mr-1"></i><?= View::e($skill['name']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Security & Account Settings -->
  <div class="job-card">
    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-lock text-danger mr-2"></i>Security & Authentication</h6>
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <div class="font-weight-bold small text-dark">Password</div>
        <div class="text-muted" style="font-size: 12px;">Last updated via verified credentials</div>
      </div>
      <div>
        <a href="<?= View::url('/change-password') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold">
          Change Password
        </a>
      </div>
    </div>
  </div>
</div>
