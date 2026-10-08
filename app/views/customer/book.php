<?php
$user = \App\Core\Auth::user();
$customerProfile = $user ? \App\Core\Database::fetchOne("SELECT address, city, pincode FROM customer_profiles WHERE user_id = ?", [$user['id']]) : null;
$currentCity = $_SESSION['selected_city'] ?? ($customerProfile['city'] ?? 'Gurugram');
$selectedServiceId = $service['id'] ?? 0;
?>
<div class="container py-5 my-3">
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
          
          <!-- Hidden inputs for backend form processing -->
          <input type="hidden" name="preferred_date" id="preferredDateInput" value="<?= date('Y-m-d') ?>" required>
          <input type="hidden" name="preferred_time" id="preferredTimeInput" value="02:00 - 04:00 PM" required>

          <div class="row mb-4">
            <!-- Calendar Input with Popover Dropdown (Matching reference image) -->
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
              <label class="font-weight-bold small text-muted text-uppercase mb-2 d-block" style="letter-spacing: 0.5px; font-size: 12px;">
                Select Service Date <span class="text-danger">*</span>
              </label>

              <div class="position-relative" id="datePickerContainer">
                <!-- Clickable Date Input Trigger -->
                <div class="cal-input-trigger" id="dateInputTrigger" role="button" tabindex="0">
                  <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                    <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input type="text" id="displayDateInput" class="cal-custom-input" readonly value="<?= date('d M Y') ?>" placeholder="Click to select date...">
                  </div>
                  <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="calChevronIcon"></i>
                </div>

                <!-- Dropdown / Popover Calendar -->
                <div id="calendarDropdown" class="calendar-dropdown-popover">
                  <div class="refixel-calendar-card">
                    <!-- Top Header: Day Number, Month Name & Navigation -->
                    <div class="cal-top-header">
                      <div class="cal-title-wrap">
                        <div id="calDayNumber" class="cal-day-num"><?= date('j') ?></div>
                        <div id="calMonthName" class="cal-month-name"><?= date('F') ?></div>
                      </div>
                      <div class="cal-nav-wrap d-flex align-items-center" style="gap: 6px;">
                        <button type="button" id="calPrevMonthBtn" class="cal-nav-btn" title="Previous Month" aria-label="Previous Month">
                          <i class="fa fa-chevron-left" style="font-size: 11px;"></i>
                        </button>
                        <!-- Mini 3D Calendar Icon Badge from reference image -->
                        <div class="cal-icon-badge" title="Calendar">
                          <div class="cal-badge-bar"></div>
                          <div class="cal-badge-dots">
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot red"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                            <span class="cal-badge-dot"></span>
                          </div>
                        </div>
                        <button type="button" id="calNextMonthBtn" class="cal-nav-btn" title="Next Month" aria-label="Next Month">
                          <i class="fa fa-chevron-right" style="font-size: 11px;"></i>
                        </button>
                      </div>
                    </div>

                    <!-- 3-Segment Accent Bar (matching reference image) -->
                    <div class="cal-accent-divider">
                      <span class="bar-segment" style="flex: 1.2;"></span>
                      <span class="bar-segment" style="flex: 1.8;"></span>
                      <span class="bar-segment muted" style="flex: 2.2;"></span>
                    </div>

                    <!-- Weekday Headers (M T W T F S S) -->
                    <div class="cal-weekdays">
                      <span class="cal-weekday">M</span>
                      <span class="cal-weekday">T</span>
                      <span class="cal-weekday">W</span>
                      <span class="cal-weekday">T</span>
                      <span class="cal-weekday">F</span>
                      <span class="cal-weekday">S</span>
                      <span class="cal-weekday">S</span>
                    </div>

                    <!-- Monthly Days Grid -->
                    <div id="calDaysGrid" class="cal-days-grid">
                      <!-- Generated dynamically via JS -->
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Time Slots & Live Confirmation Card -->
            <div class="col-lg-6 col-md-12 d-flex flex-column justify-content-between">
              <div>
                <label class="font-weight-bold small text-muted text-uppercase mb-2 d-block" style="letter-spacing: 0.5px; font-size: 12px;">
                  Select Time Slot <span class="text-danger">*</span>
                </label>
                <div class="time-slots-grid" id="timeSlotsGroup">
                  <button type="button" class="slot-pill-btn" data-slot="09:00 - 11:00 AM">
                    <div class="slot-time font-weight-bold">09:00 - 11:00 AM</div>
                    <div class="slot-tag">Morning Slot</div>
                  </button>
                  <button type="button" class="slot-pill-btn" data-slot="11:00 AM - 01:00 PM">
                    <div class="slot-time font-weight-bold">11:00 AM - 01:00 PM</div>
                    <div class="slot-tag">Noon Slot</div>
                  </button>
                  <button type="button" class="slot-pill-btn active" data-slot="02:00 - 04:00 PM">
                    <div class="slot-time font-weight-bold">02:00 - 04:00 PM</div>
                    <div class="slot-tag">Afternoon Slot</div>
                  </button>
                  <button type="button" class="slot-pill-btn" data-slot="04:00 - 06:00 PM">
                    <div class="slot-time font-weight-bold">04:00 - 06:00 PM</div>
                    <div class="slot-tag">Evening Slot</div>
                  </button>
                </div>
              </div>

              <!-- Scheduled Slot Badge -->
              <div class="schedule-summary-box mt-3 p-3 rounded-lg" style="background: #fff8f5; border: 1.5px dashed #ffdacf; border-radius: 14px;">
                <div class="d-flex align-items-center">
                  <div class="schedule-summary-icon mr-3" style="width: 40px; height: 40px; border-radius: 12px; background: #ff5238; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 82, 56, 0.3);">
                    <i class="fa fa-calendar-check-o"></i>
                  </div>
                  <div>
                    <div class="small" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: #f25b29;">Confirmed Slot</div>
                    <div class="font-weight-bold text-dark" id="calSummaryDisplay" style="font-size: 14px;">Loading schedule...</div>
                  </div>
                </div>
              </div>
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

<style>
/* Modern Reference Calendar Card */
.refixel-calendar-card {
  background: #ffffff;
  border-radius: 26px;
  border: 1.5px solid #edf0f5;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
  padding: 22px 20px 22px;
  width: 100%;
  max-width: 330px;
  user-select: none;
}
.cal-top-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}
.cal-day-num {
  font-size: 38px;
  font-weight: 700;
  line-height: 1;
  color: #111827;
  letter-spacing: -0.5px;
}
.cal-month-name {
  font-size: 16px;
  font-weight: 500;
  color: #8a92a0;
  margin-top: 5px;
}
.cal-nav-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  cursor: pointer;
  transition: all 0.2s ease;
}
.cal-nav-btn:hover:not(:disabled) {
  background: #ff5238;
  color: #ffffff;
  border-color: #ff5238;
}
.cal-nav-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
  pointer-events: none;
}
.cal-icon-badge {
  width: 42px;
  height: 42px;
  background: #ffffff;
  border: 1.5px solid #eef2f6;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.cal-icon-badge .cal-badge-bar {
  background: #ff5238;
  height: 12px;
  width: 100%;
}
.cal-icon-badge .cal-badge-dots {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(5, 3px);
  grid-gap: 3px;
  align-content: center;
  justify-content: center;
  padding: 3px;
}
.cal-icon-badge .cal-badge-dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: #d1d5db;
}
.cal-icon-badge .cal-badge-dot.red {
  background: #ff5238;
}
.cal-accent-divider {
  display: flex;
  gap: 5px;
  margin-bottom: 16px;
}
.cal-accent-divider .bar-segment {
  height: 3px;
  border-radius: 3px;
  background: #ff5238;
}
.cal-accent-divider .bar-segment.muted {
  background: #eef2f6;
}
.cal-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 8px;
}
.cal-weekday {
  font-size: 13px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
}
.cal-days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  row-gap: 6px;
  column-gap: 2px;
  text-align: center;
}
.cal-day-cell {
  width: 34px;
  height: 34px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14.5px;
  font-weight: 600;
  color: #1f2937;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.cal-day-cell:hover:not(.disabled):not(.selected) {
  background: #fff0eb;
  color: #ff5238;
}
.cal-day-cell.selected {
  background: #ff5238 !important;
  color: #ffffff !important;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(255, 82, 56, 0.4);
  border-radius: 8px;
}
.cal-day-cell.today:not(.selected) {
  border: 1.5px solid #ff5238;
  color: #ff5238;
}
.cal-day-cell.disabled {
  color: #cbd5e1;
  cursor: not-allowed;
  opacity: 0.35;
  pointer-events: none;
}
.cal-day-cell.empty {
  cursor: default;
  pointer-events: none;
}

/* Date Input Trigger */
.cal-input-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}
.cal-input-trigger:hover {
  border-color: #ff5238;
}
.cal-input-trigger.active {
  border-color: #ff5238;
  box-shadow: 0 0 0 3px rgba(255, 82, 56, 0.15);
}
.cal-custom-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  cursor: pointer;
  width: 100%;
}
.cal-trigger-chevron {
  font-size: 12px;
  transition: transform 0.25s ease;
}
.cal-input-trigger.active .cal-trigger-chevron {
  transform: rotate(180deg);
  color: #ff5238 !important;
}

/* Calendar Popover Dropdown */
.calendar-dropdown-popover {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  z-index: 1050;
  display: none;
  filter: drop-shadow(0 14px 30px rgba(15, 23, 42, 0.15));
  width: 330px;
  max-width: calc(100vw - 32px);
}
.calendar-dropdown-popover.show {
  display: block;
  animation: calDropdownFade 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes calDropdownFade {
  from {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Time Slot Pills */
.time-slots-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.slot-pill-btn {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 14px;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
}
.slot-pill-btn:hover {
  border-color: #ff5238;
  background: #fffaf8;
}
.slot-pill-btn.active {
  border-color: #ff5238;
  background: #fff5f2;
  box-shadow: 0 2px 8px rgba(255, 82, 56, 0.15);
}
.slot-pill-btn .slot-time {
  font-size: 13px;
  color: #1e293b;
  margin-bottom: 2px;
}
.slot-pill-btn.active .slot-time {
  color: #ff5238;
}
.slot-pill-btn .slot-tag {
  font-size: 11px;
  color: #64748b;
}
.slot-pill-btn.active .slot-tag {
  color: #e0452d;
  font-weight: 600;
}
@media (max-width: 576px) {
  .time-slots-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Service selection price update
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

  // Interactive Modern Calendar with Dropdown / Popover
  var datePickerContainer = document.getElementById('datePickerContainer');
  var dateInputTrigger = document.getElementById('dateInputTrigger');
  var displayDateInput = document.getElementById('displayDateInput');
  var calendarDropdown = document.getElementById('calendarDropdown');
  var preferredDateInput = document.getElementById('preferredDateInput');
  var preferredTimeInput = document.getElementById('preferredTimeInput');
  var calDayNumber = document.getElementById('calDayNumber');
  var calMonthName = document.getElementById('calMonthName');
  var calDaysGrid = document.getElementById('calDaysGrid');
  var calPrevMonthBtn = document.getElementById('calPrevMonthBtn');
  var calNextMonthBtn = document.getElementById('calNextMonthBtn');
  var calSummaryDisplay = document.getElementById('calSummaryDisplay');
  var slotButtons = document.querySelectorAll('.slot-pill-btn');

  if (calDaysGrid && preferredDateInput && dateInputTrigger && calendarDropdown) {
    var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    var today = new Date();
    today.setHours(0, 0, 0, 0);

    // Initial selected date (defaults to today)
    var selectedDate = new Date(today.getTime());
    var viewYear = selectedDate.getFullYear();
    var viewMonth = selectedDate.getMonth();

    function formatYYYYMMDD(d) {
      var y = d.getFullYear();
      var m = String(d.getMonth() + 1).padStart(2, '0');
      var day = String(d.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + day;
    }

    function formatInputDate(d) {
      var day = String(d.getDate()).padStart(2, '0');
      var m = monthNames[d.getMonth()].substring(0, 3);
      return day + ' ' + m + ' ' + d.getFullYear();
    }

    function formatDisplayDate(d) {
      var isToday = (d.getFullYear() === today.getFullYear() && d.getMonth() === today.getMonth() && d.getDate() === today.getDate());
      var prefix = isToday ? 'Today, ' : (dayNames[d.getDay()] + ', ');
      return prefix + d.getDate() + ' ' + monthNames[d.getMonth()].substring(0, 3) + ' ' + d.getFullYear();
    }

    function updateSummary() {
      if (!calSummaryDisplay) return;
      var timeSlot = preferredTimeInput ? preferredTimeInput.value : '';
      calSummaryDisplay.textContent = formatDisplayDate(selectedDate) + ' (' + timeSlot + ')';
    }

    function toggleDropdown(show) {
      if (typeof show === 'boolean') {
        if (show) {
          calendarDropdown.classList.add('show');
          dateInputTrigger.classList.add('active');
        } else {
          calendarDropdown.classList.remove('show');
          dateInputTrigger.classList.remove('active');
        }
      } else {
        var isOpen = calendarDropdown.classList.contains('show');
        if (isOpen) {
          calendarDropdown.classList.remove('show');
          dateInputTrigger.classList.remove('active');
        } else {
          calendarDropdown.classList.add('show');
          dateInputTrigger.classList.add('active');
        }
      }
    }

    // Trigger open/close when clicking input box
    dateInputTrigger.addEventListener('click', function(e) {
      toggleDropdown();
    });

    // Close when clicking outside
    document.addEventListener('click', function(e) {
      if (datePickerContainer && !datePickerContainer.contains(e.target)) {
        toggleDropdown(false);
      }
    });

    function renderCalendar() {
      calDaysGrid.innerHTML = '';

      calDayNumber.textContent = selectedDate.getDate();
      calMonthName.textContent = monthNames[viewMonth];

      var isCurrentMonth = (viewYear === today.getFullYear() && viewMonth === today.getMonth());
      calPrevMonthBtn.disabled = isCurrentMonth;

      var firstDayOfMonth = new Date(viewYear, viewMonth, 1);
      // Monday = 0, Sunday = 6
      var startDayIndex = (firstDayOfMonth.getDay() + 6) % 7;
      var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

      // Blank cells before first day
      for (var b = 0; b < startDayIndex; b++) {
        var blank = document.createElement('div');
        blank.className = 'cal-day-cell empty';
        calDaysGrid.appendChild(blank);
      }

      // Days of month
      for (var d = 1; d <= daysInMonth; d++) {
        var cellDate = new Date(viewYear, viewMonth, d);
        cellDate.setHours(0, 0, 0, 0);

        var cell = document.createElement('div');
        cell.className = 'cal-day-cell';
        cell.textContent = d;

        var isPast = cellDate.getTime() < today.getTime();
        var isToday = cellDate.getTime() === today.getTime();
        var isSelected = (
          cellDate.getFullYear() === selectedDate.getFullYear() &&
          cellDate.getMonth() === selectedDate.getMonth() &&
          cellDate.getDate() === selectedDate.getDate()
        );

        if (isPast) {
          cell.classList.add('disabled');
        } else {
          if (isToday) cell.classList.add('today');
          if (isSelected) cell.classList.add('selected');

          (function(cDate, dayNum) {
            cell.addEventListener('click', function(ev) {
              ev.stopPropagation();
              selectedDate = new Date(cDate.getTime());
              preferredDateInput.value = formatYYYYMMDD(selectedDate);
              displayDateInput.value = formatInputDate(selectedDate);
              calDayNumber.textContent = dayNum;
              calMonthName.textContent = monthNames[selectedDate.getMonth()];
              renderCalendar();
              updateSummary();
              // Close dropdown after selecting date
              toggleDropdown(false);
            });
          })(cellDate, d);
        }

        calDaysGrid.appendChild(cell);
      }
    }

    if (calNextMonthBtn) {
      calNextMonthBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        viewMonth++;
        if (viewMonth > 11) {
          viewMonth = 0;
          viewYear++;
        }
        renderCalendar();
      });
    }

    if (calPrevMonthBtn) {
      calPrevMonthBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        var isCurrentMonth = (viewYear === today.getFullYear() && viewMonth === today.getMonth());
        if (isCurrentMonth) return;
        viewMonth--;
        if (viewMonth < 0) {
          viewMonth = 11;
          viewYear--;
        }
        renderCalendar();
      });
    }

    // Time Slot pill clicks
    slotButtons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        slotButtons.forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        var slot = this.getAttribute('data-slot');
        if (preferredTimeInput) preferredTimeInput.value = slot;
        updateSummary();
      });
    });

    // Initial setup
    preferredDateInput.value = formatYYYYMMDD(selectedDate);
    displayDateInput.value = formatInputDate(selectedDate);
    renderCalendar();
    updateSummary();
  }
});
</script>
