<?php
use App\Core\View;
use App\Core\Auth;

$user = $user ?? [];
$profile = $profile ?? [];
$skills = $skills ?? [];
$allCategories = $allCategories ?? [];
$jobs = $jobs ?? [];
$earnings = $earnings ?? [];
$earningsSummary = $earningsSummary ?? ['total_earned' => 0, 'this_month' => 0, 'settled' => 0, 'pending' => 0];

$skillIds = array_column($skills, 'id');
$currentUser = Auth::user();
$isSuperAdmin = ($currentUser && ($currentUser['role'] ?? '') === 'superadmin');
$backUrl = $isSuperAdmin ? View::url('/superadmin/staff') : View::url('/admin/staff');
$backTitle = $isSuperAdmin ? 'Super Admin Technicians' : 'Staff Directory';

// Clean phone for WhatsApp / dialer
$cleanPhone = preg_replace('/[^0-9]/', '', (string)($user['phone'] ?? ''));
$waPhone = (strlen($cleanPhone) === 10) ? ('91' . $cleanPhone) : $cleanPhone;
?>

<div class="admin-staff-show">
  <!-- Top Navigation & Breadcrumb -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a href="<?= $backUrl ?>" class="btn btn-sm btn-light border font-weight-bold text-dark px-2 py-1" style="font-size: 12px; border-radius: 6px;">
      <i class="fa fa-arrow-left mr-1 text-muted"></i>Back to <?= View::e($backTitle) ?>
    </a>

    <div class="d-flex align-items-center gap-2" style="gap: 8px;">
      <?php if (($user['status'] ?? '') === 'pending'): ?>
        <form method="POST" action="<?= View::url('/admin/staff/' . $user['id'] . '/approve') ?>" class="d-inline" onsubmit="return confirm('Approve technician <?= View::e($user['name']) ?> and send portal login password to <?= View::e($user['email']) ?>?');">
          <?= View::csrfField() ?>
          <button type="submit" class="btn btn-sm btn-success font-weight-bold shadow-sm px-3" style="border-radius: 6px;">
            <i class="fa fa-check-circle mr-1"></i>Approve & Send Password
          </button>
        </form>
      <?php endif; ?>

      <!-- Toggle Status Button -->
      <form method="POST" action="<?= View::url('/admin/staff/' . $user['id'] . '/toggle-status') ?>" class="d-inline">
        <?= View::csrfField() ?>
        <?php if (($user['status'] ?? '') === 'active'): ?>
          <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold px-3" style="border-radius: 6px;" onclick="return confirm('Are you sure you want to deactivate this technician?')">
            <i class="fa fa-ban mr-1"></i>Deactivate Account
          </button>
        <?php elseif (($user['status'] ?? '') === 'pending'): ?>
          <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;"><i class="fa fa-clock-o mr-1"></i>Pending Review</span>
        <?php else: ?>
          <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3" style="border-radius: 6px;">
            <i class="fa fa-check mr-1"></i>Activate Account
          </button>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <?php if (($user['status'] ?? '') === 'pending'): ?>
    <div class="alert alert-warning mb-2 border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap p-2 px-3" style="border-radius: 8px; background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b !important;">
      <div class="d-flex align-items-center py-1">
        <i class="fa fa-clock-o text-warning mr-2" style="font-size: 18px;"></i>
        <span class="small font-weight-bold">
          Partner application pending approval. Password will be generated & emailed to <u><?= View::e($user['email']) ?></u>.
        </span>
      </div>
      <form method="POST" action="<?= View::url('/admin/staff/' . $user['id'] . '/approve') ?>" class="py-1" onsubmit="return confirm('Approve technician <?= View::e($user['name']) ?>?');">
        <?= View::csrfField() ?>
        <button type="submit" class="btn btn-xs btn-success font-weight-bold px-3 py-1 shadow-sm" style="border-radius: 5px;">
          <i class="fa fa-check mr-1"></i>Approve Now
        </button>
      </form>
    </div>
  <?php endif; ?>

  <!-- 1. COMPACT HERO PROFILE HEADER CARD -->
  <div class="stat-card p-3 mb-2" style="border-radius: 10px; background: #ffffff;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
      <!-- Left: Avatar + Details -->
      <div class="d-flex align-items-center">
        <!-- Avatar Circle with presence dot -->
        <div class="position-relative mr-3 flex-shrink-0">
          <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold shadow-sm" 
               style="width: 52px; height: 52px; font-size: 22px; background: linear-gradient(135deg, #f25b29 0%, #d44d20 100%); border: 2.5px solid #fff; box-shadow: 0 2px 8px rgba(242, 91, 41, 0.3);">
            <?= strtoupper(substr($user['name'] ?? 'T', 0, 1)) ?>
          </div>
          <!-- Online/Offline Presence Dot -->
          <span class="position-absolute rounded-circle border border-white" 
                style="bottom: 1px; right: 1px; width: 14px; height: 14px; background: <?= !empty($profile['is_available']) ? '#10b981' : '#94a3b8' ?>;"
                title="<?= !empty($profile['is_available']) ? 'Available for Jobs' : 'Off Duty' ?>"></span>
        </div>

        <div>
          <div class="d-flex align-items-center flex-wrap gap-2 mb-1" style="gap: 6px;">
            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 18px; line-height: 1.2;">
              <?= View::e($user['name']) ?>
            </h5>
            <span class="badge badge-light border text-muted px-2 py-0 font-weight-bold" style="font-size: 11px;">#<?= (int)$user['id'] ?></span>
            
            <?php if (($user['status'] ?? '') === 'active'): ?>
              <span class="badge badge-success px-2 py-0 font-weight-bold" style="font-size: 11px;">Active</span>
            <?php elseif (($user['status'] ?? '') === 'pending'): ?>
              <span class="badge badge-warning text-dark px-2 py-0 font-weight-bold" style="font-size: 11px;">Pending</span>
            <?php else: ?>
              <span class="badge badge-secondary px-2 py-0 font-weight-bold" style="font-size: 11px;">Disabled</span>
            <?php endif; ?>

            <?php if (!empty($profile['is_available'])): ?>
              <span class="badge font-weight-bold px-2 py-0" style="font-size: 11px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                <i class="fa fa-circle mr-1" style="font-size: 7px;"></i>Available
              </span>
            <?php else: ?>
              <span class="badge font-weight-bold px-2 py-0" style="font-size: 11px; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                <i class="fa fa-circle mr-1" style="font-size: 7px;"></i>Off Duty
              </span>
            <?php endif; ?>
          </div>

          <!-- Contact Row with Quick Actions -->
          <div class="d-flex align-items-center flex-wrap gap-3 text-muted small" style="gap: 12px; font-size: 12.5px;">
            <a href="tel:<?= View::e($user['phone']) ?>" class="text-dark font-weight-bold text-decoration-none">
              <i class="fa fa-phone text-success mr-1"></i><?= View::e($user['phone']) ?>
            </a>
            <?php if (!empty($waPhone)): ?>
              <a href="https://wa.me/<?= View::e($waPhone) ?>" target="_blank" rel="noopener noreferrer" class="text-success small font-weight-bold text-decoration-none" title="Chat on WhatsApp">
                <i class="fa fa-whatsapp mr-1"></i>WhatsApp
              </a>
            <?php endif; ?>
            <?php if (!empty($user['email'])): ?>
              <a href="mailto:<?= View::e($user['email']) ?>" class="text-muted text-decoration-none">
                <i class="fa fa-envelope text-primary mr-1"></i><?= View::e($user['email']) ?>
              </a>
            <?php endif; ?>
            <span class="text-muted border-left pl-2">
              <i class="fa fa-star text-warning mr-1"></i><strong><?= number_format((float)($profile['rating_avg'] ?? 5.0), 1) ?></strong> (<?= (int)($profile['rating_count'] ?? 0) ?> reviews)
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Duty Note Pill -->
      <div class="text-md-right w-100 w-md-auto mt-2 mt-md-0">
        <div class="p-2 px-3 rounded small border d-inline-block text-left" style="background: #f8fafc; border-color: #e2e8f0; max-width: 320px;">
          <div class="text-muted font-weight-bold" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Dispatch Note</div>
          <div class="text-dark font-weight-bold text-truncate" style="font-size: 12px;" title="<?= View::e($profile['availability_note'] ?? 'Available for dispatch') ?>">
            <?= View::e($profile['availability_note'] ?? 'Available for dispatch') ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. QUICK 4-CARD FINANCIAL & ACTIVITY METRICS STRIP -->
  <div class="row mx-n1 mb-2">
    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #10b981 !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Total Commission</span>
          <span class="font-weight-bold text-success" style="font-size: 17px; line-height: 1.1;">₹<?= number_format((float)$earningsSummary['total_earned'], 2) ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.12);">
          <i class="fa fa-inr" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #0284c7 !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Month (<?= date('M Y') ?>)</span>
          <span class="font-weight-bold text-dark" style="font-size: 17px; line-height: 1.1;">₹<?= number_format((float)$earningsSummary['this_month'], 2) ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-info" style="width: 32px; height: 32px; background: rgba(2, 132, 199, 0.12);">
          <i class="fa fa-calendar-check-o" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #6366f1 !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Settled Payouts</span>
          <span class="font-weight-bold text-dark" style="font-size: 17px; line-height: 1.1;">₹<?= number_format((float)$earningsSummary['settled'], 2) ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
          <i class="fa fa-check" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 px-1 mb-1 mb-md-0">
      <div class="p-2 px-3 rounded border bg-white shadow-sm d-flex align-items-center justify-content-between h-100" style="border-left: 3.5px solid #f59e0b !important;">
        <div>
          <span class="text-muted small font-weight-bold d-block" style="font-size: 11px; text-transform: uppercase;">Pending Settlement</span>
          <span class="font-weight-bold text-warning" style="font-size: 17px; line-height: 1.1;">₹<?= number_format((float)$earningsSummary['pending'], 2) ?></span>
        </div>
        <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.12);">
          <i class="fa fa-clock-o" style="font-size: 14px;"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. BALANCED DUAL-COLUMN WORKSPACE: SKILLS (LEFT) + JOBS HISTORY (RIGHT) -->
  <div class="row mx-n1">
    <!-- LEFT COLUMN: AUTHORIZED TRADE SKILLS (COMPACT MULTI-COLUMN CHIP GRID) -->
    <div class="col-lg-5 px-1 mb-2 mb-lg-0">
      <div class="stat-card p-3 mb-0 h-100 d-flex flex-column justify-content-between" style="border-radius: 10px;">
        <div>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div class="d-flex align-items-center">
              <i class="fa fa-wrench text-primary mr-2" style="font-size: 15px;"></i>
              <h6 class="font-weight-bold text-dark mb-0" style="font-size: 13.5px;">Authorized Trade Skills</h6>
              <span class="badge badge-light border text-primary ml-2 font-weight-bold" id="skillsSelectedBadge" style="font-size: 11px;">
                <?= count($skillIds) ?> Active
              </span>
            </div>

            <!-- Quick Select Helpers -->
            <div class="d-flex gap-1" style="gap: 4px;">
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 font-weight-bold text-muted" onclick="selectAllSkills(true)" style="font-size: 11px;">All</button>
              <button type="button" class="btn btn-xs btn-light border py-0 px-2 font-weight-bold text-muted" onclick="selectAllSkills(false)" style="font-size: 11px;">Clear</button>
            </div>
          </div>

          <p class="text-muted small mb-2" style="font-size: 11.5px; line-height: 1.4;">
            Tap trade badges to authorize or revoke assignment eligibility for customer bookings:
          </p>

          <form method="POST" action="<?= View::url('/admin/staff/' . $user['id'] . '/skills') ?>" id="tradeSkillsForm">
            <?= View::csrfField() ?>

            <!-- Compact 2-Column Responsive Chip Grid -->
            <div class="skills-chip-grid mb-3">
              <?php foreach ($allCategories as $cat): ?>
                <?php $isChecked = in_array($cat['id'], $skillIds, true); ?>
                <label class="skill-chip <?= $isChecked ? 'active' : '' ?>" for="cat_<?= $cat['id'] ?>">
                  <input type="checkbox" name="skills[]" value="<?= $cat['id'] ?>" class="skill-checkbox" id="cat_<?= $cat['id'] ?>" <?= $isChecked ? 'checked' : '' ?> onchange="toggleSkillChip(this)">
                  <span class="skill-check-icon"><i class="fa fa-check"></i></span>
                  <span class="skill-title text-truncate"><?= View::e($cat['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-sm btn-brand btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 6px; font-size: 13px;">
              <i class="fa fa-save mr-1"></i>Save Trade Skills
            </button>
          </form>
        </div>

        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center text-muted small" style="font-size: 11px;">
          <span><i class="fa fa-info-circle mr-1"></i>Changes reflect instantly in dispatch queue.</span>
          <span class="font-weight-bold text-dark"><?= count($allCategories) ?> Total Trades</span>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN: ASSIGNED JOBS HISTORY -->
    <div class="col-lg-7 px-1">
      <div class="stat-card p-0 overflow-hidden mb-0 h-100 d-flex flex-column" style="border-radius: 10px;">
        <div class="p-2 px-3 bg-white border-bottom d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <i class="fa fa-briefcase text-muted mr-2" style="font-size: 15px;"></i>
            <h6 class="font-weight-bold text-dark mb-0" style="font-size: 13.5px;">Assigned Jobs History</h6>
            <span class="badge badge-light border text-dark ml-2 font-weight-bold" style="font-size: 11px;"><?= count($jobs) ?></span>
          </div>

          <?php if (!empty($jobs)): ?>
            <span class="text-muted small" style="font-size: 11px;">Showing latest assigned jobs</span>
          <?php endif; ?>
        </div>

        <div class="table-responsive flex-grow-1" style="max-height: 380px; overflow-y: auto;">
          <table class="table table-hover mb-0" style="font-size: 13px;">
            <thead class="bg-light sticky-top" style="z-index: 2;">
              <tr>
                <th style="padding: 9px 12px; font-size: 11px;">Booking #</th>
                <th style="padding: 9px 12px; font-size: 11px;">Customer</th>
                <th style="padding: 9px 12px; font-size: 11px;">Service</th>
                <th style="padding: 9px 12px; font-size: 11px;">Schedule</th>
                <th style="padding: 9px 12px; font-size: 11px;">Status</th>
                <th class="text-right" style="padding: 9px 12px; font-size: 11px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($jobs)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">
                    <div class="py-2">
                      <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2 text-muted" style="width: 44px; height: 44px;">
                        <i class="fa fa-briefcase fa-lg" style="opacity: 0.4;"></i>
                      </div>
                      <div class="font-weight-bold text-dark small">No jobs assigned to this technician yet.</div>
                      <div class="text-muted" style="font-size: 11.5px;">When dispatched from the bookings calendar, assigned jobs appear here.</div>
                    </div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($jobs as $j): ?>
                  <tr>
                    <td style="padding: 8px 12px;">
                      <a href="<?= View::url('/admin/bookings/' . $j['booking_id']) ?>" class="font-weight-bold text-dark">
                        #<?= View::e($j['booking_no']) ?>
                      </a>
                    </td>
                    <td style="padding: 8px 12px;">
                      <div class="font-weight-bold text-dark small"><?= View::e($j['customer_name']) ?></div>
                      <div class="text-muted" style="font-size: 11px;"><?= View::e($j['customer_phone']) ?></div>
                    </td>
                    <td style="padding: 8px 12px;">
                      <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11.5px;">
                        <?= View::e($j['service_name']) ?>
                      </span>
                    </td>
                    <td style="padding: 8px 12px;">
                      <div class="small font-weight-bold text-dark"><?= View::e($j['preferred_date'] ?? 'Flexible') ?></div>
                    </td>
                    <td style="padding: 8px 12px;">
                      <span class="badge badge-status-<?= View::e($j['status']) ?>" style="font-size: 10.5px; padding: 3px 7px;">
                        <?= ucfirst(str_replace('_', ' ', $j['status'])) ?>
                      </span>
                    </td>
                    <td class="text-right" style="padding: 8px 12px;">
                      <a href="<?= View::url('/admin/bookings/' . $j['booking_id']) ?>" class="btn btn-xs btn-brand-outline px-2 py-1 font-weight-bold" style="font-size: 11px;">
                        View &rarr;
                      </a>
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
</div>

<!-- STYLES FOR COMPACT SKILLS CHIPS & INTERACTIVE UI -->
<style>
.skills-chip-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 7px;
  max-height: 240px;
  overflow-y: auto;
  padding: 2px 1px;
}
@media (max-width: 575.98px) {
  .skills-chip-grid {
    grid-template-columns: 1fr;
  }
}
.skill-chip {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 6px 10px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.16s ease;
  user-select: none;
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0;
  min-height: 36px;
}
.skill-chip:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}
.skill-chip input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 0;
  height: 0;
}
.skill-chip .skill-check-icon {
  width: 17px;
  height: 17px;
  border-radius: 4px;
  border: 1.5px solid #94a3b8;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  color: transparent;
  background: #ffffff;
  transition: all 0.16s ease;
  flex-shrink: 0;
}
.skill-chip.active {
  background: #fff7ed;
  border-color: #f25b29;
  color: #c2410c;
  box-shadow: 0 1px 4px rgba(242, 91, 41, 0.12);
}
.skill-chip.active .skill-check-icon {
  background: #f25b29;
  border-color: #f25b29;
  color: #ffffff;
}
.skill-chip .skill-title {
  min-width: 0;
  flex: 1;
}
</style>

<script>
function toggleSkillChip(input) {
  const chip = input.closest('.skill-chip');
  if (chip) {
    if (input.checked) {
      chip.classList.add('active');
    } else {
      chip.classList.remove('active');
    }
  }
  updateSkillsBadge();
}

function selectAllSkills(select) {
  const form = document.getElementById('tradeSkillsForm');
  if (!form) return;
  const checkboxes = form.querySelectorAll('.skill-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = select;
    const chip = cb.closest('.skill-chip');
    if (chip) {
      if (select) chip.classList.add('active');
      else chip.classList.remove('active');
    }
  });
  updateSkillsBadge();
}

function updateSkillsBadge() {
  const form = document.getElementById('tradeSkillsForm');
  const badge = document.getElementById('skillsSelectedBadge');
  if (form && badge) {
    const checked = form.querySelectorAll('.skill-checkbox:checked').length;
    badge.textContent = checked + ' Active';
  }
}
</script>

