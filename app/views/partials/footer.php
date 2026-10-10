<style>
#footer .container {
  max-width: 1240px;
}
.footer_links h4,
.footer_links h4.footer-title,
.footer-title {
  font-size: 16px !important;
  font-weight: 700 !important;
  color: #ffffff !important;
  margin-bottom: 18px !important;
  margin-top: 0 !important;
  line-height: 1.3 !important;
  white-space: nowrap !important;
}
.footer-nav-link {
  color: #cbd5e1 !important;
  text-decoration: none !important;
  display: inline-block;
  padding: 2px 0;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer !important;
  position: relative;
  font-weight: 400;
}
.footer-nav-link:hover {
  color: #f25b29 !important;
  transform: translateX(4px);
  text-decoration: none !important;
}
.footer-social-btn {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1 !important;
  font-size: 16px;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  text-decoration: none !important;
  cursor: pointer !important;
  position: relative;
  z-index: 5;
}
.footer-social-btn:hover {
  background: #f25b29 !important;
  color: #ffffff !important;
  transform: translateY(-3px);
  box-shadow: 0 5px 14px rgba(242, 91, 41, 0.45);
}
.footer-social-btn:active {
  transform: translateY(0);
}
.footer-icon-box {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 18px !important;
  min-width: 18px !important;
  height: 18px !important;
  color: #f25b29 !important;
  font-size: 15px !important;
  flex-shrink: 0 !important;
  line-height: 1 !important;
  margin: 0 !important;
}
.footer-contact-item {
  display: flex !important;
  align-items: flex-start !important;
  gap: 12px !important;
  margin-bottom: 14px !important;
  padding: 0 !important;
}
.footer-contact-item:last-child {
  margin-bottom: 0 !important;
}
.footer-contact-item.is-single-line {
  align-items: center !important;
}
.footer-contact-item:not(.is-single-line) .footer-icon-box {
  margin-top: 2px !important;
}
.footer-contact-text {
  flex: 1 !important;
  min-width: 0 !important;
  font-size: 13.5px !important;
  line-height: 1.45 !important;
  color: #cbd5e1 !important;
  margin: 0 !important;
  padding: 0 !important;
  display: block !important;
}
.footer_links ul li a.footer-contact-link,
.footer-contact-link {
  color: #cbd5e1 !important;
  text-decoration: none !important;
  display: inline-flex !important;
  align-items: center !important;
  line-height: 1.4 !important;
  font-size: 13.5px !important;
  font-weight: 400 !important;
  white-space: nowrap !important;
  transition: color 0.2s ease !important;
  padding: 0 !important;
  margin: 0 !important;
}
.footer_links ul li a.footer-contact-link:hover,
.footer-contact-link:hover {
  color: #f25b29 !important;
  text-decoration: none !important;
  transform: none !important;
}
.footer-hours-box {
  display: flex !important;
  align-items: flex-start !important;
  gap: 12px !important;
  margin: 0 !important;
  padding: 0 !important;
}
.footer-hours-box .footer-icon-box {
  margin-top: 2px !important;
}
.footer-hours-content {
  flex: 1 !important;
  min-width: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
}
.footer-hours-days {
  font-weight: 600 !important;
  color: #ffffff !important;
  font-size: 13.5px !important;
  line-height: 1.35 !important;
  margin: 0 !important;
  padding: 0 !important;
}
.footer-hours-time {
  color: #cbd5e1 !important;
  font-size: 13px !important;
  line-height: 1.35 !important;
  margin-top: 3px !important;
  padding: 0 !important;
}
.footer-services-section {
  display: flex;
  justify-content: center;
}
.footer-services-inner {
  display: inline-block;
  text-align: left;
}
.footer-services-title {
  text-align: left !important;
  margin-left: 20px !important;
  margin-bottom: 18px !important;
}
.footer-services-wrap {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}
.footer-services-col {
  flex: 0 0 auto;
  text-align: left;
}
@media (max-width: 767.98px) {
  .footer-services-section {
    justify-content: flex-start;
  }
  .footer-services-title {
    margin-left: 0 !important;
  }
}
@media (min-width: 992px) {
  .footer-col-brand { flex: 0 0 21%; max-width: 21%; }
  .footer-col-quick { flex: 0 0 16%; max-width: 16%; }
  .footer-col-services { flex: 0 0 26%; max-width: 26%; }
  .footer-col-contact { flex: 0 0 22%; max-width: 22%; }
  .footer-col-hours { flex: 0 0 15%; max-width: 15%; }
}
</style>
<div id="footer" class="footer_main" style="background: #0a1c33; color: #cbd5e1; padding: 24px 0 30px 0; position: relative; z-index: 10;">
  <div class="container">
    <div class="row">
      <!-- Col 1: Brand & Social -->
      <div class="footer-col-brand col-md-6 mb-4">
        <div class="footer_links">
          <div class="mb-3">
            <a href="<?= \App\Core\View::url('/') ?>" style="display: inline-block; width: 100%; max-width: 250px; text-decoration: none;">
              <img src="<?= \App\Core\View::asset('img/refixel-footer-logo-trimmed.png') ?>" alt="REFIXEL" style="width: 100%; max-width: 250px; height: auto; display: block; object-fit: contain;">
            </a>
          </div>
          <p style="font-size: 14.5px; line-height: 1.7; color: #cbd5e1;">
            Your trusted partner for professional, reliable, and hassle-free home services across India.
          </p>
        </div>

        <div class="footer_social_icons mt-3">
          <ul class="d-flex list-unstyled p-0" style="gap: 10px;">
            <li><a href="https://www.facebook.com/share/1GU16Dtfcr/" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="Facebook" aria-label="Facebook"><i class="fa fa-facebook-square"></i></a></li>
            <li><a href="https://www.instagram.com/letsrefixel?stkn=ajJtc20zcHRyM2Q3" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="Instagram" aria-label="Instagram"><i class="fa fa-instagram"></i></a></li>
            <li><a href="https://www.linkedin.com/in/lets-refixel-3aab5a43b?utm_source=share_via&amp;utm_content=profile&amp;utm_medium=member_android" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="LinkedIn" aria-label="LinkedIn"><i class="fa fa-linkedin-square"></i></a></li>
            <li><a href="https://x.com/letsrefixel" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="X (Twitter)" aria-label="X"><i class="fa fa-twitter"></i></a></li>
            <li><a href="https://youtube.com/@letsrefixel?si=Xq0LZUFrv_yzlM5C" target="_blank" rel="noopener noreferrer" class="footer-social-btn" title="YouTube" aria-label="YouTube"><i class="fa fa-youtube-play"></i></a></li>
          </ul>
        </div>
      </div>

      <!-- Col 2: Quick Links -->
      <div class="footer-col-quick col-md-6 mb-4">
        <div class="footer_links">
          <h4 class="footer-title" style="color: #ffffff !important;">Quick Links</h4>
          <ul class="list-unstyled p-0" style="font-size: 13.5px; line-height: 2.2;">
            <li><a href="<?= \App\Core\View::url('/about') ?>" class="footer-nav-link">About Us</a></li>
            <li><a href="<?= \App\Core\View::url('/privacy') ?>" class="footer-nav-link">Privacy Policy</a></li>
            <li><a href="<?= \App\Core\View::url('/terms') ?>" class="footer-nav-link">Terms & Conditions</a></li>
            <li><a href="<?= \App\Core\View::url('/refund') ?>" class="footer-nav-link">Refund Policy</a></li>
            <li><a href="<?= \App\Core\View::url('/faq') ?>" class="footer-nav-link">FAQs</a></li>
            <li><a href="<?= \App\Core\View::url('/contact') ?>" class="footer-nav-link">Contact Us</a></li>
          </ul>
        </div>
      </div>

      <!-- Col 3: Our Services -->
      <div class="footer-col-services col-md-6 mb-4">
        <div class="footer_links footer-services-section">
          <div class="footer-services-inner">
            <h4 class="footer-title footer-services-title" style="color: #ffffff !important;">Our Services</h4>
            <div class="footer-services-wrap">
              <div class="footer-services-col">
                <ul class="list-unstyled p-0 mb-0" style="font-size: 13.5px; line-height: 2.2;">
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">AC Repair</a></li>
                  <li><a href="<?= \App\Core\View::url('/electrician-services') ?>" class="footer-nav-link">Electrician</a></li>
                  <li><a href="<?= \App\Core\View::url('/plumber-services') ?>" class="footer-nav-link">Plumbing</a></li>
                  <li><a href="<?= \App\Core\View::url('/cleaning-services') ?>" class="footer-nav-link">Cleaning</a></li>
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">Laundry</a></li>
                </ul>
              </div>
              <div class="footer-services-col">
                <ul class="list-unstyled p-0 mb-0" style="font-size: 13.5px; line-height: 2.2;">
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">Pest Control</a></li>
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">Carpenter</a></li>
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">Painting</a></li>
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">Appliance Repair</a></li>
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">More Services</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Col 4: Contact Us -->
      <div class="footer-col-contact col-md-6 mb-4">
        <div class="footer_links">
          <h4 class="footer-title" style="color: #ffffff !important;">Contact Us</h4>
          <?php
            $fAddress = \App\Models\Setting::get('office_address', 'Jaspur - Kashipur Road (In front of BSV Girls Degree College), Jaspur, Uttarakhand');
            $fEmail = \App\Models\Setting::get('support_email', 'wearerefixel@gmail.com');
            $fPhone = \App\Models\Setting::get('support_phone', '+91 94581 82006');
            $fCleanPhone = preg_replace('/[^0-9+]/', '', $fPhone);
            $fHours = \App\Models\Setting::get('operating_hours', 'Mon - Sun: 08:00 AM - 09:00 PM');
          ?>
          <ul class="list-unstyled p-0 m-0">
            <li class="footer-contact-item">
              <span class="footer-icon-box">
                <i class="fa fa-map-marker"></i>
              </span>
              <span class="footer-contact-text"><?= \App\Core\View::e($fAddress) ?></span>
            </li>
            <li class="footer-contact-item is-single-line">
              <span class="footer-icon-box">
                <i class="fa fa-envelope"></i>
              </span>
              <a href="mailto:<?= \App\Core\View::e($fEmail) ?>" class="footer-contact-link"><?= \App\Core\View::e($fEmail) ?></a>
            </li>
            <li class="footer-contact-item is-single-line">
              <span class="footer-icon-box">
                <i class="fa fa-phone"></i>
              </span>
              <a href="tel:<?= \App\Core\View::e($fCleanPhone) ?>" class="footer-contact-link"><?= \App\Core\View::e($fPhone) ?></a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Col 5: Working Hours -->
      <div class="footer-col-hours col-md-6 mb-4">
        <div class="footer_links">
          <h4 class="footer-title" style="color: #ffffff !important;">Working Hours</h4>
          <div class="footer-hours-box">
            <span class="footer-icon-box">
              <i class="fa fa-clock-o"></i>
            </span>
            <div class="footer-hours-content">
              <div class="footer-hours-days"><?= \App\Core\View::e($fHours) ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bottom_footer" style="background: #061324; border-top: 1px solid rgba(255,255,255,0.06); padding: 18px 0; font-size: 13px; color: #94a3b8;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> REFIXEL Home Services. All Rights Reserved. Built / Design <span style="color: #ef4444;">❤️</span> by Vibesoft.</p>
      </div>
    </div>
  </div>
</div>
