<?php
use App\Core\View;
$categories = $categories ?? [];

// Helper to resolve asset path for category image & icon cleanly
$resolveAsset = function(?string $path, string $defaultPrefix = 'img/'): string {
    $path = trim((string)$path);
    if (empty($path)) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'uploads/') || str_starts_with($path, 'assets/') || str_starts_with($path, 'img/') || str_starts_with($path, 'icons/')) {
        return View::asset($path);
    }
    return View::asset($defaultPrefix . $path);
};
?>

<div class="admin-categories-page">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <a href="<?= View::url('/admin/services') ?>" class="text-muted small font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i>Back to Services Catalog
      </a>
      <h4 class="font-weight-bold mb-1 text-dark mt-1">Homepage Services & Trade Categories</h4>
      <p class="text-muted small mb-0">Directly controls the "Complete Home Solutions" service grid and marquee ticker on the customer homepage.</p>
    </div>

    <div class="mt-2 mt-md-0">
      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2 shadow-sm" data-toggle="modal" data-target="#addCategoryModal">
        <i class="fa fa-plus mr-1"></i>Add New Service / Category
      </button>
    </div>
  </div>

  <!-- Real-time sync announcement banner -->
  <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 10px; background-color: #e0f2fe; color: #0369a1;">
    <i class="fa fa-info-circle fa-2x mr-3 text-info"></i>
    <div class="small">
      <strong>Homepage Live Grid Sync:</strong> Every active category listed below is automatically rendered as an interactive service card on the REFIXEL homepage. You can upload banner photos, assign custom icons, rewrite descriptions, or drag-and-drop sort orders anytime.
    </div>
  </div>

  <div class="stat-card p-0 overflow-hidden shadow-sm border bg-white" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead class="bg-light">
          <tr>
            <th class="py-3 text-dark" style="width: 75px;">Banner</th>
            <th class="py-3 text-dark" style="width: 55px;">Icon</th>
            <th class="py-3 text-dark">Category / Service</th>
            <th class="py-3 text-dark">Slug / URL</th>
            <th class="py-3 text-dark text-center">Services</th>
            <th class="py-3 text-dark text-center">Sort Order</th>
            <th class="py-3 text-dark text-center">Homepage Status</th>
            <th class="py-3 text-right text-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">No service categories configured yet. Click "Add New Service / Category" above.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($categories as $c): 
              $bannerUrl = $resolveAsset($c['image'] ?? $c['icon'] ?? '', 'img/');
              $iconVal = trim((string)($c['icon'] ?? ''));
              $isImageIcon = !empty($iconVal) && (str_contains($iconVal, '/') || str_ends_with($iconVal, '.png') || str_ends_with($iconVal, '.jpg') || str_ends_with($iconVal, '.webp') || str_ends_with($iconVal, '.svg'));
              $iconUrl = $isImageIcon ? $resolveAsset($iconVal, 'icons/') : '';
            ?>
              <tr>
                <!-- Banner Image -->
                <td class="align-middle">
                  <div class="border rounded overflow-hidden bg-light shadow-sm" style="width: 65px; height: 42px;">
                    <?php if (!empty($bannerUrl)): ?>
                      <img src="<?= $bannerUrl ?>" alt="<?= View::e($c['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='<?= View::asset('img/service-pest-control.jpg') ?>';">
                    <?php else: ?>
                      <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                        <i class="fa fa-image"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Icon -->
                <td class="align-middle">
                  <div class="p-1 rounded bg-light border d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                    <?php if ($isImageIcon && !empty($iconUrl)): ?>
                      <img src="<?= $iconUrl ?>" alt="icon" style="width: 24px; height: 24px; object-fit: contain;" onerror="this.style.display='none';">
                    <?php else: ?>
                      <i class="fa <?= View::e(!empty($iconVal) ? $iconVal : 'fa-wrench') ?> text-primary" style="font-size: 16px;"></i>
                    <?php endif; ?>
                  </div>
                </td>

                <!-- Category Name & Description -->
                <td class="align-middle">
                  <div class="font-weight-bold text-dark" style="font-size: 15px;"><?= View::e($c['name']) ?></div>
                  <div class="text-muted small text-truncate" style="max-width: 260px;" title="<?= View::e($c['description'] ?? '') ?>">
                    <?= View::e($c['description'] ?? 'No description set') ?>
                  </div>
                </td>

                <!-- Slug / Canonical URL -->
                <td class="align-middle">
                  <code>/<?= View::e($c['slug']) ?></code>
                  <a href="<?= View::url('/' . (str_ends_with($c['slug'], '-services') ? $c['slug'] : ($c['slug'] === 'cleaning' ? 'cleaning-services' : $c['slug'] . '-services'))) ?>" target="_blank" class="text-muted ml-1" title="Preview public category page">
                    <i class="fa fa-external-link small"></i>
                  </a>
                </td>

                <!-- Services mapped count -->
                <td class="align-middle text-center">
                  <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                    <?= (int)($c['service_count'] ?? 0) ?> items
                  </span>
                </td>

                <!-- Sort Order -->
                <td class="align-middle text-center">
                  <span class="badge badge-secondary px-2 py-1 font-weight-bold">#<?= (int)$c['sort_order'] ?></span>
                </td>

                <!-- Homepage Active Status -->
                <td class="align-middle text-center">
                  <?php if (!empty($c['is_active'])): ?>
                    <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i>Homepage Live</span>
                  <?php else: ?>
                    <span class="badge badge-danger px-2 py-1"><i class="fa fa-eye-slash mr-1"></i>Hidden</span>
                  <?php endif; ?>
                </td>

                <!-- Actions -->
                <td class="align-middle text-right text-nowrap">
                  <form method="POST" action="<?= View::url('/admin/services/categories/' . $c['id'] . '/toggle-status') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($c['is_active']) ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold" title="Toggle active status">
                      <?= !empty($c['is_active']) ? 'Hide' : 'Show' ?>
                    </button>
                  </form>

                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-category-btn"
                    data-id="<?= (int)$c['id'] ?>"
                    data-name="<?= View::e($c['name']) ?>"
                    data-slug="<?= View::e($c['slug']) ?>"
                    data-image="<?= View::e($c['image'] ?? '') ?>"
                    data-image-preview="<?= $bannerUrl ?>"
                    data-icon="<?= View::e($c['icon'] ?? '') ?>"
                    data-icon-preview="<?= $iconUrl ?>"
                    data-sort-order="<?= (int)$c['sort_order'] ?>"
                    data-description="<?= View::e($c['description'] ?? '') ?>"
                    title="Edit category">
                    <i class="fa fa-pencil"></i>
                  </button>

                  <form method="POST" action="<?= View::url('/admin/services/categories/' . $c['id'] . '/delete') ?>" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to delete category \'<?= View::e(addslashes($c['name'])) ?>\'?');">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 font-weight-bold" title="Delete category">
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

<!-- Modal: Add New Category / Homepage Service -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="<?= View::url('/admin/services/categories') ?>" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fa fa-plus-circle text-brand mr-2"></i>Add New Homepage Service / Category</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-7">
              <div class="form-group mb-3">
                <label class="font-weight-bold small">Category / Service Title <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Deep Cleaning, Painting Services, False Ceiling..." required>
                <small class="text-muted">Displays as the main title on the homepage card.</small>
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group mb-3">
                <label class="font-weight-bold small">URL Slug <span class="text-muted">(Optional)</span></label>
                <input type="text" name="slug" class="form-control" placeholder="e.g. cleaning, painting-services">
                <small class="text-muted">Auto-generated from title if left blank.</small>
              </div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Card Subtitle / Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="e.g. Home, Office, Deep Cleaning Sofa, Carpet, Kitchen, Bathroom"></textarea>
            <small class="text-muted">Shown under the title on the homepage service card.</small>
          </div>

          <div class="row">
            <!-- Card Banner Image -->
            <div class="col-md-6">
              <div class="card p-3 bg-light border mb-3">
                <label class="font-weight-bold small mb-2"><i class="fa fa-image mr-1 text-primary"></i>Homepage Card Banner Image</label>
                <input type="file" name="image" class="form-control-file mb-2" accept="image/*">
                <small class="text-muted d-block mb-2">Upload JPG, PNG, or WEBP (Recommended: ~380x180 px)</small>
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend"><span class="input-group-text">Or filename</span></div>
                  <input type="text" name="image_path" class="form-control" placeholder="e.g. refixel-cleaning.jpg">
                </div>
              </div>
            </div>

            <!-- Card Icon -->
            <div class="col-md-6">
              <div class="card p-3 bg-light border mb-3">
                <label class="font-weight-bold small mb-2"><i class="fa fa-circle-o mr-1 text-primary"></i>Badge Icon</label>
                <input type="file" name="icon_file" class="form-control-file mb-2" accept="image/*">
                <small class="text-muted d-block mb-2">Upload PNG icon or specify asset icon path</small>
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend"><span class="input-group-text">Icon Path / Class</span></div>
                  <input type="text" name="icon" class="form-control" value="icons/cleaning icon.png" placeholder="e.g. icons/cleaning icon.png or fa-wrench">
                </div>
              </div>
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold small">Homepage Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="10" style="max-width: 150px;">
            <small class="text-muted">Lower numbers appear first on the homepage grid (e.g. 1, 2, 3...).</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold px-4">Create Service & Publish</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Category / Homepage Service -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="editCategoryForm" action="" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fa fa-pencil text-brand mr-2"></i>Edit Homepage Service / Category</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-7">
              <div class="form-group mb-3">
                <label class="font-weight-bold small">Category / Service Title <span class="text-danger">*</span></label>
                <input type="text" name="name" id="edit_cat_name" class="form-control" required>
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-group mb-3">
                <label class="font-weight-bold small">URL Slug</label>
                <input type="text" name="slug" id="edit_cat_slug" class="form-control">
              </div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Card Subtitle / Description</label>
            <textarea name="description" id="edit_cat_description" class="form-control" rows="2"></textarea>
          </div>

          <div class="row">
            <!-- Edit Card Banner Image -->
            <div class="col-md-6">
              <div class="card p-3 bg-light border mb-3">
                <label class="font-weight-bold small mb-2"><i class="fa fa-image mr-1 text-primary"></i>Homepage Card Banner Image</label>
                <div class="d-flex align-items-center mb-2">
                  <div class="border rounded overflow-hidden mr-2 bg-white shadow-sm" style="width: 60px; height: 38px;">
                    <img id="edit_img_preview" src="" alt="preview" style="width: 100%; height: 100%; object-fit: cover;">
                  </div>
                  <span class="small text-muted" id="edit_img_current_text">Current Image</span>
                </div>
                <input type="file" name="image" class="form-control-file mb-2" accept="image/*">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend"><span class="input-group-text">Or path</span></div>
                  <input type="text" name="image_path" id="edit_cat_image" class="form-control" placeholder="e.g. refixel-cleaning.jpg">
                </div>
              </div>
            </div>

            <!-- Edit Card Icon -->
            <div class="col-md-6">
              <div class="card p-3 bg-light border mb-3">
                <label class="font-weight-bold small mb-2"><i class="fa fa-circle-o mr-1 text-primary"></i>Badge Icon</label>
                <div class="d-flex align-items-center mb-2">
                  <div class="border rounded p-1 mr-2 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <img id="edit_icon_preview" src="" alt="icon" style="width: 24px; height: 24px; object-fit: contain;">
                  </div>
                  <span class="small text-muted" id="edit_icon_current_text">Current Icon</span>
                </div>
                <input type="file" name="icon_file" class="form-control-file mb-2" accept="image/*">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend"><span class="input-group-text">Path / Class</span></div>
                  <input type="text" name="icon" id="edit_cat_icon" class="form-control">
                </div>
              </div>
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold small">Homepage Sort Order</label>
            <input type="number" name="sort_order" id="edit_cat_sort_order" class="form-control" value="0" style="max-width: 150px;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold px-4">Update Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var baseUrl = '<?= View::url() ?>';
  document.querySelectorAll('.edit-category-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var name = this.getAttribute('data-name');
      var slug = this.getAttribute('data-slug');
      var image = this.getAttribute('data-image');
      var imagePreview = this.getAttribute('data-image-preview');
      var icon = this.getAttribute('data-icon');
      var iconPreview = this.getAttribute('data-icon-preview');
      var sortOrder = this.getAttribute('data-sort-order');
      var description = this.getAttribute('data-description');

      var form = document.getElementById('editCategoryForm');
      form.action = baseUrl + '/admin/services/categories/' + id + '/update';

      document.getElementById('edit_cat_name').value = name || '';
      document.getElementById('edit_cat_slug').value = slug || '';
      document.getElementById('edit_cat_image').value = image || '';
      document.getElementById('edit_cat_icon').value = icon || '';
      document.getElementById('edit_cat_sort_order').value = sortOrder || '0';
      document.getElementById('edit_cat_description').value = description || '';

      var imgEl = document.getElementById('edit_img_preview');
      if (imagePreview) {
        imgEl.src = imagePreview;
        imgEl.style.display = 'block';
      } else {
        imgEl.style.display = 'none';
      }

      var iconEl = document.getElementById('edit_icon_preview');
      if (iconPreview) {
        iconEl.src = iconPreview;
        iconEl.style.display = 'block';
      } else {
        iconEl.style.display = 'none';
      }

      $('#editCategoryModal').modal('show');
    });
  });
});
</script>
