<?php
use App\Core\View;
$items = $items ?? [];
$services = $services ?? [];
?>

<div class="admin-gallery-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Before / After Gallery Showcase</h4>
      <p class="text-muted small mb-0">Manage and update all before/after transformation images and showcase cards displayed on the website.</p>
    </div>

    <div>
      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2" data-toggle="modal" data-target="#addGalleryModal">
        <i class="fa fa-plus mr-1"></i>Add Showcase Item
      </button>
    </div>
  </div>

  <div class="row">
    <?php if (empty($items)): ?>
      <div class="col-12">
        <div class="stat-card text-center py-5 text-muted">
          <i class="fa fa-picture-o fa-3x mb-2 d-block text-muted" style="opacity: 0.3;"></i>
          No gallery showcase items added yet. Click "Add Showcase Item" above.
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($items as $item): ?>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="stat-card p-3 h-100 d-flex flex-column justify-content-between shadow-sm border" style="border-radius: 12px;">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                  <i class="fa fa-tag text-muted mr-1"></i><?= View::e($item['service_name'] ?? 'General Service') ?>
                </span>
                <span class="badge <?= !empty($item['is_active']) ? 'badge-success' : 'badge-secondary' ?> px-2 py-1">
                  <?= !empty($item['is_active']) ? 'Active' : 'Hidden' ?>
                </span>
              </div>
              <h6 class="font-weight-bold text-dark mb-3" style="font-size: 15px;"><?= View::e($item['title']) ?></h6>

              <div class="row no-gutters mx-n1 mb-3">
                <div class="col-6 p-1">
                  <div class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">
                    <i class="fa fa-clock-o mr-1"></i>BEFORE
                  </div>
                  <div class="border rounded overflow-hidden bg-light position-relative" style="height: 120px;">
                    <img src="<?= View::asset($item['before_image']) ?>" alt="Before" style="width: 100%; height: 100%; object-fit: cover;">
                  </div>
                </div>
                <div class="col-6 p-1">
                  <div class="small font-weight-bold text-success mb-1 text-uppercase" style="font-size: 11px;">
                    <i class="fa fa-check-circle mr-1"></i>AFTER
                  </div>
                  <div class="border rounded overflow-hidden bg-light position-relative" style="height: 120px;">
                    <img src="<?= View::asset($item['after_image']) ?>" alt="After" style="width: 100%; height: 100%; object-fit: cover;">
                  </div>
                </div>
              </div>
            </div>

            <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
              <form method="POST" action="<?= View::url('/admin/content/gallery/' . $item['id'] . '/toggle') ?>" class="d-inline">
                <?= View::csrfField() ?>
                <button type="submit" class="btn btn-sm <?= !empty($item['is_active']) ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold" title="Toggle visibility">
                  <i class="fa <?= !empty($item['is_active']) ? 'fa-eye-slash' : 'fa-eye' ?> mr-1"></i>
                  <?= !empty($item['is_active']) ? 'Hide' : 'Show' ?>
                </button>
              </form>

              <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-gallery-btn"
                  data-id="<?= (int)$item['id'] ?>"
                  data-title="<?= View::e($item['title']) ?>"
                  data-service-id="<?= (int)($item['service_id'] ?? 0) ?>"
                  data-sort-order="<?= (int)($item['sort_order'] ?? 0) ?>"
                  data-before-img="<?= View::e($item['before_image']) ?>"
                  data-after-img="<?= View::e($item['after_image']) ?>"
                  title="Edit details & images">
                  <i class="fa fa-pencil mr-1"></i>Edit
                </button>

                <form method="POST" action="<?= View::url('/admin/content/gallery/' . $item['id'] . '/delete') ?>" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to permanently delete this showcase item?');">
                  <?= View::csrfField() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 font-weight-bold" title="Delete showcase">
                    <i class="fa fa-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Modal: Add Gallery Item -->
<div class="modal fade" id="addGalleryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= View::url('/admin/content/gallery') ?>" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Add Before/After Showcase</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Showcase Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Sofa Foam Extraction & Sanitization" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Associated Service</label>
            <select name="service_id" class="form-control">
              <option value="0">General Service</option>
              <?php foreach ($services as $svc): ?>
                <option value="<?= $svc['id'] ?>"><?= View::e($svc['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Upload Before Image <span class="text-danger">*</span></label>
            <input type="file" name="before_image" accept="image/*" class="form-control-file" required>
            <small class="form-text text-muted">Supports JPG, PNG, WEBP (Max 5MB)</small>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Upload After Image <span class="text-danger">*</span></label>
            <input type="file" name="after_image" accept="image/*" class="form-control-file" required>
            <small class="form-text text-muted">Supports JPG, PNG, WEBP (Max 5MB)</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Upload Showcase</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Gallery Item -->
<div class="modal fade" id="editGalleryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editGalleryForm" action="" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Edit Before/After Showcase</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Showcase Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="edit_gallery_title" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Associated Service</label>
            <select name="service_id" id="edit_gallery_service_id" class="form-control">
              <option value="0">General Service</option>
              <?php foreach ($services as $svc): ?>
                <option value="<?= $svc['id'] ?>"><?= View::e($svc['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Sort Order</label>
            <input type="number" name="sort_order" id="edit_gallery_sort_order" class="form-control" value="0">
            <small class="form-text text-muted">Lower numbers appear first on the website showcase.</small>
          </div>

          <div class="form-group mb-3 border p-2 rounded bg-light">
            <label class="font-weight-bold small d-block mb-1">Replace Before Image</label>
            <input type="file" name="before_image" accept="image/*" class="form-control-file mb-2">
            <div class="small text-muted mb-1">Or direct file path/URL:</div>
            <input type="text" name="before_image_path" id="edit_gallery_before_path" class="form-control form-control-sm" placeholder="Leave empty to keep existing">
          </div>

          <div class="form-group mb-3 border p-2 rounded bg-light">
            <label class="font-weight-bold small d-block mb-1">Replace After Image</label>
            <input type="file" name="after_image" accept="image/*" class="form-control-file mb-2">
            <div class="small text-muted mb-1">Or direct file path/URL:</div>
            <input type="text" name="after_image_path" id="edit_gallery_after_path" class="form-control form-control-sm" placeholder="Leave empty to keep existing">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var baseUrl = '<?= View::url() ?>';
  document.querySelectorAll('.edit-gallery-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var title = this.getAttribute('data-title');
      var serviceId = this.getAttribute('data-service-id');
      var sortOrder = this.getAttribute('data-sort-order');
      var beforeImg = this.getAttribute('data-before-img');
      var afterImg = this.getAttribute('data-after-img');

      var form = document.getElementById('editGalleryForm');
      form.action = baseUrl + '/admin/content/gallery/' + id + '/update';

      document.getElementById('edit_gallery_title').value = title;
      document.getElementById('edit_gallery_service_id').value = serviceId;
      document.getElementById('edit_gallery_sort_order').value = sortOrder;
      document.getElementById('edit_gallery_before_path').value = beforeImg;
      document.getElementById('edit_gallery_after_path').value = afterImg;

      $('#editGalleryModal').modal('show');
    });
  });
});
</script>
