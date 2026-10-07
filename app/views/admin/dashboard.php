<?php
use App\Core\View;
$summary = $summary ?? [];
$chartData = $chartData ?? ['labels' => [], 'data' => []];
$statusDist = $statusDist ?? ['assigned' => 0, 'accepted' => 0, 'in_progress' => 0, 'completed' => 0];
$recentBookings = $recentBookings ?? [];
$filter = $filter ?? 'all';
?>

<div class="admin-dashboard-page">
  <!-- Top bar with Filter & Quick Action Buttons -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Operations & Dispatch Dashboard</h4>
      <p class="text-muted small mb-0">Real-time marketplace monitoring, service queues, and workforce status.</p>
    </div>

    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
      <!-- Date Filter Buttons -->
      <div class="btn-group mr-2" role="group">
        <a href="<?= View::url('/admin?filter=all') ?>" class="btn btn-sm <?= $filter === 'all' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold">
          All Time
        </a>
        <a href="<?= View::url('/admin?filter=month') ?>" class="btn btn-sm <?= $filter === 'month' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold">
          This Month
        </a>
        <a href="<?= View::url('/admin?filter=today') ?>" class="btn btn-sm <?= $filter === 'today' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold">
          Today
        </a>
      </div>

      <a href="<?= View::url('/admin/staff/create') ?>" class="btn btn-sm btn-outline-success font-weight-bold mr-2">
        <i class="fa fa-user-plus mr-1"></i>Add Staff
      </a>
      <a href="<?= View::url('/admin/payments') ?>" class="btn btn-sm btn-brand font-weight-bold">
        <i class="fa fa-credit-card mr-1"></i>Record Payment
      </a>
    </div>
  </div>

  <!-- 8-Card KPI Matrix -->
  <div class="row">
    <!-- 1. New Enquiries -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #f59e0b !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label text-warning">New Enquiries</div>
            <div class="stat-value text-dark"><?= (int)($summary['enquiries_count'] ?? 0) ?></div>
            <a href="<?= View::url('/admin/enquiries') ?>" class="small font-weight-bold text-warning">Review enquiries &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle text-warning">
            <i class="fa fa-inbox fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Assigned Jobs -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #0284c7 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label text-info">Assigned Jobs</div>
            <div class="stat-value text-dark"><?= (int)($summary['assigned_jobs'] ?? 0) ?></div>
            <a href="<?= View::url('/admin/bookings?status=assigned') ?>" class="small font-weight-bold text-info">View assigned &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle text-info">
            <i class="fa fa-briefcase fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Active / In Progress -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #3b82f6 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label text-primary">Active Field Jobs</div>
            <div class="stat-value text-dark"><?= (int)($summary['active_jobs'] ?? 0) ?></div>
            <a href="<?= View::url('/admin/bookings?status=in_progress') ?>" class="small font-weight-bold text-primary">Track active &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle text-primary">
            <i class="fa fa-cogs fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Completed Jobs -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #10b981 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label text-success">Completed Jobs</div>
            <div class="stat-value text-dark"><?= (int)($summary['completed_jobs'] ?? 0) ?></div>
            <a href="<?= View::url('/admin/bookings?status=completed') ?>" class="small font-weight-bold text-success">Completed list &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle text-success">
            <i class="fa fa-check-circle fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Revenue Collected -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #059669 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label text-success">Revenue Collected</div>
            <div class="stat-value text-dark" style="font-size: 24px;">₹<?= number_format((float)($summary['total_revenue'] ?? 0), 2) ?></div>
            <a href="<?= View::url('/admin/payments') ?>" class="small font-weight-bold text-success">View receipts &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle text-success">
            <i class="fa fa-money fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. Pending Payments -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #ef4444 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label text-danger">Pending Payments</div>
            <div class="stat-value text-danger" style="font-size: 24px;">₹<?= number_format((float)($summary['pending_payments'] ?? 0), 2) ?></div>
            <a href="<?= View::url('/admin/payments') ?>" class="small font-weight-bold text-danger">Collect payments &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle text-danger">
            <i class="fa fa-clock-o fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. Active Staff -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #6366f1 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label" style="color: #6366f1;">Technicians Online</div>
            <div class="stat-value text-dark"><?= (int)($summary['staff_count'] ?? 0) ?></div>
            <a href="<?= View::url('/admin/staff') ?>" class="small font-weight-bold" style="color: #6366f1;">Manage technicians &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle" style="color: #6366f1;">
            <i class="fa fa-users fa-2x"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- 8. Active Services -->
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left: 4px solid #14b8a6 !important;">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="stat-label" style="color: #0d9488;">Active Services</div>
            <div class="stat-value text-dark"><?= (int)($summary['services_count'] ?? 0) ?></div>
            <a href="<?= View::url('/admin/services') ?>" class="small font-weight-bold" style="color: #0d9488;">Catalog services &rarr;</a>
          </div>
          <div class="p-3 bg-light rounded-circle" style="color: #0d9488;">
            <i class="fa fa-wrench fa-2x"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Interactive Charts Row -->
  <div class="row mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8 mb-3 mb-lg-0">
      <div class="stat-card h-100 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-line-chart text-success mr-2"></i>Revenue Collection Trend</h6>
          <span class="badge badge-light border text-muted">Last 6 Months</span>
        </div>
        <div style="height: 240px; position: relative;">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Job Status Distribution Doughnut -->
    <div class="col-lg-4">
      <div class="stat-card h-100 mb-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-pie-chart text-primary mr-2"></i>Job Distribution</h6>
          <span class="badge badge-light border text-muted">By Status</span>
        </div>
        <div style="height: 240px; position: relative;">
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Bookings & Dispatch Table -->
  <div class="stat-card p-0 overflow-hidden mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
      <div>
        <h6 class="font-weight-bold mb-0 text-dark"><i class="fa fa-clock-o text-muted mr-2"></i>Recent Bookings & Assignment Queue</h6>
      </div>
      <a href="<?= View::url('/admin/bookings') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold">
        View All Bookings (<?= count($recentBookings) ?>+)
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
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
              <tr>
                <td>
                  <a href="<?= View::url('/admin/bookings/' . $b['id']) ?>" class="font-weight-bold text-dark">
                    #<?= View::e($b['booking_no']) ?>
                  </a>
                </td>
                <td>
                  <div class="font-weight-bold text-dark"><?= View::e($b['name']) ?></div>
                  <div class="text-muted small"><?= View::e($b['phone']) ?></div>
                </td>
                <td>
                  <div class="font-weight-bold text-dark small"><?= View::e($b['service_name']) ?></div>
                  <div class="text-success small font-weight-bold">₹<?= number_format((float)($b['starting_price'] ?? 0), 0) ?></div>
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
});
</script>

