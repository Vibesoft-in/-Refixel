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
            ID: <?= \App\Core\View::e($enquiry['booking_no'] ?? $booking_no ?? 'BK-PENDING') ?>
            <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($enquiry['booking_no'] ?? $booking_no ?? '') ?>', this)" title="Copy ID">
              <i class="fa fa-copy"></i>
            </button>
          </span>
        </div>
      </div>

      <!-- Mobile Number, Email & Password Credentials -->
      <div class="rfx-cred-box">
        <div class="rfx-cred-row">
          <span class="rfx-cred-lbl">Mobile Number</span>
          <span class="rfx-cred-val">
            <?= \App\Core\View::e($enquiry['phone'] ?? $enquiry['identifier'] ?? '') ?>
            <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($enquiry['phone'] ?? $enquiry['identifier'] ?? '') ?>', this)" title="Copy Mobile">
              <i class="fa fa-copy"></i>
            </button>
          </span>
        </div>

        <?php if (!empty($enquiry['email'])): ?>
        <div class="rfx-cred-row">
          <span class="rfx-cred-lbl">Email</span>
          <span class="rfx-cred-val" style="font-size: 11px;">
            <?= \App\Core\View::e($enquiry['email']) ?>
            <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($enquiry['email']) ?>', this)" title="Copy Email">
              <i class="fa fa-copy"></i>
            </button>
          </span>
        </div>
        <?php endif; ?>

        <div class="rfx-cred-row">
          <span class="rfx-cred-lbl">Password</span>
          <span class="rfx-cred-val">
            <?php if (!empty($enquiry['generated_password'])): ?>
              <span style="font-family: monospace; color: #fde047; letter-spacing: 0.5px;"><?= \App\Core\View::e($enquiry['generated_password']) ?></span>
              <button type="button" class="rfx-copy-btn" onclick="rfxCopy('<?= \App\Core\View::e($enquiry['generated_password']) ?>', this)" title="Copy Password">
                <i class="fa fa-copy"></i> Copy
              </button>
            <?php else: ?>
              <span style="color: #34d399; font-size: 11px;"><i class="fa fa-check-circle mr-1"></i> Logged In</span>
            <?php endif; ?>
          </span>
        </div>
      </div>

      <!-- Enquiry Summary (Short & Small Display) -->
      <div class="rfx-sum-box">
        <div class="rfx-sum-title">Enquiry Summary</div>
        <div class="rfx-sum-row">
          <span>Service</span>
          <strong style="color: #f25b29;"><?= \App\Core\View::e($enquiry['service_name'] ?? 'Home Service') ?></strong>
        </div>
        <div class="rfx-sum-row">
          <span>Name</span>
          <strong><?= \App\Core\View::e($enquiry['name'] ?? 'Guest') ?></strong>
        </div>
        <div class="rfx-sum-row">
          <span>Location</span>
          <strong><?= \App\Core\View::e($enquiry['location'] ?? 'Service Area') ?></strong>
        </div>
      </div>

      <!-- Actions -->
      <div>
        <a href="<?= \App\Core\View::url('/account') ?>" class="rfx-btn-dash">
          Open Dashboard &rarr;
        </a>
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
