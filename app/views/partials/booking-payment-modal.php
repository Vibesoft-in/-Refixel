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
              <i class="fa fa-shield mr-1" style="color: #f25b29;"></i> Select Payment Method
            </h5>
            <small style="color: #94a3b8; font-size: 11px;">Zero Advance · Pay Online or After Service</small>
          </div>
          <button type="button" class="close text-white rfx-modal-close" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; outline: none; font-size: 24px; padding: 0; margin: 0; line-height: 1; border: none; background: transparent;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-3" style="background: #ffffff;">
          <!-- Amount Highlight Card -->
          <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center" style="background: #fff8f5; border: 1.5px dashed #ffdacf;">
            <span class="small font-weight-bold text-muted">Total Payable:</span>
            <span class="font-weight-bold" style="color: #f25b29; font-size: 18px;" id="rfxModalPayTotalDisplay">₹899</span>
          </div>

          <!-- Error Alert (Hidden by default) -->
          <div id="rfxModalErrorAlert" class="alert alert-danger py-2 px-3 small mb-3" style="display: none; border-radius: 8px;"></div>

          <!-- Payment Options -->
          <div class="rfx-payment-options d-flex flex-column" style="gap: 10px;">

            <!-- Option 1: Cash on Delivery (COD) -->
            <label class="rfx-pay-card active d-flex align-items-start p-3 mb-0 rounded" id="rfxPayCardCod" style="cursor: pointer; border: 2px solid #f25b29; background: #fffbf9; transition: all 0.2s ease;">
              <input type="radio" name="modal_payment_method" value="cod" checked style="margin-top: 3px; accent-color: #f25b29;">
              <div class="ml-3 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="font-weight-bold text-dark" style="font-size: 13.5px;">Cash on Delivery (COD)</span>
                  <span class="badge" style="background: #dcfce7; color: #16a34a; font-size: 10px; font-weight: 700; padding: 3px 6px;">Zero Advance</span>
                </div>
                <p class="small text-muted mb-0 mt-1" style="font-size: 11.5px; line-height: 1.35;">
                  Pay safely with Cash or UPI to the technician after doorstep service completion.
                </p>
              </div>
            </label>

            <!-- Option 2: Pay Online -->
            <label class="rfx-pay-card d-flex align-items-start p-3 mb-0 rounded" id="rfxPayCardOnline" style="cursor: pointer; border: 1.5px solid #e2e8f0; background: #ffffff; transition: all 0.2s ease;">
              <input type="radio" name="modal_payment_method" value="online" style="margin-top: 3px; accent-color: #f25b29;">
              <div class="ml-3 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="font-weight-bold text-dark" style="font-size: 13.5px;">Pay Online (Instant)</span>
                  <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-size: 10px; font-weight: 700; padding: 2px 6px;">Instant Confirmation</span>
                </div>
                <p class="small text-muted mb-1 mt-1" style="font-size: 11.5px; line-height: 1.35;">
                  UPI, Google Pay, PhonePe, Paytm, Debit / Credit Cards & Net Banking.
                </p>
                <div class="d-flex align-items-center mt-1" style="gap: 5px;">
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">UPI</span>
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">GPay</span>
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">PhonePe</span>
                  <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 9.5px;">Cards</span>
                </div>
              </div>
            </label>

          </div>

          <!-- Guarantee Badge -->
          <div class="mt-3 p-2 rounded text-center small text-muted" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 11px;">
            <i class="fa fa-lock text-success mr-1"></i> 256-Bit SSL Secured Checkout · 24h Re-work Warranty
          </div>
        </div>

        <!-- Modal Footer CTA -->
        <div class="modal-footer p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
          <button type="button" id="rfxBtnConfirmPayment" class="btn text-white font-weight-bold w-100 py-2" style="background: #f25b29; border-radius: 8px; font-size: 14px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25); border: none;">
            <span id="rfxBtnConfirmPaymentText">Confirm Booking (Pay After Service) &rarr;</span>
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
            <span style="color: #94a3b8; font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Mobile / ID</span>
            <span class="font-weight-bold d-flex align-items-center text-white" style="gap: 5px;">
              <span id="rfxSuccessPhone">+91-XXXXXXXXXX</span>
              <button type="button" class="btn btn-xs py-0 px-1 border-0 rounded text-white" style="background: rgba(255,255,255,0.18); font-size: 9.5px;" onclick="rfxModalCopyText(document.getElementById('rfxSuccessPhone').innerText, this)">
                <i class="fa fa-copy"></i>
              </button>
            </span>
          </div>
          <div class="d-flex align-items-center justify-content-between pt-1" style="font-size: 12px;">
            <span style="color: #94a3b8; font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Password</span>
            <span class="font-weight-bold d-flex align-items-center" style="gap: 5px;">
              <span id="rfxSuccessPassword" style="font-family: monospace; color: #fde047; letter-spacing: 0.5px;">Refixel@1234</span>
              <button type="button" id="rfxSuccessPasswordCopyBtn" class="btn btn-xs py-0 px-1 border-0 rounded text-white" style="background: rgba(255,255,255,0.18); font-size: 9.5px;" onclick="rfxModalCopyText(document.getElementById('rfxSuccessPassword').innerText, this)">
                <i class="fa fa-copy"></i> Copy
              </button>
            </span>
          </div>
        </div>

        <!-- Booking Summary (Short & Small Display) -->
        <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 11.5px;">
          <div class="font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.4px;">Booking Summary</div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Service:</span>
            <strong style="color: #f25b29;" id="rfxSuccessService">Doorstep Service</strong>
          </div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Customer:</span>
            <strong class="text-dark" id="rfxSuccessName">Customer</strong>
          </div>
          <div class="d-flex justify-content-between py-1" style="border-bottom: 1px dashed #e2e8f0;">
            <span class="text-muted">Location:</span>
            <strong class="text-dark text-truncate" style="max-width: 60%;" id="rfxSuccessLocation">Service Area</strong>
          </div>
          <div class="d-flex justify-content-between pt-1">
            <span class="text-muted">Payment:</span>
            <strong class="text-success" id="rfxSuccessPayment">Cash on Delivery</strong>
          </div>
        </div>

        <!-- Action & Direct Redirect to Dashboard -->
        <div>
          <a href="<?= \App\Core\View::url('/account/bookings') ?>" id="rfxSuccessDashboardBtn" class="btn text-white font-weight-bold w-100 py-2" style="background: #f25b29; border-radius: 8px; font-size: 13.5px; box-shadow: 0 3px 10px rgba(242, 91, 41, 0.25); text-decoration: none !important;">
            Open My Bookings (Dashboard) &rarr;
          </a>
          <div class="text-center mt-2 small text-muted" style="font-size: 11px;">
            Redirecting to your dashboard in <b id="rfxRedirectCount" style="color: #f25b29;">5</b>s...
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

<script>
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
    var cardCod = document.getElementById('rfxPayCardCod');
    var cardOnline = document.getElementById('rfxPayCardOnline');
    var radioCod = cardCod ? cardCod.querySelector('input[type="radio"]') : null;
    var radioOnline = cardOnline ? cardOnline.querySelector('input[type="radio"]') : null;
    var btnText = document.getElementById('rfxBtnConfirmPaymentText');
    var payTotalDisplay = document.getElementById('rfxModalPayTotalDisplay');

    if (radioCod && radioCod.checked) {
      if (cardCod) { cardCod.style.borderColor = '#f25b29'; cardCod.style.background = '#fffbf9'; }
      if (cardOnline) { cardOnline.style.borderColor = '#e2e8f0'; cardOnline.style.background = '#ffffff'; }
      if (btnText) btnText.innerHTML = 'Confirm Booking (Pay After Service) &rarr;';
    } else if (radioOnline && radioOnline.checked) {
      if (cardOnline) { cardOnline.style.borderColor = '#f25b29'; cardOnline.style.background = '#fffbf9'; }
      if (cardCod) { cardCod.style.borderColor = '#e2e8f0'; cardCod.style.background = '#ffffff'; }
      var amtText = payTotalDisplay ? payTotalDisplay.innerText : '';
      if (btnText) btnText.innerHTML = 'Proceed to Pay ' + amtText + ' & Confirm &rarr;';
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

    // 2. Safeguard: Ensure preferred date and time have fallback defaults
    var prefDate = form.querySelector('[name="preferred_date"]');
    if (prefDate && !prefDate.value.trim()) {
      var today = new Date();
      var y = today.getFullYear();
      var m = String(today.getMonth() + 1).padStart(2, '0');
      var d = String(today.getDate()).padStart(2, '0');
      prefDate.value = y + '-' + m + '-' + d;
    }
    var prefTime = form.querySelector('[name="preferred_time"]');
    if (prefTime && !prefTime.value.trim()) {
      prefTime.value = '02:00 - 04:00 PM';
    }

    // 3. Prevent hidden collapsed inputs from failing HTML5 validation
    var hiddenBoxes = form.querySelectorAll('[style*="display: none"], [style*="display:none"], [style*="display: none;"]');
    hiddenBoxes.forEach(function(box) {
      box.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(inp) {
        inp.removeAttribute('required');
      });
    });

    // 4. Validate visible critical inputs explicitly
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

    // 5. Ensure address composite exists
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

    var cardCod = document.getElementById('rfxPayCardCod');
    var radioCod = cardCod ? cardCod.querySelector('input[type="radio"]') : null;
    if (radioCod) radioCod.checked = true;
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

        var actionUrl = activeBookingForm.getAttribute('action') || (window.REFIXEL_BASE_URL ? window.REFIXEL_BASE_URL + '/book' : '/book');

        fetch(actionUrl, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: formData
        })
        .then(function(res) {
          return res.json().then(function(data) {
            return { ok: res.ok, status: res.status, data: data };
          }).catch(function() {
            return { ok: res.ok, status: res.status, data: null };
          });
        })
        .then(function(result) {
          btnConfirm.disabled = false;
          btnConfirm.innerHTML = originalBtnHtml;

          if (!result.ok || !result.data || !result.data.success) {
            var errMsg = (result.data && result.data.message) ? result.data.message : 'Unable to complete booking. Please verify all details and try again.';
            if (errorAlert) {
              errorAlert.innerText = errMsg;
              errorAlert.style.display = 'block';
            } else {
              alert(errMsg);
            }
            return;
          }

          // Transition to Step 2 within the SAME MODAL
          var d = result.data;
          document.getElementById('modalStepPayment').style.display = 'none';
          var stepSuccess = document.getElementById('modalStepSuccess');
          stepSuccess.style.display = 'block';

          // Populate confirmation fields matching 2nd screenshot
          document.getElementById('rfxSuccessBookingNo').innerText = d.booking_no || 'BK-CONFIRMED';
          document.getElementById('rfxSuccessPhone').innerText = '+91-' + (d.identifier || d.phone || '');
          
          var pwdElem = document.getElementById('rfxSuccessPassword');
          var pwdBtn = document.getElementById('rfxSuccessPasswordCopyBtn');
          if (d.generated_password) {
            pwdElem.innerText = d.generated_password;
            pwdElem.style.color = '#fde047';
            if (pwdBtn) pwdBtn.style.display = 'inline-block';
          } else {
            pwdElem.innerHTML = '<span style="color:#34d399; font-size:11px;"><i class="fa fa-check-circle mr-1"></i> Active / Logged In</span>';
            if (pwdBtn) pwdBtn.style.display = 'none';
          }

          document.getElementById('rfxSuccessService').innerText = d.service_name || 'Doorstep Service';
          document.getElementById('rfxSuccessName').innerText = d.name || 'Customer';
          document.getElementById('rfxSuccessLocation').innerText = d.city || d.address || 'Service Area';
          document.getElementById('rfxSuccessPayment').innerText = (selectedMethod === 'online') ? 'Online Paid (Instant)' : 'Cash on Delivery (Pay After Service)';

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
