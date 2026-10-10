<h3 class="text-center font-weight-bold mb-2" style="color:#0a1c33;">Sign In to REFIXEL</h3>
<p class="text-center text-muted small mb-4">Please log in to view service details, custom pricing, and checklists.</p>

<form action="<?= \App\Core\View::url('/login') ?>" method="POST">
  <?= \App\Core\View::csrfField() ?>
  <?php 
    $redirectVal = $_POST['redirect'] ?? ($_GET['redirect'] ?? ($redirect ?? ''));
    if (!empty($redirectVal)): 
  ?>
    <input type="hidden" name="redirect" value="<?= \App\Core\View::e($redirectVal) ?>">
  <?php endif; ?>

  <div class="form-group mb-3">
    <label class="font-weight-600" style="color: #334155; font-size: 13.5px;">Email or Mobile Number</label>
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text bg-white" style="border-right: none; color: #94a3b8;"><i class="fa fa-user"></i></span>
      </div>
      <input type="text" name="identifier" class="form-control" placeholder="Mobile (10 digits) or Email" style="border-left: none;" required autofocus>
    </div>
  </div>

  <div class="form-group mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <label class="font-weight-600 mb-0" style="color: #334155; font-size: 13.5px;">Password</label>
      <a href="<?= \App\Core\View::url('/forgot-password') ?>" class="small" style="color: #f25b29; font-weight: 600;">Forgot Password?</a>
    </div>
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text bg-white" style="border-right: none; color: #94a3b8;"><i class="fa fa-lock"></i></span>
      </div>
      <input type="password" name="password" class="form-control" placeholder="••••••••" style="border-left: none;" required>
    </div>
  </div>

  <button type="submit" class="btn btn-primary-custom py-2 mt-2">Sign In</button>
</form>

<!-- Dedicated Create New Account Section -->
<div class="position-relative text-center my-4">
  <hr style="border-top: 1px solid #e2e8f0; margin: 0;">
  <span class="px-3 text-muted small bg-white position-relative font-weight-bold" style="top: -10px; color: #64748b !important; letter-spacing: 0.5px;">NEW CUSTOMER?</span>
</div>

<div>
  <a href="<?= \App\Core\View::url('/signup' . (!empty($redirectVal) ? '?redirect=' . urlencode($redirectVal) : '')) ?>" 
     class="btn btn-block font-weight-bold" 
     style="background: #ffffff; color: #0a1c33; border: 1.5px solid #0a1c33; border-radius: 6px; padding: 10px; font-size: 14.5px; transition: all 0.2s ease;">
    <i class="fa fa-user-plus mr-2" style="color: #f25b29;"></i> Create New Account
  </a>
</div>
