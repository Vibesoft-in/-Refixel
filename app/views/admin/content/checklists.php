<?php
use App\Core\View;
$items = $items ?? [];
$groupedByService = $groupedByService ?? [];
$services = $services ?? [];
$currentServiceId = (int)($currentServiceId ?? 0);
?>

<div class="admin-checklists">
  <!-- Header Section -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 15px;">
    <div>
      <h2 class="font-weight-bold text-dark mb-1">Service Checklists & Scope</h2>
      <p class="text-muted small mb-0">Manage customer-facing service inclusions and exclusions displayed on service pages. Drag and drop any item to change its position / numbering.</p>
    </div>
    <div>
      <button class="btn btn-brand font-weight-bold shadow-sm" data-toggle="modal" data-target="#addChecklistModal">
        <i class="fa fa-plus mr-1"></i>Add Checklist Item
      </button>
    </div>
  </div>

  <!-- Filter by Service Card -->
  <div class="card p-3 border-0 shadow-sm mb-4" style="border-radius: 10px;">
    <form method="GET" action="<?= View::url('/admin/content/checklists') ?>" class="form-inline d-flex align-items-center flex-wrap" style="gap: 10px;">
      <label class="font-weight-bold small text-muted mr-1">
        <i class="fa fa-filter mr-1 text-primary"></i>Filter by Service:
      </label>
      <select name="service_id" class="form-control form-control-sm font-weight-medium" onchange="this.form.submit()" style="min-width: 260px; height: 36px; border-radius: 6px;">
        <option value="0" <?= $currentServiceId === 0 ? 'selected' : '' ?>>All Services (View All Checklists)</option>
        <?php foreach ($services as $svc): ?>
          <option value="<?= (int)$svc['id'] ?>" <?= $currentServiceId === (int)$svc['id'] ? 'selected' : '' ?>>
            <?= View::e($svc['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <?php if ($currentServiceId > 0): ?>
        <a href="<?= View::url('/admin/content/checklists') ?>" class="btn btn-sm btn-light border text-muted px-3" style="height: 36px; line-height: 22px;">
          <i class="fa fa-times mr-1"></i>Show All Services
        </a>
      <?php endif; ?>

      <span class="text-muted small ml-auto font-italic d-none d-md-inline">
        <i class="fa fa-arrows mr-1 text-primary"></i> Drag <i class="fa fa-bars text-muted"></i> icon or use <i class="fa fa-chevron-up text-secondary"></i><i class="fa fa-chevron-down text-secondary"></i> to reorder items.
      </span>
    </form>
  </div>

  <!-- Toast Notification Container for Quick Alerts -->
  <div id="checklistToast" class="alert alert-success position-fixed shadow-lg" 
       style="bottom: 25px; right: 25px; z-index: 9999; display: none; min-width: 280px; border-radius: 8px;">
    <i class="fa fa-check-circle mr-2"></i><span id="checklistToastText">Changes saved successfully.</span>
  </div>

  <!-- Services Checklist Cards -->
  <?php if (empty($groupedByService)): ?>
    <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius: 12px;">
      <i class="fa fa-check-square-o fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
      <h5 class="font-weight-bold text-dark">No Checklist Items Found</h5>
      <p class="small mb-3">Add inclusions and exclusions to guide customers and technicians on scope of work.</p>
      <div>
        <button class="btn btn-brand font-weight-bold" data-toggle="modal" data-target="#addChecklistModal">
          <i class="fa fa-plus mr-1"></i>Add First Checklist Item
        </button>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($groupedByService as $svcGroup): ?>
      <?php 
        $sid = (int)$svcGroup['service_id'];
        $sname = $svcGroup['service_name'];
        $incItems = $svcGroup['included'] ?? [];
        $excItems = $svcGroup['excluded'] ?? [];
      ?>
      <div class="service-checklist-card card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;" data-service-id="<?= $sid ?>">
        <!-- Service Title Bar -->
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
          <div>
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fa fa-wrench text-primary mr-2"></i><?= View::e($sname) ?>
            </h5>
            <div class="text-muted small mt-1">Multi-Point Scope & Hygiene Checklist</div>
          </div>
          <div class="d-flex align-items-center" style="gap: 6px;">
            <span class="badge badge-success px-2 py-1" style="font-size: 11px;">
              <span class="header-inc-count"><?= count($incItems) ?></span> Included
            </span>
            <span class="badge badge-danger px-2 py-1" style="font-size: 11px;">
              <span class="header-exc-count"><?= count($excItems) ?></span> Excluded
            </span>
          </div>
        </div>

        <!-- 2 Divs: What's Included (Left) & What's Excluded (Right) -->
        <div class="card-body p-4 bg-light">
          <div class="row">
            
            <!-- DIV 1: What's Included (Green Theme) -->
            <div class="col-lg-6 mb-3 mb-lg-0">
              <div class="p-3 rounded h-100 shadow-sm" style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: #bbf7d0 !important;">
                  <h6 class="font-weight-bold text-success mb-0" style="font-size: 15px;">
                    <i class="fa fa-check-circle mr-1"></i> What's Included
                  </h6>
                  <span class="badge font-weight-bold" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 11px;">
                    <span class="count-badge"><?= count($incItems) ?></span> items
                  </span>
                </div>

                <!-- Items List -->
                <ul class="list-unstyled mb-0 checklist-items-list" data-service-id="<?= $sid ?>" data-type="1">
                  <?php if (empty($incItems)): ?>
                    <li class="empty-placeholder text-muted small font-italic py-3 text-center">No included items added yet.</li>
                  <?php else: ?>
                    <?php foreach ($incItems as $idx => $item): ?>
                      <li class="checklist-item-row d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-white border shadow-sm"
                          draggable="true"
                          data-id="<?= (int)$item['id'] ?>"
                          data-label="<?= View::e($item['label']) ?>"
                          data-included="1"
                          data-service-id="<?= $sid ?>"
                          style="border-color: #dcfce7 !important; cursor: default;">
                        
                        <div class="item-content-wrapper d-flex align-items-center flex-grow-1 mr-2" style="min-width: 0;">
                          <!-- Drag Handle -->
                          <span class="drag-handle mr-2 text-muted" title="Drag and drop to reorder" style="cursor: grab; user-select: none; font-size: 13px;">
                            <i class="fa fa-bars"></i>
                          </span>

                          <!-- Position Number Badge -->
                          <span class="badge badge-light border text-muted mr-2 item-number font-weight-bold" style="font-size: 11px; min-width: 22px; padding: 2px 5px; text-align: center;">
                            <?= $idx + 1 ?>
                          </span>

                          <!-- Included Checkmark -->
                          <span class="mr-2 text-success" style="font-size: 14px;">
                            <i class="fa fa-check"></i>
                          </span>

                          <!-- Pencil Button (Opens in front of text when Edit is clicked) -->
                          <button type="button" class="btn btn-sm btn-pencil-front mr-2 d-none shadow-sm" 
                                  title="Click to edit text inline" 
                                  style="background: #f25b29; color: #fff; border: none; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                            <i class="fa fa-pencil"></i>
                          </button>

                          <!-- Text Label -->
                          <span class="item-text text-dark font-weight-medium text-break" style="font-size: 13.5px; line-height: 1.4;">
                            <?= View::e($item['label']) ?>
                          </span>

                          <!-- Inline Input (Transforms at its own position) -->
                          <div class="item-inline-edit d-none flex-grow-1 align-items-center" style="gap: 6px;">
                            <input type="text" class="form-control form-control-sm item-edit-input" value="<?= View::e($item['label']) ?>" style="font-size: 13.5px;">
                            <button type="button" class="btn btn-sm btn-success btn-save-inline px-2 py-1 font-weight-bold" title="Save changes">
                              <i class="fa fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border btn-cancel-inline px-2 py-1" title="Cancel">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                        </div>

                        <!-- Actions Wrapper: Reorder Arrows, Edit, Trashbin -->
                        <div class="item-actions-wrapper d-flex align-items-center" style="gap: 4px; flex-shrink: 0;">
                          <div class="btn-group btn-group-sm mr-1">
                            <button type="button" class="btn btn-sm btn-light border text-secondary btn-move-up px-2 py-1" title="Move Up">
                              <i class="fa fa-chevron-up" style="font-size: 10px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border text-secondary btn-move-down px-2 py-1" title="Move Down">
                              <i class="fa fa-chevron-down" style="font-size: 10px;"></i>
                            </button>
                          </div>

                          <button type="button" class="btn btn-sm btn-light border text-primary btn-toggle-edit px-2 py-1" title="Edit this line" style="font-size: 12px; font-weight: 600;">
                            <i class="fa fa-pencil mr-1"></i>Edit
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item px-2 py-1" title="Delete this line" style="font-size: 12px;">
                            <i class="fa fa-trash"></i>
                          </button>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>

                <!-- Inline Quick Add for Included Items -->
                <div class="mt-3 pt-2 border-top" style="border-color: #bbf7d0 !important;">
                  <form class="quick-add-item-form d-flex align-items-center" data-service-id="<?= $sid ?>" data-is-included="1" style="gap: 6px;">
                    <input type="text" class="form-control form-control-sm quick-add-input" placeholder="+ Add what's included line..." required style="border-color: #86efac; font-size: 13px; height: 34px;">
                    <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3 d-flex align-items-center" style="height: 34px; white-space: nowrap;">
                      <i class="fa fa-plus mr-1"></i>Add
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- DIV 2: What's Excluded (Red Theme) -->
            <div class="col-lg-6">
              <div class="p-3 rounded h-100 shadow-sm" style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: #fecaca !important;">
                  <h6 class="font-weight-bold text-danger mb-0" style="font-size: 15px;">
                    <i class="fa fa-times-circle mr-1"></i> What's Excluded
                  </h6>
                  <span class="badge font-weight-bold" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: 11px;">
                    <span class="count-badge"><?= count($excItems) ?></span> items
                  </span>
                </div>

                <!-- Items List -->
                <ul class="list-unstyled mb-0 checklist-items-list" data-service-id="<?= $sid ?>" data-type="0">
                  <?php if (empty($excItems)): ?>
                    <li class="empty-placeholder text-muted small font-italic py-3 text-center">No excluded items added yet.</li>
                  <?php else: ?>
                    <?php foreach ($excItems as $idx => $item): ?>
                      <li class="checklist-item-row d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-white border shadow-sm"
                          draggable="true"
                          data-id="<?= (int)$item['id'] ?>"
                          data-label="<?= View::e($item['label']) ?>"
                          data-included="0"
                          data-service-id="<?= $sid ?>"
                          style="border-color: #fee2e2 !important; cursor: default;">
                        
                        <div class="item-content-wrapper d-flex align-items-center flex-grow-1 mr-2" style="min-width: 0;">
                          <!-- Drag Handle -->
                          <span class="drag-handle mr-2 text-muted" title="Drag and drop to reorder" style="cursor: grab; user-select: none; font-size: 13px;">
                            <i class="fa fa-bars"></i>
                          </span>

                          <!-- Position Number Badge -->
                          <span class="badge badge-light border text-muted mr-2 item-number font-weight-bold" style="font-size: 11px; min-width: 22px; padding: 2px 5px; text-align: center;">
                            <?= $idx + 1 ?>
                          </span>

                          <!-- Excluded Crossmark -->
                          <span class="mr-2 text-danger" style="font-size: 14px;">
                            <i class="fa fa-times"></i>
                          </span>

                          <!-- Pencil Button (Opens in front of text when Edit is clicked) -->
                          <button type="button" class="btn btn-sm btn-pencil-front mr-2 d-none shadow-sm" 
                                  title="Click to edit text inline" 
                                  style="background: #f25b29; color: #fff; border: none; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                            <i class="fa fa-pencil"></i>
                          </button>

                          <!-- Text Label -->
                          <span class="item-text text-dark font-weight-medium text-break" style="font-size: 13.5px; line-height: 1.4;">
                            <?= View::e($item['label']) ?>
                          </span>

                          <!-- Inline Input (Transforms at its own position) -->
                          <div class="item-inline-edit d-none flex-grow-1 align-items-center" style="gap: 6px;">
                            <input type="text" class="form-control form-control-sm item-edit-input" value="<?= View::e($item['label']) ?>" style="font-size: 13.5px;">
                            <button type="button" class="btn btn-sm btn-success btn-save-inline px-2 py-1 font-weight-bold" title="Save changes">
                              <i class="fa fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border btn-cancel-inline px-2 py-1" title="Cancel">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                        </div>

                        <!-- Actions Wrapper: Reorder Arrows, Edit, Trashbin -->
                        <div class="item-actions-wrapper d-flex align-items-center" style="gap: 4px; flex-shrink: 0;">
                          <div class="btn-group btn-group-sm mr-1">
                            <button type="button" class="btn btn-sm btn-light border text-secondary btn-move-up px-2 py-1" title="Move Up">
                              <i class="fa fa-chevron-up" style="font-size: 10px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border text-secondary btn-move-down px-2 py-1" title="Move Down">
                              <i class="fa fa-chevron-down" style="font-size: 10px;"></i>
                            </button>
                          </div>

                          <button type="button" class="btn btn-sm btn-light border text-primary btn-toggle-edit px-2 py-1" title="Edit this line" style="font-size: 12px; font-weight: 600;">
                            <i class="fa fa-pencil mr-1"></i>Edit
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item px-2 py-1" title="Delete this line" style="font-size: 12px;">
                            <i class="fa fa-trash"></i>
                          </button>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>

                <!-- Inline Quick Add for Excluded Items -->
                <div class="mt-3 pt-2 border-top" style="border-color: #fecaca !important;">
                  <form class="quick-add-item-form d-flex align-items-center" data-service-id="<?= $sid ?>" data-is-included="0" style="gap: 6px;">
                    <input type="text" class="form-control form-control-sm quick-add-input" placeholder="+ Add what's excluded line..." required style="border-color: #fca5a5; font-size: 13px; height: 34px;">
                    <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3 d-flex align-items-center" style="height: 34px; white-space: nowrap;">
                      <i class="fa fa-plus mr-1"></i>Add
                    </button>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Modal: Add Checklist Item (Standard Full Form) -->
<div class="modal fade" id="addChecklistModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="<?= View::url('/admin/content/checklists') ?>">
        <?= View::csrfField() ?>
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0a1c33 0%, #122844 100%);">
          <h5 class="modal-title font-weight-bold">
            <i class="fa fa-list-ul mr-2" style="color: #f25b29;"></i>Add Service Checklist Item
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted">Service <span class="text-danger">*</span></label>
            <select name="service_id" class="form-control font-weight-medium" required>
              <option value="">-- Choose Service --</option>
              <?php foreach ($services as $svc): ?>
                <option value="<?= (int)$svc['id'] ?>" <?= $currentServiceId === (int)$svc['id'] ? 'selected' : '' ?>>
                  <?= View::e($svc['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted">Checklist Section <span class="text-danger">*</span></label>
            <div class="p-2 border rounded bg-light">
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="type_inc" name="is_included" value="1" class="custom-control-input" checked>
                <label class="custom-control-label text-success font-weight-bold" for="type_inc">
                  <i class="fa fa-check-circle mr-1"></i>What's Included
                </label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="type_exc" name="is_included" value="0" class="custom-control-input">
                <label class="custom-control-label text-danger font-weight-bold" for="type_exc">
                  <i class="fa fa-times-circle mr-1"></i>What's Excluded
                </label>
              </div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted">Checklist Line Text <span class="text-danger">*</span></label>
            <textarea name="label" class="form-control" rows="2" placeholder="e.g. Mechanized high-pressure deep cleaning and scrubbing" required></textarea>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold small text-muted">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="0" placeholder="Display order">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand font-weight-bold px-4">Save Checklist Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CSRF Token for Dynamic AJAX Calls -->
<input type="hidden" id="adminCsrfToken" value="<?= View::csrfToken() ?>">

<!-- Inline Styles for Drag & Drop Feedback -->
<style>
.checklist-item-row.dragging {
  opacity: 0.45 !important;
  border: 2px dashed #f25b29 !important;
  background-color: #fffaf7 !important;
  transform: scale(0.99);
}
.checklist-items-list.drag-over {
  background-color: rgba(242, 91, 41, 0.05);
  border-radius: 8px;
}
.drag-handle:active {
  cursor: grabbing !important;
}
.item-number {
  transition: all 0.2s ease;
}
</style>

<!-- Inline Checklist Interaction Script (Drag & Drop + Reordering + Pencil Edit) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var csrfToken = document.getElementById('adminCsrfToken') ? document.getElementById('adminCsrfToken').value : '';

  // Show floating toast alert
  function showToast(message, isError) {
    var toast = document.getElementById('checklistToast');
    var toastText = document.getElementById('checklistToastText');
    if (!toast || !toastText) return;
    toastText.innerText = message;
    if (isError) {
      toast.className = 'alert alert-danger position-fixed shadow-lg';
    } else {
      toast.className = 'alert alert-success position-fixed shadow-lg';
    }
    toast.style.display = 'block';
    setTimeout(function () {
      toast.style.display = 'none';
    }, 2800);
  }

  // Update item numbering badges (1, 2, 3...) within a list
  function updateListNumbering(list) {
    if (!list) return;
    var rows = list.querySelectorAll('.checklist-item-row');
    rows.forEach(function (row, idx) {
      var numBadge = row.querySelector('.item-number');
      if (numBadge) {
        numBadge.innerText = (idx + 1);
      }
    });
  }

  // Save new order via AJAX
  function saveListOrder(list) {
    if (!list) return;
    var rows = list.querySelectorAll('.checklist-item-row');
    var ids = [];
    rows.forEach(function (r) {
      var id = r.getAttribute('data-id');
      if (id) ids.push(id);
    });

    if (ids.length === 0) return;

    var isIncluded = list.getAttribute('data-type');
    var formData = new FormData();
    formData.append('_csrf', csrfToken);
    formData.append('is_ajax', '1');
    formData.append('is_included', isIncluded);
    ids.forEach(function (id) {
      formData.append('order[]', id);
    });

    fetch('<?= View::url('/admin/content/checklists/reorder') ?>', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data && data.success) {
        showToast('Checklist numbering & position saved!', false);
      } else {
        showToast((data && data.message) ? data.message : 'Error saving order.', true);
      }
    })
    .catch(function (err) {
      console.error('Reorder error:', err);
      showToast('Network error while saving order.', true);
    });
  }

  // ========================================================
  // DRAG & DROP REORDERING (HTML5 Native)
  // ========================================================
  var draggedRow = null;

  document.addEventListener('dragstart', function (e) {
    var row = e.target.closest('.checklist-item-row');
    if (!row) return;

    draggedRow = row;
    row.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', row.getAttribute('data-id'));
  });

  document.addEventListener('dragend', function (e) {
    if (draggedRow) {
      draggedRow.classList.remove('dragging');
      var list = draggedRow.closest('.checklist-items-list');
      if (list) {
        list.classList.remove('drag-over');
        updateListNumbering(list);
        saveListOrder(list);
      }
      draggedRow = null;
    }
  });

  document.addEventListener('dragover', function (e) {
    var list = e.target.closest('.checklist-items-list');
    if (!list || !draggedRow) return;
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    list.classList.add('drag-over');

    var afterElement = getDragAfterElement(list, e.clientY);
    if (afterElement == null) {
      list.appendChild(draggedRow);
    } else {
      list.insertBefore(draggedRow, afterElement);
    }
    updateListNumbering(list);
  });

  document.addEventListener('dragleave', function (e) {
    var list = e.target.closest('.checklist-items-list');
    if (list && !list.contains(e.relatedTarget)) {
      list.classList.remove('drag-over');
    }
  });

  function getDragAfterElement(container, y) {
    var draggableElements = Array.from(container.querySelectorAll('.checklist-item-row:not(.dragging)'));

    return draggableElements.reduce(function (closest, child) {
      var box = child.getBoundingClientRect();
      var offset = y - box.top - box.height / 2;
      if (offset < 0 && offset > closest.offset) {
        return { offset: offset, element: child };
      } else {
        return closest;
      }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
  }

  // ========================================================
  // UP & DOWN BUTTONS (1-Click Reordering)
  // ========================================================
  document.addEventListener('click', function (e) {
    var upBtn = e.target.closest('.btn-move-up');
    var downBtn = e.target.closest('.btn-move-down');
    if (!upBtn && !downBtn) return;
    e.preventDefault();

    var row = (upBtn || downBtn).closest('.checklist-item-row');
    if (!row) return;

    var list = row.closest('.checklist-items-list');
    if (!list) return;

    if (upBtn) {
      var prev = row.previousElementSibling;
      if (prev && prev.classList.contains('checklist-item-row')) {
        list.insertBefore(row, prev);
        updateListNumbering(list);
        saveListOrder(list);
      } else {
        showToast('Item is already at the top position (#1).', false);
      }
    } else if (downBtn) {
      var next = row.nextElementSibling;
      if (next && next.classList.contains('checklist-item-row')) {
        list.insertBefore(next, row);
        updateListNumbering(list);
        saveListOrder(list);
      } else {
        showToast('Item is already at the bottom position.', false);
      }
    }
  });

  // ========================================================
  // EDIT & PENCIL INLINE EDITING
  // ========================================================
  // 1. Click on "Edit" button: Shows pencil button in front of text
  document.addEventListener('click', function (e) {
    var editBtn = e.target.closest('.btn-toggle-edit');
    if (!editBtn) return;
    e.preventDefault();

    var row = editBtn.closest('.checklist-item-row');
    if (!row) return;

    var pencilFront = row.querySelector('.btn-pencil-front');
    var textSpan = row.querySelector('.item-text');
    var inlineEdit = row.querySelector('.item-inline-edit');

    if (inlineEdit && !inlineEdit.classList.contains('d-none')) {
      inlineEdit.classList.add('d-none');
      inlineEdit.classList.remove('d-flex');
      textSpan.classList.remove('d-none');
      if (pencilFront) pencilFront.classList.remove('d-none');
      return;
    }

    if (pencilFront) {
      if (pencilFront.classList.contains('d-none')) {
        pencilFront.classList.remove('d-none');
        pencilFront.focus();
        showToast('Pencil enabled! Click the pencil in front of the text to edit inline.', false);
      } else {
        openInlineInput(row);
      }
    }
  });

  // 2. Click on the pencil button in front of text: transforms line into text input at its own position!
  document.addEventListener('click', function (e) {
    var pencilFront = e.target.closest('.btn-pencil-front');
    if (!pencilFront) return;
    e.preventDefault();

    var row = pencilFront.closest('.checklist-item-row');
    if (!row) return;

    openInlineInput(row);
  });

  function openInlineInput(row) {
    var textSpan = row.querySelector('.item-text');
    var inlineEdit = row.querySelector('.item-inline-edit');
    var input = row.querySelector('.item-edit-input');
    var pencilFront = row.querySelector('.btn-pencil-front');

    if (!inlineEdit || !textSpan || !input) return;

    textSpan.classList.add('d-none');
    if (pencilFront) pencilFront.classList.add('d-none');
    inlineEdit.classList.remove('d-none');
    inlineEdit.classList.add('d-flex');

    input.focus();
    input.select();
  }

  // 3. Save inline edit (Click Save checkmark button or press Enter)
  document.addEventListener('click', function (e) {
    var saveBtn = e.target.closest('.btn-save-inline');
    if (!saveBtn) return;
    e.preventDefault();

    var row = saveBtn.closest('.checklist-item-row');
    if (!row) return;
    submitInlineUpdate(row);
  });

  document.addEventListener('keydown', function (e) {
    var input = e.target.closest('.item-edit-input');
    if (!input) return;

    var row = input.closest('.checklist-item-row');
    if (!row) return;

    if (e.key === 'Enter') {
      e.preventDefault();
      submitInlineUpdate(row);
    } else if (e.key === 'Escape') {
      e.preventDefault();
      cancelInlineInput(row);
    }
  });

  function submitInlineUpdate(row) {
    var itemId = row.getAttribute('data-id');
    var input = row.querySelector('.item-edit-input');
    var textSpan = row.querySelector('.item-text');
    var inlineEdit = row.querySelector('.item-inline-edit');
    var pencilFront = row.querySelector('.btn-pencil-front');
    var saveBtn = row.querySelector('.btn-save-inline');

    var newLabel = input ? input.value.trim() : '';
    if (!newLabel) {
      alert('Checklist item text cannot be empty.');
      return;
    }

    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    }

    var formData = new FormData();
    formData.append('_csrf', csrfToken);
    formData.append('is_ajax', '1');
    formData.append('label', newLabel);

    fetch('<?= View::url('/admin/content/checklists/') ?>' + itemId + '/update', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-check"></i>';
      }

      if (data && data.success) {
        textSpan.innerText = newLabel;
        row.setAttribute('data-label', newLabel);
        inlineEdit.classList.add('d-none');
        inlineEdit.classList.remove('d-flex');
        textSpan.classList.remove('d-none');
        if (pencilFront) pencilFront.classList.add('d-none');

        // Flash highlight effect
        row.style.transition = 'background-color 0.4s ease';
        row.style.backgroundColor = '#dcfce7';
        setTimeout(function () {
          row.style.backgroundColor = '#ffffff';
        }, 600);

        showToast('Checklist item updated successfully!', false);
      } else {
        alert((data && data.message) ? data.message : 'Error updating checklist item.');
      }
    })
    .catch(function (err) {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa fa-check"></i>';
      }
      console.error('Update error:', err);
      alert('Network error while updating item.');
    });
  }

  // 4. Cancel inline edit
  document.addEventListener('click', function (e) {
    var cancelBtn = e.target.closest('.btn-cancel-inline');
    if (!cancelBtn) return;
    e.preventDefault();

    var row = cancelBtn.closest('.checklist-item-row');
    if (!row) return;
    cancelInlineInput(row);
  });

  function cancelInlineInput(row) {
    var textSpan = row.querySelector('.item-text');
    var inlineEdit = row.querySelector('.item-inline-edit');
    var input = row.querySelector('.item-edit-input');
    var originalLabel = row.getAttribute('data-label') || '';
    var pencilFront = row.querySelector('.btn-pencil-front');

    if (input) input.value = originalLabel;
    if (inlineEdit) {
      inlineEdit.classList.add('d-none');
      inlineEdit.classList.remove('d-flex');
    }
    if (textSpan) textSpan.classList.remove('d-none');
    if (pencilFront) pencilFront.classList.add('d-none');
  }

  // ========================================================
  // 5. TRASHBIN ACTION: Delete item via AJAX
  // ========================================================
  document.addEventListener('click', function (e) {
    var delBtn = e.target.closest('.btn-delete-item');
    if (!delBtn) return;
    e.preventDefault();

    var row = delBtn.closest('.checklist-item-row');
    if (!row) return;

    var itemLabel = row.getAttribute('data-label') || 'this item';
    if (!confirm('Are you sure you want to delete "' + itemLabel + '"?')) {
      return;
    }

    var itemId = row.getAttribute('data-id');
    delBtn.disabled = true;
    delBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

    var formData = new FormData();
    formData.append('_csrf', csrfToken);
    formData.append('is_ajax', '1');

    fetch('<?= View::url('/admin/content/checklists/') ?>' + itemId + '/delete', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data && data.success) {
        var list = row.closest('.checklist-items-list');
        var colContainer = row.closest('.col-lg-6');
        var card = row.closest('.service-checklist-card');
        var isInc = (row.getAttribute('data-included') === '1');
        
        row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        
        setTimeout(function () {
          row.remove();
          if (list) {
            updateListNumbering(list);
            saveListOrder(list);
          }
          if (colContainer) {
            var countEl = colContainer.querySelector('.count-badge');
            if (countEl) {
              var curr = parseInt(countEl.innerText) || 0;
              countEl.innerText = Math.max(0, curr - 1);
            }
          }
          if (card) {
            var headerBadge = isInc ? card.querySelector('.header-inc-count') : card.querySelector('.header-exc-count');
            if (headerBadge) {
              var hCurr = parseInt(headerBadge.innerText) || 0;
              headerBadge.innerText = Math.max(0, hCurr - 1);
            }
          }
          if (list && list.children.length === 0) {
            list.innerHTML = '<li class="empty-placeholder text-muted small font-italic py-3 text-center">No items added yet.</li>';
          }
        }, 300);

        showToast('Checklist item deleted.', false);
      } else {
        delBtn.disabled = false;
        delBtn.innerHTML = '<i class="fa fa-trash"></i>';
        alert((data && data.message) ? data.message : 'Error deleting item.');
      }
    })
    .catch(function (err) {
      delBtn.disabled = false;
      delBtn.innerHTML = '<i class="fa fa-trash"></i>';
      alert('Network error while deleting item.');
    });
  });

  // ========================================================
  // 6. QUICK ADD FORM (Inline Add at bottom of each div)
  // ========================================================
  document.addEventListener('submit', function (e) {
    var quickForm = e.target.closest('.quick-add-item-form');
    if (!quickForm) return;
    e.preventDefault();

    var serviceId = quickForm.getAttribute('data-service-id');
    var isIncluded = quickForm.getAttribute('data-is-included');
    var input = quickForm.querySelector('.quick-add-input');
    var submitBtn = quickForm.querySelector('button[type="submit"]');

    var label = input ? input.value.trim() : '';
    if (!label) return;

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    }

    var formData = new FormData();
    formData.append('_csrf', csrfToken);
    formData.append('is_ajax', '1');
    formData.append('service_id', serviceId);
    formData.append('is_included', isIncluded);
    formData.append('label', label);

    fetch('<?= View::url('/admin/content/checklists') ?>', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa fa-plus mr-1"></i>Add';
      }

      if (data && data.success && data.item) {
        input.value = '';
        var colContainer = quickForm.closest('.col-lg-6');
        var card = quickForm.closest('.service-checklist-card');
        var list = colContainer ? colContainer.querySelector('.checklist-items-list') : null;

        if (list) {
          var placeholder = list.querySelector('.empty-placeholder');
          if (placeholder) placeholder.remove();

          var isInc = (parseInt(data.item.is_included) === 1);
          var iconClass = isInc ? 'fa-check text-success' : 'fa-times text-danger';
          var borderStyle = isInc ? 'border-color: #dcfce7 !important;' : 'border-color: #fee2e2 !important;';

          var li = document.createElement('li');
          li.className = 'checklist-item-row d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-white border shadow-sm';
          li.setAttribute('draggable', 'true');
          li.setAttribute('data-id', data.item.id);
          li.setAttribute('data-label', data.item.label);
          li.setAttribute('data-included', data.item.is_included);
          li.setAttribute('data-service-id', data.item.service_id);
          li.style.cssText = borderStyle;

          li.innerHTML = 
            '<div class="item-content-wrapper d-flex align-items-center flex-grow-1 mr-2" style="min-width: 0;">' +
              '<span class="drag-handle mr-2 text-muted" title="Drag and drop to reorder" style="cursor: grab; user-select: none; font-size: 13px;">' +
                '<i class="fa fa-bars"></i>' +
              '</span>' +
              '<span class="badge badge-light border text-muted mr-2 item-number font-weight-bold" style="font-size: 11px; min-width: 22px; padding: 2px 5px; text-align: center;">' +
                (list.children.length + 1) +
              '</span>' +
              '<span class="mr-2" style="font-size: 14px;">' +
                '<i class="fa ' + iconClass + '"></i>' +
              '</span>' +
              '<button type="button" class="btn btn-sm btn-pencil-front mr-2 d-none shadow-sm" title="Click to edit text inline" style="background: #f25b29; color: #fff; border: none; padding: 2px 7px; border-radius: 4px; font-size: 11px; cursor: pointer;">' +
                '<i class="fa fa-pencil"></i>' +
              '</button>' +
              '<span class="item-text text-dark font-weight-medium text-break" style="font-size: 13.5px; line-height: 1.4;">' +
                escapeHtml(data.item.label) +
              '</span>' +
              '<div class="item-inline-edit d-none flex-grow-1 align-items-center" style="gap: 6px;">' +
                '<input type="text" class="form-control form-control-sm item-edit-input" value="' + escapeHtml(data.item.label) + '" style="font-size: 13.5px;">' +
                '<button type="button" class="btn btn-sm btn-success btn-save-inline px-2 py-1 font-weight-bold" title="Save changes">' +
                  '<i class="fa fa-check"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-sm btn-light border btn-cancel-inline px-2 py-1" title="Cancel">' +
                  '<i class="fa fa-times"></i>' +
                '</button>' +
              '</div>' +
            '</div>' +
            '<div class="item-actions-wrapper d-flex align-items-center" style="gap: 4px; flex-shrink: 0;">' +
              '<div class="btn-group btn-group-sm mr-1">' +
                '<button type="button" class="btn btn-sm btn-light border text-secondary btn-move-up px-2 py-1" title="Move Up"><i class="fa fa-chevron-up" style="font-size: 10px;"></i></button>' +
                '<button type="button" class="btn btn-sm btn-light border text-secondary btn-move-down px-2 py-1" title="Move Down"><i class="fa fa-chevron-down" style="font-size: 10px;"></i></button>' +
              '</div>' +
              '<button type="button" class="btn btn-sm btn-light border text-primary btn-toggle-edit px-2 py-1" title="Edit this line" style="font-size: 12px; font-weight: 600;">' +
                '<i class="fa fa-pencil mr-1"></i>Edit' +
              '</button>' +
              '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-item px-2 py-1" title="Delete this line" style="font-size: 12px;">' +
                '<i class="fa fa-trash"></i>' +
              '</button>' +
            '</div>';

          list.appendChild(li);
          updateListNumbering(list);

          // Update count badges
          var countEl = colContainer.querySelector('.count-badge');
          if (countEl) {
            var curr = parseInt(countEl.innerText) || 0;
            countEl.innerText = curr + 1;
          }
          if (card) {
            var headerBadge = isInc ? card.querySelector('.header-inc-count') : card.querySelector('.header-exc-count');
            if (headerBadge) {
              var hCurr = parseInt(headerBadge.innerText) || 0;
              headerBadge.innerText = hCurr + 1;
            }
          }
        }

        showToast('Checklist item added successfully!', false);
      } else {
        alert((data && data.message) ? data.message : 'Error adding checklist item.');
      }
    })
    .catch(function (err) {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa fa-plus mr-1"></i>Add';
      }
      alert('Network error while adding item.');
    });
  });

  function escapeHtml(text) {
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
  }
});
</script>
