<h3 class="text-center font-weight-bold mb-3" style="color:#13221e;">Reset Password</h3>
<p class="text-muted text-center small mb-4">Enter your registered email address or mobile number. We'll send you instructions to reset your password.</p>

<form action="<?= \App\Core\View::url('/forgot-password') ?>" method="POST">
  <?= \App\Core\View::csrfField() ?>
  <div class="form-group mb-4">
    <label class="font-weight-600">Email or Mobile Number</label>
    <input type="text" name="identifier" class="form-control" placeholder="john@example.com or 9999999999" required autofocus>
  </div>
  <button type="submit" class="btn btn-primary-custom">Send Reset Link</button>
</form>

<div class="text-center mt-4">
  <a href="<?= \App\Core\View::url('/login') ?>" style="color:#f25b29; font-size:14px; font-weight:600;">&larr; Back to Login</a>
</div>
