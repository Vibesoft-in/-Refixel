<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Booking Done | REFIXEL</title>
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=2" rel="shortcut icon" type="image/png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 16px 12px;
      background: #f1f5f9;
      font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #0f172a;
    }
    .rfx-wrap {
      width: 100%;
      max-width: 380px;
      margin: 0 auto;
    }
    .rfx-logo {
      text-align: center;
      margin-bottom: 12px;
    }
    .rfx-logo img {
      height: 32px;
      width: auto;
      object-fit: contain;
    }
    .rfx-card {
      background: #ffffff;
      border-radius: 14px;
      padding: 18px 16px 16px 16px;
      box-shadow: 0 4px 20px rgba(15, 23, 42, 0.07);
      border: 1px solid #e2e8f0;
    }
    .rfx-top {
      text-align: center;
      margin-bottom: 14px;
    }
    .rfx-done-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      color: #16a34a;
      font-size: 16px;
      font-weight: 800;
    }
    .rfx-done-badge .tick-circle {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #dcfce7;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
    }
    .rfx-id-tag {
      margin-top: 4px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #fff7ed;
      border: 1px solid #fed7aa;
      color: #c2410c;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 11.5px;
      font-weight: 700;
      font-family: monospace;
    }
    .rfx-cred-box {
      background: #0a1c33;
      color: #ffffff;
      border-radius: 10px;
      padding: 10px 12px;
      margin-bottom: 12px;
    }
    .rfx-cred-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 4px 0;
      font-size: 12px;
    }
    .rfx-cred-row + .rfx-cred-row {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      margin-top: 4px;
      padding-top: 6px;
    }
    .rfx-cred-lbl {
      color: #94a3b8;
      font-size: 10.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .rfx-cred-val {
      color: #ffffff;
      font-weight: 700;
      font-size: 12.5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .rfx-copy-btn {
      background: rgba(255, 255, 255, 0.16);
      border: none;
      color: #ffffff;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 600;
      cursor: pointer;
      line-height: 1.2;
      transition: background 0.15s;
    }
    .rfx-copy-btn:hover {
      background: #f25b29;
    }
    .rfx-sum-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 12px;
      margin-bottom: 14px;
      font-size: 12px;
    }
    .rfx-sum-title {
      font-size: 10px;
      text-transform: uppercase;
      font-weight: 800;
      color: #94a3b8;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .rfx-sum-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 3px 0;
      color: #64748b;
      border-bottom: 1px dashed #e2e8f0;
    }
    .rfx-sum-row:last-child {
      border-bottom: none;
    }
    .rfx-sum-row span {
      font-size: 11.5px;
    }
    .rfx-sum-row strong {
      color: #0f172a;
      font-weight: 700;
      font-size: 12px;
      text-align: right;
      max-width: 65%;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .rfx-btn-dash {
      background: #f25b29;
      color: #ffffff !important;
      font-weight: 700;
      font-size: 13.5px;
      padding: 9px 14px;
      border-radius: 8px;
      display: block;
      width: 100%;
      text-align: center;
      text-decoration: none !important;
      box-shadow: 0 3px 10px rgba(242, 91, 41, 0.25);
      transition: opacity 0.15s;
    }
    .rfx-btn-dash:hover {
      opacity: 0.92;
    }
    .rfx-link-home {
      display: block;
      text-align: center;
      color: #64748b;
      font-size: 11.5px;
      font-weight: 600;
      text-decoration: none !important;
      margin-top: 8px;
    }
    .rfx-link-home:hover {
      color: #0f172a;
    }
  </style>
</head>
<body>

  <div class="rfx-wrap">
    
    <!-- Brand Logo -->
    <div class="rfx-logo">
      <a href="<?= \App\Core\View::url('/') ?>">
        <img src="<?= \App\Core\View::asset('img/refixel-logo-horizontal.png') ?>" alt="REFIXEL">
      </a>
    </div>

    <!-- Small Card -->
    <div class="rfx-card">
      
      <!-- Booking Done + ID -->
      <div class="rfx-top">
        <div class="rfx-done-badge">
          <span class="tick-circle"><i class="fa fa-check"></i></span>
          <span>Booking Done</span>
        </div>
        <div>
          <span class="rfx-id-tag">
            ID: <?= \App\Core\View::e($booking_no ?? ($booking['booking_no'] ?? 'BK-CONFIRMED')) ?>
            <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($booking_no ?? ($booking['booking_no'] ?? '')) ?>', this)" title="Copy ID">
              <i class="fa fa-copy"></i>
            </button>
          </span>
        </div>
      </div>

      <!-- Contact Info Box: Mobile Number & Email -->
      <?php 
        $hasGuestAcc = isset($_SESSION['guest_account_created']) && $_SESSION['guest_account_created'] === true;
        $guestId = $_SESSION['guest_account_identifier'] ?? ($booking['phone'] ?? '');
        $cleanPhone = preg_replace('/\D/', '', (string)($guestId ?: ($booking['phone'] ?? '')));
        if (str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 12) {
            $cleanPhone = substr($cleanPhone, 2);
        }
        $customerEmail = !empty($booking['email']) ? $booking['email'] : null;
      ?>
      <div class="rfx-cred-box">
        <div class="rfx-cred-row">
          <span class="rfx-cred-lbl">Contact Mobile</span>
          <span class="rfx-cred-val">
            <?= \App\Core\View::e($cleanPhone ?: 'Registered User') ?>
            <?php if (!empty($cleanPhone)): ?>
            <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($cleanPhone) ?>', this)" title="Copy Mobile">
              <i class="fa fa-copy"></i>
            </button>
            <?php endif; ?>
          </span>
        </div>

        <?php if (!empty($customerEmail)): ?>
        <div class="rfx-cred-row">
          <span class="rfx-cred-lbl">Email</span>
          <span class="rfx-cred-val" style="font-size: 11px;">
            <?= \App\Core\View::e($customerEmail) ?>
            <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($customerEmail) ?>', this)" title="Copy Email">
              <i class="fa fa-copy"></i>
            </button>
          </span>
        </div>
        <?php endif; ?>

        <?php if ($hasGuestAcc): ?>
        <div class="rfx-cred-row">
          <span class="rfx-cred-lbl">Account</span>
          <span class="rfx-cred-val" style="font-size: 11px; color: #86efac;">
            <i class="fa fa-check-circle mr-1"></i> Active (Login via Mobile)
          </span>
        </div>
        <?php endif; ?>
      </div>
      <?php
        if ($hasGuestAcc) {
          unset($_SESSION['guest_account_created']); 
          unset($_SESSION['guest_account_password']); 
          unset($_SESSION['guest_account_identifier']); 
        }
      ?>

      <!-- Booking Summary (Short & Small Display) -->
      <?php if (!empty($booking)): ?>
      <?php 
        $totAmt = (float)($booking['total_amount'] ?? $booking['starting_price'] ?? 0);
        $advAmt = (float)($booking['advance_amount'] ?? 0);
        $dueAmt = (float)($booking['due_amount'] ?? 0);
        $isCod = (($booking['payment_type'] ?? '') === 'cod' || $dueAmt > 0 || ($booking['priority'] ?? '') === 'normal');
      ?>
      <div class="rfx-sum-box">
        <div class="rfx-sum-title">Booking & Payment Summary</div>
        <div class="rfx-sum-row">
          <span>Service</span>
          <strong style="color: #f25b29;"><?= \App\Core\View::e($booking['service_name'] ?? 'Home Service') ?></strong>
        </div>
        <div class="rfx-sum-row">
          <span>Name</span>
          <strong><?= \App\Core\View::e($booking['name'] ?? 'Customer') ?></strong>
        </div>
        <div class="rfx-sum-row">
          <span>Priority</span>
          <?php if ($isCod && $dueAmt > 0): ?>
            <strong style="color: #b45309;"><i class="fa fa-clock-o mr-1"></i>Medium Priority</strong>
          <?php else: ?>
            <strong style="color: #15803d;"><i class="fa fa-bolt mr-1"></i>High Priority</strong>
          <?php endif; ?>
        </div>
        <div class="rfx-sum-row">
          <span>Total Service</span>
          <strong>₹<?= number_format($totAmt > 0 ? $totAmt : 899, 2) ?></strong>
        </div>
        <?php if ($isCod && $dueAmt > 0): ?>
          <div class="rfx-sum-row">
            <span>Pre-Booking (Online)</span>
            <strong style="color: #16a34a;">- ₹<?= number_format($advAmt > 0 ? $advAmt : 49, 2) ?> (Paid)</strong>
          </div>
          <div class="rfx-sum-row" style="background: #fff8f5; border-radius: 4px; padding: 4px 6px;">
            <span style="color: #c2410c; font-weight: 700;">Due to Technician</span>
            <strong style="color: #c2410c; font-size: 13px;">₹<?= number_format($dueAmt > 0 ? $dueAmt : 850, 2) ?></strong>
          </div>
        <?php else: ?>
          <div class="rfx-sum-row">
            <span>Payment Status</span>
            <strong style="color: #16a34a;"><i class="fa fa-check-circle mr-1"></i>100% Paid Online</strong>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Actions -->
      <div>
        <?php if (\App\Core\Auth::check()): ?>
          <a href="<?= \App\Core\View::url('/account/bookings') ?>" class="rfx-btn-dash">
            Open Dashboard &rarr;
          </a>
        <?php else: ?>
          <?php 
            $viewBookingToken = $token ?? ($booking['guest_token'] ?? '');
            $viewBookingUrl = !empty($viewBookingToken) ? \App\Core\View::url('/book-success?token=' . urlencode($viewBookingToken)) : \App\Core\View::url('/');
          ?>
          <a href="<?= $viewBookingUrl ?>" class="rfx-btn-dash">
            <i class="fa fa-ticket mr-1"></i> View Booking Details
          </a>
        <?php endif; ?>
        <a href="<?= \App\Core\View::url('/') ?>" class="rfx-link-home">
          Return to Home
        </a>
      </div>

    </div>

  </div>

  <script>
  function rfxCopy(text, btn) {
    if (!text) return;
    var orig = btn.innerHTML;
    var done = function() {
      btn.innerHTML = '<i class="fa fa-check text-success"></i>';
      setTimeout(function() { btn.innerHTML = orig; }, 1400);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(done).catch(function() {
        fallbackCopy(text, done);
      });
    } else {
      fallbackCopy(text, done);
    }
  }
  function fallbackCopy(text, cb) {
    var ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch(e) {}
    document.body.removeChild(ta);
    if (cb) cb();
  }
  </script>
</body>
</html>
