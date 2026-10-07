<?php
use App\Core\View;
$areas = $areas ?? [];
?>

<div class="admin-areas-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Service Areas & Target Pincodes</h4>
      <p class="text-muted small mb-0">Locations and postal codes where REFIXEL dispatches field technicians.</p>
    </div>

    <div>
      <button type="button" class="btn btn-sm btn-brand font-weight-bold" data-toggle="modal" data-target="#addAreaModal">
        <i class="fa fa-plus mr-1"></i>Add Service Area
      </button>
    </div>
  </div>

  <div class="stat-card p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>City / Territory</th>
            <th>Pincode</th>
            <th>Area / Colony Name</th>
            <th>Service Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($areas)): ?>
            <tr><td colspan="5" class="text-center py-5 text-muted">No service areas configured yet.</td></tr>
          <?php else: ?>
            <?php foreach ($areas as $a): ?>
              <tr>
                <td><strong class="text-dark"><i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($a['city']) ?></strong></td>
                <td><code><?= View::e($a['pincode']) ?></code></td>
                <td><?= View::e($a['area_name'] ?? $a['name'] ?? '') ?></td>
                <td>
                  <span class="badge <?= !empty($a['is_active']) ? 'badge-success' : 'badge-secondary' ?>">
                    <?= !empty($a['is_active']) ? 'Active Dispatch' : 'Paused' ?>
                  </span>
                </td>
                <td class="text-right">
                  <form method="POST" action="<?= View::url('/admin/content/areas/' . $a['id'] . '/toggle') ?>" class="d-inline">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($a['is_active']) ? 'btn-outline-danger' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold">
                      <?= !empty($a['is_active']) ? 'Pause Area' : 'Enable Area' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Add Area -->
<div class="modal fade" id="addAreaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= View::url('/admin/content/areas') ?>">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Add Service Area / Pincode</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">City <span class="text-danger">*</span></label>
            <input type="text" name="city" class="form-control" placeholder="e.g. Kashipur or Gurugram" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
            <input type="text" name="pincode" class="form-control" placeholder="e.g. 244713" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Area / Sector Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Ramnagar Road / Sector 54">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Save Area</button>
        </div>
      </form>
    </div>
  </div>
</div>

