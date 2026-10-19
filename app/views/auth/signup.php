<h3 class="text-center font-weight-bold mb-2" style="color:#0a1c33;">Create Customer Account</h3>
<p class="text-center text-muted small mb-4">Create your free REFIXEL account in 30 seconds to view services and book verified technicians.</p>

<form action="<?= \App\Core\View::url('/signup') ?>" method="POST">
  <?= \App\Core\View::csrfField() ?>
  <?php 
    $redirectVal = $_POST['redirect'] ?? ($_GET['redirect'] ?? ($redirect ?? ''));
    if (!empty($redirectVal)): 
  ?>
    <input type="hidden" name="redirect" value="<?= \App\Core\View::e($redirectVal) ?>">
  <?php endif; ?>

  <div class="form-group mb-3">
    <label class="font-weight-600" style="color: #334155; font-size: 13.5px;">Full Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" placeholder="e.g. Ananya Sharma" required autofocus>
  </div>
  <div class="form-group mb-3">
    <label class="font-weight-600" style="color: #334155; font-size: 13.5px;">Mobile Number <span class="text-danger">*</span></label>
    <div class="input-group">
      <div class="input-group-prepend"><span class="input-group-text bg-white" style="color: #475569; font-weight: 600;">+91</span></div>
      <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile number" pattern="[6-9][0-9]{9}" required>
    </div>
  </div>
  <div class="form-group mb-3">
    <label class="font-weight-600" style="color: #334155; font-size: 13.5px;">Email (Optional for GST Invoice)</label>
    <input type="email" name="email" class="form-control" placeholder="ananya@example.com">
  </div>
  <div class="form-group mb-3">
    <label class="font-weight-600" style="color: #334155; font-size: 13.5px;">Password <span class="text-danger">*</span></label>
    <div class="input-group">
      <input type="password" name="password" id="signupPasswordInput" class="form-control" placeholder="Minimum 6 characters" style="border-right: none;" required>
      <div class="input-group-append">
        <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('signupPasswordInput', this)" title="Show/Hide password">
          <i class="fa fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
  <div class="form-group mb-4">
    <label class="font-weight-600" style="color: #334155; font-size: 13.5px;">Confirm Password <span class="text-danger">*</span></label>
    <div class="input-group">
      <input type="password" name="password_confirmation" id="signupConfirmPassInput" class="form-control" placeholder="Repeat password" style="border-right: none;" required>
      <div class="input-group-append">
        <button type="button" class="btn password-toggle-btn" onclick="togglePasswordVisibility('signupConfirmPassInput', this)" title="Show/Hide password">
          <i class="fa fa-eye"></i>
        </button>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary-custom py-2">Create Account & Continue &rarr;</button>
</form>

<div class="text-center mt-4">
  <p class="text-muted mb-0 small">Already have an account? <a href="<?= \App\Core\View::url('/login' . (!empty($redirectVal) ? '?redirect=' . urlencode($redirectVal) : '')) ?>" style="color:#f25b29; font-weight:700;">Sign In</a></p>
</div>
