<?php
$user = \App\Core\Auth::user();
$customerProfile = $user ? \App\Core\Database::fetchOne("SELECT address, city, pincode FROM customer_profiles WHERE user_id = ?", [$user['id']]) : null;
$currentCity = $_SESSION['selected_city'] ?? ($customerProfile['city'] ?? 'Gurugram');
$selectedServiceId = $service['id'] ?? 0;
?>
<div class="container py-5 my-3">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0 mb-4" style="font-size: 14px;">
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/') ?>" style="color:#f25b29;">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/services') ?>" style="color:#f25b29;">Services</a></li>
      <li class="breadcrumb-item active" aria-current="page">Schedule Booking</li>
    </ol>
  </nav>

  <div class="row">
    <!-- Booking Form -->
    <div class="col-lg-8 mb-4">
      <div class="card p-4 p-md-5 border-0 shadow-sm" style="border-radius: 16px;">
        <h2 class="font-weight-bold mb-2" style="font-size: 26px; color: #1a1a1a;">Book Doorstep Service</h2>
        <p class="text-muted small mb-4">A background-verified technician equipped with mechanized tools will arrive at your scheduled slot.</p>

        <form action="<?= \App\Core\View::url('/book') ?>" method="POST" id="bookingForm">
          <?= \App\Core\View::csrf() ?>

          <!-- Service Selection -->
          <div class="form-group mb-4">
            <label class="font-weight-bold small">Selected Service <span class="text-danger">*</span></label>
            <?php if ($service): ?>
              <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>" id="serviceIdInput">
              <div class="p-3 rounded bg-light border d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="font-weight-bold mb-0"><?= \App\Core\View::e($service['name']) ?></h6>
                  <small class="text-muted">Estimated duration: ~<?= (int)($service['duration_minutes'] ?? 60) ?> mins</small>
                </div>
                <h5 class="font-weight-bold mb-0" style="color: #f25b29;">₹<?= number_format((float)$service['starting_price'], 0) ?></h5>
              </div>
            <?php else: ?>
              <select name="service_id" id="serviceIdSelect" class="form-control" required>
                <option value="">-- Choose a service package --</option>
                <?php foreach ($allServices ?? [] as $s): ?>
                  <option value="<?= (int)$s['id'] ?>" data-price="<?= (float)$s['starting_price'] ?>">
                    <?= \App\Core\View::e($s['name']) ?> (Starting at ₹<?= number_format((float)$s['starting_price'], 0) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <!-- Contact Details -->
          <h5 class="font-weight-bold mt-4 mb-3" style="font-size: 18px; color: #0a1c33;">1. Contact Details</h5>
          <div class="form-row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="<?= \App\Core\View::e($user['name'] ?? '') ?>" placeholder="e.g. Ananya Sharma" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">Mobile Number <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text">+91</span></div>
                <input type="tel" name="phone" class="form-control" value="<?= \App\Core\View::e($user['phone'] ?? '') ?>" placeholder="10-digit number" pattern="[6-9][0-9]{9}" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="font-weight-bold small">Email Address (Optional for GST Invoice)</label>
            <input type="email" name="email" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? '') ?>" placeholder="yourname@example.com">
          </div>

          <!-- Address & City -->
          <h5 class="font-weight-bold mt-4 mb-3" style="font-size: 18px; color: #0a1c33;">2. Service Address</h5>
          
          <div class="form-group mb-3">
            <label class="font-weight-bold small d-block mb-2">Address Type</label>
            <div class="custom-control custom-radio custom-control-inline">
              <input type="radio" id="typeHomeBook" name="address_type" class="custom-control-input" value="Home" <?= ($customerProfile['address_type'] ?? 'Home') === 'Home' ? 'checked' : '' ?>>
              <label class="custom-control-label small" for="typeHomeBook">Home</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
              <input type="radio" id="typeOfficeBook" name="address_type" class="custom-control-input" value="Office" <?= ($customerProfile['address_type'] ?? '') === 'Office' ? 'checked' : '' ?>>
              <label class="custom-control-label small" for="typeOfficeBook">Office</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
              <input type="radio" id="typeOtherBook" name="address_type" class="custom-control-input" value="Other" <?= ($customerProfile['address_type'] ?? '') === 'Other' ? 'checked' : '' ?>>
              <label class="custom-control-label small" for="typeOtherBook">Other</label>
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">House / Flat / Office No. <span class="text-danger">*</span></label>
              <input type="text" name="house_no" class="form-control" value="<?= \App\Core\View::e($customerProfile['house_no'] ?? '') ?>" placeholder="e.g. Flat 604" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">Street / Society / Area <span class="text-danger">*</span></label>
              <input type="text" name="street" class="form-control" value="<?= \App\Core\View::e($customerProfile['street'] ?? '') ?>" placeholder="e.g. Palm Springs" required>
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-4 form-group">
              <label class="font-weight-bold small">City <span class="text-danger">*</span></label>
              <input type="text" name="city" class="form-control" value="<?= \App\Core\View::e($customerProfile['city'] ?? $currentCity) ?>" required>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold small">State <span class="text-danger">*</span></label>
              <input type="text" name="state" class="form-control" value="<?= \App\Core\View::e($customerProfile['state'] ?? 'Haryana') ?>" required>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
              <input type="text" name="pincode" class="form-control" placeholder="6 digits" pattern="[0-9]{6}" value="<?= \App\Core\View::e($customerProfile['pincode'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="font-weight-bold small">Complete Address / Landmark (Optional)</label>
            <textarea name="address" rows="2" class="form-control" placeholder="Any extra landmark details"><?= \App\Core\View::e($customerProfile['address'] ?? '') ?></textarea>
          </div>

          <!-- Preferred Slot -->
          <h5 class="font-weight-bold mt-4 mb-3" style="font-size: 18px; color: #0a1c33;">3. Preferred Schedule</h5>
          <div class="form-row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">Date <span class="text-danger">*</span></label>
              <input type="date" name="preferred_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">Time Slot <span class="text-danger">*</span></label>
              <select name="preferred_time" class="form-control" required>
                <option value="09:00 - 11:00 AM">09:00 - 11:00 AM (Morning)</option>
                <option value="11:00 AM - 01:00 PM">11:00 AM - 01:00 PM (Noon)</option>
                <option value="02:00 - 04:00 PM" selected>02:00 - 04:00 PM (Afternoon)</option>
                <option value="04:00 - 06:00 PM">04:00 - 06:00 PM (Evening)</option>
              </select>
            </div>
          </div>

          <!-- Special instructions -->
          <div class="form-group mb-4">
            <label class="font-weight-bold small">Specific Instructions or Issues (Optional)</label>
            <textarea name="issue_details" rows="2" class="form-control" placeholder="e.g. Please bring extra tile descaler, parking available in basement"></textarea>
          </div>

          <button type="submit" class="btn text-white py-3 font-weight-bold w-100" style="background:#f25b29; border-radius: 8px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);">
            Confirm & Schedule Booking &rarr;
          </button>
        </form>
      </div>
    </div>

    <!-- Booking Summary Sticky Card -->
    <div class="col-lg-4">
      <div class="card p-4 border-0 shadow-sm sticky-top" style="top: 90px; border-radius: 16px; background:#fff8f5; border:1px solid #ffdacf;">
        <h4 class="font-weight-bold mb-3" style="font-size: 20px; color: #0a1c33;">Booking Summary</h4>

        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Service Base Fee:</span>
          <span class="font-weight-bold text-dark" id="summaryBasePrice">₹<?= $service ? number_format((float)$service['starting_price'], 0) : '0' ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Safety & Hygiene Gear:</span>
          <span class="font-weight-bold" style="color: #f25b29;">FREE</span>
        </div>
        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Taxes & GST (18%):</span>
          <span>Included</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-baseline mb-4">
          <span class="font-weight-bold">Estimated Total:</span>
          <h3 class="font-weight-bold mb-0" style="color: #f25b29;" id="summaryTotal">₹<?= $service ? number_format((float)$service['starting_price'], 0) : '0' ?></h3>
        </div>

        <div class="p-3 rounded bg-white border mb-3 small" style="line-height: 1.8;">
          <div class="d-flex align-items-center mb-1 font-weight-bold" style="color: #f25b29;">
            <i class="fa fa-shield mr-2"></i> REFIXEL Promise
          </div>
          <p class="text-muted mb-0">Pay securely after completion. No advance required. Full 24-hour re-clean guarantee on all packages.</p>
        </div>

        <small class="text-muted text-center d-block">
          Need immediate assistance? Call <a href="tel:+919458182006" class="text-dark font-weight-bold">+91 94581 82006</a>
        </small>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var serviceSelect = document.getElementById('serviceIdSelect');
  var summaryBase = document.getElementById('summaryBasePrice');
  var summaryTotal = document.getElementById('summaryTotal');

  if (serviceSelect) {
    serviceSelect.addEventListener('change', function() {
      var selected = this.options[this.selectedIndex];
      var price = selected.getAttribute('data-price') || 0;
      if (summaryBase) summaryBase.textContent = '₹' + Number(price).toLocaleString('en-IN');
      if (summaryTotal) summaryTotal.textContent = '₹' + Number(price).toLocaleString('en-IN');
    });
  }
});
</script>
