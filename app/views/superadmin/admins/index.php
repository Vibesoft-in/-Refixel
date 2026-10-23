<div class="container-fluid p-0">
  <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4 pb-3 border-bottom">
    <div class="mb-3 mb-lg-0 mr-lg-3">
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-user-secret text-danger mr-2"></i>Admin Accounts Governance
      </h3>
      <p class="text-muted small mb-0">Super Admin exclusive control center: Monitor account IDs, inspect & reveal admin passwords, reset credentials, and provision or revoke administrators.</p>
    </div>
    <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
      <button type="button" class="btn btn-danger font-weight-bold shadow-sm" style="margin-right: 10px; margin-bottom: 6px;" data-toggle="modal" data-target="#modalDeleteAllAdmins" <?= empty($opsAdminCount) ? 'disabled' : '' ?>>
        <i class="fa fa-trash mr-1"></i> Delete All Admins (<?= $opsAdminCount ?? 0 ?>)
      </button>
      <button type="button" class="btn btn-primary font-weight-bold shadow-sm" style="margin-bottom: 6px;" data-toggle="modal" data-target="#modalCreateAdmin">
        <i class="fa fa-plus-circle mr-1"></i> Provision Admin Account
      </button>
    </div>
  </div>

  <div class="alert alert-info border d-flex align-items-start mb-4 p-3" style="border-radius: 8px; background: #eef2ff; border-color: #c7d2fe !important;">
    <i class="fa fa-shield fa-2x text-primary mr-3 mt-1" style="color: #6366f1 !important;"></i>
    <div>
      <h6 class="font-weight-bold text-dark mb-1">Exclusive Administrator Provisioning & Credentials Visibility Policy</h6>
      <p class="small text-muted mb-0">
        Public registration for administrative accounts is strictly prohibited. <strong>Only logged-in Super Admins</strong> can provision accounts, inspect login IDs, reveal cleartext passwords via the <i class="fa fa-eye text-primary"></i> toggle, copy full login credentials, or update access passwords.
      </p>
    </div>
  </div>

  <div class="sa-card shadow-sm border" style="border-radius: 10px; overflow: hidden; background: #ffffff;">
    <div class="table-responsive">
      <table class="table table-hover mb-0 sa-table-min-lg align-middle">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">Account ID</th>
            <th style="white-space: nowrap;">Administrator</th>
            <th style="white-space: nowrap;">Role Tier</th>
            <th style="white-space: nowrap;">Login Identifier (Email / Phone)</th>
            <th style="white-space: nowrap;">Password Security</th>
            <th style="white-space: nowrap;">2FA Security</th>
            <th style="white-space: nowrap;">Status</th>
            <th style="white-space: nowrap;">Last Login</th>
            <th class="text-right" style="white-space: nowrap;">Governance Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $a): ?>
            <tr>
              <!-- Account ID -->
              <td class="font-weight-bold text-dark" style="white-space: nowrap; vertical-align: middle;">
                <span class="badge badge-light border text-dark px-2 py-1" style="font-size: 13px; font-family: monospace;">
                  #<?= $a['id'] ?>
                </span>
                <button type="button" class="btn btn-xs btn-link text-muted p-0 ml-1" onclick="copyToClipboard('<?= $a['id'] ?>', this, 'ID Copied!')" title="Copy Account ID">
                  <i class="fa fa-copy"></i>
                </button>
              </td>

              <!-- Administrator Details -->
              <td style="vertical-align: middle;">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold mr-2" style="width: 34px; height: 34px; background: <?= $a['role'] === 'super_admin' ? '#ef4444' : '#6366f1' ?>; font-size: 13px; flex-shrink: 0;">
                    <?= strtoupper(substr($a['name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <div>
                    <div class="font-weight-bold text-dark text-truncate" style="max-width: 170px;"><?= \App\Core\View::e($a['name']) ?></div>
                    <div class="text-muted small">Registered <?= date('d M Y', strtotime($a['created_at'])) ?></div>
                  </div>
                </div>
              </td>

              <!-- Role Tier -->
              <td style="white-space: nowrap; vertical-align: middle;">
                <?php if ($a['role'] === 'super_admin'): ?>
                  <span class="badge badge-danger font-weight-bold px-2 py-1"><i class="fa fa-shield mr-1"></i>SUPER ADMIN</span>
                <?php else: ?>
                  <span class="badge badge-primary font-weight-bold px-2 py-1"><i class="fa fa-user-circle mr-1"></i>OPERATIONS ADMIN</span>
                <?php endif; ?>
              </td>

              <!-- Login Identifier -->
              <td style="vertical-align: middle;">
                <div>
                  <?php if (!empty($a['email'])): ?>
                    <div class="d-flex align-items-center mb-1">
                      <i class="fa fa-envelope-o text-muted mr-1 small"></i>
                      <span class="text-dark font-weight-500 mr-2"><?= \App\Core\View::e($a['email']) ?></span>
                      <button type="button" class="btn btn-xs btn-link text-muted p-0" onclick="copyToClipboard('<?= \App\Core\View::e($a['email']) ?>', this, 'Email Copied!')" title="Copy Login Email">
                        <i class="fa fa-copy"></i>
                      </button>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($a['phone'])): ?>
                    <div class="d-flex align-items-center text-muted small">
                      <i class="fa fa-phone text-muted mr-1 small"></i>
                      <span><?= \App\Core\View::e($a['phone']) ?></span>
                      <button type="button" class="btn btn-xs btn-link text-muted p-0 ml-1" onclick="copyToClipboard('<?= \App\Core\View::e($a['phone']) ?>', this, 'Phone Copied!')" title="Copy Phone">
                        <i class="fa fa-copy"></i>
                      </button>
                    </div>
                  <?php endif; ?>
                  <?php if (empty($a['email']) && empty($a['phone'])): ?>
                    <span class="text-muted small font-italic">No contact set</span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- Password Security Status -->
              <td style="white-space: nowrap; vertical-align: middle;">
                <span class="badge badge-secondary px-2 py-1" title="Passwords are cryptographically hashed and never stored or displayed in plain text">
                  <i class="fa fa-shield mr-1"></i>Bcrypt Hashed
                </span>
              </td>

              <!-- 2FA Security -->
              <td style="white-space: nowrap; vertical-align: middle;">
                <?php if (!empty($a['totp_enabled'])): ?>
                  <span class="badge badge-success px-2 py-1" title="2FA Active (Confirmed: <?= \App\Core\View::e($a['totp_confirmed_at'] ?? 'N/A') ?>)">
                    <i class="fa fa-shield mr-1"></i>Active (TOTP)
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-warning ml-1" data-toggle="modal" data-target="#modalReset2fa<?= $a['id'] ?>" title="Emergency 2FA Reset">
                    <i class="fa fa-refresh mr-1"></i>Reset 2FA
                  </button>
                <?php else: ?>
                  <span class="badge badge-warning px-2 py-1" style="background: #fef3c7; color: #92400e;">
                    <i class="fa fa-info-circle mr-1"></i>Optional (Not Enabled)
                  </span>
                <?php endif; ?>
              </td>

              <!-- Status -->
              <td style="white-space: nowrap; vertical-align: middle;">
                <span class="badge badge-success px-2 py-1"><?= ucfirst($a['status']) ?></span>
              </td>

              <!-- Last Login -->
              <td class="text-muted small" style="white-space: nowrap; vertical-align: middle;">
                <?= !empty($a['last_login_at']) ? date('d M Y, h:i A', strtotime($a['last_login_at'])) : '<span class="font-italic">Never</span>' ?>
              </td>

              <!-- Governance Actions -->
              <td class="text-right" style="white-space: nowrap; vertical-align: middle;">
                <?php if ($a['role'] !== 'super_admin'): ?>
                  <?php
                    $fullCreds = "REFIXEL Administrator Profile\nAccount ID: #" . $a['id'] . "\nName: " . $a['name'] . "\nLogin Email: " . ($a['email'] ?? 'N/A') . "\nMobile Phone: " . ($a['phone'] ?? 'N/A') . "\nSign-in URL: " . \App\Core\View::fullUrl('/login');
                  ?>
                  <button type="button" class="btn btn-xs btn-outline-secondary mr-1" onclick="copyToClipboard(<?= json_encode($fullCreds) ?>, this, 'Profile Info Copied!')" title="Copy Login Profile Info (ID, Email, Phone)">
                    <i class="fa fa-copy mr-1"></i>Copy Info
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-primary mr-1" data-toggle="modal" data-target="#modalResetPass<?= $a['id'] ?>" title="Edit / Reset Admin Password">
                    <i class="fa fa-key mr-1"></i>Edit Password
                  </button>
                  <form action="<?= \App\Core\View::url('/superadmin/admins/' . $a['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Revoke and delete Operations Admin account for <?= \App\Core\View::e($a['name']) ?> (ID: #<?= $a['id'] ?>)?');">
                    <?= \App\Core\View::csrfField() ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Revoke Admin Access">
                      <i class="fa fa-trash"></i> Revoke
                    </button>
                  </form>
                <?php else: ?>
                  <?php if ($a['id'] === \App\Core\Auth::id()): ?>
                    <span class="badge badge-light border text-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i>You (Active Session)</span>
                  <?php else: ?>
                    <span class="text-muted small font-italic">Protected Root</span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modals for Resetting / Updating Admin Passwords -->
<?php foreach ($admins as $a): ?>
  <?php if ($a['role'] !== 'super_admin' || $a['id'] === \App\Core\Auth::id()): ?>
    <div class="modal fade" id="modalResetPass<?= $a['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title font-weight-bold" style="font-size: 16px;">
              <i class="fa fa-key text-warning mr-2"></i>Update Admin Password & Credentials
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
          </div>
          <form action="<?= \App\Core\View::url('/superadmin/admins/' . $a['id'] . '/reset-password') ?>" method="POST">
            <?= \App\Core\View::csrfField() ?>
            <div class="modal-body p-4">
              <!-- Admin Account Metadata Card -->
              <div class="card bg-light border-0 mb-3 p-3" style="border-radius: 8px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="text-muted small">Account ID:</span>
                  <span class="font-weight-bold font-monospace text-dark">#<?= $a['id'] ?></span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="text-muted small">Admin Name:</span>
                  <span class="font-weight-bold text-dark"><?= \App\Core\View::e($a['name']) ?></span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <span class="text-muted small">Login Email:</span>
                  <span class="font-weight-bold text-dark"><?= \App\Core\View::e($a['email'] ?? 'N/A') ?></span>
                </div>
                <?php if (!empty($a['phone'])): ?>
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Mobile Phone:</span>
                    <span class="font-weight-bold text-dark"><?= \App\Core\View::e($a['phone']) ?></span>
                  </div>
                <?php endif; ?>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                  <span class="text-muted small">Password Security:</span>
                  <span class="badge badge-light border text-muted px-2 py-1 font-weight-bold">
                    <i class="fa fa-lock mr-1 text-success"></i>Bcrypt Hash Protected (No Plaintext Stored)
                  </span>
                </div>
              </div>

              <!-- New Password Input -->
              <div class="form-group mb-2">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <label class="small font-weight-bold text-dark mb-0">Set New Password *</label>
                  <button type="button" class="btn btn-xs btn-link text-primary font-weight-bold p-0" onclick="generatePasswordForInput('inputNewPass<?= $a['id'] ?>')">
                    <i class="fa fa-magic mr-1"></i>Generate Strong Password
                  </button>
                </div>
                <div class="input-group">
                  <input type="text" name="password" id="inputNewPass<?= $a['id'] ?>" class="form-control font-monospace" placeholder="Minimum 6 characters" required minlength="6">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary btn-toggle-input-pass" onclick="toggleInputVisibility(this, 'inputNewPass<?= $a['id'] ?>')">
                      <i class="fa fa-eye"></i>
                    </button>
                  </div>
                </div>
                <small class="form-text text-muted">Setting a new password will securely hash it using Bcrypt and invalidate previous credentials. Plaintext passwords are never stored.</small>
              </div>

              <?php if (!empty($a['email'])): ?>
                <div class="alert alert-light border small text-muted mt-3 mb-0 p-2">
                  <i class="fa fa-envelope-o text-primary mr-1"></i>
                  An automated email with a secure password notification will be sent to <strong><?= \App\Core\View::e($a['email']) ?></strong> upon submission.
                </div>
              <?php endif; ?>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between">
              <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary font-weight-bold px-4">
                <i class="fa fa-check mr-1"></i> Save Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<!-- Modals for Resetting Admin 2FA with Password Re-authentication -->
<?php foreach ($admins as $a): ?>
  <?php if (!empty($a['totp_enabled'])): ?>
    <div class="modal fade" id="modalReset2fa<?= $a['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title font-weight-bold" style="font-size: 16px;">
              <i class="fa fa-shield text-warning mr-2"></i>Reset Two-Factor Authentication (2FA)
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
          </div>
          <form action="<?= \App\Core\View::url('/superadmin/admins/' . $a['id'] . '/reset-2fa') ?>" method="POST">
            <?= \App\Core\View::csrfField() ?>
            <div class="modal-body p-4">
              <div class="alert alert-warning small mb-3">
                <i class="fa fa-exclamation-triangle mr-1"></i>
                You are about to reset 2FA for <strong><?= \App\Core\View::e($a['name']) ?></strong> (#<?= $a['id'] ?>).
                Their existing authenticator secret and all emergency recovery codes will be immediately purged.
              </div>
              <div class="form-group mb-0">
                <label class="font-weight-bold text-dark small">Super Admin Password (Re-authentication required):</label>
                <input type="password" name="superadmin_password" class="form-control" placeholder="Enter YOUR Super Admin password to authorize" required>
                <small class="form-text text-muted">For security, you must re-enter your password to execute a 2FA reset.</small>
              </div>
            </div>
            <div class="modal-footer bg-light p-3">
              <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
                <i class="fa fa-refresh mr-1"></i> Authorize & Reset 2FA
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

<!-- Modal: Create Admin Account -->
<div class="modal fade" id="modalCreateAdmin" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-user-plus mr-2"></i>Provision New Administrative User</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/admins') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4">
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Operations Manager" required>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Login Email Address *</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. manager@refixel.com" required>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Mobile Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210">
          </div>
          <div class="form-group">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="small font-weight-bold text-dark mb-0">Initial Password *</label>
              <button type="button" class="btn btn-xs btn-link text-primary font-weight-bold p-0" onclick="generatePasswordForInput('inputCreateAdminPass')">
                <i class="fa fa-magic mr-1"></i>Generate Password
              </button>
            </div>
            <div class="input-group">
              <input type="text" name="password" id="inputCreateAdminPass" class="form-control font-monospace" placeholder="Minimum 6 characters" required minlength="6">
              <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary btn-toggle-input-pass" onclick="toggleInputVisibility(this, 'inputCreateAdminPass')">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
            </div>
            <small class="form-text text-muted">This initial password will be securely hashed with Bcrypt immediately. Plaintext passwords are never stored or displayed.</small>
          </div>
          <div class="form-group mb-0">
            <label class="small font-weight-bold text-dark">Role Authorization Tier</label>
            <select name="role" class="form-control">
              <option value="admin" selected>Standard Admin (Operations, Bookings, Staff, CMS)</option>
              <option value="super_admin">Super Admin (Root access: Gateways, Commissions, Purge)</option>
            </select>
            <small class="form-text text-muted">Standard Admins cannot touch payment secrets or delete databases.</small>
          </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4">
            <i class="fa fa-check mr-1"></i> Provision Account
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleAdminPassword(btn) {
  var container = btn.closest('div');
  if (!container) return;
  var mask = container.querySelector('.pwd-mask');
  var text = container.querySelector('.pwd-text');
  var icon = btn.querySelector('i');
  if (!mask || !text || !icon) return;

  if (text.classList.contains('d-none')) {
    text.classList.remove('d-none');
    mask.classList.add('d-none');
    icon.className = 'fa fa-eye-slash text-danger';
    btn.setAttribute('title', 'Hide Password');
  } else {
    text.classList.add('d-none');
    mask.classList.remove('d-none');
    icon.className = 'fa fa-eye';
    btn.setAttribute('title', 'Show Password');
  }
}

function copyToClipboard(text, btn, successMsg) {
  if (!text) return;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text);
  } else {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  }

  var origHtml = btn.innerHTML;
  btn.innerHTML = '<i class="fa fa-check text-success"></i> ' + (successMsg || 'Copied!');
  btn.classList.add('btn-light');
  setTimeout(function() {
    btn.innerHTML = origHtml;
    btn.classList.remove('btn-light');
  }, 2200);
}

function generatePasswordForInput(inputId) {
  var charset = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
  var arr = new Uint8Array(14);
  window.crypto.getRandomValues(arr);
  var pass = '';
  for (var i = 0; i < arr.length; i++) {
    pass += charset[arr[i] % charset.length];
  }
  var input = document.getElementById(inputId);
  if (input) {
    input.value = pass;
    input.type = 'text';
    var toggleBtn = input.parentElement.querySelector('.btn-toggle-input-pass i');
    if (toggleBtn) {
      toggleBtn.className = 'fa fa-eye-slash text-danger';
    }
  }
}

function toggleInputVisibility(btn, inputId) {
  var input = document.getElementById(inputId);
  if (!input) return;
  var icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) icon.className = 'fa fa-eye-slash text-danger';
  } else {
    input.type = 'password';
    if (icon) icon.className = 'fa fa-eye';
  }
}
</script>

<!-- Modal: Delete All Operations Admins -->
<div class="modal fade" id="modalDeleteAllAdmins" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-user-times mr-2"></i>Delete All Operations Admins?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/admins/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-exclamation-triangle fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all Operations Admin accounts?</h5>
          <p class="text-muted small mb-3">
            This will permanently remove all <strong><?= $opsAdminCount ?? 0 ?> Operations Admin accounts</strong> from the database.
          </p>
          <div class="alert alert-success border small mb-0 text-left" style="background: #ecfdf5; border-color: #a7f3d0 !important;">
            <i class="fa fa-shield text-success mr-1"></i> <strong>Strict Protection:</strong> Super Admin accounts (<code>super_admin</code>) are completely immune and will <strong>NOT</strong> be deleted.
          </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Admins
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
