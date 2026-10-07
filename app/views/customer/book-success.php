<div class="container py-5 my-4">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 text-center">
      <div class="card p-5 border-0 shadow-sm" style="border-radius: 20px; background: #ffffff;">
        <div class="mb-4">
          <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: #fff3ec; color: #f25b29; font-size: 38px; border: 2px solid #ffdacf;">
            <i class="fa fa-check"></i>
          </div>
        </div>

        <h2 class="font-weight-bold mb-2" style="color: #1a1a1a;">Booking Confirmed!</h2>
        <p class="text-muted mb-4">Thank you for booking with REFIXEL. We have matched your request with our operations desk.</p>

        <div class="p-3 rounded bg-light border mb-4 text-center">
          <span class="text-muted small d-block">Your Booking Reference Number</span>
          <h3 class="font-weight-bold mb-0" style="color: #f25b29; letter-spacing: 1px;"><?= \App\Core\View::e($booking_no ?? 'RFX-BK-PENDING') ?></h3>
        </div>

        <p class="text-muted small mb-4" style="line-height: 1.8;">
          A confirmation SMS and WhatsApp message has been dispatched to your mobile number. A verified technician will call you 30 minutes prior to arrival.
        </p>

        <?php if (isset($_SESSION['guest_account_created']) && $_SESSION['guest_account_created'] === true): ?>
          <div class="alert alert-success text-left p-4 mb-4" style="border-radius: 12px; border: 1px solid #ffdacf; background-color: #fff8f5;">
            <h5 class="alert-heading font-weight-bold mb-2" style="color: #0a1c33;"><i class="fa fa-user-circle mr-2" style="color: #f25b29;"></i> Account Created Automatically!</h5>
            <p class="small mb-3" style="color: #475569;">We have created a free account for you so you can easily track your bookings and download invoices.</p>
            <div class="bg-white p-3 rounded border">
              <div class="mb-2">
                <span class="text-muted small">Login ID (Mobile/Email):</span><br>
                <strong class="text-dark" style="font-size: 15px;"><?= \App\Core\View::e($_SESSION['guest_account_identifier']) ?></strong>
              </div>
              <div>
                <span class="text-muted small">Temporary Password:</span><br>
                <strong class="text-dark" style="font-size: 15px; font-family: monospace; letter-spacing: 1px;"><?= \App\Core\View::e($_SESSION['guest_account_password']) ?></strong>
              </div>
            </div>
            <p class="small mt-3 mb-0 text-muted"><em>Please save these details. You can change your password anytime from your account profile.</em></p>
          </div>
          <?php 
            // Clear the session so it doesn't show again if refreshed
            unset($_SESSION['guest_account_created']); 
            unset($_SESSION['guest_account_password']); 
            unset($_SESSION['guest_account_identifier']); 
          ?>
        <?php endif; ?>

        <div class="d-flex flex-column flex-sm-row justify-content-center" style="gap: 12px;">
          <?php if (\App\Core\Auth::check()): ?>
            <a href="<?= \App\Core\View::url('/account/bookings') ?>" class="btn text-white px-4 py-2 font-weight-bold" style="background:#f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
              Track in My Account
            </a>
          <?php endif; ?>
          <a href="<?= \App\Core\View::url('/') ?>" class="btn btn-outline-dark px-4 py-2 font-weight-bold" style="border-radius: 8px;">
            Return to Home
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
