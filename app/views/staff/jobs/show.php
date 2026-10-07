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
        <h5 class="font-weight-bold text-dark mb-0">#<?= View::e($job['booking_no']) ?></h5>
      </div>
      <div class="text-right">
        <span class="text-muted small text-uppercase font-weight-bold">Est. Fee</span>
        <h5 class="font-weight-bold text-success mb-0">₹<?= number_format((float)($job['starting_price'] ?? 0), 0) ?></h5>
      </div>
    </div>
    <div class="border-top pt-2 mt-2">
      <div class="font-weight-bold text-dark" style="font-size: 16px;"><?= View::e($job['service_name']) ?></div>
      <div class="small text-muted mt-1">
        <i class="fa fa-calendar-check-o text-success mr-1"></i><strong>Scheduled:</strong> <?= View::e($job['preferred_date'] ?? 'As soon as possible') ?> &bull; <?= View::e($job['preferred_time'] ?? 'Flexible') ?>
      </div>
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
      <p class="small text-muted mb-3">This job has been assigned to you. Please accept it to confirm you are taking charge.</p>
      <form method="POST" action="<?= View::url('/staff/jobs/' . $job['id'] . '/accept') ?>">
        <?= View::csrfField() ?>
        <button type="submit" class="btn btn-brand btn-block py-2 font-weight-bold">
          <i class="fa fa-check-circle mr-1"></i>Accept Assignment
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
      <a href="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" class="btn btn-success btn-block py-2 font-weight-bold">
        <i class="fa fa-check-square-o mr-1"></i>Complete This Job
      </a>

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

