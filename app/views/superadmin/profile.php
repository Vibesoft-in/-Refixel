<?php
use App\Core\Auth;
use App\Core\View;

$totpActive = !empty($user['totp_enabled']);
?>

<div class="container-fluid px-0">
  <!-- Page Header -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
      <div class="text-muted small mb-1">
        <a href="<?= View::url('/superadmin') ?>" class="text-decoration-none text-muted">Super Admin</a> &rsaquo;
        <span class="text-dark font-weight-500">Account Security & Profile</span>
      </div>
      <h2 class="font-weight-bold mb-1" style="color: #0f172a; font-size: 24px;">
        <i class="fa fa-user-circle text-primary mr-2"></i>Profile & Security Controls
      </h2>
      <p class="text-muted small mb-0">Manage root administrator identity, security credentials, and two-factor authentication.</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex align-items-center">
      <span class="badge badge-danger px-3 py-2 mr-2" style="font-size: 11.5px; letter-spacing: 0.5px;">
        <i class="fa fa-shield mr-1"></i>ROOT PRIVILEGES
      </span>
      <a href="<?= View::url('/superadmin') ?>" class="btn btn-sm btn-outline-secondary font-weight-500 px-3 py-2" style="border-radius: 6px;">
        <i class="fa fa-arrow-left mr-1"></i> Control Center
      </a>
    </div>
  </div>

  <div class="row">
    <!-- Left Column: Identity & 2FA Status -->
    <div class="col-lg-4 mb-4">
      <!-- Identity Overview Card -->
      <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); padding: 32px 20px 24px; text-align: center; color: #fff;">
          <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 76px; height: 76px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); border: 3px solid rgba(255, 255, 255, 0.35); box-shadow: 0 4px 14px rgba(0,0,0,0.25);">
            <i class="fa fa-user-secret" style="font-size: 36px; color: #ffffff;"></i>
          </div>
          <h4 class="font-weight-bold mb-1" style="font-size: 19px;"><?= View::e($user['name'] ?? 'Super Admin') ?></h4>
          <div class="badge badge-danger px-3 py-1 font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px; border-radius: 12px;">
            SUPER ADMINISTRATOR
          </div>
        </div>
        <div class="card-body p-3">
          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-hashtag mr-1"></i> Account ID</span>
              <span class="font-weight-bold text-dark">#<?= (int)$user['id'] ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-envelope-o mr-1"></i> Email</span>
              <span class="font-weight-bold text-dark text-truncate" style="max-width: 170px;" title="<?= View::e($user['email'] ?? '') ?>"><?= View::e($user['email'] ?? 'Not Set') ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-phone mr-1"></i> Phone</span>
              <span class="font-weight-bold text-dark"><?= View::e($user['phone'] ?? 'Not Set') ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-check-circle mr-1 text-success"></i> Account Status</span>
              <span class="badge badge-success px-2 py-1"><?= ucfirst(View::e($user['status'] ?? 'active')) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-calendar-check-o mr-1"></i> Member Since</span>
              <span class="text-muted"><?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'N/A' ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-clock-o mr-1"></i> Last Login</span>
              <span class="text-muted"><?= !empty($user['last_login_at']) ? date('d M Y, h:i A', strtotime($user['last_login_at'])) : '<span class="font-italic">Never</span>' ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="text-muted"><i class="fa fa-history mr-1"></i> Password Version</span>
              <span class="badge badge-secondary">v<?= (int)($user['password_version'] ?? 1) ?></span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Two-Factor Authentication (2FA) Status Card -->
      <div class="card border-0 shadow-sm" id="two-factor" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
          <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 15px;">
            <i class="fa fa-shield mr-2 text-warning"></i>2FA Authentication
          </h5>
          <?php if ($totpActive): ?>
            <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>Active</span>
          <?php else: ?>
            <span class="badge badge-warning px-2 py-1" style="background: #fef3c7; color: #92400e;">Optional (Off)</span>
          <?php endif; ?>
        </div>
        <div class="card-body p-3">
          <?php if ($totpActive): ?>
            <div class="d-flex align-items-start mb-3">
              <div class="mr-3 text-success" style="font-size: 24px;">
                <i class="fa fa-lock"></i>
              </div>
              <div class="small">
                <strong class="d-block text-dark font-weight-bold mb-1">Authenticator App Active</strong>
                <span class="text-muted">Your account requires a 6-digit TOTP code during login.</span>
                <?php if (!empty($user['totp_confirmed_at'])): ?>
                  <div class="text-muted mt-1" style="font-size: 11px;">
                    Confirmed: <?= date('d M Y, h:i A', strtotime($user['totp_confirmed_at'])) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-block font-weight-bold" data-toggle="modal" data-target="#disable2faModal" style="border-radius: 6px;">
              <i class="fa fa-power-off mr-1"></i> Disable 2FA Security
            </button>
          <?php else: ?>
            <p class="small text-muted mb-3" style="line-height: 1.5;">
              Two-Factor Authentication is currently not enabled on this account. You can freely use your dashboard, but enabling 2FA is recommended for root administrative security.
            </p>
            <a href="<?= View::url('/2fa/setup') ?>" class="btn btn-sm btn-warning btn-block font-weight-bold text-dark" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
              <i class="fa fa-shield mr-1"></i> Set Up 2FA Now
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right Column: Edit Forms -->
    <div class="col-lg-8">
      <!-- Card 1: Profile & Contact Details -->
      <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
          <i class="fa fa-id-card-o text-primary mr-2" style="font-size: 18px;"></i>
          <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 16px;">Personal & Contact Details</h5>
        </div>
        <div class="card-body p-4">
          <form action="<?= View::url('/superadmin/profile') ?>" method="POST">
            <?= View::csrfField() ?>

            <div class="form-row">
              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Full Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fa fa-user text-muted"></i></span>
                  </div>
                  <input type="text" name="name" class="form-control" value="<?= View::e($user['name'] ?? '') ?>" required minlength="2" maxlength="100" placeholder="e.g. Master Super Admin">
                </div>
              </div>

              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Email Address <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fa fa-envelope text-muted"></i></span>
                  </div>
                  <input type="email" name="email" class="form-control" value="<?= View::e($user['email'] ?? '') ?>" required placeholder="superadmin@refixel.com">
                </div>
                <small class="form-text text-muted">Used for critical system notifications and superadmin sign-in.</small>
              </div>
            </div>

            <div class="form-row">
              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Mobile Phone Number</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0 font-weight-bold text-muted">+91</span>
                  </div>
                  <input type="tel" name="phone" class="form-control" value="<?= View::e($user['phone'] ?? '') ?>" placeholder="10-digit mobile number" maxlength="15">
                </div>
                <small class="form-text text-muted">Optional secondary sign-in identifier.</small>
              </div>

              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Platform Role & Access</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fa fa-lock text-muted"></i></span>
                  </div>
                  <input type="text" class="form-control bg-light font-weight-bold text-danger" value="Super Administrator (Root)" readonly disabled>
                </div>
                <small class="form-text text-muted">Role cannot be altered from self-service profile.</small>
              </div>
            </div>

            <div class="text-right pt-2 border-top mt-3">
              <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 6px;">
                <i class="fa fa-save mr-1"></i> Update Profile Details
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Card 2: Change Password -->
      <div class="card border-0 shadow-sm mb-4" id="password" style="border-radius: 12px;">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
          <div class="d-flex align-items-center">
            <i class="fa fa-key text-warning mr-2" style="font-size: 18px;"></i>
            <h5 class="font-weight-bold mb-0 text-dark" style="font-size: 16px;">Change Super Admin Password</h5>
          </div>
          <span class="badge badge-light border text-muted px-2 py-1 small">Bcrypt 256-bit</span>
        </div>
        <div class="card-body p-4">
          <form action="<?= View::url('/superadmin/profile/password') ?>" method="POST">
            <?= View::csrfField() ?>

            <!-- Current Password -->
            <div class="form-group mb-3">
              <label class="font-weight-bold small text-dark">Current Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" name="current_password" id="curPass" class="form-control" placeholder="Enter your current password" style="border-right: none;" required>
                <div class="input-group-append">
                  <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('curPass', this)" title="Show/Hide password" style="border-left: none; background: #fff;">
                    <i class="fa fa-eye text-muted"></i>
                  </button>
                </div>
              </div>
              <small class="form-text text-muted">Required to authorize changes to administrative credentials.</small>
            </div>

            <!-- New Password -->
            <div class="form-row">
              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" name="new_password" id="newPass" class="form-control" placeholder="Minimum 6 characters" minlength="6" style="border-right: none;" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('newPass', this)" title="Show/Hide password" style="border-left: none; background: #fff;">
                      <i class="fa fa-eye text-muted"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Confirm New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" name="new_password_confirmation" id="newPassConf" class="form-control" placeholder="Re-enter new password" minlength="6" style="border-right: none;" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('newPassConf', this)" title="Show/Hide password" style="border-left: none; background: #fff;">
                      <i class="fa fa-eye text-muted"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="alert alert-light border small text-muted mb-3 py-2 px-3" style="border-radius: 8px;">
              <i class="fa fa-info-circle text-info mr-1"></i>
              <strong>Security Policy:</strong> Password must be at least 6 characters long. Updating your password automatically invalidates older sessions and increments your security version.
            </div>

            <div class="text-right pt-2 border-top">
              <button type="submit" class="btn btn-warning font-weight-bold px-4 py-2 text-dark" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <i class="fa fa-key mr-1"></i> Save New Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Disable 2FA Confirmation -->
<?php if ($totpActive): ?>
<div class="modal fade" id="disable2faModal" tabindex="-1" role="dialog" aria-labelledby="disable2faLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow" style="border-radius: 12px;">
      <div class="modal-header bg-danger text-white py-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <h5 class="modal-title font-weight-bold" id="disable2faLabel" style="font-size: 16px;">
          <i class="fa fa-exclamation-triangle mr-2"></i>Disable Two-Factor Authentication
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= View::url('/superadmin/profile/disable-2fa') ?>" method="POST">
        <?= View::csrfField() ?>
        <div class="modal-body p-4">
          <p class="text-dark small mb-3">
            You are about to remove Two-Factor Authentication (TOTP) from your Super Administrator account. You will be able to log in using only your email/phone and password.
          </p>
          <div class="form-group mb-2">
            <label class="font-weight-bold small text-dark">Confirm Super Admin Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="Enter your current password" required autofocus>
            <small class="form-text text-muted">For security, enter your current password to authorize this action.</small>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
          <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-danger font-weight-bold">
            <i class="fa fa-unlock mr-1"></i> Authorize & Disable 2FA
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
