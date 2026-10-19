<h3 class="text-center font-weight-bold mb-3" style="color:#13221e;">Update Your Password</h3>
<?php if (!empty($user['must_change_password'])): ?>
  <div class="alert alert-info py-2 small mb-3">
    <strong>First-time Login:</strong> Please change your temporary password before accessing the field workforce dashboard.
  </div>
<?php endif; ?>

<form action="<?= \App\Core\View::url('/change-password') ?>" method="POST">
  <?= \App\Core\View::csrfField() ?>

  <?php if (empty($user['must_change_password'])): ?>
    <div class="form-group mb-3">
      <label class="font-weight-600">Current Password</label>
      <div class="input-group">
        <input type="password" name="current_password" id="currentPassInput" class="form-control" placeholder="••••••••" style="border-right: none;" required>
        <div class="input-group-append">
          <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('currentPassInput', this)" title="Show/Hide password">
            <i class="fa fa-eye"></i>
          </button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="form-group mb-3">
    <label class="font-weight-600">New Password</label>
    <div class="input-group">
      <input type="password" name="new_password" id="newPassInput" class="form-control" placeholder="Minimum 6 characters" style="border-right: none;" required>
      <div class="input-group-append">
        <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('newPassInput', this)" title="Show/Hide password">
          <i class="fa fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
  <div class="form-group mb-4">
    <label class="font-weight-600">Confirm New Password</label>
    <div class="input-group">
      <input type="password" name="new_password_confirmation" id="newConfirmPassInput" class="form-control" placeholder="Repeat new password" style="border-right: none;" required>
      <div class="input-group-append">
        <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('newConfirmPassInput', this)" title="Show/Hide password">
          <i class="fa fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary-custom">Save & Continue</button>
</form>
