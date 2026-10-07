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

    <div>
      <a href="<?= View::url('/admin/staff/create') ?>" class="btn btn-sm btn-brand font-weight-bold px-3">
        <i class="fa fa-user-plus mr-1"></i>Add New Technician
      </a>
    </div>
  </div>

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
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="text-right">
                  <a href="<?= View::url('/admin/staff/' . $s['id']) ?>" class="btn btn-sm btn-brand-outline px-2 py-1 font-weight-bold">
                    Profile &rarr;
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

