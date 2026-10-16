<?php
$staffMembers = $staffMembers ?? [];
$totalCount = (int)($totalCount ?? count($staffMembers));

$activeCount = 0;
$pendingCount = 0;
$disabledCount = 0;
$totalCompletedJobs = 0;

foreach ($staffMembers as $s) {
    if (($s['status'] ?? '') === 'active') {
        $activeCount++;
    } elseif (($s['status'] ?? '') === 'pending') {
        $pendingCount++;
    } else {
        $disabledCount++;
    }
    $totalCompletedJobs += (int)($s['completed_jobs_count'] ?? 0);
}
?>

<div class="container-fluid p-0">
  <!-- Section Header with Actions -->
  <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
    <div>
      <h4 class="font-weight-bold text-dark mb-0" style="font-size: 20px;">
        <i class="fa fa-wrench text-info mr-1"></i>Technicians Directory
      </h4>
      <p class="text-muted small mb-0" style="font-size: 12px;">Global field workforce directory, skill qualifications, and account lifecycle management.</p>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-sm-0" style="gap: 8px;">
      <a href="<?= \App\Core\View::url('/admin/staff/create') ?>" class="btn btn-sm btn-primary font-weight-bold px-3 shadow-sm" style="border-radius: 6px; font-size: 12px;">
        <i class="fa fa-user-plus mr-1"></i> Add Technician
      </a>
      <button type="button" class="btn btn-sm btn-danger font-weight-bold shadow-sm px-3" data-toggle="modal" data-target="#modalSectionDeleteStaff" <?= empty($staffMembers) ? 'disabled' : '' ?> style="border-radius: 6px; font-size: 12px;">
        <i class="fa fa-trash mr-1"></i> Delete All (<?= $totalCount ?>)
      </button>
    </div>
  </div>

  <!-- 4-CARD SUPER ADMIN KPI METRICS STRIP -->
  <div class="row mx-n1 mb-2">
    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="sa-card p-2 px-3 shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #6366f1 !important; border-radius: 8px;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Total Registered</span>
          <span class="font-weight-bold text-dark" style="font-size: 18px; line-height: 1.1;"><?= $totalCount ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
          <i class="fa fa-users" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="sa-card p-2 px-3 shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #10b981 !important; border-radius: 8px;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Active Technicians</span>
          <span class="font-weight-bold text-success" style="font-size: 18px; line-height: 1.1;"><?= $activeCount ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.12);">
          <i class="fa fa-check-circle" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="sa-card p-2 px-3 shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #f59e0b !important; border-radius: 8px;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Pending Approval</span>
          <span class="font-weight-bold text-warning" style="font-size: 18px; line-height: 1.1;"><?= $pendingCount ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.12);">
          <i class="fa fa-clock-o" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="sa-card p-2 px-3 shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #0284c7 !important; border-radius: 8px;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Completed Jobs</span>
          <span class="font-weight-bold text-info" style="font-size: 18px; line-height: 1.1;"><?= $totalCompletedJobs ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-info" style="width: 32px; height: 32px; background: rgba(2, 132, 199, 0.12);">
          <i class="fa fa-trophy" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- INTERACTIVE SEARCH & FILTER TOOLBAR -->
  <div class="sa-card p-2 px-3 mb-2 bg-white" style="border-radius: 8px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
      <!-- Search Input -->
      <div class="position-relative flex-grow-1 mr-md-3" style="max-width: 400px;">
        <i class="fa fa-search position-absolute text-muted" style="left: 10px; top: 9px; font-size: 13px;"></i>
        <input type="text" id="saTechSearchInput" class="form-control form-control-sm pl-4" placeholder="Filter by technician name, phone, email, or skill..." style="font-size: 12.5px; border-radius: 6px;">
      </div>

      <!-- Quick Filter Pills -->
      <div class="d-flex align-items-center gap-1 flex-wrap mt-1 mt-md-0" style="gap: 5px;">
        <span class="text-muted small font-weight-bold mr-1" style="font-size: 11px;">Filter:</span>
        <button type="button" class="btn btn-xs font-weight-bold sa-filter-btn active" data-filter="all" style="border-radius: 5px; font-size: 11.5px;">
          All (<?= $totalCount ?>)
        </button>
        <button type="button" class="btn btn-xs font-weight-bold sa-filter-btn" data-filter="active" style="border-radius: 5px; font-size: 11.5px;">
          Active (<?= $activeCount ?>)
        </button>
        <?php if ($pendingCount > 0): ?>
          <button type="button" class="btn btn-xs font-weight-bold sa-filter-btn" data-filter="pending" style="border-radius: 5px; font-size: 11.5px;">
            Pending (<?= $pendingCount ?>)
          </button>
        <?php endif; ?>
        <?php if ($disabledCount > 0): ?>
          <button type="button" class="btn btn-xs font-weight-bold sa-filter-btn" data-filter="disabled" style="border-radius: 5px; font-size: 11.5px;">
            Disabled (<?= $disabledCount ?>)
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- COMPACT TECHNICIANS TABLE -->
  <div class="sa-card p-0 overflow-hidden mb-2" style="border-radius: 10px;">
    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
      <table class="table table-hover mb-0" id="saTechTable" style="font-size: 13px;">
        <thead class="bg-light sticky-top" style="z-index: 2;">
          <tr>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">ID</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Technician</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Contact</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Authorized Trades</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Rating</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Jobs Done</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Status</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Joined On</th>
            <th class="text-right" style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($staffMembers)): ?>
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="fa fa-users fa-3x text-muted mb-2 d-block" style="opacity: 0.3;"></i>
                No technicians registered in the database.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($staffMembers as $s): ?>
              <?php
                $sStatus = $s['status'] ?? 'active';
                $isAvail = !empty($s['is_available']);
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)($s['phone'] ?? ''));
                $waPhone = (strlen($cleanPhone) === 10) ? ('91' . $cleanPhone) : $cleanPhone;
                $tradeSkills = !empty($s['trade_skills']) ? explode(', ', $s['trade_skills']) : [];
              ?>
              <tr class="sa-tech-row"
                  data-name="<?= strtolower(\App\Core\View::e($s['name'])) ?>"
                  data-phone="<?= strtolower(\App\Core\View::e($s['phone'] ?? '')) ?>"
                  data-email="<?= strtolower(\App\Core\View::e($s['email'] ?? '')) ?>"
                  data-trades="<?= strtolower(\App\Core\View::e($s['trade_skills'] ?? '')) ?>"
                  data-status="<?= $sStatus ?>">
                <td class="font-weight-bold text-dark" style="padding: 9px 14px; white-space: nowrap;">#<?= $s['id'] ?></td>
                
                <td style="padding: 9px 14px;">
                  <div class="d-flex align-items-center">
                    <div class="position-relative mr-2 flex-shrink-0">
                      <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white font-weight-bold" 
                           style="width: 34px; height: 34px; font-size: 14px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                        <?= strtoupper(substr($s['name'] ?? 'T', 0, 1)) ?>
                      </div>
                      <span class="position-absolute rounded-circle border border-white" 
                            style="bottom: 0; right: 0; width: 9px; height: 9px; background: <?= $isAvail ? '#10b981' : '#94a3b8' ?>;"
                            title="<?= $isAvail ? 'Available' : 'Off Duty' ?>"></span>
                    </div>
                    <div>
                      <a href="<?= \App\Core\View::url('/admin/staff/' . $s['id']) ?>" class="font-weight-bold text-dark text-decoration-none">
                        <?= \App\Core\View::e($s['name']) ?>
                      </a>
                      <div class="text-muted" style="font-size: 11px;"><?= \App\Core\View::e($s['availability_note'] ?? 'Field Staff') ?></div>
                    </div>
                  </div>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <div class="d-flex align-items-center gap-1">
                    <a href="tel:<?= \App\Core\View::e($s['phone'] ?? '') ?>" class="text-dark small font-weight-bold text-decoration-none mr-2">
                      <i class="fa fa-phone text-success mr-1"></i><?= \App\Core\View::e($s['phone'] ?? 'N/A') ?>
                    </a>
                    <?php if (!empty($waPhone)): ?>
                      <a href="https://wa.me/<?= \App\Core\View::e($waPhone) ?>" target="_blank" rel="noopener noreferrer" class="text-success small" title="Chat on WhatsApp">
                        <i class="fa fa-whatsapp"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($s['email'])): ?>
                    <div class="text-muted" style="font-size: 11px;"><?= \App\Core\View::e($s['email']) ?></div>
                  <?php endif; ?>
                </td>

                <td style="padding: 9px 14px;">
                  <?php if (empty($tradeSkills)): ?>
                    <span class="text-muted small" style="font-size: 11px;"><em>No trades assigned</em></span>
                  <?php else: ?>
                    <div class="d-flex flex-wrap gap-1" style="gap: 4px; max-width: 220px;">
                      <?php 
                        $visibleSkills = array_slice($tradeSkills, 0, 2);
                        $remaining = count($tradeSkills) - count($visibleSkills);
                      ?>
                      <?php foreach ($visibleSkills as $ts): ?>
                        <span class="badge badge-light border text-dark font-weight-bold py-1 px-2" style="font-size: 11px; border-radius: 4px;">
                          <?= \App\Core\View::e($ts) ?>
                        </span>
                      <?php endforeach; ?>
                      <?php if ($remaining > 0): ?>
                        <span class="badge badge-secondary py-1 px-1 font-weight-bold" style="font-size: 10px; border-radius: 4px;" title="<?= \App\Core\View::e(implode(', ', array_slice($tradeSkills, 2))) ?>">
                          +<?= $remaining ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                    ★ <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?> (<?= $s['rating_count'] ?? 0 ?>)
                  </span>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px;">
                    <?= $s['completed_jobs_count'] ?? 0 ?> / <?= $s['jobs_count'] ?? 0 ?> Jobs
                  </span>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <?php if ($sStatus === 'active'): ?>
                    <span class="badge badge-success px-2 py-1" style="font-size: 11px;">Active</span>
                  <?php elseif ($sStatus === 'pending'): ?>
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;"><i class="fa fa-clock-o mr-1"></i>Pending</span>
                  <?php else: ?>
                    <span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">Disabled</span>
                  <?php endif; ?>
                </td>

                <td class="text-muted small" style="padding: 9px 14px; white-space: nowrap; font-size: 11.5px;">
                  <?= !empty($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : 'N/A' ?>
                </td>

                <td class="text-right" style="padding: 9px 14px; white-space: nowrap;">
                  <?php if ($sStatus === 'pending'): ?>
                    <form action="<?= \App\Core\View::url('/superadmin/staff/' . $s['id'] . '/approve') ?>" method="POST" class="d-inline mr-1" onsubmit="return confirm('Approve partner <?= \App\Core\View::e($s['name']) ?>?');">
                      <?= \App\Core\View::csrfField() ?>
                      <button type="submit" class="btn btn-xs btn-success font-weight-bold shadow-sm px-2 py-1" title="Approve & Send Credentials">
                        <i class="fa fa-check-circle mr-1"></i> Approve
                      </button>
                    </form>
                  <?php endif; ?>
                  <a href="<?= \App\Core\View::url('/admin/staff/' . $s['id']) ?>" class="btn btn-xs btn-outline-secondary font-weight-bold px-2 py-1 mr-1" title="View Technician Details">
                    <i class="fa fa-eye mr-1"></i> View
                  </a>
                  <button type="button" class="btn btn-xs btn-outline-danger px-2 py-1" onclick="confirmDeleteStaff(<?= $s['id'] ?>)" title="Delete Technician">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr id="saNoMatchRow" style="display: none;">
              <td colspan="9" class="text-center py-4 text-muted">
                <i class="fa fa-search fa-2x mb-2 d-block text-muted" style="opacity: 0.3;"></i>
                No technicians match the selected search or filter.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Delete All Staff -->
<div class="modal fade" id="modalSectionDeleteStaff" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white p-3">
        <h5 class="modal-title font-weight-bold" style="font-size: 16px;"><i class="fa fa-user-times mr-2"></i>Delete All Technicians?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/staff/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-exclamation-triangle fa-3x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= $totalCount ?> Technician accounts?</h5>
          <p class="text-muted small mb-0">
            This will permanently delete every technician account, remove their skill categories, clear availability schedules, and unlink active job dispatches safely.
          </p>
        </div>
        <div class="modal-footer bg-light p-2 px-3 d-flex justify-content-between">
          <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Technicians
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<form id="deleteSingleStaffForm" action="" method="POST" style="display:none;">
  <?= \App\Core\View::csrfField() ?>
</form>

<style>
.sa-filter-btn {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  transition: all 0.15s ease;
}
.sa-filter-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}
.sa-filter-btn.active {
  background: #6366f1 !important;
  color: #ffffff !important;
  border-color: #6366f1 !important;
  box-shadow: 0 1px 4px rgba(99, 102, 241, 0.3);
}
</style>

<script>
function confirmDeleteStaff(id) {
  if (confirm('Permanently delete technician #' + id + '? Active assignments will be reset.')) {
    var f = document.getElementById('deleteSingleStaffForm');
    f.action = '<?= \App\Core\View::url('/superadmin/staff/') ?>' + id + '/delete';
    f.submit();
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('saTechSearchInput');
  const filterBtns = document.querySelectorAll('.sa-filter-btn');
  const rows = document.querySelectorAll('.sa-tech-row');
  const noMatchRow = document.getElementById('saNoMatchRow');

  let activeFilter = 'all';

  function applyFilters() {
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    let visibleCount = 0;

    rows.forEach(function (row) {
      const name = row.getAttribute('data-name') || '';
      const phone = row.getAttribute('data-phone') || '';
      const email = row.getAttribute('data-email') || '';
      const trades = row.getAttribute('data-trades') || '';
      const status = row.getAttribute('data-status') || '';

      const matchesSearch = !query || name.includes(query) || phone.includes(query) || email.includes(query) || trades.includes(query);
      let matchesFilter = true;

      if (activeFilter === 'active') {
        matchesFilter = (status === 'active');
      } else if (activeFilter === 'pending') {
        matchesFilter = (status === 'pending');
      } else if (activeFilter === 'disabled') {
        matchesFilter = (status === 'disabled');
      }

      if (matchesSearch && matchesFilter) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    if (noMatchRow) {
      noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.getAttribute('data-filter') || 'all';
      applyFilters();
    });
  });
});
</script>
