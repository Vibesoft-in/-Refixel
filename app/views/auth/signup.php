<h3 class="text-center font-weight-bold mb-4" style="color:#13221e;">Create Customer Account</h3>
    <form action="<?= \App\Core\View::url('/signup') ?>" method="POST">
      <?= \App\Core\View::csrfField() ?>
      <div class="form-group mb-3">
        <label class="font-weight-600">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
      </div>
      <div class="form-group mb-3">
        <label class="font-weight-600">Mobile Number</label>
        <input type="tel" name="phone" class="form-control" placeholder="9876543210" required>
      </div>
      <div class="form-group mb-3">
        <label class="font-weight-600">Email (Optional)</label>
        <input type="email" name="email" class="form-control" placeholder="john@example.com">
      </div>
      <div class="form-group mb-3">
        <label class="font-weight-600">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
      </div>
      <div class="form-group mb-4">
        <label class="font-weight-600">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn btn-primary-custom">Create Account</button>
    </form>
    <div class="text-center mt-4">
      <p class="text-muted mb-0">Already have an account? <a href="<?= \App\Core\View::url('/login') ?>" style="color:#f25b29; font-weight:600;">Sign In</a></p>
    </div>
