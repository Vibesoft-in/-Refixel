<h3 class="text-center font-weight-bold mb-3" style="color:#13221e;">Create New Password</h3>
<p class="text-muted text-center small mb-4">Please choose a strong password with at least 6 characters.</p>

<form action="<?= \App\Core\View::url('/reset-password') ?>" method="POST">
  <?= \App\Core\View::csrfField() ?>
  <input type="hidden" name="token" value="<?= \App\Core\View::e($token) ?>">
  <div class="form-group mb-3">
    <label class="font-weight-600">New Password</label>
    <div class="input-group">
      <input type="password" name="password" id="resetPasswordInput" class="form-control" placeholder="••••••••" style="border-right: none;" required autofocus>
      <div class="input-group-append">
        <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('resetPasswordInput', this)" title="Show/Hide password">
          <i class="fa fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
  <div class="form-group mb-4">
    <label class="font-weight-600">Confirm New Password</label>
    <div class="input-group">
      <input type="password" name="password_confirmation" id="resetConfirmPassInput" class="form-control" placeholder="••••••••" style="border-right: none;" required>
      <div class="input-group-append">
        <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('resetConfirmPassInput', this)" title="Show/Hide password">
          <i class="fa fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary-custom">Update Password</button>
</form>
