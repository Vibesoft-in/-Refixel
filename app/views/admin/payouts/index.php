<?php
use App\Core\View;

$payouts = $payouts ?? [];
$stats = $stats ?? [];
$technicians = $technicians ?? [];
$months = $months ?? [];
$filters = $filters ?? [
    'status'   => 'pending',
    'staff_id' => 0,
    'month'    => '',
    'search'   => '',
];

$pendingSum = (float)($stats['total_pending_amount'] ?? 0);
$pendingCount = (int)($stats['pending_count'] ?? 0);
$settledSum = (float)($stats['total_settled_amount'] ?? 0);
$settledCount = (int)($stats['settled_count'] ?? 0);
$companySplitSum = (float)($stats['total_company_split'] ?? 0);
$techsWithPending = $stats['technicians_with_pending'] ?? [];
$currentStatus = $filters['status'] ?? 'pending';
?>

<div class="admin-payouts-page pb-4">
  <!-- Page Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 pb-2 border-bottom">
    <div class="mb-2 mb-md-0">
      <div class="d-flex align-items-center">
        <h4 class="font-weight-bold mb-0 text-dark mr-2">Technician Payouts & Settlements</h4>
        <?php if ($pendingCount > 0): ?>
          <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 6px;">
            <i class="fa fa-clock-o mr-1"></i><?= $pendingCount ?> PENDING
          </span>
        <?php else: ?>
          <span class="badge badge-success px-2 py-1" style="font-size: 11px; border-radius: 6px;">
            <i class="fa fa-check mr-1"></i>ALL SETTLED
          </span>
        <?php endif; ?>
      </div>
      <p class="text-muted small mb-0 mt-1">
        Disburse pending payments to service technicians, track company commission splits (25%), and record transaction references (UPI/Bank/Cash).
      </p>
    </div>

    <div class="d-flex align-items-center">
      <a href="<?= View::url('/admin/commissions') ?>" class="btn btn-sm btn-outline-secondary mr-2" style="border-radius: 6px;">
        <i class="fa fa-percent mr-1"></i>Commission Rules
      </a>
      <a href="<?= View::url('/admin/payments') ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
        <i class="fa fa-file-text-o mr-1"></i>Customer Invoices
      </a>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if ($flashSuccess = View::flash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show font-weight-500 shadow-sm" role="alert">
      <i class="fa fa-check-circle mr-1"></i> <?= View::e($flashSuccess) ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <?php if ($flashError = View::flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show font-weight-500 shadow-sm" role="alert">
      <i class="fa fa-exclamation-triangle mr-1"></i> <?= View::e($flashError) ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <?php if ($flashWarning = View::flash('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show font-weight-500 shadow-sm" role="alert">
      <i class="fa fa-info-circle mr-1"></i> <?= View::e($flashWarning) ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php endif; ?>

  <!-- Summary KPI Cards -->
  <div class="row mb-3">
    <div class="col-6 col-lg-3 mb-2 mb-lg-0">
      <div class="stat-card h-100" style="border-left: 4px solid #f59e0b !important;">
        <div class="stat-label text-warning font-weight-bold">
          <i class="fa fa-clock-o mr-1"></i>Total Pending Payouts
        </div>
        <div class="stat-value text-dark" style="font-size: 24px;">₹<?= number_format($pendingSum, 2) ?></div>
        <span class="text-muted small"><strong><?= $pendingCount ?></strong> jobs awaiting disbursement</span>
      </div>
    </div>
    <div class="col-6 col-lg-3 mb-2 mb-lg-0">
      <div class="stat-card h-100" style="border-left: 4px solid #10b981 !important;">
        <div class="stat-label text-success font-weight-bold">
          <i class="fa fa-check-circle mr-1"></i>Total Settled Payouts
        </div>
        <div class="stat-value text-success" style="font-size: 24px;">₹<?= number_format($settledSum, 2) ?></div>
        <span class="text-muted small"><strong><?= $settledCount ?></strong> completed payouts cleared</span>
      </div>
    </div>
    <div class="col-6 col-lg-3 mb-2 mb-lg-0">
      <div class="stat-card h-100" style="border-left: 4px solid #f25b29 !important;">
        <div class="stat-label" style="color: #f25b29; font-weight: 700;">
          <i class="fa fa-pie-chart mr-1"></i>Company Split (25%)
        </div>
        <div class="stat-value text-dark" style="font-size: 24px;">₹<?= number_format($companySplitSum, 2) ?></div>
        <span class="text-muted small">Refixel platform fee retained</span>
      </div>
    </div>
    <div class="col-6 col-lg-3 mb-2 mb-lg-0">
      <div class="stat-card h-100" style="border-left: 4px solid #0284c7 !important;">
        <div class="stat-label text-info font-weight-bold">
          <i class="fa fa-users mr-1"></i>Technicians Awaiting Pay
        </div>
        <div class="stat-value text-info" style="font-size: 24px;"><?= count($techsWithPending) ?></div>
        <span class="text-muted small">Active partners with pending balance</span>
      </div>
    </div>
  </div>

  <!-- Quick Settle Per Technician (1-Click Batch Disbursement) -->
  <?php if (!empty($techsWithPending)): ?>
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #fff;">
      <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="font-weight-bold mb-0 text-dark" style="font-size: 13.5px;">
          <i class="fa fa-bolt text-warning mr-1"></i>Quick Settle by Technician (1-Click Settle All)
        </h6>
        <span class="text-muted small"><?= count($techsWithPending) ?> technician(s) have pending balances</span>
      </div>
      <div class="card-body p-3">
        <div class="row">
          <?php foreach ($techsWithPending as $tp): ?>
            <div class="col-12 col-md-6 col-xl-4 mb-2">
              <div class="p-3 rounded border d-flex justify-content-between align-items-center" style="background: #fafafa;">
                <div>
                  <div class="font-weight-bold text-dark mb-0" style="font-size: 14.5px;">
                    <i class="fa fa-user-circle text-primary mr-1"></i><?= View::e($tp['name']) ?>
                  </div>
                  <div class="text-muted small mt-1">
                    <?php if (!empty($tp['phone'])): ?>
                      <i class="fa fa-phone mr-1"></i><?= View::e($tp['phone']) ?> &bull; 
                    <?php endif; ?>
                    <span class="badge badge-warning text-dark font-weight-bold"><?= (int)$tp['pending_jobs'] ?> jobs pending</span>
                  </div>
                  <div class="font-weight-bold text-success mt-1" style="font-size: 15px;">
                    Total Due: ₹<?= number_format((float)$tp['pending_sum'], 2) ?>
                  </div>
                </div>
                <div>
                  <button type="button" 
                          class="btn btn-sm btn-success font-weight-bold btn-settle-all"
                          data-staff-id="<?= (int)$tp['staff_id'] ?>"
                          data-staff-name="<?= View::e($tp['name']) ?>"
                          data-pending-amount="<?= number_format((float)$tp['pending_sum'], 2) ?>"
                          data-pending-jobs="<?= (int)$tp['pending_jobs'] ?>"
                          data-toggle="modal" 
                          data-target="#settleAllModal"
                          style="border-radius: 6px; white-space: nowrap;">
                    <i class="fa fa-check-circle mr-1"></i>Pay All
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Filter & Search Toolbar -->
  <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
    <div class="card-body p-3">
      <!-- Status Tabs -->
      <ul class="nav nav-pills mb-3 border-bottom pb-2">
        <li class="nav-item">
          <a class="nav-link font-weight-bold <?= $currentStatus === 'pending' ? 'active' : '' ?>" 
             href="<?= View::url('/admin/payouts?status=pending' . ($filters['staff_id'] ? '&staff_id=' . $filters['staff_id'] : '') . ($filters['month'] ? '&month=' . urlencode($filters['month']) : '') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')) ?>"
             style="<?= $currentStatus === 'pending' ? 'background: #f25b29; color: #fff;' : 'color: #475569;' ?>">
            <i class="fa fa-clock-o mr-1"></i>Pending Payouts
            <?php if ($pendingCount > 0): ?>
              <span class="badge badge-light text-dark ml-1 font-weight-bold"><?= $pendingCount ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link font-weight-bold <?= $currentStatus === 'all' ? 'active' : '' ?>" 
             href="<?= View::url('/admin/payouts?status=all' . ($filters['staff_id'] ? '&staff_id=' . $filters['staff_id'] : '') . ($filters['month'] ? '&month=' . urlencode($filters['month']) : '') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')) ?>"
             style="<?= $currentStatus === 'all' ? 'background: #0a1c33; color: #fff;' : 'color: #475569;' ?>">
            <i class="fa fa-list mr-1"></i>All Records
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link font-weight-bold <?= $currentStatus === 'settled' ? 'active' : '' ?>" 
             href="<?= View::url('/admin/payouts?status=settled' . ($filters['staff_id'] ? '&staff_id=' . $filters['staff_id'] : '') . ($filters['month'] ? '&month=' . urlencode($filters['month']) : '') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')) ?>"
             style="<?= $currentStatus === 'settled' ? 'background: #10b981; color: #fff;' : 'color: #475569;' ?>">
            <i class="fa fa-check-circle mr-1"></i>Settled / Paid Only
            <span class="badge badge-light text-dark ml-1 font-weight-bold"><?= $settledCount ?></span>
          </a>
        </li>
      </ul>

      <!-- Filter Form -->
      <form action="<?= View::url('/admin/payouts') ?>" method="GET" class="row align-items-end">
        <input type="hidden" name="status" value="<?= View::e($currentStatus) ?>">

        <div class="col-12 col-md-3 mb-2 mb-md-0">
          <label class="small font-weight-bold text-muted mb-1">Technician</label>
          <select name="staff_id" class="form-control form-control-sm" style="border-radius: 6px;">
            <option value="0">All Technicians</option>
            <?php foreach ($technicians as $tech): ?>
              <option value="<?= (int)$tech['id'] ?>" <?= ((int)$filters['staff_id'] === (int)$tech['id']) ? 'selected' : '' ?>>
                <?= View::e($tech['name']) ?> (<?= View::e($tech['phone']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-2 mb-2 mb-md-0">
          <label class="small font-weight-bold text-muted mb-1">Month</label>
          <select name="month" class="form-control form-control-sm" style="border-radius: 6px;">
            <option value="">All Months</option>
            <?php foreach ($months as $m): ?>
              <option value="<?= View::e($m) ?>" <?= ($filters['month'] === $m) ? 'selected' : '' ?>>
                <?= View::e($m) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-4 mb-2 mb-md-0">
          <label class="small font-weight-bold text-muted mb-1">Search</label>
          <div class="input-group input-group-sm">
            <input type="text" name="search" class="form-control" placeholder="Booking #, Tech Name, Service, UTR..." value="<?= View::e($filters['search']) ?>" style="border-radius: 6px 0 0 6px;">
            <div class="input-group-append">
              <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3 mb-2 mb-md-0 text-md-right">
          <button type="submit" class="btn btn-sm btn-brand font-weight-bold" style="border-radius: 6px;">
            <i class="fa fa-filter mr-1"></i>Apply Filter
          </button>
          <a href="<?= View::url('/admin/payouts') ?>" class="btn btn-sm btn-outline-secondary ml-1" style="border-radius: 6px;">
            Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Detailed Payouts Table -->
  <div class="stat-card p-0 overflow-hidden shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th style="width: 80px;">ID</th>
            <th>Technician</th>
            <th>Booking & Service</th>
            <th class="text-right">Gross Total</th>
            <th class="text-right">Company Split (25%)</th>
            <th class="text-right">Net Tech Payout (75%)</th>
            <th class="text-center">Status</th>
            <th class="text-right" style="width: 140px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payouts)): ?>
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="text-muted">
                  <i class="fa fa-money fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                  <h6 class="font-weight-bold text-dark">No Payout Records Found</h6>
                  <p class="small text-muted mb-0">
                    <?php if ($currentStatus === 'pending'): ?>
                      Great job! There are currently no pending payouts waiting for disbursement.
                    <?php else: ?>
                      No payout records match the selected filter criteria.
                    <?php endif; ?>
                  </p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($payouts as $p): ?>
              <?php
                $isSettled = !empty($p['is_settled']);
                $grossAmt = (float)($p['gross_amount'] ?? 0);
                $splitAmt = (float)($p['admin_fee'] ?? 0);
                $techAmt = (float)($p['amount'] ?? 0);
                $workerPct = (int)($p['worker_percent'] ?? 75);
                $adminPct = (int)($p['admin_percent'] ?? 25);
              ?>
              <tr>
                <!-- ID & Date -->
                <td>
                  <strong class="text-dark">#<?= (int)$p['id'] ?></strong>
                  <div class="text-muted small" style="font-size: 11px;">
                    <?= View::e(substr((string)$p['created_at'], 0, 10)) ?>
                  </div>
                  <?php if (!empty($p['month'])): ?>
                    <span class="badge badge-light border text-secondary" style="font-size: 10px;"><?= View::e($p['month']) ?></span>
                  <?php endif; ?>
                </td>

                <!-- Technician Details -->
                <td>
                  <div class="font-weight-bold text-dark">
                    <i class="fa fa-user-circle text-muted mr-1"></i><?= View::e($p['staff_name']) ?>
                  </div>
                  <div class="text-muted small">
                    <i class="fa fa-phone mr-1"></i><?= View::e($p['staff_phone']) ?>
                  </div>
                </td>

                <!-- Booking & Service -->
                <td>
                  <div>
                    <a href="<?= View::url('/admin/bookings/' . (int)$p['job_id']) ?>" class="font-weight-bold text-primary">
                      #<?= View::e($p['booking_no']) ?>
                    </a>
                  </div>
                  <div class="font-weight-bold text-dark small">
                    <?= View::e($p['service_name']) ?>
                  </div>
                  <?php if (!empty($p['category_name'])): ?>
                    <div class="text-muted small" style="font-size: 11px;">
                      <?= View::e($p['category_name']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Gross Total -->
                <td class="text-right">
                  <span class="font-weight-bold text-dark">₹<?= number_format($grossAmt, 2) ?></span>
                </td>

                <!-- Refixel Split (25%) -->
                <td class="text-right">
                  <span class="text-danger font-weight-bold">-₹<?= number_format($splitAmt, 2) ?></span>
                  <div class="text-muted small" style="font-size: 10.5px;">(<?= $adminPct ?>% fee)</div>
                </td>

                <!-- Net Tech Payout (75%) -->
                <td class="text-right">
                  <span class="font-weight-bold text-success" style="font-size: 15px;">₹<?= number_format($techAmt, 2) ?></span>
                  <div class="text-muted small" style="font-size: 10.5px;">(<?= $workerPct ?>% net)</div>
                </td>

                <!-- Status -->
                <td class="text-center">
                  <?php if ($isSettled): ?>
                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px; border-radius: 6px;">
                      <i class="fa fa-check mr-1"></i>Settled
                    </span>
                    <div class="text-muted small mt-1" style="font-size: 11px;">
                      Via <strong><?= strtoupper(View::e($p['settled_method'] ?? 'UPI')) ?></strong>
                      <?php if (!empty($p['settled_ref'])): ?>
                        <br><span class="text-dark">Ref: <code><?= View::e($p['settled_ref']) ?></code></span>
                      <?php endif; ?>
                      <?php if (!empty($p['settled_at'])): ?>
                        <br><span class="text-muted"><?= View::e(substr((string)$p['settled_at'], 0, 10)) ?></span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 11px; border-radius: 6px;">
                      <i class="fa fa-clock-o mr-1"></i>Pending Payout
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Action Button -->
                <td class="text-right">
                  <?php if (!$isSettled): ?>
                    <button type="button" 
                            class="btn btn-sm btn-brand font-weight-bold btn-settle-single"
                            data-payout-id="<?= (int)$p['id'] ?>"
                            data-staff-name="<?= View::e($p['staff_name']) ?>"
                            data-booking-no="<?= View::e($p['booking_no']) ?>"
                            data-service-name="<?= View::e($p['service_name']) ?>"
                            data-amount="<?= number_format($techAmt, 2) ?>"
                            data-toggle="modal" 
                            data-target="#settleModal"
                            style="border-radius: 6px;">
                      <i class="fa fa-paper-plane mr-1"></i>Pay / Settle
                    </button>
                  <?php else: ?>
                    <button type="button" class="btn btn-sm btn-light border text-muted" disabled style="border-radius: 6px; font-size: 12px;">
                      <i class="fa fa-check text-success mr-1"></i>Paid
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: SINGLE PAYOUT SETTLEMENT -->
<!-- ========================================== -->
<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-labelledby="settleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #0a1c33 0%, #122844 100%);">
        <h5 class="modal-title font-weight-bold" id="settleModalLabel" style="font-size: 16px;">
          <i class="fa fa-paper-plane text-warning mr-2"></i>Disburse Technician Payment
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="settleSingleForm" method="POST" action="">
        <?= View::csrfField() ?>
        <div class="modal-body p-4">
          <!-- Summary Banner -->
          <div class="p-3 rounded mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Technician:</span>
              <strong class="text-dark" id="modalTechName">-</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Booking & Service:</span>
              <span class="text-dark small font-weight-bold" id="modalBookingInfo">-</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted font-weight-bold">Net Payout to Disburse:</span>
              <h4 class="text-success font-weight-bold mb-0" id="modalAmount">₹0.00</h4>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="form-group">
            <label class="font-weight-bold text-dark small mb-1">Payment Method / Channel <span class="text-danger">*</span></label>
            <select name="settled_method" class="form-control" required style="border-radius: 6px;">
              <option value="upi" selected>UPI (Google Pay / PhonePe / Paytm / BHIM)</option>
              <option value="bank_transfer">Bank Transfer (IMPS / NEFT / RTGS)</option>
              <option value="cash">Cash in Hand</option>
              <option value="cheque">Cheque / Demand Draft</option>
              <option value="other">Other / Digital Wallet</option>
            </select>
          </div>

          <!-- Transaction Reference / UTR -->
          <div class="form-group">
            <label class="font-weight-bold text-dark small mb-1">Transaction Ref / UTR / Txn ID</label>
            <input type="text" 
                   name="settled_ref" 
                   class="form-control" 
                   placeholder="e.g. UPI-49281749284 or IMPS-918237492"
                   style="border-radius: 6px;">
            <small class="form-text text-muted">Provide the bank/UPI UTR reference number for technician audit records.</small>
          </div>

          <!-- Remarks / Notes -->
          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">Settlement Notes (Optional)</label>
            <input type="text" 
                   name="settled_notes" 
                   class="form-control" 
                   placeholder="e.g. Transferred to SBI A/c ending in 4102"
                   style="border-radius: 6px;">
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
          <button type="submit" class="btn btn-success font-weight-bold" style="border-radius: 6px;">
            <i class="fa fa-check-circle mr-1"></i>Confirm & Mark as Settled
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: BATCH SETTLE ALL FOR TECHNICIAN -->
<!-- ========================================== -->
<div class="modal fade" id="settleAllModal" tabindex="-1" role="dialog" aria-labelledby="settleAllModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
        <h5 class="modal-title font-weight-bold" id="settleAllModalLabel" style="font-size: 16px;">
          <i class="fa fa-bolt mr-2"></i>1-Click Settle All Technician Jobs
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="settleAllForm" method="POST" action="">
        <?= View::csrfField() ?>
        <div class="modal-body p-4">
          <!-- Summary Banner -->
          <div class="p-3 rounded mb-3" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Technician:</span>
              <strong class="text-dark" id="modalBatchTechName">-</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Pending Jobs:</span>
              <span class="badge badge-success font-weight-bold" id="modalBatchJobsCount">0 jobs</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-dark font-weight-bold">Total Batch Payout:</span>
              <h4 class="text-success font-weight-bold mb-0" id="modalBatchTotalAmount">₹0.00</h4>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="form-group">
            <label class="font-weight-bold text-dark small mb-1">Payment Method / Channel <span class="text-danger">*</span></label>
            <select name="settled_method" class="form-control" required style="border-radius: 6px;">
              <option value="upi" selected>UPI (Google Pay / PhonePe / Paytm / BHIM)</option>
              <option value="bank_transfer">Bank Transfer (IMPS / NEFT / RTGS)</option>
              <option value="cash">Cash in Hand</option>
              <option value="cheque">Cheque / Demand Draft</option>
              <option value="other">Other / Digital Wallet</option>
            </select>
          </div>

          <!-- Transaction Reference / UTR -->
          <div class="form-group">
            <label class="font-weight-bold text-dark small mb-1">Transaction Ref / UTR / Txn ID</label>
            <input type="text" 
                   name="settled_ref" 
                   class="form-control" 
                   placeholder="e.g. BATCH-UPI-982341908 or IMPS-584920"
                   style="border-radius: 6px;">
            <small class="form-text text-muted">Reference number of the combined payment transfer.</small>
          </div>

          <!-- Remarks / Notes -->
          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark small mb-1">Settlement Notes (Optional)</label>
            <input type="text" 
                   name="settled_notes" 
                   class="form-control" 
                   placeholder="e.g. Combined payout for all completed bookings"
                   style="border-radius: 6px;">
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3 border-top">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
          <button type="submit" class="btn btn-success font-weight-bold" style="border-radius: 6px;">
            <i class="fa fa-check-circle mr-1"></i>Settle All Pending Payouts
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Single payout settlement modal populate
  $('.btn-settle-single').on('click', function() {
    var payoutId = $(this).data('payout-id');
    var staffName = $(this).data('staff-name');
    var bookingNo = $(this).data('booking-no');
    var serviceName = $(this).data('service-name');
    var amount = $(this).data('amount');

    $('#modalTechName').text(staffName);
    $('#modalBookingInfo').text('#' + bookingNo + ' (' + serviceName + ')');
    $('#modalAmount').text('₹' + amount);

    var actionUrl = '<?= View::url('/admin/payouts/') ?>' + payoutId + '/settle';
    $('#settleSingleForm').attr('action', actionUrl);
  });

  // Batch settle all modal populate
  $('.btn-settle-all').on('click', function() {
    var staffId = $(this).data('staff-id');
    var staffName = $(this).data('staff-name');
    var pendingAmount = $(this).data('pending-amount');
    var pendingJobs = $(this).data('pending-jobs');

    $('#modalBatchTechName').text(staffName);
    $('#modalBatchJobsCount').text(pendingJobs + ' jobs');
    $('#modalBatchTotalAmount').text('₹' + pendingAmount);

    var actionUrl = '<?= View::url('/admin/payouts/technicians/') ?>' + staffId + '/settle-all';
    $('#settleAllForm').attr('action', actionUrl);
  });
});
</script>
