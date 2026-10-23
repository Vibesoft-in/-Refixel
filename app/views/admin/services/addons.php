<?php
use App\Core\View;

$rules = $rules ?? [];
$services = $services ?? [];
$categories = $categories ?? [];
$filterServiceId = $filterServiceId ?? null;
$filterCategoryId = $filterCategoryId ?? null;
$totalRules = $totalRules ?? count($rules);
$activeRules = $activeRules ?? 0;
$servicesWithAddons = $servicesWithAddons ?? 0;
$autoSensible = $autoSensible ?? true;
$addonCategoriesMap = $addonCategoriesMap ?? [];
$addonServices = $addonServices ?? [];
?>

<style>
.category-checkbox-card {
  border: 1.5px solid #e2e8f0;
  transition: all 0.15s ease;
  background: #ffffff;
}
.category-checkbox-card:hover {
  border-color: #f25b29;
  background: #fffdfb;
}
.category-checkbox-card.is-selected {
  border-color: #f25b29;
  background: #fff6f0;
}
.category-checkbox-grid::-webkit-scrollbar {
  width: 4px;
}
.category-checkbox-grid::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>

<div class="admin-addons-page">
  <!-- Top Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4" style="gap: 15px;">
    <div>
      <div class="d-flex align-items-center mb-1" style="gap: 10px;">
        <h4 class="font-weight-bold mb-0 text-dark">Add-ons Control &amp; Smart Pairing</h4>
        <?php if ($autoSensible): ?>
          <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11.5px; border-radius: 6px;">
            <i class="fa fa-bolt mr-1"></i> Auto-Sensible: ACTIVE
          </span>
        <?php else: ?>
          <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11.5px; border-radius: 6px;">
            <i class="fa fa-pause mr-1"></i> Auto-Sensible: PAUSED
          </span>
        <?php endif; ?>
      </div>
      <p class="text-muted small mb-0">Control which add-ons appear when customers book services (Kitchen, Bathroom, Room, AC, etc.). Put add-ons into any category or service, manage custom companion pricing, and badges.</p>
    </div>

    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
      <!-- Toggle Auto-Engine Form -->
      <form method="POST" action="<?= View::url('/admin/services/addons/toggle-auto-engine') ?>" class="d-inline">
        <?= View::csrfField() ?>
        <button type="submit" class="btn btn-sm <?= $autoSensible ? 'btn-outline-secondary' : 'btn-outline-success' ?> font-weight-bold" title="Toggle automatic sensible add-on matching for unconfigured services">
          <i class="fa <?= $autoSensible ? 'fa-toggle-on text-success' : 'fa-toggle-off' ?> mr-1"></i>
          <?= $autoSensible ? 'Disable Auto-Sensible' : 'Enable Auto-Sensible' ?>
        </button>
      </form>

      <a href="<?= View::url('/admin/services') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fa fa-wrench mr-1"></i> Catalog
      </a>

      <button type="button" class="btn btn-sm btn-outline-brand font-weight-bold px-3" data-toggle="modal" data-target="#createAddonServiceModal" style="border: 1px solid #f25b29; color: #f25b29;">
        <i class="fa fa-plus-circle mr-1"></i> Create Addon Service
      </button>

      <button type="button" class="btn btn-sm btn-brand font-weight-bold px-3 py-2 text-white" data-toggle="modal" data-target="#addPairingModal" style="background: #f25b29; border: none; border-radius: 6px; box-shadow: 0 2px 8px rgba(242, 91, 41, 0.25);">
        <i class="fa fa-link mr-1"></i> New Add-on Pairing
      </button>
    </div>
  </div>

  <!-- KPI Matrix Cards -->
  <div class="row mb-4">
    <div class="col-6 col-md-3 mb-3 mb-md-0">
      <div class="stat-card p-3 bg-white border rounded shadow-sm">
        <div class="text-muted small text-uppercase font-weight-bold">Total Pairing Rules</div>
        <div class="h3 font-weight-bold text-dark mb-0 mt-1"><?= $totalRules ?></div>
        <div class="small text-muted mt-1"><i class="fa fa-puzzle-piece text-primary mr-1"></i> Admin pairings</div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3 mb-md-0">
      <div class="stat-card p-3 bg-white border rounded shadow-sm">
        <div class="text-muted small text-uppercase font-weight-bold">Active Add-ons</div>
        <div class="h3 font-weight-bold text-success mb-0 mt-1"><?= $activeRules ?></div>
        <div class="small text-muted mt-1"><i class="fa fa-check-circle text-success mr-1"></i> Showing to customers</div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3 mb-md-0">
      <div class="stat-card p-3 bg-white border rounded shadow-sm">
        <div class="text-muted small text-uppercase font-weight-bold">Configured Services</div>
        <div class="h3 font-weight-bold text-dark mb-0 mt-1"><?= $servicesWithAddons ?></div>
        <div class="small text-muted mt-1"><i class="fa fa-wrench text-warning mr-1"></i> Target services paired</div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3 mb-md-0">
      <div class="stat-card p-3 bg-white border rounded shadow-sm">
        <div class="text-muted small text-uppercase font-weight-bold">Sensible Auto-Engine</div>
        <div class="h3 font-weight-bold <?= $autoSensible ? 'text-primary' : 'text-secondary' ?> mb-0 mt-1" style="font-size: 19px;">
          <?= $autoSensible ? 'ACTIVE' : 'MANUAL' ?>
        </div>
        <div class="small text-muted mt-1"><i class="fa fa-magic text-info mr-1"></i> Kitchen, Bath, Room smart logic</div>
      </div>
    </div>
  </div>

  <!-- Filter & Action Bar -->
  <div class="stat-card p-3 mb-4 bg-white border rounded shadow-sm d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
    <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
      <span class="font-weight-bold small text-muted"><i class="fa fa-filter mr-1"></i> Filter Add-ons:</span>
      <form method="GET" action="<?= View::url('/admin/services/addons') ?>" class="form-inline m-0 flex-wrap" style="gap: 8px;">
        <!-- Category Filter -->
        <select name="category_id" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="min-width: 200px;">
          <option value="">-- All Categories --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($filterCategoryId === (int)$cat['id']) ? 'selected' : '' ?>>
              📁 <?= View::e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Service Filter -->
        <select name="service_id" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="min-width: 240px;">
          <option value="">-- All Target Services --</option>
          <?php foreach ($services as $srv): ?>
            <option value="<?= $srv['id'] ?>" <?= ($filterServiceId === (int)$srv['id']) ? 'selected' : '' ?>>
              <?= View::e($srv['name']) ?> (₹<?= number_format((float)$srv['starting_price'], 0) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <?php if ($filterServiceId || $filterCategoryId): ?>
          <a href="<?= View::url('/admin/services/addons') ?>" class="btn btn-sm btn-light text-muted" title="Clear Filters">
            <i class="fa fa-times mr-1"></i> Clear Filters
          </a>
        <?php endif; ?>
      </form>
    </div>

    <div class="small text-muted">
      Showing <strong><?= count($rules) ?></strong> configured pairing rule(s)
    </div>
  </div>

  <!-- Table of Add-on Rules -->
  <div class="stat-card p-0 overflow-hidden shadow-sm border bg-white" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="bg-light">
          <tr>
            <th class="py-3 text-dark" style="width: 240px;">Category &amp; Target Service</th>
            <th class="py-3 text-dark">Add-on Service Item</th>
            <th class="py-3 text-dark">Marketing Badge</th>
            <th class="py-3 text-dark">Price (Companion Rate)</th>
            <th class="py-3 text-dark">Companion Note</th>
            <th class="py-3 text-dark text-center" style="width: 70px;">Order</th>
            <th class="py-3 text-dark text-center" style="width: 100px;">Status</th>
            <th class="py-3 text-right text-dark" style="width: 130px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rules)): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="fa fa-puzzle-piece fa-2x mb-2 text-muted d-block"></i>
                No add-on pairings found matching current filter.<br>
                Click <strong>"New Add-on Pairing"</strong> above to link complementary add-ons to your categories &amp; services.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rules as $r): ?>
              <tr>
                <!-- Target Service & Category -->
                <td class="align-middle">
                  <div class="mb-1">
                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-0" style="font-size: 11px;">
                      📁 <?= View::e($r['source_category_name'] ?: 'General Category') ?>
                    </span>
                  </div>
                  <?php if (!empty($r['source_service_name'])): ?>
                    <strong class="text-dark d-block" style="font-size: 13.5px;"><?= View::e($r['source_service_name']) ?></strong>
                    <span class="text-muted small">Specific Service</span>
                  <?php else: ?>
                    <strong class="text-primary d-block" style="font-size: 13.5px;">All Services in <?= View::e($r['source_category_name'] ?? 'Category') ?></strong>
                    <span class="badge badge-info small px-2 py-0">Category-Wide</span>
                  <?php endif; ?>
                </td>

                <!-- Add-on Service Item -->
                <td class="align-middle">
                  <div class="d-flex align-items-center" style="gap: 10px;">
                    <div class="border rounded overflow-hidden bg-light flex-shrink-0" style="width: 44px; height: 44px;">
                      <?php 
                        $img = trim((string)($r['addon_image'] ?? ''));
                        $imgUrl = '';
                        if (!empty($img)) {
                            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, 'uploads/') || str_starts_with($img, 'assets/') || str_starts_with($img, 'img/')) {
                                $imgUrl = View::asset($img);
                            } else {
                                $imgUrl = View::asset('img/' . $img);
                            }
                        }
                      ?>
                      <?php if (!empty($imgUrl)): ?>
                        <img src="<?= $imgUrl ?>" alt="<?= View::e($r['addon_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= View::asset('img/refixel-cleaning.jpg') ?>';">
                      <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                          <i class="fa fa-cubes"></i>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="d-flex align-items-center" style="gap: 7px;">
                        <span class="font-weight-bold text-dark" style="font-size: 13.5px;"><?= View::e($r['addon_name']) ?></span>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 quick-rename-btn"
                                data-addon-id="<?= (int)$r['addon_service_id'] ?>"
                                data-addon-name="<?= View::e($r['addon_name']) ?>"
                                title="Click to rename this add-on"
                                style="font-size: 10.5px; border-radius: 4px; line-height: 1.2;">
                          <i class="fa fa-pencil text-primary"></i> Edit Name
                        </button>
                      </div>
                      <div class="small text-muted">
                        Base: ₹<?= number_format((float)$r['addon_base_price'], 2) ?>
                        <?php if (!empty($r['addon_category_name'])): ?>
                          &bull; <span class="text-secondary"><?= View::e($r['addon_category_name']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Marketing Badge -->
                <td class="align-middle">
                  <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px; border-radius: 4px;">
                    <?= View::e($r['custom_badge'] ?: '🔥 Popular Add-on') ?>
                  </span>
                </td>

                <!-- Companion Price -->
                <td class="align-middle">
                  <?php if ($r['custom_price'] !== null && (float)$r['custom_price'] > 0): ?>
                    <strong class="text-success font-weight-bold" style="font-size: 14px;">₹<?= number_format((float)$r['custom_price'], 2) ?></strong>
                    <?php if ((float)$r['custom_price'] < (float)$r['addon_base_price']): ?>
                      <div class="small text-muted"><del>₹<?= number_format((float)$r['addon_base_price'], 2) ?></del></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-dark font-weight-bold" style="font-size: 13.5px;">₹<?= number_format((float)$r['addon_base_price'], 2) ?></span>
                    <div class="small text-muted">Catalog Price</div>
                  <?php endif; ?>
                </td>

                <!-- Companion Note -->
                <td class="align-middle small text-muted">
                  <?= View::e($r['custom_note'] ?: 'Special Companion Rate') ?>
                </td>

                <!-- Order -->
                <td class="align-middle text-center small font-weight-bold text-muted">
                  <?= (int)$r['sort_order'] ?>
                </td>

                <!-- Status -->
                <td class="align-middle text-center">
                  <form method="POST" action="<?= View::url('/admin/services/addons/' . $r['id'] . '/toggle-status') ?>" class="d-inline">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm <?= !empty($r['is_active']) ? 'btn-success' : 'btn-secondary' ?> py-0 px-2 font-weight-bold" style="font-size: 11px; border-radius: 12px;" title="Click to toggle active status">
                      <?= !empty($r['is_active']) ? 'Active' : 'Disabled' ?>
                    </button>
                  </form>
                </td>

                <!-- Actions -->
                <td class="align-middle text-right">
                  <?php 
                    $curCatId = (int)($r['effective_category_id'] ?? $r['source_category_id'] ?? 0);
                    $allCats = $addonCategoriesMap[(int)$r['addon_service_id']] ?? ($curCatId > 0 ? [$curCatId] : []);
                    if ($curCatId > 0 && !in_array($curCatId, $allCats, true)) {
                      $allCats[] = $curCatId;
                    }
                    $allCatsStr = implode(',', array_unique(array_filter($allCats)));
                    $effImg = !empty($r['custom_image']) ? $r['custom_image'] : ($r['addon_image'] ?? '');
                    $effImgUrl = !empty($effImg) ? \App\Models\Service::resolveImage($r['addon_slug'] ?? '', $effImg) : View::asset('img/refixel-cleaning.jpg');
                  ?>
                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-addon-rule-btn"
                    data-id="<?= (int)$r['id'] ?>"
                    data-source-category-id="<?= $curCatId ?>"
                    data-all-category-ids="<?= $allCatsStr ?>"
                    data-source-service-id="<?= (int)($r['source_service_id'] ?? 0) ?>"
                    data-addon-service-id="<?= (int)$r['addon_service_id'] ?>"
                    data-image="<?= htmlspecialchars($effImgUrl) ?>"
                    data-source-name="<?= View::e($r['source_service_name'] ?: 'All Services in ' . ($r['source_category_name'] ?? 'Category')) ?>"
                    data-addon-name="<?= View::e($r['addon_name']) ?>"
                    data-badge="<?= View::e($r['custom_badge'] ?? '') ?>"
                    data-price="<?= ($r['custom_price'] !== null && (float)$r['custom_price'] > 0) ? (float)$r['custom_price'] : '' ?>"
                    data-note="<?= View::e($r['custom_note'] ?? '') ?>"
                    data-order="<?= (int)$r['sort_order'] ?>"
                    data-active="<?= (int)$r['is_active'] ?>"
                    title="Edit Rule">
                    <i class="fa fa-pencil"></i>
                  </button>

                  <form method="POST" action="<?= View::url('/admin/services/' . $r['addon_service_id'] . '/toggle-addon') ?>" class="d-inline ml-1" onsubmit="return confirm('Convert \'<?= View::e(addslashes($r['addon_name'])) ?>\' back to Main Services Catalog?');">
                    <?= View::csrfField() ?>
                    <input type="hidden" name="redirect_to" value="<?= View::url('/admin/services/addons') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 font-weight-bold" title="Move this service back to Main Services Catalog (Make Main Service)">
                      <i class="fa fa-cubes mr-1"></i> Make Main
                    </button>
                  </form>

                  <form method="POST" action="<?= View::url('/admin/services/addons/' . $r['id'] . '/delete') ?>" class="d-inline ml-1" onsubmit="return confirm('Remove add-on pairing between \'<?= View::e(addslashes($r['source_service_name'] ?? 'Target')) ?>\' and \'<?= View::e(addslashes($r['addon_name'])) ?>\'?');">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 font-weight-bold" title="Delete Rule">
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

  <!-- Standalone Add-on Services Directory & One-Click Return to Main Catalog -->
  <div class="mt-5 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 10px;">
      <div>
        <div class="d-flex align-items-center mb-1" style="gap: 8px;">
          <h5 class="font-weight-bold text-dark mb-0">
            <i class="fa fa-cubes text-primary mr-1"></i> Standalone Add-on Services Directory (<?= count($addonServices) ?>)
          </h5>
          <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
            Add-on Only (is_addon = 1)
          </span>
        </div>
        <p class="text-muted small mb-0">
          All services currently assigned as <strong>Add-on Only</strong>. If a service was accidentally moved here from the main catalog, click the green <strong>"Move to Main Catalog"</strong> button to restore it instantly!
        </p>
      </div>

      <div class="d-flex align-items-center" style="gap: 8px;">
        <a href="<?= View::url('/admin/services') ?>" class="btn btn-sm btn-outline-brand font-weight-bold" style="border: 1px solid #f25b29; color: #f25b29;">
          <i class="fa fa-wrench mr-1"></i> Open Main Services Catalog
        </a>
      </div>
    </div>

    <div class="stat-card p-0 overflow-hidden shadow-sm border bg-white" style="border-radius: 12px;">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="bg-light">
            <tr>
              <th class="py-3 text-dark">Add-on Service Name</th>
              <th class="py-3 text-dark">Category</th>
              <th class="py-3 text-dark">Base Price</th>
              <th class="py-3 text-dark text-center">Status</th>
              <th class="py-3 text-dark text-center">Paired In</th>
              <th class="py-3 text-right text-dark" style="width: 250px;">Restore to Main Catalog</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($addonServices)): ?>
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  No add-on only services found in database.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($addonServices as $asv): ?>
                <tr>
                  <td class="align-middle">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                      <div class="border rounded overflow-hidden bg-light flex-shrink-0" style="width: 44px; height: 44px;">
                        <?php 
                          $asvImg = $asv['image'] ?? 'refixel-cleaning.jpg';
                          $asvImgUrl = View::asset(str_starts_with($asvImg, 'http') || str_starts_with($asvImg, 'assets/') || str_starts_with($asvImg, 'img/') ? $asvImg : 'img/' . $asvImg);
                        ?>
                        <img src="<?= $asvImgUrl ?>" alt="<?= View::e($asv['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= View::asset('img/refixel-cleaning.jpg') ?>';">
                      </div>
                      <div>
                        <strong class="text-dark d-block" style="font-size: 14px;"><?= View::e($asv['name']) ?></strong>
                        <span class="text-monospace text-secondary small">/<?= View::e($asv['slug']) ?></span>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle">
                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                      📁 <?= View::e($asv['category_name'] ?? 'General') ?>
                    </span>
                  </td>
                  <td class="align-middle">
                    <strong class="text-dark font-weight-bold" style="font-size: 14px;">₹<?= number_format((float)$asv['starting_price'], 2) ?></strong>
                    <?php if (!empty($asv['duration_minutes'])): ?>
                      <div class="small text-muted"><?= (int)$asv['duration_minutes'] ?> mins</div>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle text-center">
                    <?php if (!empty($asv['is_active'])): ?>
                      <span class="badge badge-success px-2 py-1">Active</span>
                    <?php else: ?>
                      <span class="badge badge-secondary px-2 py-1">Disabled</span>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-info px-2 py-1 font-weight-bold" style="border-radius: 6px;">
                      <?= (int)($asv['pairing_count'] ?? 0) ?> pairings
                    </span>
                  </td>
                  <td class="align-middle text-right">
                    <form method="POST" action="<?= View::url('/admin/services/' . $asv['id'] . '/toggle-addon') ?>" class="d-inline" onsubmit="return confirm('Move \'<?= View::e(addslashes($asv['name'])) ?>\' back to the Main Services Catalog?');">
                      <?= View::csrfField() ?>
                      <input type="hidden" name="redirect_to" value="<?= View::url('/admin/services/addons') ?>">
                      <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3 py-1 text-white shadow-sm" style="background: #10b981; border: none; border-radius: 6px;" title="Convert this service back to Main Services Catalog">
                        <i class="fa fa-arrow-left mr-1"></i> Make Main Service
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
</div>

<!-- ============================================== -->
<!-- MODAL: ADD NEW ADD-ON PAIRING                  -->
<!-- ============================================== -->
<div class="modal fade" id="addPairingModal" tabindex="-1" role="dialog" aria-labelledby="addPairingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
      <form method="POST" action="<?= View::url('/admin/services/addons') ?>" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header bg-light border-bottom">
          <h5 class="modal-title font-weight-bold text-dark" id="addPairingModalLabel">
            <i class="fa fa-link text-brand mr-1" style="color: #f25b29;"></i> Create Smart Add-on Pairing
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <!-- 1. Select Categories (Checkboxes) -->
          <div class="form-group mb-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="font-weight-bold small text-dark mb-0">
                <i class="fa fa-folder text-brand mr-1" style="color: #f25b29;"></i> Select Categories <span class="text-danger">*</span>
              </label>
              <div class="category-quick-btns">
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 select-all-cats-btn" style="font-size: 11px;">Select All</button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 clear-all-cats-btn" style="font-size: 11px;">Clear</button>
              </div>
            </div>
            <small class="text-muted d-block mb-2">Check one or more categories where this add-on should be offered.</small>

            <div class="category-checkbox-grid p-2 rounded border" style="background: #f8fafc; max-height: 185px; overflow-y: auto;">
              <div class="row no-gutters">
                <?php foreach ($categories as $cat): ?>
                  <?php $isPreSelected = ($filterCategoryId === (int)$cat['id']); ?>
                  <div class="col-sm-6 p-1">
                    <label class="category-checkbox-card d-flex align-items-center p-2 rounded border bg-white mb-0 <?= $isPreSelected ? 'is-selected' : '' ?>" for="modal_cat_chk_<?= (int)$cat['id'] ?>" style="cursor: pointer; transition: all 0.15s ease;">
                      <input type="checkbox" 
                             name="source_category_ids[]" 
                             value="<?= (int)$cat['id'] ?>" 
                             id="modal_cat_chk_<?= (int)$cat['id'] ?>" 
                             class="modal-cat-chk mr-2" 
                             style="width: 16px; height: 16px; accent-color: #f25b29; cursor: pointer;"
                             <?= $isPreSelected ? 'checked' : '' ?>>
                      <span class="small font-weight-semibold text-dark text-truncate" title="<?= View::e($cat['name']) ?>">
                        <?= View::e($cat['name']) ?>
                      </span>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="invalid-feedback d-none" id="modalCatError">Please select at least one category.</div>
          </div>

          <!-- 2. Target Service (Optional: specific or entire category) -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Target Service (Trigger)</label>
            <select name="source_service_id" id="modalSourceService" class="form-control">
              <option value="">-- Apply to ALL Services in Selected Categories (Category-Wide) --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>" data-category="<?= $srv['category_id'] ?>" <?= ($filterServiceId === (int)$srv['id']) ? 'selected' : '' ?>>
                  <?= View::e($srv['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Choose a specific service, or leave as "ALL Services" to show across all selected categories.</small>
          </div>

          <!-- 3. Add-on Service Item -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">
              <i class="fa fa-puzzle-piece mr-1 text-primary"></i> Add-on Item to Recommend <span class="text-danger">*</span>
            </label>
            <select name="addon_service_id" class="form-control" required id="modalAddonService">
              <option value="">-- Select Companion Add-on Service --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>" data-image="<?= htmlspecialchars(\App\Models\Service::resolveImage($srv['slug'] ?? '', $srv['image'] ?? null)) ?>">
                  <?= View::e($srv['name']) ?> (Base: ₹<?= number_format((float)$srv['starting_price'], 0) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 4. Add-on Image Upload -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">
              <i class="fa fa-image text-brand mr-1" style="color: #f25b29;"></i> Add-on Image
            </label>
            <div class="d-flex align-items-center" style="gap: 12px;">
              <div class="addon-preview-box rounded border bg-light d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; overflow: hidden; flex-shrink: 0; background: #f8fafc;">
                <img id="modalAddonImagePreview" src="<?= View::url('/assets/images/service-default.jpg') ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= View::asset('img/refixel-cleaning.jpg') ?>';">
              </div>
              <div class="flex-grow-1">
                <input type="file" name="image" id="modalAddonImageInput" class="form-control-file small" accept="image/png, image/jpeg, image/webp" style="font-size: 12px;">
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Upload custom thumbnail (JPG, PNG, WEBP - max 5MB). Leave empty to use default service image.</small>
              </div>
            </div>
          </div>

          <!-- Marketing Badge -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Marketing Badge</label>
            <input type="text" name="custom_badge" class="form-control" id="modalBadgeInput" value="🔥 Popular Add-on" placeholder="e.g. 🔥 Kitchen Must-Have, 🧼 Scale Remover">
            <div class="d-flex flex-wrap mt-2" style="gap: 5px;">
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 badge-suggest-btn" data-text="🔥 Kitchen Must-Have">🔥 Kitchen Must-Have</button>
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 badge-suggest-btn" data-text="🧼 Scale Remover">🧼 Scale Remover</button>
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 badge-suggest-btn" data-text="⭐ Frequently Added">⭐ Frequently Added</button>
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 badge-suggest-btn" data-text="⚡ 60-Min Quick Clean">⚡ 60-Min Quick Clean</button>
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 badge-suggest-btn" data-text="🛡️ Protection Plan">🛡️ Protection Plan</button>
            </div>
          </div>

          <!-- Companion Price & Order -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Companion Discount Price (₹)</label>
              <input type="number" step="0.01" name="custom_price" class="form-control" placeholder="Leave blank for base price">
              <small class="text-muted">Special companion rate for this add-on</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="0">
              <small class="text-muted">Lower numbers show first (0, 1, 2)</small>
            </div>
          </div>

          <!-- Companion Note -->
          <div class="form-group mb-0">
            <label class="font-weight-bold small text-dark mb-1">Companion Short Note / Value Prop</label>
            <input type="text" name="custom_note" class="form-control" value="Special Companion Rate" placeholder="e.g. Removes heavy grease & oil">
          </div>
        </div>

        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold px-4 text-white" style="background: #f25b29;">
            Save Pairing Rule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- MODAL: EDIT ADD-ON PAIRING                     -->
<!-- ============================================== -->
<div class="modal fade" id="editPairingModal" tabindex="-1" role="dialog" aria-labelledby="editPairingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
      <form method="POST" id="editPairingForm" action="" enctype="multipart/form-data">
        <?= View::csrfField() ?>
        <div class="modal-header bg-light border-bottom">
          <h5 class="modal-title font-weight-bold text-dark" id="editPairingModalLabel">
            <i class="fa fa-pencil text-primary mr-1"></i> Edit Add-on Pairing
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <!-- 1. Select Categories (Checkboxes) -->
          <div class="form-group mb-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="font-weight-bold small text-dark mb-0">
                <i class="fa fa-folder text-brand mr-1" style="color: #f25b29;"></i> Select Categories <span class="text-danger">*</span>
              </label>
              <div class="category-quick-btns">
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 edit-select-all-cats-btn" style="font-size: 11px;">Select All</button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 edit-clear-all-cats-btn" style="font-size: 11px;">Clear</button>
              </div>
            </div>
            <small class="text-muted d-block mb-2">Check all categories where this add-on should be offered.</small>

            <div class="category-checkbox-grid p-2 rounded border" style="background: #f8fafc; max-height: 185px; overflow-y: auto;">
              <div class="row no-gutters">
                <?php foreach ($categories as $cat): ?>
                  <div class="col-sm-6 p-1">
                    <label class="category-checkbox-card d-flex align-items-center p-2 rounded border bg-white mb-0" for="edit_cat_chk_<?= (int)$cat['id'] ?>" style="cursor: pointer; transition: all 0.15s ease;">
                      <input type="checkbox" 
                             name="source_category_ids[]" 
                             value="<?= (int)$cat['id'] ?>" 
                             id="edit_cat_chk_<?= (int)$cat['id'] ?>" 
                             class="edit-modal-cat-chk mr-2" 
                             style="width: 16px; height: 16px; accent-color: #f25b29; cursor: pointer;">
                      <span class="small font-weight-semibold text-dark text-truncate" title="<?= View::e($cat['name']) ?>">
                        <?= View::e($cat['name']) ?>
                      </span>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="invalid-feedback d-none" id="editCatError">Please select at least one category.</div>
          </div>

          <!-- 2. Target Service (Trigger) -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Target Service (Trigger)</label>
            <select name="source_service_id" id="editSourceServiceId" class="form-control">
              <option value="">-- Apply to ALL Services in Selected Categories (Category-Wide) --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>" data-category="<?= $srv['category_id'] ?>">
                  <?= View::e($srv['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Select a specific service, or leave as "ALL Services" to trigger across all selected categories.</small>
          </div>

          <!-- 3. Add-on Item to Recommend -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">
              <i class="fa fa-puzzle-piece mr-1 text-primary"></i> Add-on Item to Recommend <span class="text-danger">*</span>
            </label>
            <select name="addon_service_id" id="editAddonServiceId" class="form-control" required>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>" 
                        data-name="<?= View::e($srv['name']) ?>" 
                        data-image="<?= htmlspecialchars(\App\Models\Service::resolveImage($srv['slug'] ?? '', $srv['image'] ?? null)) ?>">
                  <?= View::e($srv['name']) ?> (Base: ₹<?= number_format((float)$srv['starting_price'], 0) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Edit Add-on Name -->
          <div class="form-group mb-3 p-2.5 rounded border" style="background: #f8fafc; border-color: #e2e8f0;">
            <label class="font-weight-bold small text-dark mb-1 d-flex align-items-center justify-content-between">
              <span><i class="fa fa-pencil text-primary mr-1"></i> Edit Add-on Name <span class="text-danger">*</span></span>
              <span class="badge badge-light border text-muted" style="font-size: 10px;">Editable</span>
            </label>
            <input type="text" name="addon_name" id="editAddonNameInput" class="form-control" placeholder="Enter add-on name..." required>
            <small class="text-muted d-block mt-1" style="font-size: 11px;">
              <i class="fa fa-info-circle mr-1"></i> Renaming this add-on updates its title in catalog lists, pairing cards, and cart recommendations.
            </small>
          </div>

          <!-- 4. Add-on Image Upload -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">
              <i class="fa fa-image text-brand mr-1" style="color: #f25b29;"></i> Add-on Image
            </label>
            <div class="d-flex align-items-center" style="gap: 12px;">
              <div class="addon-preview-box rounded border bg-light d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; overflow: hidden; flex-shrink: 0; background: #f8fafc;">
                <img id="editAddonImagePreview" src="<?= View::url('/assets/images/service-default.jpg') ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='<?= View::asset('img/refixel-cleaning.jpg') ?>';">
              </div>
              <div class="flex-grow-1">
                <input type="file" name="image" id="editAddonImageInput" class="form-control-file small" accept="image/png, image/jpeg, image/webp" style="font-size: 12px;">
                <small class="text-muted d-block mt-1" style="font-size: 11px;">Upload a new image to replace current thumbnail (or leave empty to keep current).</small>
              </div>
            </div>
          </div>

          <!-- Marketing Badge -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Marketing Badge</label>
            <input type="text" name="custom_badge" id="editBadgeInput" class="form-control" required>
          </div>

          <!-- Companion Price & Order -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Companion Discount Price (₹)</label>
              <input type="number" step="0.01" name="custom_price" id="editPriceInput" class="form-control" placeholder="Leave blank for default">
              <small class="text-muted">Special discounted companion rate</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Sort Order</label>
              <input type="number" name="sort_order" id="editOrderInput" class="form-control" value="0">
            </div>
          </div>

          <!-- Companion Note -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Companion Short Note</label>
            <input type="text" name="custom_note" id="editNoteInput" class="form-control">
          </div>

          <!-- Status -->
          <div class="form-group mb-0">
            <label class="font-weight-bold small text-dark mb-1">Status</label>
            <select name="is_active" id="editActiveSelect" class="form-control">
              <option value="1">Active (Show to customers)</option>
              <option value="0">Disabled (Hidden from customers)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4">
            Update Rule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- MODAL: QUICK RENAME ADD-ON SERVICE             -->
<!-- ============================================== -->
<div class="modal fade" id="quickRenameModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
      <form method="POST" action="<?= View::url('/admin/services/addons/rename') ?>">
        <?= View::csrfField() ?>
        <input type="hidden" name="addon_service_id" id="quickRenameAddonId" value="">
        <div class="modal-header bg-light py-3 px-4 border-bottom">
          <h6 class="modal-title font-weight-bold text-dark mb-0">
            <i class="fa fa-pencil text-primary mr-1"></i> Edit Add-on Name
          </h6>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-0">
            <label class="font-weight-bold small text-dark mb-1">Add-on Display Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="quickRenameInput" class="form-control" required placeholder="Enter new add-on name...">
            <small class="text-muted d-block mt-2" style="font-size: 11.5px; line-height: 1.4;">
              <i class="fa fa-info-circle mr-1 text-primary"></i> This will immediately update the add-on name across all customer-facing service pages, smart recommendations, and checkout.
            </small>
          </div>
        </div>
        <div class="modal-footer py-2 px-4 bg-light border-top">
          <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
            <i class="fa fa-save mr-1"></i> Save Name
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- MODAL: QUICK CREATE NEW ADDON SERVICE          -->
<!-- ============================================== -->
<div class="modal fade" id="createAddonServiceModal" tabindex="-1" role="dialog" aria-labelledby="createAddonServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
      <form method="POST" action="<?= View::url('/admin/services/addons/create-addon-service') ?>">
        <?= View::csrfField() ?>
        <div class="modal-header bg-light border-bottom">
          <h5 class="modal-title font-weight-bold text-dark" id="createAddonServiceModalLabel">
            <i class="fa fa-plus-circle text-brand mr-1" style="color: #f25b29;"></i> Create Dedicated Add-on Service
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Add-on Service Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Chimney Deep Degreasing, Oven Sanitization">
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Category <span class="text-danger">*</span></label>
              <select name="category_id" class="form-control" required>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= View::e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Starting Price (₹) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" name="starting_price" class="form-control" required placeholder="249.00">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Duration (Minutes)</label>
              <input type="number" name="duration_minutes" class="form-control" value="30">
            </div>
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Representative Image</label>
              <select name="image" class="form-control">
                <option value="refixel-kitchen-cleaning.jpg">Kitchen Cleaning</option>
                <option value="refixel-bathroom-cleaning.jpg">Bathroom Cleaning</option>
                <option value="refixel-cleaning.jpg">Home Cleaning / Room</option>
                <option value="refixel-sofa-cleaning.jpg">Sofa / Upholstery</option>
                <option value="refixel-ac-service.jpg">AC Service</option>
                <option value="refixel-plumber.jpg">Plumber</option>
                <option value="refixel-electrician.jpg">Electrician</option>
                <option value="refixel-pest-control.jpg">Pest Control</option>
              </select>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Description / Value Proposition</label>
            <textarea name="description" rows="2" class="form-control" placeholder="Short description of what is included in this add-on..."></textarea>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold small text-dark mb-1">Immediately Pair with Target Service (Optional)</label>
            <select name="pair_with_service_id" class="form-control">
              <option value="">-- Don't pair yet (Keep standalone) --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>" <?= ($filterServiceId === (int)$srv['id']) ? 'selected' : '' ?>>
                  <?= View::e($srv['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold px-4 text-white" style="background: #f25b29;">
            Create Service &amp; Add to Catalog
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  var baseUrl = '<?= rtrim(\App\Core\View::url("/"), "/") ?>';

  // Helper to filter services by checked categories
  function filterServicesByCheckedCategories(chkClass, serviceSelectId, preserveSelectedId) {
    var srvSelect = document.getElementById(serviceSelectId);
    if (!srvSelect) return;

    var checkedCats = [];
    document.querySelectorAll("." + chkClass + ":checked").forEach(function(chk) {
      checkedCats.push(String(chk.value));
    });

    var options = srvSelect.querySelectorAll("option");
    options.forEach(function(opt) {
      if (!opt.value) {
        opt.style.display = "";
        return;
      }
      var optCat = opt.getAttribute("data-category");
      if (checkedCats.length === 0 || checkedCats.indexOf(optCat) !== -1) {
        opt.style.display = "";
      } else {
        opt.style.display = "none";
      }
    });

    if (preserveSelectedId !== undefined) {
      srvSelect.value = String(preserveSelectedId || '');
    }
  }

  // Handle visual card toggle on checkbox change
  document.addEventListener("change", function(e) {
    if (e.target && (e.target.classList.contains("modal-cat-chk") || e.target.classList.contains("edit-modal-cat-chk"))) {
      var card = e.target.closest(".category-checkbox-card");
      if (card) {
        if (e.target.checked) card.classList.add("is-selected");
        else card.classList.remove("is-selected");
      }
      if (e.target.classList.contains("modal-cat-chk")) {
        filterServicesByCheckedCategories("modal-cat-chk", "modalSourceService");
      } else {
        filterServicesByCheckedCategories("edit-modal-cat-chk", "editSourceServiceId");
      }
    }
  });

  // Select all / clear in Add modal
  document.querySelectorAll(".select-all-cats-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      document.querySelectorAll(".modal-cat-chk").forEach(function(chk) {
        chk.checked = true;
        chk.closest(".category-checkbox-card")?.classList.add("is-selected");
      });
      filterServicesByCheckedCategories("modal-cat-chk", "modalSourceService");
    });
  });

  document.querySelectorAll(".clear-all-cats-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      document.querySelectorAll(".modal-cat-chk").forEach(function(chk) {
        chk.checked = false;
        chk.closest(".category-checkbox-card")?.classList.remove("is-selected");
      });
      filterServicesByCheckedCategories("modal-cat-chk", "modalSourceService");
    });
  });

  // Select all / clear in Edit modal
  document.querySelectorAll(".edit-select-all-cats-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      document.querySelectorAll(".edit-modal-cat-chk").forEach(function(chk) {
        chk.checked = true;
        chk.closest(".category-checkbox-card")?.classList.add("is-selected");
      });
      filterServicesByCheckedCategories("edit-modal-cat-chk", "editSourceServiceId");
    });
  });

  document.querySelectorAll(".edit-clear-all-cats-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      document.querySelectorAll(".edit-modal-cat-chk").forEach(function(chk) {
        chk.checked = false;
        chk.closest(".category-checkbox-card")?.classList.remove("is-selected");
      });
      filterServicesByCheckedCategories("edit-modal-cat-chk", "editSourceServiceId");
    });
  });

  // Initialize filtering in Create modal
  filterServicesByCheckedCategories("modal-cat-chk", "modalSourceService");

  // Validate at least one category selected before submit
  var addForm = document.querySelector("#addonModal form");
  if (addForm) {
    addForm.addEventListener("submit", function(e) {
      var checked = addForm.querySelectorAll(".modal-cat-chk:checked").length;
      var srvId = addForm.querySelector("#modalSourceService") ? addForm.querySelector("#modalSourceService").value : "";
      if (checked === 0 && !srvId) {
        e.preventDefault();
        alert("Please select at least one Category for this add-on.");
      }
    });
  }

  // Badge suggestion chips
  document.querySelectorAll(".badge-suggest-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      var text = this.getAttribute("data-text");
      var input = document.getElementById("modalBadgeInput");
      if (input && text) {
        input.value = text;
      }
    });
  });

  // Edit Addon Rule Modal Populator
  var editForm = document.getElementById("editPairingForm");
  if (editForm) {
    editForm.addEventListener("submit", function(e) {
      var checked = editForm.querySelectorAll(".edit-modal-cat-chk:checked").length;
      var srvId = editForm.querySelector("#editSourceServiceId") ? editForm.querySelector("#editSourceServiceId").value : "";
      if (checked === 0 && !srvId) {
        e.preventDefault();
        alert("Please select at least one Category for this add-on.");
      }
    });
  }

  document.querySelectorAll(".edit-addon-rule-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      var id = this.getAttribute("data-id");
      var allCatIdsStr = this.getAttribute("data-all-category-ids") || this.getAttribute("data-source-category-id") || '';
      var allCatIds = allCatIdsStr.split(",").map(function(s) { return s.trim(); }).filter(Boolean);
      var sourceSrvId = this.getAttribute("data-source-service-id") || '';
      var addonSrvId = this.getAttribute("data-addon-service-id") || '';
      var badge = this.getAttribute("data-badge") || '';
      var price = this.getAttribute("data-price") || '';
      var note = this.getAttribute("data-note") || '';
      var order = this.getAttribute("data-order") || '0';
      var active = this.getAttribute("data-active") || '1';

      // Check all matching categories
      document.querySelectorAll(".edit-modal-cat-chk").forEach(function(chk) {
        var shouldCheck = allCatIds.indexOf(String(chk.value)) !== -1;
        chk.checked = shouldCheck;
        var card = chk.closest(".category-checkbox-card");
        if (card) {
          if (shouldCheck) card.classList.add("is-selected");
          else card.classList.remove("is-selected");
        }
      });

      filterServicesByCheckedCategories("edit-modal-cat-chk", "editSourceServiceId", sourceSrvId);

      var addonName = this.getAttribute("data-addon-name") || '';
      var nameEl = document.getElementById("editAddonNameInput");
      if (nameEl) {
        nameEl.value = addonName;
      }

      var addonEl = document.getElementById("editAddonServiceId");
      if (addonEl) {
        addonEl.value = addonSrvId;
      }

      var badgeEl = document.getElementById("editBadgeInput");
      if (badgeEl) badgeEl.value = badge;

      var priceEl = document.getElementById("editPriceInput");
      if (priceEl) priceEl.value = price;

      var noteEl = document.getElementById("editNoteInput");
      if (noteEl) noteEl.value = note;

      var orderEl = document.getElementById("editOrderInput");
      if (orderEl) orderEl.value = order;

      var activeEl = document.getElementById("editActiveSelect");
      if (activeEl) activeEl.value = active;

      var img = this.getAttribute("data-image") || '';
      var cleanBase = (baseUrl || '').replace(/\/+$/, '');
      var imgPreview = document.getElementById("editAddonImagePreview");
      if (imgPreview) {
        imgPreview.src = img || (cleanBase + '/assets/images/service-default.jpg');
      }
      var imgInput = document.getElementById("editAddonImageInput");
      if (imgInput) {
        imgInput.value = '';
      }

      if (editForm) {
        editForm.action = cleanBase + '/admin/services/addons/' + id + '/update';
      }

      if (window.$) {
        $('#editPairingModal').modal('show');
      }
    });
  });

  // On addon service dropdown change in edit modal, update the name input to default option name
  var editAddonSelect = document.getElementById("editAddonServiceId");
  if (editAddonSelect) {
    editAddonSelect.addEventListener("change", function() {
      var opt = editAddonSelect.options[editAddonSelect.selectedIndex];
      var nameEl = document.getElementById("editAddonNameInput");
      if (opt && nameEl) {
        nameEl.value = opt.getAttribute("data-name") || opt.text.split(' (Base:')[0];
      }
    });
  }

  // Quick Rename button click handler
  document.querySelectorAll('.quick-rename-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var aId = this.getAttribute('data-addon-id');
      var aName = this.getAttribute('data-addon-name');
      var idInput = document.getElementById('quickRenameAddonId');
      var nameInput = document.getElementById('quickRenameInput');
      if (idInput) idInput.value = aId;
      if (nameInput) nameInput.value = aName;
      if (window.$) {
        $('#quickRenameModal').modal('show');
      }
    });
  });

  // Image upload live preview handlers
  function setupImagePreview(inputId, previewId) {
    var input = document.getElementById(inputId);
    var preview = document.getElementById(previewId);
    if (!input || !preview) return;

    input.addEventListener("change", function() {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  }

  setupImagePreview("modalAddonImageInput", "modalAddonImagePreview");
  setupImagePreview("editAddonImageInput", "editAddonImagePreview");

  // On addon service dropdown change, update preview to service default if no file uploaded
  var modalAddonSelect = document.getElementById("modalAddonService");
  if (modalAddonSelect) {
    modalAddonSelect.addEventListener("change", function() {
      var opt = modalAddonSelect.options[modalAddonSelect.selectedIndex];
      var optImg = opt ? opt.getAttribute("data-image") : '';
      var preview = document.getElementById("modalAddonImagePreview");
      var fileInput = document.getElementById("modalAddonImageInput");
      if (preview && (!fileInput || !fileInput.files || fileInput.files.length === 0)) {
        var cleanBase = (baseUrl || '').replace(/\/+$/, '');
        preview.src = optImg || (cleanBase + '/assets/images/service-default.jpg');
      }
    });
  }
});
</script>
