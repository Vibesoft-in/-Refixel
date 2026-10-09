<?php
use App\Core\View;
$settings = $settings ?? [];
?>

<div class="admin-settings-page" style="max-width: 900px;">
  <div class="mb-4">
    <h4 class="font-weight-bold mb-1 text-dark">Business, Media & Notification Settings</h4>
    <p class="text-muted small mb-0">Control company address, Google Maps location pin, website banners, media images, and notification channels.</p>
  </div>

  <form method="POST" action="<?= View::url('/admin/settings') ?>" enctype="multipart/form-data">
    <?= View::csrfField() ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
      <li class="nav-item">
        <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-business">
          <i class="fa fa-building-o mr-1"></i>Business & Location
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-media">
          <i class="fa fa-picture-o mr-1"></i>Website Media & Banners
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-notifications">
          <i class="fa fa-bell-o mr-1"></i>Notifications & Alerts
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-social">
          <i class="fa fa-globe mr-1"></i>Social & Channels
        </a>
      </li>
    </ul>

    <div class="tab-content mb-4">
      <!-- Tab 1: Business Profile & Office Location -->
      <div class="tab-pane fade show active" id="tab-business">
        <div class="stat-card p-4 border rounded shadow-sm bg-white mb-3">
          <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">Company & Head Office Information</h6>

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
              <label class="font-weight-bold small text-dark">Customer Support Helpline Phone</label>
              <input type="text" name="support_phone" class="form-control" value="<?= View::e($settings['support_phone'] ?? '+91 94581 82006') ?>">
              <small class="form-text text-muted">Displayed on Header, Contact Us, and Footer.</small>
            </div>

            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Official Support Email</label>
              <input type="email" name="support_email" class="form-control" value="<?= View::e($settings['support_email'] ?? 'wearerefixel@gmail.com') ?>">
              <small class="form-text text-muted">Displayed on Contact Us and customer communications.</small>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Public Office Address (Displayed on Contact Us & Footer)</label>
            <textarea name="office_address" class="form-control" rows="2"><?= View::e($settings['office_address'] ?? 'Jaspur - Kashipur Road, in front of BSV Girls Degree College, Jaspur, Uttarakhand (PIN: 244712)') ?></textarea>
          </div>

          <div class="form-row">
            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Office Landmark / Area Subtitle</label>
              <input type="text" name="office_landmark" class="form-control" value="<?= View::e($settings['office_landmark'] ?? 'In front of BSV Girls Degree College, Jaspur') ?>">
            </div>

            <div class="col-md-6 form-group mb-3">
              <label class="font-weight-bold small text-dark">Google Maps Pin Query / Location</label>
              <input type="text" name="office_map_query" class="form-control" value="<?= View::e($settings['office_map_query'] ?? 'BSV Girls Degree College, Kashipur Road, Jaspur, Uttarakhand') ?>">
              <small class="form-text text-muted">Used to pin the live Google Map on the Contact page.</small>
            </div>
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

      <!-- Tab 2: Website Media & Banners -->
      <div class="tab-pane fade" id="tab-media">
        <div class="stat-card p-4 border rounded shadow-sm bg-white mb-3">
          <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">Website Images, Logos & Banners</h6>

          <!-- 1. Site Logo -->
          <div class="form-group mb-4 pb-3 border-bottom">
            <label class="font-weight-bold small text-dark d-block">Website Logo</label>
            <div class="row align-items-center">
              <div class="col-sm-3 mb-2 mb-sm-0">
                <div class="border rounded p-2 text-center bg-light" style="max-height: 80px;">
                  <?php $logoUrl = !empty($settings['site_logo']) ? View::asset($settings['site_logo']) : View::asset('img/logo.png'); ?>
                  <img src="<?= $logoUrl ?>" alt="Logo" style="max-height: 60px; max-width: 100%; object-fit: contain;">
                </div>
              </div>
              <div class="col-sm-9">
                <input type="file" name="site_logo" accept="image/*" class="form-control-file mb-1">
                <div class="small text-muted">Or image path/URL:</div>
                <input type="text" name="site_logo" class="form-control form-control-sm" value="<?= View::e($settings['site_logo'] ?? 'img/logo.png') ?>">
              </div>
            </div>
          </div>

          <!-- 2. Hero Headline & Subtitle -->
          <div class="form-group mb-4 pb-3 border-bottom">
            <h6 class="font-weight-bold text-dark mb-2">Homepage Hero Banner Text</h6>
            <div class="form-group mb-3">
              <label class="font-weight-bold small text-dark">Hero Tagline / Promo Ribbon Text</label>
              <input type="text" name="hero_tagline" class="form-control" value="<?= View::e($settings['hero_tagline'] ?? 'Starting at ₹999 • Save up to 25% on your first booking') ?>">
            </div>
            <div class="form-group mb-0">
              <label class="font-weight-bold small text-dark">Hero Main Headline</label>
              <input type="text" name="hero_title" class="form-control" value="<?= View::e($settings['hero_title'] ?? 'Get Your Home Spotless & Germ-Free with Expert Deep Cleaning') ?>">
            </div>
          </div>

          <!-- 3. Hero Background Image / Poster -->
          <div class="form-group mb-4 pb-3 border-bottom">
            <label class="font-weight-bold small text-dark d-block">Hero Background Banner Image (Or Video Poster)</label>
            <div class="row align-items-center">
              <div class="col-sm-3 mb-2 mb-sm-0">
                <div class="border rounded p-1 text-center bg-light" style="height: 90px; overflow: hidden;">
                  <?php $heroImg = !empty($settings['hero_banner_image']) ? View::asset($settings['hero_banner_image']) : View::asset('img/banner-1.jpg'); ?>
                  <img src="<?= $heroImg ?>" alt="Hero Banner" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              </div>
              <div class="col-sm-9">
                <input type="file" name="hero_banner_image" accept="image/*" class="form-control-file mb-1">
                <div class="small text-muted">Or image path/URL:</div>
                <input type="text" name="hero_banner_image" class="form-control form-control-sm" value="<?= View::e($settings['hero_banner_image'] ?? 'img/banner-1.jpg') ?>">
              </div>
            </div>
          </div>

          <!-- 4. Promo Banner Image -->
          <div class="form-group mb-4 pb-3 border-bottom">
            <label class="font-weight-bold small text-dark d-block">Promotional Card / Seasonal Offer Image</label>
            <div class="row align-items-center">
              <div class="col-sm-3 mb-2 mb-sm-0">
                <div class="border rounded p-1 text-center bg-light" style="height: 90px; overflow: hidden;">
                  <?php $promoImg = !empty($settings['promo_banner_image']) ? View::asset($settings['promo_banner_image']) : View::asset('img/promo-banner.jpg'); ?>
                  <img src="<?= $promoImg ?>" alt="Promo Banner" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              </div>
              <div class="col-sm-9">
                <input type="file" name="promo_banner_image" accept="image/*" class="form-control-file mb-1">
                <div class="small text-muted">Or image path/URL:</div>
                <input type="text" name="promo_banner_image" class="form-control form-control-sm" value="<?= View::e($settings['promo_banner_image'] ?? 'img/promo-banner.jpg') ?>">
              </div>
            </div>
          </div>

          <!-- 5. About Us Showcase Image -->
          <div class="form-group mb-2">
            <label class="font-weight-bold small text-dark d-block">About Us Story Showcase Image</label>
            <div class="row align-items-center">
              <div class="col-sm-3 mb-2 mb-sm-0">
                <div class="border rounded p-1 text-center bg-light" style="height: 90px; overflow: hidden;">
                  <?php $aboutImg = !empty($settings['about_image']) ? View::asset($settings['about_image']) : View::asset('img/about-story.jpg'); ?>
                  <img src="<?= $aboutImg ?>" alt="About Story" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              </div>
              <div class="col-sm-9">
                <input type="file" name="about_image" accept="image/*" class="form-control-file mb-1">
                <div class="small text-muted">Or image path/URL:</div>
                <input type="text" name="about_image" class="form-control form-control-sm" value="<?= View::e($settings['about_image'] ?? 'img/about-story.jpg') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 3: Notification & Dispatch Alerts -->
      <div class="tab-pane fade" id="tab-notifications">
        <div class="stat-card p-4 border rounded shadow-sm bg-white mb-3">
          <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">Automated Communications & Dispatch</h6>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Admin Alert Notification Email</label>
            <input type="email" name="admin_notification_email" class="form-control" value="<?= View::e($settings['admin_notification_email'] ?? 'wearerefixel@gmail.com') ?>">
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

      <!-- Tab 4: Social & External Channels -->
      <div class="tab-pane fade" id="tab-social">
        <div class="stat-card p-4 border rounded shadow-sm bg-white mb-3">
          <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">Social & Live Chat Links</h6>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Live WhatsApp Chat Number</label>
            <input type="text" name="whatsapp_number" class="form-control" value="<?= View::e($settings['whatsapp_number'] ?? '919458182006') ?>">
            <small class="form-text text-muted">Used for the floating WhatsApp button on the public website (Format: 919458182006).</small>
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
