<?php
use App\Core\View;

$service         = $service ?? [];
$category        = $category ?? [];
$groupedVariants = $groupedVariants ?? [];
$allVariants     = $allVariants ?? [];

$sImg = trim((string)($service['image'] ?? ''));
$sImgUrl = '';
if (!empty($sImg)) {
    if (str_starts_with($sImg, 'http://') || str_starts_with($sImg, 'https://') || str_starts_with($sImg, 'uploads/') || str_starts_with($sImg, 'assets/') || str_starts_with($sImg, 'img/')) {
        $sImgUrl = View::asset($sImg);
    } else {
        $sImgUrl = View::asset('img/' . $sImg);
    }
}
if (empty($sImgUrl)) {
    $sImgUrl = View::asset('img/refixel-cleaning.jpg');
}
?>

<div class="admin-service-variants-page">
  <!-- Breadcrumb Navigation -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb bg-transparent p-0 mb-0 small">
      <li class="breadcrumb-item"><a href="<?= View::url('/admin/dashboard') ?>" class="text-muted">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= View::url('/admin/services') ?>" class="text-muted">Services Catalog</a></li>
      <li class="breadcrumb-item active text-dark font-weight-bold" aria-current="page"><?= View::e($service['name']) ?> Variations</li>
    </ol>
  </nav>

  <!-- Top Hero Header Card -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: #ffffff;">
    <div class="card-body p-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center" style="gap: 16px;">
        <div class="d-flex align-items-center" style="gap: 16px;">
          <div class="border rounded overflow-hidden shadow-sm" style="width: 72px; height: 72px; min-width: 72px; background: #f8fafc;">
            <img src="<?= $sImgUrl ?>" alt="<?= View::e($service['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= View::asset('img/refixel-cleaning.jpg') ?>';">
          </div>
          <div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
              <h4 class="font-weight-bold mb-0 text-dark"><?= View::e($service['name']) ?></h4>
              <span class="badge badge-light border text-secondary font-weight-bold px-2 py-1">
                <?= View::e($category['name'] ?? 'Home Service') ?>
              </span>
            </div>
            <div class="text-muted small mt-1">
              Base Starting Price: <strong class="text-success font-weight-bold">₹<?= number_format((float)$service['starting_price'], 2) ?></strong>
              &bull; Standard Duration: <strong><?= (int)($service['duration_minutes'] ?? 60) ?> mins</strong>
              &bull; Slug: <code>/<?= View::e($service['slug']) ?></code>
            </div>
          </div>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
          <a href="<?= View::url('/' . $service['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-2">
            <i class="fa fa-external-link mr-1"></i>View Live Page
          </a>
          <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2 text-white shadow-sm" style="background: #f25b29;" data-toggle="modal" data-target="#addVariantModal">
            <i class="fa fa-plus mr-1"></i>Add New Option / Size
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Instruction Banner -->
  <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important; border-radius: 10px; color: #1e40af;">
    <div class="mr-3" style="font-size: 20px;"><i class="fa fa-info-circle"></i></div>
    <div class="small">
      <strong>Customer Experience Preview:</strong> Customers can select these variations (such as tank capacities 500L, 1000L, sofa seats 4-seater, 6-seater, or kitchen size &amp; cleaning type) on the service page just like choosing a shirt size. The price, duration, and checkout summary update in real time.
    </div>
  </div>

  <!-- Options List Organized By Group -->
  <?php if (empty($groupedVariants)): ?>
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 14px; background: #ffffff;">
      <div class="card-body">
        <div class="stat-icon-circle mx-auto mb-3" style="background: rgba(242, 91, 41, 0.1); color: #f25b29; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px;">
          <i class="fa fa-sliders"></i>
        </div>
        <h5 class="font-weight-bold text-dark mb-1">No Custom Options or Variations Added</h5>
        <p class="text-muted small mb-4" style="max-width: 480px; margin-inline: auto;">
          This service currently sells at the flat starting price of ₹<?= number_format((float)$service['starting_price'], 2) ?>. Click below to add your first size or capacity option.
        </p>
        <button type="button" class="btn btn-brand font-weight-bold px-4 py-2 text-white" style="background: #f25b29;" data-toggle="modal" data-target="#addVariantModal">
          <i class="fa fa-plus mr-1"></i>Create First Option
        </button>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($groupedVariants as $groupName => $variants): ?>
      <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 14px; background: #ffffff;">
        <div class="card-header bg-light py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
          <div class="d-flex align-items-center" style="gap: 8px;">
            <span class="badge px-2.5 py-1 text-white font-weight-bold" style="background: #0f172a; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
              Option Group
            </span>
            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 17px;"><?= View::e($groupName) ?></h5>
            <span class="badge badge-light border text-muted small"><?= count($variants) ?> options</span>
          </div>

          <button type="button" class="btn btn-sm btn-outline-brand font-weight-bold add-to-group-btn" data-group="<?= View::e($groupName) ?>" style="border-color: #f25b29; color: #f25b29;">
            <i class="fa fa-plus mr-1"></i>Add to <?= View::e($groupName) ?>
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="bg-white text-muted small border-bottom">
              <tr>
                <th class="py-2.5 px-4">Option / Size Name</th>
                <th class="py-2.5">Price (₹)</th>
                <th class="py-2.5">Duration</th>
                <th class="py-2.5">Marketing Badge</th>
                <th class="py-2.5">Default Choice</th>
                <th class="py-2.5">Sort</th>
                <th class="py-2.5">Status</th>
                <th class="py-2.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($variants as $v): ?>
                <tr>
                  <td class="align-middle px-4">
                    <span class="font-weight-bold text-dark" style="font-size: 14.5px;"><?= View::e($v['name']) ?></span>
                  </td>
                  <td class="align-middle">
                    <strong class="text-success font-weight-bold" style="font-size: 16px;">
                      <?= ($groupName === 'Cleaning Type' && (float)$v['price'] > 0 ? '+' : '') ?>₹<?= number_format((float)$v['price'], 2) ?>
                    </strong>
                  </td>
                  <td class="align-middle text-muted small">
                    <?= (int)$v['duration_minutes'] > 0 ? ((int)$v['duration_minutes'] . ' mins') : 'Included' ?>
                  </td>
                  <td class="align-middle">
                    <?php if (!empty($v['badge'])): ?>
                      <span class="badge px-2 py-1 font-weight-bold" style="background: #fff3ec; color: #f25b29; border: 1px solid #ffdacf;">
                        <?= View::e($v['badge']) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted small">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle">
                    <?php if (!empty($v['is_default'])): ?>
                      <span class="badge badge-success px-2 py-1 font-weight-bold" title="Preselected by default on customer service page">
                        <i class="fa fa-check mr-1"></i>Default
                      </span>
                    <?php else: ?>
                      <span class="text-muted small">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle text-muted small">
                    <?= (int)$v['sort_order'] ?>
                  </td>
                  <td class="align-middle">
                    <form method="POST" action="<?= View::url('/admin/services/variants/' . $v['id'] . '/toggle-status') ?>" class="d-inline">
                      <?= View::csrfField() ?>
                      <?php if (!empty($v['is_active'])): ?>
                        <button type="submit" class="btn btn-sm btn-outline-success py-0.5 px-2 font-weight-bold" style="font-size: 11.5px;">
                          Active
                        </button>
                      <?php else: ?>
                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0.5 px-2 font-weight-bold" style="font-size: 11.5px;">
                          Disabled
                        </button>
                      <?php endif; ?>
                    </form>
                  </td>
                  <td class="align-middle px-4 text-right text-nowrap">
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary py-1 px-2.5 font-weight-bold edit-variant-btn"
                            data-id="<?= (int)$v['id'] ?>"
                            data-group="<?= View::e($v['group_name']) ?>"
                            data-name="<?= View::e($v['name']) ?>"
                            data-price="<?= (float)$v['price'] ?>"
                            data-duration="<?= (int)$v['duration_minutes'] ?>"
                            data-badge="<?= View::e($v['badge'] ?? '') ?>"
                            data-is-default="<?= !empty($v['is_default']) ? '1' : '0' ?>"
                            data-sort-order="<?= (int)$v['sort_order'] ?>"
                            title="Edit price & details">
                      <i class="fa fa-pencil mr-1"></i>Edit
                    </button>

                    <form method="POST" action="<?= View::url('/admin/services/variants/' . $v['id'] . '/delete') ?>" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to permanently delete option \'<?= View::e(addslashes($v['name'])) ?>\'?');">
                      <?= View::csrfField() ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 font-weight-bold" title="Delete option">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Modal: Add New Variant -->
<div class="modal fade" id="addVariantModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 14px;">
      <form method="POST" action="<?= View::url('/admin/services/' . $service['id'] . '/variants') ?>">
        <?= View::csrfField() ?>
        <div class="modal-header border-bottom pb-3">
          <h5 class="modal-title font-weight-bold text-dark">
            <i class="fa fa-plus mr-1" style="color: #f25b29;"></i>Add Option / Variation
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Option Group Name <span class="text-danger">*</span></label>
            <input type="text" name="group_name" id="addVariantGroupName" class="form-control" list="groupSuggestions" placeholder="e.g. Capacity, Seats, Kitchen Size, Cleaning Type" required>
            <datalist id="groupSuggestions">
              <option value="Capacity">
              <option value="Seats">
              <option value="Kitchen Size">
              <option value="Home Size">
              <option value="Cleaning Type">
              <option value="Duration">
              <option value="Room Count">
            </datalist>
            <small class="text-muted">Options with the same group name are displayed as mutually exclusive selection pills.</small>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Option Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. 500 Litres, 6 Seater, Medium, Deep Cleaning" required>
          </div>

          <div class="row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Price (₹) <span class="text-danger">*</span></label>
              <input type="number" name="price" step="0.01" min="0" class="form-control" placeholder="e.g. 400.00" required>
              <small class="text-muted">For primary size: full price. For type: add-on modifier (+₹).</small>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Duration (Minutes)</label>
              <input type="number" name="duration_minutes" min="0" value="60" class="form-control" placeholder="60">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Badge Label (Optional)</label>
              <input type="text" name="badge" class="form-control" placeholder="e.g. Popular, Best Value, Most Booked">
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Sort Order</label>
              <input type="number" name="sort_order" value="1" class="form-control">
            </div>
          </div>

          <div class="form-check mt-2">
            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="addIsDefault">
            <label class="form-check-label small font-weight-bold text-dark" for="addIsDefault">
              Set as preselected default in this group
            </label>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand btn-sm font-weight-bold px-3 text-white" style="background: #f25b29;">Save Option</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit Variant -->
<div class="modal fade" id="editVariantModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 14px;">
      <form method="POST" id="editVariantForm" action="">
        <?= View::csrfField() ?>
        <div class="modal-header border-bottom pb-3">
          <h5 class="modal-title font-weight-bold text-dark">
            <i class="fa fa-pencil mr-1" style="color: #f25b29;"></i>Edit Option / Price
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Option Group Name <span class="text-danger">*</span></label>
            <input type="text" name="group_name" id="editVariantGroupName" class="form-control" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Option Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="editVariantName" class="form-control" required>
          </div>

          <div class="row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Price (₹) <span class="text-danger">*</span></label>
              <input type="number" name="price" id="editVariantPrice" step="0.01" min="0" class="form-control" required>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Duration (Minutes)</label>
              <input type="number" name="duration_minutes" id="editVariantDuration" min="0" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Badge Label (Optional)</label>
              <input type="text" name="badge" id="editVariantBadge" class="form-control">
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Sort Order</label>
              <input type="number" name="sort_order" id="editVariantSort" class="form-control">
            </div>
          </div>

          <div class="form-check mt-2">
            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="editIsDefault">
            <label class="form-check-label small font-weight-bold text-dark" for="editIsDefault">
              Set as preselected default in this group
            </label>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand btn-sm font-weight-bold px-3 text-white" style="background: #f25b29;">Update Option</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Add to specific group button helper
  document.querySelectorAll('.add-to-group-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var grp = this.getAttribute('data-group');
      var input = document.getElementById('addVariantGroupName');
      if (input) input.value = grp;
      $('#addVariantModal').modal('show');
    });
  });

  // Edit variant button
  document.querySelectorAll('.edit-variant-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var group = this.getAttribute('data-group');
      var name = this.getAttribute('data-name');
      var price = this.getAttribute('data-price');
      var duration = this.getAttribute('data-duration');
      var badge = this.getAttribute('data-badge');
      var isDefault = this.getAttribute('data-is-default') === '1';
      var sortOrder = this.getAttribute('data-sort-order');

      var form = document.getElementById('editVariantForm');
      if (form) {
        form.action = '<?= View::url('/admin/services/variants/') ?>' + id + '/update';
      }

      document.getElementById('editVariantGroupName').value = group || '';
      document.getElementById('editVariantName').value = name || '';
      document.getElementById('editVariantPrice').value = price || '0';
      document.getElementById('editVariantDuration').value = duration || '60';
      document.getElementById('editVariantBadge').value = badge || '';
      document.getElementById('editVariantSort').value = sortOrder || '0';
      document.getElementById('editIsDefault').checked = isDefault;

      $('#editVariantModal').modal('show');
    });
  });
});
</script>
