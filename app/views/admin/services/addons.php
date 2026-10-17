<?php
use App\Core\View;

$rules = $rules ?? [];
$services = $services ?? [];
$categories = $categories ?? [];
$filterServiceId = $filterServiceId ?? null;
$totalRules = $totalRules ?? count($rules);
$activeRules = $activeRules ?? 0;
$servicesWithAddons = $servicesWithAddons ?? 0;
$autoSensible = $autoSensible ?? true;
?>

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
      <p class="text-muted small mb-0">Control which add-ons appear when customers book services (Kitchen, Bathroom, Room, AC, etc.). Manage custom companion pricing, badges, and recommendations.</p>
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
      <span class="font-weight-bold small text-muted"><i class="fa fa-filter mr-1"></i> Filter Target Service:</span>
      <form method="GET" action="<?= View::url('/admin/services/addons') ?>" class="form-inline m-0">
        <select name="service_id" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()" style="min-width: 260px;">
          <option value="">-- All Target Services (<?= count($rules) ?> rules) --</option>
          <?php foreach ($services as $srv): ?>
            <option value="<?= $srv['id'] ?>" <?= ($filterServiceId === (int)$srv['id']) ? 'selected' : '' ?>>
              <?= View::e($srv['name']) ?> (₹<?= number_format((float)$srv['starting_price'], 0) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($filterServiceId): ?>
          <a href="<?= View::url('/admin/services/addons') ?>" class="btn btn-sm btn-light ml-2 text-muted" title="Clear Filter">
            <i class="fa fa-times mr-1"></i> Clear
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
            <th class="py-3 text-dark" style="width: 220px;">Trigger (Target Service)</th>
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
                Click <strong>"New Add-on Pairing"</strong> above to link complementary add-ons to your services.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rules as $r): ?>
              <tr>
                <!-- Target Service -->
                <td class="align-middle">
                  <?php if (!empty($r['source_service_name'])): ?>
                    <strong class="text-dark d-block" style="font-size: 13.5px;"><?= View::e($r['source_service_name']) ?></strong>
                    <span class="badge badge-light border text-muted small px-2 py-0">Single Service</span>
                  <?php elseif (!empty($r['source_category_name'])): ?>
                    <strong class="text-primary d-block" style="font-size: 13.5px;">All in <?= View::e($r['source_category_name']) ?></strong>
                    <span class="badge badge-info small px-2 py-0">Category Rule</span>
                  <?php else: ?>
                    <strong class="text-secondary d-block" style="font-size: 13.5px;">Global Fallback</strong>
                    <span class="badge badge-secondary small px-2 py-0">All Services</span>
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
                      <div class="font-weight-bold text-dark" style="font-size: 13.5px;"><?= View::e($r['addon_name']) ?></div>
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
                  <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold edit-addon-rule-btn"
                    data-id="<?= (int)$r['id'] ?>"
                    data-source="<?= View::e($r['source_service_name'] ?: 'Global / Category') ?>"
                    data-addon="<?= View::e($r['addon_name']) ?>"
                    data-badge="<?= View::e($r['custom_badge'] ?? '') ?>"
                    data-price="<?= ($r['custom_price'] !== null && (float)$r['custom_price'] > 0) ? (float)$r['custom_price'] : '' ?>"
                    data-note="<?= View::e($r['custom_note'] ?? '') ?>"
                    data-order="<?= (int)$r['sort_order'] ?>"
                    data-active="<?= (int)$r['is_active'] ?>"
                    title="Edit Rule">
                    <i class="fa fa-pencil"></i>
                  </button>

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
</div>

<!-- ============================================== -->
<!-- MODAL: ADD NEW ADD-ON PAIRING                  -->
<!-- ============================================== -->
<div class="modal fade" id="addPairingModal" tabindex="-1" role="dialog" aria-labelledby="addPairingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
      <form method="POST" action="<?= View::url('/admin/services/addons') ?>">
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
          <!-- Target Service -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Target Service (Trigger) <span class="text-danger">*</span></label>
            <select name="source_service_id" class="form-control" required id="modalSourceService">
              <option value="">-- Select Service Being Booked --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>" <?= ($filterServiceId === (int)$srv['id']) ? 'selected' : '' ?>>
                  <?= View::e($srv['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">When a customer books or adds this service, the add-on below will be recommended.</small>
          </div>

          <!-- Add-on Service Item -->
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark mb-1">Add-on Item to Recommend <span class="text-danger">*</span></label>
            <select name="addon_service_id" class="form-control" required id="modalAddonService">
              <option value="">-- Select Companion Add-on Service --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?= $srv['id'] ?>">
                  <?= View::e($srv['name']) ?> (Base: ₹<?= number_format((float)$srv['starting_price'], 0) ?>)
                </option>
              <?php endforeach; ?>
            </select>
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

          <!-- Companion Price & Note Row -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="font-weight-bold small text-dark mb-1">Companion Discount Price (₹)</label>
              <input type="number" step="0.01" name="custom_price" class="form-control" placeholder="Leave blank for base price">
              <small class="text-muted">Special companion rate for this service</small>
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
      <form method="POST" id="editPairingForm" action="">
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
          <div class="p-2 mb-3 rounded bg-light border">
            <div class="small text-muted">Target Service: <strong class="text-dark" id="editSourceLabel"></strong></div>
            <div class="small text-muted">Add-on Item: <strong class="text-brand" id="editAddonLabel" style="color: #f25b29;"></strong></div>
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
  var baseUrl = '<?= View::url("") ?>';

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
  var editModal = document.getElementById("editPairingModal");
  var editForm = document.getElementById("editPairingForm");

  document.querySelectorAll(".edit-addon-rule-btn").forEach(function(btn) {
    btn.addEventListener("click", function() {
      var id = this.getAttribute("data-id");
      var source = this.getAttribute("data-source");
      var addon = this.getAttribute("data-addon");
      var badge = this.getAttribute("data-badge");
      var price = this.getAttribute("data-price");
      var note = this.getAttribute("data-note");
      var order = this.getAttribute("data-order");
      var active = this.getAttribute("data-active");

      document.getElementById("editSourceLabel").textContent = source;
      document.getElementById("editAddonLabel").textContent = addon;
      document.getElementById("editBadgeInput").value = badge;
      document.getElementById("editPriceInput").value = price;
      document.getElementById("editNoteInput").value = note;
      document.getElementById("editOrderInput").value = order;
      document.getElementById("editActiveSelect").value = active;

      editForm.action = baseUrl + '/admin/services/addons/' + id + '/update';

      if (window.$) {
        $('#editPairingModal').modal('show');
      }
    });
  });
});
</script>
