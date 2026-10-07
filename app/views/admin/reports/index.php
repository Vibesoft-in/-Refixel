<?php
use App\Core\View;
$summary = $summary ?? [];
$chartData = $chartData ?? ['labels' => [], 'data' => []];
$statusDist = $statusDist ?? [];
$topServices = $topServices ?? [];
$staffLeaderboard = $staffLeaderboard ?? [];
?>

<div class="admin-reports-page">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Analytics & Operations Reports</h4>
      <p class="text-muted small mb-0">Marketplace performance insights, workforce metrics, and revenue analytics.</p>
    </div>

    <div>
      <button onclick="window.print()" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fa fa-print mr-1"></i>Print Report
      </button>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="row mb-4">
    <div class="col-lg-8 mb-3 mb-lg-0">
      <div class="stat-card h-100 mb-0">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-line-chart text-success mr-2"></i>Revenue Trend (Last 6 Months)</h6>
        <div style="height: 250px; position: relative;">
          <canvas id="revenueAnalyticsChart"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="stat-card h-100 mb-0">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-pie-chart text-primary mr-2"></i>Field Work Pipeline</h6>
        <div style="height: 250px; position: relative;">
          <canvas id="pipelineChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Top Services by Demand -->
    <div class="col-lg-6 mb-4">
      <div class="stat-card p-0 overflow-hidden h-100 mb-0">
        <div class="p-3 bg-white border-bottom">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-star text-warning mr-2"></i>Top Services by Booking Demand</h6>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Service</th>
                <th>Category</th>
                <th>Bookings</th>
                <th class="text-right">Starting Price</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($topServices)): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">No data available.</td></tr>
              <?php else: ?>
                <?php foreach ($topServices as $ts): ?>
                  <tr>
                    <td><strong><?= View::e($ts['name']) ?></strong></td>
                    <td><span class="badge badge-light border"><?= View::e($ts['category_name']) ?></span></td>
                    <td><span class="badge badge-primary"><?= (int)$ts['booking_count'] ?></span></td>
                    <td class="text-right text-success font-weight-bold">₹<?= number_format((float)$ts['starting_price'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Staff Performance Leaderboard -->
    <div class="col-lg-6 mb-4">
      <div class="stat-card p-0 overflow-hidden h-100 mb-0">
        <div class="p-3 bg-white border-bottom">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-trophy text-success mr-2"></i>Technician Field Scorecard</h6>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Technician</th>
                <th>Rating</th>
                <th>Completed</th>
                <th class="text-right">Reliability</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($staffLeaderboard)): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">No staff activity data yet.</td></tr>
              <?php else: ?>
                <?php foreach ($staffLeaderboard as $sl): ?>
                  <?php
                    $rate = ($sl['total_jobs'] > 0) ? round(($sl['completed_jobs'] / $sl['total_jobs']) * 100) : 100;
                  ?>
                  <tr>
                    <td>
                      <strong><?= View::e($sl['name']) ?></strong>
                      <div class="text-muted small"><?= View::e($sl['phone']) ?></div>
                    </td>
                    <td>
                      <span class="badge badge-warning text-dark font-weight-bold">
                        ★ <?= number_format((float)($sl['rating_avg'] ?? 5.0), 1) ?>
                      </span>
                    </td>
                    <td>
                      <strong><?= (int)$sl['completed_jobs'] ?></strong> / <?= (int)$sl['total_jobs'] ?>
                    </td>
                    <td class="text-right">
                      <span class="badge <?= $rate >= 80 ? 'badge-success' : 'badge-warning' ?>"><?= $rate ?>%</span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const revCtx = document.getElementById('revenueAnalyticsChart');
  if (revCtx) {
    new Chart(revCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartData['labels'] ?? []) ?>,
        datasets: [{
          label: 'Collections (₹)',
          data: <?= json_encode($chartData['data'] ?? []) ?>,
          backgroundColor: '#f25b29',
          borderRadius: 4
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

  const pipeCtx = document.getElementById('pipelineChart');
  if (pipeCtx) {
    new Chart(pipeCtx, {
      type: 'pie',
      data: {
        labels: ['Assigned', 'Accepted', 'In Progress', 'Completed'],
        datasets: [{
          data: [
            <?= (int)($statusDist['assigned'] ?? 0) ?>,
            <?= (int)($statusDist['accepted'] ?? 0) ?>,
            <?= (int)($statusDist['in_progress'] ?? 0) ?>,
            <?= (int)($statusDist['completed'] ?? 0) ?>
          ],
          backgroundColor: ['#0284c7', '#8b5cf6', '#3b82f6', '#10b981']
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

