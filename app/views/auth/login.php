<h3 class="text-center font-weight-bold mb-4" style="color:#13221e;">Sign In to REFIXEL</h3>
    <form action="<?= \App\Core\View::url('/login') ?>" method="POST">
      <?= \App\Core\View::csrfField() ?>
      <?php if (!empty($_GET['redirect'])): ?>
        <input type="hidden" name="redirect" value="<?= \App\Core\View::e($_GET['redirect']) ?>">
      <?php endif; ?>
      <div class="form-group mb-3">
        <label class="font-weight-600">Email or Mobile Number</label>
        <input type="text" name="identifier" class="form-control" placeholder="admin@refixel.com or 9999999999" required autofocus>
      </div>
      <div class="form-group mb-4">
        <label class="font-weight-600">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary-custom">Sign In</button>
    </form>
    <div class="text-center mt-4">
      <p class="text-muted mb-0">Don't have an account? <a href="<?= \App\Core\View::url('/signup') ?>" style="color:#f25b29; font-weight:600;">Sign Up</a></p>
    </div>
