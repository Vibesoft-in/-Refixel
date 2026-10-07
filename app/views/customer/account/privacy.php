<div class="dash-card p-4 p-md-5">
  <div class="mb-4 pb-3" style="border-bottom: 1px solid #edf2f7;">
    <h3 class="dash-heading mb-1">
      <i class="fa fa-shield text-success mr-2" style="opacity: 0.8;"></i> Privacy & Data Governance
    </h3>
    <p class="text-muted small mb-0" style="font-weight: 400; line-height: 1.6;">
      In alignment with the Digital Personal Data Protection (DPDP) Act, REFIXEL provides full transparency into your personal data processing, active consents, and data rights.
    </p>
  </div>

  <!-- Data Portability / Export -->
  <div class="p-4 mb-5 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
      <div class="mb-3 mb-md-0">
        <h6 class="dash-heading mb-1 text-dark">Data Portability (Export Your Information)</h6>
        <p class="small text-muted mb-0" style="font-weight: 400;">Download a complete, machine-readable JSON copy of your profile, bookings, reviews, and consent trail.</p>
      </div>
      <a href="<?= \App\Core\View::url('/account/privacy/export') ?>" class="btn soft-btn soft-btn-outline px-3 py-2 btn-sm text-nowrap">
        <i class="fa fa-download mr-1"></i> Export Data (JSON)
      </a>
    </div>
  </div>

  <!-- Consent Audit Trail -->
  <h6 class="dash-heading mb-3">Consent Audit Log</h6>
  <div class="table-responsive mb-5">
    <table class="table dash-table text-nowrap mb-0">
      <thead>
        <tr>
          <th>Purpose</th>
          <th>Timestamp (IST)</th>
          <th>IP Address</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($consents)): ?>
          <?php foreach ($consents as $c): ?>
            <tr>
              <td class="font-weight-500 text-dark"><?= \App\Core\View::e(str_replace('_', ' ', ucwords($c['purpose']))) ?></td>
              <td class="text-muted"><?= \App\Core\View::e($c['granted_at']) ?></td>
              <td class="text-muted" style="font-family: monospace; font-size: 12px;"><?= \App\Core\View::e($c['ip']) ?></td>
              <td><span class="badge-soft badge-soft-success">ACTIVE</span></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center text-muted py-4" style="font-weight: 400;">No explicit consent records recorded yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Right to Erasure / Data Deletion Request -->
  <div class="p-4 rounded" style="background: #fffafa; border: 1px solid #fecdd3;">
    <h6 class="dash-heading text-danger mb-2">
      <i class="fa fa-exclamation-triangle mr-1" style="opacity: 0.8;"></i> Right to Erasure / Data Deletion Request
    </h6>
    <p class="small text-muted mb-4" style="font-weight: 400; line-height: 1.6;">
      You may request the permanent deletion of your personal account data. Please note that statutory records (such as GST-compliant tax invoices and transaction ledgers) must be retained under applicable Indian tax laws for statutory retention periods.
    </p>

    <form action="<?= \App\Core\View::url('/account/privacy/delete-request') ?>" method="POST" onsubmit="return confirm('Are you sure you wish to submit a data erasure request? This action will initiate account deactivation.');">
      <?= \App\Core\View::csrf() ?>
      <div class="form-group mb-3">
        <label class="small font-weight-500 text-dark">Reason for Deletion Request (Optional)</label>
        <textarea name="reason" class="form-control border-0 bg-white shadow-sm" style="border-radius: 8px;" rows="2" placeholder="Briefly describe your reason for requesting erasure..."></textarea>
      </div>
      <div class="custom-control custom-checkbox mb-4">
        <input type="checkbox" class="custom-control-input" id="confirmErasure" name="confirm_erasure" value="1" required>
        <label class="custom-control-label small text-muted" for="confirmErasure" style="font-weight: 400; padding-top: 2px;">
          I understand that submitting this request will initiate account closure and anonymization of my customer profile.
        </label>
      </div>
      <button type="submit" class="btn soft-btn px-4 py-2" style="background: #ef4444; color: #ffffff; border: none;">
        Submit Erasure Request
      </button>
    </form>
  </div>
</div>

