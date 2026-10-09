<?php
/**
 * REFIXEL - Customer Cart View
 */
$user = \App\Core\Auth::user();
$customerProfile = $user ? \App\Core\Database::fetchOne("SELECT address, city, pincode FROM customer_profiles WHERE user_id = ?", [$user['id']]) : null;
$currentCity = $_SESSION['selected_city'] ?? ($customerProfile['city'] ?? 'Gurugram');
$items = $cart['items'] ?? [];
$isEmpty = $cart['is_empty'] ?? empty($items);
?>
<div class="container py-5 my-3">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0 mb-4" style="font-size: 14px;">
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/') ?>" style="color:#f25b29;">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/services') ?>" style="color:#f25b29;">Services</a></li>
      <li class="breadcrumb-item active" aria-current="page">Your Service Cart</li>
    </ol>
  </nav>

  <?php if ($isEmpty): ?>
    <!-- Empty Cart State -->
    <div class="row justify-content-center py-5">
      <div class="col-md-7 col-lg-5 text-center">
        <div class="card p-5 border-0 shadow-sm" style="border-radius: 20px;">
          <div class="mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 90px; height: 90px; background: #fff3ec; color: #f25b29; font-size: 42px;">
              <i class="fa fa-shopping-cart"></i>
            </div>
          </div>
          <h3 class="font-weight-bold mb-2" style="color: #1a1a1a;">Your cart is empty</h3>
          <p class="text-muted small mb-4">You haven't selected any home or commercial maintenance packages yet. Explore our verified services to get started.</p>
          <a href="<?= \App\Core\View::url('/services') ?>" class="btn text-white px-5 py-3 font-weight-bold" style="background:#f25b29; border-radius: 50px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);">
            Explore Services Now &rarr;
          </a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Active Cart & Booking Form -->
    <div class="row">
      <!-- Cart Items List -->
      <div class="col-lg-7 mb-4">
        <div class="card p-4 border-0 shadow-sm mb-4" style="border-radius: 16px;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="font-weight-bold mb-0" style="font-size: 20px;">Selected Services (<?= count($items) ?>)</h4>
            <button type="button" class="btn btn-sm btn-link text-danger font-weight-bold p-0" id="clearCartBtn">
              Clear All
            </button>
          </div>

          <div class="cart-items-wrapper">
            <?php foreach ($items as $item): ?>
              <div class="d-flex align-items-center justify-content-between py-3 border-bottom cart-row" data-id="<?= $item['service_id'] ?>">
                <div class="d-flex align-items-center">
                  <img src="<?= \App\Core\View::asset('img/' . ($item['image'] ?? 'Full-home-clean.jpg')) ?>"
                       alt="<?= \App\Core\View::e($item['name']) ?>"
                       class="rounded mr-3" style="width: 70px; height: 70px; object-fit: cover;"
                       onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/Full-home-clean.jpg') ?>';">
                  <div>
                    <h6 class="font-weight-bold mb-1" style="font-size: 15px;"><?= \App\Core\View::e($item['name']) ?></h6>
                    <small class="text-muted d-block">Est. duration: ~<?= (int)$item['duration'] ?> mins</small>
                    <span class="font-weight-bold" style="color: #f25b29;">₹<?= number_format((float)$item['unit_price'], 0) ?> each</span>
                  </div>
                </div>

                <div class="text-right">
                  <div class="d-flex align-items-center justify-content-end mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 cart-minus-btn" data-id="<?= $item['service_id'] ?>">&minus;</button>
                    <span class="px-3 font-weight-bold" style="font-size: 14px;"><?= $item['quantity'] ?></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 cart-plus-btn" data-id="<?= $item['service_id'] ?>">&plus;</button>
                  </div>
                  <h6 class="font-weight-bold mb-0">₹<?= number_format((float)$item['line_total'], 0) ?></h6>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Doorstep Booking Schedule Form -->
        <div class="card p-4 p-md-5 border-0 shadow-sm" style="border-radius: 16px;">
          <h4 class="font-weight-bold mb-2" style="font-size: 20px;">Doorstep Visit Details</h4>
          <p class="text-muted small mb-4">Enter your schedule and service location. Verified technicians will be matched to your booking.</p>

          <form action="<?= \App\Core\View::url('/book') ?>" method="POST" enctype="multipart/form-data" id="cartCheckoutForm">
            <?= \App\Core\View::csrf() ?>
            <input type="hidden" name="from_cart" value="1">
            <input type="hidden" name="service_id" value="<?= (int)($items[0]['service_id'] ?? 1) ?>">

            <div class="form-row">
              <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Your Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= \App\Core\View::e($user['name'] ?? '') ?>" placeholder="e.g. Rahul Verma" required>
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
              <input type="email" name="email" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? '') ?>" placeholder="rahul@example.com">
            </div>

            <!-- Service Address Header with Auto-fill & "Use My Current Location" button -->
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 mb-3" style="gap: 10px;">
              <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <h5 class="font-weight-bold mb-0" style="font-size: 17px; color: #0a1c33;">Service Address</h5>
                <span id="cartBookingAutoFillBadge" class="badge" style="display: none; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 11.5px; font-weight: 600; padding: 4px 9px; border-radius: 6px;">
                  <i class="fa fa-check-circle mr-1"></i> Auto-filled from Home Page
                </span>
              </div>
              <button type="button" id="cartBtnUseCurrentLocation" class="btn btn-sm btn-use-curr-loc" style="background: #fff3ec; color: #f25b29; border: 1.5px solid #ffdacf; font-weight: 600; border-radius: 8px; padding: 7px 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s ease; box-shadow: 0 2px 6px rgba(242, 91, 41, 0.08); cursor: pointer;">
                <i class="fa fa-crosshairs"></i> <span>Use My Current Location</span>
              </button>
            </div>
            <div id="cartBookingLocStatus" class="small mb-3" style="display: none;"></div>

            <div class="form-group mb-3">
              <label class="font-weight-bold small d-block mb-2">Address Type</label>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="typeHomeCart" name="address_type" class="custom-control-input" value="Home" <?= ($customerProfile['address_type'] ?? 'Home') === 'Home' ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="typeHomeCart">Home</label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="typeOfficeCart" name="address_type" class="custom-control-input" value="Office" <?= ($customerProfile['address_type'] ?? '') === 'Office' ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="typeOfficeCart">Office</label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="typeOtherCart" name="address_type" class="custom-control-input" value="Other" <?= ($customerProfile['address_type'] ?? '') === 'Other' ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="typeOtherCart">Other</label>
              </div>
            </div>

            <div class="form-row">
              <div class="col-md-6 form-group">
                <label class="font-weight-bold small">House / Flat / Office No. <span class="text-danger">*</span></label>
                <input type="text" name="house_no" id="cartInputHouseNo" class="form-control" value="<?= \App\Core\View::e($customerProfile['house_no'] ?? '') ?>" placeholder="e.g. Flat 604 / House No." required>
              </div>
              <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Street / Society / Area <span class="text-danger">*</span></label>
                <input type="text" name="street" id="cartInputStreet" class="form-control" value="<?= \App\Core\View::e($customerProfile['street'] ?? '') ?>" placeholder="e.g. Colony, Street, Apartment" required autocomplete="off">
              </div>
            </div>

            <div class="form-row">
              <div class="col-md-4 form-group">
                <label class="font-weight-bold small">City <span class="text-danger">*</span></label>
                <input type="text" name="city" id="cartInputCity" class="form-control" value="<?= \App\Core\View::e($customerProfile['city'] ?? '') ?>" placeholder="e.g. City / Town" required>
              </div>
              <div class="col-md-4 form-group">
                <label class="font-weight-bold small">State <span class="text-danger">*</span></label>
                <input type="text" name="state" id="cartInputState" class="form-control" value="<?= \App\Core\View::e($customerProfile['state'] ?? '') ?>" placeholder="e.g. State / Province / Region" required>
              </div>
              <div class="col-md-4 form-group">
                <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
                <input type="text" name="pincode" id="cartInputPincode" class="form-control" placeholder="Postal Code / PIN" value="<?= \App\Core\View::e($customerProfile['pincode'] ?? '') ?>" required>
              </div>
            </div>
            
            <div class="form-group">
              <label class="font-weight-bold small">Complete Address / Landmark (Optional)</label>
              <textarea name="address" id="cartInputAddress" rows="2" class="form-control" placeholder="Any extra landmark details"><?= \App\Core\View::e($customerProfile['address'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
              <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Preferred Service Date <span class="text-danger">*</span></label>
                <div class="position-relative" id="cartDatePickerContainer">
                  <!-- Hidden input for form submission -->
                  <input type="hidden" name="preferred_date" id="cartPreferredDateInput" value="<?= date('Y-m-d') ?>" required>
                  
                  <!-- Clickable Date Trigger -->
                  <div class="cal-input-trigger" id="cartDateInputTrigger" role="button" tabindex="0">
                    <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                      <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" id="cartDisplayDateInput" class="cal-custom-input" readonly value="<?= date('d M Y') ?>" placeholder="Click to select date...">
                    </div>
                    <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="cartCalChevronIcon"></i>
                  </div>

                  <!-- Dropdown / Popover Calendar -->
                  <div id="cartCalendarDropdown" class="calendar-dropdown-popover">
                    <div class="refixel-calendar-card">
                      <!-- Top Header: Day Number, Month Name & Navigation -->
                      <div class="cal-top-header">
                        <div class="cal-title-wrap">
                          <div id="cartCalDayNumber" class="cal-day-num"><?= date('j') ?></div>
                          <div id="cartCalMonthName" class="cal-month-name"><?= date('F') ?></div>
                        </div>
                        <div class="cal-nav-wrap d-flex align-items-center" style="gap: 6px;">
                          <button type="button" id="cartCalPrevMonthBtn" class="cal-nav-btn" title="Previous Month" aria-label="Previous Month">
                            <i class="fa fa-chevron-left" style="font-size: 11px;"></i>
                          </button>
                          <!-- Mini 3D Calendar Icon Badge -->
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
                          <button type="button" id="cartCalNextMonthBtn" class="cal-nav-btn" title="Next Month" aria-label="Next Month">
                            <i class="fa fa-chevron-right" style="font-size: 11px;"></i>
                          </button>
                        </div>
                      </div>

                      <!-- 3-Segment Accent Bar -->
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
                      <div id="cartCalDaysGrid" class="cal-days-grid">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group">
                <label class="font-weight-bold small">Preferred Time Slot <span class="text-danger">*</span></label>
                
                <div class="position-relative" id="cartTimePickerContainer">
                  <!-- Hidden input for form submission -->
                  <input type="hidden" name="preferred_time" id="cartPreferredTimeInput" value="02:00 - 04:00 PM" required>

                  <!-- Clickable Time Input Trigger -->
                  <div class="cal-input-trigger" id="cartTimeInputTrigger" role="button" tabindex="0">
                    <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                      <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                        <i class="fa fa-clock-o"></i>
                      </div>
                      <input type="text" id="cartDisplayTimeInput" class="cal-custom-input" readonly value="02:00 - 04:00 PM" placeholder="Click to select time slot...">
                    </div>
                    <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="cartTimeChevronIcon"></i>
                  </div>

                  <!-- Dropdown / Popover Time Slot List -->
                  <div id="cartTimeDropdown" class="time-dropdown-popover">
                    <div class="time-dropdown-menu-card">
                      <div class="time-dropdown-header pb-2 mb-2 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 4px 6px;">
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: #64748b;">Available Arrival Slots</span>
                        <span class="badge" style="background: #fff3ec; color: #ff5238; font-size: 10.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px;">Doorstep Visit</span>
                      </div>
                      <div class="time-options-list" id="cartTimeSlotsGroup">
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

              <!-- Scheduled Slot Badge -->
              <div class="col-12 mt-2 mb-3">
                <div class="schedule-summary-box p-3 rounded-lg" style="background: #fff8f5; border: 1.5px dashed #ffdacf; border-radius: 14px;">
                  <div class="d-flex align-items-center">
                    <div class="schedule-summary-icon mr-3" style="width: 38px; height: 38px; border-radius: 12px; background: #ff5238; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 82, 56, 0.3);">
                      <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <div>
                      <div class="small" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: #f25b29;">Confirmed Slot</div>
                      <div class="font-weight-bold text-dark" id="cartCalSummaryDisplay" style="font-size: 13.5px;">Loading schedule...</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="font-weight-bold small">Booking Priority</label>
              <select name="priority" class="form-control">
                <option value="normal" selected>Standard Scheduled Visit</option>
                <option value="high">High Priority (Urgent requirement)</option>
              </select>
            </div>

            <!-- Issue Photo Upload -->
            <div class="form-group">
              <label class="font-weight-bold small">Upload Issue Photos (Optional - Max 5MB)</label>
              <input type="file" name="photos[]" class="form-control-file" accept="image/jpeg,image/png,image/webp,application/pdf" multiple>
              <small class="text-muted">Upload photos of the stain, leak, or specific area needing cleaning/repair.</small>
            </div>

            <div class="form-group mb-4">
              <label class="font-weight-bold small">Special Instructions / Issue Details</label>
              <textarea name="issue_details" rows="2" class="form-control" placeholder="Any access gates, specific stains, or precautions"></textarea>
            </div>

            <button type="submit" class="btn text-white py-3 font-weight-bold w-100" style="background:#f25b29; border-radius: 8px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);">
              Confirm Booking & Schedule Visit &rarr;
            </button>
          </form>
        </div>
      </div>

      <!-- Price Breakdown Sticky Summary -->
      <div class="col-lg-5">
        <div class="card p-4 border-0 shadow-sm sticky-top" style="top: 90px; border-radius: 16px; background:#fff8f5; border: 1px solid #ffdacf;">
          <h4 class="font-weight-bold mb-3" style="font-size: 20px;">Price Summary</h4>

          <div class="d-flex justify-content-between mb-2 small text-muted">
            <span>Item Subtotal (<?= $cart['count'] ?> items):</span>
            <span class="font-weight-bold text-dark">₹<?= number_format((float)$cart['subtotal'], 0) ?></span>
          </div>
          <div class="d-flex justify-content-between mb-2 small text-muted">
            <span>Safety, Hygiene & Tools:</span>
            <span class="font-weight-bold" style="color: #f25b29;">FREE</span>
          </div>
          <div class="d-flex justify-content-between mb-2 small text-muted">
            <span>GST Tax (18%):</span>
            <span class="text-dark">₹<?= number_format((float)$cart['tax'], 2) ?></span>
          </div>
          <hr>
          <div class="d-flex justify-content-between align-items-baseline mb-4">
            <span class="font-weight-bold" style="font-size: 17px;">Total Payable:</span>
            <h3 class="font-weight-bold mb-0" style="color: #f25b29;">₹<?= number_format((float)$cart['total'], 0) ?></h3>
          </div>

          <div class="p-3 rounded bg-white border mb-3 small" style="line-height: 1.8;">
            <div class="d-flex align-items-center mb-1 font-weight-bold" style="color: #f25b29;">
              <i class="fa fa-shield mr-2"></i> REFIXEL Guarantee
            </div>
            <p class="text-muted mb-0">Zero advance payment. Pay after inspection via UPI or Cash. 24-hour satisfaction re-work warranty included.</p>
          </div>

          <div class="text-center small text-muted">
            <i class="fa fa-lock text-success mr-1"></i> 256-Bit SSL Encrypted Checkout
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var baseUrl = '<?= rtrim(\App\Core\View::url(), '/') ?>';

  // Plus Qty
  document.querySelectorAll('.cart-plus-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      fetch(baseUrl + '/api/cart/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({service_id: id, action: 'plus'})
      }).then(function() { window.location.reload(); });
    });
  });

  // Minus Qty
  document.querySelectorAll('.cart-minus-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.getAttribute('data-id');
      fetch(baseUrl + '/api/cart/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({service_id: id, action: 'minus'})
      }).then(function() { window.location.reload(); });
    });
  });

  // Clear Cart
  var clearBtn = document.getElementById('clearCartBtn');
  if (clearBtn) {
    clearBtn.addEventListener('click', function() {
      if (confirm('Are you sure you want to remove all items from your cart?')) {
        fetch(baseUrl + '/api/cart/clear', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'}
        }).then(function() { window.location.reload(); });
      }
    });
  }

  // Interactive Calendar & Time Slot Popovers for Cart
  var datePickerContainer = document.getElementById('cartDatePickerContainer');
  var dateInputTrigger = document.getElementById('cartDateInputTrigger');
  var displayDateInput = document.getElementById('cartDisplayDateInput');
  var calendarDropdown = document.getElementById('cartCalendarDropdown');
  var preferredDateInput = document.getElementById('cartPreferredDateInput');
  var calDayNumber = document.getElementById('cartCalDayNumber');
  var calMonthName = document.getElementById('cartCalMonthName');
  var calDaysGrid = document.getElementById('cartCalDaysGrid');
  var calPrevMonthBtn = document.getElementById('cartCalPrevMonthBtn');
  var calNextMonthBtn = document.getElementById('cartCalNextMonthBtn');
  var cartCalSummaryDisplay = document.getElementById('cartCalSummaryDisplay');

  // Time Slot elements
  var timePickerContainer = document.getElementById('cartTimePickerContainer');
  var timeInputTrigger = document.getElementById('cartTimeInputTrigger');
  var displayTimeInput = document.getElementById('cartDisplayTimeInput');
  var timeDropdown = document.getElementById('cartTimeDropdown');
  var preferredTimeInput = document.getElementById('cartPreferredTimeInput');
  var timeOptionItems = document.querySelectorAll('#cartTimeSlotsGroup .time-option-item');

  if (calDaysGrid && preferredDateInput && dateInputTrigger && calendarDropdown) {
    var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    var today = new Date();
    today.setHours(0, 0, 0, 0);

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

    function updateCartSummary() {
      if (!cartCalSummaryDisplay) return;
      var slotVal = preferredTimeInput ? preferredTimeInput.value : '02:00 - 04:00 PM';
      cartCalSummaryDisplay.textContent = formatDisplayDate(selectedDate) + ' (' + slotVal + ')';
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

    dateInputTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      toggleDropdown();
    });

    if (timeInputTrigger) {
      timeInputTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleTimeDropdown();
      });
    }

    document.addEventListener('click', function(e) {
      if (datePickerContainer && !datePickerContainer.contains(e.target)) {
        toggleDropdown(false);
      }
      if (timePickerContainer && !timePickerContainer.contains(e.target)) {
        toggleTimeDropdown(false);
      }
    });

    // Time Slot Option clicks
    timeOptionItems.forEach(function(opt) {
      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        timeOptionItems.forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        var slot = this.getAttribute('data-slot');
        if (displayTimeInput) displayTimeInput.value = slot;
        if (preferredTimeInput) preferredTimeInput.value = slot;
        updateCartSummary();
        toggleTimeDropdown(false);
      });
    });

    function renderCalendar() {
      calDaysGrid.innerHTML = '';

      calDayNumber.textContent = selectedDate.getDate();
      calMonthName.textContent = monthNames[viewMonth];

      var isCurrentMonth = (viewYear === today.getFullYear() && viewMonth === today.getMonth());
      calPrevMonthBtn.disabled = isCurrentMonth;

      var firstDayOfMonth = new Date(viewYear, viewMonth, 1);
      var startDayIndex = (firstDayOfMonth.getDay() + 6) % 7;
      var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

      for (var b = 0; b < startDayIndex; b++) {
        var blank = document.createElement('div');
        blank.className = 'cal-day-cell empty';
        calDaysGrid.appendChild(blank);
      }

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
              updateCartSummary();
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

    preferredDateInput.value = formatYYYYMMDD(selectedDate);
    displayDateInput.value = formatInputDate(selectedDate);
    if (preferredTimeInput && displayTimeInput) {
      displayTimeInput.value = preferredTimeInput.value || '02:00 - 04:00 PM';
    }
    renderCalendar();
    updateCartSummary();
  }

  /* ══════════════════════════════════════════════════════════════════
     CART AUTO-FILL & "USE MY CURRENT LOCATION" LOGIC
     ══════════════════════════════════════════════════════════════════ */
  var btnUseCurrentLoc    = document.getElementById('cartBtnUseCurrentLocation');
  var bookingLocStatus    = document.getElementById('cartBookingLocStatus');
  var bookingAutoFillBadge= document.getElementById('cartBookingAutoFillBadge');

  var inputHouseNo = document.getElementById('cartInputHouseNo');
  var inputStreet  = document.getElementById('cartInputStreet');
  var inputCity    = document.getElementById('cartInputCity');
  var inputState   = document.getElementById('cartInputState');
  var inputPincode = document.getElementById('cartInputPincode');
  var inputAddress = document.getElementById('cartInputAddress');

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
      var districtName = (loc.district || '').trim().toLowerCase();
      var stateName = (loc.state || '').trim().toLowerCase();
      var countryName = (loc.country || '').trim().toLowerCase();

      function isInvalidStreet(str) {
        if (!str) return true;
        var s = str.trim().toLowerCase();
        if (s === cityName || s === districtName || s === stateName || s === countryName) return true;
        if (districtName && (s === districtName + ' area' || s.indexOf(districtName) !== -1)) return true;
        if (s === 'doorstep service area' || s === 'india' || /^\d+$/.test(s)) return true;
        return false;
      }

      if (isInvalidStreet(resolvedStreet)) {
        resolvedStreet = '';
      }

      // If no valid street, fallback to area or fullAddress tokens (excluding district and invalid strings)
      if (!resolvedStreet) {
        if (loc.area && !isInvalidStreet(loc.area)) {
          resolvedStreet = loc.area.trim();
        } else if (loc.fullAddress) {
          var parts = loc.fullAddress.split(',').map(function(s) { return s.trim(); });
          for (var p = 0; p < parts.length; p++) {
            if (!isInvalidStreet(parts[p])) {
              resolvedStreet = parts[p];
              break;
            }
          }
        }
      }

      if (resolvedStreet) {
        inputStreet.value = resolvedStreet;
        flashHighlight(inputStreet);
        filledCount++;
      } else {
        if (isInvalidStreet(inputStreet.value)) {
          inputStreet.value = '';
          inputStreet.placeholder = 'e.g. Colony, Street, Apartment';
        }
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
    console.warn('Error reading saved location on cart:', e);
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
                street: '',
                area: '',
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
          var photonUrl = 'https://photon.komoot.io/reverse?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon);

          Promise.allSettled([
            fetch(nominatimUrl).then(function(res) { return res.ok ? res.json() : null; }),
            fetch(photonUrl).then(function(res) { return res.ok ? res.json() : null; })
          ]).then(function(results) {
            var data = (results[0].status === 'fulfilled') ? results[0].value : null;
            var phoData = (results[1].status === 'fulfilled') ? results[1].value : null;

            if (!data && !phoData) {
              throw new Error('Both reverse geocoders failed');
            }

            var addr = (data && data.address) || {};
            var phoProps = (phoData && phoData.features && phoData.features[0] && phoData.features[0].properties) || {};

            var houseNo = addr.house_number || addr.building || addr.flat || addr.house_name || phoProps.housenumber || '';
            var locality = addr.city || addr.town || addr.village || addr.municipality || addr.city_district || phoProps.city || '';
            var district = addr.state_district || addr.district || '';
            var state = addr.state || addr.region || addr.province || phoProps.state || '';
            var country = addr.country || phoProps.country || '';
            var postcode = (addr.postcode || phoProps.postcode || '').trim();

            // Extract POI (shop, amenity, landmark)
            var poi = (addr.shop || addr.amenity || addr.office || addr.commercial || addr.building || addr.tourism || addr.leisure || (data && data.name) || phoProps.name || '').trim();
            if (poi && (poi.toLowerCase() === (locality || '').toLowerCase() || poi.toLowerCase() === (district || '').toLowerCase() || poi.toLowerCase() === (state || '').toLowerCase())) {
              poi = '';
            }

            // Extract Road
            var road = (addr.road || addr.street || addr.pedestrian || addr.footway || addr.path || phoProps.street || '').trim();
            if (road && (road.toLowerCase() === (locality || '').toLowerCase() || road.toLowerCase() === (district || '').toLowerCase())) {
              road = '';
            }

            // Extract SubLocality / Colony / Neighbourhood
            var subLocality = (addr.suburb || addr.neighbourhood || addr.residential || addr.subdistrict || addr.quarter || addr.hamlet || (phoProps.district && phoProps.district !== district ? phoProps.district : '') || '').trim();
            if (subLocality && (subLocality.toLowerCase() === (locality || '').toLowerCase() || subLocality.toLowerCase() === (district || '').toLowerCase())) {
              subLocality = '';
            }

            var street = '';
            if (poi && road) {
              street = poi + ', ' + road;
            } else if (poi && subLocality) {
              street = poi + ', ' + subLocality;
            } else if (poi) {
              street = poi;
            } else if (road && subLocality) {
              street = road + ', ' + subLocality;
            } else if (road) {
              street = road;
            } else if (subLocality) {
              street = subLocality;
            } else {
              street = '';
            }

            var cityVal = locality || district || subLocality || '';
            var stateVal = state || '';

            var fullAddr = (data && data.display_name) || [street, cityVal, stateVal, country].filter(Boolean).join(', ');

            var locObj = {
              houseNo: houseNo,
              street: street,
              area: subLocality || poi || road || '',
              district: district,
              city: cityVal,
              state: stateVal,
              country: country,
              pincode: postcode,
              fullAddress: fullAddr
            };

            localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
            applyLocationData(locObj, 'Filled via GPS (' + (cityVal || country || 'Live') + ')');
          })
          .catch(function(geoErr) {
            console.warn('Primary geocoders failed, falling back to BigDataCloud:', geoErr);
            var bdcUrl = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + encodeURIComponent(lat) + '&longitude=' + encodeURIComponent(lon) + '&localityLanguage=en';
            fetch(bdcUrl)
              .then(function(r) { return r.json(); })
              .then(function(bdc) {
                var city = (bdc.city || bdc.locality || '').trim();
                var state = (bdc.principalSubdivision || '').trim();
                var country = (bdc.countryName || '').trim();
                var zip = (bdc.postcode || '').trim();
                var streetVal = (bdc.locality && bdc.locality !== city) ? bdc.locality : '';
                var locObj = {
                  houseNo: '',
                  street: streetVal,
                  area: streetVal || city,
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

