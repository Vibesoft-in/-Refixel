<?php
use App\Core\View;
$staff = $staff ?? [];
$pendingAppsCount = (int)($pendingAppsCount ?? 0);

$totalStaff = count($staff);
$availableCount = 0;
$offDutyCount = 0;
$disabledCount = 0;

foreach ($staff as $s) {
    if (($s['status'] ?? '') === 'disabled') {
        $disabledCount++;
    } elseif (!empty($s['is_available'])) {
        $availableCount++;
    } else {
        $offDutyCount++;
    }
}
?>

<div class="admin-staff-page">
  <!-- Top Header with Actions -->
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
    <div>
      <h4 class="font-weight-bold mb-0 text-dark" style="font-size: 20px;">Field Technicians & Staff</h4>
      <p class="text-muted small mb-0" style="font-size: 12px;">Active workforce roster, trade qualifications, and live duty availability.</p>
    </div>

    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0" style="gap: 8px;">
      <a href="<?= View::url('/admin/partner-applications') ?>" class="btn btn-sm btn-outline-warning text-dark font-weight-bold px-3 shadow-sm" style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 6px; font-size: 12px;">
        <i class="fa fa-id-card-o mr-1 text-warning"></i>Partner Applications
        <?php if ($pendingAppsCount > 0): ?>
          <span class="badge badge-warning text-dark ml-1 font-weight-bold" style="background: #f59e0b; color: #fff;"><?= $pendingAppsCount ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= View::url('/admin/staff/create') ?>" class="btn btn-sm btn-brand font-weight-bold px-3 shadow-sm" style="border-radius: 6px; font-size: 12px;">
        <i class="fa fa-user-plus mr-1"></i>Add Technician
      </a>
    </div>
  </div>

  <?php if ($pendingAppsCount > 0): ?>
    <div class="alert alert-warning mb-2 border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap p-2 px-3" style="border-radius: 8px; background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b !important;">
      <div class="d-flex align-items-center py-1">
        <i class="fa fa-id-card-o text-warning mr-2" style="font-size: 18px;"></i>
        <span class="small font-weight-bold">
          <?= $pendingAppsCount ?> new partner application(s) awaiting verification!
        </span>
      </div>
      <a href="<?= View::url('/admin/partner-applications') ?>" class="btn btn-xs font-weight-bold px-3 py-1 shadow-sm" style="background: #f59e0b; color: #ffffff; border-radius: 5px;">
        Review & Approve &rarr;
      </a>
    </div>
  <?php endif; ?>

  <!-- 4-CARD WORKFORCE KPI STRIP -->
  <div class="row mx-n1 mb-2">
    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #6366f1 !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Total Workforce</span>
          <span class="font-weight-bold text-dark" style="font-size: 18px; line-height: 1.1;"><?= $totalStaff ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
          <i class="fa fa-users" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #10b981 !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Available on Duty</span>
          <span class="font-weight-bold text-success" style="font-size: 18px; line-height: 1.1;"><?= $availableCount ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.12);">
          <i class="fa fa-bolt" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #94a3b8 !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Off Duty / Idle</span>
          <span class="font-weight-bold text-muted" style="font-size: 18px; line-height: 1.1;"><?= $offDutyCount ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 32px; height: 32px; background: #f1f5f9;">
          <i class="fa fa-moon-o" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #f59e0b !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Pending Review</span>
          <span class="font-weight-bold text-warning" style="font-size: 18px; line-height: 1.1;"><?= $pendingAppsCount ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.12);">
          <i class="fa fa-clock-o" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- INTERACTIVE TOOLBAR: SEARCH & STATUS PILL FILTERS -->
  <div class="stat-card p-2 px-3 mb-2 bg-white" style="border-radius: 8px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
      <!-- Search Input -->
      <div class="position-relative flex-grow-1 mr-md-3" style="max-width: 400px;">
        <i class="fa fa-search position-absolute text-muted" style="left: 10px; top: 9px; font-size: 13px;"></i>
        <input type="text" id="techSearchInput" class="form-control form-control-sm pl-4" placeholder="Filter by technician name, phone, email, or skill..." style="font-size: 12.5px; border-radius: 6px;">
      </div>

      <!-- Quick Filter Pills -->
      <div class="d-flex align-items-center gap-1 flex-wrap mt-1 mt-md-0" style="gap: 5px;">
        <span class="text-muted small font-weight-bold mr-1" style="font-size: 11px;">Status:</span>
        <button type="button" class="btn btn-xs font-weight-bold filter-btn active" data-filter="all" style="border-radius: 5px; font-size: 11.5px;">
          All (<?= $totalStaff ?>)
        </button>
        <button type="button" class="btn btn-xs font-weight-bold filter-btn" data-filter="available" style="border-radius: 5px; font-size: 11.5px;">
          Available (<?= $availableCount ?>)
        </button>
        <button type="button" class="btn btn-xs font-weight-bold filter-btn" data-filter="offduty" style="border-radius: 5px; font-size: 11.5px;">
          Off Duty (<?= $offDutyCount ?>)
        </button>
        <?php if ($disabledCount > 0): ?>
          <button type="button" class="btn btn-xs font-weight-bold filter-btn" data-filter="disabled" style="border-radius: 5px; font-size: 11.5px;">
            Disabled (<?= $disabledCount ?>)
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- COMPACT TECHNICIANS TABLE -->
  <div class="stat-card p-0 overflow-hidden mb-2" style="border-radius: 10px;">
    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
      <table class="table table-hover mb-0" id="techTable" style="font-size: 13px;">
        <thead class="bg-light sticky-top" style="z-index: 2;">
          <tr>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Technician</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Contact</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Authorized Trades</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Rating</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Duty Status</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Workload</th>
            <th style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Account</th>
            <th class="text-right" style="padding: 10px 14px; font-size: 11px; white-space: nowrap;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($staff)): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="fa fa-users fa-2x mb-2 d-block text-muted" style="opacity: 0.3;"></i>
                No staff members registered. Click "Add Technician" above.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($staff as $s): ?>
              <?php
                $isAvail = !empty($s['is_available']);
                $sStatus = $s['status'] ?? 'active';
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)($s['phone'] ?? ''));
                $waPhone = (strlen($cleanPhone) === 10) ? ('91' . $cleanPhone) : $cleanPhone;
                $tradeSkills = !empty($s['trade_skills']) ? explode(', ', $s['trade_skills']) : [];
              ?>
              <tr class="tech-row" 
                  data-name="<?= strtolower(View::e($s['name'])) ?>" 
                  data-phone="<?= strtolower(View::e($s['phone'])) ?>" 
                  data-email="<?= strtolower(View::e($s['email'] ?? '')) ?>" 
                  data-trades="<?= strtolower(View::e($s['trade_skills'] ?? '')) ?>"
                  data-status="<?= $sStatus ?>"
                  data-avail="<?= $isAvail ? 'available' : 'offduty' ?>">
                <td style="padding: 9px 14px;">
                  <div class="d-flex align-items-center">
                    <!-- Avatar with Presence Dot -->
                    <div class="position-relative mr-2 flex-shrink-0">
                      <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white font-weight-bold" 
                           style="width: 36px; height: 36px; font-size: 15px; background: linear-gradient(135deg, #f25b29 0%, #d44d20 100%);">
                        <?= strtoupper(substr($s['name'], 0, 1)) ?>
                      </div>
                      <span class="position-absolute rounded-circle border border-white" 
                            style="bottom: 0; right: 0; width: 10px; height: 10px; background: <?= $isAvail ? '#10b981' : '#94a3b8' ?>;"
                            title="<?= $isAvail ? 'Available' : 'Off Duty' ?>"></span>
                    </div>
                    <div>
                      <a href="<?= View::url('/admin/staff/' . $s['id']) ?>" class="font-weight-bold text-dark text-decoration-none">
                        <?= View::e($s['name']) ?>
                      </a>
                      <div class="text-muted" style="font-size: 11px;">ID: #<?= (int)$s['id'] ?></div>
                    </div>
                  </div>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <div class="d-flex align-items-center gap-1">
                    <a href="tel:<?= View::e($s['phone']) ?>" class="text-dark small font-weight-bold text-decoration-none mr-2">
                      <i class="fa fa-phone text-success mr-1"></i><?= View::e($s['phone']) ?>
                    </a>
                    <?php if (!empty($waPhone)): ?>
                      <a href="https://wa.me/<?= View::e($waPhone) ?>" target="_blank" rel="noopener noreferrer" class="text-success small" title="Chat on WhatsApp">
                        <i class="fa fa-whatsapp"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($s['email'])): ?>
                    <div class="text-muted" style="font-size: 11px;"><?= View::e($s['email']) ?></div>
                  <?php endif; ?>
                </td>

                <td style="padding: 9px 14px;">
                  <?php if (empty($tradeSkills)): ?>
                    <span class="text-muted small" style="font-size: 11px;"><em>No trades assigned</em></span>
                  <?php else: ?>
                    <div class="d-flex flex-wrap gap-1" style="gap: 4px; max-width: 240px;">
                      <?php 
                        $visibleSkills = array_slice($tradeSkills, 0, 2);
                        $remaining = count($tradeSkills) - count($visibleSkills);
                      ?>
                      <?php foreach ($visibleSkills as $ts): ?>
                        <span class="badge badge-light border text-dark font-weight-bold py-1 px-2" style="font-size: 11px; border-radius: 4px;">
                          <?= View::e($ts) ?>
                        </span>
                      <?php endforeach; ?>
                      <?php if ($remaining > 0): ?>
                        <span class="badge badge-secondary py-1 px-1 font-weight-bold" style="font-size: 10px; border-radius: 4px;" title="<?= View::e(implode(', ', array_slice($tradeSkills, 2))) ?>">
                          +<?= $remaining ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px;">
                    <i class="fa fa-star text-warning"></i> <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?>
                  </span>
                  <span class="text-muted small ml-1">(<?= (int)($s['rating_count'] ?? 0) ?>)</span>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <?php if ($isAvail): ?>
                    <span class="badge font-weight-bold px-2 py-1" style="font-size: 11px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                      <i class="fa fa-circle mr-1" style="font-size: 7px;"></i>Available
                    </span>
                  <?php else: ?>
                    <span class="badge font-weight-bold px-2 py-1" style="font-size: 11px; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                      <i class="fa fa-circle mr-1" style="font-size: 7px;"></i>Off Duty
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($s['availability_note'])): ?>
                    <div class="text-muted text-truncate mt-1" style="font-size: 11px; max-width: 140px;" title="<?= View::e($s['availability_note']) ?>">
                      <?= View::e($s['availability_note']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td style="padding: 9px 14px; white-space: nowrap;">
                  <div class="small">
                    <span class="font-weight-bold text-dark"><?= (int)$s['active_jobs'] ?></span> active
                    <span class="text-muted">/ <?= (int)$s['total_jobs'] ?> tot</span>
                  </div>
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

                <td class="text-right" style="padding: 9px 14px; white-space: nowrap;">
                  <div class="d-inline-flex align-items-center">
                    <?php if ($sStatus === 'pending'): ?>
                      <form action="<?= View::url('/admin/staff/' . $s['id'] . '/approve') ?>" method="POST" class="d-inline mr-1" onsubmit="return confirm('Approve partner <?= View::e($s['name']) ?>?');">
                        <?= View::csrfField() ?>
                        <input type="hidden" name="redirect" value="<?= View::url('/admin/staff') ?>">
                        <button type="submit" class="btn btn-xs btn-success px-2 py-1 font-weight-bold shadow-sm" title="Approve application">
                          <i class="fa fa-check mr-1"></i>Approve
                        </button>
                      </form>
                    <?php endif; ?>
                    <a href="<?= View::url('/admin/staff/' . $s['id']) ?>" class="btn btn-xs btn-brand-outline px-2 py-1 font-weight-bold" style="font-size: 11.5px;">
                      Profile &rarr;
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr id="noMatchingRow" style="display: none;">
              <td colspan="8" class="text-center py-4 text-muted">
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

<style>
.filter-btn {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  transition: all 0.15s ease;
}
.filter-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}
.filter-btn.active {
  background: #f25b29 !important;
  color: #ffffff !important;
  border-color: #f25b29 !important;
  box-shadow: 0 1px 4px rgba(242, 91, 41, 0.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('techSearchInput');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const rows = document.querySelectorAll('.tech-row');
  const noMatchRow = document.getElementById('noMatchingRow');

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
      const avail = row.getAttribute('data-avail') || '';

      const matchesSearch = !query || name.includes(query) || phone.includes(query) || email.includes(query) || trades.includes(query);
      let matchesFilter = true;

      if (activeFilter === 'available') {
        matchesFilter = (avail === 'available' && status === 'active');
      } else if (activeFilter === 'offduty') {
        matchesFilter = (avail === 'offduty' && status === 'active');
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

