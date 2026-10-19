<?php
use App\Core\View;
$job = $job ?? [];
$history = $history ?? [];
$photos = $photos ?? [];
$beforePhotos = $beforePhotos ?? [];
$afterPhotos = $afterPhotos ?? [];
$customerAttachments = $customerAttachments ?? [];

$cleanPhone = preg_replace('/[^0-9]/', '', (string)($job['customer_phone'] ?? ''));
if (strlen($cleanPhone) === 10) {
    $waPhone = '91' . $cleanPhone;
} else {
    $waPhone = $cleanPhone;
}

$mapsQuery = urlencode(($job['address'] ?? '') . ' ' . ($job['pincode'] ?? ''));
?>

<div class="staff-job-detail">
  <!-- Back navigation & Title -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= View::url('/staff/jobs') ?>" class="text-dark font-weight-bold" style="text-decoration: none;">
      <i class="fa fa-arrow-left mr-2"></i>Back to Jobs
    </a>
    <span class="badge badge-status-<?= View::e($job['status']) ?> px-3 py-1 font-weight-bold" style="font-size: 13px;">
      <?= strtoupper(str_replace('_', ' ', $job['status'])) ?>
    </span>
  </div>

  <!-- Job Header Card -->
  <div class="job-card mb-3">
    <div class="d-flex justify-content-between align-items-start mb-2">
      <div>
        <span class="text-muted small text-uppercase font-weight-bold">Booking ID</span>
        <h5 class="font-weight-bold text-dark mb-1">#<?= View::e($job['booking_no']) ?></h5>
        
        <div class="d-flex flex-wrap align-items-center mt-1" style="gap: 6px;">
          <!-- Prepaid / Pre-Booked Badge -->
          <?php if (!empty($job['is_prepaid'])): ?>
            <span class="badge font-weight-bold px-2 py-1" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px;">
              <i class="fa fa-credit-card mr-1"></i>Prepaid (Fully Paid)
            </span>
          <?php else: ?>
            <span class="badge font-weight-bold px-2 py-1" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px;">
              <i class="fa fa-calendar-check-o mr-1"></i>Pre-Booked
            </span>
          <?php endif; ?>

          <!-- Priority Badge -->
          <?php if (($job['priority_key'] ?? '') === 'urgent'): ?>
            <span class="badge font-weight-bold px-2 py-1" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: 11px;">
              <i class="fa fa-fire mr-1"></i>Urgent Priority
            </span>
          <?php elseif (($job['priority_key'] ?? '') === 'high'): ?>
            <span class="badge font-weight-bold px-2 py-1" style="background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; font-size: 11px;">
              <i class="fa fa-bolt mr-1"></i>High Priority
            </span>
          <?php elseif (($job['priority_key'] ?? '') === 'low'): ?>
            <span class="badge font-weight-bold px-2 py-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-size: 11px;">
              <i class="fa fa-arrow-down mr-1"></i>Low Priority
            </span>
          <?php else: ?>
            <span class="badge font-weight-bold px-2 py-1" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 11px;">
              Normal Priority
            </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="text-right">
        <span class="text-muted small text-uppercase font-weight-bold">Your Net Payout</span>
        <h5 class="font-weight-bold text-success mb-0" style="font-size: 22px;">₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?></h5>
        <span class="badge badge-success px-2 py-0" style="font-size: 10px;"><?= (float)($job['worker_percent'] ?? 75) ?>% Share</span>
      </div>
    </div>
    <div class="border-top pt-2 mt-2">
      <div class="font-weight-bold text-dark" style="font-size: 16px;"><?= View::e($job['service_name']) ?></div>
      <div class="small text-muted mt-1">
        <i class="fa fa-calendar-check-o text-success mr-1"></i><strong>Scheduled:</strong> <?= View::e($job['preferred_date'] ?? 'As soon as possible') ?> &bull; <?= View::e($job['preferred_time'] ?? 'Flexible') ?>
      </div>
      <div class="mt-2 p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 12px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 6px;">
          <span class="text-muted">Total Service Value: <strong class="text-dark">₹<?= number_format((float)($job['gross_amount'] ?? 0), 0) ?></strong></span>
          <span class="text-muted">Company Split: <strong class="text-danger">-₹<?= number_format((float)($job['company_split'] ?? 0), 0) ?></strong> (<?= (float)($job['admin_percent'] ?? 25) ?>%)</span>
          <span class="text-success font-weight-bold">Net Tech Share: ₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?> (<?= (float)($job['worker_percent'] ?? 75) ?>%)</span>
        </div>
      </div>

      <?php 
        $items = $job['items'] ?? \App\Models\Booking::getItems((int)$job['booking_id'], $job);
        $addons = array_values(array_filter($items, fn($i) => ($i['item_type'] ?? '') === 'addon'));
      ?>
      <?php if (!empty($addons)): ?>
        <div class="mt-3 p-3 rounded" style="background: #fffbeb; border: 1.5px solid #fde68a;">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="font-weight-bold text-dark" style="font-size: 13.5px;">
              <i class="fa fa-cubes text-warning mr-1"></i>Scope of Work: Included Add-ons (<?= count($addons) ?>)
            </span>
            <span class="badge badge-warning font-weight-bold px-2 py-1" style="font-size: 11px; background: #fef08a; color: #854d0e; border: 1px solid #fde047;">
              ⚡ Required On-Site
            </span>
          </div>
          <div class="small text-muted mb-2" style="font-size: 11.5px;">
            Customer paid for the following services and add-ons. Please execute all items during your visit:
          </div>
          <div class="list-group list-group-flush rounded bg-white border">
            <div class="list-group-item py-2 px-3 d-flex justify-content-between align-items-center" style="font-size: 13px;">
              <div>
                <i class="fa fa-wrench text-primary mr-2"></i>
                <strong class="text-dark"><?= View::e($job['service_name']) ?></strong>
                <span class="badge badge-primary px-2 py-0 ml-1" style="font-size: 10px;">Primary Service</span>
              </div>
              <span class="text-muted font-weight-bold">₹<?= number_format((float)($job['starting_price'] ?? 0), 0) ?></span>
            </div>
            <?php foreach ($addons as $ad): ?>
              <div class="list-group-item py-2 px-3 d-flex justify-content-between align-items-center" style="font-size: 13px; background: #f0fdf4;">
                <div>
                  <i class="fa fa-plus-circle text-success mr-2"></i>
                  <strong class="text-dark"><?= View::e($ad['name']) ?></strong>
                  <span class="badge badge-success px-2 py-0 ml-1" style="font-size: 10px;">+ Add-on Included</span>
                  <?php if (!empty($ad['badge'])): ?>
                    <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;"><?= View::e($ad['badge']) ?></span>
                  <?php endif; ?>
                </div>
                <span class="font-weight-bold text-success">+₹<?= number_format((float)($ad['total_price'] ?? $ad['unit_price'] ?? 0), 0) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Customer Contact & Actions Card -->
  <div class="job-card mb-3">
    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-user-circle-o text-primary mr-2"></i>Customer & Location</h6>
    
    <div class="mb-3">
      <div class="font-weight-bold text-dark" style="font-size: 15px;"><?= View::e($job['customer_name']) ?></div>
      <div class="text-muted small"><i class="fa fa-phone mr-1"></i><?= View::e($job['customer_phone']) ?></div>
      <?php if (!empty($job['customer_email'])): ?>
        <div class="text-muted small"><i class="fa fa-envelope-o mr-1"></i><?= View::e($job['customer_email']) ?></div>
      <?php endif; ?>
    </div>

    <div class="bg-light p-2 rounded mb-3">
      <div class="small text-muted font-weight-bold mb-1"><i class="fa fa-map-marker text-danger mr-1"></i>Service Address</div>
      <div class="text-dark small"><?= View::e($job['address']) ?></div>
      <?php if (!empty($job['pincode'])): ?>
        <div class="text-muted small"><strong>Pincode:</strong> <?= View::e($job['pincode']) ?></div>
      <?php endif; ?>
    </div>

    <!-- 1-Tap Field Actions (Call, WhatsApp, Maps) -->
    <div class="row no-gutters mx-n1">
      <div class="col-4 p-1">
        <a href="tel:<?= View::e($job['customer_phone']) ?>" class="btn btn-sm btn-outline-success btn-block py-2 font-weight-bold">
          <i class="fa fa-phone fa-lg d-block mb-1"></i>Call
        </a>
      </div>
      <div class="col-4 p-1">
        <a href="https://wa.me/<?= View::e($waPhone) ?>?text=<?= urlencode("Hello " . $job['customer_name'] . ", I am your REFIXEL technician for booking #" . $job['booking_no'] . ".") ?>" 
           target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success btn-block py-2 font-weight-bold" style="border-color: #25d366; color: #25d366;">
          <i class="fa fa-whatsapp fa-lg d-block mb-1"></i>WhatsApp
        </a>
      </div>
      <div class="col-4 p-1">
        <a href="https://www.google.com/maps/search/?api=1&query=<?= $mapsQuery ?>" 
           target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary btn-block py-2 font-weight-bold">
          <i class="fa fa-map-marker fa-lg d-block mb-1"></i>Maps
        </a>
      </div>
    </div>
  </div>

  <!-- Customer Issue Details & Attachments -->
  <div class="job-card mb-3">
    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-info-circle text-info mr-2"></i>Reported Issue</h6>
    <?php if (!empty($job['issue_details'])): ?>
      <p class="text-dark small mb-3 p-2 bg-light rounded"><?= nl2br(View::e($job['issue_details'])) ?></p>
    <?php else: ?>
      <p class="text-muted small mb-3">No specific issue details reported by customer.</p>
    <?php endif; ?>

    <?php if (!empty($customerAttachments)): ?>
      <div class="small font-weight-bold text-muted mb-2">Customer Photos / Attachments:</div>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($customerAttachments as $att): ?>
          <a href="<?= View::asset($att['file_path']) ?>" target="_blank" class="mr-2 mb-2 d-inline-block border rounded overflow-hidden shadow-sm" style="width: 80px; height: 80px;">
            <img src="<?= View::asset($att['file_path']) ?>" alt="Attachment" style="width: 100%; height: 100%; object-fit: cover;">
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- WORKFLOW ACTION PANEL (Status Driven) -->
  <div class="job-card mb-3" style="border: 2px solid #f25b29; background: #fff8f5;">
    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-tasks mr-2" style="color:#f25b29;"></i>Job Actions</h6>

    <?php if ($job['status'] === 'assigned'): ?>
      <div class="p-3 bg-white rounded border mb-3" style="border-left: 4px solid #10b981 !important;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="text-muted small font-weight-bold">Your Net Payout (After Split):</span>
          <span class="font-weight-bold text-success" style="font-size: 20px;">₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?></span>
        </div>
        <div class="small text-muted mb-2">
          Service Total: <strong>₹<?= number_format((float)($job['gross_amount'] ?? 0), 0) ?></strong> &bull;
          Refixel Split (<?= (float)($job['admin_percent'] ?? 25) ?>%): <span class="text-danger font-weight-bold">-₹<?= number_format((float)($job['company_split'] ?? 0), 0) ?></span>
        </div>
        <div class="d-flex flex-wrap align-items-center mt-2 mb-2" style="gap: 6px;">
          <?php if (!empty($job['is_prepaid'])): ?>
            <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i>Prepaid (Fully Paid) - No Cash Collection</span>
          <?php else: ?>
            <span class="badge badge-primary px-2 py-1"><i class="fa fa-calendar-check-o mr-1"></i>Pre-Booked - Collect on Visit</span>
          <?php endif; ?>
          <span class="badge badge-light border text-dark px-2 py-1 font-weight-bold">
            <?= View::e($job['priority_label'] ?? 'Normal Priority') ?>
          </span>
        </div>
        <p class="small text-muted mb-0">Check the service requirement, priority and location to decide if you want to take this job.</p>
      </div>
      <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/accept') ?>">
        <?= View::csrfField() ?>
        <button type="submit" class="btn btn-brand btn-block py-2 font-weight-bold shadow-sm" style="font-size: 15px;">
          <i class="fa fa-check-circle mr-1"></i>Accept Job (Net Payout: ₹<?= number_format((float)($job['technician_payout'] ?? 0), 0) ?>)
        </button>
      </form>

    <?php elseif ($job['status'] === 'accepted'): ?>
      <p class="small text-muted mb-3">You accepted this job. Notify the customer when you start travelling or start the job on site.</p>
      <div class="row no-gutters mx-n1">
        <div class="col-6 p-1">
          <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/on-the-way') ?>">
            <?= View::csrfField() ?>
            <button type="submit" class="btn btn-outline-primary btn-block font-weight-bold py-2">
              <i class="fa fa-motorcycle mr-1"></i>On the Way
            </button>
          </form>
        </div>
        <div class="col-6 p-1">
          <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/start') ?>">
            <?= View::csrfField() ?>
            <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2">
              <i class="fa fa-play mr-1"></i>Start Job
            </button>
          </form>
        </div>
      </div>

    <?php elseif ($job['status'] === 'in_progress'): ?>
      <div class="alert alert-primary py-2 px-3 small font-weight-bold mb-3">
        <i class="fa fa-wrench mr-1"></i>Work is in progress. Remember to take Before and After photos!
      </div>

      <?php if (($dueAmt ?? 0) > 0 && empty($job['is_prepaid'])): ?>
        <div class="p-3 bg-white rounded border mb-3" style="border-left: 4px solid #f25b29 !important;">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small font-weight-bold text-dark"><i class="fa fa-credit-card mr-1 text-danger"></i>Customer Payment Due:</span>
            <span class="font-weight-bold text-danger" style="font-size: 19px;">₹<?= number_format($dueAmt, 0) ?></span>
          </div>
          <div class="small text-muted mb-2">Total Service Fee ₹<?= round($totAmt ?? 0) ?> &bull; Advance Paid ₹<?= round($advAmt ?? 0) ?></div>
          <button type="button" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm" data-toggle="modal" data-target="#staffPaymentModal">
            <i class="fa fa-qrcode mr-1"></i>Collect Payment (Online QR / Cash)
          </button>
        </div>

        <a href="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" class="btn btn-outline-success btn-block py-2 font-weight-bold">
          <i class="fa fa-check-square-o mr-1"></i>Complete This Job
        </a>
      <?php else: ?>
        <a href="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" class="btn btn-success btn-block py-2 font-weight-bold">
          <i class="fa fa-check-square-o mr-1"></i>Complete This Job
        </a>
      <?php endif; ?>

    <?php elseif ($job['status'] === 'completed'): ?>
      <div class="alert alert-success py-2 px-3 small font-weight-bold mb-2">
        <i class="fa fa-check-circle mr-1"></i>This job was marked as completed on <?= View::e($job['completed_at'] ?? $job['updated_at']) ?>.
      </div>
      <?php if (!empty($job['work_summary'])): ?>
        <div class="bg-white p-2 rounded border mb-2 small">
          <strong>Work Summary:</strong> <?= nl2br(View::e($job['work_summary'])) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($job['final_notes'])): ?>
        <div class="bg-white p-2 rounded border mb-2 small">
          <strong>Final Notes:</strong> <?= nl2br(View::e($job['final_notes'])) ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Before & After Photos Section -->
  <div class="job-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-camera text-secondary mr-2"></i>Job Photos</h6>
    </div>

    <!-- Upload Photo Modal Trigger / Form if job in progress or accepted -->
    <?php if (in_array($job['status'], ['accepted', 'in_progress'], true)): ?>
      <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/photos') ?>" enctype="multipart/form-data" class="bg-light p-3 rounded mb-3">
        <?= View::csrfField() ?>
        <div class="form-row align-items-center">
          <div class="col-4">
            <select name="type" class="form-control form-control-sm">
              <option value="before">Before Photo</option>
              <option value="after">After Photo</option>
            </select>
          </div>
          <div class="col-5">
            <input type="file" name="photo" accept="image/*" capture="environment" class="form-control-file form-control-sm" required>
          </div>
          <div class="col-3">
            <button type="submit" class="btn btn-sm btn-brand btn-block font-weight-bold">Upload</button>
          </div>
        </div>
      </form>
    <?php endif; ?>

    <div class="row">
      <!-- Before Photos -->
      <div class="col-6">
        <div class="small font-weight-bold text-muted mb-2">BEFORE (<?= count($beforePhotos) ?>):</div>
        <?php if (empty($beforePhotos)): ?>
          <div class="text-muted small font-italic">No before photos</div>
        <?php else: ?>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($beforePhotos as $p): ?>
              <a href="<?= View::asset($p['file_path']) ?>" target="_blank" class="mr-1 mb-1 d-inline-block border rounded overflow-hidden" style="width: 70px; height: 70px;">
                <img src="<?= View::asset($p['file_path']) ?>" alt="Before" style="width: 100%; height: 100%; object-fit: cover;">
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- After Photos -->
      <div class="col-6">
        <div class="small font-weight-bold text-muted mb-2">AFTER (<?= count($afterPhotos) ?>):</div>
        <?php if (empty($afterPhotos)): ?>
          <div class="text-muted small font-italic">No after photos</div>
        <?php else: ?>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($afterPhotos as $p): ?>
              <a href="<?= View::asset($p['file_path']) ?>" target="_blank" class="mr-1 mb-1 d-inline-block border rounded overflow-hidden" style="width: 70px; height: 70px;">
                <img src="<?= View::asset($p['file_path']) ?>" alt="After" style="width: 100%; height: 100%; object-fit: cover;">
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Status Audit Timeline -->
  <div class="job-card mb-3">
    <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-history text-muted mr-2"></i>Status History</h6>
    <?php if (empty($history)): ?>
      <p class="text-muted small mb-0">No status log recorded yet.</p>
    <?php else: ?>
      <div class="timeline pl-2" style="border-left: 2px solid #e2ece7;">
        <?php foreach ($history as $h): ?>
          <div class="position-relative pl-3 pb-3">
            <div style="position: absolute; left: -7px; top: 3px; width: 12px; height: 12px; border-radius: 50%; background: #f25b29; border: 2px solid #fff;"></div>
            <div class="d-flex justify-content-between">
              <span class="font-weight-bold small text-dark"><?= ucfirst(str_replace('_', ' ', $h['to_status'])) ?></span>
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

<?= View::partial('staff-payment-modal', [
    'job'    => $job,
    'totAmt' => $totAmt ?? 0,
    'advAmt' => $advAmt ?? 0,
    'dueAmt' => $dueAmt ?? 0
]) ?>

