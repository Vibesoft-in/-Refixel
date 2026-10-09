<div class="dash-card p-4 p-md-5">
  <div class="mb-4 pb-3" style="border-bottom: 1px solid #edf2f7;">
    <h3 class="dash-heading mb-1">Profile & Account Settings</h3>
    <p class="text-muted small mb-0" style="font-weight: 400;">Manage your personal contact details, saved addresses, and security credentials.</p>
  </div>

  <div class="row">
    <div class="col-lg-10">
      <form action="<?= \App\Core\View::url('/account/profile') ?>" method="POST">
        <?= \App\Core\View::csrf() ?>

        <div class="form-row mb-3">
          <div class="col-md-6 form-group">
            <label class="font-weight-500 small text-dark">Full Name</label>
            <input type="text" name="name" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px; font-weight: 500;" value="<?= \App\Core\View::e($user['name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6 form-group">
            <label class="font-weight-500 small text-dark">Mobile Number</label>
            <input type="tel" name="phone" class="form-control bg-light border-0 px-3 py-2 text-muted" style="border-radius: 8px;" value="<?= \App\Core\View::e($user['phone'] ?? '') ?>" readonly>
            <small class="text-muted mt-1 d-block" style="font-size: 11px;">Mobile number is verified via OTP.</small>
          </div>
        </div>

        <div class="form-group mb-4">
          <label class="font-weight-500 small text-dark">Email Address (for GST Invoices & Receipts)</label>
          <input type="email" name="email" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px; font-weight: 500;" value="<?= \App\Core\View::e($user['email'] ?? '') ?>" placeholder="name@example.com">
        </div>

        <h5 class="font-weight-bold mt-4 mb-3" style="font-size: 16px; color: #0a1c33;">Saved Address Details</h5>
        
        <div class="form-group mb-3">
          <label class="font-weight-500 small text-dark d-block mb-2">Address Type</label>
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="typeHome" name="address_type" class="custom-control-input" value="Home" <?= ($profile['address_type'] ?? 'Home') === 'Home' ? 'checked' : '' ?>>
            <label class="custom-control-label small" for="typeHome">Home</label>
          </div>
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="typeOffice" name="address_type" class="custom-control-input" value="Office" <?= ($profile['address_type'] ?? '') === 'Office' ? 'checked' : '' ?>>
            <label class="custom-control-label small" for="typeOffice">Office</label>
          </div>
          <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="typeOther" name="address_type" class="custom-control-input" value="Other" <?= ($profile['address_type'] ?? '') === 'Other' ? 'checked' : '' ?>>
            <label class="custom-control-label small" for="typeOther">Other</label>
          </div>
        </div>

        <div class="form-row mb-3">
          <div class="col-md-6 form-group">
            <label class="font-weight-500 small text-dark">House / Flat / Office No.</label>
            <input type="text" name="house_no" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px;" value="<?= \App\Core\View::e($profile['house_no'] ?? '') ?>" placeholder="e.g. Flat 101">
          </div>
          <div class="col-md-6 form-group">
            <label class="font-weight-500 small text-dark">Street / Society / Area</label>
            <input type="text" name="street" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px;" value="<?= \App\Core\View::e($profile['street'] ?? '') ?>" placeholder="e.g. Palm Springs">
          </div>
        </div>

        <div class="form-row mb-3">
          <div class="col-md-4 form-group">
            <label class="font-weight-500 small text-dark">City</label>
            <input type="text" name="city" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px;" value="<?= \App\Core\View::e($profile['city'] ?? '') ?>" placeholder="e.g. City / Town">
          </div>
          <div class="col-md-4 form-group">
            <label class="font-weight-500 small text-dark">State / Province</label>
            <input type="text" name="state" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px;" value="<?= \App\Core\View::e($profile['state'] ?? '') ?>" placeholder="e.g. State / Province / Region">
          </div>
          <div class="col-md-4 form-group">
            <label class="font-weight-500 small text-dark">Postal Code / PIN</label>
            <input type="text" name="pincode" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px;" value="<?= \App\Core\View::e($profile['pincode'] ?? '') ?>" placeholder="Postal code">
          </div>
        </div>
        
        <div class="form-group mb-4">
          <label class="font-weight-500 small text-dark">Complete Address (Optional)</label>
          <textarea name="address" rows="2" class="form-control bg-light border-0 px-3 py-2" style="border-radius: 8px;" placeholder="Landmark or complete address string"><?= \App\Core\View::e($profile['address'] ?? '') ?></textarea>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pt-4" style="border-top: 1px solid #edf2f7;">
          <div class="mb-3 mb-md-0 d-flex gap-3">
            <a href="<?= \App\Core\View::url('/change-password') ?>" class="text-decoration-none font-weight-500 small mr-4" style="color: #64748b;">
              <i class="fa fa-key mr-1"></i> Change Password
            </a>
            <a href="<?= \App\Core\View::url('/account/privacy') ?>" class="text-decoration-none font-weight-500 small" style="color: #64748b;">
              <i class="fa fa-shield mr-1"></i> Privacy & Data
            </a>
          </div>
          <button type="submit" class="btn soft-btn soft-btn-primary px-4 py-2">
            Save Profile Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
