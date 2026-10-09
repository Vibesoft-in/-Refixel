<?php
use App\Core\View;
$categories = $categories ?? [];
?>

<div class="admin-categories-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="<?= View::url('/admin/services') ?>" class="text-muted small font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i>Back to Services
      </a>
      <h4 class="font-weight-bold mb-1 text-dark mt-1">Service Categories</h4>
      <p class="text-muted small mb-0">High-level trade categories shown on website navigation and skill filters.</p>
    </div>

    <div>
      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2" data-toggle="modal" data-target="#addCategoryModal">
        <i class="fa fa-plus mr-1"></i>Add New Category
      </button>
    </div>
  </div>

  <div class="stat-card p-0 overflow-hidden shadow-sm border" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light">
          <tr>
            <th class="py-3 text-dark">Icon</th>
            <th class="py-3 text-dark">Category Name</th>
            <th class="py-3 text-dark">Slug</th>
            <th class="py-3 text-dark">Services Mapped</th>
            <th class="py-3 text-dark">Sort Order</th>
            <th class="py-3 text-dark">Status</th>
            <th class="py-3 text-right text-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">No categories configured.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($categories as $c): ?>
              <tr>
                <td class="align-middle">
                  <div class="p-2 rounded bg-light d-inline-block text-primary text-center" style="width: 38px;">
                    <i class="fa <?= View::e($c['icon'] ?? 'fa-wrench') ?> fa-lg"></i>
                  </div>
                </td>
                <td class="align-middle">
                  <div class="font-weight-bold text-dark"><?= View::e($c['name']) ?></div>
                  <div class="text-muted small"><?= View::e($c['description'] ?? '') ?></div>
                </td>
                <td class="align-middle">
                  <code>/<?= View::e($c['slug']) ?></code>
                </td>
                <td class="align-middle">
                  <span class="badge badge-light border text-dark font-weight-bold">
                    <?= (int)($c['service_count'] ?? 0) ?> services
                  </span>
                </td>
                <td class="align-middle">
                  <span class="badge badge-light border"><?= (int)$c['sort_order'] ?></span>
                </td>
                <td class="align-middle">
                  <?php if (!empty($c['is_active'])): ?>
                    <span class="badge badge-success px-2 py-1">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger px-2 py-1">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-right">
                  <form method="POST" action="<?= View::url('/admin/services/categories/' . $c['id'] . '/toggle-status') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($c['is_active']) ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold" title="Toggle active status">
                      <?= !empty($c['is_active']) ? 'Disable' : 'Enable' ?>
                    </button>
                  </form>

                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-category-btn"
                    data-id="<?= (int)$c['id'] ?>"
                    data-name="<?= View::e($c['name']) ?>"
                    data-icon="<?= View::e($c['icon'] ?? 'fa-wrench') ?>"
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

<!-- Modal: Add New Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= View::url('/admin/services/categories') ?>">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Add Trade Category</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Pest Control Services" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">FontAwesome Icon Class</label>
            <input type="text" name="icon" class="form-control" value="fa-wrench" placeholder="e.g. fa-bug, fa-paint-brush">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="0">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Brief overview of services in this category..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editCategoryForm" action="">
        <?= View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Edit Category</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_cat_name" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">FontAwesome Icon Class</label>
            <input type="text" name="icon" id="edit_cat_icon" class="form-control">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Sort Order</label>
            <input type="number" name="sort_order" id="edit_cat_sort_order" class="form-control" value="0">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Description</label>
            <textarea name="description" id="edit_cat_description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold">Update Category</button>
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
      var icon = this.getAttribute('data-icon');
      var sortOrder = this.getAttribute('data-sort-order');
      var description = this.getAttribute('data-description');

      var form = document.getElementById('editCategoryForm');
      form.action = baseUrl + '/admin/services/categories/' + id + '/update';

      document.getElementById('edit_cat_name').value = name;
      document.getElementById('edit_cat_icon').value = icon;
      document.getElementById('edit_cat_sort_order').value = sortOrder;
      document.getElementById('edit_cat_description').value = description;

      $('#editCategoryModal').modal('show');
    });
  });
});
</script>
