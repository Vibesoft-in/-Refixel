<?php
use App\Core\View;
$settings = $settings ?? [];
?>

<div class="admin-settings-page" style="max-width: 850px;">
  <div class="mb-4">
    <h4 class="font-weight-bold mb-1 text-dark">Business & Notification Settings</h4>
    <p class="text-muted small mb-0">Configure company metadata, tax identification, notification channels, and operational preferences.</p>
  </div>

  <form method="POST" action="<?= View::url('/admin/settings') ?>">
    <?= View::csrfField() ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item">
        <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-business">
          <i class="fa fa-building-o mr-1"></i>Business Profile
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-notifications">
          <i class="fa fa-bell-o mr-1"></i>Notifications & Dispatch Alerts
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-social">
          <i class="fa fa-globe mr-1"></i>Contact & Social Channels
        </a>
      </li>
    </ul>

    <div class="tab-content">
      <!-- Tab 1: Business Profile -->
      <div class="tab-pane fade show active" id="tab-business">
        <div class="stat-card">
          <h6 class="font-weight-bold text-dark mb-3">Company Information</h6>

          <div class="form-row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Company / Platform Name</label>
              <input type="text" name="business_name" class="form-control" value="<?= View::e($settings['business_name'] ?? 'REFIXEL') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">GSTIN / Tax ID</label>
              <input type="text" name="gstin" class="form-control" value="<?= View::e($settings['gstin'] ?? '07AAAAA0000A1Z5') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Customer Support Helpline</label>
              <input type="text" name="support_phone" class="form-control" value="<?= View::e($settings['support_phone'] ?? '+91 98765 43210') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Official Support Email</label>
              <input type="email" name="support_email" class="form-control" value="<?= View::e($settings['support_email'] ?? 'care@REFIXEL.com') ?>">
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Registered Head Office Address</label>
            <textarea name="business_address" class="form-control" rows="2"><?= View::e($settings['business_address'] ?? 'DLF Cyber City, Tower B, Sector 24, Gurugram, Haryana - 122002') ?></textarea>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Operating Hours</label>
              <input type="text" name="operating_hours" class="form-control" value="<?= View::e($settings['operating_hours'] ?? 'Mon - Sun: 08:00 AM - 09:00 PM') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Currency Symbol</label>
              <input type="text" name="currency_symbol" class="form-control" value="<?= View::e($settings['currency_symbol'] ?? '₹') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 2: Notification & Dispatch Alerts -->
      <div class="tab-pane fade" id="tab-notifications">
        <div class="stat-card">
          <h6 class="font-weight-bold text-dark mb-3">Automated Communications & Dispatch</h6>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Admin Alert Notification Email</label>
            <input type="email" name="admin_notification_email" class="form-control" value="<?= View::e($settings['admin_notification_email'] ?? 'admin@REFIXEL.com') ?>">
            <small class="form-text text-muted">Receives instant notifications whenever a new booking or enquiry is placed.</small>
          </div>

          <div class="p-3 bg-light rounded mb-3">
            <div class="custom-control custom-switch mb-2">
              <input type="hidden" name="notify_email_enabled" value="0">
              <input type="checkbox" name="notify_email_enabled" value="1" class="custom-control-input" id="notif_email" <?= !empty($settings['notify_email_enabled']) ? 'checked' : '' ?>>
              <label class="custom-control-label font-weight-bold text-dark" for="notif_email">
                Enable Email Notifications (Customer Confirmation & Admin Alerts)
              </label>
            </div>
            <div class="custom-control custom-switch mb-2">
              <input type="hidden" name="notify_sms_enabled" value="0">
              <input type="checkbox" name="notify_sms_enabled" value="1" class="custom-control-input" id="notif_sms" <?= !empty($settings['notify_sms_enabled']) ? 'checked' : '' ?>>
              <label class="custom-control-label font-weight-bold text-dark" for="notif_sms">
                Enable SMS Booking Updates (Transactional Gateway)
              </label>
            </div>
            <div class="custom-control custom-switch">
              <input type="hidden" name="notify_whatsapp_enabled" value="0">
              <input type="checkbox" name="notify_whatsapp_enabled" value="1" class="custom-control-input" id="notif_wa" <?= !empty($settings['notify_whatsapp_enabled']) ? 'checked' : '' ?>>
              <label class="custom-control-label font-weight-bold text-dark" for="notif_wa">
                Enable WhatsApp Dispatch & Technician Arrival Alerts
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 3: Social & External Channels -->
      <div class="tab-pane fade" id="tab-social">
        <div class="stat-card">
          <h6 class="font-weight-bold text-dark mb-3">Social & Live Chat Links</h6>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Live WhatsApp Chat Number</label>
            <input type="text" name="whatsapp_number" class="form-control" value="<?= View::e($settings['whatsapp_number'] ?? '919876543210') ?>">
            <small class="form-text text-muted">Used for the floating WhatsApp button on the public website (Format: 919876543210).</small>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Facebook Page URL</label>
            <input type="text" name="social_facebook" class="form-control" value="<?= View::e($settings['social_facebook'] ?? 'https://www.facebook.com/share/1GU16Dtfcr/') ?>">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Instagram Profile URL</label>
            <input type="text" name="social_instagram" class="form-control" value="<?= View::e($settings['social_instagram'] ?? 'https://www.instagram.com/letsrefixel?stkn=ajJtc20zcHRyM2Q3') ?>">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">LinkedIn Profile URL</label>
            <input type="text" name="social_linkedin" class="form-control" value="<?= View::e($settings['social_linkedin'] ?? 'https://www.linkedin.com/in/lets-refixel-3aab5a43b?utm_source=share_via&utm_content=profile&utm_medium=member_android') ?>">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">X (Twitter) Profile URL</label>
            <input type="text" name="social_twitter" class="form-control" value="<?= View::e($settings['social_twitter'] ?? 'https://x.com/letsrefixel') ?>">
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">YouTube Channel URL</label>
            <input type="text" name="social_youtube" class="form-control" value="<?= View::e($settings['social_youtube'] ?? 'https://youtube.com/@letsrefixel?si=Xq0LZUFrv_yzlM5C') ?>">
          </div>
        </div>
      </div>
    </div>

    <div class="text-right">
      <button type="submit" class="btn btn-brand px-4 py-2 font-weight-bold">
        <i class="fa fa-save mr-1"></i>Save All Settings
      </button>
    </div>
  </form>
</div>

