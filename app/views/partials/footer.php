<style>
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
</style>
<div id="footer" class="footer_main" style="background: #0a1c33; color: #cbd5e1; padding: 24px 0 30px 0; position: relative; z-index: 10;">
  <div class="container">
    <div class="row">
      <!-- Col 1: Brand & Social -->
      <div class="col-lg-3 col-md-6 mb-4">
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
      <div class="col-lg-2 col-md-6 mb-4">
        <div class="footer_links">
          <h4 style="font-size: 16px; font-weight: 700; color: #ffffff; margin-bottom: 18px;">Quick Links</h4>
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
      <div class="col-lg-3 col-md-6 mb-4">
        <div class="footer_links">
          <h4 style="font-size: 16px; font-weight: 700; color: #ffffff; margin-bottom: 18px;">Our Services</h4>
          <div class="row">
            <div class="col-6">
              <ul class="list-unstyled p-0" style="font-size: 13.5px; line-height: 2.2;">
                <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">AC Repair</a></li>
                <li><a href="<?= \App\Core\View::url('/electrician-services-in-kashipur') ?>" class="footer-nav-link">Electrician</a></li>
                <li><a href="<?= \App\Core\View::url('/plumber-services-in-kashipur') ?>" class="footer-nav-link">Plumbing</a></li>
                <li><a href="<?= \App\Core\View::url('/cleaning-services-in-kashipur') ?>" class="footer-nav-link">Cleaning</a></li>
                <li><a href="<?= \App\Core\View::url('/services') ?>" class="footer-nav-link">Laundry</a></li>
              </ul>
            </div>
            <div class="col-6">
              <ul class="list-unstyled p-0" style="font-size: 13.5px; line-height: 2.2;">
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

      <!-- Col 4: Contact Us -->
      <div class="col-lg-2 col-md-6 mb-4">
        <div class="footer_links">
          <h4 style="font-size: 16px; font-weight: 700; color: #ffffff; margin-bottom: 18px;">Contact Us</h4>
          <ul class="list-unstyled p-0" style="font-size: 13.5px; line-height: 1.8;">
            <li class="mb-3 d-flex" style="gap: 10px;">
              <i class="fa fa-map-marker text-refixel-orange mt-1"></i>
              <span style="color: #cbd5e1;">Rajpur (sugar mill nadehi) udham Singh Nagar Uttarakhand</span>
            </li>
            <li class="mb-3 d-flex" style="gap: 10px;">
              <i class="fa fa-envelope text-refixel-orange mt-1"></i>
              <a href="mailto:heyimaakashsaini@gmail.com" class="footer-nav-link" style="color: #cbd5e1; text-decoration: none; word-break: break-all;">heyimaakashsaini@gmail.com</a>
            </li>
            <li class="d-flex" style="gap: 10px;">
              <i class="fa fa-phone text-refixel-orange mt-1"></i>
              <a href="tel:+918791154730" class="footer-nav-link" style="color: #cbd5e1; text-decoration: none;">+91 87911 54730</a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Col 5: Working Hours -->
      <div class="col-lg-2 col-md-6 mb-4">
        <div class="footer_links">
          <h4 style="font-size: 16px; font-weight: 700; color: #ffffff; margin-bottom: 18px;">Working Hours</h4>
          <div class="d-flex" style="gap: 10px; font-size: 13.5px; color: #cbd5e1;">
            <i class="fa fa-clock-o text-refixel-orange mt-1"></i>
            <div>
              <div style="font-weight: 600; color: #ffffff;">Mon - Sun:</div>
              <div style="color: #cbd5e1;">10:00 AM - 8:00 PM</div>
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
