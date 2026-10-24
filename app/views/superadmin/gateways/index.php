<div class="container-fluid p-0">
  <!-- Page Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div>
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-credit-card text-primary mr-2"></i>Payment Gateways & API Credentials
      </h3>
      <p class="text-muted small mb-0">Super Admin Exclusive: Configure Razorpay keys, webhook secrets, currency, and live/sandbox switching.</p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
      <span class="badge badge-dark p-2 border" style="font-size: 12px; background: #0f172a;">
        <i class="fa fa-shield text-success mr-1"></i> End-to-End Encrypted Storage
      </span>
    </div>
  </div>

  <!-- Summary Stats -->
  <div class="row mb-4">
    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Active Gateways</div>
        <div class="text-dark" style="font-size: clamp(20px, 3.5vw, 26px); font-weight: 800;">
          <?= count(array_filter($gateways, fn($g) => !empty($g['is_active']))) ?>
          <span class="small text-muted font-weight-normal" style="font-size: 12px;">of <?= count($gateways) ?> enabled</span>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Primary Gateway</div>
        <div class="text-primary text-truncate" style="font-size: clamp(18px, 3.2vw, 22px); font-weight: 800;">
          Razorpay
          <span class="badge badge-success ml-1" style="font-size: 10px; vertical-align: middle;">LIVE</span>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Base Currency</div>
        <div class="text-dark" style="font-size: clamp(20px, 3.5vw, 26px); font-weight: 800;">
          INR (₹)
          <span class="small text-muted font-weight-normal" style="font-size: 12px;">Indian Rupee</span>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">Webhook Status</div>
        <div class="text-success text-truncate" style="font-size: clamp(18px, 3.2vw, 22px); font-weight: 800;">
          <i class="fa fa-check-circle mr-1"></i> Ready
          <span class="small text-muted font-weight-normal text-truncate" style="font-size: 11px; display: block;">/webhook/razorpay</span>
        </div>
      </div>
    </div>
  </div>

  <style>
    .rfx-ios-switch {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
      margin: 0;
      vertical-align: middle;
    }
    .rfx-ios-switch input {
      opacity: 0;
      width: 0;
      height: 0;
      position: absolute;
    }
    .rfx-ios-slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #cbd5e1;
      transition: .22s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 24px;
      box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }
    .rfx-ios-slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: #ffffff;
      transition: .22s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0,0,0,0.25);
    }
    input:checked + .rfx-ios-slider {
      background-color: #10b981;
    }
    input:checked + .rfx-ios-slider:before {
      transform: translateX(20px);
    }
    .rfx-policy-card {
      background: linear-gradient(135deg, #ffffff 0%, #fffbf8 100%);
      border: 1.5px solid #fed7aa;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(242, 91, 41, 0.08);
      position: relative;
      overflow: hidden;
    }
    .rfx-policy-card:before {
      content: "";
      position: absolute;
      top: 0; left: 0; bottom: 0;
      width: 5px;
      background: #f25b29;
    }
  </style>

  <!-- Pre-Booking Advance Payment Policy Card (₹49 Advance Toggle) -->
  <div class="rfx-policy-card p-3 p-md-4 mb-4">
    <div class="row align-items-center">
      <div class="col-lg-7 mb-3 mb-lg-0">
        <div class="d-flex align-items-center mb-2 flex-wrap" style="gap: 8px;">
          <h4 class="font-weight-bold text-dark mb-0" style="font-size: 18px;">
            <i class="fa fa-sliders text-warning mr-1"></i> Pre-Booking Advance Payment Policy
          </h4>
          <span id="advancePolicyStatusBadge">
            <?php if (!empty($advanceEnabled)): ?>
              <span class="badge badge-success px-2 py-1" style="font-size: 11px;">
                <i class="fa fa-check-circle mr-1"></i>Advance Requirement Active (₹<?= round($advanceAmount ?? 49) ?>)
              </span>
            <?php else: ?>
              <span class="badge px-2 py-1" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 11px;">
                <i class="fa fa-info-circle mr-1"></i>Pure COD Active (₹0 Advance)
              </span>
            <?php endif; ?>
          </span>
        </div>
        <p class="text-muted small mb-0" style="font-size: 12.5px; line-height: 1.45;">
          Control whether customers choosing <strong>Cash on Delivery (Pay After Service)</strong> must pay an online confirmation advance (e.g. ₹49) via Razorpay. 
          When <strong>OFF</strong>, customers book pure COD with <strong>₹0 advance</strong>, and customer invoices show the <strong>full service amount without deducting ₹49</strong>.
        </p>
      </div>

      <div class="col-lg-5">
        <form action="<?= \App\Core\View::url('/superadmin/gateways/advance-policy') ?>" method="POST" id="advancePolicyForm" class="bg-white p-3 rounded border">
          <?= \App\Core\View::csrfField() ?>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="font-weight-bold text-dark small mb-0" for="prebookingAdvanceToggle">
              Require Pre-booking Advance:
            </label>
            <div class="d-flex align-items-center" style="gap: 8px;">
              <span class="small font-weight-bold" id="advanceToggleLabel" style="color: <?= !empty($advanceEnabled) ? '#10b981' : '#64748b' ?>;">
                <?= !empty($advanceEnabled) ? 'ENABLED' : 'DISABLED' ?>
              </span>
              <label class="rfx-ios-switch mb-0">
                <input type="checkbox" name="prebooking_advance_enabled" value="1" id="prebookingAdvanceToggle" <?= !empty($advanceEnabled) ? 'checked' : '' ?> onchange="onAdvanceToggleChange(this)">
                <span class="rfx-ios-slider"></span>
              </label>
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between pt-2 border-top">
            <label class="font-weight-bold text-dark small mb-0" for="advanceAmountInput">
              Advance Amount (₹):
            </label>
            <div class="input-group input-group-sm" style="max-width: 140px;">
              <div class="input-group-prepend">
                <span class="input-group-text font-weight-bold">₹</span>
              </div>
              <input type="number" step="1" min="0" max="5000" name="prebooking_advance_amount" id="advanceAmountInput" class="form-control text-right font-weight-bold" value="<?= round($advanceAmount ?? 49) ?>" style="font-family: monospace;">
            </div>
          </div>

          <button type="submit" class="btn btn-sm btn-primary btn-block font-weight-bold mt-2 py-1" style="font-size: 12px; border-radius: 6px;">
            <i class="fa fa-save mr-1"></i> Save Advance Policy
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Gateway Cards -->
  <div class="row">
    <?php foreach ($gateways as $gw): ?>
      <?php 
        $isRazorpay = ($gw['code'] === 'razorpay');
        $isCod = ($gw['code'] === 'cod');
        $cardBorder = $gw['is_active'] ? ($isRazorpay ? '#f25b29' : '#6366f1') : '#cbd5e1';
      ?>
      <div class="col-lg-6 mb-4">
        <div class="sa-card h-100" id="card-gw-<?= $gw['id'] ?>" style="border-top: 3.5px solid <?= $cardBorder ?> !important; transition: border-color 0.25s ease;">
          <div class="p-3 p-sm-4 d-flex flex-column h-100">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
              <div class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0" style="width: 44px; height: 44px; background: <?= $isRazorpay ? '#fff3ef' : '#f0fdf4' ?>; border: 1px solid <?= $isRazorpay ? '#fed7aa' : '#bbf7d0' ?>;">
                  <?php if ($isRazorpay): ?>
                    <i class="fa fa-bolt" style="font-size: 20px; color: #f25b29;"></i>
                  <?php elseif ($isCod): ?>
                    <i class="fa fa-money" style="font-size: 20px; color: #16a34a;"></i>
                  <?php elseif ($gw['code'] === 'phonepe'): ?>
                    <i class="fa fa-qrcode" style="font-size: 20px; color: #6739b6;"></i>
                  <?php else: ?>
                    <i class="fa fa-credit-card" style="font-size: 20px; color: #6366f1;"></i>
                  <?php endif; ?>
                </div>
                <div>
                  <h5 class="mb-0 font-weight-bold text-dark"><?= \App\Core\View::e($gw['name']) ?></h5>
                  <small class="text-muted font-monospace">Provider: <code><?= \App\Core\View::e($gw['code']) ?></code></small>
                </div>
              </div>

              <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                <?php if ($gw['environment'] === 'live'): ?>
                  <span class="badge badge-success px-2 py-1 mr-1" style="font-size: 11px;">LIVE</span>
                <?php else: ?>
                  <span class="badge badge-warning px-2 py-1 mr-1" style="font-size: 11px;">SANDBOX</span>
                <?php endif; ?>

                <span id="gw-badge-<?= $gw['id'] ?>">
                  <?php if ($gw['is_active']): ?>
                    <span class="badge badge-primary px-2 py-1" style="background-color: #6366f1; border: none; font-size: 11px;">ACTIVE</span>
                  <?php else: ?>
                    <span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">INACTIVE</span>
                  <?php endif; ?>
                </span>

                <!-- Instant iOS Toggle Switch -->
                <label class="rfx-ios-switch mb-0 ml-1" title="Click to instantly toggle active status">
                  <input type="checkbox" id="gw-switch-<?= $gw['id'] ?>" <?= $gw['is_active'] ? 'checked' : '' ?> onchange="toggleGateway(<?= $gw['id'] ?>, this, '<?= \App\Core\View::e($gw['name']) ?>', '<?= $gw['code'] ?>')">
                  <span class="rfx-ios-slider"></span>
                </label>
              </div>
            </div>

            <!-- Description -->
            <p class="text-muted small mb-3"><?= \App\Core\View::e($gw['description'] ?? 'No description available.') ?></p>

            <!-- Key Info Box -->
            <div class="bg-light p-3 rounded mb-3 border small">
              <div class="row mb-2">
                <div class="col-12 col-sm-4 text-muted font-weight-bold mb-1 mb-sm-0">Key ID / API Key:</div>
                <div class="col-12 col-sm-8">
                  <div class="d-flex align-items-center">
                    <span class="font-monospace text-dark text-break">
                      <?= !empty($gw['key_id']) ? \App\Core\View::e($gw['key_id']) : '<em class="text-muted">Not Set</em>' ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-12 col-sm-4 text-muted font-weight-bold mb-1 mb-sm-0">Key Secret:</div>
                <div class="col-12 col-sm-8">
                  <div class="d-flex align-items-center">
                    <span class="font-monospace text-dark text-break">
                      <?= !empty($gw['has_key_secret']) ? '<span class="badge badge-success px-2 py-1"><i class="fa fa-lock mr-1"></i>Configured &amp; Secured</span>' : '<em class="text-muted">Not Set</em>' ?>
                    </span>
                  </div>
                </div>
              </div>

              <div class="row mb-2">
                <div class="col-12 col-sm-4 text-muted font-weight-bold mb-1 mb-sm-0">Merchant Name:</div>
                <div class="col-12 col-sm-8 text-dark font-weight-medium">
                  <?= \App\Core\View::e($gw['merchant_name'] ?? 'REFIXEL') ?>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-sm-4 text-muted font-weight-bold mb-1 mb-sm-0">Merchant Email:</div>
                <div class="col-12 col-sm-8 text-dark font-weight-medium text-break">
                  <?= \App\Core\View::e($gw['merchant_email'] ?? 'help@refixel.com') ?>
                </div>
              </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="mt-auto pt-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center flex-wrap gap-2 mb-2 mb-sm-0">
                <!-- Configure Modal Trigger -->
                <button type="button" class="btn btn-sm btn-primary mr-2 font-weight-bold" data-toggle="modal" data-target="#editModal-<?= $gw['id'] ?>">
                  <i class="fa fa-pencil mr-1"></i> Edit Credentials
                </button>

                <!-- Test Connection (For Razorpay) -->
                <?php if ($gw['code'] === 'razorpay'): ?>
                  <form action="<?= \App\Core\View::url('/superadmin/gateways/' . $gw['id'] . '/test') ?>" method="POST" class="d-inline mr-2">
                    <?= \App\Core\View::csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-info font-weight-bold" title="Test Razorpay API connectivity">
                      <i class="fa fa-refresh mr-1"></i> Test Ping
                    </button>
                  </form>
                <?php endif; ?>
              </div>

              <!-- Toggle Active Switch -->
              <form action="<?= \App\Core\View::url('/superadmin/gateways/' . $gw['id'] . '/toggle') ?>" method="POST" class="d-inline">
                <?= \App\Core\View::csrfField() ?>
                <?php if ($gw['is_active']): ?>
                  <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Disable this payment gateway?')">
                    <i class="fa fa-pause mr-1"></i> Deactivate
                  </button>
                <?php else: ?>
                  <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="fa fa-play mr-1"></i> Activate
                  </button>
                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>

        <!-- Edit Modal for Gateway -->
        <div class="modal fade" id="editModal-<?= $gw['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel-<?= $gw['id'] ?>" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
              <form action="<?= \App\Core\View::url('/superadmin/gateways/' . $gw['id'] . '/update') ?>" method="POST">
                <?= \App\Core\View::csrfField() ?>
                
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 100%);">
                  <h5 class="modal-title font-weight-bold" id="modalLabel-<?= $gw['id'] ?>">
                    <i class="fa fa-sliders mr-2" style="color: #6366f1;"></i>Configure <?= \App\Core\View::e($gw['name']) ?>
                  </h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>

                <div class="modal-body p-4">
                  <?php if ($gw['code'] === 'razorpay'): ?>
                    <div class="alert alert-info py-2 small mb-4">
                      <i class="fa fa-info-circle mr-1"></i>
                      <strong>Razorpay Live Credentials:</strong> You can find your Key ID and Key Secret in your Razorpay Dashboard under <em>Settings &gt; API Keys</em>.
                    </div>
                  <?php endif; ?>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Gateway Display Name *</label>
                      <input type="text" name="name" class="form-control" value="<?= \App\Core\View::e($gw['name']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Environment Mode *</label>
                      <select name="environment" class="form-control font-weight-medium">
                        <option value="live" <?= $gw['environment'] === 'live' ? 'selected' : '' ?>>Live / Production</option>
                        <option value="test" <?= $gw['environment'] === 'test' ? 'selected' : '' ?>>Test / Sandbox</option>
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Key ID / API Key *</label>
                      <input type="text" name="key_id" class="form-control font-monospace" value="<?= \App\Core\View::e($gw['key_id'] ?? '') ?>" placeholder="e.g. rzp_live_xxxxxxxxxxxx" required>
                      <small class="form-text text-muted">Public identifier used in front-end checkout SDK.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Key Secret / Secret Key</label>
                      <input type="password" name="key_secret" class="form-control font-monospace" value="" placeholder="<?= !empty($gw['has_key_secret']) ? '•••••••• (Leave blank to keep current)' : 'Enter Secret Key' ?>" autocomplete="new-password">
                      <small class="form-text text-muted">Confidential server secret. Never rendered to browser.</small>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Webhook Secret (Optional)</label>
                      <input type="password" name="webhook_secret" class="form-control font-monospace" value="" placeholder="<?= !empty($gw['has_webhook_secret']) ? '•••••••• (Leave blank to keep current)' : 'Enter Webhook Secret' ?>" autocomplete="new-password">
                      <small class="form-text text-muted">Secret for verifying webhook payloads.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Currency Code</label>
                      <input type="text" name="currency" class="form-control font-monospace" value="<?= \App\Core\View::e($gw['currency'] ?? 'INR') ?>" required>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Merchant Business Name</label>
                      <input type="text" name="merchant_name" class="form-control" value="<?= \App\Core\View::e($gw['merchant_name'] ?? 'REFIXEL') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="font-weight-bold small text-muted">Merchant Support Email</label>
                      <input type="email" name="merchant_email" class="form-control" value="<?= \App\Core\View::e($gw['merchant_email'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="font-weight-bold small text-muted">Gateway Description / Customer Note</label>
                    <textarea name="description" class="form-control" rows="2"><?= \App\Core\View::e($gw['description'] ?? '') ?></textarea>
                  </div>

                  <div class="custom-control custom-switch mt-2">
                    <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="switch-<?= $gw['id'] ?>" <?= $gw['is_active'] ? 'checked' : '' ?>>
                    <label class="custom-control-label font-weight-bold text-dark" for="switch-<?= $gw['id'] ?>">Enable this payment gateway for customer checkout</label>
                  </div>
                </div>

                <div class="modal-footer bg-light">
                  <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4">
                    <i class="fa fa-save mr-1"></i> Save Credentials
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Floating Toast Container -->
<div id="rfxGatewayToast" class="alert alert-dark shadow-lg font-weight-bold" style="display: none; position: fixed; bottom: 25px; right: 25px; z-index: 9999; border-radius: 8px; font-size: 13px; max-width: 360px; background: #0a1c33; color: #ffffff; border: 1px solid #1e293b;">
  <span id="rfxGatewayToastMsg"></span>
</div>

<script>
function showGatewayToast(msg, isSuccess = true) {
  var toast = document.getElementById('rfxGatewayToast');
  var toastMsg = document.getElementById('rfxGatewayToastMsg');
  if (!toast || !toastMsg) return;
  toastMsg.innerHTML = (isSuccess ? '<i class="fa fa-check-circle text-success mr-2"></i>' : '<i class="fa fa-exclamation-triangle text-danger mr-2"></i>') + msg;
  toast.style.display = 'block';
  toast.style.opacity = '1';
  clearTimeout(window._gwToastTimer);
  window._gwToastTimer = setTimeout(function() {
    toast.style.display = 'none';
  }, 3500);
}

function toggleGateway(id, inputEl, name, code) {
  var origChecked = !inputEl.checked;
  inputEl.disabled = true;

  var fd = new FormData();
  fd.append('_csrf', '<?= \App\Core\View::csrfToken() ?>');
  fd.append('is_ajax', '1');

  fetch('<?= \App\Core\View::url("/superadmin/gateways/") ?>' + id + '/toggle', {
    method: 'POST',
    body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    inputEl.disabled = false;
    if (data && data.success) {
      var badge = document.getElementById('gw-badge-' + id);
      var card = document.getElementById('card-gw-' + id) || inputEl.closest('.sa-card');
      if (data.is_active) {
        if (badge) badge.innerHTML = '<span class="badge badge-primary px-2 py-1" style="background-color: #6366f1; border: none; font-size: 11px;">ACTIVE</span>';
        if (card) card.style.borderTopColor = (code === 'razorpay' ? '#f25b29' : '#6366f1');
        showGatewayToast("Gateway '" + name + "' activated successfully.");
      } else {
        if (badge) badge.innerHTML = '<span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">INACTIVE</span>';
        if (card) card.style.borderTopColor = '#cbd5e1';
        showGatewayToast("Gateway '" + name + "' deactivated successfully.");
      }
    } else {
      inputEl.checked = origChecked;
      alert((data && data.message) ? data.message : 'Failed to update gateway status.');
    }
  })
  .catch(function(err) {
    inputEl.disabled = false;
    inputEl.checked = origChecked;
    showGatewayToast('Network error while toggling gateway.', false);
  });
}

function onAdvanceToggleChange(inputEl) {
  var lbl = document.getElementById('advanceToggleLabel');
  if (lbl) {
    lbl.innerText = inputEl.checked ? 'ENABLED' : 'DISABLED';
    lbl.style.color = inputEl.checked ? '#10b981' : '#64748b';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var advForm = document.getElementById('advancePolicyForm');
  if (advForm) {
    advForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var submitBtn = advForm.querySelector('button[type="submit"]');
      var origHtml = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Saving...';

      var fd = new FormData(advForm);
      fd.append('is_ajax', '1');

      fetch(advForm.getAttribute('action'), {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origHtml;

        if (data && data.success) {
          var badge = document.getElementById('advancePolicyStatusBadge');
          if (badge) {
            if (data.enabled) {
              badge.innerHTML = '<span class="badge badge-success px-2 py-1" style="font-size: 11px;"><i class="fa fa-check-circle mr-1"></i>Advance Requirement Active (₹' + Math.round(data.amount) + ')</span>';
            } else {
              badge.innerHTML = '<span class="badge px-2 py-1" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 11px;"><i class="fa fa-info-circle mr-1"></i>Pure COD Active (₹0 Advance)</span>';
            }
          }
          showGatewayToast(data.message);
        } else {
          alert('Failed to save policy.');
        }
      })
      .catch(function(err) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origHtml;
        showGatewayToast('Network error while saving advance policy.', false);
      });
    });
  }
});
</script>
