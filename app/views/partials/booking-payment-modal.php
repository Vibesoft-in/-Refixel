<!-- Booking Payment Gateway & Success Modal -->
<div class="modal fade" id="bookingPaymentModal" tabindex="-1" role="dialog" aria-labelledby="bookingPaymentModalTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px; margin: 1.75rem auto;">
    <div class="modal-content" style="border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18); overflow: hidden;">

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
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; outline: none; font-size: 24px; padding: 0; margin: 0; line-height: 1;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-3" style="background: #ffffff;">
          <!-- Amount Highlight Card -->
          <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center" style="background: #fff8f5; border: 1.5px dashed #ffdacf;">
            <span class="small font-weight-bold text-muted">Total Payable:</span>
            <span class="font-weight-bold" style="color: #f25b29; font-size: 18px;" id="rfxModalPayTotalDisplay">₹0</span>
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
          <button type="button" id="rfxBtnConfirmPayment" class="btn text-white font-weight-bold w-100 py-2" style="background: #f25b29; border-radius: 8px; font-size: 14px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
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
          <a href="<?= \App\Core\View::url('/account/bookings') ?>" id="rfxSuccessDashboardBtn" class="btn text-white font-weight-bold w-100 py-2" style="background: #f25b29; border-radius: 8px; font-size: 13.5px; box-shadow: 0 3px 10px rgba(242, 91, 41, 0.25);">
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

document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('bookingPaymentModal');
  if (!modal) return;

  var cardCod = document.getElementById('rfxPayCardCod');
  var cardOnline = document.getElementById('rfxPayCardOnline');
  var radioCod = cardCod ? cardCod.querySelector('input[type="radio"]') : null;
  var radioOnline = cardOnline ? cardOnline.querySelector('input[type="radio"]') : null;
  var btnConfirm = document.getElementById('rfxBtnConfirmPayment');
  var btnText = document.getElementById('rfxBtnConfirmPaymentText');
  var errorAlert = document.getElementById('rfxModalErrorAlert');
  var payTotalDisplay = document.getElementById('rfxModalPayTotalDisplay');
  var activeBookingForm = null;
  var redirectTimer = null;

  // Toggle card active states
  function updatePayCards() {
    if (radioCod && radioCod.checked) {
      cardCod.style.borderColor = '#f25b29';
      cardCod.style.background = '#fffbf9';
      cardOnline.style.borderColor = '#e2e8f0';
      cardOnline.style.background = '#ffffff';
      if (btnText) btnText.innerHTML = 'Confirm Booking (Pay After Service) &rarr;';
    } else if (radioOnline && radioOnline.checked) {
      cardOnline.style.borderColor = '#f25b29';
      cardOnline.style.background = '#fffbf9';
      cardCod.style.borderColor = '#e2e8f0';
      cardCod.style.background = '#ffffff';
      var amtText = payTotalDisplay ? payTotalDisplay.innerText : '';
      if (btnText) btnText.innerHTML = 'Proceed to Pay ' + amtText + ' & Confirm &rarr;';
    }
  }

  if (cardCod) {
    cardCod.addEventListener('click', function() {
      if (radioCod) radioCod.checked = true;
      updatePayCards();
    });
  }
  if (cardOnline) {
    cardOnline.addEventListener('click', function() {
      if (radioOnline) radioOnline.checked = true;
      updatePayCards();
    });
  }

  // Intercept Booking Submit on #bookingForm and #cartCheckoutForm
  function bindBookingForm(formId, submitBtnId) {
    var form = document.getElementById(formId);
    var btn = document.getElementById(submitBtnId);
    if (!form || !btn) return;

    btn.addEventListener('click', function(e) {
      // Validate form
      if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        form.reportValidity();
        return;
      }

      e.preventDefault();
      activeBookingForm = form;

      // Extract total price from page
      var totalText = '₹899';
      var sumTotalElem = document.getElementById('summaryTotal') || document.querySelector('.sticky-top h3') || document.getElementById('cartSummaryTotal');
      if (sumTotalElem) {
        totalText = sumTotalElem.innerText.trim();
      }
      if (payTotalDisplay) {
        payTotalDisplay.innerText = totalText;
      }

      // Reset modal to Step 1
      document.getElementById('modalStepPayment').style.display = 'block';
      document.getElementById('modalStepSuccess').style.display = 'none';
      if (errorAlert) { errorAlert.style.display = 'none'; errorAlert.innerText = ''; }
      if (radioCod) radioCod.checked = true;
      updatePayCards();

      // Open modal
      if (window.$ && typeof $(modal).modal === 'function') {
        $(modal).modal('show');
      } else {
        modal.style.display = 'block';
        modal.classList.add('show');
      }
    });
  }

  bindBookingForm('bookingForm', 'bookSubmitBtn');
  bindBookingForm('cartCheckoutForm', 'cartSubmitBtn');

  // Modal Confirm & Submit Handler
  if (btnConfirm) {
    btnConfirm.addEventListener('click', function(e) {
      e.preventDefault();
      if (!activeBookingForm) return;

      var selectedMethod = (radioOnline && radioOnline.checked) ? 'online' : 'cod';
      var originalBtnHtml = btnConfirm.innerHTML;
      btnConfirm.disabled = true;
      btnConfirm.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing Booking...';
      if (errorAlert) { errorAlert.style.display = 'none'; errorAlert.innerText = ''; }

      var formData = new FormData(activeBookingForm);
      formData.set('is_ajax', '1');
      formData.set('payment_method', selectedMethod);

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

        // Booking Succeeded! Transition to Step 2 within the SAME MODAL
        var d = result.data;
        document.getElementById('modalStepPayment').style.display = 'none';
        var stepSuccess = document.getElementById('modalStepSuccess');
        stepSuccess.style.display = 'block';

        // Populate confirmation fields
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
          dashBtn.addEventListener('click', function() {
            if (redirectTimer) clearInterval(redirectTimer);
          });
        }
      })
      .catch(function(err) {
        console.error('Booking submission error:', err);
        btnConfirm.disabled = false;
        btnConfirm.innerHTML = originalBtnHtml;
        if (errorAlert) {
          errorAlert.innerText = 'Network error during booking submission. Please check your connection and retry.';
          errorAlert.style.display = 'block';
        } else {
          alert('Network error. Please try again.');
        }
      });
    });
  }
});
</script>
