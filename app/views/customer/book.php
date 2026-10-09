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
          <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 mb-3" style="gap: 10px;">
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
              <h5 class="font-weight-bold mb-0" style="font-size: 18px; color: #0a1c33;">2. Service Address</h5>
              <span id="bookingAutoFillBadge" class="badge" style="display: none; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 11.5px; font-weight: 600; padding: 4px 9px; border-radius: 6px;">
                <i class="fa fa-check-circle mr-1"></i> Auto-filled from Home Page
              </span>
            </div>
            <button type="button" id="btnUseCurrentLocation" class="btn btn-sm btn-use-curr-loc" style="background: #fff3ec; color: #f25b29; border: 1.5px solid #ffdacf; font-weight: 600; border-radius: 8px; padding: 7px 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s ease; box-shadow: 0 2px 6px rgba(242, 91, 41, 0.08); cursor: pointer;">
              <i class="fa fa-crosshairs"></i> <span>Use My Current Location</span>
            </button>
          </div>
          <div id="bookingLocStatus" class="small mb-3" style="display: none;"></div>
          
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
              <input type="text" name="house_no" id="inputHouseNo" class="form-control" value="<?= \App\Core\View::e($customerProfile['house_no'] ?? '') ?>" placeholder="e.g. Flat 604 / House No." required>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold small">Street / Society / Area <span class="text-danger">*</span></label>
              <input type="text" name="street" id="inputStreet" class="form-control" value="<?= \App\Core\View::e($customerProfile['street'] ?? '') ?>" placeholder="e.g. Colony, Street, Apartment" required autocomplete="off">
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-4 form-group">
              <label class="font-weight-bold small">City <span class="text-danger">*</span></label>
              <input type="text" name="city" id="inputCity" class="form-control" value="<?= \App\Core\View::e($customerProfile['city'] ?? '') ?>" placeholder="e.g. City / Town" required>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold small">State <span class="text-danger">*</span></label>
              <input type="text" name="state" id="inputState" class="form-control" value="<?= \App\Core\View::e($customerProfile['state'] ?? '') ?>" placeholder="e.g. State / Province / Region" required>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
              <input type="text" name="pincode" id="inputPincode" class="form-control" placeholder="Postal Code / PIN" value="<?= \App\Core\View::e($customerProfile['pincode'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="font-weight-bold small">Complete Address / Landmark (Optional)</label>
            <textarea name="address" id="inputAddress" rows="2" class="form-control" placeholder="Any extra landmark details"><?= \App\Core\View::e($customerProfile['address'] ?? '') ?></textarea>
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

            <!-- Time Slot Dropdown Selection -->
            <div class="col-lg-6 col-md-12">
              <label class="font-weight-bold small text-muted text-uppercase mb-2 d-block" style="letter-spacing: 0.5px; font-size: 12px;">
                Select Time Slot <span class="text-danger">*</span>
              </label>

              <div class="position-relative" id="timePickerContainer">
                <!-- Clickable Time Input Trigger -->
                <div class="cal-input-trigger" id="timeInputTrigger" role="button" tabindex="0">
                  <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                    <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                      <i class="fa fa-clock-o"></i>
                    </div>
                    <input type="text" id="displayTimeInput" class="cal-custom-input" readonly value="02:00 - 04:00 PM" placeholder="Click to select time slot...">
                  </div>
                  <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="timeChevronIcon"></i>
                </div>

                <!-- Dropdown / Popover Time Slot List -->
                <div id="timeDropdown" class="time-dropdown-popover">
                  <div class="time-dropdown-menu-card">
                    <div class="time-dropdown-header pb-2 mb-2 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 4px 6px;">
                      <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: #64748b;">Available Arrival Slots</span>
                      <span class="badge" style="background: #fff3ec; color: #ff5238; font-size: 10.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px;">Doorstep Visit</span>
                    </div>
                    <div class="time-options-list" id="timeSlotsGroup">
                      <div class="time-option-item" role="button" tabindex="0" data-slot="09:00 - 11:00 AM">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                          <div class="time-opt-icon"><i class="fa fa-sun-o"></i></div>
                          <div>
                            <div class="time-opt-title font-weight-bold">09:00 - 11:00 AM</div>
                            <div class="time-opt-sub">Morning Slot</div>
                          </div>
                        </div>
                        <i class="fa fa-check time-opt-check"></i>
                      </div>

                      <div class="time-option-item" role="button" tabindex="0" data-slot="11:00 AM - 01:00 PM">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                          <div class="time-opt-icon"><i class="fa fa-sun-o"></i></div>
                          <div>
                            <div class="time-opt-title font-weight-bold">11:00 AM - 01:00 PM</div>
                            <div class="time-opt-sub">Noon Slot</div>
                          </div>
                        </div>
                        <i class="fa fa-check time-opt-check"></i>
                      </div>

                      <div class="time-option-item active" role="button" tabindex="0" data-slot="02:00 - 04:00 PM">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                          <div class="time-opt-icon"><i class="fa fa-cloud"></i></div>
                          <div>
                            <div class="time-opt-title font-weight-bold">02:00 - 04:00 PM</div>
                            <div class="time-opt-sub">Afternoon Slot</div>
                          </div>
                        </div>
                        <i class="fa fa-check time-opt-check"></i>
                      </div>

                      <div class="time-option-item" role="button" tabindex="0" data-slot="04:00 - 06:00 PM">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                          <div class="time-opt-icon"><i class="fa fa-moon-o"></i></div>
                          <div>
                            <div class="time-opt-title font-weight-bold">04:00 - 06:00 PM</div>
                            <div class="time-opt-sub">Evening Slot</div>
                          </div>
                        </div>
                        <i class="fa fa-check time-opt-check"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Scheduled Slot Badge (Full width below both dropdowns) -->
            <div class="col-12 mt-3">
              <div class="schedule-summary-box p-3 rounded-lg" style="background: #fff8f5; border: 1.5px dashed #ffdacf; border-radius: 14px;">
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

/* Time Dropdown Popover */
.time-dropdown-popover {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  z-index: 1050;
  display: none;
  filter: drop-shadow(0 14px 30px rgba(15, 23, 42, 0.15));
}
.time-dropdown-popover.show {
  display: block;
  animation: calDropdownFade 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.time-dropdown-menu-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1.5px solid #f1f5f9;
  padding: 12px;
}
.time-option-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s ease;
  margin-bottom: 6px;
  border: 1.5px solid transparent;
  user-select: none;
}
.time-option-item:last-child {
  margin-bottom: 0;
}
.time-option-item:hover {
  background: #fff8f5;
  border-color: #ffe3d9;
}
.time-option-item.active {
  background: #fff5f2;
  border-color: #ffdacf;
  box-shadow: 0 2px 6px rgba(255, 82, 56, 0.1);
}
.time-opt-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all 0.2s ease;
}
.time-option-item:hover .time-opt-icon {
  background: #fff0eb;
  color: #ff5238;
}
.time-option-item.active .time-opt-icon {
  background: #ff5238;
  color: #ffffff;
}
.time-opt-title {
  font-size: 13.5px;
  color: #1e293b;
  line-height: 1.2;
}
.time-option-item.active .time-opt-title {
  color: #e0452d;
}
.time-opt-sub {
  font-size: 11px;
  color: #64748b;
}
.time-option-item.active .time-opt-sub {
  color: #e0452d;
  font-weight: 600;
}
.time-opt-check {
  color: #ff5238;
  font-size: 13px;
  display: none;
}
.time-option-item.active .time-opt-check {
  display: block;
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
  var timePickerContainer = document.getElementById('timePickerContainer');
  var timeInputTrigger = document.getElementById('timeInputTrigger');
  var displayTimeInput = document.getElementById('displayTimeInput');
  var timeDropdown = document.getElementById('timeDropdown');
  var timeOptionItems = document.querySelectorAll('.time-option-item');

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

    function toggleTimeDropdown(show) {
      if (!timeDropdown || !timeInputTrigger) return;
      if (typeof show === 'boolean') {
        if (show) {
          timeDropdown.classList.add('show');
          timeInputTrigger.classList.add('active');
          if (calendarDropdown && dateInputTrigger) {
            calendarDropdown.classList.remove('show');
            dateInputTrigger.classList.remove('active');
          }
        } else {
          timeDropdown.classList.remove('show');
          timeInputTrigger.classList.remove('active');
        }
      } else {
        var isOpen = timeDropdown.classList.contains('show');
        toggleTimeDropdown(!isOpen);
      }
    }

    function toggleDropdown(show) {
      if (typeof show === 'boolean') {
        if (show) {
          calendarDropdown.classList.add('show');
          dateInputTrigger.classList.add('active');
          toggleTimeDropdown(false);
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
          toggleTimeDropdown(false);
        }
      }
    }

    // Trigger open/close when clicking date input box
    dateInputTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      toggleDropdown();
    });

    // Trigger open/close when clicking time input box
    if (timeInputTrigger) {
      timeInputTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleTimeDropdown();
      });
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
      if (datePickerContainer && !datePickerContainer.contains(e.target)) {
        toggleDropdown(false);
      }
      if (timePickerContainer && !timePickerContainer.contains(e.target)) {
        toggleTimeDropdown(false);
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

    // Time Slot dropdown option clicks
    timeOptionItems.forEach(function(opt) {
      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        timeOptionItems.forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        var slot = this.getAttribute('data-slot');
        if (displayTimeInput) displayTimeInput.value = slot;
        if (preferredTimeInput) preferredTimeInput.value = slot;
        updateSummary();
        toggleTimeDropdown(false);
      });
    });

    // Initial setup
    preferredDateInput.value = formatYYYYMMDD(selectedDate);
    displayDateInput.value = formatInputDate(selectedDate);
    if (preferredTimeInput && displayTimeInput) {
      displayTimeInput.value = preferredTimeInput.value || '02:00 - 04:00 PM';
    }
    renderCalendar();
    updateSummary();
  }

  /* ══════════════════════════════════════════════════════════════════
     AUTO-FILL & "USE MY CURRENT LOCATION" LOGIC
     ══════════════════════════════════════════════════════════════════ */
  var btnUseCurrentLoc     = document.getElementById('btnUseCurrentLocation');
  var bookingLocStatus     = document.getElementById('bookingLocStatus');
  var bookingAutoFillBadge = document.getElementById('bookingAutoFillBadge');

  var inputHouseNo = document.getElementById('inputHouseNo');
  var inputStreet  = document.getElementById('inputStreet');
  var inputCity    = document.getElementById('inputCity');
  var inputState   = document.getElementById('inputState');
  var inputPincode = document.getElementById('inputPincode');
  var inputAddress = document.getElementById('inputAddress');

  function flashHighlight(el) {
    if (!el) return;
    el.classList.add('is-valid');
    el.style.transition = 'background-color 0.4s ease, border-color 0.4s ease';
    el.style.backgroundColor = '#f0fdf4';
    el.style.borderColor = '#86efac';
    setTimeout(function() {
      el.style.backgroundColor = '';
      el.style.borderColor = '';
    }, 1500);
  }

  function applyLocationData(loc, sourceBadge) {
    if (!loc) return;
    var filledCount = 0;

    if (inputCity && loc.city) {
      inputCity.value = loc.city;
      flashHighlight(inputCity);
      filledCount++;
    }
    if (inputState && loc.state) {
      inputState.value = loc.state;
      flashHighlight(inputState);
      filledCount++;
    }
    if (inputStreet) {
      var resolvedStreet = (loc.street || '').trim();
      var cityName = (loc.city || '').trim().toLowerCase();

      // If no street or street equals city, fallback to area, district, or address tokens
      if (!resolvedStreet || resolvedStreet.toLowerCase() === cityName) {
        if (loc.area && loc.area.toLowerCase() !== cityName) {
          resolvedStreet = loc.area.trim();
        } else if (loc.district && loc.district.toLowerCase() !== cityName) {
          resolvedStreet = loc.district.trim() + ' Area';
        } else if (loc.fullAddress) {
          var parts = loc.fullAddress.split(',').map(function(s) { return s.trim(); });
          for (var p = 0; p < parts.length; p++) {
            var pt = parts[p].toLowerCase();
            if (pt && pt !== cityName && pt !== (loc.state || '').toLowerCase() && pt !== (loc.country || '').toLowerCase() && !/^\d+$/.test(pt)) {
              resolvedStreet = parts[p];
              break;
            }
          }
        }
      }

      if (!resolvedStreet || resolvedStreet.toLowerCase() === cityName) {
        resolvedStreet = loc.city ? (loc.city.trim() + ' Area') : 'Doorstep Service Area';
      }

      if (resolvedStreet) {
        inputStreet.value = resolvedStreet;
        flashHighlight(inputStreet);
        filledCount++;
      }
    }
    if (inputHouseNo) {
      if (loc.houseNo && loc.houseNo !== 'Doorstep Visit') {
        inputHouseNo.value = loc.houseNo;
        flashHighlight(inputHouseNo);
        filledCount++;
      } else if (!inputHouseNo.value.trim() || inputHouseNo.value === 'Flat 604') {
        inputHouseNo.value = '';
        inputHouseNo.placeholder = 'e.g. Flat 604 / House No.';
      }
    }
    if (inputPincode && loc.pincode && loc.pincode.toString().trim()) {
      inputPincode.value = loc.pincode.toString().trim();
      flashHighlight(inputPincode);
      filledCount++;
    }
    if (inputAddress && loc.fullAddress && loc.fullAddress.toLowerCase() !== (loc.city || '').toLowerCase()) {
      inputAddress.value = loc.fullAddress;
      flashHighlight(inputAddress);
      filledCount++;
    }

    if (filledCount > 0) {
      if (bookingAutoFillBadge) {
        bookingAutoFillBadge.innerHTML = '<i class="fa fa-check-circle mr-1"></i> ' + (sourceBadge || 'Location Detected');
        bookingAutoFillBadge.style.display = 'inline-block';
      }
      if (btnUseCurrentLoc) {
        btnUseCurrentLoc.innerHTML = '<i class="fa fa-check text-success"></i> <span>Location Applied</span>';
        btnUseCurrentLoc.style.background = '#ecfdf5';
        btnUseCurrentLoc.style.borderColor = '#a7f3d0';
        btnUseCurrentLoc.style.color = '#059669';
      }
      if (bookingLocStatus) {
        bookingLocStatus.style.display = 'block';
        var locDisplay = [loc.city, loc.state, loc.country].filter(Boolean).join(', ') || loc.fullAddress || 'Location Applied';
        bookingLocStatus.innerHTML = '<span style="color: #059669;"><i class="fa fa-map-marker text-success mr-1"></i> Detected: <strong>' + locDisplay + (loc.pincode ? ' (' + loc.pincode + ')' : '') + '</strong></span>';
      }
    }
  }

  // 1. AUTO-FILL ON PAGE LOAD if user already selected/detected location from home page
  try {
    var rawLoc = localStorage.getItem('refixel_user_location');
    if (rawLoc) {
      var savedLoc = JSON.parse(rawLoc);
      if (savedLoc && (savedLoc.fullAddress || savedLoc.city)) {
        var streetEmpty = (!inputStreet || !inputStreet.value.trim());
        if (streetEmpty) {
          applyLocationData(savedLoc, 'Auto-filled from Saved Location');
        }
      }
    }
  } catch (e) {
    console.warn('Error reading saved location:', e);
  }

  // 2. "Use My Current Location" button click handler
  if (btnUseCurrentLoc) {
    btnUseCurrentLoc.addEventListener('click', function() {
      btnUseCurrentLoc.disabled = true;
      btnUseCurrentLoc.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span>Detecting GPS Location...</span>';
      if (bookingLocStatus) {
        bookingLocStatus.style.display = 'block';
        bookingLocStatus.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Requesting GPS coordinates...</span>';
      }

      function tryIpFallback(reasonMsg) {
        if (bookingLocStatus) {
          bookingLocStatus.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Detecting location via network IP...</span>';
        }
        fetch('https://freeipapi.com/api/json')
          .then(function(res) { return res.json(); })
          .then(function(data) {
            var city = (data.cityName || '').trim();
            var region = (data.regionName || '').trim();
            var country = (data.countryName || '').trim();
            var zip = (data.zipCode || '').trim();
            if (city || region || country) {
              var locObj = {
                houseNo: '',
                street: city ? (city + ' Area') : 'Doorstep Service Area',
                area: city,
                city: city,
                state: region,
                country: country,
                pincode: zip,
                fullAddress: [city, region, country].filter(Boolean).join(', ')
              };
              localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
              applyLocationData(locObj, 'Filled via Network IP (' + (city || country) + ')');
            } else {
              throw new Error('No location from IP');
            }
          })
          .catch(function() {
            btnUseCurrentLoc.disabled = false;
            btnUseCurrentLoc.innerHTML = '<i class="fa fa-crosshairs"></i> <span>Use My Current Location</span>';
            if (bookingLocStatus) {
              bookingLocStatus.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i> Could not detect location automatically. Please enter your address manually.</span>';
            }
          });
      }

      if (!('geolocation' in navigator)) {
        tryIpFallback('Geolocation not supported');
        return;
      }

      navigator.geolocation.getCurrentPosition(
        function(pos) {
          var lat = pos.coords.latitude;
          var lon = pos.coords.longitude;

          if (bookingLocStatus) {
            bookingLocStatus.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Resolving your location...</span>';
          }

          var nominatimUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&zoom=18&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon);

          fetch(nominatimUrl)
            .then(function(res) {
              if (!res.ok) throw new Error('Nominatim HTTP ' + res.status);
              return res.json();
            })
            .then(function(data) {
              var addr = (data && data.address) || {};
              var houseNo = addr.house_number || addr.building || addr.flat || '';
              var road = addr.road || addr.street || addr.pedestrian || addr.footway || '';
              var subLocality = addr.suburb || addr.neighbourhood || addr.residential || addr.subdistrict || addr.quarter || '';
              var locality = addr.city || addr.town || addr.village || addr.municipality || addr.city_district || addr.county || '';
              var district = addr.state_district || addr.district || '';
              var state = addr.state || addr.region || addr.province || '';
              var country = addr.country || '';
              var postcode = (addr.postcode || '').trim();

              var street = road || subLocality || (district && district !== locality ? district + ' Area' : (locality ? locality + ' Area' : ''));
              var cityVal = locality || district || subLocality || '';
              var stateVal = state || district || '';

              var locObj = {
                houseNo: houseNo,
                street: street,
                area: subLocality || district || '',
                city: cityVal,
                state: stateVal,
                country: country,
                pincode: postcode,
                fullAddress: data.display_name || [street, cityVal, stateVal, country].filter(Boolean).join(', ')
              };

              localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
              applyLocationData(locObj, 'Filled via GPS (' + (cityVal || country || 'Live') + ')');
            })
            .catch(function(geoErr) {
              console.warn('Nominatim failed, falling back to BigDataCloud:', geoErr);
              var bdcUrl = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + encodeURIComponent(lat) + '&longitude=' + encodeURIComponent(lon) + '&localityLanguage=en';
              fetch(bdcUrl)
                .then(function(r) { return r.json(); })
                .then(function(bdc) {
                  var city = (bdc.city || bdc.locality || '').trim();
                  var state = (bdc.principalSubdivision || '').trim();
                  var country = (bdc.countryName || '').trim();
                  var zip = (bdc.postcode || '').trim();
                  var locObj = {
                    houseNo: '',
                    street: bdc.locality && bdc.locality !== city ? bdc.locality : (city ? city + ' Area' : 'Doorstep Service Area'),
                    area: bdc.locality || city,
                    city: city || state,
                    state: state,
                    country: country,
                    pincode: zip,
                    fullAddress: [city, state, country].filter(Boolean).join(', ')
                  };
                  localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
                  applyLocationData(locObj, 'Filled via GPS (' + (city || country || 'Live') + ')');
                })
                .catch(function() {
                  tryIpFallback('Reverse geocode failed');
                });
            });
        },
        function(err) {
          console.warn('GPS error, using IP fallback:', err);
          tryIpFallback('GPS permission denied or timeout');
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
      );
    });
  }
});
</script>
