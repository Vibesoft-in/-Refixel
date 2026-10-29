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
  #footer .container {
    padding-left: 14px !important;
    padding-right: 14px !important;
  }
  #footer .row {
    margin-left: -6px !important;
    margin-right: -6px !important;
  }
  .footer-col-quick,
  .footer-col-services,
  .footer-col-contact,
  .footer-col-hours {
    padding-left: 6px !important;
    padding-right: 6px !important;
    margin-bottom: 22px !important;
  }

  /* Center Brand logo, paragraph, and social icons on mobile */
  .footer-col-brand {
    text-align: center !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    margin-bottom: 24px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
  }
  .footer-col-brand .footer_links {
    text-align: center !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    width: 100% !important;
  }
  .footer-col-brand .footer_links a {
    display: inline-block !important;
    margin: 0 auto !important;
  }
  .footer-col-brand .footer_links img {
    margin: 0 auto !important;
  }
  .footer-col-brand .footer_links p {
    text-align: center !important;
    max-width: 380px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
  }
  .footer_social_icons {
    width: 100% !important;
    display: flex !important;
    justify-content: center !important;
  }
  .footer_social_icons ul {
    justify-content: center !important;
    margin: 0 auto !important;
  }

  /* Section Titles */
  .footer_links h4,
  .footer_links h4.footer-title,
  .footer-title {
    font-size: 14.5px !important;
    margin-bottom: 12px !important;
  }

  /* Quick links in left 50% column */
  .footer-quick-links-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 0 !important;
    width: 100% !important;
  }
  .footer-quick-links-list li {
    width: 100% !important;
    margin-bottom: 2px !important;
  }
  .footer-quick-links-list .footer-nav-link {
    font-size: 12px !important;
    line-height: 1.65 !important;
    padding: 1px 0 !important;
    display: inline-block !important;
  }

  /* Our Services in right 50% column (2 sub-columns of 5 items) */
  .footer-services-section {
    justify-content: flex-start !important;
    display: block !important;
  }
  .footer-services-inner {
    display: block !important;
    width: 100% !important;
  }
  .footer-services-title {
    margin-left: 0 !important;
  }
  .footer-services-wrap {
    display: flex !important;
    gap: 6px !important;
    width: 100% !important;
  }
  .footer-services-col {
    flex: 1 1 50% !important;
    min-width: 0 !important;
  }
  .footer-services-col ul {
    font-size: 11.5px !important;
    line-height: 1.65 !important;
  }
  .footer-services-col ul li {
    margin-bottom: 2px !important;
  }
  .footer-services-col .footer-nav-link {
    font-size: 11px !important;
    line-height: 1.4 !important;
    padding: 1px 0 !important;
    display: block !important;
    letter-spacing: -0.2px !important;
    word-break: break-word !important;
  }

  /* Contact Us in left 50% column */
  .footer-contact-item {
    gap: 7px !important;
    margin-bottom: 9px !important;
  }
  .footer-icon-box {
    width: 14px !important;
    min-width: 14px !important;
    height: 14px !important;
    font-size: 12px !important;
    margin-top: 2px !important;
  }
  .footer-contact-text {
    font-size: 11.5px !important;
    line-height: 1.4 !important;
  }
  .footer-contact-link {
    font-size: 11.5px !important;
    line-height: 1.35 !important;
    word-break: break-all !important;
    white-space: normal !important;
  }

  /* Working Hours in right 50% column */
  .footer-hours-box {
    gap: 7px !important;
  }
  .footer-hours-days {
    font-size: 11.5px !important;
    line-height: 1.4 !important;
  }
  .footer-hours-time {
    font-size: 11px !important;
    line-height: 1.35 !important;
  }

  .footer-map-box {
    margin-top: 10px;
  }
  .footer-map-frame {
    height: 105px;
  }
  .footer-map-link {
    font-size: 10.5px;
  }

  .bottom_footer {
    padding-bottom: 80px !important;
  }

  /* Local Service Directory Hubs (amne-samne 2-column mobile layout) */
  .footer-dir-intro {
    font-size: 11.5px !important;
    line-height: 1.5 !important;
    margin-bottom: 14px !important;
  }
  .footer-dir-col {
    padding-left: 6px !important;
    padding-right: 6px !important;
    margin-bottom: 18px !important;
  }
  .footer-dir-col h6 {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    margin-bottom: 8px !important;
    line-height: 1.35 !important;
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
  }
  .footer-dir-col ul {
    font-size: 11px !important;
    line-height: 1.4 !important;
  }
  .footer-dir-col ul li {
    margin-bottom: 6px !important;
  }
  .footer-dir-col .footer-nav-link {
    font-size: 10.5px !important;
    line-height: 1.35 !important;
    padding: 1px 0 !important;
    display: block !important;
    word-break: break-word !important;
    white-space: normal !important;
  }
}
@media (min-width: 992px) {
  .footer-col-brand { flex: 0 0 21%; max-width: 21%; }
  .footer-col-quick { flex: 0 0 16%; max-width: 16%; }
  .footer-col-services { flex: 0 0 26%; max-width: 26%; }
  .footer-col-contact-group { flex: 0 0 37%; max-width: 37%; }
  .footer-col-contact-inner { flex: 0 0 59%; max-width: 59%; }
  .footer-col-hours-inner { flex: 0 0 41%; max-width: 41%; }
}
.footer-map-box {
  margin-top: 14px;
  width: 100%;
}
.footer-map-frame {
  width: 100%;
  height: 135px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.28);
  background: #0f2744;
  position: relative;
}
.footer-map-frame iframe {
  width: 100% !important;
  height: 100% !important;
  border: 0 !important;
  display: block !important;
}
.footer-map-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-top: 6px;
  font-size: 11.5px;
  color: #f25b29 !important;
  text-decoration: none !important;
  font-weight: 500;
  transition: all 0.2s ease;
}
.footer-map-link:hover {
  color: #ff7a4d !important;
  transform: translateX(3px);
  text-decoration: none !important;
}
.footer-seo-tag {
  color: #94a3b8 !important;
  text-decoration: none !important;
  transition: color 0.15s ease;
}
.footer-seo-tag:hover {
  color: #f25b29 !important;
  text-decoration: underline !important;
}
.footer-seo-loc {
  color: #64748b;
}
</style>
<div id="footer" class="footer_main" style="background: #0a1c33; color: #cbd5e1; padding: 24px 0 30px 0; position: relative; z-index: 10;">
  <div class="container">
    <div class="row">
      <!-- Col 1: Brand & Social -->
      <div class="footer-col-brand col-12 col-md-6 mb-4">
        <div class="footer_links">
          <div class="mb-3">
            <a href="<?= \App\Core\View::url('/') ?>" style="display: inline-block; width: 100%; max-width: 250px; text-decoration: none;">
              <img src="<?= \App\Core\View::asset('img/refixel-footer-logo-trimmed.png') ?>" alt="REFIXEL" style="width: 100%; max-width: 250px; height: auto; display: block; object-fit: contain;">
            </a>
          </div>
          <p style="font-size: 14.5px; line-height: 1.7; color: #cbd5e1; margin-bottom: 8px;">
            REFIXEL – Best verified home services in Kashipur, Jaspur &amp; Thakurdwara, Uttarakhand.
          </p>
          <div class="footer-founders-badge mb-2" style="font-size: 12.5px; color: #94a3b8; padding-top: 4px;">
            <span style="color: #f25b29; font-weight: 700;"><i class="fa fa-users mr-1"></i> Founders:</span>
            <span style="color: #f8fafc; font-weight: 600;">Shanu Kumar</span> &bull; 
            <span style="color: #f8fafc; font-weight: 600;">Manish Kumar</span> &bull; 
            <span style="color: #f8fafc; font-weight: 600;">Aakash Kumar</span>
          </div>
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

      <!-- Col 2: Quick Links (Row 1 Left) -->
      <div class="footer-col-quick col-6 col-md-6 mb-4">
        <div class="footer_links">
          <h4 class="footer-title" style="color: #ffffff !important;">Quick Links</h4>
          <ul class="list-unstyled p-0 footer-quick-links-list" style="font-size: 13.5px; line-height: 2.2;">
            <li><a href="<?= \App\Core\View::url('/about') ?>" class="footer-nav-link">About Us</a></li>
            <li><a href="<?= \App\Core\View::url('/privacy') ?>" class="footer-nav-link">Privacy Policy</a></li>
            <li><a href="<?= \App\Core\View::url('/terms') ?>" class="footer-nav-link">Terms &amp; Conditions</a></li>
            <li><a href="<?= \App\Core\View::url('/refund') ?>" class="footer-nav-link">Refund Policy</a></li>
            <li><a href="<?= \App\Core\View::url('/faq') ?>" class="footer-nav-link">FAQs</a></li>
            <li><a href="<?= \App\Core\View::url('/contact') ?>" class="footer-nav-link">Contact Us</a></li>
          </ul>
        </div>
      </div>

      <!-- Col 3: Our Services (Row 1 Right) -->
      <div class="footer-col-services col-6 col-md-6 mb-4">
        <div class="footer_links footer-services-section">
          <div class="footer-services-inner">
            <h4 class="footer-title footer-services-title" style="color: #ffffff !important;">Our Services</h4>
            <div class="footer-services-wrap">
              <div class="footer-services-col">
                <ul class="list-unstyled p-0 mb-0" style="font-size: 13.5px; line-height: 2.2;">
                  <li><a href="<?= \App\Core\View::url('/ac-services') ?>" class="footer-nav-link">AC Repair</a></li>
                  <li><a href="<?= \App\Core\View::url('/electrician-services') ?>" class="footer-nav-link">Electrician</a></li>
                  <li><a href="<?= \App\Core\View::url('/plumber-services') ?>" class="footer-nav-link">Plumbing</a></li>
                  <li><a href="<?= \App\Core\View::url('/cleaning-services') ?>" class="footer-nav-link">Cleaning</a></li>
                  <li><a href="<?= \App\Core\View::url('/fall-ceiling-services') ?>" class="footer-nav-link">Fall Ceiling</a></li>
                </ul>
              </div>
              <div class="footer-services-col">
                <ul class="list-unstyled p-0 mb-0" style="font-size: 13.5px; line-height: 2.2;">
                  <li><a href="<?= \App\Core\View::url('/pest-control-services') ?>" class="footer-nav-link">Pest Control</a></li>
                  <li><a href="<?= \App\Core\View::url('/carpenter-services') ?>" class="footer-nav-link">Carpenter</a></li>
                  <li><a href="<?= \App\Core\View::url('/painting-services') ?>" class="footer-nav-link">Painting</a></li>
                  <li><a href="<?= \App\Core\View::url('/appliance-repair-services') ?>" class="footer-nav-link">Appliance Repair</a></li>
                  <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">More Services</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Col 4 & 5: Contact Us & Working Hours with Full Width Map -->
      <div class="footer-col-contact-group col-12 col-md-12 mb-4">
        <div class="row">
          <!-- Contact Us -->
          <div class="footer-col-contact footer-col-contact-inner col-6 col-md-6 mb-2">
            <div class="footer_links">
              <h4 class="footer-title" style="color: #ffffff !important;">Contact Us</h4>
              <?php
                $fAddress = \App\Models\Setting::get('office_address', 'Jaspur - Kashipur Road (In front of BSV Girls Degree College), Jaspur, Uttarakhand');
                $fEmail = \App\Models\Setting::get('support_email', 'contact@refixel.com');
                $fPhone = \App\Models\Setting::get('support_phone', '+91 87911 54730');
                $fCleanPhone = preg_replace('/[^0-9+]/', '', $fPhone);
                $fHours = \App\Models\Setting::get('operating_hours', 'Mon - Sun: 08:00 AM - 09:00 PM');
                $fMapEmbed = \App\Models\Setting::get('google_maps_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3479.157801016238!2d78.8243635755258!3d29.307046475303157!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390a496b4e5d108d%3A0xf6d218115857576e!2sRefixel%20%7C%20Home%20cleaning%20service!5e0!3m2!1sen!2sin!4v1792744016340!5m2!1sen!2sin');
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
                  <a href="tel:<?= $fCleanPhone ?>" class="footer-contact-link"><?= \App\Core\View::e($fPhone) ?></a>
                </li>
              </ul>
            </div>
          </div>

          <!-- Working Hours -->
          <div class="footer-col-hours footer-col-hours-inner col-6 col-md-6 mb-2">
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

          <!-- Full Width Google Map across Contact Us & Working Hours -->
          <div class="col-12">
            <div class="footer-map-box">
              <div class="footer-map-frame">
                <iframe
                  src="<?= \App\Core\View::e($fMapEmbed) ?>"
                  width="100%"
                  height="100%"
                  style="border:0;"
                  allowfullscreen=""
                  loading="lazy"
                  referrerpolicy="strict-origin-when-cross-origin"
                  title="Refixel Location on Google Maps"
                ></iframe>
              </div>
              <a href="https://maps.google.com/?cid=17785210960533280622" target="_blank" rel="noopener noreferrer" class="footer-map-link" title="Open Refixel in Google Maps">
                <i class="fa fa-map-marker"></i> Open in Maps <i class="fa fa-external-link" style="font-size: 9.5px;"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Local Service Coverage & Directory Section (SEO internal linking for organic indexing) -->
      <div class="col-12 mt-1 pt-4 border-top" style="border-top: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
          <h5 class="footer-title m-0" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.2px;">
            <i class="fa fa-map-marker mr-2" style="color: #f25b29;"></i> Local Service Directory &amp; Coverage Hubs
          </h5>
          <span class="badge px-2.5 py-1" style="background: rgba(242, 91, 41, 0.15); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.3); font-size: 11.5px; border-radius: 6px;">
            <i class="fa fa-bolt mr-1"></i> Verified Doorstep Pros
          </span>
        </div>
        <p class="footer-dir-intro" style="font-size: 12.5px; color: #94a3b8; line-height: 1.6; margin-bottom: 20px;">
          REFIXEL provides standardized, background-verified home care and maintenance services across Kashipur, Jaspur &amp; Thakurdwara. Select your location below to book certified technicians with transparent pricing and 30-Day Service Guarantee:
        </p>

        <div class="row footer-dir-grid">
          <!-- City: Kashipur -->
          <div class="footer-dir-col col-6 col-sm-6 col-lg-3 mb-3">
            <h6 style="color: #f8fafc; font-size: 13px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f25b29;"></span> Kashipur (244713)
            </h6>
            <ul class="list-unstyled p-0 m-0" style="font-size: 12px; line-height: 2;">
              <li><a href="<?= \App\Core\View::url('/ac-services-in-kashipur') ?>" class="footer-nav-link" title="AC Service &amp; Repair in Kashipur">AC Service &amp; Repair in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-jet-service-in-kashipur') ?>" class="footer-nav-link" title="Split AC High-Pressure Jet Service in Kashipur">AC High-Pressure Jet Wash in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-gas-leakage-refill-in-kashipur') ?>" class="footer-nav-link" title="AC Gas Leak Fix &amp; Refill in Kashipur">AC Gas Leak &amp; Refill in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/cleaning-services-in-kashipur') ?>" class="footer-nav-link" title="Deep Home Cleaning in Kashipur">Deep Home Cleaning in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/water-tank-cleaning-in-kashipur') ?>" class="footer-nav-link" title="Water Tank Deep Cleaning in Kashipur">Water Tank Cleaning in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/sofa-cleaning-in-kashipur') ?>" class="footer-nav-link" title="Sofa &amp; Upholstery Shampooing in Kashipur">Sofa Shampoo &amp; Deep Clean in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/electrician-services-in-kashipur') ?>" class="footer-nav-link" title="Electrician Services in Kashipur">Electrician &amp; Wiring in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/plumber-services-in-kashipur') ?>" class="footer-nav-link" title="Plumber Services in Kashipur">Plumber &amp; Pipe Repair in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/painting-services-in-kashipur') ?>" class="footer-nav-link" title="House &amp; Wall Painting in Kashipur">House &amp; Wall Painting in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/pest-control-services-in-kashipur') ?>" class="footer-nav-link" title="Pest &amp; Termite Control in Kashipur">Pest &amp; Termite Control in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/fall-ceiling-services-in-kashipur') ?>" class="footer-nav-link" title="Fall Ceiling &amp; POP in Kashipur">Fall Ceiling &amp; POP in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/carpenter-services-in-kashipur') ?>" class="footer-nav-link" title="Carpenter Services in Kashipur">Carpenter &amp; Furniture in Kashipur</a></li>
              <li><a href="<?= \App\Core\View::url('/appliance-repair-services-in-kashipur') ?>" class="footer-nav-link" title="Appliance Repair in Kashipur">Washing Machine &amp; Appliance Repair</a></li>
            </ul>
          </div>

          <!-- City: Jaspur -->
          <div class="footer-dir-col col-6 col-sm-6 col-lg-3 mb-3">
            <h6 style="color: #f8fafc; font-size: 13px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f25b29;"></span> Jaspur (244712)
            </h6>
            <ul class="list-unstyled p-0 m-0" style="font-size: 12px; line-height: 2;">
              <li><a href="<?= \App\Core\View::url('/ac-services-in-jaspur') ?>" class="footer-nav-link" title="AC Service &amp; Repair in Jaspur">AC Service &amp; Repair in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-jet-service-in-jaspur') ?>" class="footer-nav-link" title="Split AC High-Pressure Jet Service in Jaspur">AC High-Pressure Jet Wash in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-gas-leakage-refill-in-jaspur') ?>" class="footer-nav-link" title="AC Gas Leak Fix &amp; Refill in Jaspur">AC Gas Leak &amp; Refill in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/cleaning-services-in-jaspur') ?>" class="footer-nav-link" title="Deep Home Cleaning in Jaspur">Deep Home Cleaning in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/water-tank-cleaning-in-jaspur') ?>" class="footer-nav-link" title="Water Tank Deep Cleaning in Jaspur">Water Tank Cleaning in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/sofa-cleaning-in-jaspur') ?>" class="footer-nav-link" title="Sofa &amp; Upholstery Shampooing in Jaspur">Sofa Shampoo &amp; Deep Clean in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/electrician-services-in-jaspur') ?>" class="footer-nav-link" title="Electrician Services in Jaspur">Electrician &amp; Wiring in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/plumber-services-in-jaspur') ?>" class="footer-nav-link" title="Plumber Services in Jaspur">Plumber &amp; Pipe Repair in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/painting-services-in-jaspur') ?>" class="footer-nav-link" title="House &amp; Wall Painting in Jaspur">House &amp; Wall Painting in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/pest-control-services-in-jaspur') ?>" class="footer-nav-link" title="Pest &amp; Termite Control in Jaspur">Pest &amp; Termite Control in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/fall-ceiling-services-in-jaspur') ?>" class="footer-nav-link" title="Fall Ceiling &amp; POP in Jaspur">Fall Ceiling &amp; POP in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/carpenter-services-in-jaspur') ?>" class="footer-nav-link" title="Carpenter Services in Jaspur">Carpenter &amp; Furniture in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/appliance-repair-services-in-jaspur') ?>" class="footer-nav-link" title="Appliance Repair in Jaspur">Washing Machine &amp; Appliance Repair</a></li>
            </ul>
          </div>

          <!-- City: Thakurdwara -->
          <div class="footer-dir-col col-6 col-sm-6 col-lg-3 mb-3">
            <h6 style="color: #f8fafc; font-size: 13px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f25b29;"></span> Thakurdwara (244601)
            </h6>
            <ul class="list-unstyled p-0 m-0" style="font-size: 12px; line-height: 2;">
              <li><a href="<?= \App\Core\View::url('/ac-services-in-thakurdwara') ?>" class="footer-nav-link" title="AC Service &amp; Repair in Thakurdwara">AC Service &amp; Repair in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-jet-service-in-thakurdwara') ?>" class="footer-nav-link" title="Split AC High-Pressure Jet Service in Thakurdwara">AC High-Pressure Jet Wash in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-gas-leakage-refill-in-thakurdwara') ?>" class="footer-nav-link" title="AC Gas Leak Fix &amp; Refill in Thakurdwara">AC Gas Leak &amp; Refill in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/cleaning-services-in-thakurdwara') ?>" class="footer-nav-link" title="Deep Home Cleaning in Thakurdwara">Deep Home Cleaning in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/water-tank-cleaning-in-thakurdwara') ?>" class="footer-nav-link" title="Water Tank Deep Cleaning in Thakurdwara">Water Tank Cleaning in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/sofa-cleaning-in-thakurdwara') ?>" class="footer-nav-link" title="Sofa &amp; Upholstery Shampooing in Thakurdwara">Sofa Shampoo &amp; Deep Clean in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/electrician-services-in-thakurdwara') ?>" class="footer-nav-link" title="Electrician Services in Thakurdwara">Electrician &amp; Wiring in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/plumber-services-in-thakurdwara') ?>" class="footer-nav-link" title="Plumber Services in Thakurdwara">Plumber &amp; Pipe Repair in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/painting-services-in-thakurdwara') ?>" class="footer-nav-link" title="House &amp; Wall Painting in Thakurdwara">House &amp; Wall Painting in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/pest-control-services-in-thakurdwara') ?>" class="footer-nav-link" title="Pest &amp; Termite Control in Thakurdwara">Pest &amp; Termite Control in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/fall-ceiling-services-in-thakurdwara') ?>" class="footer-nav-link" title="Fall Ceiling &amp; POP in Thakurdwara">Fall Ceiling &amp; POP in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/carpenter-services-in-thakurdwara') ?>" class="footer-nav-link" title="Carpenter Services in Thakurdwara">Carpenter &amp; Furniture in Thakurdwara</a></li>
              <li><a href="<?= \App\Core\View::url('/appliance-repair-services-in-thakurdwara') ?>" class="footer-nav-link" title="Appliance Repair in Thakurdwara">Washing Machine &amp; Appliance Repair</a></li>
            </ul>
          </div>

          <!-- Popular High-Intent Searches -->
          <div class="footer-dir-col col-6 col-sm-6 col-lg-3 mb-3">
            <h6 style="color: #f8fafc; font-size: 13px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #f25b29;"></span> Popular Searches
            </h6>
            <ul class="list-unstyled p-0 m-0" style="font-size: 12px; line-height: 2;">
              <li><a href="<?= \App\Core\View::url('/ac-jet-service-in-kashipur') ?>" class="footer-nav-link" title="Split AC High-Pressure Jet Wash">Split AC Jet Wash &amp; Filter Clean</a></li>
              <li><a href="<?= \App\Core\View::url('/ac-gas-leakage-refill-in-kashipur') ?>" class="footer-nav-link" title="AC Gas Refill &amp; Leak Repair">AC Gas Leakage &amp; Full Refill</a></li>
              <li><a href="<?= \App\Core\View::url('/professional-full-home-cleaning-in-kashipur') ?>" class="footer-nav-link" title="Full Home Deep Cleaning in Kashipur">Full Home Deep Cleaning (1/2/3 BHK)</a></li>
              <li><a href="<?= \App\Core\View::url('/water-tank-cleaning-in-thakurdwara') ?>" class="footer-nav-link" title="Water Tank Deep Cleaning in Thakurdwara">Water Tank Sludge &amp; UV Disinfection</a></li>
              <li><a href="<?= \App\Core\View::url('/professional-bathroom-cleaning-in-jaspur') ?>" class="footer-nav-link" title="Bathroom Deep Cleaning in Jaspur">Bathroom Calcium Descaling in Jaspur</a></li>
              <li><a href="<?= \App\Core\View::url('/kitchen-deep-cleaning-in-kashipur') ?>" class="footer-nav-link" title="Kitchen Deep Cleaning in Kashipur">Kitchen Chimney &amp; Oil Degreasing</a></li>
              <li><a href="<?= \App\Core\View::url('/sofa-upholstery-deep-cleaning-in-kashipur') ?>" class="footer-nav-link" title="Sofa &amp; Upholstery Deep Cleaning in Kashipur">Sofa Shampoo &amp; Fabric Extraction</a></li>
              <li><a href="<?= \App\Core\View::url('/balcony-deep-pressure-wash-in-jaspur') ?>" class="footer-nav-link" title="Balcony Deep Pressure Wash in Jaspur">Balcony High-Pressure Jet Wash</a></li>
              <li><a href="<?= \App\Core\View::url('/fall-ceiling-installation-in-kashipur') ?>" class="footer-nav-link" title="Designer Fall Ceiling Installation in Kashipur">Designer Fall Ceiling &amp; Cove Lights</a></li>
              <li><a href="<?= \App\Core\View::url('/tap-leak-repair-in-thakurdwara') ?>" class="footer-nav-link" title="Tap &amp; Pipe Leak Repair in Thakurdwara">Emergency Pipe &amp; Tap Leak Repair</a></li>
              <li><a href="<?= \App\Core\View::url('/fan-switchboard-repair-in-kashipur') ?>" class="footer-nav-link" title="Fan &amp; Switchboard Repair in Kashipur">Fan, Switchboard &amp; MCB Repair</a></li>
              <li><a href="<?= \App\Core\View::url('/termite-deemak-control-treatment-in-kashipur') ?>" class="footer-nav-link" title="Termite (Deemak) Treatment in Kashipur">Termite (Deemak) Drill &amp; Fill Treatment</a></li>
              <li><a href="<?= \App\Core\View::url('/washing-machine-repair-service-in-kashipur') ?>" class="footer-nav-link" title="Washing Machine Repair &amp; Service">Automatic Washing Machine Repair</a></li>
            </ul>
          </div>
        </div>

        <!-- SEO Service & Location Keyword Clusters (SEO Ranking & High-Intent Indexing) -->
        <div class="col-12 mt-4 pt-3 border-top" style="border-top: 1px dashed rgba(255,255,255,0.12) !important;">
          <div class="footer-seo-keywords-wrap">
            <h6 style="color: #f8fafc; font-size: 13px; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
              <i class="fa fa-search" style="color: #f25b29; font-size: 12px;"></i> Popular Service &amp; Location Keywords:
            </h6>
            
            <div class="footer-seo-clusters mb-2" style="font-size: 11.5px; line-height: 1.8; color: #94a3b8;">
              <div class="mb-2">
                <strong style="color: #cbd5e1;">AC Service &amp; Repair:</strong>
                <a href="<?= \App\Core\View::url('/ac-services-in-kashipur') ?>" class="footer-seo-tag">AC Service Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-services-in-jaspur') ?>" class="footer-seo-tag">AC Repair in Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-services-in-thakurdwara') ?>" class="footer-seo-tag">AC Service Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-jet-service-in-kashipur') ?>" class="footer-seo-tag">Split AC Jet Cleaning Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-gas-leakage-refill-in-kashipur') ?>" class="footer-seo-tag">AC Gas Filling Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-gas-leakage-refill-in-jaspur') ?>" class="footer-seo-tag">AC Gas Refill Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-services-in-kashipur') ?>" class="footer-seo-tag">Window AC Service Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-services-in-thakurdwara') ?>" class="footer-seo-tag">Inverter AC PCB Repair Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/ac-services') ?>" class="footer-seo-tag">Best AC Technician Near Me</a>
              </div>

              <div class="mb-2">
                <strong style="color: #cbd5e1;">Deep Cleaning &amp; Hygiene:</strong>
                <a href="<?= \App\Core\View::url('/cleaning-services-in-kashipur') ?>" class="footer-seo-tag">Full Home Deep Cleaning Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/cleaning-services-in-jaspur') ?>" class="footer-seo-tag">House Deep Cleaning Services Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/cleaning-services-in-thakurdwara') ?>" class="footer-seo-tag">Flat &amp; Villa Cleaning Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/water-tank-cleaning-in-kashipur') ?>" class="footer-seo-tag">Water Tank Cleaning Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/water-tank-cleaning-in-jaspur') ?>" class="footer-seo-tag">Mechanized Water Tank Disinfection Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/water-tank-cleaning-in-thakurdwara') ?>" class="footer-seo-tag">Overhead Sludge Cleaning Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/sofa-cleaning-in-kashipur') ?>" class="footer-seo-tag">Sofa Dry Cleaning &amp; Shampoo Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/cleaning-services-in-kashipur') ?>" class="footer-seo-tag">Kitchen Chimney &amp; Degreasing Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/cleaning-services-in-jaspur') ?>" class="footer-seo-tag">Bathroom Calcium Descaling Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/cleaning-services') ?>" class="footer-seo-tag">Commercial Office Cleaning</a>
              </div>

              <div class="mb-2">
                <strong style="color: #cbd5e1;">Electrician &amp; Plumbing:</strong>
                <a href="<?= \App\Core\View::url('/electrician-services-in-kashipur') ?>" class="footer-seo-tag">Electrician in Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/electrician-services-in-jaspur') ?>" class="footer-seo-tag">Electrician Near Me Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/electrician-services-in-thakurdwara') ?>" class="footer-seo-tag">Emergency Electrician Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/electrician-services-in-kashipur') ?>" class="footer-seo-tag">Short Circuit &amp; MCB Repair Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/plumber-services-in-kashipur') ?>" class="footer-seo-tag">Plumber in Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/plumber-services-in-jaspur') ?>" class="footer-seo-tag">Plumber Near Me Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/plumber-services-in-thakurdwara') ?>" class="footer-seo-tag">Water Motor &amp; Tap Leak Repair Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/plumber-services-in-kashipur') ?>" class="footer-seo-tag">Drain Blockage Cleaning Kashipur</a>
              </div>

              <div class="mb-2">
                <strong style="color: #cbd5e1;">Painting, Pest Control &amp; Home Renovation:</strong>
                <a href="<?= \App\Core\View::url('/painting-services-in-kashipur') ?>" class="footer-seo-tag">House Painting Services Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/painting-services-in-jaspur') ?>" class="footer-seo-tag">Wall Painting Contractor Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/painting-services-in-thakurdwara') ?>" class="footer-seo-tag">Waterproofing &amp; Dampness Fix Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/pest-control-services-in-kashipur') ?>" class="footer-seo-tag">Termite (Deemak) Treatment Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/pest-control-services-in-jaspur') ?>" class="footer-seo-tag">Pest Control Services in Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/pest-control-services-in-thakurdwara') ?>" class="footer-seo-tag">Cockroach &amp; Bedbug Control Thakurdwara</a> &bull;
                <a href="<?= \App\Core\View::url('/fall-ceiling-services-in-kashipur') ?>" class="footer-seo-tag">False Ceiling &amp; POP Contractor Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/fall-ceiling-services-in-jaspur') ?>" class="footer-seo-tag">Gypsum Ceiling Design Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/carpenter-services-in-kashipur') ?>" class="footer-seo-tag">Carpenter in Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/carpenter-services-in-jaspur') ?>" class="footer-seo-tag">Furniture &amp; Door Lock Repair Jaspur</a> &bull;
                <a href="<?= \App\Core\View::url('/appliance-repair-services-in-kashipur') ?>" class="footer-seo-tag">Washing Machine Repair Kashipur</a> &bull;
                <a href="<?= \App\Core\View::url('/appliance-repair-services-in-jaspur') ?>" class="footer-seo-tag">Refrigerator &amp; Microwave Repair Jaspur</a>
              </div>

              <div>
                <strong style="color: #cbd5e1;">Verified Coverage Localities:</strong>
                <span class="footer-seo-loc">Kashipur (244713)</span> &bull;
                <span class="footer-seo-loc">Jaspur (244712)</span> &bull;
                <span class="footer-seo-loc">Thakurdwara (244601)</span> &bull;
                <span class="footer-seo-loc">Ramnagar Road</span> &bull;
                <span class="footer-seo-loc">Station Road</span> &bull;
                <span class="footer-seo-loc">Moradabad Road</span> &bull;
                <span class="footer-seo-loc">Aliganj Road</span> &bull;
                <span class="footer-seo-loc">Kundeshwari</span> &bull;
                <span class="footer-seo-loc">Bazpur Road</span> &bull;
                <span class="footer-seo-loc">Mahua Kheraganj</span> &bull;
                <span class="footer-seo-loc">Subhash Chowk</span> &bull;
                <span class="footer-seo-loc">Surjan Nagar</span> &bull;
                <span class="footer-seo-loc">Avas Vikas Colony</span>
              </div>
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
        <p class="mb-0">&copy; <?= date('Y') ?> <?= \App\Core\View::e(\App\Core\Theme::getBrandName()) ?>. All Rights Reserved. Founded by <strong>Shanu Kumar, Manish Kumar &amp; Aakash Kumar</strong>. Built / Design <span style="color: #ef4444;">❤️</span> by Vibesoft.</p>
      </div>
    </div>
  </div>
</div>
