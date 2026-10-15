<div class="container-fluid p-0">
  <!-- Section Header with Single-Click Delete All Button -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div class="mb-2 mb-md-0">
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-wrench text-info mr-2"></i>Technicians Directory
      </h3>
      <p class="text-muted small mb-0">Manage all registered field staff & service technicians. Single-click purge removes all staff safely.</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-sm-0">
      <a href="<?= \App\Core\View::url('/admin/staff/create') ?>" class="btn btn-primary font-weight-bold mr-2">
        <i class="fa fa-user-plus mr-1"></i> Add New Technician
      </a>
      <button type="button" class="btn btn-danger font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalSectionDeleteStaff" <?= empty($staffMembers) ? 'disabled' : '' ?>>
        <i class="fa fa-trash mr-1"></i> Delete All Technicians (<?= $totalCount ?>)
      </button>
    </div>
  </div>

  <!-- Technicians Table -->
  <div class="sa-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 sa-table-min-lg">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">ID</th>
            <th style="white-space: nowrap;">Technician</th>
            <th style="white-space: nowrap;">Contact</th>
            <th style="white-space: nowrap;">Rating</th>
            <th style="white-space: nowrap;">Jobs Done</th>
            <th style="white-space: nowrap;">Status</th>
            <th style="white-space: nowrap;">Joined On</th>
            <th class="text-right" style="white-space: nowrap;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($staffMembers)): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="fa fa-users fa-3x text-muted mb-2 d-block"></i>
                No technicians registered in the database.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($staffMembers as $s): ?>
              <tr>
                <td class="font-weight-bold text-dark" style="white-space: nowrap;">#<?= $s['id'] ?></td>
                <td>
                  <div class="font-weight-bold text-dark text-truncate" style="max-width: 180px;"><?= \App\Core\View::e($s['name']) ?></div>
                  <small class="text-muted"><?= \App\Core\View::e($s['availability_note'] ?? 'Field Staff') ?></small>
                </td>
                <td style="white-space: nowrap;">
                  <div><i class="fa fa-phone text-muted mr-1"></i><?= \App\Core\View::e($s['phone'] ?? 'N/A') ?></div>
                  <small class="text-muted"><i class="fa fa-envelope text-muted mr-1"></i><?= \App\Core\View::e($s['email'] ?? 'N/A') ?></small>
                </td>
                <td style="white-space: nowrap;">
                  <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                    ★ <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?> (<?= $s['rating_count'] ?? 0 ?>)
                  </span>
                </td>
                <td style="white-space: nowrap;">
                  <span class="badge badge-light border text-dark font-weight-bold">
                    <?= $s['completed_jobs_count'] ?? 0 ?> / <?= $s['jobs_count'] ?? 0 ?> Jobs
                  </span>
                </td>
                <td style="white-space: nowrap;">
                  <?php if ($s['status'] === 'active'): ?>
                    <span class="badge badge-success px-2 py-1">Active</span>
                  <?php else: ?>
                    <span class="badge badge-secondary px-2 py-1">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted small" style="white-space: nowrap;"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="<?= \App\Core\View::url('/admin/staff/' . $s['id']) ?>" class="btn btn-sm btn-outline-secondary mr-1" title="View Technician Details">
                    <i class="fa fa-eye"></i> View
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteStaff(<?= $s['id'] ?>)" title="Delete Technician">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Delete All Staff -->
<div class="modal fade" id="modalSectionDeleteStaff" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-user-times mr-2"></i>Delete All Technicians?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/staff/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-exclamation-triangle fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= $totalCount ?> Technician accounts?</h5>
          <p class="text-muted small mb-0">
            This will permanently delete every technician account, remove their skill categories, clear availability schedules, and unlink active job dispatches safely.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Technicians
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<form id="deleteSingleStaffForm" action="" method="POST" style="display:none;">
  <?= \App\Core\View::csrfField() ?>
</form>

<script>
function confirmDeleteStaff(id) {
  if (confirm('Permanently delete technician #' + id + '? Active assignments will be reset.')) {
    var f = document.getElementById('deleteSingleStaffForm');
    f.action = '<?= \App\Core\View::url('/superadmin/staff/') ?>' + id + '/delete';
    f.submit();
  }
}
</script>
