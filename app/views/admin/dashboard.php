<?php
use App\Core\View;
$summary = $summary ?? [];
$chartData = $chartData ?? ['labels' => [], 'data' => []];
$statusDist = $statusDist ?? ['assigned' => 0, 'accepted' => 0, 'in_progress' => 0, 'completed' => 0];
$recentBookings = $recentBookings ?? [];
$filter = $filter ?? 'all';
$isBusyMode = $isBusyMode ?? false;
?>

<div class="admin-dashboard-page">
  <!-- Top bar with Filter & Quick Action Buttons -->
  <div class="mb-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
      <div class="mb-2 mb-md-0">
        <h4 class="font-weight-bold mb-1 text-dark" style="font-size: clamp(17px, 3.8vw, 22px);">Operations & Dispatch Dashboard</h4>
        <p class="text-muted small mb-0">Real-time marketplace monitoring, service queues, and workforce status.</p>
      </div>

      <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-start justify-content-md-end" style="gap: 8px;">
        <!-- Date Filter Buttons -->
        <div class="btn-group" role="group" aria-label="Date Filters">
          <a href="<?= View::url('/admin?filter=all') ?>" class="btn btn-sm <?= $filter === 'all' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold px-2 px-sm-3" style="font-size: 12px;">
            All Time
          </a>
          <a href="<?= View::url('/admin?filter=month') ?>" class="btn btn-sm <?= $filter === 'month' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold px-2 px-sm-3" style="font-size: 12px;">
            This Month
          </a>
          <a href="<?= View::url('/admin?filter=today') ?>" class="btn btn-sm <?= $filter === 'today' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold px-2 px-sm-3" style="font-size: 12px;">
            Today
          </a>
        </div>

        <div class="d-inline-flex gap-2" style="gap: 6px;">
          <a href="<?= View::url('/admin/staff/create') ?>" class="btn btn-sm btn-outline-success font-weight-bold px-2 py-1" style="font-size: 12px; white-space: nowrap;">
            <i class="fa fa-user-plus mr-1"></i><span class="d-none d-sm-inline">Add </span>Staff
          </a>
          <a href="<?= View::url('/admin/payments') ?>" class="btn btn-sm btn-brand font-weight-bold px-2 py-1" style="font-size: 12px; white-space: nowrap;">
            <i class="fa fa-credit-card mr-1"></i><span class="d-none d-sm-inline">Record </span>Payment
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Admin Busy Mode / Auto-Dispatch Controller Card -->
  <div class="card border-0 shadow-sm mb-3 auto-dispatch-card" style="border-radius: 14px; background: linear-gradient(135deg, #07172c 0%, #0d2749 100%); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.08); overflow: hidden;">
    <div class="card-body p-3 p-md-3">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between" style="gap: 16px;">
        <!-- Left: Status & Info -->
        <div class="d-flex align-items-center" style="gap: 14px;">
          <div class="dispatch-mode-icon-wrap" id="dispatchModeIconWrap" style="width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: <?= !empty($isBusyMode) ? 'rgba(242, 91, 41, 0.2)' : 'rgba(255, 255, 255, 0.08)' ?>; color: <?= !empty($isBusyMode) ? '#f25b29' : '#94a3b8' ?>; font-size: 20px; transition: all 0.3s ease;">
            <i class="fa <?= !empty($isBusyMode) ? 'fa-bolt' : 'fa-user' ?>" id="dispatchModeIcon"></i>
          </div>
          <div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
              <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15.5px; letter-spacing: -0.2px;">Admin Busy Mode (Auto-Assign)</h5>
              <span class="badge" id="dispatchModeBadge" style="font-size: 11px; padding: 3px 9px; border-radius: 20px; font-weight: 700; <?= !empty($isBusyMode) ? 'background:#f25b29; color:#fff;' : 'background:rgba(255,255,255,0.15); color:#cbd5e1;' ?>">
                <i class="fa <?= !empty($isBusyMode) ? 'fa-flash' : 'fa-circle-o' ?> mr-1"></i>
                <span id="dispatchModeBadgeText"><?= !empty($isBusyMode) ? 'Busy Mode ON (Auto-Assign Active)' : 'Admin Active (Manual Dispatch)' ?></span>
              </span>
            </div>
            <p class="mb-0 small" style="opacity: 0.85; font-size: 12.5px; line-height: 1.4; margin-top: 3px;" id="dispatchModeDesc">
              <?= !empty($isBusyMode) 
                ? 'Admin is currently busy. Enquiries are automatically assigned to the first free technician of the matching category.' 
                : 'Turn this toggle ON when busy. All pending and incoming customer enquiries will automatically be assigned to available technicians of the same category.' ?>
            </p>
          </div>
        </div>

        <!-- Right: The Toggle Control -->
        <div class="d-flex align-items-center justify-content-between w-100 w-md-auto pl-md-3" style="gap: 16px;">
          <div class="text-right d-none d-sm-block">
            <span class="d-block font-weight-bold text-white small" id="dispatchToggleLabel"><?= !empty($isBusyMode) ? 'Busy Mode ON' : 'Busy Mode OFF' ?></span>
            <small class="text-muted" style="font-size: 11px; color: #94a3b8 !important;" id="dispatchToggleSub"><?= !empty($isBusyMode) ? 'Auto-assigning' : 'Click to activate' ?></small>
          </div>

          <!-- Switch Slider -->
          <label class="refixel-busy-switch mb-0" title="Click to toggle Admin Busy Mode">
            <input type="checkbox" id="adminBusyToggle" <?= !empty($isBusyMode) ? 'checked' : '' ?>>
            <span class="refixel-busy-slider"></span>
          </label>
        </div>
      </div>

      <!-- Live Auto-Assignment Toast / Results Strip -->
      <div id="dispatchLiveStatus" class="mt-2 pt-2 border-top small" style="display: none; border-color: rgba(255,255,255,0.12) !important; font-size: 12px; color: #e2e8f0;">
        <i class="fa fa-info-circle text-warning mr-1"></i> <span id="dispatchLiveText"></span>
      </div>
    </div>
  </div>

  <!-- 8-Card KPI Matrix (2-col on Mobile, 2-col on Tablet, 4-col on Desktop) -->
  <div class="row mx-n1 mx-sm-n2 mb-3">
    <!-- 1. New Enquiries -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #f59e0b !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-warning text-truncate pr-1" title="New Enquiries">New Enquiries</div>
          <div class="stat-icon-circle" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
            <i class="fa fa-inbox"></i>
          </div>
        </div>
        <div class="stat-value text-dark" style="font-size: clamp(18px, 4.2vw, 24px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;">
          <?= (int)($summary['enquiries_count'] ?? 0) ?>
        </div>
        <a href="<?= View::url('/admin/enquiries') ?>" class="small font-weight-bold text-warning text-truncate d-inline-block" style="font-size: 11px; max-width: 100%;">
          Review enquiries &rarr;
        </a>
      </div>
    </div>

    <!-- 2. Assigned Jobs -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #0284c7 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-info text-truncate pr-1" title="Assigned Jobs">Assigned Jobs</div>
          <div class="stat-icon-circle" style="background: rgba(2, 132, 199, 0.12); color: #0284c7;">
            <i class="fa fa-briefcase"></i>
          </div>
        </div>
        <div class="stat-value text-dark" style="font-size: clamp(18px, 4.2vw, 24px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;">
          <?= (int)($summary['assigned_jobs'] ?? 0) ?>
        </div>
        <a href="<?= View::url('/admin/bookings?status=assigned') ?>" class="small font-weight-bold text-info text-truncate d-inline-block" style="font-size: 11px; max-width: 100%;">
          View assigned &rarr;
        </a>
      </div>
    </div>

    <!-- 3. Active / In Progress -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #3b82f6 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-primary text-truncate pr-1" title="Active Field Jobs">Active Field Jobs</div>
          <div class="stat-icon-circle" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
            <i class="fa fa-cogs"></i>
          </div>
        </div>
        <div class="stat-value text-dark" style="font-size: clamp(18px, 4.2vw, 24px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;">
          <?= (int)($summary['active_jobs'] ?? 0) ?>
        </div>
        <a href="<?= View::url('/admin/bookings?status=in_progress') ?>" class="small font-weight-bold text-primary text-truncate d-inline-block" style="font-size: 11px; max-width: 100%;">
          Track active &rarr;
        </a>
      </div>
    </div>

    <!-- 4. Completed Jobs -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #10b981 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-success text-truncate pr-1" title="Completed Jobs">Completed Jobs</div>
          <div class="stat-icon-circle" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
            <i class="fa fa-check-circle"></i>
          </div>
        </div>
        <div class="stat-value text-dark" style="font-size: clamp(18px, 4.2vw, 24px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;">
          <?= (int)($summary['completed_jobs'] ?? 0) ?>
        </div>
        <a href="<?= View::url('/admin/bookings?status=completed') ?>" class="small font-weight-bold text-success text-truncate d-inline-block" style="font-size: 11px; max-width: 100%;">
          Completed list &rarr;
        </a>
      </div>
    </div>

    <!-- 5. Revenue Collected -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #059669 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-success text-truncate pr-1" title="Revenue Collected">Revenue</div>
          <div class="stat-icon-circle" style="background: rgba(5, 150, 105, 0.12); color: #059669;">
            <i class="fa fa-money"></i>
          </div>
        </div>
        <div class="stat-value text-dark text-truncate" style="font-size: clamp(16px, 3.8vw, 22px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;" title="₹<?= number_format((float)($summary['total_revenue'] ?? 0), 2) ?>">
          ₹<?= number_format((float)($summary['total_revenue'] ?? 0), 0) ?>
        </div>
        <a href="<?= View::url('/admin/payments') ?>" class="small font-weight-bold text-success text-truncate d-inline-block" style="font-size: 11px; max-width: 100%;">
          View receipts &rarr;
        </a>
      </div>
    </div>

    <!-- 6. Pending Payments -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #ef4444 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-danger text-truncate pr-1" title="Pending Payments">Pending Due</div>
          <div class="stat-icon-circle" style="background: rgba(239, 68, 68, 0.12); color: #ef4444;">
            <i class="fa fa-clock-o"></i>
          </div>
        </div>
        <div class="stat-value text-danger text-truncate" style="font-size: clamp(16px, 3.8vw, 22px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;" title="₹<?= number_format((float)($summary['pending_payments'] ?? 0), 2) ?>">
          ₹<?= number_format((float)($summary['pending_payments'] ?? 0), 0) ?>
        </div>
        <a href="<?= View::url('/admin/payments') ?>" class="small font-weight-bold text-danger text-truncate d-inline-block" style="font-size: 11px; max-width: 100%;">
          Collect payments &rarr;
        </a>
      </div>
    </div>

    <!-- 7. Active Staff -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #6366f1 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-truncate pr-1" style="color: #6366f1;" title="Technicians">Technicians</div>
          <div class="stat-icon-circle" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
            <i class="fa fa-users"></i>
          </div>
        </div>
        <div class="stat-value text-dark" style="font-size: clamp(18px, 4.2vw, 24px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;">
          <?= (int)($summary['staff_count'] ?? 0) ?>
        </div>
        <a href="<?= View::url('/admin/staff') ?>" class="small font-weight-bold text-truncate d-inline-block" style="color: #6366f1; font-size: 11px; max-width: 100%;">
          Manage team &rarr;
        </a>
      </div>
    </div>

    <!-- 8. Active Services -->
    <div class="col-6 col-md-6 col-xl-3 px-1 px-sm-2 mb-2 mb-sm-3">
      <div class="dash-stat-card" style="border-left: 4px solid #14b8a6 !important;">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="stat-label text-truncate pr-1" style="color: #0d9488;" title="Active Services">Services</div>
          <div class="stat-icon-circle" style="background: rgba(20, 184, 166, 0.12); color: #0d9488;">
            <i class="fa fa-wrench"></i>
          </div>
        </div>
        <div class="stat-value text-dark" style="font-size: clamp(18px, 4.2vw, 24px); font-weight: 800; line-height: 1.1; margin: 2px 0 4px;">
          <?= (int)($summary['services_count'] ?? 0) ?>
        </div>
        <a href="<?= View::url('/admin/services') ?>" class="small font-weight-bold text-truncate d-inline-block" style="color: #0d9488; font-size: 11px; max-width: 100%;">
          Catalog &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- Interactive Charts Row -->
  <div class="row mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-12 col-lg-8 mb-3 mb-lg-0">
      <div class="stat-card h-100 mb-0 p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="font-weight-bold text-dark mb-0" style="font-size: 13.5px;"><i class="fa fa-line-chart text-success mr-2"></i>Revenue Collection Trend</h6>
          <span class="badge badge-light border text-muted" style="font-size: 11px;">Last 6 Months</span>
        </div>
        <div style="height: 220px; position: relative;">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Job Status Distribution Doughnut -->
    <div class="col-12 col-lg-4">
      <div class="stat-card h-100 mb-0 p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="font-weight-bold text-dark mb-0" style="font-size: 13.5px;"><i class="fa fa-pie-chart text-primary mr-2"></i>Job Distribution</h6>
          <span class="badge badge-light border text-muted" style="font-size: 11px;">By Status</span>
        </div>
        <div style="height: 220px; position: relative;">
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Bookings & Dispatch Table -->
  <div class="stat-card p-0 overflow-hidden mb-4">
    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center bg-white gap-2">
      <div>
        <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 14px;"><i class="fa fa-clock-o text-muted mr-2"></i>Recent Bookings & Assignment Queue</h6>
      </div>
      <a href="<?= View::url('/admin/bookings') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold mt-1 mt-sm-0" style="font-size: 12px;">
        View All Bookings (<?= count($recentBookings) ?>+)
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0 admin-table-min">
        <thead>
          <tr class="text-nowrap">
            <th>Booking #</th>
            <th>Customer</th>
            <th>Service Requested</th>
            <th>Schedule</th>
            <th>Assigned Technician</th>
            <th>Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentBookings)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">No bookings found in database.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentBookings as $b): ?>
              <?php 
                $bDue = (float)($b['due_amount'] ?? 0);
                $isBHigh = (($b['priority'] ?? '') === 'high' || ($b['payment_type'] ?? '') === 'online');
                $bTotal = (float)($b['total_amount'] ?? $b['starting_price'] ?? 0);
                $bAdv = (float)($b['advance_amount'] ?? 0);
              ?>
              <tr>
                <td>
                  <a href="<?= View::url('/admin/bookings/' . $b['id']) ?>" class="font-weight-bold text-dark">
                    #<?= View::e($b['booking_no']) ?>
                  </a>
                  <div class="mt-1">
                    <?php if ($isBHigh && $bDue <= 0): ?>
                      <span class="badge badge-success font-weight-bold" style="font-size: 10px; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                        <i class="fa fa-bolt mr-1"></i>High
                      </span>
                    <?php else: ?>
                      <span class="badge font-weight-bold" style="font-size: 10px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                        <i class="fa fa-clock-o mr-1"></i>Medium (COD)
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div class="font-weight-bold text-dark"><?= View::e($b['name']) ?></div>
                  <div class="text-muted small"><?= View::e($b['phone']) ?></div>
                </td>
                <td>
                  <div class="font-weight-bold text-dark small"><?= View::e($b['service_name']) ?></div>
                  <?php if ($bDue > 0): ?>
                    <div class="small font-weight-bold text-dark">Total: ₹<?= number_format($bTotal > 0 ? $bTotal : 899, 0) ?></div>
                    <div class="small" style="font-size: 11px;">
                      <span class="text-success font-weight-bold">Adv: ₹<?= number_format($bAdv > 0 ? $bAdv : 49, 0) ?></span> &bull; 
                      <span class="text-danger font-weight-bold">Due: ₹<?= number_format($bDue, 0) ?></span>
                    </div>
                  <?php else: ?>
                    <div class="text-success small font-weight-bold">₹<?= number_format($bTotal > 0 ? $bTotal : (float)($b['starting_price'] ?? 0), 0) ?> (Online Full)</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="small font-weight-bold text-dark"><?= View::e($b['preferred_date'] ?? 'Flexible') ?></div>
                  <div class="text-muted small"><?= View::e($b['preferred_time'] ?? '') ?></div>
                </td>
                <td>
                  <?php if (!empty($b['staff_name'])): ?>
                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                      <i class="fa fa-user mr-1 text-primary"></i><?= View::e($b['staff_name']) ?>
                    </span>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark px-2 py-1">
                      <i class="fa fa-exclamation-triangle mr-1"></i>Unassigned
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-status-<?= View::e($b['status']) ?>">
                    <?= ucfirst(str_replace('_', ' ', $b['status'])) ?>
                  </span>
                </td>
                <td class="text-right">
                  <a href="<?= View::url('/admin/bookings/' . $b['id']) ?>" class="btn btn-sm btn-brand-outline py-1 px-2 font-weight-bold">
                    Manage &rarr;
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Revenue Chart
  const revCtx = document.getElementById('revenueChart');
  if (revCtx) {
    new Chart(revCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($chartData['labels'] ?? []) ?>,
        datasets: [{
          label: 'Revenue (₹)',
          data: <?= json_encode($chartData['data'] ?? []) ?>,
          borderColor: '#f25b29',
          backgroundColor: 'rgba(15, 110, 86, 0.1)',
          fill: true,
          tension: 0.3,
          borderWidth: 2,
          pointBackgroundColor: '#f25b29',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#eef2f0' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  // Status Distribution Chart
  const statusCtx = document.getElementById('statusChart');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Assigned', 'Accepted', 'In Progress', 'Completed'],
        datasets: [{
          data: [
            <?= (int)($statusDist['assigned'] ?? 0) ?>,
            <?= (int)($statusDist['accepted'] ?? 0) ?>,
            <?= (int)($statusDist['in_progress'] ?? 0) ?>,
            <?= (int)($statusDist['completed'] ?? 0) ?>
          ],
          backgroundColor: ['#0284c7', '#8b5cf6', '#3b82f6', '#10b981'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
        }
      }
    });
  }

  // Admin Busy Mode Toggle Handler
  const busyToggle = document.getElementById('adminBusyToggle');
  const modeBadge = document.getElementById('dispatchModeBadge');
  const modeIconWrap = document.getElementById('dispatchModeIconWrap');
  const modeIcon = document.getElementById('dispatchModeIcon');
  const modeDesc = document.getElementById('dispatchModeDesc');
  const toggleLabel = document.getElementById('dispatchToggleLabel');
  const toggleSub = document.getElementById('dispatchToggleSub');
  const liveStatus = document.getElementById('dispatchLiveStatus');
  const liveText = document.getElementById('dispatchLiveText');

  if (busyToggle) {
    busyToggle.addEventListener('change', function () {
      const isChecked = this.checked;
      busyToggle.disabled = true;

      if (liveStatus) {
        liveStatus.style.display = 'block';
        liveText.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Updating Admin Busy status & scanning enquiries...';
      }

      fetch('<?= View::url('/admin/api/toggle-busy-mode') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'enabled=' + (isChecked ? '1' : '0')
      })
      .then(res => res.json())
      .then(data => {
        busyToggle.disabled = false;
        if (data.success) {
          const isBusy = !!data.busy_mode;
          busyToggle.checked = isBusy;

          if (isBusy) {
            modeIconWrap.style.background = 'rgba(242, 91, 41, 0.2)';
            modeIconWrap.style.color = '#f25b29';
            modeIcon.className = 'fa fa-bolt';
            modeBadge.style.background = '#f25b29';
            modeBadge.style.color = '#ffffff';
            modeBadge.innerHTML = '<i class="fa fa-flash mr-1"></i> Busy Mode ON (Auto-Assign Active)';
            modeDesc.textContent = 'Admin is currently busy. Enquiries are automatically assigned to the first free technician of the matching category.';
            if (toggleLabel) toggleLabel.textContent = 'Busy Mode ON';
            if (toggleSub) toggleSub.textContent = 'Auto-assigning';

            if (data.assigned_count > 0) {
              const assignedDetails = (data.assigned || []).map(a => `${a.booking_no} &rarr; <strong>${a.technician}</strong> (${a.category_name})`).join(', ');
              liveText.innerHTML = `<span class="text-success"><i class="fa fa-check-circle mr-1"></i> <strong>${data.assigned_count}</strong> enquiry(s) auto-assigned: ${assignedDetails}</span>`;
              setTimeout(() => { window.location.reload(); }, 2500);
            } else {
              liveText.innerHTML = '<span class="text-info"><i class="fa fa-check-circle mr-1"></i> Admin Busy Mode is active. New enquiries will be auto-assigned to available technicians.</span>';
            }
          } else {
            modeIconWrap.style.background = 'rgba(255, 255, 255, 0.08)';
            modeIconWrap.style.color = '#94a3b8';
            modeIcon.className = 'fa fa-user';
            modeBadge.style.background = 'rgba(255,255,255,0.15)';
            modeBadge.style.color = '#cbd5e1';
            modeBadge.innerHTML = '<i class="fa fa-circle-o mr-1"></i> Admin Active (Manual Dispatch)';
            modeDesc.textContent = 'Turn this toggle ON when busy. All pending and incoming customer enquiries will automatically be assigned to available technicians of the same category.';
            if (toggleLabel) toggleLabel.textContent = 'Busy Mode OFF';
            if (toggleSub) toggleSub.textContent = 'Click to activate';

            liveText.innerHTML = '<span class="text-light"><i class="fa fa-info-circle mr-1"></i> Busy mode deactivated. Enquiries will be dispatched manually.</span>';
            setTimeout(() => { if (liveStatus) liveStatus.style.display = 'none'; }, 3500);
          }
        } else {
          alert('Failed to update Busy Mode. Please try again.');
          busyToggle.checked = !isChecked;
        }
      })
      .catch(err => {
        console.error(err);
        busyToggle.disabled = false;
        busyToggle.checked = !isChecked;
        if (liveStatus) {
          liveText.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-triangle mr-1"></i> Network error updating Busy Mode.</span>';
        }
      });
    });
  }
});
</script>

<style>
/* Modern Switch Toggle styling */
.refixel-busy-switch {
  position: relative;
  display: inline-block;
  width: 58px;
  height: 30px;
  flex-shrink: 0;
  cursor: pointer;
  margin: 0;
}
.refixel-busy-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.refixel-busy-slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(255, 255, 255, 0.2);
  transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 30px;
  border: 1.5px solid rgba(255, 255, 255, 0.35);
}
.refixel-busy-slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 3px;
  bottom: 2.5px;
  background-color: #ffffff;
  transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 50%;
  box-shadow: 0 2px 5px rgba(0,0,0,0.35);
}
.refixel-busy-switch input:checked + .refixel-busy-slider {
  background-color: #f25b29;
  border-color: #f25b29;
  box-shadow: 0 0 12px rgba(242, 91, 41, 0.45);
}
.refixel-busy-switch input:checked + .refixel-busy-slider:before {
  transform: translateX(27px);
}
.refixel-busy-switch input:disabled + .refixel-busy-slider {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>

