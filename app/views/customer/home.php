<?php
/**
 * REFIXEL - Authentic Customer Home View
 * Faithfully matches https://www.REFIXEL.com/index.php
 */
$currentCity = $_SESSION['selected_city'] ?? 'Gurugram';
$currentCitySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $currentCity), '-'));
?>
<style>
.home_banners {
  min-height: 520px;
}
.search_service {
  padding: 85px 0 65px 0;
}
.trust_stats_strip {
  background: #ffffff;
  padding: 30px 0 26px 0;
  position: relative;
  z-index: 5;
}
.trust_stats_strip .complate_serv {
  margin: 0 auto;
  width: 100%;
  max-width: 1040px;
}
.trust_stats_strip .complate_serv ul {
  display: flex;
  justify-content: center;
  align-items: stretch;
  gap: 16px;
}
.trust_stats_strip .complate_serv ul li {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
  border-radius: 14px;
}
.trust_stats_strip .complate_serv ul li:hover {
  border-color: #ffdacf;
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.09);
}
@media (max-width: 767px) {
  .home_banners {
    min-height: auto;
  }
  .search_service {
    padding: 24px 0 28px 0;
  }
  .trust_stats_strip {
    padding: 20px 0;
  }
  .trust_stats_strip .complate_serv {
    display: block !important;
    width: 100% !important;
  }
  .trust_stats_strip .complate_serv ul {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .trust_stats_strip .complate_serv ul li {
    width: 100% !important;
    padding: 12px 8px !important;
    font-size: 19px !important;
  }
  .trust_stats_strip .complate_serv ul li h6 {
    font-size: 11px !important;
  }
}

/* Complete Home Solutions Styles */
.complete_solutions_main {
  padding: 60px 0 55px 0;
  background: #ffffff;
  position: relative;
}
.solutions_header {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 38px auto;
}
.solutions_eyebrow {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 2px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 12px;
}
.solutions_eyebrow .eyebrow_dash {
  display: inline-block;
  width: 22px;
  height: 2px;
  background: #f25b29;
  border-radius: 2px;
}
.solutions_header h2 {
  font-size: 36px;
  font-weight: 800;
  color: #0b1a2d;
  line-height: 1.25;
  margin: 0 0 12px 0;
  letter-spacing: -0.5px;
}
.solutions_header p {
  font-size: 15px;
  line-height: 1.6;
  color: #64748b;
  margin: 0 auto;
  max-width: 600px;
}
.solutions_grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 22px;
  max-width: 1240px;
  margin: 0 auto;
}
a.solution_card,
a.solution_card:hover,
a.solution_card:focus,
a.solution_card:active,
.solution_card,
.solution_card:hover,
.solution_card * {
  text-decoration: none !important;
  outline: none !important;
}
.solution_card {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border: 1px solid #e8edf3;
  border-radius: 18px;
  box-shadow: 0 4px 18px rgba(15, 23, 42, 0.07);
  overflow: hidden;
  text-decoration: none !important;
  color: inherit;
  transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}
.solution_card:hover {
  transform: translateY(-5px);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.11);
  border-color: #ffd0b5;
  text-decoration: none !important;
  color: inherit;
}
.solution_media_wrapper {
  width: 100%;
  height: 172px;
  overflow: hidden;
  background: #f1f5f9;
}
.solution_media_wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  display: block;
  transition: transform 0.4s ease;
}
.solution_card:hover .solution_media_wrapper img {
  transform: scale(1.05);
}
.solution_body {
  padding: 14px 18px 18px 18px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  background: #ffffff;
}
.solution_icon_wrap {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f25b29 0%, #ff7040 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 19px;
  box-shadow: 0 5px 14px rgba(242, 91, 41, 0.38);
  margin-top: -24px;
  margin-bottom: 12px;
  flex-shrink: 0;
  border: 3px solid #ffffff;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.solution_card:hover .solution_icon_wrap {
  transform: scale(1.1);
  box-shadow: 0 7px 18px rgba(242, 91, 41, 0.5);
}
.solution_title {
  font-size: 19px;
  font-weight: 800;
  color: #0b1a2d;
  margin: 0 0 7px 0;
  line-height: 1.3;
  transition: color 0.2s ease;
  position: relative;
  z-index: 2;
}
.solution_card:hover .solution_title {
  color: #f25b29;
}
.solution_desc {
  font-size: 12.5px;
  line-height: 1.55;
  color: #64748b;
  margin: 0 0 16px 0;
  flex-grow: 1;
}
.solution_footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: auto;
}
.solution_arrow {
  color: #f25b29;
  font-size: 20px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.25s ease;
}
.solution_card:hover .solution_arrow {
  transform: translateX(4px);
}
@media (min-width: 992px) and (max-width: 1199px) {
  .solutions_grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }
  .solution_body {
    padding: 8px 18px 18px 18px;
  }
  .solution_title {
    font-size: 18px;
  }
  .solution_desc {
    font-size: 12px;
  }
}
@media (min-width: 768px) and (max-width: 991px) {
  .complete_solutions_main {
    padding: 45px 0 40px 0;
  }
  .solutions_header h2 {
    font-size: 30px;
  }
  .solutions_grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    max-width: 720px;
  }
}
@media (max-width: 767px) {
  .complete_solutions_main {
    padding: 35px 0 30px 0;
  }
  .solutions_header {
    margin-bottom: 25px;
  }
  .solutions_header h2 {
    font-size: 26px;
  }
  .solutions_header p {
    font-size: 13.5px;
    padding: 0 12px;
  }
  .solutions_grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    max-width: 520px;
  }
  .solution_media_wrapper  { height: 130px; }
  .solution_icon_wrap      { width: 40px; height: 40px; font-size: 16px; margin-top: -20px; margin-bottom: 10px; }
  .solution_title          { font-size: 15px; margin-bottom: 4px; }
  .solution_desc           { font-size: 11.5px; line-height: 1.45; margin-bottom: 10px; }
  .solution_body           { padding: 10px 13px 14px 13px; }
}
@media (max-width: 480px) {
  .solutions_grid {
    grid-template-columns: 1fr;
    max-width: 340px;
    gap: 14px;
  }
  .solution_media_wrapper { height: 155px; }
  .solution_icon_wrap     { width: 44px; height: 44px; font-size: 17px; margin-top: -22px; }
}
</style>

<!-- Hero & Search Banner -->
<div class="home_banners">
  <video class="hero_bg_video" id="heroBgVideo" autoplay muted loop playsinline preload="auto">
    <source src="<?= \App\Core\View::asset('hero-vid/Technicians_providing_home_services_1080p_20261005125651.mp4') ?>" type="video/mp4">
    <source src="<?= \App\Core\View::asset('hero-vid/hero-bg.mp4') ?>" type="video/mp4">
  </video>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var v = document.getElementById('heroBgVideo');
      if (v) {
        v.muted = true;
        v.defaultMuted = true;
        v.play().catch(function() {
          window.addEventListener('click', function() { v.play(); }, { once: true });
        });
      }
    });
  </script>
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="search_service">
          <div class="looking_ser">
            <h3>Starting at ₹999 • Save up to 25% on your first booking</h3>
            <h1>Get Your Home Spotless & Germ-Free with Expert Deep Cleaning</h1>
            <a href="<?= \App\Core\View::url('/services') ?>" class="btn_cleaned_today d-inline-block text-decoration-none">
              Get Your Home Cleaned Today
            </a>
          </div>

          <div class="my_autocomplate">
            <!-- Service Search Input & Autocomplete Dropdown -->
            <div class="form-group search-wrapper" style="position:relative;">
              <i class="fa fa-search btn_btn_primary_search"></i>
              <input type="text"
                     class="form-control form_control_input"
                     placeholder="What are you looking for?"
                     id="citySearch_service"
                     autocomplete="off">

              <div id="searchDropdown" class="search-dropdown" style="display:none;">
                <div id="trendingBox">
                  <h6>Trending searches</h6>
                  <ul>
                    <li data-service="cleaning"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Professional Full Home cleaning</li>
                    <li data-service="cleaning"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Professional Bathroom Cleaning</li>
                    <li data-service="cleaning"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Kitchen Deep Cleaning</li>
                    <li data-service="cleaning"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Sofa Deep Cleaning</li>
                    <li data-service="cleaning"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Commercial Space Cleaning</li>
                    <li data-service="pest-control"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Pest Control</li>
                    <li data-service="painting-services"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> Home Painting</li>
                    <li data-service="ac-services"><img src="<?= \App\Core\View::asset('img/traning.webp') ?>" alt="Trending"> AC Jet Service</li>
                  </ul>
                </div>
                <div id="ajaxResults" style="display:none;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Trust Stats Section (Moved below Hero Banner) -->
<div class="trust_stats_strip">
  <div class="container">
    <div class="complate_serv">
      <ul>
        <li>4.8★ <h6>Rated by 1000+ Happy Customers</h6></li>
        <li>5000+ <h6>Homes Professionally <br>Cleaned</h6></li>
        <li>60+ <h6>Trusted Service <br>Partners</h6></li>
        <li>400+ <h6>Verified <br>Professionals</h6></li>
      </ul>
    </div>
  </div>
</div>

<!-- Services Marquee Ticker Strip (Shifted below Trust Stats) -->
<div class="services_marquee_strip" aria-label="Available Services">
  <div class="services_marquee_track">
    <div class="services_marquee_group">
      <a href="<?= \App\Core\View::url("/cleaning-services-in-{$currentCitySlug}") ?>" class="sm-link">Full Home Deep Cleaning</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/ac-services-in-{$currentCitySlug}") ?>" class="sm-link">AC Service & Repair</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/plumber-services-in-{$currentCitySlug}") ?>" class="sm-link">Plumbing & Leak Repair</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/fall-ceiling-services-in-{$currentCitySlug}") ?>" class="sm-link">Fall Ceiling & POP Design</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/carpenter-services-in-{$currentCitySlug}") ?>" class="sm-link">Carpentry & Woodwork</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/painting-services-in-{$currentCitySlug}") ?>" class="sm-link">Interior & Exterior Painting</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/electrician-services-in-{$currentCitySlug}") ?>" class="sm-link">Electrical Wiring & MCB</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/appliance-repair-services-in-{$currentCitySlug}") ?>" class="sm-link">Appliance & Washing Machine Repair</a>
      <span class="sm-dot">✦</span>
    </div><div class="services_marquee_group" aria-hidden="true">
      <a href="<?= \App\Core\View::url("/cleaning-services-in-{$currentCitySlug}") ?>" class="sm-link">Full Home Deep Cleaning</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/ac-services-in-{$currentCitySlug}") ?>" class="sm-link">AC Service & Repair</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/plumber-services-in-{$currentCitySlug}") ?>" class="sm-link">Plumbing & Leak Repair</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/fall-ceiling-services-in-{$currentCitySlug}") ?>" class="sm-link">Fall Ceiling & POP Design</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/carpenter-services-in-{$currentCitySlug}") ?>" class="sm-link">Carpentry & Woodwork</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/painting-services-in-{$currentCitySlug}") ?>" class="sm-link">Interior & Exterior Painting</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/electrician-services-in-{$currentCitySlug}") ?>" class="sm-link">Electrical Wiring & MCB</a>
      <span class="sm-dot">✦</span>
      <a href="<?= \App\Core\View::url("/appliance-repair-services-in-{$currentCitySlug}") ?>" class="sm-link">Appliance & Washing Machine Repair</a>
      <span class="sm-dot">✦</span>
    </div>
  </div>
</div>


<!-- Complete Home Solutions Section -->
<?php
$solutionCards = [
  [
    'title' => 'Cleaning',
    'desc'  => 'Home, Office, Deep Cleaning Sofa, Carpet, Kitchen, Bathroom',
    'image' => 'refixel-cleaning.jpg',
    'icon'  => 'fa fa-paint-brush',
    'url'   => \App\Core\View::url("/cleaning-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'Electrician',
    'desc'  => 'Wiring, Switch, Fan, Light, Inverter, LED, Electrical Repairs',
    'image' => 'refixel-electrician.jpg',
    'icon'  => 'fa fa-bolt',
    'url'   => \App\Core\View::url("/electrician-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'Appliance Repair',
    'desc'  => 'TV, Fridge, Washing Machine, Microwave, Mixer & Home Appliances',
    'image' => 'refixel-appliance-repair.jpg',
    'icon'  => 'fa fa-cog',
    'url'   => \App\Core\View::url("/appliance-repair-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'Plumber',
    'desc'  => 'Leak Repair, Pipe Fitting, Taps, Flush, Drainage Bathroom & Kitchen Plumbing',
    'image' => 'refixel-plumber.jpg',
    'icon'  => 'fa fa-wrench',
    'url'   => \App\Core\View::url("/plumber-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'AC Service & Repair',
    'desc'  => 'AC Installation, Uninstallation, Deep Jet Cleaning & Gas Refilling',
    'image' => 'refixel-ac-service.jpg',
    'icon'  => 'fa fa-snowflake-o',
    'url'   => \App\Core\View::url("/ac-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'Painting Services',
    'desc'  => 'Interior & Exterior Wall Painting, Waterproofing, Texture & Stencil',
    'image' => 'refixel-painting.jpg',
    'icon'  => 'fa fa-paint-brush',
    'url'   => \App\Core\View::url("/painting-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'Carpenter',
    'desc'  => 'Furniture Repair, Door Lock, Hinges, Modular Kitchen & Woodwork',
    'image' => 'refixel-carpenter.jpg',
    'icon'  => 'fa fa-gavel',
    'url'   => \App\Core\View::url("/carpenter-services-in-{$currentCitySlug}"),
  ],
  [
    'title' => 'Fall Ceiling',
    'desc'  => 'POP Ceiling, Gypsum Board, Cove Lighting & False Ceiling Design',
    'image' => 'refixel-fall-ceiling.jpg',
    'icon'  => 'fa fa-th-large',
    'url'   => \App\Core\View::url("/fall-ceiling-services-in-{$currentCitySlug}"),
  ],
];
?>
<section class="complete_solutions_main" id="completeHomeSolutions">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="solutions_header">
          <div class="solutions_eyebrow">
            <span class="eyebrow_dash"></span>
            <span>OUR SERVICES</span>
            <span class="eyebrow_dash"></span>
          </div>
          <h2>Complete Home Solutions</h2>
          <p>From cleaning to repairs, we provide end-to-end home services with skilled professionals.</p>
        </div>
      </div>
    </div>

    <div class="solutions_grid">
      <?php foreach ($solutionCards as $card): ?>
        <a href="<?= $card['url'] ?>" class="solution_card" title="<?= \App\Core\View::e($card['title']) ?>">
            <div class="solution_media_wrapper">
            <img src="<?= \App\Core\View::asset('img/' . $card['image']) ?>"
                 alt="<?= \App\Core\View::e($card['title']) ?>"
                 width="280" height="172" loading="lazy"
                 onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/refixel-cleaning.jpg') ?>';">
          </div>
          <div class="solution_body">
            <div class="solution_icon_wrap">
              <i class="<?= $card['icon'] ?>" aria-hidden="true"></i>
            </div>
            <h3 class="solution_title"><?= \App\Core\View::e($card['title']) ?></h3>
            <p class="solution_desc"><?= \App\Core\View::e($card['desc']) ?></p>
            <div class="solution_footer">
              <span class="solution_arrow" aria-hidden="true">
                <i class="fa fa-angle-right"></i>
              </span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- City / Service State -->
<div class="ourservice_main">
  <div class="container">
    <div class="row">
      <div class="col-lg-12" style="text-align:center; padding:30px 15px;">
        <h4 style="color:#f25b29; font-weight:700;">Services - Available in your location</h4>
        <p style="margin:0; color:#555;">Verified professionals available for instant and scheduled booking - Available in your location.</p>
      </div>
    </div>
  </div>
</div>

<!-- Why Choose Us Section -->
<div class="whychoose_main">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <div class="page_heading" style="text-align: left;">
          <h6>WHY CHOOSE US</h6>
          <h3>Why Our Customer Choose Us</h3>
          <p>With REFIXEL.com you will find the best Professionals in the area, whatever your need.</p>
        </div>

        <div class="whyus_item">
          <div class="whyus_item_img">
            <img src="<?= \App\Core\View::asset('img/trust.webp') ?>" alt="Verified" width="60" height="60" loading="lazy" decoding="async">
            <div class="bottm_arrow"></div>
          </div>
          <div class="whyus_content">
            <h4>Verified & vetted professionals</h4>
            <p>Get service from trusted and verified partner with professional skills and experience.</p>
          </div>
        </div>

        <div class="whyus_item">
          <div class="whyus_item_img">
            <img src="<?= \App\Core\View::asset('img/Services_Single.webp') ?>" alt="Matched to your needs" width="60" height="60" loading="lazy" decoding="async">
            <div class="bottm_arrow"></div>
          </div>
          <div class="whyus_content">
            <h4>Matched to your needs.</h4>
            <p>Avail tailored, service-specific options according to your precise needs and preferences.</p>
          </div>
        </div>

        <div class="whyus_item">
          <div class="whyus_item_img">
            <img src="<?= \App\Core\View::asset('img/repair.webp') ?>" alt="Customer support" width="60" height="60" loading="lazy" decoding="async">
          </div>
          <div class="whyus_content">
            <h4>Customer support at every step.</h4>
            <p>Ensuring smooth connections and support at every step for our users.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="choose_right_bar">
          <img src="<?= \App\Core\View::asset('img/find-expert.webp') ?>" alt="Happy Customers" loading="lazy" decoding="async">
          <div class="happy_clients">
            <ul>
              <li>400+ <h6>Verified Professionals</h6></li>
              <li>5000+ <h6>Homes Professionally Cleaned</h6></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Frequently Asked Questions (Dynamic from Database) -->
<div class="homepage_faq">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="page_heading text-center">
          <h6>Get answers to common questions</h6>
          <h3>Frequently Asked Questions</h3>
        </div>

        <div class="faq-container">
          <?php if (!empty($faqs)): ?>
            <?php foreach ($faqs as $idx => $faq): ?>
              <div class="faq-item <?= $idx === 0 ? 'active' : '' ?>">
                <div class="faq-question">
                  <span><?= \App\Core\View::e($faq['question']) ?></span>
                  <div class="faq-icon"><?= $idx === 0 ? '−' : '+' ?></div>
                </div>
                <div class="faq-answer" style="<?= $idx === 0 ? 'display:block;' : 'display:none;' ?>">
                  <?= nl2br(\App\Core\View::e($faq['answer'])) ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="faq-item active">
              <div class="faq-question">
                <span>What is included in a full home deep cleaning service?</span>
                <div class="faq-icon">−</div>
              </div>
              <div class="faq-answer" style="display:block;">
                Our full home deep cleaning covers bedrooms, bathrooms, kitchen, living areas, floors, furniture, appliances, windows (inside), dusting, degreasing, sanitization, and more.
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Partner Program Banner -->
<div class="container my-5">
  <section class="partner-banner">
    <div class="bg-pattern pattern-1"></div>
    <div class="bg-pattern pattern-2"></div>

    <div class="banner-content">
      <div class="partner-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
        </svg>
        Partner Program
      </div>
      <h2 class="banner-title">Grow your business with REFIXEL</h2>
      <p class="banner-subtitle">
        India's fast-growing Home & Professional Services platform — connecting skilled professionals with thousands of customers across multiple cities.
      </p>
      <a href="<?= \App\Core\View::url('/contact') ?>" class="cta-button">
        Become a Service Partner
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M1 8H15M15 8L8 1M15 8L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
    </div>

    <div class="banner-visual">
      <div class="partners-container">
        <div class="partner-char">
          <img src="<?= \App\Core\View::asset('img/service_partners_p.png') ?>" alt="Home Cleaning Partner" loading="lazy" decoding="async">
          <div class="service-tag tag-left">
            <div class="icon-box">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            </div>
            <div class="tag-info">
              <span class="tag-title">Home Cleaning</span>
              <span class="tag-desc">Service Provider</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Mini Floating Cart Bar -->
<div class="homecart_items" id="homeCartBar">
  <h4>
    <span><i class="fa fa-shopping-cart"></i> <span id="cartItemCount">0</span> item</span>
    <span id="cartTotalPrice">₹0</span>
  </h4>
  <a href="<?= \App\Core\View::url('/cart') ?>">
    View Cart
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2307 5.53999C12.9769 5.28615 12.5653 5.28615 12.3115 5.53999C12.0576 5.79383 12.0576 6.20539 12.3115 6.45923L17.2019 11.3496L5.39414 11.3496C5.03516 11.3496 4.74414 11.6406 4.74414 11.9996C4.74414 12.3586 5.03516 12.6496 5.39414 12.6496L17.2019 12.6496L12.3115 17.54C12.0576 17.7938 12.0576 18.2054 12.3115 18.4592C12.5653 18.7131 12.9769 18.7131 13.2307 18.4592L18.949 12.741C19.3584 12.3315 19.3584 11.6677 18.949 11.2583L13.2307 5.53999Z" fill="#f25b29"/>
    </svg>
  </a>
</div>

<!-- Home-Specific Interactive Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  var selectedCity = '<?= addslashes($currentCity) ?>';
  var selectedCitySlug = '<?= addslashes($currentCitySlug) ?>';
  var baseUrl = '<?= rtrim(\App\Core\View::url(), '/') ?>';

  // 1. Animated Typing Search Placeholder
  var services = ["AC Service & Repair", "Carpenter", "Cleaning", "Painting Services", "Pest Control", "Plumbers"];
  var sIdx = 0, charIdx = 0, isDeleting = false;
  var searchInput = document.getElementById("citySearch_service");
  var baseText = "Search services ";

  function typeEffect() {
    if (!searchInput || services.length === 0) return;
    var current = services[sIdx];
    if (!isDeleting) {
      charIdx++;
      searchInput.setAttribute("placeholder", baseText + "‘" + current.substring(0, charIdx) + "’");
      if (charIdx >= current.length) {
        setTimeout(function() { isDeleting = true; }, 1200);
      }
    } else {
      charIdx--;
      searchInput.setAttribute("placeholder", baseText + "‘" + current.substring(0, charIdx) + "’");
      if (charIdx <= 0) {
        isDeleting = false;
        sIdx = (sIdx + 1) % services.length;
      }
    }
  }
  setInterval(typeEffect, 60);

  // 2. Search dropdown & Trending
  var searchDropdown = document.getElementById("searchDropdown");
  var trendingBox = document.getElementById("trendingBox");
  var ajaxResults = document.getElementById("ajaxResults");

  if (searchInput) {
    searchInput.addEventListener("focus", function() {
      if (searchDropdown) searchDropdown.style.display = "block";
      if (trendingBox) trendingBox.style.display = "block";
      if (ajaxResults) ajaxResults.style.display = "none";
    });

    searchInput.addEventListener("input", function() {
      var q = this.value.trim().toLowerCase();
      if (q.length >= 2) {
        if (trendingBox) trendingBox.style.display = "none";
        if (ajaxResults) {
          ajaxResults.style.display = "block";
          ajaxResults.innerHTML = '<div class="p-2 text-muted small">Searching...</div>';
          fetch(baseUrl + '/api/services?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
              if (data && data.services && data.services.length) {
                var html = '<ul class="list-unstyled mb-0">';
                data.services.forEach(function(s) {
                  html += '<li class="p-2 border-bottom suggestion-item" style="cursor:pointer;" data-slug="' + s.slug + '">' +
                          '<strong>' + s.name + '</strong> <span class="badge badge-success float-right">₹' + s.starting_price + '</span></li>';
                });
                html += '</ul>';
                ajaxResults.innerHTML = html;
              } else {
                ajaxResults.innerHTML = '<div class="p-2 text-muted small">No direct match. Press enter or browse categories below.</div>';
              }
            })
            .catch(function() {
              ajaxResults.innerHTML = '<div class="p-2 text-muted small">Search service active.</div>';
            });
        }
      } else {
        if (ajaxResults) ajaxResults.style.display = "none";
        if (trendingBox) trendingBox.style.display = "block";
      }
    });
  }

  // Click outside closes dropdown
  document.addEventListener("click", function(e) {
    if (!e.target.closest(".search-wrapper")) {
      if (searchDropdown) searchDropdown.style.display = "none";
    }
  });

  // Trending & Suggestion clicks
  document.addEventListener("click", function(e) {
    var trendLi = e.target.closest("#trendingBox li");
    if (trendLi) {
      var sSlug = trendLi.getAttribute("data-service") || 'cleaning';
      window.location.href = baseUrl + '/' + sSlug + '-services-in-' + selectedCitySlug;
    }
    var suggLi = e.target.closest(".suggestion-item");
    if (suggLi) {
      var sSlug2 = suggLi.getAttribute("data-slug") || 'cleaning';
      window.location.href = baseUrl + '/' + sSlug2 + '-in-' + selectedCitySlug;
    }
  });

  // 3. FAQ Accordion Toggle
  document.querySelectorAll(".faq-item").forEach(function(item) {
    var q = item.querySelector(".faq-question");
    if (q) {
      q.addEventListener("click", function() {
        var isOpen = item.classList.contains("active");
        document.querySelectorAll(".faq-item").forEach(function(other) {
          other.classList.remove("active");
          var ans = other.querySelector(".faq-answer");
          var ico = other.querySelector(".faq-icon");
          if (ans) ans.style.display = "none";
          if (ico) ico.textContent = "+";
        });
        if (!isOpen) {
          item.classList.add("active");
          var ans = item.querySelector(".faq-answer");
          var ico = item.querySelector(".faq-icon");
          if (ans) ans.style.display = "block";
          if (ico) ico.textContent = "−";
        }
      });
    }
  });
});
</script>

