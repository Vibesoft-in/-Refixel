<?php
use App\Core\View;
$applications = $applications ?? [];
$currentTab = $currentTab ?? 'pending';
$counts = $counts ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'all' => 0];
?>

<div class="partner-applications-page">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <div class="mb-2 mb-md-0">
      <h4 class="font-weight-bold mb-1 text-dark">
        <i class="fa fa-id-card-o text-primary mr-2"></i>Partner & Job Applications
      </h4>
      <p class="text-muted small mb-0">Review online technician submissions. Once approved, the technician account is activated and login credentials are automatically emailed.</p>
    </div>

    <div>
      <a href="<?= View::url('/admin/staff') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold px-3">
        <i class="fa fa-users mr-1"></i>View Approved Workforce &rarr;
      </a>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: #ffffff;">
    <div class="card-body p-2 d-flex flex-wrap align-items-center" style="gap: 8px;">
      <a href="<?= View::url('/admin/partner-applications?status=pending') ?>" 
         class="btn btn-sm font-weight-bold <?= $currentTab === 'pending' ? 'btn-primary' : 'btn-light text-muted' ?>" 
         style="border-radius: 10px; padding: 7px 16px;">
        <i class="fa fa-clock-o mr-1"></i> Pending Review
        <span class="badge badge-pill <?= $currentTab === 'pending' ? 'badge-light text-primary' : 'badge-warning text-dark' ?> ml-1">
          <?= (int)$counts['pending'] ?>
        </span>
      </a>

      <a href="<?= View::url('/admin/partner-applications?status=approved') ?>" 
         class="btn btn-sm font-weight-bold <?= $currentTab === 'approved' ? 'btn-primary' : 'btn-light text-muted' ?>" 
         style="border-radius: 10px; padding: 7px 16px;">
        <i class="fa fa-check-circle mr-1"></i> Approved
        <span class="badge badge-pill <?= $currentTab === 'approved' ? 'badge-light text-primary' : 'badge-success' ?> ml-1">
          <?= (int)$counts['approved'] ?>
        </span>
      </a>

      <a href="<?= View::url('/admin/partner-applications?status=rejected') ?>" 
         class="btn btn-sm font-weight-bold <?= $currentTab === 'rejected' ? 'btn-primary' : 'btn-light text-muted' ?>" 
         style="border-radius: 10px; padding: 7px 16px;">
        <i class="fa fa-times-circle mr-1"></i> Rejected
        <span class="badge badge-pill <?= $currentTab === 'rejected' ? 'badge-light text-primary' : 'badge-danger' ?> ml-1">
          <?= (int)$counts['rejected'] ?>
        </span>
      </a>

      <a href="<?= View::url('/admin/partner-applications?status=all') ?>" 
         class="btn btn-sm font-weight-bold <?= $currentTab === 'all' ? 'btn-primary' : 'btn-light text-muted' ?>" 
         style="border-radius: 10px; padding: 7px 16px;">
        <i class="fa fa-list mr-1"></i> All Submissions
        <span class="badge badge-pill <?= $currentTab === 'all' ? 'badge-light text-primary' : 'badge-secondary' ?> ml-1">
          <?= (int)$counts['all'] ?>
        </span>
      </a>
    </div>
  </div>

  <!-- Applications Table Card -->
  <div class="stat-card p-0 overflow-hidden shadow-sm" style="border-radius: 14px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0 admin-table-min-lg align-middle">
        <thead class="bg-light" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
          <tr>
            <th style="padding: 14px 18px;">Applicant</th>
            <th>Contact Details</th>
            <th>Trade & Experience</th>
            <th>City / Location</th>
            <th>Status</th>
            <th>Submitted On</th>
            <th class="text-right" style="padding-right: 18px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($applications)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <div style="font-size: 40px; color: #cbd5e1; margin-bottom: 10px;">📋</div>
                <h6 class="font-weight-bold text-dark mb-1">No Applications in this Tab</h6>
                <p class="text-muted small mb-0">No technician registrations match the "<?= ucfirst($currentTab) ?>" filter.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($applications as $app): ?>
              <tr>
                <!-- Applicant Name -->
                <td style="padding: 14px 18px;">
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mr-2 text-primary font-weight-bold flex-shrink-0" style="width: 40px; height: 40px; border: 1.5px solid #f25b29; font-size: 15px;">
                      <?= strtoupper(substr($app['name'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="font-weight-bold text-dark" style="font-size: 14.5px;">
                        <?= View::e($app['name']) ?>
                      </div>
                      <div class="text-muted" style="font-size: 11px;">ID: #<?= (int)$app['id'] ?></div>
                    </div>
                  </div>
                </td>

                <!-- Contact -->
                <td>
                  <div>
                    <a href="tel:<?= View::e($app['phone']) ?>" class="text-success small font-weight-bold text-decoration-none">
                      <i class="fa fa-phone mr-1"></i><?= View::e($app['phone']) ?>
                    </a>
                  </div>
                  <?php if (!empty($app['email'])): ?>
                    <div>
                      <a href="mailto:<?= View::e($app['email']) ?>" class="text-muted small text-decoration-none" title="Send email">
                        <i class="fa fa-envelope-o mr-1 text-primary"></i><?= View::e($app['email']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Trade & Experience -->
                <td>
                  <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 12px;">
                    <i class="fa fa-wrench mr-1 text-primary"></i><?= View::e($app['skill'] ?: 'General Technician') ?>
                  </span>
                  <?php if (!empty($app['experience'])): ?>
                    <div class="text-muted small mt-1" style="font-size: 11.5px;">
                      <i class="fa fa-briefcase mr-1"></i><?= View::e($app['experience']) ?> Exp
                    </div>
                  <?php endif; ?>
                </td>

                <!-- City -->
                <td>
                  <span class="badge badge-info px-2 py-1 font-weight-bold" style="font-size: 11.5px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                    <i class="fa fa-map-marker mr-1"></i><?= View::e($app['city'] ?: 'Refixel Network') ?>
                  </span>
                </td>

                <!-- Status -->
                <td>
                  <?php if ($app['status'] === 'active'): ?>
                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 12px;">
                      <i class="fa fa-check mr-1"></i> Approved
                    </span>
                    <?php if (!empty($app['approved_by_name'])): ?>
                      <div class="text-muted" style="font-size: 11px; margin-top: 2px;">By <?= View::e($app['approved_by_name']) ?></div>
                    <?php endif; ?>
                  <?php elseif ($app['status'] === 'pending'): ?>
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 12px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                      <i class="fa fa-clock-o mr-1"></i> Pending Review
                    </span>
                  <?php elseif ($app['status'] === 'rejected'): ?>
                    <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 12px;">
                      <i class="fa fa-ban mr-1"></i> Rejected
                    </span>
                    <?php if (!empty($app['rejection_reason'])): ?>
                      <div class="text-danger small mt-1 text-truncate" style="max-width: 160px; font-size: 11px;" title="<?= View::e($app['rejection_reason']) ?>">
                        <?= View::e($app['rejection_reason']) ?>
                      </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="badge badge-secondary px-2 py-1"><?= ucfirst(View::e($app['status'])) ?></span>
                  <?php endif; ?>
                </td>

                <!-- Submission Date -->
                <td class="text-muted small" style="white-space: nowrap;">
                  <div><?= date('d M Y', strtotime($app['created_at'])) ?></div>
                  <div style="font-size: 11px;"><?= date('h:i A', strtotime($app['created_at'])) ?></div>
                </td>

                <!-- Actions -->
                <td class="text-right" style="padding-right: 18px; white-space: nowrap;">
                  <div class="d-inline-flex align-items-center gap-1">
                    <?php if ($app['status'] === 'pending' || $app['status'] === 'rejected'): ?>
                      <!-- Approve Button -->
                      <form action="<?= View::url('/admin/partner-applications/' . $app['id'] . '/approve') ?>" method="POST" class="d-inline mr-1" onsubmit="return confirm('Approve partner application for <?= View::e($app['name']) ?>?\n\nThis will activate their account in Workforce, generate a secure password, and send approval credentials to <?= View::e($app['email']) ?>.');">
                        <?= View::csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-success font-weight-bold px-2 py-1 shadow-sm" title="Approve & Send Portal Password">
                          <i class="fa fa-check-circle mr-1"></i> Approve
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($app['status'] === 'pending'): ?>
                      <!-- Reject Button triggers modal -->
                      <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold px-2 py-1 mr-1" 
                              onclick="openRejectModal(<?= (int)$app['id'] ?>, '<?= View::e(addslashes($app['name'])) ?>', '<?= View::e(addslashes($app['email'])) ?>')" 
                              title="Reject Application">
                        <i class="fa fa-times-circle mr-1"></i> Reject
                      </button>
                    <?php endif; ?>

                    <?php if ($app['status'] === 'active'): ?>
                      <a href="<?= View::url('/admin/staff/' . $app['id']) ?>" class="btn btn-sm btn-brand-outline px-2 py-1 font-weight-bold mr-1" title="View Technician Profile">
                        Workforce Profile &rarr;
                      </a>
                    <?php endif; ?>

                    <!-- Delete button -->
                    <form action="<?= View::url('/admin/partner-applications/' . $app['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this application record permanently?');">
                      <?= View::csrfField() ?>
                      <button type="submit" class="btn btn-sm btn-light text-muted px-2 py-1" title="Delete Application">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Reject Application with Reason -->
<div class="modal fade" id="rejectApplicationModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fa fa-times-circle mr-2"></i>Reject Partner Application
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="closeRejectModal()">&times;</button>
      </div>

      <form id="rejectForm" method="POST" action="">
        <?= View::csrfField() ?>
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">
            You are about to reject the application for <strong id="rejectApplicantName" class="text-dark">Applicant</strong> (<span id="rejectApplicantEmail" class="text-primary">email</span>). An update email will be delivered to the technician.
          </p>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted">Select Reason or Preset</label>
            <select class="form-control" id="rejectReasonPreset" onchange="onPresetChange(this.value)">
              <option value="">-- Choose Preset or Write Below --</option>
              <option value="Incomplete trade documents or KYC verification.">Incomplete trade documents or KYC verification</option>
              <option value="Service category is not currently active in your operational area.">Service category not active in this area</option>
              <option value="Minimum trade experience requirement of 1+ years not met.">Minimum experience requirement not met</option>
              <option value="Unable to contact technician on provided mobile number.">Unable to contact on phone</option>
              <option value="Current technician quota for this trade is full in your city.">Current workforce quota full</option>
            </select>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold small text-muted">Rejection Reason / Feedback Note <span class="text-danger">*</span></label>
            <textarea name="reason" id="rejectReasonText" class="form-control" rows="3" required placeholder="Provide reason to be sent to applicant..."></textarea>
            <small class="form-text text-muted">This explanation will be included in the email sent to the applicant.</small>
          </div>
        </div>

        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" onclick="closeRejectModal()">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold shadow-sm">
            <i class="fa fa-ban mr-1"></i> Confirm & Reject
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openRejectModal(id, name, email) {
  var form = document.getElementById('rejectForm');
  form.action = '<?= View::url('/admin/partner-applications/') ?>' + id + '/reject';
  document.getElementById('rejectApplicantName').textContent = name;
  document.getElementById('rejectApplicantEmail').textContent = email;
  document.getElementById('rejectReasonText').value = 'Application did not meet current network criteria or service coverage.';
  
  if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
    jQuery('#rejectApplicationModal').modal('show');
  } else {
    var modal = document.getElementById('rejectApplicationModal');
    modal.classList.add('show');
    modal.style.display = 'block';
  }
}

function closeRejectModal() {
  if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal !== 'undefined') {
    jQuery('#rejectApplicationModal').modal('hide');
  } else {
    var modal = document.getElementById('rejectApplicationModal');
    modal.classList.remove('show');
    modal.style.display = 'none';
  }
}

function onPresetChange(val) {
  if (val) {
    document.getElementById('rejectReasonText').value = val;
  }
}
</script>
