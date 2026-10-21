<div class="mb-4 text-center">
  <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: #fff3ef; color: #f25b29; font-size: 28px;">
    <i class="fa fa-lock"></i>
  </div>
  <h3 class="font-weight-bold mb-2" style="color: #0a1c33;">Set Up Two-Factor Authentication</h3>
  <p class="text-muted small mb-0">Enhance your administrator account security by binding an authenticator app (RFC 6238 TOTP).</p>
</div>

<!-- Step 1: Scan QR Code -->
<div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border-radius: 10px;">
  <div class="card-body p-3">
    <div class="d-flex align-items-center mb-3">
      <span class="badge badge-pill badge-primary mr-2" style="background: #f25b29; font-size: 13px; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">1</span>
      <h6 class="font-weight-bold mb-0" style="color: #0f172a;">Scan QR Code or Enter Manual Key</h6>
    </div>
    
    <div class="row align-items-center">
      <div class="col-md-5 text-center mb-3 mb-md-0">
        <div id="qrcode" class="p-2 bg-white d-inline-block rounded border" style="min-width: 150px; min-height: 150px;"></div>
      </div>
      <div class="col-md-7">
        <p class="small text-muted mb-2">Scan with Google Authenticator, Microsoft Authenticator, or 1Password.</p>
        <p class="small text-muted mb-1 font-weight-600">Manual Setup Key:</p>
        <div class="input-group input-group-sm mb-2">
          <input type="text" id="secretKeyInput" class="form-control font-weight-bold bg-white text-monospace" value="<?= \App\Core\View::e($secret) ?>" readonly style="letter-spacing: 1.5px; font-size: 13px;">
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" onclick="copySecretKey()" id="copyKeyBtn">
              <i class="fa fa-copy mr-1"></i> Copy
            </button>
          </div>
        </div>
        <small class="text-muted">Account: <strong><?= \App\Core\View::e($user['email'] ?? $user['name'] ?? 'Admin') ?></strong></small>
      </div>
    </div>
  </div>
</div>

<!-- Step 2: Emergency Recovery Codes -->
<div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border-radius: 10px;">
  <div class="card-body p-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="d-flex align-items-center">
        <span class="badge badge-pill badge-primary mr-2" style="background: #f25b29; font-size: 13px; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">2</span>
        <h6 class="font-weight-bold mb-0" style="color: #0f172a;">Save Emergency Recovery Codes</h6>
      </div>
      <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="copyRecoveryCodes()">
        <i class="fa fa-clipboard mr-1"></i> Copy All
      </button>
    </div>
    <p class="small text-muted mb-2">If you ever lose access to your authenticator device, each recovery code can be used <strong>once</strong> to regain entry.</p>

    <div class="bg-white p-3 rounded border text-monospace font-weight-bold" id="recoveryCodesList" style="font-size: 13.5px; letter-spacing: 1px;">
      <div class="row">
        <?php foreach ($recoveryCodes as $code): ?>
          <div class="col-6 py-1 text-center text-dark">
            <code><?= \App\Core\View::e($code) ?></code>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="alert alert-warning py-2 px-3 small mt-2 mb-0" style="border-radius: 6px;">
      <i class="fa fa-exclamation-triangle mr-1"></i> <strong>Important:</strong> Save these codes in a password manager now. They will NOT be shown again!
    </div>
  </div>
</div>

<!-- Step 3: Verification & Activation -->
<div class="card border-0 shadow-sm" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important; border-radius: 10px;">
  <div class="card-body p-3">
    <div class="d-flex align-items-center mb-3">
      <span class="badge badge-pill badge-primary mr-2" style="background: #f25b29; font-size: 13px; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">3</span>
      <h6 class="font-weight-bold mb-0" style="color: #0f172a;">Confirm Code to Activate</h6>
    </div>

    <form action="<?= \App\Core\View::url('/2fa/setup') ?>" method="POST">
      <?= \App\Core\View::csrfField() ?>

      <div class="form-group mb-3">
        <label class="font-weight-600 small text-muted">Enter the 6-digit code shown in your app:</label>
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-white" style="border-right: none; color: #94a3b8;"><i class="fa fa-key"></i></span>
          </div>
          <input type="text" 
                 name="code" 
                 class="form-control text-center font-weight-bold" 
                 placeholder="123456" 
                 maxlength="6" 
                 pattern="[0-9]{6}" 
                 inputmode="numeric" 
                 style="border-left: none; font-size: 20px; letter-spacing: 4px;" 
                 required 
                 autofocus>
        </div>
      </div>

      <button type="submit" class="btn btn-primary-custom py-2 w-100 font-weight-bold" style="background: #f25b29; color: #fff; border-radius: 6px;">
        <i class="fa fa-check-circle mr-1"></i> Verify & Enable 2FA
      </button>
    </form>
  </div>
</div>

<script src="<?= \App\Core\View::asset('assets/js/qrcode.min.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const otpauthUri = <?= json_encode($otpauthUri) ?>;
  const qrContainer = document.getElementById("qrcode");
  if (qrContainer && typeof QRCode !== "undefined") {
    new QRCode(qrContainer, {
      text: otpauthUri,
      width: 140,
      height: 140,
      colorDark: "#0a1c33",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.M
    });
  }
});

function copySecretKey() {
  const input = document.getElementById('secretKeyInput');
  input.select();
  navigator.clipboard.writeText(input.value).then(() => {
    const btn = document.getElementById('copyKeyBtn');
    btn.innerHTML = '<i class="fa fa-check text-success mr-1"></i> Copied!';
    setTimeout(() => {
      btn.innerHTML = '<i class="fa fa-copy mr-1"></i> Copy';
    }, 2000);
  });
}

function copyRecoveryCodes() {
  const codes = <?= json_encode($recoveryCodes) ?>.join("\n");
  navigator.clipboard.writeText(codes).then(() => {
    alert("Emergency recovery codes copied to clipboard! Please store them securely.");
  });
}
</script>
