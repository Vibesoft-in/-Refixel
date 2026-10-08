<?php
$bNo = \App\Core\View::e($booking['booking_no']);
$status = \App\Core\View::e($booking['status']);
$payments = $payments ?? [];
$invoices = $invoices ?? [];
$review = $review ?? null;

$hasPaidPayment = false;
foreach ($payments as $p) {
    if ($p['status'] === 'paid') {
        $hasPaidPayment = true;
        break;
    }
}
?>
  <div class="row">
    <!-- Booking Details Column -->
    <div class="col-lg-8 mb-4">
      <div class="card p-4 p-md-5 border-0 shadow-sm" style="border-radius: 16px;">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 10px;">
          <div>
            <span class="text-muted small">Booking Number</span>
            <h2 class="font-weight-bold mb-0" style="font-size: 26px; color: #1a1a1a;">#<?= $bNo ?></h2>
          </div>
          <?php
          $badgeClass = match($booking['status']) {
            'new'              => 'warning',
            'assigned'         => 'info',
            'accepted'         => 'primary',
            'in_progress'      => 'primary',
            'completed'        => 'success',
            'invoiced'         => 'success',
            'reviewed'         => 'success',
            'rescheduled'      => 'info',
            'cancelled'        => 'danger',
            'refund_requested' => 'warning',
            'refunded'         => 'dark',
            default            => 'secondary'
          };
          ?>
          <span class="badge badge-<?= $badgeClass ?> px-3 py-2 font-weight-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">
            <?= str_replace('_', ' ', $status) ?>
          </span>
        </div>

        <div class="p-3 rounded bg-light border mb-4">
          <h6 class="font-weight-bold mb-3" style="font-size: 14px; color: #0a1c33;">Service Progress Tracking</h6>
          <?php if (in_array($booking['status'], ['cancelled', 'refund_requested', 'refunded'])): ?>
            <div class="alert alert-danger mb-0 small">
              <i class="fa fa-exclamation-triangle mr-1"></i>
              <strong>Booking <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>:</strong>
              <?php if ($booking['status'] === 'cancelled'): ?>
                This service visit has been cancelled.
              <?php elseif ($booking['status'] === 'refund_requested'): ?>
                Refund request is under review by our accounts team.
              <?php elseif ($booking['status'] === 'refunded'): ?>
                Payment refund has been processed.
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="d-flex justify-content-between text-center" style="gap: 15px; font-size: 12px; overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
              <div class="<?= in_array($booking['status'], ['new', 'rescheduled', 'assigned', 'accepted', 'in_progress', 'completed', 'invoiced', 'reviewed', 'closed']) ? 'text-success font-weight-bold' : 'text-muted' ?>">
                <i class="fa fa-dot-circle-o d-block mb-1 mx-auto" style="font-size: 18px;"></i>
                <?= $booking['status'] === 'rescheduled' ? 'Rescheduled' : 'Booked' ?>
              </div>
              <div class="<?= in_array($booking['status'], ['assigned', 'accepted', 'in_progress', 'completed', 'invoiced', 'reviewed', 'closed']) ? 'text-success font-weight-bold' : 'text-muted' ?>">
                <i class="fa fa-user d-block mb-1 mx-auto" style="font-size: 18px;"></i> Assigned
              </div>
              <div class="<?= in_array($booking['status'], ['in_progress', 'completed', 'invoiced', 'reviewed', 'closed']) ? 'text-success font-weight-bold' : 'text-muted' ?>">
                <i class="fa fa-wrench d-block mb-1 mx-auto" style="font-size: 18px;"></i> In Progress
              </div>
              <div class="<?= in_array($booking['status'], ['completed', 'invoiced', 'reviewed', 'closed']) ? 'text-success font-weight-bold' : 'text-muted' ?>">
                <i class="fa fa-check-circle d-block mb-1 mx-auto" style="font-size: 18px;"></i> Completed
              </div>
              <div class="<?= in_array($booking['status'], ['invoiced', 'reviewed', 'closed']) ? 'text-success font-weight-bold' : 'text-muted' ?>">
                <i class="fa fa-file-text-o d-block mb-1 mx-auto" style="font-size: 18px;"></i> Invoiced
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Service Information -->
        <h5 class="font-weight-bold mb-3" style="font-size: 18px; color: #0a1c33;">Service Information</h5>
        <div class="row mb-4">
          <div class="col-md-6 mb-2">
            <span class="text-muted small d-block">Service Requested:</span>
            <strong><?= \App\Core\View::e($booking['service_name'] ?? 'Home Maintenance') ?></strong>
          </div>
          <div class="col-md-6 mb-2">
            <span class="text-muted small d-block">Scheduled Slot:</span>
            <strong><?= \App\Core\View::e($booking['preferred_date']) ?> (<?= \App\Core\View::e($booking['preferred_time']) ?>)</strong>
          </div>
          <div class="col-12 mt-2">
            <span class="text-muted small d-block">Service Address:</span>
            <p class="mb-0 text-dark"><?= \App\Core\View::e($booking['address']) ?>, <?= \App\Core\View::e($booking['city'] ?? '') ?> - <?= \App\Core\View::e($booking['pincode'] ?? '') ?></p>
          </div>
          <?php if (!empty($booking['issue_details'])): ?>
            <div class="col-12 mt-2">
              <span class="text-muted small d-block">Specific Instructions / Issue Notes:</span>
              <p class="mb-0 text-muted fst-italic">"<?= \App\Core\View::e($booking['issue_details']) ?>"</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Assigned Technician (if present) -->
        <?php if (!empty($booking['staff_name'])): ?>
          <h5 class="font-weight-bold mb-3" style="font-size: 18px; color: #0a1c33;">Assigned Professional</h5>
          <div class="p-3 rounded bg-light border d-flex align-items-center mb-4">
            <img src="<?= \App\Core\View::asset('img/account.png') ?>" alt="Technician" class="rounded-circle mr-3" style="width: 48px; height: 48px;">
            <div>
              <h6 class="font-weight-bold mb-0"><?= \App\Core\View::e($booking['staff_name']) ?></h6>
              <small class="font-weight-bold" style="color: #f25b29;"><i class="fa fa-shield"></i> Verified REFIXEL Partner</small>
            </div>
          </div>
        <?php endif; ?>

        <!-- Customer Booking Actions (Reschedule / Cancel / Refund Request) -->
        <?php if (\App\Core\Workflow::canTransition((string)$booking['status'], \App\Core\Workflow::STATUS_CANCELLED, 'customer') || \App\Core\Workflow::canTransition((string)$booking['status'], \App\Core\Workflow::STATUS_RESCHEDULED, 'customer')): ?>
          <div class="border-top pt-4 mt-2">
            <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Need to modify this booking?</h5>
            <div class="d-flex flex-wrap" style="gap: 10px;">
              <button type="button" class="btn btn-outline-primary font-weight-bold btn-sm" data-toggle="modal" data-target="#rescheduleModal">
                <i class="fa fa-calendar mr-1"></i> Reschedule Visit
              </button>
              <button type="button" class="btn btn-outline-danger font-weight-bold btn-sm" data-toggle="modal" data-target="#cancelModal">
                <i class="fa fa-times mr-1"></i> Cancel Booking
              </button>
            </div>
          </div>
        <?php elseif ($booking['status'] === 'cancelled' && $hasPaidPayment): ?>
          <div class="border-top pt-4 mt-2">
            <h5 class="font-weight-bold mb-2" style="font-size: 16px;">Paid Booking Cancellation</h5>
            <p class="small text-muted mb-3">You have paid transactions associated with this cancelled booking. You can request a full refund.</p>
            <button type="button" class="btn btn-warning font-weight-bold btn-sm" data-toggle="modal" data-target="#refundModal">
              <i class="fa fa-undo mr-1"></i> Request Refund
            </button>
          </div>
        <?php endif; ?>

        <!-- Customer Reviews Section (Prompt 10) -->
        <?php if (in_array($booking['status'], ['completed', 'invoiced', 'reviewed', 'closed'])): ?>
          <div class="border-top pt-4 mt-4">
            <h5 class="font-weight-bold mb-3" style="font-size: 18px; color: #0a1c33;">Service Feedback & Review</h5>
            <?php if (!empty($review)): ?>
              <div class="p-3 rounded bg-light border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="text-warning" style="font-size: 18px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i class="fa fa-star<?= $i <= (int)$review['rating'] ? '' : '-o' ?>"></i>
                    <?php endfor; ?>
                    <span class="text-dark font-weight-bold ml-2" style="font-size: 14px;"><?= (int)$review['rating'] ?>/5 Stars</span>
                  </div>
                  <?php if (!empty($review['is_approved'])): ?>
                    <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i> Verified & Public</span>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o mr-1"></i> Under Moderation</span>
                  <?php endif; ?>
                </div>
                <p class="mb-1 text-dark" style="font-size: 14.5px;">"<?= \App\Core\View::e($review['comment']) ?>"</p>
                <small class="text-muted">Submitted on <?= \App\Core\View::e(substr((string)$review['created_at'], 0, 10)) ?></small>
              </div>
            <?php else: ?>
              <form method="POST" action="<?= \App\Core\View::url('/account/bookings/' . (int)$booking['id'] . '/review') ?>" class="p-4 rounded border bg-light">
                <?= \App\Core\View::csrfField() ?>
                <h6 class="font-weight-bold mb-2">Rate Your Experience</h6>
                <p class="small text-muted mb-3">Help other homeowners in your city by rating your technician and service quality.</p>

                <div class="form-group mb-3">
                  <label class="small font-weight-bold d-block">Rating</label>
                  <div class="d-flex" style="gap: 15px;">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                      <div class="custom-control custom-radio">
                        <input type="radio" id="star_<?= $i ?>" name="rating" value="<?= $i ?>" class="custom-control-input" <?= $i === 5 ? 'checked' : '' ?>>
                        <label class="custom-control-label font-weight-bold text-warning" for="star_<?= $i ?>">
                          <?= $i ?> <i class="fa fa-star"></i>
                        </label>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label class="small font-weight-bold">Your Review & Comments <span class="text-danger">*</span></label>
                  <textarea name="comment" class="form-control" rows="3" placeholder="Tell us how the service went, timeliness, and technician professionalism..." required></textarea>
                </div>

                <button type="submit" class="btn text-white font-weight-bold px-4 py-2" style="background:#f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
                  Submit Review
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Booking Summary Sidebar -->
    <div class="col-lg-4">
      <div class="card p-4 border-0 shadow-sm mb-4" style="border-radius: 16px; background:#fff8f5; border:1px solid #ffdacf;">
        <h5 class="font-weight-bold mb-3" style="font-size: 18px; color: #0a1c33;">Payment & Invoices</h5>

        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Starting Base Price:</span>
          <span class="font-weight-bold text-dark">₹<?= number_format((float)($booking['starting_price'] ?? 0), 0) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Payment Status:</span>
          <span class="badge badge-light p-1 font-weight-bold"><?= strtoupper(\App\Core\View::e($booking['payment_status'] ?? 'PENDING')) ?></span>
        </div>

        <hr>

        <?php if (!empty($invoices)): ?>
          <h6 class="font-weight-bold mb-2 small text-dark">Available GST Invoices:</h6>
          <?php foreach ($invoices as $inv): ?>
            <a href="<?= \App\Core\View::url('/account/invoices/' . (int)$inv['id']) ?>" class="btn font-weight-bold btn-sm w-100 mb-2 text-left d-flex justify-content-between align-items-center" style="border: 1px solid #ffdacf; background: #ffffff; color: #f25b29;">
              <span><i class="fa fa-file-text-o mr-2"></i><?= \App\Core\View::e($inv['invoice_no']) ?></span>
              <span>₹<?= number_format((float)$inv['total'], 2) ?> &rarr;</span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-info small mb-3">
            <i class="fa fa-info-circle mr-1"></i> Payment receipt & GST tax invoice are issued upon technician service completion.
          </div>
        <?php endif; ?>

        <a href="https://api.whatsapp.com/send?phone=+919953358855&text=Inquiry%20regarding%20booking%20<?= urlencode($bNo) ?>" target="_blank" class="btn btn-outline-success py-2 font-weight-bold w-100 small mt-2" style="border-radius: 8px;">
          <i class="fa fa-whatsapp mr-1"></i> Need Help with this Booking?
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reschedule Booking -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="<?= \App\Core\View::url('/account/bookings/' . (int)$booking['id'] . '/reschedule') ?>">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Reschedule Service Visit</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Select a new preferred date and time slot for your service appointment.</p>
          <div class="form-group mb-3">
            <label class="small font-weight-bold">Preferred New Date <span class="text-danger">*</span></label>
            <div class="position-relative" id="reschedDatePickerContainer">
              <input type="hidden" name="preferred_date" id="reschedPreferredDateInput" value="<?= \App\Core\View::e($booking['preferred_date'] ?: date('Y-m-d')) ?>" required>
              
              <div class="cal-input-trigger" id="reschedDateInputTrigger" role="button" tabindex="0">
                <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                  <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="text" id="reschedDisplayDateInput" class="cal-custom-input" readonly value="<?= date('d M Y', strtotime($booking['preferred_date'] ?: 'now')) ?>" placeholder="Click to select date...">
                </div>
                <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="reschedCalChevronIcon"></i>
              </div>

              <!-- Dropdown / Popover Calendar -->
              <div id="reschedCalendarDropdown" class="calendar-dropdown-popover" style="position: absolute; top: calc(100% + 6px); left: 0; z-index: 1060;">
                <div class="refixel-calendar-card">
                  <!-- Top Header: Day Number, Month Name & Navigation -->
                  <div class="cal-top-header">
                    <div class="cal-title-wrap">
                      <div id="reschedCalDayNumber" class="cal-day-num"><?= date('j', strtotime($booking['preferred_date'] ?: 'now')) ?></div>
                      <div id="reschedCalMonthName" class="cal-month-name"><?= date('F', strtotime($booking['preferred_date'] ?: 'now')) ?></div>
                    </div>
                    <div class="cal-nav-wrap d-flex align-items-center" style="gap: 6px;">
                      <button type="button" id="reschedCalPrevMonthBtn" class="cal-nav-btn" title="Previous Month" aria-label="Previous Month">
                        <i class="fa fa-chevron-left" style="font-size: 11px;"></i>
                      </button>
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
                      <button type="button" id="reschedCalNextMonthBtn" class="cal-nav-btn" title="Next Month" aria-label="Next Month">
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
                  <div id="reschedCalDaysGrid" class="cal-days-grid">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="small font-weight-bold">Preferred Time Slot <span class="text-danger">*</span></label>
            <select name="preferred_time" class="form-control" required>
              <option value="09:00 AM - 12:00 PM" <?= $booking['preferred_time'] === '09:00 AM - 12:00 PM' ? 'selected' : '' ?>>09:00 AM - 12:00 PM (Morning)</option>
              <option value="12:00 PM - 03:00 PM" <?= $booking['preferred_time'] === '12:00 PM - 03:00 PM' ? 'selected' : '' ?>>12:00 PM - 03:00 PM (Afternoon)</option>
              <option value="03:00 PM - 06:00 PM" <?= $booking['preferred_time'] === '03:00 PM - 06:00 PM' ? 'selected' : '' ?>>03:00 PM - 06:00 PM (Evening)</option>
              <option value="06:00 PM - 08:00 PM" <?= $booking['preferred_time'] === '06:00 PM - 08:00 PM' ? 'selected' : '' ?>>06:00 PM - 08:00 PM (Late)</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn text-white font-weight-bold" style="background:#f25b29; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">Confirm Reschedule</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Cancel Booking -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="<?= \App\Core\View::url('/account/bookings/' . (int)$booking['id'] . '/cancel') ?>">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold text-danger">Cancel Booking</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p class="text-dark">Are you sure you want to cancel booking <strong>#<?= $bNo ?></strong>?</p>
          <div class="form-group mb-3">
            <label class="small font-weight-bold">Cancellation Reason</label>
            <select name="reason" class="form-control">
              <option value="Plan changed / No longer required">Plan changed / No longer required</option>
              <option value="Booked by mistake">Booked by mistake</option>
              <option value="Found alternative service">Found alternative service</option>
              <option value="Scheduling conflict">Scheduling conflict</option>
              <option value="Other">Other reason</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Keep Booking</button>
          <button type="submit" class="btn btn-danger font-weight-bold">Yes, Cancel Booking</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Refund Request -->
<?php if ($booking['status'] === 'cancelled' && $hasPaidPayment): ?>
<div class="modal fade" id="refundModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="<?= \App\Core\View::url('/account/bookings/' . (int)$booking['id'] . '/refund-request') ?>">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Request Payment Refund</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p class="small text-muted mb-3">Please specify details for refund processing. Our accounts team will review and refund to your original payment method or bank account.</p>
          <div class="form-group mb-3">
            <label class="small font-weight-bold">Reason for Refund / Notes <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason and UPI ID or account details if needed..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-warning font-weight-bold">Submit Refund Request</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
#rescheduleModal .modal-content,
#rescheduleModal .modal-body {
  overflow: visible !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var datePickerContainer = document.getElementById('reschedDatePickerContainer');
  var dateInputTrigger = document.getElementById('reschedDateInputTrigger');
  var displayDateInput = document.getElementById('reschedDisplayDateInput');
  var calendarDropdown = document.getElementById('reschedCalendarDropdown');
  var preferredDateInput = document.getElementById('reschedPreferredDateInput');
  var calDayNumber = document.getElementById('reschedCalDayNumber');
  var calMonthName = document.getElementById('reschedCalMonthName');
  var calDaysGrid = document.getElementById('reschedCalDaysGrid');
  var calPrevMonthBtn = document.getElementById('reschedCalPrevMonthBtn');
  var calNextMonthBtn = document.getElementById('reschedCalNextMonthBtn');

  if (calDaysGrid && preferredDateInput && dateInputTrigger && calendarDropdown) {
    var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var initVal = preferredDateInput.value;
    var selectedDate = initVal ? new Date(initVal) : new Date(today.getTime());
    if (isNaN(selectedDate.getTime())) selectedDate = new Date(today.getTime());
    selectedDate.setHours(0, 0, 0, 0);

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

    dateInputTrigger.addEventListener('click', function() {
      toggleDropdown();
    });

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
    renderCalendar();
  }
});
</script>
