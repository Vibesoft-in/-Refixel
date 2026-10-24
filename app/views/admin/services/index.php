<?php
use App\Core\View;
$services      = $services ?? [];
$categories    = $categories ?? [];
$selectedCatId = (int)($selectedCatId ?? 0);
$typeFilter    = $typeFilter ?? 'main';
$search        = $search ?? '';
$stats         = $stats ?? ['total_main' => count($services), 'total_addons' => 0, 'active_main' => 0, 'coming_soon_main' => 0];
?>

<div class="admin-services-page">
  <!-- Top Page Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 pb-2 border-bottom" style="gap: 12px;">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">
        <i class="fa fa-wrench mr-2" style="color: #f25b29;"></i>Services &amp; Pricing Catalog
      </h4>
      <p class="text-muted small mb-0">
        Control marketplace main doorstep services, descriptions, pricing, durations, and images. (Companion add-ons are managed separately in Add-ons Control).
      </p>
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
      <a href="<?= View::url('/admin/services/addons') ?>" class="btn btn-sm btn-outline-brand font-weight-bold" style="border: 1.5px solid #f25b29; color: #f25b29;">
        <i class="fa fa-puzzle-piece mr-1"></i>Manage Add-ons &amp; Pairing (<?= (int)($stats['total_addons'] ?? 0) ?>)
      </a>
      <a href="<?= View::url('/admin/services/categories') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fa fa-th-large mr-1"></i>Categories Order
      </a>
      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2 text-white" style="background: #f25b29;" data-toggle="modal" data-target="#addServiceModal">
        <i class="fa fa-plus mr-1"></i>Add New Service
      </button>
    </div>
  </div>

  <!-- KPI Metrics Cards Row -->
  <div class="row mb-4">
    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #f25b29; background: #fff; border-radius: 10px; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">MAIN SERVICES</span>
          <div class="stat-icon-circle" style="background: rgba(242, 91, 41, 0.15); color: #f25b29; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-cubes"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-dark mb-0"><?= (int)($stats['total_main'] ?? count($services)) ?></h3>
        <small class="text-muted">Primary customer catalog</small>
      </div>
    </div>

    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #10b981; background: #fff; border-radius: 10px; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">ACTIVE LIVE</span>
          <div class="stat-icon-circle" style="background: rgba(16, 185, 129, 0.15); color: #059669; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-check-circle"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-success mb-0"><?= (int)($stats['active_main'] ?? 0) ?></h3>
        <small class="text-success font-weight-bold">Bookable by customers</small>
      </div>
    </div>

    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #f59e0b; background: #fff; border-radius: 10px; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">COMING SOON</span>
          <div class="stat-icon-circle" style="background: rgba(245, 158, 11, 0.15); color: #d97706; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-clock-o"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-warning mb-0"><?= (int)($stats['coming_soon_main'] ?? 0) ?></h3>
        <small class="text-muted">Launch preview mode</small>
      </div>
    </div>

    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #6366f1; background: #fff; border-radius: 10px; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">COMPANION ADD-ONS</span>
          <div class="stat-icon-circle" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-puzzle-piece"></i>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="font-weight-bold text-dark mb-0"><?= (int)($stats['total_addons'] ?? 0) ?></h3>
          <a href="<?= View::url('/admin/services/addons') ?>" class="small font-weight-bold" style="color: #6366f1;">Manage &rarr;</a>
        </div>
        <small class="text-muted">Filtered into Add-ons desk</small>
      </div>
    </div>
  </div>

  <!-- Filter & Category Toolbar -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; background: #ffffff;">
    <div class="card-body p-3">
      <!-- Search & Category Filters -->
      <form method="GET" action="<?= View::url('/admin/services') ?>" class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center" style="gap: 12px;">
        <input type="hidden" name="type" value="<?= View::e($typeFilter) ?>">

        <!-- Left: Quick Category Pills -->
        <div class="d-flex align-items-center flex-wrap overflow-auto pb-1" style="gap: 6px;">
          <a href="<?= View::url('/admin/services?type=' . urlencode($typeFilter) . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
             class="btn btn-sm rounded-pill font-weight-bold px-3 <?= empty($selectedCatId) ? 'btn-brand text-white' : 'btn-light border text-muted' ?>" style="<?= empty($selectedCatId) ? 'background:#f25b29;' : '' ?>">
            All Categories
          </a>
          <?php foreach ($categories as $cat): ?>
            <?php $isActiveCat = ($selectedCatId === (int)$cat['id']); ?>
            <a href="<?= View::url('/admin/services?category_id=' . $cat['id'] . '&type=' . urlencode($typeFilter) . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
               class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $isActiveCat ? 'btn-brand text-white' : 'btn-light border text-muted' ?>" style="<?= $isActiveCat ? 'background:#f25b29;' : '' ?>">
              <?= View::e($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Right: Search Input -->
        <div class="d-flex align-items-center" style="gap: 8px;">
          <div class="input-group input-group-sm" style="min-width: 220px; max-width: 280px;">
            <input type="text" name="q" value="<?= View::e($search) ?>" class="form-control" placeholder="Search service name...">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
            </div>
          </div>
          <?php if (!empty($search) || !empty($selectedCatId)): ?>
            <a href="<?= View::url('/admin/services') ?>" class="btn btn-sm btn-light border text-muted font-weight-bold" title="Reset Filters">
              <i class="fa fa-times mr-1"></i>Reset
            </a>
          <?php endif; ?>
        </div>
      </form>

      <!-- Mode Info Strip -->
      <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small text-muted">
        <div>
          <i class="fa fa-filter text-primary mr-1"></i>
          Showing <strong><?= count($services) ?></strong> Main Catalog Services
          <?php if (!empty($selectedCatId)): ?>
            in <strong><?= View::e(array_column($categories, 'name', 'id')[$selectedCatId] ?? 'Category') ?></strong>
          <?php endif; ?>
          <span class="badge badge-light border ml-2 text-dark font-weight-bold">Add-ons Excluded</span>
        </div>
        <div>
          <a href="<?= View::url('/admin/services/addons') ?>" class="font-weight-bold" style="color: #f25b29;">
            <i class="fa fa-puzzle-piece mr-1"></i>Switch to Add-ons Control Desk (<?= (int)($stats['total_addons'] ?? 0) ?> items) &rarr;
          </a>
        </div>
      </div>
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
            <th class="py-3 text-dark">Type</th>
            <th class="py-3 text-dark">Starting Price</th>
            <th class="py-3 text-dark">Duration</th>
            <th class="py-3 text-dark text-center">Coming Soon</th>
            <th class="py-3 text-dark">Status</th>
            <th class="py-3 text-right text-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($services)): ?>
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">No services registered in catalog. Click "Add New Service" above.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($services as $s): ?>
              <tr>
                <td class="align-middle">
                  <div class="border rounded overflow-hidden bg-light" style="width: 50px; height: 50px;">
                    <?php 
                      $sImg = trim((string)($s['image'] ?? ''));
                      $sImgUrl = '';
                      if (!empty($sImg)) {
                          if (str_starts_with($sImg, 'http://') || str_starts_with($sImg, 'https://') || str_starts_with($sImg, 'uploads/') || str_starts_with($sImg, 'assets/') || str_starts_with($sImg, 'img/')) {
                              $sImgUrl = View::asset($sImg);
                          } else {
                              $sImgUrl = View::asset('img/' . $sImg);
                          }
                      }
                    ?>
                    <?php if (!empty($sImgUrl)): ?>
                      <img src="<?= $sImgUrl ?>" alt="<?= View::e($s['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= View::asset('img/refixel-cleaning.jpg') ?>';">
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
                  <?php if (!empty($s['is_addon'])): ?>
                    <span class="badge badge-warning text-dark px-2 py-1" style="background:#fff3cd; border: 1px solid #ffeeba;" title="Add-on Only (Hidden from main catalog & packages)">
                      <i class="fa fa-puzzle-piece mr-1 text-warning"></i>Add-on Only
                    </span>
                  <?php else: ?>
                    <span class="badge badge-primary px-2 py-1" style="background:#e0f2fe; color:#0369a1; border: 1px solid #bae6fd;" title="Public Main Service (Visible in catalog & packages)">
                      <i class="fa fa-cubes mr-1"></i>Main Service
                    </span>
                  <?php endif; ?>
                </td>
                <td class="align-middle">
                  <strong class="text-success font-weight-bold" style="font-size: 15px;">₹<?= number_format((float)$s['starting_price'], 2) ?></strong>
                </td>
                <td class="align-middle text-muted small">
                  <?= !empty($s['duration_minutes']) ? ((int)$s['duration_minutes'] . ' mins') : '60 mins' ?>
                </td>
                <td class="align-middle text-center text-nowrap">
                  <form method="POST" action="<?= View::url('/admin/services/' . $s['id'] . '/toggle-coming-soon') ?>" class="d-inline">
                    <?= View::csrfField() ?>
                    <?php if (!empty($s['is_coming_soon'])): ?>
                      <button type="submit" class="btn btn-sm font-weight-bold px-3 py-1 shadow-sm" style="background: #fef3c7; color: #92400e; border: 1.5px solid #f59e0b; border-radius: 20px; font-size: 11.5px; transition: all 0.2s;" title="Coming Soon is currently ON. Click to turn OFF (Make Live)">
                        <i class="fa fa-clock-o mr-1 text-warning"></i> Coming Soon: <strong class="text-danger">ON</strong>
                      </button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-sm font-weight-bold px-3 py-1 text-muted" style="background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; border-radius: 20px; font-size: 11.5px; transition: all 0.2s;" title="Coming Soon is currently OFF. Click to turn ON">
                        <i class="fa fa-clock-o mr-1"></i> Coming Soon: <strong>OFF</strong>
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
                <td class="align-middle text-nowrap">
                  <?php if (!empty($s['is_active'])): ?>
                    <span class="badge badge-success px-2 py-1">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger px-2 py-1">Disabled</span>
                  <?php endif; ?>
                  <?php if (!empty($s['is_coming_soon'])): ?>
                    <span class="badge badge-warning text-dark px-2 py-1 ml-1" style="background:#fef3c7; color:#92400e; border: 1px solid #fde68a;" title="Customers can view service, but Booking & Add-to-Cart will show 'Coming Soon'">
                      <i class="fa fa-clock-o mr-1"></i>Coming Soon
                    </span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-right text-nowrap">
                  <form method="POST" action="<?= View::url('/admin/services/' . $s['id'] . '/toggle-coming-soon') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($s['is_coming_soon']) ? 'btn-warning font-weight-bold text-dark' : 'btn-outline-warning' ?> py-1 px-2 font-weight-bold" title="<?= !empty($s['is_coming_soon']) ? 'Turn OFF Coming Soon' : 'Turn ON Coming Soon' ?>" style="<?= !empty($s['is_coming_soon']) ? 'background: #f59e0b; color: #fff !important;' : 'color: #d97706; border-color: #f59e0b;' ?>">
                      <i class="fa fa-clock-o mr-1"></i><?= !empty($s['is_coming_soon']) ? 'Soon: ON' : 'Soon: OFF' ?>
                    </button>
                  </form>

                  <form method="POST" action="<?= View::url('/admin/services/' . $s['id'] . '/toggle-addon') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($s['is_addon']) ? 'btn-outline-info' : 'btn-outline-warning' ?> py-1 px-2 font-weight-bold" title="Toggle between Main Service and Add-on Only">
                      <?= !empty($s['is_addon']) ? '<i class="fa fa-cubes mr-1"></i>Make Main' : '<i class="fa fa-puzzle-piece mr-1"></i>Make Addon' ?>
                    </button>
                  </form>

                  <form method="POST" action="<?= View::url('/admin/services/' . $s['id'] . '/toggle-status') ?>" class="d-inline mr-1">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($s['is_active']) ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-1 px-2 font-weight-bold" title="Toggle active status">
                      <?= !empty($s['is_active']) ? 'Disable' : 'Enable' ?>
                    </button>
                  </form>

                  <a href="<?= View::url('/admin/services/' . $s['id'] . '/variants') ?>" class="btn btn-sm btn-outline-dark py-1 px-2 font-weight-bold mr-1" title="Manage Sizes, Seats, Capacity & Variations" style="border-color: #f25b29; color: #f25b29;">
                    <i class="fa fa-sliders mr-1"></i>Options
                  </a>

                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-service-btn"
                    data-id="<?= (int)$s['id'] ?>"
                    data-name="<?= View::e($s['name']) ?>"
                    data-category-id="<?= (int)$s['category_id'] ?>"
                    data-price="<?= (float)$s['starting_price'] ?>"
                    data-duration="<?= (int)($s['duration_minutes'] ?? 60) ?>"
                    data-description="<?= View::e($s['description'] ?? '') ?>"
                    data-image="<?= View::e($s['image'] ?? '') ?>"
                    data-is-addon="<?= !empty($s['is_addon']) ? '1' : '0' ?>"
                    data-is-coming-soon="<?= !empty($s['is_coming_soon']) ? '1' : '0' ?>"
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

          <div class="form-group mb-2 p-2 rounded bg-light border">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="is_coming_soon" value="1" class="custom-control-input" id="new_is_coming_soon">
              <label class="custom-control-label font-weight-bold small text-dark" for="new_is_coming_soon">
                <i class="fa fa-clock-o text-warning mr-1"></i> Mark as "Coming Soon" (Customers can view details, but Booking &amp; Add-to-Cart are paused)
              </label>
            </div>
            <small class="form-text text-muted pl-4">When enabled, the service displays "Coming Soon" badges and buttons across all customer pages.</small>
          </div>

          <div class="form-group mb-2 p-2 rounded bg-light border">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="is_addon" value="1" class="custom-control-input" id="new_is_addon">
              <label class="custom-control-label font-weight-bold small text-dark" for="new_is_addon">
                Treat as Add-on Only (Hide from main services catalog &amp; packages list)
              </label>
            </div>
            <small class="form-text text-muted pl-4">Add-ons only appear in the add-on modal during checkout or when explicitly paired with main services.</small>
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

          <div class="form-group mb-2 p-2 rounded bg-light border">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="is_coming_soon" value="1" class="custom-control-input" id="edit_svc_is_coming_soon">
              <label class="custom-control-label font-weight-bold small text-dark" for="edit_svc_is_coming_soon">
                <i class="fa fa-clock-o text-warning mr-1"></i> Mark as "Coming Soon" (Customers can view details, but Booking &amp; Add-to-Cart are paused)
              </label>
            </div>
            <small class="form-text text-muted pl-4">When enabled, the service displays "Coming Soon" badges and buttons across all customer pages.</small>
          </div>

          <div class="form-group mb-2 p-2 rounded bg-light border">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="is_addon" value="1" class="custom-control-input" id="edit_svc_is_addon">
              <label class="custom-control-label font-weight-bold small text-dark" for="edit_svc_is_addon">
                Treat as Add-on Only (Hide from main services catalog &amp; packages list)
              </label>
            </div>
            <small class="form-text text-muted pl-4">Add-ons only appear in the add-on modal during checkout or when explicitly paired with main services.</small>
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
  var baseUrl = '<?= rtrim(\App\Core\View::url("/"), "/") ?>';
  document.querySelectorAll('.edit-service-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      var name = this.getAttribute('data-name');
      var categoryId = this.getAttribute('data-category-id');
      var price = this.getAttribute('data-price');
      var duration = this.getAttribute('data-duration');
      var description = this.getAttribute('data-description');
      var image = this.getAttribute('data-image');
      var isAddon = this.getAttribute('data-is-addon');
      var isComingSoon = this.getAttribute('data-is-coming-soon');

      var form = document.getElementById('editServiceForm');
      var cleanBase = (baseUrl || '').replace(/\/+$/, '');
      form.action = cleanBase + '/admin/services/' + id + '/update';

      document.getElementById('edit_svc_name').value = name;
      document.getElementById('edit_svc_category_id').value = categoryId;
      document.getElementById('edit_svc_price').value = price;
      document.getElementById('edit_svc_duration').value = duration;
      document.getElementById('edit_svc_description').value = description;
      document.getElementById('edit_svc_image_path').value = image;
      document.getElementById('edit_svc_is_addon').checked = (isAddon === '1');
      document.getElementById('edit_svc_is_coming_soon').checked = (isComingSoon === '1');

      $('#editServiceModal').modal('show');
    });
  });
});
</script>
