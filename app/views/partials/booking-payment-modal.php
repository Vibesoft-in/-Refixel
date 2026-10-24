<?php
$isRazorpayActive = \App\Models\PaymentGateway::isRazorpayActive();
$isCodActive = \App\Models\PaymentGateway::isCodActive();
$isAdvanceRequired = \App\Models\PaymentGateway::isAdvanceRequired();
$advanceAmount = \App\Models\PaymentGateway::getAdvanceAmount();
$defaultMethod = $isRazorpayActive ? 'online' : 'cod';
?>
<!-- Booking Payment Gateway & Success Modal -->
<div class="modal fade" id="bookingPaymentModal" tabindex="-1" role="dialog" aria-labelledby="bookingPaymentModalTitle" aria-hidden="true" style="z-index: 1075;">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px; width: 92%; margin: 1.5rem auto;">
    <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.22); overflow: hidden; background: #ffffff;">

      <!-- ================= STEP 1: PAYMENT GATEWAYS ================= -->
      <div id="modalStepPayment">
        <!-- Modal Header -->
        <div class="modal-header d-flex align-items-center justify-content-between px-3 py-3" style="background: #0a1c33; color: #ffffff; border-bottom: none;">
          <div>
            <h5 class="modal-title font-weight-bold mb-0" id="bookingPaymentModalTitle" style="font-size: 16px; color: #ffffff;">
              <i class="fa fa-shield mr-1" style="color: #f25b29;"></i> Select Payment Option
            </h5>
            <small style="color: #94a3b8; font-size: 11px;">
              <?= $isAdvanceRequired ? 'Pre-booking Online Confirmation · Doorstep Service' : 'Instant Booking Confirmation · Doorstep Service' ?>
            </small>
          </div>
          <button type="button" class="close text-white rfx-modal-close" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; outline: none; font-size: 24px; padding: 0; margin: 0; line-height: 1; border: none; background: transparent;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-3" style="background: #ffffff;">
          <!-- Amount Highlight Card with Real-time Advance & Due Breakdown -->
          <div class="p-2 mb-3 rounded" style="background: #fff8f5; border: 1.5px dashed #ffdacf;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="small font-weight-bold text-muted">Total Service Value:</span>
              <span class="font-weight-bold" style="color: #f25b29; font-size: 17px;" id="rfxModalPayTotalDisplay">₹899</span>
            </div>
            <div class="d-flex justify-content-between align-items-center small py-1 border-top" style="border-color: #ffdacf !important;">
              <span class="text-muted font-weight-bold">Pay Now Online (Advance):</span>
              <span class="font-weight-bold text-success" id="rfxModalPayAdvanceDisplay">
                <?= ($defaultMethod === 'online') ? '₹899 (Prepaid Full)' : ($isAdvanceRequired ? '₹' . round($advanceAmount) . ' (Online Advance)' : '₹0 (No Advance Required)') ?>
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center small py-1 border-top" style="border-color: #ffdacf !important;">
              <span class="text-muted font-weight-bold">Due to Staff After Service:</span>
              <span class="font-weight-bold text-danger" id="rfxModalPayDueDisplay">
                <?= ($defaultMethod === 'online') ? '₹0 (Fully Paid)' : ($isAdvanceRequired ? '₹850 (To Staff)' : '₹899 (Pay After Service)') ?>
              </span>
            </div>
          </div>

          <!-- Error Alert (Hidden by default) -->
          <div id="rfxModalErrorAlert" class="alert alert-danger py-2 px-3 small mb-3" style="display: none; border-radius: 8px;"></div>

          <!-- Payment Options -->
          <div class="rfx-payment-options d-flex flex-column" style="gap: 10px;">

            <?php if ($isRazorpayActive): ?>
            <!-- Option 1: Prepaid (Active when online payment gateway is enabled) -->
            <label class="rfx-pay-card <?= $defaultMethod === 'online' ? 'active' : '' ?> d-flex align-items-start p-3 mb-0 rounded" id="rfxPayCardOnline" style="cursor: pointer; border: <?= $defaultMethod === 'online' ? '2px solid #f25b29' : '1.5px solid #e2e8f0' ?>; background: <?= $defaultMethod === 'online' ? '#fffbf9' : '#ffffff' ?>; transition: all 0.2s ease;">
              <input type="radio" name="modal_payment_method" value="online" <?= $defaultMethod === 'online' ? 'checked' : '' ?> style="margin-top: 3px; accent-color: #f25b29;">
              <div class="ml-3 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                  <span class="font-weight-bold text-dark" style="font-size: 13.5px;">Prepaid</span>
                  <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                    <i class="fa fa-bolt mr-1"></i>High Priority
                  </span>
                </div>
                <p class="small text-muted mb-1 mt-1" style="font-size: 11.5px; line-height: 1.35;">
                  Pay 100% full amount online via UPI / Cards. High priority staff dispatch & zero balance during visit.
                </p>
                <div class="d-flex align-items-center mt-1" style="gap: 5px;">
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">UPI QR</span>
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">GPay</span>
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">PhonePe</span>
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">Cards</span>
                </div>
              </div>
            </label>
            <?php endif; ?>

            <?php if ($isCodActive || !$isRazorpayActive): ?>
            <!-- Option 2: Pay After Service / Cash on Delivery -->
            <label class="rfx-pay-card <?= $defaultMethod === 'cod' ? 'active' : '' ?> d-flex align-items-start p-3 mb-0 rounded" id="rfxPayCardCod" style="cursor: pointer; border: <?= $defaultMethod === 'cod' ? '2px solid #f25b29' : '1.5px solid #e2e8f0' ?>; background: <?= $defaultMethod === 'cod' ? '#fffbf9' : '#ffffff' ?>; transition: all 0.2s ease;">
              <input type="radio" name="modal_payment_method" value="cod" <?= $defaultMethod === 'cod' ? 'checked' : '' ?> style="margin-top: 3px; accent-color: #f25b29;">
              <div class="ml-3 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 4px;">
                  <span class="font-weight-bold text-dark" style="font-size: 13.5px;">Pay After Service</span>
                  <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                    <i class="fa fa-clock-o mr-1"></i>Medium Priority
                  </span>
                </div>
                <?php if ($isAdvanceRequired): ?>
                  <p class="small text-muted mb-1 mt-1" style="font-size: 11.5px; line-height: 1.35;">
                    Pay <strong>₹<?= round($advanceAmount) ?> pre-booking advance online</strong> to confirm slot. Remaining <strong id="rfxCodDueNote" class="text-danger">₹850</strong> (- ₹<?= round($advanceAmount) ?> deducted) is payable to staff after service.
                  </p>
                  <div class="d-flex align-items-center" style="gap: 5px;">
                    <span class="badge badge-light border text-success font-weight-bold" style="font-size: 9.5px;">₹<?= round($advanceAmount) ?> Advance Online</span>
                    <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">Rest Cash/UPI Later</span>
                  </div>
                <?php else: ?>
                  <p class="small text-muted mb-1 mt-1" style="font-size: 11.5px; line-height: 1.35;">
                    Pay <strong>100% full amount to staff via Cash / UPI</strong> after service is completed. <strong>Zero advance payment required!</strong>
                  </p>
                  <div class="d-flex align-items-center" style="gap: 5px;">
                    <span class="badge badge-light border text-success font-weight-bold" style="font-size: 9.5px;"><i class="fa fa-check text-success mr-1"></i>100% Cash/UPI on Visit</span>
                    <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 9.5px;">Zero Advance Online</span>
                  </div>
                <?php endif; ?>
              </div>
            </label>
            <?php endif; ?>

          </div>

          <!-- Guarantee Badge -->
          <div class="mt-3 p-2 rounded text-center small text-muted" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 11px;">
            <i class="fa fa-lock text-success mr-1"></i> 256-Bit SSL Secured Checkout · 24h Re-work Warranty
          </div>
        </div>

        <!-- Modal Footer CTA -->
        <div class="modal-footer p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
          <button type="button" id="rfxBtnConfirmPayment" class="btn text-white font-weight-bold w-100 py-2" style="background: #f25b29; border-radius: 8px; font-size: 14px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25); border: none;">
            <span id="rfxBtnConfirmPaymentText">
              <?php if ($defaultMethod === 'online'): ?>
                Pay Prepaid ₹899 (High Priority) &rarr;
              <?php elseif ($isAdvanceRequired): ?>
                Pay ₹<?= round($advanceAmount) ?> Advance &amp; Book &rarr;
              <?php else: ?>
                Confirm Booking (Pay After Service) &rarr;
              <?php endif; ?>
            </span>
          </button>
        </div>
      </div>

      <!-- ================= STEP 2: BOOKING DONE POPUP CARD ================= -->
      <div id="modalStepSuccess" style="display: none; background: #ffffff; padding: 22px 18px;">
        
        <!-- Brand Logo -->
        <div class="text-center mb-2">
          <img src="<?= \App\Core\View::asset('img/refixel-logo-horizontal.png') ?>" alt="REFIXEL" style="height: 30px; width: auto; object-fit: contain;">
        </div>

        <!-- Booking Done Badge + ID -->
        <div class="text-center mb-3">
          <div class="d-inline-flex align-items-center font-weight-bold" style="color: #16a34a; font-size: 15.5px; gap: 6px;">
            <span style="width: 22px; height: 22px; border-radius: 50%; background: #dcfce7; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">
              <i class="fa fa-check"></i>
            </span>
            <span>Booking Done</span>
          </div>
          <div>
            <span class="d-inline-flex align-items-center mt-1 px-2 py-1 rounded-pill font-weight-bold" style="background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; font-size: 11px; font-family: monospace; gap: 5px;">
              ID: <span id="rfxSuccessBookingNo">BK-PENDING</span>
              <button type="button" class="btn btn-xs p-0 text-dark border-0 bg-transparent" onclick="rfxModalCopyText(document.getElementById('rfxSuccessBookingNo').innerText, this)" title="Copy ID">
                <i class="fa fa-copy"></i>
              </button>
            </span>
          </div>
        </div>

        <!-- Credentials Box (Mobile / ID + Password) -->
        <div class="p-2 mb-3 rounded" style="background: #0a1c33; color: #ffffff;">
          <div class="d-flex align-items-center justify-content-between py-1" style="font-size: 12px; border-bottom: 1px solid rgba(255,255,255,0.08);">
            <span style="color: #94a3b8; font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Mobile Number</span>
            <span class="font-weight-bold d-flex align-items-center text-white" style="gap: 5px;">
              <span id="rfxSuccessPhone">7017853715</span>
              <button type="button" class="btn btn-xs py-0 px-1 border-0 rounded text-white" style="background: rgba(255,255,255,0.18); font-size: 9.5px;" onclick="rfxModalCopyText(document.getElementById('rfxSuccessPhone').innerText, this)">
                <i class="fa fa-copy"></i>
              </button>
            </span>
          </div>
          <div id="rfxSuccessEmailRow" class="d-flex align-items-center justify-content-between py-1" style="display: none; font-size: 12px; border-bottom: 1px solid rgba(255,255,255,0.08);">
            <span style="color: #94a3b8; font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Email</span>
            <span class="font-weight-bold d-flex align-items-center text-white" style="gap: 5px; font-size: 11px;">
              <span id="rfxSuccessEmail" style="word-break: break-all;">-</span>
              <button type="button" class="btn btn-xs py-0 px-1 border-0 rounded text-white" style="background: rgba(255,255,255,0.18); font-size: 9.5px;" onclick="rfxModalCopyText(document.getElementById('rfxSuccessEmail').innerText, this)">
                <i class="fa fa-copy"></i>
              </button>
            </span>
          </div>
          <div class="text-center pt-1" style="font-size: 11px; color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.08); margin-top: 4px;">
            <i class="fa fa-shield text-warning"></i> A secure set-password link has been sent to your email/SMS.
          </div>
        </div>

        <!-- Booking Summary (Short & Small Display) -->
        <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 11.5px;">
          <div class="font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.4px;">Booking & Payment Summary</div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Service:</span>
            <strong style="color: #f25b29;" id="rfxSuccessService">Doorstep Service</strong>
          </div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Customer:</span>
            <strong class="text-dark" id="rfxSuccessName">Customer</strong>
          </div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Booking Priority:</span>
            <strong id="rfxSuccessPriority" style="color: #15803d;">Medium Priority</strong>
          </div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;" id="rfxSuccessAdvanceRow">
            <span class="text-muted">Pre-Booking Advance:</span>
            <strong class="text-success" id="rfxSuccessAdvance">₹49 (Paid Online)</strong>
          </div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Due to Staff:</span>
            <strong class="text-danger" id="rfxSuccessDue">₹850 (After Service)</strong>
          </div>
          <div class="d-flex justify-content-between pt-1">
            <span class="text-muted">Payment Mode:</span>
            <strong class="text-dark" id="rfxSuccessPayment">Pay After Service</strong>
          </div>
        </div>

        <!-- Action & Direct Redirect to Dashboard -->
        <div>
          <a href="<?= \App\Core\View::url('/account/bookings') ?>" id="rfxSuccessDashboardBtn" class="btn text-white font-weight-bold w-100 py-2" style="background: #f25b29; border-radius: 8px; font-size: 13.5px; box-shadow: 0 3px 10px rgba(242, 91, 41, 0.25); text-decoration: none !important;">
            View Booking Details &rarr;
          </a>
          <div class="text-center mt-2 small text-muted" style="font-size: 11px;">
            Redirecting in <b id="rfxRedirectCount" style="color: #f25b29;">5</b>s...
          </div>
          <div class="text-center mt-1">
            <a href="<?= \App\Core\View::url('/') ?>" class="small text-muted text-decoration-none" style="font-size: 11px;">
              Return to Home
            </a>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
window.RFX_PAYMENT_CONFIG = {
  isRazorpayActive: <?= $isRazorpayActive ? 'true' : 'false' ?>,
  isCodActive: <?= $isCodActive ? 'true' : 'false' ?>,
  isAdvanceRequired: <?= $isAdvanceRequired ? 'true' : 'false' ?>,
  advanceAmount: <?= json_encode((float)$advanceAmount) ?>,
  defaultMethod: <?= json_encode($defaultMethod) ?>
};

function rfxModalCopyText(text, btn) {
  if (!text) return;
  var orig = btn.innerHTML;
  var done = function() {
    btn.innerHTML = '<i class="fa fa-check text-success"></i>';
    setTimeout(function() { btn.innerHTML = orig; }, 1400);
  };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(done).catch(function() { fallbackModalCopy(text, done); });
  } else {
    fallbackModalCopy(text, done);
  }
}
function fallbackModalCopy(text, cb) {
  var ta = document.createElement('textarea');
  ta.value = text;
  document.body.appendChild(ta);
  ta.select();
  try { document.execCommand('copy'); } catch(e) {}
  document.body.removeChild(ta);
  if (cb) cb();
}

(function() {
  var activeBookingForm = null;
  var redirectTimer = null;

  function getModal() {
    return document.getElementById('bookingPaymentModal');
  }

  function showPaymentModal() {
    var modal = getModal();
    if (!modal) return;
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
      window.jQuery('#bookingPaymentModal').modal('show');
    } else if (window.$ && typeof window.$.fn.modal === 'function') {
      window.$('#bookingPaymentModal').modal('show');
    } else {
      modal.style.display = 'block';
      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
      if (!document.querySelector('.rfx-backdrop')) {
        var bd = document.createElement('div');
        bd.className = 'modal-backdrop fade show rfx-backdrop';
        bd.style.zIndex = '1070';
        document.body.appendChild(bd);
      }
    }
  }

  function parseSlotMinutes(slot) {
    if (!slot || typeof slot !== 'string') return null;
    var m = slot.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
    if (!m) return null;
    var hours = parseInt(m[1], 10);
    var minutes = parseInt(m[2], 10);
    var ampm = m[3] ? m[3].toUpperCase() : null;
    if (!ampm) {
      var endM = slot.match(/(AM|PM)$/i);
      if (endM && endM[1].toUpperCase() === 'PM' && hours < 8) {
        hours += 12;
      }
    } else {
      if (ampm === 'PM' && hours < 12) hours += 12;
      if (ampm === 'AM' && hours === 12) hours = 0;
    }
    return hours * 60 + minutes;
  }

  function hidePaymentModal() {
    var modal = getModal();
    if (!modal) return;
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
      window.jQuery('#bookingPaymentModal').modal('hide');
    } else if (window.$ && typeof window.$.fn.modal === 'function') {
      window.$('#bookingPaymentModal').modal('hide');
    } else {
      modal.style.display = 'none';
      modal.classList.remove('show');
      modal.setAttribute('aria-hidden', 'true');
      var bd = document.querySelector('.rfx-backdrop');
      if (bd && bd.parentNode) bd.parentNode.removeChild(bd);
    }
  }

  function updatePayCards() {
    var cfg = window.RFX_PAYMENT_CONFIG || { isRazorpayActive: true, isCodActive: true, isAdvanceRequired: false, advanceAmount: 49 };
    var cardCod = document.getElementById('rfxPayCardCod');
    var cardOnline = document.getElementById('rfxPayCardOnline');
    var radioCod = cardCod ? cardCod.querySelector('input[type="radio"]') : null;
    var radioOnline = cardOnline ? cardOnline.querySelector('input[type="radio"]') : null;
    var btnText = document.getElementById('rfxBtnConfirmPaymentText');
    var payTotalDisplay = document.getElementById('rfxModalPayTotalDisplay');
    var advDisplay = document.getElementById('rfxModalPayAdvanceDisplay');
    var dueDisplay = document.getElementById('rfxModalPayDueDisplay');
    var dueNote = document.getElementById('rfxCodDueNote');

    var totText = payTotalDisplay ? payTotalDisplay.innerText : '₹899';
    var totalVal = parseFloat(totText.replace(/[^\d.]/g, '')) || 899;

    var isAdvanceRequired = cfg.isAdvanceRequired && cfg.isRazorpayActive;
    var advanceAmount = isAdvanceRequired ? cfg.advanceAmount : 0;
    var dueVal = isAdvanceRequired ? Math.max(0, totalVal - advanceAmount) : totalVal;

    if (dueNote) {
      dueNote.innerText = '₹' + Math.round(dueVal);
    }

    if (radioCod && radioCod.checked) {
      if (cardCod) { cardCod.style.borderColor = '#f25b29'; cardCod.style.background = '#fffbf9'; }
      if (cardOnline) { cardOnline.style.borderColor = '#e2e8f0'; cardOnline.style.background = '#ffffff'; }
      
      if (advDisplay) {
        advDisplay.innerText = isAdvanceRequired ? ('₹' + Math.round(advanceAmount) + ' (Online Advance)') : '₹0 (No Advance Required)';
      }
      if (dueDisplay) {
        dueDisplay.innerText = '₹' + Math.round(dueVal) + (isAdvanceRequired ? ' (To Staff After Service)' : ' (Pay After Service)');
      }
      if (btnText) {
        btnText.innerHTML = isAdvanceRequired 
          ? ('Pay ₹' + Math.round(advanceAmount) + ' Advance & Book &rarr;') 
          : 'Confirm Booking (Pay After Service) &rarr;';
      }
    } else if (radioOnline && radioOnline.checked) {
      if (cardOnline) { cardOnline.style.borderColor = '#f25b29'; cardOnline.style.background = '#fffbf9'; }
      if (cardCod) { cardCod.style.borderColor = '#e2e8f0'; cardCod.style.background = '#ffffff'; }
      if (advDisplay) advDisplay.innerText = '₹' + Math.round(totalVal) + ' (Prepaid Full)';
      if (dueDisplay) dueDisplay.innerText = '₹0 (Fully Paid)';
      if (btnText) btnText.innerHTML = 'Pay Prepaid ₹' + Math.round(totalVal) + ' (High Priority) &rarr;';
    }
  }

  function handleBookingFormTrigger(e, triggerBtn) {
    var form = triggerBtn.closest('form') || document.getElementById('bookingForm') || document.getElementById('cartCheckoutForm');
    if (!form) return;

    // 1. Safeguard: Fallback for house_no
    var houseNo = form.querySelector('[name="house_no"]');
    if (houseNo && !houseNo.value.trim()) {
      houseNo.value = 'House No. 1';
    }

    // 2. Validate Service Selection
    var srvInp = form.querySelector('[name="service_id"]');
    if (srvInp && (!srvInp.value || srvInp.value === '0')) {
      var srvSelect = form.querySelector('select[name="service_id"]');
      if (srvSelect) {
        e.preventDefault();
        e.stopPropagation();
        srvSelect.focus();
        if (typeof srvSelect.reportValidity === 'function') srvSelect.reportValidity();
        return false;
      }
    }

    // 3. Safeguard & Validate Preferred Schedule
    var prefDate = form.querySelector('[name="preferred_date"]');
    if (prefDate && !prefDate.value.trim()) {
      var today = new Date();
      var y = today.getFullYear();
      var m = String(today.getMonth() + 1).padStart(2, '0');
      var d = String(today.getDate()).padStart(2, '0');
      prefDate.value = y + '-' + m + '-' + d;
    }
    var prefTime = form.querySelector('[name="preferred_time"]');
    if (!prefTime || !prefTime.value.trim()) {
      e.preventDefault();
      e.stopPropagation();
      alert('Please select an available time slot for your appointment or pick another date.');
      var timeTrigger = document.getElementById('timeInputTrigger') || document.getElementById('cartTimeInputTrigger');
      if (timeTrigger) timeTrigger.click();
      return false;
    }

    // Safeguard: Validate that selected slot is at least 30 min in advance for today
    if (prefDate && prefTime && prefTime.value.trim()) {
      var selDate = prefDate.value.trim();
      var now = new Date();
      var todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
      if (selDate === todayStr) {
        var slotMins = parseSlotMinutes(prefTime.value.trim());
        var cutoffMins = now.getHours() * 60 + now.getMinutes() + 30;
        if (slotMins !== null && slotMins < cutoffMins) {
          e.preventDefault();
          e.stopPropagation();
          alert('The selected time slot (' + prefTime.value + ') has already passed or is within 30 minutes. Please select an upcoming slot starting at least 30 minutes from now or pick another date.');
          var timeTrigger = document.getElementById('timeInputTrigger') || document.getElementById('cartTimeInputTrigger');
          if (timeTrigger) timeTrigger.click();
          return false;
        }
      }
    }

    // 4. Prevent hidden collapsed inputs from failing HTML5 validation
    var hiddenBoxes = form.querySelectorAll('[style*="display: none"], [style*="display:none"], [style*="display: none;"]');
    hiddenBoxes.forEach(function(box) {
      box.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(inp) {
        inp.removeAttribute('required');
      });
    });

    // 5. Validate visible critical inputs explicitly
    var nameInp = form.querySelector('[name="name"]');
    if (nameInp && !nameInp.value.trim()) {
      e.preventDefault();
      e.stopPropagation();
      nameInp.focus();
      if (typeof nameInp.reportValidity === 'function') nameInp.reportValidity();
      return false;
    }

    var phoneInp = form.querySelector('[name="phone"]');
    if (phoneInp) {
      var cleanPhone = phoneInp.value.replace(/\D/g, '');
      if (cleanPhone.length < 10) {
        e.preventDefault();
        e.stopPropagation();
        phoneInp.focus();
        if (typeof phoneInp.reportValidity === 'function') phoneInp.reportValidity();
        return false;
      }
    }

    // 6. Ensure address composite exists
    var streetInp = form.querySelector('[name="street"]');
    var addrInp = form.querySelector('[name="address"]');
    var cityInp = form.querySelector('[name="city"]');
    var pinInp = form.querySelector('[name="pincode"]');
    var savedAddrBox = form.querySelector('#savedAddressSummaryBox, #cartSavedAddressSummaryBox');
    var hasSaved = savedAddrBox && (savedAddrBox.style.display !== 'none');

    if (!hasSaved && streetInp && !streetInp.value.trim()) {
      var detailedBox = form.querySelector('#addressDetailedFields, #cartAddressDetailedFields');
      if (detailedBox) detailedBox.style.display = 'block';
      e.preventDefault();
      e.stopPropagation();
      streetInp.focus();
      if (typeof streetInp.reportValidity === 'function') streetInp.reportValidity();
      return false;
    }

    // Auto-compose address if missing or short
    if (!addrInp || !addrInp.value || addrInp.value.trim().length < 5) {
      var parts = [
        houseNo ? houseNo.value.trim() : 'House No. 1',
        streetInp ? streetInp.value.trim() : '',
        cityInp ? cityInp.value.trim() : 'Kashipur',
        pinInp ? pinInp.value.trim() : ''
      ].filter(Boolean);
      var comp = parts.join(', ');
      if (addrInp) {
        addrInp.value = comp;
      } else {
        var hiddenAddr = document.createElement('input');
        hiddenAddr.type = 'hidden';
        hiddenAddr.name = 'address';
        hiddenAddr.value = comp;
        form.appendChild(hiddenAddr);
      }
    }

    // STOP regular submit & OPEN PAYMENT MODAL
    e.preventDefault();
    e.stopPropagation();
    activeBookingForm = form;

    // Detect price summary on page
    var totalText = '₹899';
    var sumElem = document.getElementById('summaryTotal') || 
                  document.getElementById('cartSummaryTotal') || 
                  document.querySelector('.sticky-top h3') ||
                  document.querySelector('h3.font-weight-bold[style*="#f25b29"]') ||
                  document.querySelector('#cartSummaryTotal');
    if (sumElem) {
      totalText = sumElem.innerText.trim();
    }
    var payTotalDisplay = document.getElementById('rfxModalPayTotalDisplay');
    if (payTotalDisplay) {
      payTotalDisplay.innerText = totalText;
    }

    // Reset modal steps
    var stepPay = document.getElementById('modalStepPayment');
    var stepSucc = document.getElementById('modalStepSuccess');
    if (stepPay) stepPay.style.display = 'block';
    if (stepSucc) stepSucc.style.display = 'none';
    var errAlert = document.getElementById('rfxModalErrorAlert');
    if (errAlert) { errAlert.style.display = 'none'; errAlert.innerText = ''; }

    var cardOnline = document.getElementById('rfxPayCardOnline');
    var radioOnline = cardOnline ? cardOnline.querySelector('input[type="radio"]') : null;
    var cardCod = document.getElementById('rfxPayCardCod');
    var radioCod = cardCod ? cardCod.querySelector('input[type="radio"]') : null;

    var cfg = window.RFX_PAYMENT_CONFIG || { isRazorpayActive: true };
    if (radioOnline && cfg.isRazorpayActive) {
      radioOnline.checked = true;
    } else if (radioCod) {
      radioCod.checked = true;
    }
    updatePayCards();

    // Show modal popup
    showPaymentModal();
    return false;
  }

  function initModalHandlers() {
    var modal = getModal();
    if (!modal) return;

    // Wire Card Clicks
    var cardCod = document.getElementById('rfxPayCardCod');
    var cardOnline = document.getElementById('rfxPayCardOnline');
    if (cardCod) {
      cardCod.onclick = function() {
        var r = cardCod.querySelector('input[type="radio"]');
        if (r) r.checked = true;
        updatePayCards();
      };
    }
    if (cardOnline) {
      cardOnline.onclick = function() {
        var r = cardOnline.querySelector('input[type="radio"]');
        if (r) r.checked = true;
        updatePayCards();
      };
    }

    // Wire Close buttons
    modal.querySelectorAll('.close, .rfx-modal-close, [data-dismiss="modal"]').forEach(function(cBtn) {
      cBtn.onclick = function(ev) {
        ev.preventDefault();
        hidePaymentModal();
      };
    });

    // Wire Confirm Payment Button
    var btnConfirm = document.getElementById('rfxBtnConfirmPayment');
    if (btnConfirm) {
      btnConfirm.onclick = function(e) {
        e.preventDefault();
        if (!activeBookingForm) {
          activeBookingForm = document.getElementById('bookingForm') || document.getElementById('cartCheckoutForm');
        }
        if (!activeBookingForm) return;

        var radioOnline = document.querySelector('input[name="modal_payment_method"][value="online"]');
        var selectedMethod = (radioOnline && radioOnline.checked) ? 'online' : 'cod';

        // Check preferred_time is present before sending
        var prefTimeInForm = activeBookingForm.querySelector('[name="preferred_time"]');
        if (!prefTimeInForm || !prefTimeInForm.value.trim()) {
          var errorAlert = document.getElementById('rfxModalErrorAlert');
          if (errorAlert) {
            errorAlert.innerText = 'Please choose an available time slot for your service appointment.';
            errorAlert.style.display = 'block';
          } else {
            alert('Please choose an available time slot for your appointment.');
          }
          return;
        }

        // Check preferred_time is valid for today (30 min buffer)
        var prefDateInForm = activeBookingForm.querySelector('[name="preferred_date"]');
        if (prefDateInForm && prefTimeInForm && prefTimeInForm.value.trim()) {
          var selDate = prefDateInForm.value.trim();
          var now = new Date();
          var todayStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
          if (selDate === todayStr) {
            var slotMins = parseSlotMinutes(prefTimeInForm.value.trim());
            var cutoffMins = now.getHours() * 60 + now.getMinutes() + 30;
            if (slotMins !== null && slotMins < cutoffMins) {
              var errorAlert = document.getElementById('rfxModalErrorAlert');
              var slotWarn = 'The selected time slot (' + prefTimeInForm.value + ') has passed or is within 30 minutes. Please choose a later slot or select another date.';
              if (errorAlert) {
                errorAlert.innerText = slotWarn;
                errorAlert.style.display = 'block';
              } else {
                alert(slotWarn);
              }
              return;
            }
          }
        }

        var originalBtnHtml = btnConfirm.innerHTML;
        btnConfirm.disabled = true;
        btnConfirm.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing Booking...';
        var errorAlert = document.getElementById('rfxModalErrorAlert');
        if (errorAlert) { errorAlert.style.display = 'none'; errorAlert.innerText = ''; }

        var formData = new FormData(activeBookingForm);
        formData.set('is_ajax', '1');
        formData.set('payment_method', selectedMethod);

        // Ensure address exists in formData
        var addrInput = activeBookingForm.querySelector('[name="address"]');
        if (addrInput && addrInput.value) {
          formData.set('address', addrInput.value);
        }

        // Get CSRF Token value
        var csrfVal = '';
        var csrfEl = activeBookingForm.querySelector('[name="_csrf"]') || document.querySelector('input[name="_csrf"]');
        if (csrfEl && csrfEl.value) {
          csrfVal = csrfEl.value;
          formData.set('_csrf', csrfVal);
        }

        var actionUrl = activeBookingForm.getAttribute('action') || (window.REFIXEL_BASE_URL ? window.REFIXEL_BASE_URL + '/book' : '/book');

        fetch(actionUrl, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfVal
          },
          body: formData
        })
        .then(function(res) {
          return res.text().then(function(text) {
            var data = null;
            try {
              data = JSON.parse(text);
            } catch(e) {}
            return { ok: res.ok, status: res.status, data: data, rawText: text };
          });
        })
        .then(function(result) {
          btnConfirm.disabled = false;
          btnConfirm.innerHTML = originalBtnHtml;

          if (!result.ok || !result.data || !result.data.success) {
            var errMsg = '';
            if (result.data) {
              errMsg = result.data.message || result.data.error;
              if (!errMsg && result.data.errors) {
                var firstKey = Object.keys(result.data.errors)[0];
                errMsg = Array.isArray(result.data.errors[firstKey]) ? result.data.errors[firstKey][0] : result.data.errors[firstKey];
              }
            }
            if (!errMsg) {
              if (result.status === 419) {
                errMsg = 'Session expired. Please refresh the page and try again.';
              } else if (result.status === 422) {
                errMsg = 'Please verify all booking details (name, phone, address, and slot) and try again.';
              } else if (result.status >= 500) {
                errMsg = 'Unable to complete booking due to a server error. Please try again.';
              } else {
                errMsg = 'Unable to complete booking. Please verify all details and try again.';
              }
            }

            if (errorAlert) {
              errorAlert.innerText = errMsg;
              errorAlert.style.display = 'block';
              try { errorAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch(e) {}
            } else {
              alert(errMsg);
            }
            return;
          }

          function showSuccessCard(d, paymentText) {
            document.getElementById('modalStepPayment').style.display = 'none';
            var stepSuccess = document.getElementById('modalStepSuccess');
            stepSuccess.style.display = 'block';

            document.getElementById('rfxSuccessBookingNo').innerText = d.booking_no || 'BK-CONFIRMED';
            var rawDigits = String(d.phone || d.identifier || '').replace(/\D/g, '');
            if (rawDigits.length === 12 && rawDigits.startsWith('91')) rawDigits = rawDigits.slice(2);
            document.getElementById('rfxSuccessPhone').innerText = rawDigits || 'Customer';

            var emailRow = document.getElementById('rfxSuccessEmailRow');
            var emailElem = document.getElementById('rfxSuccessEmail');
            if (d.email && emailRow && emailElem) {
              emailElem.innerText = d.email;
              emailRow.style.display = 'flex';
            } else if (emailRow) {
              emailRow.style.display = 'none';
            }
            
            var dashBtn = document.getElementById('rfxSuccessDashboardBtn');
            if (dashBtn) {
              if (d.guest_token) {
                dashBtn.innerText = 'View Booking Details →';
              } else if (d.set_password_url) {
                dashBtn.innerText = 'Set Your Password & View Booking →';
              } else {
                dashBtn.innerText = 'Open My Bookings (Dashboard) →';
              }
            }

            document.getElementById('rfxSuccessService').innerText = d.service_name || 'Doorstep Service';
            document.getElementById('rfxSuccessName').innerText = d.name || 'Customer';

            var isCod = (d.payment_type === 'cod' || (d.due_amount && d.due_amount > 0));
            var prioElem = document.getElementById('rfxSuccessPriority');
            if (prioElem) {
              if (isCod) {
                prioElem.innerHTML = '<span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a;"><i class="fa fa-clock-o mr-1"></i>Medium Priority (COD)</span>';
              } else {
                prioElem.innerHTML = '<span class="badge badge-success"><i class="fa fa-bolt mr-1"></i>High Priority (Online)</span>';
              }
            }

            var advRow = document.getElementById('rfxSuccessAdvanceRow');
            var advElem = document.getElementById('rfxSuccessAdvance');
            var advVal = (d.advance_amount !== undefined) ? parseFloat(d.advance_amount) : (isCod ? (window.RFX_PAYMENT_CONFIG.isAdvanceRequired ? window.RFX_PAYMENT_CONFIG.advanceAmount : 0) : (d.total_amount || 899));
            if (advRow) {
              advRow.style.display = (advVal > 0) ? 'flex' : 'none';
            }
            if (advElem && advVal > 0) {
              advElem.innerText = '₹' + Math.round(advVal) + ' (Paid Online)';
            }

            var dueElem = document.getElementById('rfxSuccessDue');
            if (dueElem) {
              var dueVal = (d.due_amount !== undefined) ? parseFloat(d.due_amount) : (isCod ? Math.max(0, (d.total_amount || 899) - advVal) : 0);
              dueElem.innerText = '₹' + Math.round(dueVal) + (dueVal > 0 ? ' (Due to Staff on Visit)' : ' (Zero Due)');
            }

            var defaultPayText = isCod 
              ? (advVal > 0 ? ('Pay After Service (₹' + Math.round(advVal) + ' Advance Paid)') : 'Cash on Delivery (Pay After Service)') 
              : 'Prepaid (100% Online)';
            document.getElementById('rfxSuccessPayment').innerText = paymentText || defaultPayText;

            var targetUrl = d.redirect_url || (window.REFIXEL_BASE_URL ? window.REFIXEL_BASE_URL + '/account/bookings' : '/account/bookings');
            var dashBtn = document.getElementById('rfxSuccessDashboardBtn');
            if (dashBtn) {
              dashBtn.setAttribute('href', targetUrl);
            }

            // Start Countdown auto-redirect to dashboard
            var secondsLeft = 5;
            var countElem = document.getElementById('rfxRedirectCount');
            if (redirectTimer) clearInterval(redirectTimer);
            redirectTimer = setInterval(function() {
              secondsLeft--;
              if (countElem) countElem.innerText = secondsLeft;
              if (secondsLeft <= 0) {
                clearInterval(redirectTimer);
                window.location.href = targetUrl;
              }
            }, 1000);

            if (dashBtn) {
              dashBtn.onclick = function() {
                if (redirectTimer) clearInterval(redirectTimer);
              };
            }
          }

          if (result.data.requires_payment) {
            var d = result.data;
            if (typeof Razorpay === 'undefined') {
              if (errorAlert) {
                errorAlert.innerText = 'Razorpay payment SDK not loaded. Please refresh the page and try again.';
                errorAlert.style.display = 'block';
              }
              return;
            }

            var isCodMode = (d.payment_type === 'cod');
            var advAmtText = d.advance_amount ? Math.round(d.advance_amount) : 49;
            var rzpDesc = isCodMode 
              ? ('Pre-booking Advance (₹' + advAmtText + ') · ' + (d.service_name || 'Doorstep Service') + ' (' + d.booking_no + ')')
              : ('100% Full Payment · ' + (d.service_name || 'Doorstep Service') + ' (' + d.booking_no + ')');

            var rzpOptions = {
              key: d.razorpay_key_id,
              amount: d.amount,
              currency: d.currency || 'INR',
              name: 'REFIXEL Technologies',
              description: rzpDesc,
              image: '<?= \App\Core\View::asset("img/refixel-logo-horizontal.png") ?>',
              order_id: d.razorpay_order_id,
              prefill: {
                name: d.name,
                contact: d.phone,
                email: d.email || ''
              },
              notes: {
                booking_no: d.booking_no,
                payment_type: d.payment_type || 'cod',
                total_amount: d.total_amount,
                advance_amount: d.advance_amount,
                due_amount: d.due_amount,
                priority: d.priority
              },
              theme: {
                color: '#f25b29'
              },
              modal: {
                backdropclose: false,
                escape: false,
                handleback: true,
                ondismiss: function() {
                  btnConfirm.disabled = false;
                  btnConfirm.innerHTML = originalBtnHtml;
                  if (errorAlert) {
                    errorAlert.innerText = isCodMode
                      ? ('Pre-booking advance payment was closed. Please complete the ₹' + advAmtText + ' online payment to confirm your booking.')
                      : 'Online payment was closed. You can retry paying online or choose Pay After Service.';
                    errorAlert.style.display = 'block';
                  }

                  // Immediately report drop-off to admin recovery desk
                  try {
                    var dropPayload = new FormData();
                    dropPayload.append('booking_id', d.booking_id || 0);
                    dropPayload.append('step', 'razorpay_modal_dismissed');
                    dropPayload.append('reason', isCodMode ? ('Closed ₹' + advAmtText + ' advance payment modal') : 'Closed full online payment modal');
                    if (window.REFIXEL_CSRF_TOKEN) dropPayload.append('_csrf', window.REFIXEL_CSRF_TOKEN);
                    var dropUrl = window.REFIXEL_BASE_URL ? (window.REFIXEL_BASE_URL + '/api/booking/record-dropoff') : '/api/booking/record-dropoff';
                    if (navigator.sendBeacon) {
                      navigator.sendBeacon(dropUrl, dropPayload);
                    } else {
                      fetch(dropUrl, { method: 'POST', body: dropPayload, keepalive: true }).catch(function(){});
                    }
                  } catch(dropErr) {}
                }
              },
              handler: function(paymentResponse) {
                btnConfirm.disabled = true;
                btnConfirm.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Verifying UPI Payment...';
                if (errorAlert) { errorAlert.style.display = 'none'; errorAlert.innerText = ''; }

                var verifyData = new FormData();
                verifyData.append('booking_id', d.booking_id);
                verifyData.append('booking_no', d.booking_no);
                verifyData.append('service_name', d.service_name || '');
                verifyData.append('razorpay_order_id', paymentResponse.razorpay_order_id);
                verifyData.append('razorpay_payment_id', paymentResponse.razorpay_payment_id);
                verifyData.append('razorpay_signature', paymentResponse.razorpay_signature);
                if (d.guest_token) {
                  verifyData.append('guest_token', d.guest_token);
                }
                if (activeBookingForm) {
                  var csrfEl = activeBookingForm.querySelector('[name="_csrf"]');
                  if (csrfEl) verifyData.append('_csrf', csrfEl.value);
                }
                if (!verifyData.has('_csrf') && window.REFIXEL_CSRF_TOKEN) {
                  verifyData.append('_csrf', window.REFIXEL_CSRF_TOKEN);
                }

                var verifyUrl = window.REFIXEL_BASE_URL ? (window.REFIXEL_BASE_URL + '/book/verify-payment') : '/book/verify-payment';
                fetch(verifyUrl, {
                  method: 'POST',
                  headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                  },
                  body: verifyData
                })
                .then(function(vRes) {
                  return vRes.json().then(function(vData) {
                    return { ok: vRes.ok, data: vData };
                  }).catch(function() {
                    return { ok: vRes.ok, data: null };
                  });
                })
                .then(function(vResult) {
                  btnConfirm.disabled = false;
                  btnConfirm.innerHTML = originalBtnHtml;

                  if (!vResult.ok || !vResult.data || !vResult.data.success) {
                    var vErr = (vResult.data && vResult.data.message) ? vResult.data.message : 'Payment verification failed.';
                    if (errorAlert) {
                      errorAlert.innerText = vErr;
                      errorAlert.style.display = 'block';
                    } else {
                      alert(vErr);
                    }
                    return;
                  }

                  var finalData = Object.assign({}, d, vResult.data);
                  var succAdv = finalData.advance_amount ? parseFloat(finalData.advance_amount) : 0;
                  var succText = (finalData.payment_type === 'cod') 
                    ? (succAdv > 0 ? ('Pre-booking Advance Paid (₹' + Math.round(succAdv) + ' Online)') : 'Cash on Delivery (Pay After Service)') 
                    : '100% Full Payment Paid Online';
                  showSuccessCard(finalData, succText);
                })
                .catch(function(err) {
                  btnConfirm.disabled = false;
                  btnConfirm.innerHTML = originalBtnHtml;
                  if (errorAlert) {
                    errorAlert.innerText = 'Network error while verifying payment. Ref ID: ' + paymentResponse.razorpay_payment_id;
                    errorAlert.style.display = 'block';
                  }
                });
              }
            };

            var rzp = new Razorpay(rzpOptions);
            rzp.on('payment.failed', function(resp) {
              btnConfirm.disabled = false;
              btnConfirm.innerHTML = originalBtnHtml;
              var failDesc = (resp.error && resp.error.description) ? resp.error.description : 'Transaction declined by bank/UPI.';
              if (errorAlert) {
                errorAlert.innerText = 'Payment failed: ' + failDesc;
                errorAlert.style.display = 'block';
              }

              // Immediately report payment failure to admin recovery desk
              try {
                var failPayload = new FormData();
                failPayload.append('booking_id', d.booking_id || 0);
                failPayload.append('step', 'payment_failed');
                failPayload.append('reason', 'Payment Failed: ' + failDesc + ' (Code: ' + (resp.error ? resp.error.code : 'DECLINED') + ')');
                if (window.REFIXEL_CSRF_TOKEN) failPayload.append('_csrf', window.REFIXEL_CSRF_TOKEN);
                var dropUrl = window.REFIXEL_BASE_URL ? (window.REFIXEL_BASE_URL + '/api/booking/record-dropoff') : '/api/booking/record-dropoff';
                if (navigator.sendBeacon) {
                  navigator.sendBeacon(dropUrl, failPayload);
                } else {
                  fetch(dropUrl, { method: 'POST', body: failPayload, keepalive: true }).catch(function(){});
                }
              } catch(payFailErr) {}
            });
            rzp.open();
            return;
          }

          // COD or standard non-payment flow
          showSuccessCard(result.data, (selectedMethod === 'online') ? 'Prepaid (100% Online)' : 'Pay After Service');
        })
        .catch(function(err) {
          console.error('Booking submission error:', err);
          btnConfirm.disabled = false;
          btnConfirm.innerHTML = originalBtnHtml;
          if (errorAlert) {
            errorAlert.innerText = 'Network error during booking submission. Please check connection and retry.';
            errorAlert.style.display = 'block';
          } else {
            alert('Network error. Please try again.');
          }
        });
      };
    }
  }

  // Global Event Delegation: Catches clicks reliably regardless of load order
  document.addEventListener('click', function(e) {
    var triggerBtn = e.target.closest('#bookSubmitBtn, #cartSubmitBtn, .btn-confirm-booking');
    if (triggerBtn) {
      handleBookingFormTrigger(e, triggerBtn);
    }
  }, true); // Capture phase ensures we intercept before form submit cancels

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModalHandlers);
  } else {
    initModalHandlers();
  }
})();
</script>
