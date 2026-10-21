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

  <!-- Gateway Cards -->
  <div class="row">
    <?php foreach ($gateways as $gw): ?>
      <?php 
        $isRazorpay = ($gw['code'] === 'razorpay');
        $isCod = ($gw['code'] === 'cod');
        $cardBorder = $gw['is_active'] ? ($isRazorpay ? '#f25b29' : '#6366f1') : '#cbd5e1';
      ?>
      <div class="col-lg-6 mb-4">
        <div class="sa-card h-100" style="border-top: 3.5px solid <?= $cardBorder ?> !important;">
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

              <div class="d-flex align-items-center flex-wrap gap-1">
                <?php if ($gw['environment'] === 'live'): ?>
                  <span class="badge badge-success px-2 py-1 mr-1" style="font-size: 11px;">LIVE</span>
                <?php else: ?>
                  <span class="badge badge-warning px-2 py-1 mr-1" style="font-size: 11px;">SANDBOX</span>
                <?php endif; ?>

                <?php if ($gw['is_active']): ?>
                  <span class="badge badge-primary px-2 py-1" style="background-color: #6366f1; border: none; font-size: 11px;">ACTIVE</span>
                <?php else: ?>
                  <span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">INACTIVE</span>
                <?php endif; ?>
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
