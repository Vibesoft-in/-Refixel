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

  <!-- Job Details Summary Card -->
  <div class="job-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="font-weight-bold text-dark">#<?= View::e($job['booking_no']) ?></span>
      <span class="text-success font-weight-bold">₹<?= number_format((float)($job['starting_price'] ?? 0), 0) ?></span>
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

