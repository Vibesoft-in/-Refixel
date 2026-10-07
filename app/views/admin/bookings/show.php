<?php
use App\Core\View;
$booking = $booking ?? [];
$job = $job ?? [];
$staffMembers = $staffMembers ?? [];
$history = $history ?? [];
$jobPhotos = $jobPhotos ?? [];
$attachments = $attachments ?? [];
$payments = $payments ?? [];
$invoice = $invoice ?? null;

$beforePhotos = array_filter($jobPhotos, fn($p) => $p['type'] === 'before');
$afterPhotos = array_filter($jobPhotos, fn($p) => $p['type'] === 'after');
?>

<div class="admin-booking-show">
  <!-- Top Bar -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <a href="<?= View::url('/admin/bookings') ?>" class="text-muted small font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i>Back to Bookings
      </a>
      <h4 class="font-weight-bold text-dark mb-0">Booking #<?= View::e($booking['booking_no']) ?></h4>
    </div>
    <div>
      <span class="badge badge-status-<?= View::e($booking['status']) ?> px-3 py-2 font-weight-bold" style="font-size: 13px;">
        <?= strtoupper(str_replace('_', ' ', $booking['status'])) ?>
      </span>
    </div>
  </div>

  <div class="row">
    <!-- Left Column: Booking Info & Field Details -->
    <div class="col-lg-8">
      <!-- Customer & Service Card -->
      <div class="stat-card mb-3">
        <div class="row">
          <div class="col-md-6 mb-3">
            <span class="text-muted small text-uppercase font-weight-bold">Customer Profile</span>
            <h5 class="font-weight-bold text-dark mt-1 mb-1"><?= View::e($booking['name']) ?></h5>
            <div class="small">
              <a href="tel:<?= View::e($booking['phone']) ?>" class="text-success font-weight-bold mr-2">
                <i class="fa fa-phone mr-1"></i><?= View::e($booking['phone']) ?>
              </a>
              <?php if (!empty($booking['email'])): ?>
                <span class="text-muted"><i class="fa fa-envelope mr-1"></i><?= View::e($booking['email']) ?></span>
              <?php endif; ?>
            </div>
            <div class="p-2 bg-light rounded small text-dark mt-2">
              <i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($booking['address']) ?>
              <?php if (!empty($booking['pincode'])): ?> &bull; <?= View::e($booking['pincode']) ?><?php endif; ?>
            </div>
          </div>

          <div class="col-md-6 mb-3">
            <span class="text-muted small text-uppercase font-weight-bold">Service & Fee</span>
            <h5 class="font-weight-bold text-dark mt-1 mb-1"><?= View::e($booking['service_name']) ?></h5>
            <div class="text-success font-weight-bold mb-2">Base Price: ₹<?= number_format((float)($booking['starting_price'] ?? 0), 2) ?></div>
            <div class="p-2 bg-light rounded small text-dark">
              <i class="fa fa-calendar-check-o text-primary mr-1"></i><strong>Scheduled:</strong> <?= View::e($booking['preferred_date'] ?? 'Flexible') ?> (<?= View::e($booking['preferred_time'] ?? '') ?>)
            </div>
          </div>
        </div>

        <?php if (!empty($booking['issue_details'])): ?>
          <div class="border-top pt-3 mt-1">
            <div class="small font-weight-bold text-muted mb-1">Issue Details / Work Requested:</div>
            <div class="p-3 bg-light rounded small text-dark"><?= nl2br(View::e($booking['issue_details'])) ?></div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Customer Issue Attachments -->
      <?php if (!empty($attachments)): ?>
        <div class="stat-card mb-3">
          <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-paperclip text-muted mr-2"></i>Customer Uploaded Attachments</h6>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($attachments as $att): ?>
              <a href="<?= View::asset($att['file_path']) ?>" target="_blank" class="mr-2 mb-2 border rounded overflow-hidden d-inline-block shadow-sm" style="width: 80px; height: 80px;">
                <img src="<?= View::asset($att['file_path']) ?>" alt="Attachment" style="width: 100%; height: 100%; object-fit: cover;">
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Field Photos (Before and After) -->
      <?php if (!empty($jobPhotos) || !empty($job['work_summary'])): ?>
        <div class="stat-card mb-3">
          <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-camera text-info mr-2"></i>Technician Execution & Proof Photos</h6>
          
          <?php if (!empty($job['work_summary'])): ?>
            <div class="alert alert-light border small text-dark mb-3">
              <strong>Work Summary:</strong> <?= nl2br(View::e($job['work_summary'])) ?>
              <?php if (!empty($job['final_notes'])): ?>
                <div class="mt-1 text-muted"><strong>Final Notes:</strong> <?= nl2br(View::e($job['final_notes'])) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="row">
            <div class="col-6">
              <span class="small font-weight-bold text-muted mb-2 d-block">BEFORE PHOTOS (<?= count($beforePhotos) ?>):</span>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($beforePhotos as $p): ?>
                  <a href="<?= View::asset($p['file_path']) ?>" target="_blank" class="mr-1 mb-1 border rounded overflow-hidden" style="width: 70px; height: 70px;">
                    <img src="<?= View::asset($p['file_path']) ?>" alt="Before" style="width: 100%; height: 100%; object-fit: cover;">
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-6">
              <span class="small font-weight-bold text-muted mb-2 d-block">AFTER PHOTOS (<?= count($afterPhotos) ?>):</span>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($afterPhotos as $p): ?>
                  <a href="<?= View::asset($p['file_path']) ?>" target="_blank" class="mr-1 mb-1 border rounded overflow-hidden" style="width: 70px; height: 70px;">
                    <img src="<?= View::asset($p['file_path']) ?>" alt="After" style="width: 100%; height: 100%; object-fit: cover;">
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Payments & Invoices -->
      <div class="stat-card mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-money text-success mr-2"></i>Payment & Billing</h6>
          <?php if (empty($payments)): ?>
            <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" data-toggle="modal" data-target="#recordPaymentModal">
              <i class="fa fa-plus mr-1"></i>Record Payment
            </button>
          <?php endif; ?>
        </div>

        <?php if (empty($payments)): ?>
          <div class="p-3 bg-light rounded text-center text-muted small">
            No payments recorded yet for this booking.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead class="bg-light">
                <tr>
                  <th>Amount</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Paid At</th>
                  <th>Invoice</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td class="font-weight-bold text-success">₹<?= number_format((float)$p['amount'], 2) ?></td>
                    <td><?= strtoupper(View::e($p['method'] ?? 'CASH')) ?></td>
                    <td><span class="badge badge-success"><?= strtoupper(View::e($p['status'])) ?></span></td>
                    <td class="small"><?= View::e($p['paid_at'] ?? $p['created_at']) ?></td>
                    <td>
                      <?php if (!empty($invoice)): ?>
                        <span class="badge badge-light border text-dark">#<?= View::e($invoice['invoice_no']) ?></span>
                      <?php else: ?>
                        <span class="text-muted small">Auto-generating</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Status Audit History Timeline -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-history text-muted mr-2"></i>Status History Timeline</h6>
        <?php if (empty($history)): ?>
          <p class="text-muted small mb-0">No timeline history recorded yet.</p>
        <?php else: ?>
          <div class="timeline pl-2" style="border-left: 2px solid #e2ece7;">
            <?php foreach ($history as $h): ?>
              <div class="position-relative pl-3 pb-3">
                <div style="position: absolute; left: -7px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: #f25b29; border: 2px solid #fff;"></div>
                <div class="d-flex justify-content-between">
                  <span class="font-weight-bold small text-dark">
                    <?= ucfirst(str_replace('_', ' ', $h['to_status'])) ?>
                    <?php if (!empty($h['changed_by_name'])): ?>
                      <span class="text-muted font-weight-normal">(by <?= View::e($h['changed_by_name']) ?>)</span>
                    <?php endif; ?>
                  </span>
                  <span class="text-muted" style="font-size: 11px;"><?= View::e($h['created_at']) ?></span>
                </div>
                <?php if (!empty($h['notes'])): ?>
                  <div class="text-muted small mt-1"><?= View::e($h['notes']) ?></div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Column: Operations Panel (Assign, Reschedule, Status) -->
    <div class="col-lg-4">
      <!-- Assign / Reassign Staff Card -->
      <div class="stat-card mb-3" style="border: 2px solid #f25b29;">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-user-plus text-success mr-2"></i>Technician Assignment</h6>
        
        <?php if (!empty($booking['staff_name'])): ?>
          <div class="p-2 bg-light rounded mb-3 small">
            <div><span class="text-muted">Assigned:</span> <strong><?= View::e($booking['staff_name']) ?></strong></div>
            <div><span class="text-muted">Status:</span> <span class="badge badge-status-<?= View::e($job['status'] ?? 'assigned') ?>"><?= ucfirst($job['status'] ?? 'assigned') ?></span></div>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= View::url('/admin/bookings/' . $booking['id'] . '/assign') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Select Technician</label>
            <select name="staff_id" class="form-control form-control-sm" required>
              <option value="">-- Choose technician --</option>
              <?php foreach ($staffMembers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($booking['staff_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                  <?= View::e($s['name']) ?> (★ <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?>) <?= !empty($s['is_available']) ? '🟢' : '🔴' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Scheduled Time</label>
            <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i') ?>">
          </div>

          <button type="submit" class="btn btn-brand btn-block btn-sm font-weight-bold py-2">
            <i class="fa fa-check mr-1"></i><?= !empty($booking['staff_name']) ? 'Reassign Technician' : 'Assign Technician' ?>
          </button>
        </form>
      </div>

      <!-- Reschedule Booking Card -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-calendar text-info mr-2"></i>Reschedule Slot</h6>
        
        <form method="POST" action="<?= View::url('/admin/bookings/' . $booking['id'] . '/reschedule') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-2">
            <label class="font-weight-bold small text-dark">New Date</label>
            <input type="date" name="preferred_date" class="form-control form-control-sm" value="<?= View::e($booking['preferred_date'] ?? date('Y-m-d')) ?>" required>
          </div>

          <div class="form-group mb-2">
            <label class="font-weight-bold small text-dark">New Time Slot</label>
            <select name="preferred_time" class="form-control form-control-sm">
              <option value="09:00 AM - 12:00 PM">09:00 AM - 12:00 PM</option>
              <option value="12:00 PM - 03:00 PM">12:00 PM - 03:00 PM</option>
              <option value="03:00 PM - 06:00 PM">03:00 PM - 06:00 PM</option>
              <option value="06:00 PM - 09:00 PM">06:00 PM - 09:00 PM</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Reason (Optional)</label>
            <input type="text" name="reason" class="form-control form-control-sm" placeholder="e.g. Customer requested morning slot">
          </div>

          <button type="submit" class="btn btn-outline-info btn-block btn-sm font-weight-bold">
            <i class="fa fa-refresh mr-1"></i>Confirm Reschedule
          </button>
        </form>
      </div>

      <!-- Override Status / Cancellation Card -->
      <div class="stat-card">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-sliders text-danger mr-2"></i>Status Override / Cancel</h6>
        
        <form method="POST" action="<?= View::url('/admin/bookings/' . $booking['id'] . '/status') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-2">
            <label class="font-weight-bold small text-dark">Change Status</label>
            <select name="status" class="form-control form-control-sm">
              <option value="new" <?= $booking['status'] === 'new' ? 'selected' : '' ?>>New</option>
              <option value="assigned" <?= $booking['status'] === 'assigned' ? 'selected' : '' ?>>Assigned</option>
              <option value="in_progress" <?= $booking['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
              <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
              <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Admin Reason / Note</label>
            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason for status change..." required>
          </div>

          <button type="submit" class="btn btn-outline-danger btn-block btn-sm font-weight-bold">
            Update Status
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= View::url('/admin/payments') ?>">
        <?= View::csrfField() ?>
        <input type="hidden" name="booking_id" value="<?= View::e($booking['id']) ?>">
        
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Record Payment for #<?= View::e($booking['booking_no']) ?></h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="font-weight-bold small">Amount (INR)</label>
            <input type="number" step="0.01" name="amount" class="form-control" value="<?= View::e($booking['starting_price'] ?? 0) ?>" required>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small">Payment Method</label>
            <select name="method" class="form-control">
              <option value="cash">Cash on Delivery (Cash)</option>
              <option value="upi">UPI / QR Code</option>
              <option value="card">Debit / Credit Card</option>
              <option value="bank">Bank Transfer / NetBanking</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success font-weight-bold">Save Payment & Generate Invoice</button>
        </div>
      </form>
    </div>
  </div>
</div>

