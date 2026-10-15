<?php
use App\Core\View;
$staff = $staff ?? [];
?>

<div class="admin-staff-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Field Technicians & Staff</h4>
      <p class="text-muted small mb-0">Manage workforce profiles, active field assignments, trade skills, and duty status.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <a href="<?= View::url('/admin/partner-applications') ?>" class="btn btn-sm btn-outline-warning text-dark font-weight-bold px-3 mr-2" style="background: #fffbeb; border: 1.5px solid #fde68a;">
        <i class="fa fa-id-card-o mr-1 text-warning"></i>Partner Applications
        <?php if (!empty($pendingAppsCount) && $pendingAppsCount > 0): ?>
          <span class="badge badge-warning text-dark ml-1"><?= (int)$pendingAppsCount ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= View::url('/admin/staff/create') ?>" class="btn btn-sm btn-brand font-weight-bold px-3">
        <i class="fa fa-user-plus mr-1"></i>Add New Technician
      </a>
    </div>
  </div>

  <?php if (!empty($pendingAppsCount) && $pendingAppsCount > 0): ?>
    <div class="alert alert-warning mb-3 border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap" style="border-radius: 12px; background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b !important; padding: 14px 18px;">
      <div class="d-flex align-items-center mb-2 mb-md-0">
        <i class="fa fa-id-card-o text-warning mr-3" style="font-size: 24px;"></i>
        <div>
          <strong class="d-block" style="font-size: 14.5px;">You have <?= (int)$pendingAppsCount ?> new partner application(s) awaiting verification!</strong>
          <span class="small" style="color: #78350f;">New technicians submitted online do not appear in this active workforce directory until you approve them.</span>
        </div>
      </div>
      <a href="<?= View::url('/admin/partner-applications') ?>" class="btn btn-sm font-weight-bold shadow-sm" style="background: #f59e0b; color: #ffffff; border-radius: 8px; padding: 6px 16px;">
        Review & Approve Applications &rarr;
      </a>
    </div>
  <?php endif; ?>

  <div class="stat-card p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Technician</th>
            <th>Contact</th>
            <th>Rating</th>
            <th>Availability</th>
            <th>Workload</th>
            <th>Account Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($staff)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="fa fa-users fa-2x mb-2 d-block text-muted" style="opacity: 0.3;"></i>
                No staff members registered. Click "Add New Technician" above.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($staff as $s): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mr-2 text-primary font-weight-bold" style="width: 38px; height: 38px; border: 1.5px solid #f25b29;">
                      <?= strtoupper(substr($s['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <a href="<?= View::url('/admin/staff/' . $s['id']) ?>" class="font-weight-bold text-dark">
                        <?= View::e($s['name']) ?>
                      </a>
                      <div class="text-muted" style="font-size: 11px;">ID: #<?= (int)$s['id'] ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div><a href="tel:<?= View::e($s['phone']) ?>" class="text-success small font-weight-bold"><i class="fa fa-phone mr-1"></i><?= View::e($s['phone']) ?></a></div>
                  <div class="text-muted small"><?= View::e($s['email'] ?? 'No email') ?></div>
                </td>
                <td>
                  <span class="badge badge-warning text-dark font-weight-bold">
                    <i class="fa fa-star text-warning"></i> <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?>
                  </span>
                  <span class="text-muted small ml-1">(<?= (int)($s['rating_count'] ?? 0) ?>)</span>
                </td>
                <td>
                  <?php if (!empty($s['is_available'])): ?>
                    <span class="badge badge-success px-2 py-1"><i class="fa fa-circle mr-1" style="font-size: 8px;"></i>Available</span>
                  <?php else: ?>
                    <span class="badge badge-secondary px-2 py-1"><i class="fa fa-circle mr-1" style="font-size: 8px;"></i>Off Duty</span>
                  <?php endif; ?>
                  <div class="text-muted small mt-1"><?= View::e($s['availability_note'] ?? '') ?></div>
                </td>
                <td>
                  <div class="small">
                    <strong><?= (int)$s['active_jobs'] ?></strong> active / <strong><?= (int)$s['total_jobs'] ?></strong> total
                  </div>
                </td>
                <td>
                  <?php if ($s['status'] === 'active'): ?>
                    <span class="badge badge-success px-2 py-1">Active</span>
                  <?php elseif ($s['status'] === 'pending'): ?>
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1"><i class="fa fa-clock-o mr-1"></i>Pending Review</span>
                  <?php else: ?>
                    <span class="badge badge-danger px-2 py-1">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="text-right">
                  <div class="d-inline-flex align-items-center">
                    <?php if ($s['status'] === 'pending'): ?>
                      <form action="<?= View::url('/admin/staff/' . $s['id'] . '/approve') ?>" method="POST" class="d-inline mr-1" onsubmit="return confirm('Approve this partner? This will generate credentials and send an approval email to <?= View::e($s['email']) ?>.');">
                        <?= View::csrfField() ?>
                        <input type="hidden" name="redirect" value="<?= View::url('/admin/staff') ?>">
                        <button type="submit" class="btn btn-sm btn-success px-2 py-1 font-weight-bold" title="Approve application and send login details">
                          <i class="fa fa-check-circle mr-1"></i>Approve
                        </button>
                      </form>
                    <?php endif; ?>
                    <a href="<?= View::url('/admin/staff/' . $s['id']) ?>" class="btn btn-sm btn-brand-outline px-2 py-1 font-weight-bold">
                      Profile &rarr;
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

