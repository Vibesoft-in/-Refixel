<?php
use App\Core\View;
$booking = $booking ?? [];
$staffMembers = $staffMembers ?? [];
$attachments = $attachments ?? [];
?>

<div class="admin-enquiry-show">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <a href="<?= View::url('/admin/enquiries') ?>" class="text-muted small font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i>Back to Enquiries
      </a>
      <h4 class="font-weight-bold text-dark mb-0">Enquiry #<?= View::e($booking['booking_no']) ?></h4>
    </div>
    <div>
      <span class="badge badge-status-<?= View::e($booking['status']) ?> px-3 py-2 font-weight-bold" style="font-size: 13px;">
        STATUS: <?= strtoupper(str_replace('_', ' ', $booking['status'])) ?>
      </span>
    </div>
  </div>

  <div class="row">
    <!-- Left Column: Details -->
    <div class="col-lg-7">
      <!-- Customer Card -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-user-circle text-primary mr-2"></i>Customer Information</h6>
        <div class="row">
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Customer Name</div>
            <div class="font-weight-bold text-dark"><?= View::e($booking['name']) ?></div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Contact Phone</div>
            <div>
              <a href="tel:<?= View::e($booking['phone']) ?>" class="text-success font-weight-bold">
                <i class="fa fa-phone mr-1"></i><?= View::e($booking['phone']) ?>
              </a>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Email Address</div>
            <div class="text-dark"><?= View::e($booking['email'] ?? 'Not provided') ?></div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Preferred Schedule</div>
            <div class="text-dark font-weight-bold">
              <i class="fa fa-calendar mr-1 text-muted"></i><?= View::e($booking['preferred_date'] ?? 'Flexible') ?> (<?= View::e($booking['preferred_time'] ?? '') ?>)
            </div>
          </div>
          <div class="col-12 mt-2">
            <div class="text-muted small">Service Address</div>
            <div class="p-2 bg-light rounded text-dark mt-1">
              <i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($booking['address']) ?>
              <?php if (!empty($booking['pincode'])): ?>
                &bull; Pincode: <strong><?= View::e($booking['pincode']) ?></strong>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Service Requested & Issue Details -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-wrench text-info mr-2"></i>Service Requested</h6>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <span class="badge badge-light border text-muted"><?= View::e($booking['category_name'] ?? 'General') ?></span>
            <h5 class="font-weight-bold text-dark mt-1 mb-0"><?= View::e($booking['service_name']) ?></h5>
          </div>
          <div class="text-right">
            <div class="text-muted small">Starting Price</div>
            <h4 class="font-weight-bold text-success mb-0">₹<?= number_format((float)($booking['starting_price'] ?? 0), 2) ?></h4>
          </div>
        </div>

        <div class="border-top pt-3 mt-3">
          <div class="small font-weight-bold text-dark mb-1">Customer Issue Notes:</div>
          <?php if (!empty($booking['issue_details'])): ?>
            <div class="p-3 bg-light rounded small text-dark"><?= nl2br(View::e($booking['issue_details'])) ?></div>
          <?php else: ?>
            <div class="text-muted small font-italic">No additional notes provided by customer.</div>
          <?php endif; ?>
        </div>

        <?php if (!empty($attachments)): ?>
          <div class="border-top pt-3 mt-3">
            <div class="small font-weight-bold text-dark mb-2">Issue Attachments / Photos:</div>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($attachments as $att): ?>
                <a href="<?= View::asset($att['file_path']) ?>" target="_blank" class="mr-2 mb-2 border rounded overflow-hidden d-inline-block shadow-sm" style="width: 80px; height: 80px;">
                  <img src="<?= View::asset($att['file_path']) ?>" alt="Attachment" style="width: 100%; height: 100%; object-fit: cover;">
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Column: Operations (Notes, Priority, Assign Technician) -->
    <div class="col-lg-5">
      <!-- Assign Technician Form -->
      <div class="stat-card mb-3" style="border: 2px solid #f25b29; background: #fff8f5;">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-motorcycle text-success mr-2"></i>Assign Technician</h6>
        
        <?php if (!empty($booking['staff_name'])): ?>
          <div class="alert alert-info py-2 px-3 small font-weight-bold mb-3">
            <i class="fa fa-info-circle mr-1"></i>Currently assigned to: <strong><?= View::e($booking['staff_name']) ?></strong> (Status: <?= ucfirst($booking['job_status'] ?? 'assigned') ?>)
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= View::url('/admin/bookings/' . $booking['id'] . '/assign') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Select Field Technician</label>
            <select name="staff_id" class="form-control" required>
              <option value="">-- Choose active technician --</option>
              <?php foreach ($staffMembers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($booking['staff_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                  <?= View::e($s['name']) ?> (★ <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?>) <?= !empty($s['is_available']) ? '🟢 Available' : '🔴 Busy/Off' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Dispatch / Scheduled Time</label>
            <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
          </div>

          <button type="submit" class="btn btn-brand btn-block font-weight-bold py-2">
            <i class="fa fa-check-circle mr-1"></i>Confirm & Assign Technician
          </button>
        </form>
      </div>

      <!-- Update Enquiry Status, Priority & Admin Notes -->
      <div class="stat-card">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-edit text-muted mr-2"></i>Update Enquiry Details</h6>
        
        <form method="POST" action="<?= View::url('/admin/enquiries/' . $booking['id'] . '/update') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Priority</label>
            <select name="priority" class="form-control">
              <option value="normal" <?= ($booking['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
              <option value="high" <?= ($booking['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
              <option value="urgent" <?= ($booking['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
              <option value="low" <?= ($booking['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Internal Admin Notes</label>
            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add internal notes about customer conversation, discount discussed, special tools required..."><?= View::e($booking['admin_notes'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-outline-secondary btn-block font-weight-bold">
            <i class="fa fa-save mr-1"></i>Save Enquiry Notes
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

