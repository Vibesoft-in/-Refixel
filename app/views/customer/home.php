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
  position: relative;
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
  padding: 18px 18px 18px 18px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  background: #ffffff;
}
.solution_icon_wrap {
  position: absolute;
  top: 14px;
  left: 14px;
  z-index: 4;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f25b29 0%, #ff7040 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
  margin: 0;
  border: 2.5px solid #ffffff;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.solution_card:hover .solution_icon_wrap {
  transform: scale(1.1);
  box-shadow: 0 6px 18px rgba(242, 91, 41, 0.45);
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
  .solution_icon_wrap      { width: 36px; height: 36px; font-size: 14px; top: 10px; left: 10px; margin: 0; }
  .solution_title          { font-size: 15px; margin-bottom: 4px; }
  .solution_desc           { font-size: 11.5px; line-height: 1.45; margin-bottom: 10px; }
  .solution_body           { padding: 12px 13px 14px 13px; }
}
@media (max-width: 480px) {
  .solutions_grid {
    grid-template-columns: 1fr;
    max-width: 340px;
    gap: 14px;
  }
  .solution_media_wrapper { height: 155px; }
  .solution_icon_wrap     { width: 40px; height: 40px; font-size: 16px; top: 12px; left: 12px; margin: 0; }
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
            <div class="solution_icon_wrap">
              <i class="<?= $card['icon'] ?>" aria-hidden="true"></i>
            </div>
          </div>
          <div class="solution_body">
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


<!-- Modern About REFIXEL Section -->
<style>
.home_about_section {
  padding: 80px 0 85px 0;
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
  position: relative;
  border-top: 1px solid #edf2f7;
  border-bottom: 1px solid #edf2f7;
}
.home_about_eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 12px;
}
.home_about_eyebrow .eyebrow_line {
  display: inline-block;
  width: 26px;
  height: 3px;
  background: #f25b29;
  border-radius: 2px;
}
.home_about_title {
  font-size: 36px;
  font-weight: 800;
  color: #0a1c33;
  line-height: 1.25;
  margin: 0 0 16px 0;
  letter-spacing: -0.5px;
}
.home_about_title .text_highlight {
  color: #f25b29;
}
.home_about_desc {
  font-size: 15.5px;
  line-height: 1.7;
  color: #475569;
  margin: 0 0 28px 0;
  max-width: 530px;
}
.home_about_features {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-bottom: 32px;
}
.home_about_feature_item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  transition: transform 0.25s ease;
}
.home_about_feature_item:hover {
  transform: translateX(4px);
}
.home_about_feature_item .feature_icon_box {
  width: 48px;
  height: 48px;
  min-width: 48px;
  border-radius: 12px;
  background: #fff5f0;
  color: #f25b29;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  box-shadow: 0 4px 12px rgba(242, 91, 41, 0.12);
  transition: all 0.3s ease;
}
.home_about_feature_item:hover .feature_icon_box {
  background: #f25b29;
  color: #ffffff;
  transform: scale(1.06);
}
.home_about_feature_item .feature_text h5 {
  font-size: 16px;
  font-weight: 700;
  color: #0a1c33;
  margin: 0 0 4px 0;
}
.home_about_feature_item .feature_text p {
  font-size: 13.5px;
  color: #64748b;
  line-height: 1.5;
  margin: 0;
}
.home_about_actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 14px;
}
.btn_home_about_primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 13px 26px;
  background: #f25b29;
  color: #ffffff !important;
  font-weight: 700;
  font-size: 14.5px;
  border-radius: 10px;
  text-decoration: none !important;
  box-shadow: 0 8px 20px rgba(242, 91, 41, 0.28);
  transition: all 0.3s ease;
}
.btn_home_about_primary:hover {
  background: #d94b1c;
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(242, 91, 41, 0.36);
  color: #ffffff !important;
}
.btn_home_about_secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #ffffff;
  color: #0a1c33 !important;
  font-weight: 600;
  font-size: 14.5px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  text-decoration: none !important;
  transition: all 0.3s ease;
}
.btn_home_about_secondary:hover {
  border-color: #f25b29;
  color: #f25b29 !important;
  transform: translateY(-2px);
  background: #fff8f5;
}
.home_about_visual_wrapper {
  position: relative;
  padding: 15px 15px 42px 15px;
  max-width: 530px;
  margin: 0 auto;
}
.home_about_img_frame {
  border-radius: 22px;
  overflow: hidden;
  box-shadow: 0 20px 45px -10px rgba(10, 28, 51, 0.18);
  border: 4px solid #ffffff;
  background: #0a1c33;
  position: relative;
}
.home_about_main_img {
  width: 100%;
  height: 380px;
  object-fit: cover;
  display: block;
  transition: transform 0.6s ease;
}
.home_about_img_frame:hover .home_about_main_img {
  transform: scale(1.03);
}
.home_about_floating_badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #ffffff;
  padding: 10px 18px;
  border-radius: 14px;
  box-shadow: 0 12px 28px rgba(10, 28, 51, 0.15);
  border: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  z-index: 3;
}
.home_about_floating_badge .badge_star_rating {
  display: flex;
  align-items: center;
  gap: 3px;
  color: #f59e0b;
  font-size: 13px;
}
.home_about_floating_badge .rating_score {
  font-weight: 800;
  color: #0a1c33;
  margin-left: 5px;
  font-size: 13.5px;
}
.home_about_floating_badge .badge_subtext {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.home_about_stats_card {
  position: absolute;
  bottom: 12px;
  left: 6%;
  right: 6%;
  background: rgba(10, 28, 51, 0.94);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 16px 20px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  box-shadow: 0 18px 36px rgba(10, 28, 51, 0.28);
  border: 1px solid rgba(255, 255, 255, 0.14);
  z-index: 3;
}
.home_about_stats_card .about_stat_box {
  text-align: center;
  flex: 1;
}
.home_about_stats_card .stat_num {
  font-size: 22px;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.1;
  margin-bottom: 2px;
}
.home_about_stats_card .stat_txt {
  font-size: 11.5px;
  font-weight: 500;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.home_about_stats_card .stat_divider {
  width: 1px;
  height: 32px;
  background: rgba(255, 255, 255, 0.15);
}
@media (max-width: 991px) {
  .home_about_section {
    padding: 50px 0 55px 0;
  }
  .home_about_title {
    font-size: 28px;
  }
  .home_about_visual_wrapper {
    margin-top: 35px;
    padding: 10px 0 35px 0;
    max-width: 100%;
  }
  .home_about_main_img {
    height: 300px;
  }
  .home_about_floating_badge {
    top: -6px;
    right: 8px;
    padding: 8px 14px;
  }
  .home_about_stats_card {
    bottom: 2px;
    left: 2%;
    right: 2%;
    padding: 12px 8px;
  }
  .home_about_stats_card .stat_num {
    font-size: 18px;
  }
  .home_about_stats_card .stat_txt {
    font-size: 10px;
  }
}
@media (max-width: 480px) {
  .home_about_actions {
    flex-direction: column;
    width: 100%;
  }
  .btn_home_about_primary,
  .btn_home_about_secondary {
    width: 100%;
    justify-content: center;
  }
}
</style>

<section class="home_about_section" id="aboutRefixel">
  <div class="container">
    <div class="row align-items-center">
      <!-- Left Column: Story, Features & CTA -->
      <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
        <div class="home_about_content">
          <div class="home_about_eyebrow">
            <span class="eyebrow_line"></span>
            <span>ABOUT REFIXEL</span>
          </div>
          <h2 class="home_about_title">
            More Than a Service.<br>
            <span class="text_highlight">A Trusted Home Partner.</span>
          </h2>
          <p class="home_about_desc">
            REFIXEL was founded with a singular mission — to make home care, maintenance, and repair services completely transparent, reliable, and hassle-free. We bridge homeowners directly with verified professionals who take genuine pride in every job.
          </p>

          <!-- 3 Key Value Feature Cards -->
          <div class="home_about_features">
            <div class="home_about_feature_item">
              <div class="feature_icon_box">
                <i class="fa fa-shield"></i>
              </div>
              <div class="feature_text">
                <h5>Verified & Background-Checked Pros</h5>
                <p>Every specialist is trade-tested, background-vetted, and held to strict quality benchmarks.</p>
              </div>
            </div>

            <div class="home_about_feature_item">
              <div class="feature_icon_box">
                <i class="fa fa-tag"></i>
              </div>
              <div class="feature_text">
                <h5>Transparent, Upfront Pricing</h5>
                <p>Clear, fixed rates with zero hidden charges or surprises before any work begins.</p>
              </div>
            </div>

            <div class="home_about_feature_item">
              <div class="feature_icon_box">
                <i class="fa fa-handshake-o"></i>
              </div>
              <div class="feature_text">
                <h5>Dedicated Support & Warranty</h5>
                <p>Enjoy end-to-end assistance and our satisfaction guarantee on every completed booking.</p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="home_about_actions">
            <a href="<?= \App\Core\View::url('/about') ?>" class="btn_home_about_primary">
              Learn More About Us <i class="fa fa-arrow-right"></i>
            </a>
            <a href="<?= \App\Core\View::url('/services') ?>" class="btn_home_about_secondary">
              <i class="fa fa-th-large"></i> Explore Services
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column: Visual Showcase & Trust Badges -->
      <div class="col-lg-6 col-md-12">
        <div class="home_about_visual_wrapper">
          <!-- Main Showcase Image: Genuine Storefront Hub -->
          <div class="home_about_img_frame">
            <img 
              src="<?= \App\Core\View::asset('img/refixel-storefront.jpg') ?>" 
              alt="REFIXEL Physical Storefront & Service Hub" 
              class="img-fluid home_about_main_img"
              loading="lazy"
              decoding="async"
            >
          </div>

          <!-- Floating Rating Badge (Top Right) -->
          <div class="home_about_floating_badge">
            <div class="badge_star_rating">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <span class="rating_score">4.9 / 5</span>
            </div>
            <div class="badge_subtext">Customer Satisfaction</div>
          </div>

          <!-- Bottom Floating Stats Bar -->
          <div class="home_about_stats_card">
            <div class="about_stat_box">
              <div class="stat_num"><?= \App\Core\View::e(\App\Models\Setting::get('stat_homes_cleaned', '5,000+')) ?></div>
              <div class="stat_txt"><?= \App\Core\View::e(\App\Models\Setting::get('stat_homes_note', 'Homes Served')) ?></div>
            </div>
            <div class="stat_divider"></div>
            <div class="about_stat_box">
              <div class="stat_num"><?= \App\Core\View::e(\App\Models\Setting::get('stat_verified_pros', '400+')) ?></div>
              <div class="stat_txt"><?= \App\Core\View::e(\App\Models\Setting::get('stat_pros_note', 'Verified Pros')) ?></div>
            </div>
            <div class="stat_divider"></div>
            <div class="about_stat_box">
              <div class="stat_num"><?= \App\Core\View::e(\App\Models\Setting::get('stat_service_partners', '60+')) ?></div>
              <div class="stat_txt"><?= \App\Core\View::e(\App\Models\Setting::get('stat_partners_note', 'Partners')) ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Why Choose Us Section -->
<style>
.home_why_choose_section {
  padding: 60px 0 65px 0;
  background: radial-gradient(circle at 50% 0%, #112a4d 0%, #0a1c33 65%, #071526 100%);
  position: relative;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: #ffffff;
}
.home_why_choose_section .container {
  width: 70% !important;
  max-width: 70vw !important;
  margin: 0 auto !important;
  padding-left: 15px;
  padding-right: 15px;
}
.home_why_header {
  text-align: center;
  max-width: 620px;
  margin: 0 auto 36px auto;
}
.home_why_eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 10px;
}
.home_why_eyebrow .eyebrow_dash {
  display: inline-block;
  width: 20px;
  height: 2px;
  background: #f25b29;
  border-radius: 2px;
}
.home_why_title {
  font-size: 30px;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.25;
  margin: 0 0 10px 0;
  letter-spacing: -0.5px;
}
.home_why_desc {
  font-size: 14.5px;
  line-height: 1.6;
  color: #94a3b8;
  margin: 0 auto;
  max-width: 540px;
}
.home_why_grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
}
.home_why_card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 24px 18px 20px 18px;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}
.home_why_card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #f25b29 0%, #ff8c66 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}
.home_why_card:hover {
  transform: translateY(-6px);
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(242, 91, 41, 0.6);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35), 0 0 20px rgba(242, 91, 41, 0.15);
}
.home_why_card:hover::before {
  opacity: 1;
}
.home_why_icon_wrap {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  background: rgba(242, 91, 41, 0.14);
  border: 1px solid rgba(242, 91, 41, 0.28);
  color: #f25b29;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  margin-bottom: 16px;
  transition: all 0.3s ease;
}
.home_why_card:hover .home_why_icon_wrap {
  background: #f25b29;
  border-color: #f25b29;
  color: #ffffff;
  transform: scale(1.08) rotate(4deg);
  box-shadow: 0 6px 20px rgba(242, 91, 41, 0.45);
}
.home_why_card_title {
  font-size: 16.5px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 8px 0;
  line-height: 1.3;
}
.home_why_card_text {
  font-size: 13px;
  color: #cbd5e1;
  line-height: 1.55;
  margin: 0;
  flex-grow: 1;
}
.home_why_trust_bar {
  margin-top: 34px;
  padding: 14px 20px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
}
.trust_bar_item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
}
.trust_bar_item i {
  color: #34d399;
  font-size: 15px;
}
@media (max-width: 1200px) {
  .home_why_choose_section .container {
    width: 82% !important;
    max-width: 82vw !important;
  }
}
@media (max-width: 991px) {
  .home_why_choose_section {
    padding: 48px 0 52px 0;
  }
  .home_why_choose_section .container {
    width: 90% !important;
    max-width: 90vw !important;
  }
  .home_why_title {
    font-size: 26px;
  }
  .home_why_grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }
  .home_why_header {
    margin-bottom: 28px;
  }
}
@media (max-width: 576px) {
  .home_why_choose_section .container {
    width: 94% !important;
    max-width: 94vw !important;
  }
  .home_why_grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .home_why_card {
    padding: 20px 16px;
  }
  .home_why_trust_bar {
    flex-direction: column;
    align-items: flex-start;
    padding: 14px 16px;
  }
}
</style>

<section class="home_why_choose_section" id="whyChooseUs">
  <div class="container">
    <div class="home_why_header">
      <div class="home_why_eyebrow">
        <span class="eyebrow_dash"></span>
        <span>WHY CHOOSE REFIXEL</span>
        <span class="eyebrow_dash"></span>
      </div>
      <h2 class="home_why_title">Why Our Customers Choose Us</h2>
      <p class="home_why_desc">
        We make home maintenance stress-free with verified professionals, upfront transparent pricing, and our 100% satisfaction guarantee.
      </p>
    </div>

    <div class="home_why_grid">
      <!-- 1. Verified Professionals -->
      <div class="home_why_card">
        <div class="home_why_icon_wrap">
          <i class="fa fa-shield"></i>
        </div>
        <h4 class="home_why_card_title">Verified Professionals</h4>
        <p class="home_why_card_text">
          Every service partner is verified with comprehensive background checks and professional skill certifications.
        </p>
      </div>

      <!-- 2. Transparent Pricing -->
      <div class="home_why_card">
        <div class="home_why_icon_wrap">
          <i class="fa fa-tag"></i>
        </div>
        <h4 class="home_why_card_title">Transparent & Upfront Pricing</h4>
        <p class="home_why_card_text">
          Clear, fixed rates with zero hidden charges. You know the exact cost upfront before any work begins.
        </p>
      </div>

      <!-- 3. Safe & Advanced Tools -->
      <div class="home_why_card">
        <div class="home_why_icon_wrap">
          <i class="fa fa-cogs"></i>
        </div>
        <h4 class="home_why_card_title">Advanced Equipment & Safety</h4>
        <p class="home_why_card_text">
          We use industry-grade equipment and safe, eco-friendly products for flawless, hygienic, and long-lasting results.
        </p>
      </div>

      <!-- 4. Customer Trusted & Warranty -->
      <div class="home_why_card">
        <div class="home_why_icon_wrap">
          <i class="fa fa-thumbs-up"></i>
        </div>
        <h4 class="home_why_card_title">Satisfaction Guaranteed</h4>
        <p class="home_why_card_text">
          Dedicated customer support at every step. Complete peace of mind with our hassle-free service guarantee.
        </p>
      </div>
    </div>

    <!-- Trust Highlights Bar -->
    <div class="home_why_trust_bar">
      <div class="trust_bar_item">
        <i class="fa fa-check-circle"></i>
        <span>100% Background-Verified Pros</span>
      </div>
      <div class="trust_bar_item">
        <i class="fa fa-check-circle"></i>
        <span>Zero Hidden Fees or Surprise Charges</span>
      </div>
      <div class="trust_bar_item">
        <i class="fa fa-check-circle"></i>
        <span>Doorstep Service on Your Schedule</span>
    </div>
  </div>
</section>

<!-- How It Works Section (Roadmap) -->
<style>
.home_how_it_works_section {
  padding: 80px 0 85px 0;
  background: #ffffff;
  position: relative;
  border-top: 1px solid #edf2f7;
  border-bottom: 1px solid #edf2f7;
}
.how_it_works_header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 55px;
  gap: 30px;
}
.how_it_works_header_left {
  max-width: 520px;
}
.how_it_works_eyebrow {
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 2px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 8px;
  display: block;
}
.how_it_works_title {
  font-size: 38px;
  font-weight: 800;
  color: #0a1c33;
  line-height: 1.2;
  margin: 0;
  letter-spacing: -0.5px;
}
.how_it_works_desc {
  max-width: 440px;
  font-size: 16px;
  line-height: 1.6;
  color: #64748b;
  margin: 0;
  padding-bottom: 4px;
}
.how_it_works_roadmap {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
.how_it_works_track {
  position: absolute;
  top: 29px;
  left: 8%;
  right: 8%;
  height: 2px;
  background: #ffdacf;
  z-index: 1;
}
.how_step_item {
  position: relative;
  z-index: 2;
  text-align: center;
  flex: 1;
  padding: 0 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.how_step_node {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #ffffff;
  border: 2.5px solid #f25b29;
  color: #f25b29;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  font-weight: 800;
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.14);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: default;
}
.how_step_item:hover .how_step_node {
  background: #f25b29;
  color: #ffffff;
  transform: scale(1.12);
  box-shadow: 0 8px 24px rgba(242, 91, 41, 0.35);
}
.how_step_content {
  margin-top: 18px;
}
.how_step_title {
  font-size: 16.5px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0 0 6px 0;
  line-height: 1.3;
}
.how_step_desc {
  font-size: 13.5px;
  color: #64748b;
  line-height: 1.5;
  margin: 0;
}
@media (max-width: 991px) {
  .how_it_works_header {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 40px;
  }
  .how_it_works_title {
    font-size: 30px;
  }
  .how_it_works_desc {
    max-width: 100%;
    font-size: 14.5px;
  }
  .how_step_node {
    width: 48px;
    height: 48px;
    font-size: 15px;
  }
  .how_it_works_track {
    top: 24px;
  }
  .how_step_title {
    font-size: 14.5px;
  }
  .how_step_desc {
    font-size: 12px;
  }
}
@media (max-width: 640px) {
  .home_how_it_works_section {
    padding: 50px 0 55px 0;
  }
  .how_it_works_roadmap {
    flex-direction: column;
    align-items: flex-start;
    padding-left: 15px;
    gap: 28px;
  }
  .how_it_works_track {
    top: 10px;
    bottom: 20px;
    left: 38px;
    width: 2px;
    height: auto;
    right: auto;
  }
  .how_step_item {
    flex-direction: row;
    align-items: flex-start;
    text-align: left;
    padding: 0;
    gap: 16px;
    width: 100%;
  }
  .how_step_node {
    flex-shrink: 0;
  }
  .how_step_content {
    margin-top: 2px;
  }
}
</style>

<section class="home_how_it_works_section" id="howItWorks">
  <div class="container">
    <!-- Header with Left Title and Right Description -->
    <div class="how_it_works_header">
      <div class="how_it_works_header_left">
        <span class="how_it_works_eyebrow"> HOW IT WORKS</span>
        <h2 class="how_it_works_title">A simple roadmap.</h2>
      </div>
      <div class="how_it_works_header_right">
        <p class="how_it_works_desc">Choose your service, share the details, secure your slot and let REFIXEL handle the rest.</p>
      </div>
    </div>

    <!-- 5-Step Connected Roadmap -->
    <div class="how_it_works_roadmap">
      <div class="how_it_works_track" aria-hidden="true"></div>

      <!-- Step 01 -->
      <div class="how_step_item">
        <div class="how_step_node">01</div>
        <div class="how_step_content">
          <h4 class="how_step_title">Choose Service</h4>
          <p class="how_step_desc">Select what you need.</p>
        </div>
      </div>

      <!-- Step 02 -->
      <div class="how_step_item">
        <div class="how_step_node">02</div>
        <div class="how_step_content">
          <h4 class="how_step_title">Share Details</h4>
          <p class="how_step_desc">Tell us about the job.</p>
        </div>
      </div>

      <!-- Step 03 -->
      <div class="how_step_item">
        <div class="how_step_node">03</div>
        <div class="how_step_content">
          <h4 class="how_step_title">Choose Time</h4>
          <p class="how_step_desc">Select your preferred slot.</p>
        </div>
      </div>

      <!-- Step 04 -->
      <div class="how_step_item">
        <div class="how_step_node">04</div>
        <div class="how_step_content">
          <h4 class="how_step_title">Pre-book ₹50</h4>
          <p class="how_step_desc">Secure your request.</p>
        </div>
      </div>

      <!-- Step 05 -->
      <div class="how_step_item">
        <div class="how_step_node">05</div>
        <div class="how_step_content">
          <h4 class="how_step_title">Get It Done</h4>
          <p class="how_step_desc">Professional arrives.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- What's Required Section -->
<style>
.home_required_section {
  padding: 80px 0 85px 0;
  background: #f8fafc;
  position: relative;
  border-top: 1px solid #edf2f7;
  border-bottom: 1px solid #edf2f7;
}
.required_header {
  text-align: center;
  max-width: 680px;
  margin: 0 auto 45px auto;
}
.required_eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 10px;
}
.required_eyebrow .eyebrow_dash {
  display: inline-block;
  width: 20px;
  height: 2px;
  background: #f25b29;
  border-radius: 2px;
}
.required_title {
  font-size: 34px;
  font-weight: 800;
  color: #0a1c33;
  line-height: 1.25;
  margin: 0 0 12px 0;
  letter-spacing: -0.5px;
}
.required_desc {
  font-size: 15px;
  line-height: 1.6;
  color: #64748b;
  margin: 0 auto;
}
.required_grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.required_card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 28px 20px 24px 20px;
  box-shadow: 0 4px 18px rgba(10, 28, 51, 0.04);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}
.required_card:hover {
  transform: translateY(-5px);
  border-color: #ffdacf;
  box-shadow: 0 12px 28px rgba(242, 91, 41, 0.1);
}
.required_icon_wrap {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  background: #fff5f0;
  color: #f25b29;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  margin-bottom: 18px;
  transition: all 0.3s ease;
}
.required_card:hover .required_icon_wrap {
  background: #f25b29;
  color: #ffffff;
  transform: scale(1.08);
}
.required_card_num {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 4px;
}
.required_card_title {
  font-size: 17px;
  font-weight: 700;
  color: #0a1c33;
  margin: 0 0 8px 0;
  line-height: 1.35;
}
.required_card_text {
  font-size: 13.5px;
  color: #64748b;
  line-height: 1.55;
  margin: 0;
  flex-grow: 1;
}
.required_banner_strip {
  margin-top: 38px;
  padding: 16px 24px;
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  text-align: center;
}
.required_banner_strip i {
  color: #f25b29;
  font-size: 18px;
}
.required_banner_strip span {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

@media (max-width: 991px) {
  .required_grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .required_title {
    font-size: 28px;
  }
}
@media (max-width: 768px) {
  .required_icon_wrap {
    width: 68px;
    height: 68px;
    border-radius: 18px;
    font-size: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(242, 91, 41, 0.14);
  }
}
@media (max-width: 576px) {
  .required_grid {
    grid-template-columns: 1fr;
  }
  .required_icon_wrap {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    font-size: 32px;
    margin-bottom: 20px;
  }
  .required_banner_strip {
    flex-direction: column;
    padding: 14px 16px;
  }
}

/* Before & After Interactive Compact Single-Row Section */
.home_before_after_section {
  padding: 55px 0 65px 0;
  background: #f8fafc;
  position: relative;
  border-bottom: 1px solid #edf2f7;
  overflow: hidden;
}
.ba_header_row {
  text-align: center;
  max-width: 680px;
  margin: 0 auto 30px auto;
}
.ba_eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 1.2px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.ba_eyebrow .eyebrow_dash {
  display: inline-block;
  width: 16px;
  height: 2px;
  background: #f25b29;
  border-radius: 2px;
}
.ba_title {
  font-size: 30px;
  font-weight: 800;
  color: #0a1c33;
  line-height: 1.25;
  margin: 0 0 8px 0;
  letter-spacing: -0.4px;
}
.ba_desc {
  font-size: 14px;
  line-height: 1.5;
  color: #64748b;
  margin: 0;
}

/* Single-Row Grid / Carousel */
.ba_single_row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  padding: 4px 2px 10px 2px;
}

/* Compact Interactive Card */
.ba_compare_card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 18px rgba(10, 28, 51, 0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}
.ba_compare_card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 26px rgba(10, 28, 51, 0.1);
  border-color: #cbd5e1;
}

/* Compact Slideable Viewer */
.ba_compare_viewer {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: #0a1c33;
  user-select: none;
  -webkit-user-select: none;
  touch-action: pan-y;
  --pos: 50%;
}
.ba_img_base {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  pointer-events: none;
  display: block;
}
.ba_layer_before {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
  clip-path: inset(0 calc(100% - var(--pos, 50%)) 0 0);
  -webkit-clip-path: inset(0 calc(100% - var(--pos, 50%)) 0 0);
  transition: none;
}
.ba_img_clip {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Compact Badges */
.ba_badge_pill {
  position: absolute;
  top: 8px;
  font-size: 9px;
  font-weight: 800;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 12px;
  z-index: 5;
  pointer-events: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
.ba_badge_before {
  left: 8px;
  background: rgba(225, 29, 72, 0.95);
  color: #ffffff;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
.ba_badge_after {
  right: 8px;
  background: rgba(16, 185, 129, 0.95);
  color: #ffffff;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.ba_service_pill {
  position: absolute;
  bottom: 8px;
  left: 8px;
  background: rgba(10, 28, 51, 0.88);
  color: #ffffff;
  font-size: 9px;
  font-weight: 700;
  padding: 2.5px 8px;
  border-radius: 12px;
  z-index: 5;
  pointer-events: none;
  letter-spacing: 0.4px;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
.ba_hint_pill {
  position: absolute;
  bottom: 8px;
  right: 8px;
  background: rgba(15, 23, 42, 0.85);
  color: #f8fafc;
  font-size: 9px;
  font-weight: 600;
  padding: 2.5px 7px;
  border-radius: 12px;
  z-index: 5;
  pointer-events: none;
  display: flex;
  align-items: center;
  gap: 3px;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Compact Divider Handle */
.ba_slider_handle {
  position: absolute;
  top: 0;
  bottom: 0;
  left: var(--pos, 50%);
  width: 2px;
  background: #ffffff;
  transform: translateX(-50%);
  z-index: 6;
  pointer-events: none;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.45);
}
.ba_slider_button {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #ffffff;
  color: #0a1c33;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), 0 0 0 2px rgba(242, 91, 41, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
  font-size: 9.5px;
  transition: transform 0.2s ease;
}
.ba_slider_button i {
  color: #f25b29;
}
.ba_compare_viewer:hover .ba_slider_button {
  transform: translate(-50%, -50%) scale(1.1);
}

/* Invisible Range Input */
.ba_range_input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  opacity: 0;
  cursor: ew-resize;
  z-index: 10;
  -webkit-appearance: none;
  appearance: none;
}
.ba_range_input::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 36px;
  height: 100%;
  cursor: ew-resize;
}
.ba_range_input::-moz-range-thumb {
  width: 36px;
  height: 100%;
  cursor: ew-resize;
}

/* Compact Card Body */
.ba_card_details {
  padding: 14px 14px 12px 14px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}
.ba_card_title {
  font-size: 14.5px;
  font-weight: 700;
  color: #0a1c33;
  margin: 0 0 5px 0;
  line-height: 1.3;
}
.ba_card_desc {
  font-size: 12px;
  line-height: 1.45;
  color: #64748b;
  margin: 0 0 10px 0;
  flex-grow: 1;
}

/* Compact Quick Snap Buttons */
.ba_quick_controls {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 10px;
  padding: 3px;
  background: #f1f5f9;
  border-radius: 8px;
}
.ba_quick_btn {
  flex: 1;
  padding: 3.5px 4px;
  font-size: 10px;
  font-weight: 600;
  border-radius: 5px;
  border: none;
  background: transparent;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
  white-space: nowrap;
}
.ba_quick_btn:hover {
  color: #0a1c33;
}
.ba_quick_btn.active {
  background: #ffffff;
  color: #0a1c33;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

.ba_card_footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #f1f5f9;
  padding-top: 10px;
  margin-top: auto;
}
.ba_badge_guarantee {
  font-size: 11px;
  font-weight: 600;
  color: #10b981;
  display: flex;
  align-items: center;
  gap: 4px;
}
.ba_book_btn {
  font-size: 11.5px;
  font-weight: 700;
  color: #f25b29 !important;
  text-decoration: none !important;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  transition: gap 0.2s ease;
}
.ba_book_btn:hover {
  gap: 6px;
}

/* Responsive: 1 row with horizontal swipe on tablets/mobile */
@media (max-width: 991px) {
  .ba_single_row {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    gap: 14px;
    padding-bottom: 14px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #ffdacf transparent;
  }
  .ba_single_row::-webkit-scrollbar {
    height: 5px;
  }
  .ba_single_row::-webkit-scrollbar-thumb {
    background-color: #ffdacf;
    border-radius: 5px;
  }
  .ba_compare_card {
    flex: 0 0 270px;
    max-width: 270px;
    scroll-snap-align: start;
  }
}
@media (max-width: 576px) {
  .home_before_after_section {
    padding: 45px 0 50px 0;
  }
  .ba_title {
    font-size: 24px;
  }
  .ba_compare_card {
    flex: 0 0 250px;
    max-width: 250px;
  }
}
</style>

<!-- 1. What's Required Section -->
<section class="home_required_section" id="whatsRequired">
  <div class="container">
    <div class="required_header">
      <div class="required_eyebrow">
        <span class="eyebrow_dash"></span>
        <span> PREPARATION GUIDE</span>
        <span class="eyebrow_dash"></span>
      </div>
      <h2 class="required_title">What's Required From Your Side</h2>
      <p class="required_desc">A quick checklist to ensure our service professionals have everything they need for a seamless, on-time service.</p>
    </div>

    <div class="required_grid">
      <!-- Item 1 -->
      <div class="required_card">
        <div class="required_icon_wrap">
          <i class="fa fa-plug"></i>
        </div>
        <div class="required_card_num">01 / UTILITIES</div>
        <h4 class="required_card_title">Water & Electricity Access</h4>
        <p class="required_card_text">A working electrical socket and fresh tap water connection for mechanized cleaning tools, jet pumps, and power tools.</p>
      </div>

      <!-- Item 2 -->
      <div class="required_card">
        <div class="required_icon_wrap">
          <i class="fa fa-expand"></i>
        </div>
        <div class="required_card_num">02 / WORKSPACE</div>
        <h4 class="required_card_title">Clear Area & Access</h4>
        <p class="required_card_text">Move basic clutter and lightweight objects away from the target appliance, sofa, or room to provide safe, speedy working space.</p>
      </div>

      <!-- Item 3 -->
      <div class="required_card">
        <div class="required_icon_wrap">
          <i class="fa fa-lock"></i>
        </div>
        <div class="required_card_num">03 / SECURITY</div>
        <h4 class="required_card_title">Secure Valuables</h4>
        <p class="required_card_text">Please safely lock away cash, jewelry, delicate heirlooms, and sensitive documents prior to service execution for peace of mind.</p>
      </div>

      <!-- Item 4 -->
      <div class="required_card">
        <div class="required_icon_wrap">
          <i class="fa fa-user"></i>
        </div>
        <div class="required_card_num">04 / APPROVAL</div>
        <h4 class="required_card_title">Adult Present on Site</h4>
        <p class="required_card_text">An adult family member should be available for the initial job brief and post-service quality check to ensure 100% satisfaction.</p>
      </div>
    </div>

    <!-- Assurance banner -->
    <div class="required_banner_strip">
      <i class="fa fa-check-circle"></i>
      <span>Everything else — tools, commercial equipment, safe chemicals, and genuine spares — is 100% brought by REFIXEL.</span>
    </div>
  </div>
</section>

<!-- 2. Before & After Interactive Slideable Showcase Section (Single Compact Row) -->
<section class="home_before_after_section" id="beforeAfterSection">
  <div class="container">
    <div class="ba_header_row">
      <div class="ba_eyebrow">
        <span class="eyebrow_dash"></span>
        <span>PROVEN TRANSFORMATIONS</span>
        <span class="eyebrow_dash"></span>
      </div>
      <h2 class="ba_title">Hold & Slide: Real Results</h2>
      <p class="ba_desc">Drag the interactive slider horizontally to compare before and after.</p>
    </div>

    <div class="ba_single_row">
      <!-- Card 1: Sofa Cleaning -->
      <div class="ba_compare_card">
        <div class="ba_compare_viewer" style="--pos: 50%;">
          <!-- Base Image: After -->
          <img src="<?= \App\Core\View::asset('img/transformations/sofa-after.jpg') ?>" alt="Living Room Sofa After Cleaning" class="ba_img_base" loading="lazy">
          <span class="ba_badge_pill ba_badge_after">AFTER</span>

          <!-- Clipped Overlay: Before -->
          <div class="ba_layer_before">
            <img src="<?= \App\Core\View::asset('img/transformations/sofa-before.jpg') ?>" alt="Living Room Sofa Before Cleaning" class="ba_img_clip" loading="lazy">
            <span class="ba_badge_pill ba_badge_before">BEFORE</span>
          </div>

          <!-- Divider Line and Draggable Knob -->
          <div class="ba_slider_handle">
            <div class="ba_slider_button">
              <i class="fa fa-chevron-left"></i>
              <i class="fa fa-chevron-right"></i>
            </div>
          </div>

          <!-- Service & Hint Badges -->
          <span class="ba_service_pill">SOFA CARE</span>
          <span class="ba_hint_pill"><i class="fa fa-arrows-h"></i> Slide</span>

          <!-- Invisible range input covering entire viewer -->
          <input type="range" min="0" max="100" value="50" class="ba_range_input" aria-label="Slide to compare sofa before and after cleaning">
        </div>

        <div class="ba_card_details">
          <h4 class="ba_card_title">Sofa Shampoo & Stain Lift</h4>
          <p class="ba_card_desc">Coffee spills & deep grime extracted with active bio-foam wash.</p>
          
          <div class="ba_quick_controls">
            <button type="button" class="ba_quick_btn" data-set-pos="100">Before</button>
            <button type="button" class="ba_quick_btn active" data-set-pos="50">Split</button>
            <button type="button" class="ba_quick_btn" data-set-pos="0">After</button>
          </div>

          <div class="ba_card_footer">
            <span class="ba_badge_guarantee"><i class="fa fa-check-circle"></i> 100% Lifted</span>
            <a href="<?= \App\Core\View::url('/services') ?>" class="ba_book_btn">Book <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Card 2: Kitchen Degreasing -->
      <div class="ba_compare_card">
        <div class="ba_compare_viewer" style="--pos: 50%;">
          <!-- Base Image: After -->
          <img src="<?= \App\Core\View::asset('img/transformations/kitchen-after.jpg') ?>" alt="Kitchen Stove Countertop After Degreasing" class="ba_img_base" loading="lazy">
          <span class="ba_badge_pill ba_badge_after">AFTER</span>

          <!-- Clipped Overlay: Before -->
          <div class="ba_layer_before">
            <img src="<?= \App\Core\View::asset('img/transformations/kitchen-before.jpg') ?>" alt="Kitchen Stove Countertop Before Degreasing" class="ba_img_clip" loading="lazy">
            <span class="ba_badge_pill ba_badge_before">BEFORE</span>
          </div>

          <!-- Divider Line and Draggable Knob -->
          <div class="ba_slider_handle">
            <div class="ba_slider_button">
              <i class="fa fa-chevron-left"></i>
              <i class="fa fa-chevron-right"></i>
            </div>
          </div>

          <!-- Service & Hint Badges -->
          <span class="ba_service_pill">KITCHEN CLEAN</span>
          <span class="ba_hint_pill"><i class="fa fa-arrows-h"></i> Slide</span>

          <!-- Invisible range input covering entire viewer -->
          <input type="range" min="0" max="100" value="50" class="ba_range_input" aria-label="Slide to compare kitchen stove before and after degreasing">
        </div>

        <div class="ba_card_details">
          <h4 class="ba_card_title">Stove & Tiles Degreasing</h4>
          <p class="ba_card_desc">Burnt grease and oil crust stripped with eco-degreaser & steam flush.</p>
          
          <div class="ba_quick_controls">
            <button type="button" class="ba_quick_btn" data-set-pos="100">Before</button>
            <button type="button" class="ba_quick_btn active" data-set-pos="50">Split</button>
            <button type="button" class="ba_quick_btn" data-set-pos="0">After</button>
          </div>

          <div class="ba_card_footer">
            <span class="ba_badge_guarantee"><i class="fa fa-sparkles"></i> Zero Grease</span>
            <a href="<?= \App\Core\View::url('/services') ?>" class="ba_book_btn">Book <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Card 3: Bathroom Descaling -->
      <div class="ba_compare_card">
        <div class="ba_compare_viewer" style="--pos: 50%;">
          <!-- Base Image: After -->
          <img src="<?= \App\Core\View::asset('img/transformations/bathroom-after.jpg') ?>" alt="Bathroom Shower Glass After Descaling" class="ba_img_base" loading="lazy">
          <span class="ba_badge_pill ba_badge_after">AFTER</span>

          <!-- Clipped Overlay: Before -->
          <div class="ba_layer_before">
            <img src="<?= \App\Core\View::asset('img/transformations/bathroom-before.jpg') ?>" alt="Bathroom Shower Glass Before Descaling" class="ba_img_clip" loading="lazy">
            <span class="ba_badge_pill ba_badge_before">BEFORE</span>
          </div>

          <!-- Divider Line and Draggable Knob -->
          <div class="ba_slider_handle">
            <div class="ba_slider_button">
              <i class="fa fa-chevron-left"></i>
              <i class="fa fa-chevron-right"></i>
            </div>
          </div>

          <!-- Service & Hint Badges -->
          <span class="ba_service_pill">BATHROOM</span>
          <span class="ba_hint_pill"><i class="fa fa-arrows-h"></i> Slide</span>

          <!-- Invisible range input covering entire viewer -->
          <input type="range" min="0" max="100" value="50" class="ba_range_input" aria-label="Slide to compare bathroom shower glass before and after descaling">
        </div>

        <div class="ba_card_details">
          <h4 class="ba_card_title">Shower Glass Descaling</h4>
          <p class="ba_card_desc">Hard-water calcium & soap scum dissolved to crystal transparency.</p>
          
          <div class="ba_quick_controls">
            <button type="button" class="ba_quick_btn" data-set-pos="100">Before</button>
            <button type="button" class="ba_quick_btn active" data-set-pos="50">Split</button>
            <button type="button" class="ba_quick_btn" data-set-pos="0">After</button>
          </div>

          <div class="ba_card_footer">
            <span class="ba_badge_guarantee"><i class="fa fa-shield"></i> Clear Glass</span>
            <a href="<?= \App\Core\View::url('/services') ?>" class="ba_book_btn">Book <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Card 4: AC Jet Cleaning -->
      <div class="ba_compare_card">
        <div class="ba_compare_viewer" style="--pos: 50%;">
          <!-- Base Image: After -->
          <img src="<?= \App\Core\View::asset('img/transformations/ac-after.jpg') ?>" alt="Split AC Cooling Coil After Jet Clean" class="ba_img_base" loading="lazy">
          <span class="ba_badge_pill ba_badge_after">AFTER</span>

          <!-- Clipped Overlay: Before -->
          <div class="ba_layer_before">
            <img src="<?= \App\Core\View::asset('img/transformations/ac-before.jpg') ?>" alt="Split AC Cooling Coil Before Jet Clean" class="ba_img_clip" loading="lazy">
            <span class="ba_badge_pill ba_badge_before">BEFORE</span>
          </div>

          <!-- Divider Line and Draggable Knob -->
          <div class="ba_slider_handle">
            <div class="ba_slider_button">
              <i class="fa fa-chevron-left"></i>
              <i class="fa fa-chevron-right"></i>
            </div>
          </div>

          <!-- Service & Hint Badges -->
          <span class="ba_service_pill">AC SERVICE</span>
          <span class="ba_hint_pill"><i class="fa fa-arrows-h"></i> Slide</span>

          <!-- Invisible range input covering entire viewer -->
          <input type="range" min="0" max="100" value="50" class="ba_range_input" aria-label="Slide to compare split AC coil before and after jet clean">
        </div>

        <div class="ba_card_details">
          <h4 class="ba_card_title">AC Cooling Coil Jet Flush</h4>
          <p class="ba_card_desc">Choked fins and mold flushed with 120-bar pressurized jet wash.</p>
          
          <div class="ba_quick_controls">
            <button type="button" class="ba_quick_btn" data-set-pos="100">Before</button>
            <button type="button" class="ba_quick_btn active" data-set-pos="50">Split</button>
            <button type="button" class="ba_quick_btn" data-set-pos="0">After</button>
          </div>

          <div class="ba_card_footer">
            <span class="ba_badge_guarantee"><i class="fa fa-snowflake-o"></i> 2x Airflow</span>
            <a href="<?= \App\Core\View::url('/services') ?>" class="ba_book_btn">Book <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var viewers = document.querySelectorAll('.ba_compare_viewer');

  viewers.forEach(function (viewer) {
    var range = viewer.querySelector('.ba_range_input');
    var card = viewer.closest('.ba_compare_card');
    var quickBtns = card ? card.querySelectorAll('.ba_quick_btn') : [];

    function updatePosition(val) {
      viewer.style.setProperty('--pos', val + '%');
      if (range) {
        range.value = val;
      }
      if (quickBtns.length > 0) {
        quickBtns.forEach(function (btn) {
          if (btn.getAttribute('data-set-pos') === String(val)) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });
      }
    }

    if (range) {
      range.addEventListener('input', function () {
        updatePosition(this.value);
      });
      range.addEventListener('change', function () {
        updatePosition(this.value);
      });
    }

    if (quickBtns.length > 0) {
      quickBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var targetVal = this.getAttribute('data-set-pos');
          updatePosition(targetVal);
        });
      });
    }
  });
</script>

<!-- ==========================================================================
     06. SERVICE PROMO SHOWCASE & CUSTOMER REVIEWS
     ========================================================================== -->
<style>
/* Service Promo & Reviews Section */
.home_promo_review_section {
  padding: 65px 0 70px 0;
  background: #ffffff;
  position: relative;
  border-bottom: 1px solid #edf2f7;
}

.reviews_header_row {
  margin-bottom: 28px;
}
.reviews_eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.reviews_eyebrow .eyebrow_dash {
  display: inline-block;
  width: 18px;
  height: 2px;
  background: #f25b29;
}
.reviews_title {
  font-size: 36px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0;
  letter-spacing: -0.5px;
  line-height: 1.2;
}

.single_promo_video_card {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  min-height: 440px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 30px 28px;
  background: #0a1c33;
  box-shadow: 0 10px 30px rgba(10, 28, 51, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid #e2e8f0;
}
.single_promo_video_card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 36px rgba(10, 28, 51, 0.14);
}

.promo_card_bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  transition: transform 0.6s ease;
}
.single_promo_video_card:hover .promo_card_bg {
  transform: scale(1.05);
}
.promo_card_overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(10, 28, 51, 0.15) 0%, rgba(10, 28, 51, 0.5) 45%, rgba(10, 28, 51, 0.94) 100%);
}

.promo_card_content {
  position: relative;
  z-index: 2;
}
.promo_brand_badge {
  position: absolute;
  top: -300px;
  left: 0;
  background: rgba(255, 255, 255, 0.94);
  padding: 6px 14px;
  border-radius: 10px;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.promo_logo {
  height: 22px;
  width: auto;
  display: block;
}
.promo_card_title {
  color: #ffffff;
  font-size: 24px;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 5px 0;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}
.promo_card_sub {
  color: #e2e8f0;
  font-size: 13.5px;
  font-weight: 500;
  margin: 0 0 16px 0;
}
.promo_card_features {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  margin-bottom: 20px;
}
.promo_feat_pill {
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: #ffffff;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 11px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.promo_card_btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #f25b29;
  color: #ffffff !important;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 22px;
  border-radius: 10px;
  text-decoration: none !important;
  transition: all 0.25s ease;
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);
  width: fit-content;
}
.promo_card_btn:hover {
  background: #e04815;
  transform: translateX(3px);
  box-shadow: 0 6px 18px rgba(242, 91, 41, 0.45);
}

/* Central Glowing Play Button */
.single_video_play_btn {
  position: absolute;
  top: 44%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background: #f25b29;
  color: #ffffff;
  border: none;
  cursor: pointer;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  box-shadow: 0 0 0 9px rgba(242, 91, 41, 0.32), 0 10px 28px rgba(0, 0, 0, 0.35);
  transition: all 0.3s ease;
}
.single_video_play_btn:hover {
  transform: translate(-50%, -50%) scale(1.1);
  box-shadow: 0 0 0 15px rgba(242, 91, 41, 0.42), 0 14px 34px rgba(0, 0, 0, 0.45);
}
.single_video_play_btn i {
  margin-left: 3px;
}

/* Reviews Swipeable Slider Card */
.reviews_slider_card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 28px 30px 24px 30px;
  min-height: 440px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 10px 30px rgba(10, 28, 51, 0.06);
  position: relative;
}

.reviews_card_top_bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  gap: 12px;
}

.google_badge_pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 6px 14px;
  border-radius: 20px;
}
.google_g_icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #4285F4;
  color: #ffffff;
  font-size: 11px;
  font-weight: 900;
}
.google_score_star {
  color: #f59e0b;
  font-weight: 800;
  font-size: 11.5px;
}

.reviews_nav_controls {
  display: flex;
  align-items: center;
  gap: 8px;
}
.review_counter_pill {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  background: #f1f5f9;
  padding: 5px 10px;
  border-radius: 12px;
  letter-spacing: 0.5px;
}
.slider_arrow_btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #0a1c33;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.slider_arrow_btn:hover {
  background: #0a1c33;
  color: #ffffff;
  border-color: #0a1c33;
  transform: scale(1.06);
}

.reviews_slider_viewport {
  overflow: hidden;
  width: 100%;
  flex: 1;
  display: flex;
  align-items: center;
  cursor: grab;
  user-select: none;
  -webkit-user-select: none;
}
.reviews_slider_viewport:active {
  cursor: grabbing;
}
.reviews_slider_track {
  display: flex;
  width: 100%;
  transition: transform 0.42s cubic-bezier(0.25, 1, 0.5, 1);
  will-change: transform;
}
.review_slide_item {
  min-width: 100%;
  width: 100%;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  box-sizing: border-box;
}

.testimonial_stars {
  color: #f59e0b;
  font-size: 17px;
  margin-bottom: 12px;
  display: flex;
  gap: 4px;
}
.testimonial_quote {
  font-size: 18px;
  font-weight: 600;
  line-height: 1.55;
  color: #0a1c33;
  margin: 0 0 20px 0;
  font-style: normal;
}
.testimonial_author {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: auto;
}
.author_avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0a1c33, #1e3a66);
  color: #ffffff;
  font-weight: 800;
  font-size: 13.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(10, 28, 51, 0.15);
}
.author_meta {
  flex: 1;
}
.author_name {
  font-size: 14.5px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.verified_tag {
  font-size: 10.5px;
  font-weight: 700;
  color: #10b981;
  background: #ecfdf5;
  padding: 2px 7px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.author_role {
  font-size: 12.5px;
  color: #64748b;
  margin: 2px 0 0 0;
}

.reviews_card_bottom_bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  gap: 15px;
  flex-wrap: wrap;
}
.slider_dots_wrapper {
  display: flex;
  align-items: center;
  gap: 6px;
}
.slider_dot {
  width: 8px;
  height: 8px;
  border-radius: 4px;
  background: #cbd5e1;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: all 0.25s ease;
}
.slider_dot.active {
  width: 24px;
  background: #f25b29;
}
.btn_google_reviews {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #0a1c33;
  color: #ffffff !important;
  font-size: 12.5px;
  font-weight: 700;
  padding: 9px 18px;
  border-radius: 8px;
  text-decoration: none !important;
  width: fit-content;
  transition: all 0.25s ease;
}
.btn_google_reviews:hover {
  background: #1e3a66;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(10, 28, 51, 0.2);
}

/* 07. Contact Section */
.home_contact_section {
  padding: 65px 0 24px 0;
  background: #f8fafc;
}
.contact_header_row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  gap: 20px;
}
.contact_eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.5px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.contact_eyebrow .eyebrow_dash {
  display: inline-block;
  width: 18px;
  height: 2px;
  background: #f25b29;
}
.contact_title {
  font-size: 36px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0;
  letter-spacing: -0.5px;
}
.contact_sub_desc {
  font-size: 14.5px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

.home_contact_box {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(10, 28, 51, 0.05);
  padding: 16px;
}
.contact_help_card {
  background: #0a1c33;
  border-radius: 16px;
  padding: 36px 30px;
  height: 100%;
  color: #ffffff;
  display: flex;
  flex-direction: column;
}
.help_badge {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #94a3b8;
  text-transform: uppercase;
  margin-bottom: 12px;
  display: inline-block;
}
.help_title {
  font-size: 26px;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 14px 0;
  line-height: 1.25;
}
.help_desc {
  font-size: 13.5px;
  line-height: 1.6;
  color: #cbd5e1;
  margin: 0 0 30px 0;
}
.help_items {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-top: auto;
}
.help_item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13.5px;
  color: #f1f5f9;
}
.help_item i {
  color: #f25b29;
  font-size: 16px;
  width: 20px;
  text-align: center;
}
.help_item a {
  color: #f1f5f9;
  text-decoration: none;
  transition: color 0.2s;
}
.help_item a:hover {
  color: #f25b29;
}

.contact_form_wrap {
  padding: 28px 30px;
}
.form_field_group {
  margin-bottom: 16px;
}
.form_field_label {
  display: block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: #475569;
  margin-bottom: 8px;
}
.ref_contact_input,
.ref_contact_select,
.ref_contact_textarea {
  width: 100%;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 14px;
  color: #0f172a;
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}
.ref_contact_input:focus,
.ref_contact_select:focus,
.ref_contact_textarea:focus {
  background: #ffffff;
  border-color: #f25b29;
  box-shadow: 0 0 0 3px rgba(242, 91, 41, 0.15);
}
.ref_location_input_wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.ref_contact_input.has_loc_btn {
  padding-right: 125px;
}
.btn_input_detect_loc {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  background: #0a1c33;
  color: #ffffff;
  border: none;
  font-size: 11px;
  font-weight: 700;
  padding: 6px 12px;
  border-radius: 7px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn_input_detect_loc:hover {
  background: #f25b29;
}
.btn_input_detect_loc i {
  color: #f25b29;
  font-size: 11px;
  transition: color 0.2s ease;
}
.btn_input_detect_loc:hover i {
  color: #ffffff;
}
.btn_input_detect_loc.is_detected {
  background: #10b981;
}
.btn_input_detect_loc.is_detected i {
  color: #ffffff;
}

.quick_loc_chips {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 5px;
  margin-top: 6px;
}
.quick_loc_chips span {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 600;
}
.loc_chip {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.loc_chip:hover {
  background: #0a1c33;
  color: #ffffff;
  border-color: #0a1c33;
}

.btn_send_enquiry {
  width: 100%;
  background: #f25b29;
  color: #ffffff;
  border: none;
  border-radius: 10px;
  padding: 14px;
  font-size: 14.5px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.25s ease;
  box-shadow: 0 6px 18px rgba(242, 91, 41, 0.35);
  margin-top: 6px;
}
.btn_send_enquiry:hover {
  background: #e04815;
  transform: translateY(-2px);
  box-shadow: 0 8px 22px rgba(242, 91, 41, 0.45);
}

/* 08. Service Location Section */
.home_location_section {
  padding: 0 0 65px 0;
  background: #f8fafc;
  margin-top: -6px;
}
.home_location_card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(10, 28, 51, 0.05);
}
.location_card_header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 20px;
}
.location_eyebrow {
  display: block;
  font-size: 11.5px;
  font-weight: 800;
  letter-spacing: 1.2px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.location_title {
  font-size: 26px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0 0 4px 0;
}
.location_sub {
  font-size: 13.5px;
  color: #64748b;
  margin: 0;
}
.location_header_actions {
  display: flex;
  align-items: center;
  gap: 12px;
}
.btn_use_location {
  background: #f25b29;
  color: #ffffff;
  border: none;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 20px;
  border-radius: 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.25s ease;
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);
}
.btn_use_location:hover {
  background: #e04815;
  transform: translateY(-2px);
}
.btn_open_maps {
  background: #ffffff;
  color: #0a1c33 !important;
  border: 1.5px solid #cbd5e1;
  font-size: 13px;
  font-weight: 700;
  padding: 9px 18px;
  border-radius: 10px;
  text-decoration: none !important;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.25s ease;
}
.btn_open_maps:hover {
  border-color: #0a1c33;
  background: #f8fafc;
}

/* Stylized Modern Map Canvas */
.location_map_box {
  position: relative;
  width: 100%;
  height: 380px;
  border-radius: 16px;
  overflow: hidden;
  background: #e8edf2;
  box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05);
}
.map_canvas_svg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}
.map_center_pin {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 5;
  pointer-events: none;
}
.pin_pulse_ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: rgba(242, 91, 41, 0.35);
  animation: pinPulse 2s infinite ease-out;
}
@keyframes pinPulse {
  0% { transform: translate(-50%, -50%) scale(0.6); opacity: 0.95; }
  100% { transform: translate(-50%, -50%) scale(2.4); opacity: 0; }
}
.pin_icon_circle {
  position: relative;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: #f25b29;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  box-shadow: 0 6px 20px rgba(242, 91, 41, 0.5), 0 0 0 3px #ffffff;
}

.map_status_pill {
  position: absolute;
  bottom: 20px;
  left: 20px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #0f172a;
  font-size: 12.5px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 30px;
  z-index: 6;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.map_corner_btn {
  position: absolute;
  bottom: 20px;
  right: 20px;
  background: #f25b29;
  color: #ffffff;
  border: none;
  font-size: 12.5px;
  font-weight: 700;
  padding: 9px 18px;
  border-radius: 8px;
  cursor: pointer;
  z-index: 6;
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.4);
  transition: all 0.2s ease;
}
.map_corner_btn:hover {
  background: #e04815;
  transform: translateY(-1px);
}

@media (max-width: 991px) {
  .single_promo_video_card {
    min-height: 380px;
  }
  .reviews_slider_card {
    min-height: 380px;
    padding: 24px 20px 20px 20px;
  }
  .contact_header_row,
  .location_card_header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .reviews_title,
  .contact_title {
    font-size: 28px;
  }
  .location_title {
    font-size: 22px;
  }
  .contact_form_wrap {
    padding: 24px 16px;
  }
  .location_map_box {
    height: 300px;
  }
  .prebook_title {
    font-size: 30px;
  }
  .final_cta_card {
    padding: 45px 20px;
  }
  .final_cta_title {
    font-size: 30px;
  }
}

/* ==========================================================================
   FREQUENTLY ASKED QUESTIONS (2-Column Pill Card Accordion)
   ========================================================================== */
.home_faq_section {
  padding: 80px 0 90px 0;
  background: #f8fafc;
  position: relative;
}
.faq_header_center {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 46px auto;
}
.faq_badge_pill {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #f25b29;
  background: #ffede6;
  padding: 5px 14px;
  border-radius: 20px;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.faq_main_title {
  font-size: 38px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0 0 12px 0;
  letter-spacing: -0.5px;
  line-height: 1.25;
}
.faq_main_title .faq_orange_word {
  color: #f25b29;
}
.faq_sub_title {
  font-size: 15px;
  color: #64748b;
  line-height: 1.6;
  margin: 0;
  font-weight: 500;
}

/* Two-column Grid */
.faq_two_col_grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px 22px;
  align-items: start;
}
.faq_col_stack {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.faq_pill_card {
  background: #ffffff;
  border: 1.5px solid #edf2f7;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(10, 28, 51, 0.03);
  transition: all 0.25s ease;
  overflow: hidden;
  cursor: pointer;
}
.faq_pill_card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 6px 20px rgba(10, 28, 51, 0.06);
  transform: translateY(-1px);
}
.faq_pill_card.active {
  border-color: #f25b29;
  box-shadow: 0 8px 25px rgba(242, 91, 41, 0.1);
}

.faq_pill_header {
  padding: 18px 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  user-select: none;
}
.faq_pill_question {
  font-size: 15px;
  font-weight: 700;
  color: #0a1c33;
  margin: 0;
  line-height: 1.45;
  flex: 1;
}
.faq_circle_plus {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f1f5f9;
  color: #0a1c33;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
  transition: all 0.25s ease;
}
.faq_pill_card:hover .faq_circle_plus {
  background: #0a1c33;
  color: #ffffff;
}
.faq_pill_card.active .faq_circle_plus {
  background: #f25b29;
  color: #ffffff;
  transform: rotate(45deg);
}

.faq_pill_body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease, padding 0.35s ease;
  padding: 0 22px;
}
.faq_pill_card.active .faq_pill_body {
  max-height: 250px;
  padding: 0 22px 20px 22px;
}
.faq_pill_answer {
  font-size: 13.5px;
  color: #64748b;
  line-height: 1.65;
  margin: 0;
  border-top: 1px solid #f1f5f9;
  padding-top: 14px;
}

/* Responsive adjustments for FAQ Section */
@media (max-width: 991px) {
  .home_faq_section {
    padding: 60px 0 70px 0;
  }
  .faq_header_center {
    margin-bottom: 34px;
  }
  .faq_main_title {
    font-size: 30px;
  }
  .faq_two_col_grid {
    grid-template-columns: 1fr !important;
    gap: 14px;
  }
  .faq_col_stack {
    gap: 14px;
  }
}

@media (max-width: 576px) {
  .home_faq_section {
    padding: 48px 0 55px 0;
  }
  .faq_header_center {
    margin-bottom: 26px;
    padding: 0 6px;
  }
  .faq_main_title {
    font-size: 25px;
    line-height: 1.3;
  }
  .faq_sub_title {
    font-size: 13.5px;
  }
  .faq_two_col_grid {
    grid-template-columns: 1fr !important;
    gap: 12px;
    width: 100%;
  }
  .faq_col_stack {
    gap: 12px;
    width: 100%;
  }
  .faq_pill_card {
    width: 100%;
    border-radius: 14px;
  }
  .faq_pill_header {
    padding: 15px 16px;
    gap: 12px;
  }
  .faq_pill_question {
    font-size: 14px;
    line-height: 1.42;
  }
  .faq_circle_plus {
    width: 28px;
    height: 28px;
    font-size: 11px;
    flex-shrink: 0;
  }
  .faq_pill_body {
    padding: 0 16px;
  }
  .faq_pill_card.active .faq_pill_body {
    max-height: 400px;
    padding: 0 16px 16px 16px;
  }
  .faq_pill_answer {
    font-size: 13px;
    line-height: 1.6;
    padding-top: 12px;
  }
}

/* ==========================================================================
   08 / PRE-BOOKING SECTION (Dark Navy Theme)
   ========================================================================== */
.home_prebooking_section {
  padding: 85px 0 90px 0;
  background: #0a1c33;
  color: #ffffff;
  position: relative;
  overflow: hidden;
}
.prebook_eyebrow {
  display: inline-block;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.6px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.prebook_title {
  font-size: 40px;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.2;
  margin: 0 0 14px 0;
  letter-spacing: -0.5px;
}
.prebook_sub {
  font-size: 15px;
  color: #cbd5e1;
  line-height: 1.6;
  margin: 0 0 28px 0;
  max-width: 480px;
}
.prebook_price_wrap {
  margin-bottom: 26px;
}
.prebook_price_amount {
  font-size: 52px;
  font-weight: 800;
  color: #f25b29;
  line-height: 1;
  margin-bottom: 4px;
  letter-spacing: -1px;
}
.prebook_price_label {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 600;
}
.btn_prebook_cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #f25b29;
  color: #ffffff !important;
  font-size: 14px;
  font-weight: 700;
  padding: 13px 28px;
  border-radius: 10px;
  text-decoration: none !important;
  transition: all 0.25s ease;
  box-shadow: 0 6px 20px rgba(242, 91, 41, 0.4);
}
.btn_prebook_cta:hover {
  background: #e04815;
  transform: translateY(-2px);
  box-shadow: 0 8px 26px rgba(242, 91, 41, 0.5);
}

.prebook_steps_list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  justify-content: center;
  height: 100%;
}
.prebook_step_card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 14px;
  padding: 18px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.25s ease;
}
.prebook_step_card:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(242, 91, 41, 0.45);
  transform: translateX(4px);
}
.prebook_step_num {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f25b29;
  color: #ffffff;
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(242, 91, 41, 0.4);
}
.prebook_step_text {
  font-size: 15px;
  font-weight: 600;
  color: #ffffff;
  margin: 0;
}

/* ==========================================================================
   09 / FINAL CTA SECTION (Centered Card Theme)
   ========================================================================== */
.home_final_cta_section {
  padding: 70px 0 90px 0;
  background: #ffffff;
}
.final_cta_card {
  background: #0a1c33;
  border-radius: 26px;
  padding: 65px 40px;
  text-align: center;
  max-width: 980px;
  margin: 0 auto;
  box-shadow: 0 20px 45px rgba(10, 28, 51, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.08);
  position: relative;
  overflow: hidden;
}
.final_cta_eyebrow {
  display: inline-block;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.6px;
  color: #f25b29;
  text-transform: uppercase;
  margin-bottom: 12px;
}
.final_cta_title {
  font-size: 42px;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 14px 0;
  letter-spacing: -0.5px;
}
.final_cta_title .white_line {
  color: #ffffff;
  display: block;
}
.final_cta_title .orange_line {
  color: #f25b29;
  display: block;
}
.final_cta_sub {
  font-size: 15px;
  color: #cbd5e1;
  margin: 0 0 32px 0;
  font-weight: 500;
}
.btn_final_cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #f25b29;
  color: #ffffff !important;
  font-size: 14.5px;
  font-weight: 700;
  padding: 13px 32px;
  border-radius: 10px;
  text-decoration: none !important;
  transition: all 0.25s ease;
  box-shadow: 0 6px 20px rgba(242, 91, 41, 0.4);
}
.btn_final_cta:hover {
  background: #e04815;
  transform: translateY(-2px);
  box-shadow: 0 8px 26px rgba(242, 91, 41, 0.5);
}
</style>

<!-- 06. Service Promo Showcase & Reviews Section -->
<section class="home_promo_review_section" id="promoReviews">
  <div class="container">
    <!-- Section Header Row (06 / REVIEWS) -->
    <div class="reviews_header_row">
      <div class="reviews_eyebrow">
        <span class="eyebrow_dash"></span>
        <span>REVIEWS</span>
      </div>
      <h2 class="reviews_title">Real customers. Real results.</h2>
    </div>

    <div class="row g-4 align-items-stretch">
      <!-- Left: Single Featured Promo Video Card -->
      <div class="col-lg-6">
        <div class="single_promo_video_card" id="singlePromoVideoCard">
          <div class="promo_card_bg" style="background-image: url('<?= \App\Core\View::asset('img/Branded Home Cleaning Service in Action.png') ?>');">
            <div class="promo_card_overlay"></div>
          </div>
          
          <!-- Central Glowing Play Button -->
          <button type="button" class="single_video_play_btn" id="openPromoVideo" aria-label="Watch Service Video" title="Watch Service Video">
            <i class="fa fa-play"></i>
          </button>

          <div class="promo_card_content">
            <div class="promo_brand_badge">
              <img src="<?= \App\Core\View::asset('img/refixel-logo.png') ?>" alt="REFIXEL" class="promo_logo">
            </div>
            <h3 class="promo_card_title">Professional<br>Home Cleaning</h3>
            <p class="promo_card_sub">Cleaner Home • Healthier You</p>
            
            <div class="promo_card_features">
              <span class="promo_feat_pill"><i class="fa fa-sparkles"></i> Deep Clean</span>
              <span class="promo_feat_pill"><i class="fa fa-id-badge"></i> Trained Staff</span>
              <span class="promo_feat_pill"><i class="fa fa-leaf"></i> Safe Products</span>
            </div>
            
            <a href="<?= \App\Core\View::url('/services') ?>" class="promo_card_btn">Book Now <i class="fa fa-arrow-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Right: Horizontally Swipeable Google Reviews Carousel -->
      <div class="col-lg-6">
        <div class="reviews_slider_card">
          <div class="reviews_card_top_bar">
            <div class="google_badge_pill">
              <span class="google_g_icon">G</span>
              <span>Google Reviews</span>
              <span class="google_score_star">5.0 ★★★★★</span>
            </div>
            <div class="reviews_nav_controls">
              <span class="review_counter_pill" id="reviewCounterPill">01 / 05</span>
              <button type="button" class="slider_arrow_btn" id="btnPrevReview" aria-label="Previous Review">
                <i class="fa fa-chevron-left"></i>
              </button>
              <button type="button" class="slider_arrow_btn" id="btnNextReview" aria-label="Next Review">
                <i class="fa fa-chevron-right"></i>
              </button>
            </div>
          </div>

          <!-- Viewport & Track for Swipe -->
          <div class="reviews_slider_viewport" id="reviewsSliderViewport">
            <div class="reviews_slider_track" id="reviewsSliderTrack">
              <!-- Slide 1 -->
              <div class="review_slide_item">
                <div class="testimonial_stars">
                  <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                </div>
                <blockquote class="testimonial_quote">
                  "Professional, quick and easy from booking to completion. The service felt reliable from the first call and our home was left sparkling clean."
                </blockquote>
                <div class="testimonial_author">
                  <div class="author_avatar">AV</div>
                  <div class="author_meta">
                    <h5 class="author_name">Arun Verma <span class="verified_tag"><i class="fa fa-check-circle"></i> Verified</span></h5>
                    <p class="author_role">Home Deep Cleaning • Kashipur</p>
                  </div>
                </div>
              </div>

              <!-- Slide 2 -->
              <div class="review_slide_item">
                <div class="testimonial_stars">
                  <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                </div>
                <blockquote class="testimonial_quote">
                  "Called for emergency split AC jet servicing during peak summer. The technician arrived in 40 minutes with professional equipment. Freezing cooling restored!"
                </blockquote>
                <div class="testimonial_author">
                  <div class="author_avatar">PN</div>
                  <div class="author_meta">
                    <h5 class="author_name">Pooja Negi <span class="verified_tag"><i class="fa fa-check-circle"></i> Verified</span></h5>
                    <p class="author_role">Split AC Jet Service • Haldwani</p>
                  </div>
                </div>
              </div>

              <!-- Slide 3 -->
              <div class="review_slide_item">
                <div class="testimonial_stars">
                  <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                </div>
                <blockquote class="testimonial_quote">
                  "Transparent pricing and zero hidden fees. Got my kitchen sink pipeline unclogged and bathroom fittings replaced without any mess. Highly recommend!"
                </blockquote>
                <div class="testimonial_author">
                  <div class="author_avatar">MR</div>
                  <div class="author_meta">
                    <h5 class="author_name">Mohit Rawat <span class="verified_tag"><i class="fa fa-check-circle"></i> Verified</span></h5>
                    <p class="author_role">Plumbing Solutions • Rudrapur</p>
                  </div>
                </div>
              </div>

              <!-- Slide 4 -->
              <div class="review_slide_item">
                <div class="testimonial_stars">
                  <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                </div>
                <blockquote class="testimonial_quote">
                  "Our 5-seater fabric sofa had stubborn tea and coffee stains. REFIXEL extracted everything with active bio-foam wash. Looks absolutely brand new!"
                </blockquote>
                <div class="testimonial_author">
                  <div class="author_avatar">NJ</div>
                  <div class="author_meta">
                    <h5 class="author_name">Neha Joshi <span class="verified_tag"><i class="fa fa-check-circle"></i> Verified</span></h5>
                    <p class="author_role">Sofa Shampoo & Wash • Dehradun</p>
                  </div>
                </div>
              </div>

              <!-- Slide 5 -->
              <div class="review_slide_item">
                <div class="testimonial_stars">
                  <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                </div>
                <blockquote class="testimonial_quote">
                  "Very courteous and punctual team. Booked full home deep cleaning before a family function. Every room and bathroom was sanitized thoroughly."
                </blockquote>
                <div class="testimonial_author">
                  <div class="author_avatar">RS</div>
                  <div class="author_meta">
                    <h5 class="author_name">Rajeev Sharma <span class="verified_tag"><i class="fa fa-check-circle"></i> Verified</span></h5>
                    <p class="author_role">Full Home Cleaning • Jaspur</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Action & Dots -->
          <div class="reviews_card_bottom_bar">
            <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="btn_google_reviews">
              See Google Reviews <i class="fa fa-external-link"></i>
            </a>
            <div class="slider_dots_wrapper" id="reviewDotsWrap">
              <button type="button" class="slider_dot active" data-slide="0" aria-label="Review 1"></button>
              <button type="button" class="slider_dot" data-slide="1" aria-label="Review 2"></button>
              <button type="button" class="slider_dot" data-slide="2" aria-label="Review 3"></button>
              <button type="button" class="slider_dot" data-slide="3" aria-label="Review 4"></button>
              <button type="button" class="slider_dot" data-slide="4" aria-label="Review 5"></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 07. Contact Section ("Let's make it easy.") -->
<section class="home_contact_section" id="contactSection">
  <div class="container">
    <div class="contact_header_row">
      <div class="contact_header_left">
        <div class="contact_eyebrow">
          <span class="eyebrow_dash"></span>
          <span>CONTACT</span>
        </div>
        <h2 class="contact_title">Let's make it easy.</h2>
      </div>
      <div class="contact_header_right">
        <p class="contact_sub_desc">Tell us what you need and where you need it.</p>
      </div>
    </div>

    <!-- Contact Box: Left Dark Blue Card + Right Form -->
    <div class="home_contact_box">
      <div class="row g-0">
        <!-- Left: Dark Navy Help Card -->
        <div class="col-lg-4">
          <div class="contact_help_card">
            <span class="help_badge">GET IN TOUCH</span>
            <h3 class="help_title">We're here to help.</h3>
            <p class="help_desc">
              Have questions or need assistance? Reach out directly and our local support team will assist you immediately.
            </p>
            <div class="help_items">
              <div class="help_item">
                <i class="fa fa-phone"></i>
                <a href="tel:+918791154730">+91 87911 54730</a>
              </div>
              <div class="help_item">
                <i class="fa fa-envelope"></i>
                <a href="mailto:wearerefixel@gmail.com">wearerefixel@gmail.com</a>
              </div>
              <div class="help_item">
                <i class="fa fa-map-marker"></i>
                <span>Uttarakhand / Service Area</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Form -->
        <div class="col-lg-8">
          <div class="contact_form_wrap">
            <form id="homeContactForm" action="<?= \App\Core\View::url('/contact') ?>" method="POST">
              <?= \App\Core\View::csrf() ?>
              <input type="hidden" name="detected_location" id="homeContactLocation" value="">
              
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form_field_group">
                    <label class="form_field_label">NAME</label>
                    <input type="text" name="name" class="ref_contact_input" placeholder="Your name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form_field_group">
                    <label class="form_field_label">PHONE</label>
                    <input type="tel" name="phone" class="ref_contact_input" placeholder="+91" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form_field_group">
                    <label class="form_field_label">SERVICE</label>
                    <select name="service" class="ref_contact_select" required>
                      <option value="" disabled selected>Select a service</option>
                      <option value="Home Deep Cleaning">Full Home Deep Cleaning</option>
                      <option value="Plumbing Solutions">Plumbing Solutions & Pipe Repair</option>
                      <option value="AC Jet Service">Split AC Jet Cleaning & Servicing</option>
                      <option value="Electrical Repair">Electrical Repair & Rewiring</option>
                      <option value="Painting Services">Interior & Exterior Wall Painting</option>
                      <option value="Pest Control">Cockroach & Pest Control Treatment</option>
                      <option value="Carpentry Repair">Carpentry & Furniture Works</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form_field_group">
                    <label class="form_field_label">CHOOSE LOCATION</label>
                    <div class="ref_location_input_wrap">
                      <input type="text" name="location" id="homeContactLocationInput" list="refixelLocationSuggestions" class="ref_contact_input has_loc_btn" placeholder="Enter or detect your location..." required autocomplete="off">
                      <button type="button" class="btn_input_detect_loc" id="btnInputDetectLoc" title="Detect Exact GPS Location" aria-label="Auto-Detect Location">
                        <i class="fa fa-crosshairs"></i>
                        <span>Auto-Detect</span>
                      </button>
                    </div>

                    <!-- Datalist suggestions for instant auto-complete -->
                    <datalist id="refixelLocationSuggestions">
                      <option value="Nadehi (Sugar Mill), Udham Singh Nagar">
                      <option value="Kashipur, Udham Singh Nagar">
                      <option value="Jaspur, Udham Singh Nagar">
                      <option value="Rudrapur, Udham Singh Nagar">
                      <option value="Haldwani, Nainital">
                      <option value="Ramnagar, Nainital">
                      <option value="Dehradun, Uttarakhand">
                      <option value="Haridwar, Uttarakhand">
                      <option value="Roorkee, Haridwar">
                      <option value="Rishikesh, Dehradun">
                      <option value="Nainital, Uttarakhand">
                      <option value="Pantnagar, Udham Singh Nagar">
                      <option value="Bazpur, Udham Singh Nagar">
                      <option value="Kichha, Udham Singh Nagar">
                      <option value="Khatima, Udham Singh Nagar">
                    </datalist>

                    <!-- Quick Location Chips for 1-Tap Selection -->
                    <div class="quick_loc_chips">
                      <span>Quick Area:</span>
                      <button type="button" class="loc_chip" data-loc="Nadehi (Sugar Mill), Udham Singh Nagar">Nadehi</button>
                      <button type="button" class="loc_chip" data-loc="Kashipur, Udham Singh Nagar">Kashipur</button>
                      <button type="button" class="loc_chip" data-loc="Jaspur, Udham Singh Nagar">Jaspur</button>
                      <button type="button" class="loc_chip" data-loc="Rudrapur, Udham Singh Nagar">Rudrapur</button>
                      <button type="button" class="loc_chip" data-loc="Haldwani, Nainital">Haldwani</button>
                      <button type="button" class="loc_chip" data-loc="Dehradun, Uttarakhand">Dehradun</button>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form_field_group">
                    <label class="form_field_label">MESSAGE</label>
                    <textarea name="message" class="ref_contact_textarea" rows="4" placeholder="Tell us briefly what you need" required></textarea>
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn_send_enquiry">
                    Send Enquiry <i class="fa fa-arrow-right"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 08. Service Location Section ("Drop your location in one click.") -->
<section class="home_location_section" id="locationSection">
  <div class="container">
    <div class="home_location_card">
      <div class="location_card_header">
        <div class="location_header_left">
          <span class="location_eyebrow">YOUR SERVICE LOCATION</span>
          <h3 class="location_title">Drop your location in one click.</h3>
          <p class="location_sub">Use your browser location to set the service point.</p>
        </div>
        <div class="location_header_actions">
          <button type="button" class="btn_use_location" id="btnDetectLocation">
            <i class="fa fa-plus"></i> Use My Location
          </button>
          <a href="https://maps.google.com/?q=Uttarakhand,India" target="_blank" rel="noopener noreferrer" class="btn_open_maps">
            Open in Google Maps <i class="fa fa-external-link"></i>
          </a>
        </div>
      </div>

      <!-- Stylized Interactive Map Container -->
      <div class="location_map_box" id="locationMapBox">
        <!-- SVG Architectural Road Network Map (matching reference screenshot) -->
        <svg class="map_canvas_svg" viewBox="0 0 1000 400" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="bgGrad" x1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#e9edf2"/>
              <stop offset="50%" stop-color="#e3e8ef"/>
              <stop offset="100%" stop-color="#dde3ea"/>
            </linearGradient>
            <filter id="roadShadow" x="-10%" y="-10%" width="120%" height="120%">
              <feDropShadow dx="0" dy="2" stdDeviation="6" flood-color="#0a1c33" flood-opacity="0.08"/>
            </filter>
          </defs>
          <rect width="1000" height="400" fill="url(#bgGrad)" />

          <!-- Diagonal Primary Road 1 (Top-Left to Bottom-Right) -->
          <line x1="-50" y1="-20" x2="1050" y2="420" stroke="#ffffff" stroke-width="64" stroke-linecap="round" filter="url(#roadShadow)"/>
          <line x1="-50" y1="-20" x2="1050" y2="420" stroke="#f1f5f9" stroke-width="50" />
          <line x1="-50" y1="-20" x2="1050" y2="420" stroke="#cbd5e1" stroke-width="2.5" stroke-dasharray="14 10"/>

          <!-- Diagonal Primary Road 2 (Top-Right to Bottom-Left) -->
          <line x1="1050" y1="-20" x2="-50" y2="420" stroke="#ffffff" stroke-width="64" stroke-linecap="round" filter="url(#roadShadow)"/>
          <line x1="1050" y1="-20" x2="-50" y2="420" stroke="#f1f5f9" stroke-width="50" />
          <line x1="1050" y1="-20" x2="-50" y2="420" stroke="#cbd5e1" stroke-width="2.5" stroke-dasharray="14 10"/>

          <!-- Secondary connector street -->
          <line x1="500" y1="0" x2="500" y2="400" stroke="#ffffff" stroke-width="32" filter="url(#roadShadow)"/>
          <line x1="500" y1="0" x2="500" y2="400" stroke="#f8fafc" stroke-width="24"/>
        </svg>

        <!-- Central Glowing Orange Location Marker Pin -->
        <div class="map_center_pin">
          <div class="pin_pulse_ring"></div>
          <div class="pin_icon_circle">
            <i class="fa fa-map-marker"></i>
          </div>
        </div>

        <!-- Bottom Float Status Bar -->
        <div class="map_status_pill" id="locationStatusPill">
          <i class="fa fa-compass" style="color: #f25b29;"></i>
          <span id="locationStatusText">No location selected yet.</span>
        </div>

        <!-- Bottom Right Quick Button -->
        <button type="button" class="map_corner_btn" id="btnCornerLocation">
          Use My Location
        </button>
      </div>
    </div>
  </div>
</section>

<!-- Location Detection Script & Video Modal Trigger -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var statusText = document.getElementById('locationStatusText');
  var hiddenLoc = document.getElementById('homeContactLocation');
  var locInput = document.getElementById('homeContactLocationInput');
  var btnInputDetect = document.getElementById('btnInputDetectLoc');
  var btnDetect = document.getElementById('btnDetectLocation');
  var btnCorner = document.getElementById('btnCornerLocation');
  var openVideoBtn = document.getElementById('openPromoVideo');

  var defaultPlaceholder = locInput ? locInput.placeholder : 'Enter or detect your location...';

  function fillLocation(locValue) {
    if (locInput) {
      locInput.value = locValue;
      locInput.placeholder = defaultPlaceholder;
      locInput.classList.add('is-valid');
      // Dispatch input event so any existing listeners or validation notice the change
      locInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (hiddenLoc) {
      hiddenLoc.value = locValue;
    }
    if (statusText) {
      statusText.innerHTML = '<span style="color: #10b981;"><i class="fa fa-check-circle"></i></span> Location Set: ' + locValue;
    }
    if (btnInputDetect) {
      btnInputDetect.disabled = false;
      btnInputDetect.classList.add('is_detected');
      btnInputDetect.innerHTML = '<i class="fa fa-check"></i> <span>Detected</span>';
    }
  }

  function resetDetectButton() {
    if (btnInputDetect) {
      btnInputDetect.disabled = false;
      btnInputDetect.classList.remove('is_detected');
      btnInputDetect.innerHTML = '<i class="fa fa-crosshairs"></i> <span>Auto-Detect</span>';
    }
    if (locInput) {
      locInput.placeholder = defaultPlaceholder;
    }
    if (statusText && (!locInput || !locInput.value.trim())) {
      statusText.innerHTML = '<i class="fa fa-compass" style="color: #f25b29;"></i> No location selected yet.';
    }
  }

  function handleLocationDetect() {
    // 1. Check geolocation in navigator and window.isSecureContext
    if (!('geolocation' in navigator)) {
      alert('Geolocation is not supported by your browser. Please type your location or pick a Quick Area.');
      return;
    }
    if (!window.isSecureContext) {
      alert('Location access requires a secure connection (HTTPS or localhost). Please type your location or pick a Quick Area.');
      return;
    }

    // 5. Disable the button while detecting
    if (btnInputDetect) {
      btnInputDetect.disabled = true;
      btnInputDetect.classList.remove('is_detected');
      btnInputDetect.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span>Detecting...</span>';
    }
    if (locInput) {
      locInput.placeholder = 'Detecting exact GPS location...';
    }
    if (statusText) {
      statusText.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Detecting exact GPS location...';
    }

    // 2. Call navigator.geolocation.getCurrentPosition with required parameters
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var lat = pos.coords.latitude;
        var lon = pos.coords.longitude;
        var fallbackCoords = lat.toFixed(4) + ', ' + lon.toFixed(4);

        // 3. Convert coordinates to area name using reverse geocoding
        var nominatimUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&zoom=14&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon);

        fetch(nominatimUrl)
          .then(function (res) {
            if (!res.ok) {
              throw new Error('Reverse geocoding network response failed: ' + res.status);
            }
            return res.json();
          })
          .then(function (data) {
            var quickAreas = ['Nadehi', 'Kashipur', 'Jaspur', 'Rudrapur', 'Haldwani', 'Dehradun'];
            var fullSearchText = (data.display_name || '') + ' ' + JSON.stringify(data.address || {});
            var matchedQuickArea = '';

            for (var i = 0; i < quickAreas.length; i++) {
              var areaName = quickAreas[i];
              var regex = new RegExp('\\b' + areaName + '\\b', 'i');
              if (regex.test(fullSearchText)) {
                matchedQuickArea = areaName;
                break;
              }
            }

            if (matchedQuickArea) {
              // Fill exactly that name if matched
              fillLocation(matchedQuickArea);
            } else {
              // Otherwise fill "suburb/village/town/city, district"
              var addr = data.address || {};
              var locality = addr.suburb || addr.village || addr.town || addr.city || addr.neighbourhood || addr.hamlet || '';
              var district = addr.state_district || addr.district || addr.county || '';

              var resolvedName = '';
              if (locality && district && locality.toLowerCase() !== district.toLowerCase()) {
                resolvedName = locality + ', ' + district;
              } else if (locality) {
                resolvedName = locality;
              } else if (district) {
                resolvedName = district;
              } else if (data.name) {
                resolvedName = data.name;
              } else {
                resolvedName = fallbackCoords;
              }

              fillLocation(resolvedName);
            }
          })
          .catch(function (geoErr) {
            console.error('Reverse geocoding error:', geoErr);
            // If the geocoding request fails, fill "LAT, LON" (4 decimals) so the form can still be submitted
            fillLocation(fallbackCoords);
          });
      },
      function (err) {
        // 7. Log the error object with console.error so user can debug it
        console.error('Geolocation error:', err);

        // 4 & 5. Reset button, placeholder, and re-enable button
        resetDetectButton();

        // 4. Specific message by error code
        var errorMsg = 'Location request failed. Please try again, or pick a Quick Area.';
        if (err && err.code === 1) {
          errorMsg = 'Location permission is blocked. Click the lock icon next to the URL, set Location to Allow, then reload. Or pick a Quick Area.';
        } else if (err && err.code === 2) {
          errorMsg = 'Your device could not find its location. Turn on Location services, or pick a Quick Area.';
        } else if (err && err.code === 3) {
          errorMsg = 'Location request timed out. Please try again, or pick a Quick Area.';
        }
        alert(errorMsg);
      },
      { enableHighAccuracy: false, timeout: 15000, maximumAge: 300000 }
    );
  }

  // 6. Quick Area buttons must keep working exactly as they do now
  document.querySelectorAll('.loc_chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      var chosenArea = this.getAttribute('data-loc');
      if (locInput) {
        locInput.value = chosenArea;
        locInput.placeholder = defaultPlaceholder;
        locInput.classList.add('is-valid');
        locInput.dispatchEvent(new Event('input', { bubbles: true }));
        locInput.focus();
      }
      if (hiddenLoc) {
        hiddenLoc.value = chosenArea;
      }
      if (statusText) {
        statusText.innerHTML = '<span style="color: #10b981;"><i class="fa fa-check-circle"></i></span> Location Set: ' + chosenArea;
      }
      if (btnInputDetect) {
        btnInputDetect.disabled = false;
        btnInputDetect.classList.remove('is_detected');
        btnInputDetect.innerHTML = '<i class="fa fa-crosshairs"></i> <span>Auto-Detect</span>';
      }
    });
  });

  // Two-way sync: If user manually changes the input, update map status in real-time
  if (locInput) {
    locInput.addEventListener('input', function () {
      var val = this.value.trim();
      if (statusText) {
        if (val) {
          statusText.innerHTML = '<span style="color: #f25b29;"><i class="fa fa-map-marker"></i></span> ' + val;
        } else {
          statusText.textContent = 'No location selected yet.';
        }
      }
      if (hiddenLoc) {
        hiddenLoc.value = val;
      }
    });
  }

  // Button handlers
  if (btnInputDetect) {
    btnInputDetect.addEventListener('click', function () {
      handleLocationDetect();
    });
  }
  if (btnDetect) {
    btnDetect.addEventListener('click', function () {
      handleLocationDetect();
      if (locInput) {
        locInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        locInput.focus();
      }
    });
  }
  if (btnCorner) {
    btnCorner.addEventListener('click', function () {
      handleLocationDetect();
      if (locInput) {
        locInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        locInput.focus();
      }
    });
  }

  if (openVideoBtn) {
    openVideoBtn.addEventListener('click', function () {
      window.location.href = '<?= \App\Core\View::url('/services') ?>';
    });
  }

  // =========================================================================
  // Google Reviews Swipeable Carousel (1 Video + 1 Review in view, multi-review swipe)
  // =========================================================================
  var reviewsTrack = document.getElementById('reviewsSliderTrack');
  var reviewsViewport = document.getElementById('reviewsSliderViewport');
  var videoCard = document.getElementById('singlePromoVideoCard');
  var counterPill = document.getElementById('reviewCounterPill');
  var prevReviewBtn = document.getElementById('btnPrevReview');
  var nextReviewBtn = document.getElementById('btnNextReview');
  var reviewDots = document.querySelectorAll('#reviewDotsWrap .slider_dot');
  var totalReviews = reviewDots.length || 5;
  var currentReviewIndex = 0;
  var autoAdvanceTimer = null;

  function setReviewSlide(idx) {
    if (idx < 0) {
      idx = totalReviews - 1;
    } else if (idx >= totalReviews) {
      idx = 0;
    }
    currentReviewIndex = idx;

    if (reviewsTrack) {
      reviewsTrack.style.transform = 'translateX(-' + (currentReviewIndex * 100) + '%)';
    }
    if (counterPill) {
      counterPill.textContent = '0' + (currentReviewIndex + 1) + ' / 0' + totalReviews;
    }
    reviewDots.forEach(function (dot, i) {
      if (i === currentReviewIndex) {
        dot.classList.add('active');
      } else {
        dot.classList.remove('active');
      }
    });
  }

  if (prevReviewBtn) {
    prevReviewBtn.addEventListener('click', function () {
      setReviewSlide(currentReviewIndex - 1);
      resetAutoAdvance();
    });
  }
  if (nextReviewBtn) {
    nextReviewBtn.addEventListener('click', function () {
      setReviewSlide(currentReviewIndex + 1);
      resetAutoAdvance();
    });
  }
  reviewDots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      var slideNum = parseInt(this.getAttribute('data-slide'), 10);
      if (!isNaN(slideNum)) {
        setReviewSlide(slideNum);
        resetAutoAdvance();
      }
    });
  });

  // Touch & Mouse Drag Swiping
  function attachSwipeGesture(element) {
    if (!element) return;
    var startX = 0;
    var currentX = 0;
    var isDragging = false;

    element.addEventListener('touchstart', function (e) {
      if (e.touches && e.touches.length > 0) {
        startX = e.touches[0].clientX;
        currentX = startX;
        isDragging = true;
      }
    }, { passive: true });

    element.addEventListener('touchmove', function (e) {
      if (!isDragging) return;
      if (e.touches && e.touches.length > 0) {
        currentX = e.touches[0].clientX;
      }
    }, { passive: true });

    element.addEventListener('touchend', function () {
      if (!isDragging) return;
      var diffX = startX - currentX;
      var threshold = 40;
      if (Math.abs(diffX) > threshold) {
        if (diffX > 0) {
          // Swipe Left -> Next Review
          setReviewSlide(currentReviewIndex + 1);
        } else {
          // Swipe Right -> Prev Review
          setReviewSlide(currentReviewIndex - 1);
        }
        resetAutoAdvance();
      }
      isDragging = false;
      startX = 0;
      currentX = 0;
    });

    // Mouse drag
    element.addEventListener('mousedown', function (e) {
      startX = e.clientX;
      currentX = startX;
      isDragging = true;
    });

    window.addEventListener('mousemove', function (e) {
      if (isDragging) {
        currentX = e.clientX;
      }
    });

    window.addEventListener('mouseup', function () {
      if (!isDragging) return;
      var diffX = startX - currentX;
      var threshold = 50;
      if (Math.abs(diffX) > threshold) {
        if (diffX > 0) {
          setReviewSlide(currentReviewIndex + 1);
        } else {
          setReviewSlide(currentReviewIndex - 1);
        }
        resetAutoAdvance();
      }
      isDragging = false;
      startX = 0;
      currentX = 0;
    });
  }

  attachSwipeGesture(reviewsViewport);
  attachSwipeGesture(videoCard);

  function startAutoAdvance() {
    stopAutoAdvance();
    autoAdvanceTimer = setInterval(function () {
      setReviewSlide(currentReviewIndex + 1);
    }, 7000);
  }
  function stopAutoAdvance() {
    if (autoAdvanceTimer) {
      clearInterval(autoAdvanceTimer);
      autoAdvanceTimer = null;
    }
  }
  function resetAutoAdvance() {
    stopAutoAdvance();
    startAutoAdvance();
  }

  startAutoAdvance();

  if (reviewsViewport) {
    reviewsViewport.addEventListener('mouseenter', stopAutoAdvance);
    reviewsViewport.addEventListener('mouseleave', startAutoAdvance);
  }

  // FAQ Accordion Toggle Interaction
  document.querySelectorAll('.faq_pill_card').forEach(function (card) {
    card.addEventListener('click', function () {
      var isAlreadyActive = this.classList.contains('active');
      document.querySelectorAll('.faq_pill_card').forEach(function (c) {
        c.classList.remove('active');
      });
      if (!isAlreadyActive) {
        this.classList.add('active');
      }
    });
  });
});
</script>

<!-- 07. Frequently Asked Questions (2-Column Pill Accordion matching reference) -->
<section class="home_faq_section" id="faqSection">
  <div class="container">
    <div class="faq_header_center">
      <span class="faq_badge_pill">HELP & COMMON QUESTIONS</span>
      <h2 class="faq_main_title">Frequently Asked <span class="faq_orange_word">Questions</span></h2>
      <p class="faq_sub_title">Everything you need to know about booking, payments, service warranty, and technician verification.</p>
    </div>

    <!-- 2-Column Grid (4 items left, 4 items right) -->
    <div class="faq_two_col_grid">
      <!-- Left Column -->
      <div class="faq_col_stack">
        <!-- 01 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">How do I book a home service with Refixel?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">Booking takes under 60 seconds. Simply select your service, choose a convenient date and time slot, confirm your location, and pre-book with ₹50. A verified technician will arrive promptly at your doorstep.</p>
          </div>
        </div>

        <!-- 02 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">Are Refixel service technicians verified and background-checked?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">Yes, 100%. Every Refixel technician undergoes an extensive 3-tier verification: government ID validation, police background clearance, and trade skill certifications before visiting your home.</p>
          </div>
        </div>

        <!-- 03 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">How does pricing work? Are there any hidden fees?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">We maintain absolute pricing transparency. All rates and service scope are shown upfront with zero hidden charges. You pre-book for ₹50, and the remaining amount is paid only after complete job satisfaction.</p>
          </div>
        </div>

        <!-- 04 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">What payment methods do you accept?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">We accept all major payment methods including UPI (Google Pay, PhonePe, Paytm), Credit & Debit cards, Net Banking, and direct cash to the service technician after the job is completed.</p>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="faq_col_stack">
        <!-- 05 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">Can I reschedule or cancel my booking?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">Yes! You can reschedule your date/time slot or cancel your booking anytime up to 2 hours prior to your scheduled appointment with zero penalty directly from your account or phone support.</p>
          </div>
        </div>

        <!-- 06 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">Is there a service warranty or satisfaction guarantee?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">Yes! All our repair and maintenance services are covered by an unconditional 30-day Refixel Service Guarantee. If any issue reoccurs, we will re-work it completely free of charge within 48 hours.</p>
          </div>
        </div>

        <!-- 07 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">How quickly can a technician arrive at my home?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">We provide same-day express service across our verified pincodes. For urgent electrical, plumbing, or AC emergencies, technicians can arrive within 60 to 90 minutes of booking confirmation.</p>
          </div>
        </div>

        <!-- 08 -->
        <div class="faq_pill_card">
          <div class="faq_pill_header">
            <h4 class="faq_pill_question">How can I register as a Refixel Service Partner?</h4>
            <span class="faq_circle_plus"><i class="fa fa-plus"></i></span>
          </div>
          <div class="faq_pill_body">
            <p class="faq_pill_answer">Experienced technicians and professionals can register by clicking "Become a Service Partner" on our website or calling our partner helpline. We provide continuous bookings, verified clients, and prompt payouts.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 08. Pre-Booking Section ("Secure your service with ₹50.") -->
<section class="home_prebooking_section" id="prebookingSection">
  <div class="container">
    <div class="row align-items-center g-5">
      <!-- Left: Headline, Subtitle, Price & CTA -->
      <div class="col-lg-6">
        <span class="prebook_eyebrow"> PRE-BOOKING</span>
        <h2 class="prebook_title">Secure your service with<br>₹50.</h2>
        <p class="prebook_sub">Pre-book your request, confirm the details and get your service scheduled.</p>
        
        <div class="prebook_price_wrap">
          <div class="prebook_price_amount">₹50</div>
          <div class="prebook_price_label">Pre-booking amount</div>
        </div>

        <a href="<?= \App\Core\View::url('/services') ?>" class="btn_prebook_cta">
          Pre-book for ₹50 <i class="fa fa-arrow-right"></i>
        </a>
      </div>

      <!-- Right: 4 Step Cards -->
      <div class="col-lg-6">
        <div class="prebook_steps_list">
          <div class="prebook_step_card">
            <span class="prebook_step_num">1</span>
            <p class="prebook_step_text">Select service</p>
          </div>
          <div class="prebook_step_card">
            <span class="prebook_step_num">2</span>
            <p class="prebook_step_text">Choose date & time</p>
          </div>
          <div class="prebook_step_card">
            <span class="prebook_step_num">3</span>
            <p class="prebook_step_text">Confirm details & location</p>
          </div>
          <div class="prebook_step_card">
            <span class="prebook_step_num">4</span>
            <p class="prebook_step_text">Pay ₹50 and receive confirmation</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 09. Final CTA Section ("Multiple Home Services. One Trusted Brand.") -->
<section class="home_final_cta_section" id="finalCtaSection">
  <div class="container">
    <div class="final_cta_card">
      <span class="final_cta_eyebrow">FINAL CTA</span>
      <h2 class="final_cta_title">
        <span class="white_line">Multiple Home Services.</span>
        <span class="orange_line">One Trusted Brand.</span>
      </h2>
      <p class="final_cta_sub">Professional. Reliable. At Your Doorstep.</p>
      <a href="<?= \App\Core\View::url('/services') ?>" class="btn_final_cta">
        Book Your Service <i class="fa fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>



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

