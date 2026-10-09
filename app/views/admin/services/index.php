<?php
use App\Core\View;
$services = $services ?? [];
$categories = $categories ?? [];
?>

<div class="admin-services-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Services & Pricing Catalog</h4>
      <p class="text-muted small mb-0">Control all marketplace services, descriptions, pricing, duration, and display images.</p>
    </div>

    <div>
      <a href="<?= View::url('/admin/services/categories') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold mr-2">
        <i class="fa fa-th-large mr-1"></i>Manage Categories
      </a>
      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2" data-toggle="modal" data-target="#addServiceModal">
        <i class="fa fa-plus mr-1"></i>Add New Service
      </button>
    </div>
  </div>

  <div class="stat-card p-0 overflow-hidden shadow-sm border" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light">
          <tr>
            <th class="py-3 text-dark" style="width: 70px;">Image</th>
            <th class="py-3 text-dark">Service Name</th>
            <th class="py-3 text-dark">Category</th>
            <th class="py-3 text-dark">Starting Price</th>
            <th class="py-3 text-dark">Duration</th>
            <th class="py-3 text-dark">Status</th>
            <th class="py-3 text-right text-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($services)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No services registered in catalog. Click "Add New Service" above.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($services as $s): ?>
              <tr>
                <td class="align-middle">
                  <div class="border rounded overflow-hidden bg-light" style="width: 50px; height: 50px;">
                    <?php if (!empty($s['image'])): ?>
                      <img src="<?= View::asset($s['image']) ?>" alt="<?= View::e($s['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                      <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                        <i class="fa fa-wrench"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="align-middle">
                  <div class="font-weight-bold text-dark"><?= View::e($s['name']) ?></div>
                  <div class="text-muted small"><code>/<?= View::e($s['slug']) ?></code></div>
                  <?php if (!empty($s['description'])): ?>
                    <div class="text-muted small text-truncate" style="max-width: 250px;" title="<?= View::e($s['description']) ?>">
                      <?= View::e($s['description']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="align-middle">
                  <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                    <?= View::e($s['category_name']) ?>
                  </span>
                </td>
                <td class="align-middle">
                  <strong class="text-success font-weight-bold" style="font-size: 15px;">₹<?= number_format((float)$s['starting_price'], 2) ?></strong>
                </td>
                <td class="align-middle text-muted small">
                  <?= !empty($s['duration_minutes']) ? ((int)$s['duration_minutes'] . ' mins') : '60 mins' ?>
                </td>
                <td class="align-middle">
                  <?php if (!empty($s['is_active'])): ?>
                    <span class="badge badge-success px-2 py-1">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger px-2 py-1">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-right">
                  <form method="POST" action="<?= View::url('/admin/services/' . $s['id'] . '/toggle-status') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($s['is_active']) ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold" title="Toggle active status">
                      <?= !empty($s['is_active']) ? 'Disable' : 'Enable' ?>
                    </button>
                  </form>

                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-service-btn"
                    data-id="<?= (int)$s['id'] ?>"
                    data-name="<?= View::e($s['name']) ?>"
                    data-category-id="<?= (int)$s['category_id'] ?>"
                    data-price="<?= (float)$s['starting_price'] ?>"
                    data-duration="<?= (int)($s['duration_minutes'] ?? 60) ?>"
                    data-description="<?= View::e($s['description'] ?? '') ?>"
                    data-image="<?= View::e($s['image'] ?? '') ?>"
                    title="Edit service">
                    <i class="fa fa-pencil"></i>
                  </button>

                  <form method="POST" action="<?= View::url('/admin/services/' . $s['id'] . '/delete') ?>" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to permanently delete service \'<?= View::e(addslashes($s['name'])) ?>\'?');">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 font-weight-bold" title="Delete service">
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

<!-- Modal: Add New Service -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= View::url('/admin/services') ?>" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Add New Service to Catalog</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Service Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Sofa Deep Shampoo Cleaning" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-control" required>
              <option value="">-- Choose Category --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= View::e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small">Starting Price (INR) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" name="starting_price" class="form-control" placeholder="e.g. 799.00" required>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small">Estimated Duration (mins)</label>
              <input type="number" name="duration_minutes" class="form-control" value="60">
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Service Image</label>
            <input type="file" name="image" accept="image/*" class="form-control-file">
            <small class="form-text text-muted">Upload a photo representing this service (Max 5MB)</small>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Short Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief scope of work included in this service..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Save Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Service -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editServiceForm" action="" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Edit Service Details</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Service Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_svc_name" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Category <span class="text-danger">*</span></label>
            <select name="category_id" id="edit_svc_category_id" class="form-control" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= View::e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small">Starting Price (INR) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" name="starting_price" id="edit_svc_price" class="form-control" required>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small">Estimated Duration (mins)</label>
              <input type="number" name="duration_minutes" id="edit_svc_duration" class="form-control">
            </div>
          </div>

          <div class="form-group mb-3 border p-2 rounded bg-light">
            <label class="font-weight-bold small d-block mb-1">Replace Service Image</label>
            <input type="file" name="image" accept="image/*" class="form-control-file mb-2">
            <div class="small text-muted mb-1">Or direct image path:</div>
            <input type="text" name="image_path" id="edit_svc_image_path" class="form-control form-control-sm" placeholder="Leave empty to keep existing">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Short Description</label>
            <textarea name="description" id="edit_svc_description" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Update Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var baseUrl = '<?= View::url() ?>';
  document.querySelectorAll('.edit-service-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var name = this.getAttribute('data-name');
      var categoryId = this.getAttribute('data-category-id');
      var price = this.getAttribute('data-price');
      var duration = this.getAttribute('data-duration');
      var description = this.getAttribute('data-description');
      var image = this.getAttribute('data-image');

      var form = document.getElementById('editServiceForm');
      form.action = baseUrl + '/admin/services/' + id + '/update';

      document.getElementById('edit_svc_name').value = name;
      document.getElementById('edit_svc_category_id').value = categoryId;
      document.getElementById('edit_svc_price').value = price;
      document.getElementById('edit_svc_duration').value = duration;
      document.getElementById('edit_svc_description').value = description;
      document.getElementById('edit_svc_image_path').value = image;

      $('#editServiceModal').modal('show');
    });
  });
});
</script>
