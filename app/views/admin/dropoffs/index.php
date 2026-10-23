<?php
use App\Core\View;

$dropoffs      = $dropoffs ?? [];
$stats         = $stats ?? ['total' => 0, 'pending' => 0, 'recovered' => 0, 'failed' => 0, 'lost_value' => 0, 'recovery_rate' => 0];
$currentFilter = $currentFilter ?? 'all';
$search        = $search ?? '';
?>

<div class="admin-dropoffs-page">
  <!-- Top Header with Responsive Actions -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 pb-3 border-bottom" style="gap: 12px;">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark" style="font-size: 20px;">
        <i class="fa fa-exclamation-triangle text-warning mr-2"></i>Abandoned Bookings & Payment Drop-offs
      </h4>
      <p class="text-muted small mb-0">
        Monitor customers who left halfway during booking, dismissed Razorpay online payments, or faced checkout issues. Follow up via WhatsApp/Call to recover lost revenue.
      </p>
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
      <?php if (!empty($dropoffs)): ?>
        <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalDeleteAllDropoffs">
          <i class="fa fa-trash mr-1"></i> Clear All Drop-offs (<?= count($dropoffs) ?>)
        </button>
      <?php endif; ?>
      <a href="<?= View::url('/admin/bookings') ?>" class="btn btn-sm btn-light border font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i> Back to Bookings
      </a>
    </div>
  </div>

  <!-- KPI Metrics Cards Row -->
  <div class="row mb-4">
    <!-- Card 1: Total Drop-offs -->
    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #f59e0b;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">TOTAL DROP-OFFS</span>
          <div class="stat-icon-circle" style="background: rgba(245, 158, 11, 0.15); color: #d97706;">
            <i class="fa fa-shopping-cart"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-dark mb-0"><?= (int)$stats['total'] ?></h3>
        <small class="text-muted">Customers left checkout</small>
      </div>
    </div>

    <!-- Card 2: Unrecovered / Pending -->
    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #ef4444;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">UNRECOVERED LEADS</span>
          <div class="stat-icon-circle" style="background: rgba(239, 68, 68, 0.15); color: #dc2626;">
            <i class="fa fa-phone"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-danger mb-0"><?= (int)$stats['pending'] ?></h3>
        <small class="text-danger font-weight-bold">Immediate Follow-up Needed</small>
      </div>
    </div>

    <!-- Card 3: Potential Lost Value -->
    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #6366f1;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">POTENTIAL VALUE</span>
          <div class="stat-icon-circle" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5;">
            <i class="fa fa-inr"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-dark mb-0">₹<?= number_format((float)$stats['lost_value'], 0) ?></h3>
        <small class="text-muted">Recoverable booking revenue</small>
      </div>
    </div>

    <!-- Card 4: Recovered Bookings -->
    <div class="col-6 col-lg-3 mb-3 mb-lg-0">
      <div class="dash-stat-card p-3" style="border-left: 4px solid #10b981;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">RECOVERED LEADS</span>
          <div class="stat-icon-circle" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
            <i class="fa fa-check-circle"></i>
          </div>
        </div>
        <h3 class="font-weight-bold text-success mb-0"><?= (int)$stats['recovered'] ?></h3>
        <small class="text-success font-weight-bold"><?= $stats['recovery_rate'] ?>% Recovery Rate</small>
      </div>
    </div>
  </div>

  <!-- Filter Pills & Search Bar -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
    <div class="card-body p-3">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="gap: 12px;">
        <!-- Filters -->
        <div class="d-flex overflow-auto pb-1" style="gap: 8px;">
          <a href="<?= View::url('/admin/dropoffs?filter=all' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
             class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentFilter === 'all' ? 'btn-brand' : 'btn-light border text-muted' ?>">
            All Drop-offs (<?= (int)$stats['total'] ?>)
          </a>
          <a href="<?= View::url('/admin/dropoffs?filter=unrecovered' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
             class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentFilter === 'unrecovered' ? 'btn-danger text-white' : 'btn-light border text-muted' ?>">
            <i class="fa fa-clock-o mr-1"></i>Unrecovered (<?= (int)$stats['pending'] ?>)
          </a>
          <a href="<?= View::url('/admin/dropoffs?filter=payment_failed' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
             class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentFilter === 'payment_failed' ? 'btn-warning text-dark' : 'btn-light border text-muted' ?>">
            <i class="fa fa-times-circle mr-1"></i>Payment Issues (<?= (int)$stats['failed'] ?>)
          </a>
          <a href="<?= View::url('/admin/dropoffs?filter=recovered' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
             class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentFilter === 'recovered' ? 'btn-success text-white' : 'btn-light border text-muted' ?>">
            <i class="fa fa-check-circle mr-1"></i>Recovered (<?= (int)$stats['recovered'] ?>)
          </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="<?= View::url('/admin/dropoffs') ?>" class="form-inline m-0">
          <input type="hidden" name="filter" value="<?= View::e($currentFilter) ?>">
          <div class="input-group input-group-sm w-100" style="max-width: 280px;">
            <input type="text" name="q" class="form-control" placeholder="Search customer, phone..." value="<?= View::e($search) ?>">
            <div class="input-group-append">
              <button class="btn btn-brand" type="submit"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Drop-offs Table -->
  <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle admin-table-min-lg">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">Lead / Time</th>
            <th style="white-space: nowrap;">Customer Details</th>
            <th style="white-space: nowrap;">Service & Package</th>
            <th style="white-space: nowrap;">Amount</th>
            <th style="white-space: nowrap;">Problem Faced / Drop-off Stage</th>
            <th style="white-space: nowrap;">Status</th>
            <th class="text-right" style="white-space: nowrap;">Instant Recovery Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($dropoffs)): ?>
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="text-muted mb-2"><i class="fa fa-check-circle-o fa-3x text-success" style="opacity: 0.6;"></i></div>
                <h6 class="font-weight-bold text-dark">No Abandoned Checkouts Found</h6>
                <p class="text-muted small mb-0">Every customer who initiated a booking completed it smoothly, or no records matched your filter.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($dropoffs as $row): ?>
              <?php
                $cleanPhone = preg_replace('/\D/', '', (string)($row['phone'] ?? ''));
                if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                
                $custName = !empty($row['name']) ? $row['name'] : 'Customer';
                $svcName  = !empty($row['service_name']) ? $row['service_name'] : 'Home Service';
                $amtText  = '₹' . number_format((float)($row['amount'] ?? 0), 0);
                
                // Pre-filled personalized WhatsApp recovery pitch
                $waText = "Hi {$custName}! We noticed you started booking {$svcName} ({$amtText}) on Refixel, but the booking wasn't completed. Did you face any issue with online payment? We can assist you right now or switch your booking to Pay After Service (Cash/UPI upon work completion). Let us know!";
                $waUrl  = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($waText);

                $isRecovered = !empty($row['is_recovered']);
                $stepLabel = 'Payment Dropped';
                $stepBadge = 'badge-warning';
                $stepIcon  = 'fa-exclamation-triangle';
                
                if (stripos($row['step'] ?? '', 'fail') !== false || stripos($row['step'] ?? '', 'error') !== false) {
                    $stepLabel = 'Payment Failed';
                    $stepBadge = 'badge-danger';
                    $stepIcon  = 'fa-times-circle';
                } elseif (stripos($row['step'] ?? '', 'modal_dismissed') !== false) {
                    $stepLabel = 'Payment Window Closed';
                    $stepBadge = 'badge-warning';
                    $stepIcon  = 'fa-window-close-o';
                }
              ?>
              <tr style="<?= $isRecovered ? 'background: #f8fafc; opacity: 0.85;' : '' ?>">
                <!-- Lead ID & Time -->
                <td style="vertical-align: middle; white-space: nowrap;">
                  <span class="badge badge-light border text-dark font-monospace px-2 py-1" style="font-size: 12.5px;">
                    #<?= $row['id'] ?>
                  </span>
                  <?php if (!empty($row['booking_no'])): ?>
                    <div class="text-muted small font-monospace mt-1">BK: <?= View::e($row['booking_no']) ?></div>
                  <?php endif; ?>
                  <div class="text-muted small mt-1">
                    <i class="fa fa-clock-o mr-1"></i><?= date('d M, h:i A', strtotime($row['created_at'])) ?>
                  </div>
                </td>

                <!-- Customer Details -->
                <td style="vertical-align: middle;">
                  <div class="font-weight-bold text-dark"><?= View::e($row['name'] ?: 'Guest Customer') ?></div>
                  <?php if (!empty($row['phone'])): ?>
                    <div class="d-flex align-items-center small text-muted mt-1">
                      <i class="fa fa-phone mr-1 text-primary"></i>
                      <span class="font-weight-500 text-dark"><?= View::e($row['phone']) ?></span>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($row['email'])): ?>
                    <div class="small text-muted mt-1 text-truncate" style="max-width: 200px;">
                      <i class="fa fa-envelope-o mr-1"></i><?= View::e($row['email']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($row['address'])): ?>
                    <div class="small text-muted mt-1 text-truncate" style="max-width: 220px;" title="<?= View::e($row['address']) ?>">
                      <i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($row['address']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Service & Package -->
                <td style="vertical-align: middle;">
                  <div class="font-weight-bold text-dark"><?= View::e($svcName) ?></div>
                  <?php if (!empty($row['gateway_order_id'])): ?>
                    <div class="text-muted small font-monospace mt-1" title="Razorpay Order ID">
                      Order: <?= View::e($row['gateway_order_id']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Amount -->
                <td style="vertical-align: middle; white-space: nowrap;">
                  <span class="font-weight-bold text-dark" style="font-size: 15px;">
                    <?= $amtText ?>
                  </span>
                </td>

                <!-- Problem / Drop-off Stage -->
                <td style="vertical-align: middle;">
                  <span class="badge <?= $stepBadge ?> px-2 py-1" style="font-size: 12px;">
                    <i class="fa <?= $stepIcon ?> mr-1"></i><?= $stepLabel ?>
                  </span>
                  <?php if (!empty($row['notes'])): ?>
                    <div class="small text-muted mt-1" style="max-width: 260px; line-height: 1.35;">
                      <?= View::e($row['notes']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Status -->
                <td style="vertical-align: middle; white-space: nowrap;">
                  <?php if ($isRecovered): ?>
                    <span class="badge badge-success px-2 py-1" style="font-size: 12px;">
                      <i class="fa fa-check-circle mr-1"></i>Recovered
                    </span>
                    <?php if (!empty($row['recovered_at'])): ?>
                      <div class="text-muted small mt-1"><?= date('d M, h:i A', strtotime($row['recovered_at'])) ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="badge badge-warning px-2 py-1" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size: 12px;">
                      <i class="fa fa-bolt mr-1"></i>Follow-up Needed
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Recovery Actions -->
                <td class="text-right" style="vertical-align: middle; white-space: nowrap;">
                  <?php if (!empty($cleanPhone)): ?>
                    <!-- WhatsApp Recovery Button -->
                    <a href="<?= $waUrl ?>" target="_blank" class="btn btn-xs btn-success font-weight-bold mr-1" title="Chat on WhatsApp with pre-filled recovery message">
                      <i class="fa fa-whatsapp mr-1"></i> WhatsApp
                    </a>

                    <!-- Direct Call Button -->
                    <a href="tel:<?= $cleanPhone ?>" class="btn btn-xs btn-outline-primary mr-1" title="Call Customer Immediately">
                      <i class="fa fa-phone mr-1"></i> Call
                    </a>
                  <?php endif; ?>

                  <?php if (!$isRecovered && !empty($row['booking_id'])): ?>
                    <!-- Convert to Pay After Service (COD) -->
                    <form action="<?= View::url('/admin/dropoffs/' . $row['id'] . '/convert-cod') ?>" method="POST" class="d-inline" onsubmit="return confirm('Switch booking #<?= $row['booking_id'] ?> for <?= View::e($custName) ?> to Pay After Service (COD) and confirm booking?');">
                      <?= View::csrfField() ?>
                      <button type="submit" class="btn btn-xs btn-outline-success mr-1" title="Convert to COD / Pay After Service and send confirmation">
                        <i class="fa fa-refresh mr-1"></i> Make COD
                      </button>
                    </form>
                  <?php endif; ?>

                  <?php if (!$isRecovered): ?>
                    <!-- Mark as Recovered -->
                    <form action="<?= View::url('/admin/dropoffs/' . $row['id'] . '/mark-recovered') ?>" method="POST" class="d-inline">
                      <?= View::csrfField() ?>
                      <button type="submit" class="btn btn-xs btn-outline-secondary mr-1" title="Mark this lead as recovered">
                        <i class="fa fa-check"></i>
                      </button>
                    </form>
                  <?php endif; ?>

                  <!-- Delete Drop-off -->
                  <form action="<?= View::url('/admin/dropoffs/' . $row['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this drop-off lead record?');">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete record">
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

<!-- Modal: Delete All Drop-offs -->
<div class="modal fade" id="modalDeleteAllDropoffs" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-trash mr-2"></i>Clear All Drop-off Records?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= View::url('/admin/dropoffs/delete-all') ?>" method="POST">
        <?= View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-exclamation-triangle fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Purge All Drop-off Records?</h5>
          <p class="text-muted small mb-0">
            This will clear the history of all abandoned checkouts and drop-offs. Existing active bookings in the main bookings table will not be deleted.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Clear All Drop-offs
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
