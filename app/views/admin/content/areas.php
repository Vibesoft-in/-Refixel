<?php
use App\Core\View;
$areas = $areas ?? [];
?>

<div class="admin-areas-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Service Areas & Target Locations</h4>
      <p class="text-muted small mb-0">Control all serviceable cities, towns, colonies, and postal pincodes where REFIXEL operates.</p>
    </div>

    <div>
      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2" data-toggle="modal" data-target="#addAreaModal">
        <i class="fa fa-plus mr-1"></i>Add Service Area
      </button>
    </div>
  </div>

  <div class="stat-card p-0 overflow-hidden shadow-sm border" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light">
          <tr>
            <th class="py-3 text-dark">City / Territory</th>
            <th class="py-3 text-dark">Pincode</th>
            <th class="py-3 text-dark">Area / Colony Name</th>
            <th class="py-3 text-dark">Service Status</th>
            <th class="py-3 text-right text-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($areas)): ?>
            <tr><td colspan="5" class="text-center py-5 text-muted">No service areas configured yet. Click "Add Service Area" above.</td></tr>
          <?php else: ?>
            <?php foreach ($areas as $a): ?>
              <tr>
                <td class="align-middle">
                  <strong class="text-dark"><i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($a['city']) ?></strong>
                </td>
                <td class="align-middle"><code><?= View::e($a['pincode']) ?></code></td>
                <td class="align-middle"><?= View::e($a['area_name'] ?? $a['name'] ?? '') ?></td>
                <td class="align-middle">
                  <span class="badge <?= !empty($a['is_active']) ? 'badge-success' : 'badge-secondary' ?> px-2 py-1">
                    <?= !empty($a['is_active']) ? 'Active Dispatch' : 'Paused' ?>
                  </span>
                </td>
                <td class="align-middle text-right">
                  <form method="POST" action="<?= View::url('/admin/content/areas/' . $a['id'] . '/toggle') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($a['is_active']) ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold" title="Toggle status">
                      <?= !empty($a['is_active']) ? 'Pause' : 'Enable' ?>
                    </button>
                  </form>

                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-area-btn"
                    data-id="<?= (int)$a['id'] ?>"
                    data-city="<?= View::e($a['city']) ?>"
                    data-pincode="<?= View::e($a['pincode']) ?>"
                    data-name="<?= View::e($a['area_name'] ?? $a['name'] ?? '') ?>"
                    title="Edit area">
                    <i class="fa fa-pencil"></i>
                  </button>

                  <form method="POST" action="<?= View::url('/admin/content/areas/' . $a['id'] . '/delete') ?>" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to delete this service area (<?= View::e($a['city'] . ' - ' . $a['pincode']) ?>)?');">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 font-weight-bold" title="Delete area">
                      <i class="fa fa-trash"></i>
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
            <label class="font-weight-bold small">City / Town <span class="text-danger">*</span></label>
            <input type="text" name="city" class="form-control" placeholder="e.g. Kashipur, Jaspur, or Gurugram" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
            <input type="text" name="pincode" class="form-control" placeholder="e.g. 244713" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Area / Colony / Sector Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Ramnagar Road, Station Area, etc.">
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

<!-- Modal: Edit Area -->
<div class="modal fade" id="editAreaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editAreaForm" action="">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Edit Service Area</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">City / Town <span class="text-danger">*</span></label>
            <input type="text" name="city" id="edit_area_city" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
            <input type="text" name="pincode" id="edit_area_pincode" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Area / Colony / Sector Name</label>
            <input type="text" name="name" id="edit_area_name" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Update Area</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var baseUrl = '<?= View::url() ?>';
  document.querySelectorAll('.edit-area-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var city = this.getAttribute('data-city');
      var pincode = this.getAttribute('data-pincode');
      var name = this.getAttribute('data-name');

      var form = document.getElementById('editAreaForm');
      form.action = baseUrl + '/admin/content/areas/' + id + '/update';

      document.getElementById('edit_area_city').value = city;
      document.getElementById('edit_area_pincode').value = pincode;
      document.getElementById('edit_area_name').value = name;

      $('#editAreaModal').modal('show');
    });
  });
});
</script>
