<?php
use App\Core\View;

$totAmt = $totAmt ?? (float)(($job['total_amount'] ?? 0) > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
$advAmt = $advAmt ?? (float)($job['advance_amount'] ?? 0);
$dueAmt = $dueAmt ?? (float)(($job['due_amount'] ?? 0) > 0 ? $job['due_amount'] : max(0.0, $totAmt - $advAmt));
if (!empty($job['is_prepaid'])) {
    $dueAmt = 0.0;
}
$bNo = (string)($job['booking_no'] ?? '');
$txNote = str_starts_with($bNo, 'BK-') ? $bNo : ('BK-' . $bNo);
$upiString = 'upi://pay?pa=wearerefixel@okicici&pn=' . urlencode('REFIXEL') . '&am=' . round($dueAmt) . '&cu=INR&tn=' . urlencode($txNote);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($upiString);
?>

<!-- Staff Customer Payment Settlement Modal -->
<div class="modal fade" id="staffPaymentModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg rounded-lg overflow-hidden">
      
      <!-- Modal Header -->
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #f25b29 0%, #d44d20 100%);">
        <div>
          <h5 class="modal-title font-weight-bold mb-0 text-white">
            <i class="fa fa-credit-card mr-2"></i>Collect Customer Payment
          </h5>
          <div class="text-white-50 small mt-1">
            Booking #<?= View::e($job['booking_no']) ?> &bull; <?= View::e($job['service_name']) ?>
          </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4 bg-white">
        <!-- Error Alert -->
        <div id="staffPayErrorAlert" class="alert alert-danger py-2 px-3 small font-weight-bold mb-3" style="display: none;"></div>
        <!-- Success Alert -->
        <div id="staffPaySuccessAlert" class="alert alert-success py-2 px-3 small font-weight-bold mb-3" style="display: none;"></div>

        <?php if ($dueAmt <= 0 || !empty($job['is_prepaid'])): ?>
          <!-- Already Fully Paid -->
          <div class="text-center py-4">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #ecfdf5; color: #10b981; font-size: 28px;">
              <i class="fa fa-check"></i>
            </div>
            <h5 class="font-weight-bold text-dark mb-1">100% Prepaid (Fully Paid)</h5>
            <p class="text-muted small mb-3">Customer has already paid online in advance. Zero balance to collect from customer on site.</p>
            <a href="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" class="btn btn-success px-4 py-2 font-weight-bold">
              <i class="fa fa-arrow-right mr-1"></i>Proceed to Job Completion
            </a>
          </div>
        <?php else: ?>
          <!-- Bill Breakdown Card -->
          <div class="p-3 mb-3 rounded" style="background: #fff8f5; border: 1px solid #fbd5c8;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Total Service Amount:</span>
              <span class="font-weight-bold text-dark">₹<?= number_format($totAmt, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Advance Paid (Pre-Booking):</span>
              <span class="text-success font-weight-bold">- ₹<?= number_format($advAmt, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: #fbd5c8 !important;">
              <div>
                <span class="font-weight-bold text-dark d-block" style="font-size: 14px;">Remaining Balance Due:</span>
                <span class="text-muted small" style="font-size: 11px;">(Total - ₹<?= round($advAmt) ?> advance)</span>
              </div>
              <span class="font-weight-bold text-danger" style="font-size: 24px;">₹<?= number_format($dueAmt, 0) ?></span>
            </div>
          </div>

          <p class="small text-muted mb-2 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Choose Payment Collection Method:</p>

          <!-- 2 Action Toggle Buttons (Online vs Cash) -->
          <div class="row no-gutters mx-n1 mb-3">
            <div class="col-6 p-1">
              <button type="button" class="btn btn-block py-2 font-weight-bold rounded-lg border btn-pay-toggle active" id="btnPayModeOnline" onclick="switchStaffPayTab('online')" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0 !important;">
                <i class="fa fa-qrcode mr-1"></i>Online (QR Code)
              </button>
            </div>
            <div class="col-6 p-1">
              <button type="button" class="btn btn-block py-2 font-weight-bold rounded-lg border btn-pay-toggle" id="btnPayModeCash" onclick="switchStaffPayTab('cash')" style="background: #f8fafc; color: #475569; border-color: #e2e8f0 !important;">
                <i class="fa fa-money mr-1"></i>Cash (COD)
              </button>
            </div>
          </div>

          <!-- SECTION 1: Online QR Code View -->
          <div id="staffOnlineSection" class="p-3 bg-light rounded border text-center mb-2">
            <div class="small font-weight-bold text-muted text-uppercase mb-2">
              <i class="fa fa-mobile mr-1"></i>Scan with any UPI App to Pay
            </div>
            
            <div class="d-inline-block bg-white p-2 rounded shadow-sm mb-2 border">
              <img id="staffUpiQrImage" src="<?= $qrUrl ?>" alt="UPI QR Code" style="width: 190px; height: 190px; object-fit: contain; display: block;">
            </div>

            <div class="font-weight-bold text-success mb-1" style="font-size: 22px;">
              ₹<?= number_format($dueAmt, 0) ?>
            </div>
            <div class="small text-muted mb-3">
              Google Pay &bull; PhonePe &bull; Paytm &bull; BHIM &bull; Any Banking App
            </div>

            <!-- Optional Razorpay Checkout Window Button -->
            <button type="button" class="btn btn-sm btn-outline-primary btn-block mb-2 font-weight-bold" id="btnLaunchRzpModal" onclick="launchRazorpayCheckoutForJob()">
              <i class="fa fa-bolt mr-1"></i>Pay via Razorpay (Cards / NetBanking / Razorpay QR)
            </button>

            <!-- Confirmation Button -->
            <button type="button" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm" id="btnConfirmOnlinePaid" onclick="submitStaffPayment('upi')">
              <i class="fa fa-check-circle mr-1"></i>Customer Has Paid Online &rarr; Continue
            </button>
          </div>

          <!-- SECTION 2: Cash Collection Form -->
          <div id="staffCashSection" class="p-3 bg-light rounded border mb-2" style="display: none;">
            <div class="form-group mb-3">
              <label class="font-weight-bold small text-dark mb-1">
                Cash Amount Received from Customer <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text font-weight-bold bg-white text-dark">₹</span>
                </div>
                <input type="number" step="1" id="staffCashAmountInput" class="form-control form-control-lg font-weight-bold text-success" value="<?= round($dueAmt) ?>" required oninput="updateCashBtnLabel(this.value)">
              </div>
              <small class="form-text text-muted">
                Pre-filled with balance due (₹<?= round($dueAmt) ?>). Modify manually if partial or different cash received.
              </small>
            </div>

            <div class="form-group mb-3">
              <label class="font-weight-bold small text-dark mb-1">
                Technician Note / Remark (Optional)
              </label>
              <input type="text" id="staffCashNotesInput" class="form-control form-control-sm" placeholder="e.g. Received cash from customer on site">
            </div>

            <button type="button" class="btn btn-success btn-block py-2 font-weight-bold shadow-sm" id="btnConfirmCashPaid" onclick="submitStaffPayment('cash')">
              <i class="fa fa-money mr-1"></i>Confirm Cash Received (₹<span id="cashBtnLabelAmt"><?= round($dueAmt) ?></span>) &amp; Proceed
            </button>
          </div>

          <div class="text-center mt-3">
            <a href="<?= View::url('/staff/jobs/' . $job['id'] . '/complete') ?>" class="text-muted small font-weight-bold" style="text-decoration: underline;">
              Customer already settled / Proceed to completion &rarr;
            </a>
          </div>
        <?php endif; ?>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
        <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3" data-dismiss="modal">
          <i class="fa fa-arrow-left mr-1"></i>Back
        </button>
        <span class="small text-muted font-italic">Secure Payment by REFIXEL</span>
      </div>

    </div>
  </div>
</div>

<script>
window.switchStaffPayTab = function(mode) {
  var onlineBtn = document.getElementById('btnPayModeOnline');
  var cashBtn = document.getElementById('btnPayModeCash');
  var onlineSec = document.getElementById('staffOnlineSection');
  var cashSec = document.getElementById('staffCashSection');

  if (mode === 'online') {
    if (onlineBtn) {
      onlineBtn.style.background = '#ecfdf5';
      onlineBtn.style.color = '#047857';
      onlineBtn.style.borderColor = '#a7f3d0';
    }
    if (cashBtn) {
      cashBtn.style.background = '#f8fafc';
      cashBtn.style.color = '#475569';
      cashBtn.style.borderColor = '#e2e8f0';
    }
    if (onlineSec) onlineSec.style.display = 'block';
    if (cashSec) cashSec.style.display = 'none';
  } else {
    if (cashBtn) {
      cashBtn.style.background = '#ecfdf5';
      cashBtn.style.color = '#047857';
      cashBtn.style.borderColor = '#a7f3d0';
    }
    if (onlineBtn) {
      onlineBtn.style.background = '#f8fafc';
      onlineBtn.style.color = '#475569';
      onlineBtn.style.borderColor = '#e2e8f0';
    }
    if (cashSec) cashSec.style.display = 'block';
    if (onlineSec) onlineSec.style.display = 'none';
  }
};

window.updateCashBtnLabel = function(val) {
  var lbl = document.getElementById('cashBtnLabelAmt');
  if (lbl) {
    lbl.textContent = val ? Math.round(Number(val)) : '0';
  }
};

window.submitStaffPayment = function(method) {
  var jobId = '<?= $job['id'] ?? '' ?>';
  var errAlert = document.getElementById('staffPayErrorAlert');
  var succAlert = document.getElementById('staffPaySuccessAlert');
  if (errAlert) { errAlert.style.display = 'none'; errAlert.innerText = ''; }
  if (succAlert) { succAlert.style.display = 'none'; succAlert.innerText = ''; }

  var amount = <?= (float)$dueAmt ?>;
  var notes = '';

  if (method === 'cash') {
    var cashInp = document.getElementById('staffCashAmountInput');
    var notesInp = document.getElementById('staffCashNotesInput');
    if (cashInp) amount = parseFloat(cashInp.value) || 0;
    if (notesInp) notes = notesInp.value;

    if (amount <= 0) {
      if (errAlert) {
        errAlert.innerText = 'Please enter a valid cash amount.';
        errAlert.style.display = 'block';
      }
      return;
    }
  }

  var btn = (method === 'cash') ? document.getElementById('btnConfirmCashPaid') : document.getElementById('btnConfirmOnlinePaid');
  var origHtml = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Recording Payment...';
  }

  var fd = new FormData();
  fd.append('method', method);
  fd.append('amount', amount);
  fd.append('notes', notes);
  fd.append('_csrf', '<?= View::csrfToken() ?>');

  var url = '<?= View::url("/staff/jobs/" . ($job["id"] ?? "") . "/record-payment") ?>';

  fetch(url, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (res.success) {
      if (succAlert) {
        succAlert.innerText = res.message || 'Payment recorded successfully! Redirecting...';
        succAlert.style.display = 'block';
      }
      setTimeout(function() {
        window.location.href = res.redirect || ('<?= View::url("/staff/jobs/" . ($job["id"] ?? "") . "/complete") ?>');
      }, 700);
    } else {
      if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
      if (errAlert) {
        errAlert.innerText = res.message || 'Could not record payment. Please try again.';
        errAlert.style.display = 'block';
      }
    }
  })
  .catch(function(err) {
    if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
    if (errAlert) {
      errAlert.innerText = 'Server error recording payment. Please try again.';
      errAlert.style.display = 'block';
    }
  });
};

window.launchRazorpayCheckoutForJob = function() {
  var jobId = '<?= $job['id'] ?? '' ?>';
  var errAlert = document.getElementById('staffPayErrorAlert');
  if (errAlert) { errAlert.style.display = 'none'; }

  var rzpBtn = document.getElementById('btnLaunchRzpModal');
  if (rzpBtn) {
    rzpBtn.disabled = true;
    rzpBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Creating Razorpay Order...';
  }

  var fd = new FormData();
  fd.append('_csrf', '<?= View::csrfToken() ?>');

  fetch('<?= View::url("/staff/jobs/" . ($job["id"] ?? "") . "/create-payment-order") ?>', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (rzpBtn) {
      rzpBtn.disabled = false;
      rzpBtn.innerHTML = '<i class="fa fa-bolt mr-1"></i> Pay via Razorpay (Cards / NetBanking / Razorpay QR)';
    }

    if (!res.success) {
      if (errAlert) {
        errAlert.innerText = res.message || 'Failed to initialize Razorpay order.';
        errAlert.style.display = 'block';
      }
      return;
    }

    if (typeof Razorpay === 'undefined') {
      if (errAlert) {
        errAlert.innerText = 'Razorpay library not loaded. Customer can scan the QR code above with any UPI App.';
        errAlert.style.display = 'block';
      }
      return;
    }

    var rzpOptions = {
      key: res.razorpay_key_id,
      amount: res.amount,
      currency: res.currency || 'INR',
      name: 'REFIXEL Service',
      description: 'Balance Payment · #' + res.booking_no,
      order_id: res.razorpay_order_id,
      prefill: {
        name: res.customer_name || '',
        contact: res.customer_phone || ''
      },
      theme: { color: '#f25b29' },
      handler: function(paymentResponse) {
        // Record payment with Razorpay payment ID
        var postFd = new FormData();
        postFd.append('method', 'upi');
        postFd.append('amount', res.amount_rupees);
        postFd.append('transaction_ref', paymentResponse.razorpay_payment_id || paymentResponse.razorpay_order_id);
        postFd.append('notes', 'Paid via Razorpay Checkout Modal');
        postFd.append('_csrf', '<?= View::csrfToken() ?>');

        fetch('<?= View::url("/staff/jobs/" . ($job["id"] ?? "") . "/record-payment") ?>', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: postFd
        })
        .then(function(r) { return r.json(); })
        .then(function(pRes) {
          if (pRes.success) {
            window.location.href = pRes.redirect || ('<?= View::url("/staff/jobs/" . ($job["id"] ?? "") . "/complete") ?>');
          } else {
            alert('Payment received! Reference: ' + paymentResponse.razorpay_payment_id);
            window.location.href = '<?= View::url("/staff/jobs/" . ($job["id"] ?? "") . "/complete") ?>';
          }
        });
      }
    };

    var rzp = new Razorpay(rzpOptions);
    rzp.open();
  })
  .catch(function() {
    if (rzpBtn) {
      rzpBtn.disabled = false;
      rzpBtn.innerHTML = '<i class="fa fa-bolt mr-1"></i> Pay via Razorpay (Cards / NetBanking / Razorpay QR)';
    }
    if (errAlert) {
      errAlert.innerText = 'Could not start Razorpay window. Please use the UPI QR code above.';
      errAlert.style.display = 'block';
    }
  });
};
</script>
