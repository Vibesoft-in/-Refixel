<?php
use App\Core\View;
$job = $job ?? [];
$beforePhotos = $beforePhotos ?? [];
$afterPhotos = $afterPhotos ?? [];
?>

<div class="staff-job-complete">
  <!-- Back navigation -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= View::url('/staff/jobs/' . $job['id']) ?>" class="text-dark font-weight-bold" style="text-decoration: none;">
      <i class="fa fa-arrow-left mr-2"></i>Back to Job #<?= View::e($job['booking_no']) ?>
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-lg mb-3" style="background: #fff8f5; border-left: 4px solid #f25b29 !important;">
    <div class="card-body p-3">
      <h6 class="font-weight-bold mb-1" style=\color:#f25b29;"><i class="fa fa-check-circle mr-1"></i>Finalize Service & Completion</h6>
      <p class="small text-muted mb-0">Record your work summary, customer recommendations, and upload final photos to close the job and record your payout.</p>
    </div>
  </div>

  <?php if (($dueAmt ?? 0) > 0 && empty($job['is_prepaid'])): ?>
    <div class="card border-0 shadow-sm rounded-lg mb-3" style="background: #fff8f5; border-left: 4px solid #dc2626 !important;">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
          <div>
            <span class="badge badge-danger font-weight-bold mb-1"><i class="fa fa-exclamation-triangle mr-1"></i>Customer Payment Pending</span>
            <h6 class="font-weight-bold text-dark mb-0">Balance to Collect: ₹<?= number_format($dueAmt, 0) ?></h6>
            <div class="small text-muted">Total: ₹<?= round($totAmt ?? 0) ?> &bull; Advance Paid: ₹<?= round($advAmt ?? 0) ?></div>
          </div>
          <div>
            <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 py-2 shadow-sm" data-toggle="modal" data-target="#staffPaymentModal">
              <i class="fa fa-qrcode mr-1"></i>Pay via Online QR / Cash
            </button>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-success py-2 px-3 small font-weight-bold mb-3">
      <i class="fa fa-check-circle mr-1"></i>Customer Payment Settled &bull; 100% Fully Paid
    </div>
  <?php endif; ?>

  <!-- Job Details Summary Card -->
  <div class="job-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap: 6px;">
      <div class="d-flex align-items-center" style="gap: 6px;">
        <span class="font-weight-bold text-dark">#<?= View::e($job['booking_no']) ?></span>
        <?php if (!empty($job['is_prepaid'])): ?>
          <span class="badge font-weight-bold px-2 py-1" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px;">
            <i class="fa fa-credit-card mr-1"></i>Prepaid (Fully Paid)
          </span>
        <?php else: ?>
          <span class="badge font-weight-bold px-2 py-1" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px;">
            <i class="fa fa-calendar-check-o mr-1"></i>Pre-Booked
          </span>
        <?php endif; ?>
        <?php if (($job['priority_key'] ?? '') === 'urgent'): ?>
          <span class="badge badge-danger font-weight-bold px-2 py-1" style="font-size: 10px;">Urgent Priority</span>
        <?php elseif (($job['priority_key'] ?? '') === 'high'): ?>
          <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 10px;">High Priority</span>
        <?php endif; ?>
      </div>
      <div class="text-right">
        <span class="text-muted small">Your Net Payout (<?= (float)($job['worker_percent'] ?? 75) ?>%):</span>
        <span class="text-success font-weight-bold ml-1" style="font-size: 16px;">₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?></span>
        <div class="text-muted" style="font-size: 10.5px;">
          Total: ₹<?= number_format((float)($job['gross_amount'] ?? 0), 0) ?> &bull; Split: -₹<?= number_format((float)($job['company_split'] ?? 0), 0) ?>
        </div>
      </div>
    </div>
    <div class="font-weight-bold text-dark mb-1"><?= View::e($job['service_name']) ?></div>
    <div class="small text-muted">
      <div><i class="fa fa-user-circle-o mr-1"></i><?= View::e($job['customer_name']) ?> &bull; <?= View::e($job['customer_phone']) ?></div>
      <div><i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($job['address']) ?></div>
    </div>
  </div>

  <!-- Completion Form -->
  <div class="job-card">
    <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" enctype="multipart/form-data">
      <?= View::csrfField() ?>

      <div class="form-group mb-3">
        <label class="font-weight-bold text-dark small">
          Work Summary <span class="text-danger">*</span>
        </label>
        <textarea name="work_summary" class="form-control" rows="3" required placeholder="Describe the actual work executed, materials used, areas cleaned/fixed..."><?= View::e($job['work_summary'] ?? '') ?></textarea>
        <small class="form-text text-muted">Required. Summarize what was completed on site.</small>
      </div>

      <div class="form-group mb-3">
        <label class="font-weight-bold text-dark small">
          Final Notes / Recommendations for Customer
        </label>
        <textarea name="final_notes" class="form-control" rows="2" placeholder="Any care instructions or recommendations for the customer (e.g. keep ventilated for 2 hours)..."><?= View::e($job['final_notes'] ?? '') ?></textarea>
      </div>

      <!-- Before Photos Section -->
      <div class="form-group mb-3 p-3 bg-light rounded">
        <label class="font-weight-bold text-dark small mb-1">
          <i class="fa fa-camera text-secondary mr-1"></i>Before Photos
        </label>
        <?php if (!empty($beforePhotos)): ?>
          <div class="d-flex flex-wrap gap-2 mb-2">
            <?php foreach ($beforePhotos as $p): ?>
              <div class="mr-1 mb-1 border rounded overflow-hidden" style="width: 60px; height: 60px;">
                <img src="<?= View::asset($p['file_path']) ?>" alt="Before" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <input type="file" name="before_photos[]" multiple accept="image/*" class="form-control-file small">
        <small class="form-text text-muted">Upload photos of the site before starting work (optional if already uploaded).</small>
      </div>

      <!-- After Photos Section -->
      <div class="form-group mb-4 p-3 bg-light rounded">
        <label class="font-weight-bold text-dark small mb-1">
          <i class="fa fa-camera text-success mr-1"></i>After Photos (Completion Proof)
        </label>
        <?php if (!empty($afterPhotos)): ?>
          <div class="d-flex flex-wrap gap-2 mb-2">
            <?php foreach ($afterPhotos as $p): ?>
              <div class="mr-1 mb-1 border rounded overflow-hidden" style="width: 60px; height: 60px;">
                <img src="<?= View::asset($p['file_path']) ?>" alt="After" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <input type="file" name="after_photos[]" multiple accept="image/*" class="form-control-file small">
        <small class="form-text text-muted">Upload photos of the completed service/work area.</small>
      </div>

      <!-- Customer Payment Settlement Row (if not settled earlier) -->
      <?php if (($dueAmt ?? 0) > 0 && empty($job['is_prepaid'])): ?>
        <div class="p-3 bg-light rounded border mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold text-dark small"><i class="fa fa-money text-success mr-1"></i>Customer Payment Settlement</span>
            <span class="badge badge-warning text-dark font-weight-bold">Due: ₹<?= number_format($dueAmt, 0) ?></span>
          </div>
          <div class="form-row">
            <div class="col-6">
              <label class="small text-muted font-weight-bold mb-1">Collection Method</label>
              <select name="settle_payment_method" class="form-control form-control-sm">
                <option value="cash">💵 Cash Received</option>
                <option value="upi">📱 Online (UPI / QR Code)</option>
              </select>
            </div>
            <div class="col-6">
              <label class="small text-muted font-weight-bold mb-1">Amount Received (₹)</label>
              <input type="number" step="1" name="settle_payment_amount" value="<?= round($dueAmt) ?>" class="form-control form-control-sm font-weight-bold text-success" required>
            </div>
          </div>
          <small class="form-text text-muted mt-1">If not already recorded via the popup, confirm the collected amount here to finish.</small>
        </div>
      <?php endif; ?>

      <div class="border-top pt-3">
        <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold" style="font-size: 15px;">
          <i class="fa fa-check-circle mr-1"></i>Submit & Mark as Completed
        </button>
        <a href="<?= View::url('/staff/jobs/' . $job['id']) ?>" class="btn btn-light btn-block text-muted mt-2 font-weight-bold">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<?= View::partial('staff-payment-modal', [
    'job'    => $job,
    'totAmt' => $totAmt ?? 0,
    'advAmt' => $advAmt ?? 0,
    'dueAmt' => $dueAmt ?? 0
]) ?>

