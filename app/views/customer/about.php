<!-- Load Custom Styles for New About Page UI -->
<link rel="stylesheet" href="<?= \App\Core\View::asset('css/about-new.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/about-new.css') ?>" />

<div class="about-page-wrapper">
  <!-- Hero Story Section -->
  <div class="hero-story-container mb-5">
    <section class="hero-story-section">
      <!-- Wide Background Team Visual (extends leftward behind text with soft gradient fade) -->
      <div class="hero-wide-image-backdrop" aria-hidden="true">
        <img 
          src="<?= \App\Core\View::asset('img/about/aboutbg.png') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/img/about/aboutbg.png') ?>" 
          alt="REFIXEL Professional Verified Technicians and Service Van" 
          class="hero-wide-team-img"
          loading="eager"
          fetchpriority="high"
          decoding="async"
        >
        <div class="hero-image-soft-gradient"></div>
      </div>

      <div class="container hero-content-relative">
        <div class="row align-items-center">
          <!-- Left: Text Content -->
          <div class="col-lg-6 col-md-12 hero-text-col">
            <span class="section-eyebrow">— OUR STORY</span>
            <h1 class="hero-heading">
              <span class="hero-about-prefix">About</span> <span class="brand-highlight">REFIXEL</span>
              <span class="sub-line-navy">Professional Home Services.</span>
              <span class="sub-line-orange">Simplified for Every Home.</span>
            </h1>
            <p class="hero-description">
              REFIXEL is a modern home services platform connecting customers with verified professionals for all home maintenance and repair needs. Our mission is to make home services reliable, affordable, and hassle-free at your doorstep.
            </p>

            <div class="d-flex flex-wrap align-items-center" style="gap: 14px;">
              <a href="<?= \App\Core\View::url('/services') ?>" class="btn-refixel-primary">
                Book a Service <i class="fa fa-arrow-right ml-2" style="font-size: 13px;"></i>
              </a>
              <a href="#ourJourney" class="btn-refixel-outline">
                <i class="fa fa-th-large mr-2" style="font-size: 13px;"></i> Explore More
              </a>
            </div>

            <!-- Trust Badges Under Hero Buttons -->
            <div class="hero-trust-pills">
              <div class="trust-pill-item">
                <span class="pill-icon"><i class="fa fa-shield"></i></span>
                <span>Trusted Professionals</span>
              </div>
              <div class="trust-pill-item">
                <span class="pill-icon"><i class="fa fa-check-circle-o"></i></span>
                <span>Quality Service</span>
              </div>
              <div class="trust-pill-item">
                <span class="pill-icon"><i class="fa fa-tag"></i></span>
                <span>Affordable Pricing</span>
              </div>
              <div class="trust-pill-item">
                <span class="pill-icon"><i class="fa fa-headphones"></i></span>
                <span>On-Time Support</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- 3. Our Journey Section -->
  <section class="our-journey-section" id="ourJourney">
    <div class="container">
      <div class="row align-items-center">
        <!-- Left: Storefront Photo -->
        <div class="col-lg-5 col-md-12 mb-4 mb-lg-0">
          <div class="journey-img-wrap shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; max-width: 440px; margin: 0 auto;">
            <img 
              src="<?= \App\Core\View::asset('img/refixel-storefront.jpg') ?>" 
              alt="REFIXEL Home Services Storefront & Experience Center" 
              class="img-fluid w-100"
              style="border-radius: 20px; display: block; object-fit: cover; width: 100%; height: 320px;"
              loading="lazy"
              decoding="async"
            >
          </div>
        </div>

        <!-- Right: Journey Story & Numbers -->
        <div class="col-lg-7 col-md-12">
          <div class="journey-content-wrap">
            <span class="section-eyebrow">OUR JOURNEY</span>
            <h2 class="section-title-large mb-3">
              More Than a Service.<br>
              <span class="text-refixel-orange">A Trusted Home Partner.</span>
            </h2>
            <p class="journey-description" style="font-size: 17.5px; line-height: 1.8; color: #1e293b; font-weight: 500;">
              REFIXEL was founded with a simple idea — to make home services reliable, professional, and stress-free for every household. We bridge the gap between customers and verified service professionals, ensuring high-quality service, transparent pricing, and complete peace of mind.
            </p>

            <!-- 3 Stat Metrics -->
            <div class="journey-stats-row">
              <div class="journey-stat-card">
                <div class="stat-icon"><i class="fa fa-home"></i></div>
                <div>
                  <div class="stat-number"><?= \App\Core\View::e(\App\Models\Setting::get('stat_homes_cleaned', '5,000+')) ?></div>
                  <div class="stat-label"><?= \App\Core\View::e(\App\Models\Setting::get('stat_homes_note', 'Homes Served')) ?></div>
                </div>
              </div>
              <div class="journey-stat-card">
                <div class="stat-icon"><i class="fa fa-handshake-o"></i></div>
                <div>
                  <div class="stat-number"><?= \App\Core\View::e(\App\Models\Setting::get('stat_service_partners', '60+')) ?></div>
                  <div class="stat-label"><?= \App\Core\View::e(\App\Models\Setting::get('stat_partners_note', 'Service Partners')) ?></div>
                </div>
              </div>
              <div class="journey-stat-card">
                <div class="stat-icon"><i class="fa fa-certificate"></i></div>
                <div>
                  <div class="stat-number"><?= \App\Core\View::e(\App\Models\Setting::get('stat_verified_pros', '400+')) ?></div>
                  <div class="stat-label"><?= \App\Core\View::e(\App\Models\Setting::get('stat_pros_note', 'Verified Professionals')) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. Meet The Founder Section -->
  <section class="meet-founder-section">
    <div class="container">
      <div class="row align-items-center">
        <!-- Left: Founder Bio & Quote (Comes second on mobile, first on desktop) -->
        <div class="col-lg-6 col-md-12 order-2 order-lg-1 mb-4 mb-lg-0 founder-text-col">
          <span class="section-eyebrow">MEET THE FOUNDER</span>
          <h2 class="founder-title">Aakash Kumar</h2>
          <span class="founder-designation">Founder, REFIXEL</span>
          <p class="founder-bio">
            Aakash Kumar, the founder of REFIXEL, is dedicated to building a reliable and customer-focused home services platform. With a strong focus on quality, transparency, and customer satisfaction, his vision is to make professional home services easily accessible for every home.
          </p>

          <div class="founder-quote-box mb-4">
            <div class="quote-icon"><i class="fa fa-quote-left"></i></div>
            <p>“Our goal is to make every home cleaner, safer and more comfortable with professional services.”</p>
            <div class="quote-author">— Aakash Kumar</div>
          </div>

          <!-- Founder Social Links -->
          <div class="founder-social-links">
            <span class="founder-social-label">Follow REFIXEL:</span>
            <div class="founder-social-icons">
              <a href="https://www.linkedin.com/in/lets-refixel-3aab5a43b?utm_source=share_via&amp;utm_content=profile&amp;utm_medium=member_android" target="_blank" rel="noopener noreferrer" class="founder-social-btn btn-linkedin" title="Follow REFIXEL on LinkedIn" aria-label="LinkedIn">
                <i class="fa fa-linkedin"></i>
              </a>
              <a href="https://www.instagram.com/letsrefixel?stkn=ajJtc20zcHRyM2Q3" target="_blank" rel="noopener noreferrer" class="founder-social-btn btn-instagram" title="Follow REFIXEL on Instagram" aria-label="Instagram">
                <i class="fa fa-instagram"></i>
              </a>
              <a href="https://x.com/letsrefixel" target="_blank" rel="noopener noreferrer" class="founder-social-btn btn-x" title="Follow REFIXEL on X" aria-label="X">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="display:inline-block; vertical-align:-2px;"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              </a>
              <a href="https://www.facebook.com/share/1GU16Dtfcr/" target="_blank" rel="noopener noreferrer" class="founder-social-btn btn-facebook" title="Follow REFIXEL on Facebook" aria-label="Facebook">
                <i class="fa fa-facebook"></i>
              </a>
              <a href="https://youtube.com/@letsrefixel?si=Xq0LZUFrv_yzlM5C" target="_blank" rel="noopener noreferrer" class="founder-social-btn btn-youtube" title="Subscribe to REFIXEL on YouTube" aria-label="YouTube">
                <i class="fa fa-youtube-play"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- Right: Founder Photo (Comes first on mobile, second on desktop) -->
        <div class="col-lg-6 col-md-12 order-1 order-lg-2 mb-4 mb-lg-0 founder-photo-col">
          <div class="founder-photo-wrap shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
            <img 
              src="<?= \App\Core\View::asset('img/founder-aakash-kumar.jpg') ?>" 
              alt="Aakash Kumar, Founder of REFIXEL" 
              class="img-fluid w-100"
              style="border-radius: 20px; display: block; object-fit: cover;"
              loading="lazy"
              decoding="async"
            >
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. Our Mission & Values Section -->
  <section class="mission-values-section">
    <div class="container">
      <span class="section-eyebrow">OUR MISSION & VALUES</span>
      <h2 class="section-title-large mb-3">Our Mission & Values</h2>
      <p class="mission-intro">
        At REFIXEL, our mission is to deliver professional home services that create cleaner, healthier, and more comfortable spaces through skilled professionals, advanced tools, and safe practices. We are committed to maintaining the highest standards of quality, transparency, reliability, and customer satisfaction.
      </p>

      <div class="values-grid">
        <!-- 1. Trust -->
        <div class="value-card">
          <div class="value-icon-circle"><i class="fa fa-shield"></i></div>
          <h5>Trust</h5>
          <p class="card-desc-text">Building long-term relationships through honesty and reliability.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 2. Professionalism -->
        <div class="value-card">
          <div class="value-icon-circle"><i class="fa fa-user-circle-o"></i></div>
          <h5>Professionalism</h5>
          <p class="card-desc-text">Verified and skilled professionals for every service.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 3. Quality -->
        <div class="value-card">
          <div class="value-icon-circle"><i class="fa fa-cog"></i></div>
          <h5>Quality</h5>
          <p class="card-desc-text">Consistent and high-quality service every time.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 4. Innovation -->
        <div class="value-card">
          <div class="value-icon-circle"><i class="fa fa-lightbulb-o"></i></div>
          <h5>Innovation</h5>
          <p class="card-desc-text">Using modern tools and techniques for better results.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 5. Customer First -->
        <div class="value-card">
          <div class="value-icon-circle"><i class="fa fa-heart"></i></div>
          <h5>Customer First</h5>
          <p class="card-desc-text">Your satisfaction is always our priority.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. Why Choose Us Section -->
  <section class="why-choose-section" id="whyChoose">
    <div class="container">
      <span class="section-eyebrow" style="color: rgba(255, 64, 0, 1); font-size:24px;">WHY CHOOSE REFIXEL</span>
      <h2 class="section-title-large">Why Choose Us?</h2>

      <div class="why-choose-grid">
        <!-- 1. Verified Professionals -->
        <div class="why-card">
          <div class="why-icon-circle"><i class="fa fa-shield"></i></div>
          <h5>Verified Professionals</h5>
          <p class="card-desc-text">Every service partner is verified with background checks and skill certifications.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 2. Safe & Hygienic -->
        <div class="why-card">
          <div class="why-icon-circle"><i class="fa fa-leaf"></i></div>
          <h5>Safe & Hygienic</h5>
          <p class="card-desc-text">We use safe cleaning methods and high-quality products for a healthier environment.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 3. Advanced Equipment -->
        <div class="why-card">
          <div class="why-icon-circle"><i class="fa fa-cogs"></i></div>
          <h5>Advanced Equipment</h5>
          <p class="card-desc-text">From deep cleaning machines to specialized equipment for efficient and professional results.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
        <!-- 4. Customer Trusted -->
        <div class="why-card">
          <div class="why-icon-circle"><i class="fa fa-thumbs-up"></i></div>
          <h5>Customer Trusted</h5>
          <p class="card-desc-text">Transparent pricing, reliable service, and complete customer satisfaction.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
      </div>
    </div>
  </section>

  <!-- 7. Our Professional Process Section -->
  <section class="process-section">
    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">OUR PROCESS</span>
        <h2 class="section-title-large">Our Professional Process</h2>
      </div>

      <div class="process-grid">
        <!-- Step 01 -->
        <div class="process-step-card">
          <div class="step-badge">01</div>
          <div class="step-icon-wrap"><i class="fa fa-clipboard"></i></div>
          <h5>Service Inspection &<br>Requirement Analysis</h5>
          <p class="card-desc-text">We understand your requirements, inspect the area, and suggest the right cleaning or maintenance solution.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>

        <!-- Step 02 -->
        <div class="process-step-card">
          <div class="step-badge">02</div>
          <div class="step-icon-wrap"><i class="fa fa-wrench"></i></div>
          <h5>Professional Equipment &<br>Cleaning Preparation</h5>
          <p class="card-desc-text">Our team prepares the space with advanced tools, safe products, and proper safety measures.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>

        <!-- Step 03 -->
        <div class="process-step-card">
          <div class="step-badge">03</div>
          <div class="step-icon-wrap"><i class="fa fa-magic"></i></div>
          <h5>Deep Cleaning &<br>Sanitization Execution</h5>
          <p class="card-desc-text">We perform detailed cleaning using modern techniques to remove dust, stains, bacteria, and hidden dirt.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>

        <!-- Step 04 -->
        <div class="process-step-card">
          <div class="step-badge">04</div>
          <div class="step-icon-wrap"><i class="fa fa-check-circle"></i></div>
          <h5>Final Quality Inspection<br>& Customer Satisfaction</h5>
          <p class="card-desc-text">We conduct a final quality check to ensure everything meets our standards and guarantee your complete satisfaction.</p>
          <button type="button" class="btn-card-toggle" aria-expanded="false">View more <i class="fa fa-angle-down"></i></button>
        </div>
      </div>
    </div>
  </section>

  <!-- 8. Our Clients / Testimonials Section -->
  <!-- 8. Our Clients / Testimonials Section -->
  <section class="testimonials-section" id="clientsSection" style="scroll-margin-top: 100px;">
    <div class="container">
      <div class="testimonial-header-row">
        <div>
          <span class="section-eyebrow">OUR CLIENTS</span>
          <h2 class="section-title-large mb-0">
            Trusted by Thousands of People & Companies
            <span class="badge badge-light border ml-2" style="font-size: 15px; font-weight: 700; color: #f25b29; vertical-align: middle;">
              <i class="fa fa-star text-warning"></i> <?= \App\Core\View::e(\App\Models\Setting::get('stat_rating', '4.9★')) ?>
            </span>
          </h2>
        </div>
        <div class="d-flex align-items-center" style="gap: 14px;">
          <!-- 2 Clickable Left / Right Navigation Buttons -->
          <div class="d-flex align-items-center" style="gap: 8px;">
            <button type="button" class="review-nav-arrow" id="reviewPrevBtn" aria-label="Previous Reviews" title="Previous Reviews">
              <i class="fa fa-chevron-left"></i>
            </button>
            <button type="button" class="review-nav-arrow" id="reviewNextBtn" aria-label="Next Reviews" title="Next Reviews">
              <i class="fa fa-chevron-right"></i>
            </button>
          </div>
          <a href="<?= \App\Core\View::url('/services') ?>" class="btn-refixel-pill-outline">
            View All Reviews <i class="fa fa-arrow-right ml-1"></i>
          </a>
        </div>
      </div>

      <?php
      $googleReviews = [
          [
              'name' => 'mukul singh',
              'time' => '2 months ago',
              'avatar' => 'M',
              'avatar_class' => '',
              'quote' => 'Very good service price is also so reasonable.'
          ],
          [
              'name' => 'Rohit Rai',
              'time' => '3 months ago',
              'avatar' => 'R',
              'avatar_class' => 'avatar-blue',
              'quote' => 'Very good service and positive behaviour. Clean water tank and tap.'
          ],
          [
              'name' => 'shipra bhard',
              'time' => '3 months ago',
              'avatar' => 'S',
              'avatar_class' => 'avatar-pink',
              'quote' => 'Amazing work! My water tank was very dirty, but now it is perfectly clean and the water is crystal clear.'
          ],
          [
              'name' => 'Ld Popnal',
              'time' => '3 months ago',
              'avatar' => 'L',
              'avatar_class' => 'avatar-slate',
              'quote' => 'Very very Excellent service.'
          ],
          [
              'name' => 'Pooja Sharma',
              'time' => '1 month ago',
              'avatar' => 'P',
              'avatar_class' => 'avatar-purple',
              'quote' => 'Booked deep home cleaning for our 3BHK. The staff arrived strictly on time with commercial grade vacuums and left our home spotless. Outstanding experience!'
          ],
          [
              'name' => 'Amit Verma',
              'time' => '2 weeks ago',
              'avatar' => 'A',
              'avatar_class' => 'avatar-orange',
              'quote' => 'Emergency AC repair in Gurgaon handled within 45 mins. Gas refilling and coil cleaning done transparently with warranty receipt. Highly recommended!'
          ],
          [
              'name' => 'Deepak Rawat',
              'time' => '1 month ago',
              'avatar' => 'D',
              'avatar_class' => 'avatar-green',
              'quote' => 'Quick and hassle-free plumbing repair in Kashipur. Fixed the kitchen drainage and tap leakage with genuine spare parts. Very courteous technician.'
          ],
          [
              'name' => 'Neha Gupta',
              'time' => '3 weeks ago',
              'avatar' => 'N',
              'avatar_class' => 'avatar-teal',
              'quote' => 'Got living room painting and modular switch fitting done. Super clean execution, zero mess left on floors. REFIXEL is our go-to home service now.'
          ],
          [
              'name' => 'Vikas Choudhary',
              'time' => '2 months ago',
              'avatar' => 'V',
              'avatar_class' => 'avatar-indigo',
              'quote' => 'Carpenter was extremely skilled and polite. Repaired our master bedroom wardrobe hydraulic hinges and aligned doors smoothly in one visit.'
          ],
          [
              'name' => 'Saurabh Joshi',
              'time' => '3 weeks ago',
              'avatar' => 'S',
              'avatar_class' => 'avatar-cyan',
              'quote' => 'Sofa and mattress dry cleaning was top notch. Removed stubborn tea stains that others could not clean. Affordable rates and genuine professionals.'
          ]
      ];
      ?>

      <!-- Infinite Reviews Carousel Viewport -->
      <div class="testimonials-carousel-viewport" id="reviewsViewport">
        <div class="testimonials-carousel-track" id="reviewsTrack">
          <?php for ($set = 0; $set < 2; $set++): ?>
            <?php foreach ($googleReviews as $r): ?>
              <div class="testimonial-card">
                <div>
                  <div class="client-meta">
                    <div class="client-info">
                      <div class="client-avatar-circle <?= $r['avatar_class'] ?>"><?= $r['avatar'] ?></div>
                      <div>
                        <div class="client-name"><?= \App\Core\View::e($r['name']) ?></div>
                        <div class="client-time"><?= \App\Core\View::e($r['time']) ?></div>
                      </div>
                    </div>
                    <svg class="google-badge-icon" viewBox="0 0 24 24">
                      <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                      <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                      <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                      <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                  </div>
                  <div class="rating-stars-row">
                    <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                  </div>
                  <p class="testimonial-quote">
                    <?= \App\Core\View::e($r['quote']) ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 9. Our Recent Work / Before & After Section -->
  <section class="recent-work-section" id="beforeAfterSection">
    <div class="container">
      <div class="work-header-row flex-column flex-md-row align-items-start align-items-md-end">
        <div>
          <span class="section-eyebrow">BEFORE / AFTER</span>
          <h2 class="section-title-large mb-1">Our Recent Work & Transformations</h2>
          <p class="mb-0" style="font-size: 17px; color: #1e293b; font-weight: 500; line-height: 1.65;">Real results from verified REFIXEL home services — see the difference our professional equipment and expert technicians deliver.</p>
        </div>
        <div class="mt-3 mt-md-0">
          <a href="<?= \App\Core\View::url('/gallery') ?>" class="btn-refixel-pill-outline">
            View All Gallery <i class="fa fa-arrow-right ml-1"></i>
          </a>
        </div>
      </div>

      <!-- Top Controls Row -->
      <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        <span class="text-muted small font-weight-bold">
          <i class="fa fa-sliders mr-1 text-refixel-orange"></i> Select a Service to Highlight Showcase
        </span>
        <!-- Marquee Pause/Play Toggle Button -->
        <div class="d-flex align-items-center" style="gap: 8px;">
          <span class="text-muted small d-none d-md-inline"><i class="fa fa-info-circle mr-1"></i> Hover to pause cards</span>
          <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-1" id="marqueeToggleBtn" style="border-radius: 20px; font-size: 12.5px;">
            <i class="fa fa-pause mr-1" id="marqueeToggleIcon"></i> <span id="marqueeToggleText">Pause</span>
          </button>
        </div>
      </div>

      <!-- Service Filter Buttons Marquee Loop (Single row, loops smoothly, touch scrollable on mobile, centers on click) -->
      <div class="trans-filter-marquee-outer">
        <div class="trans-filter-wrapper-relative">
          <div class="trans-filter-marquee-viewport" id="transFilterViewport" aria-label="Services filter buttons carousel">
            <div class="transformation-filter-nav" id="transFilterTrack">
              <button type="button" class="trans-filter-btn active" data-filter="all">
                <i class="fa fa-th-large mr-1"></i> All Showcases (8)
              </button>
              <button type="button" class="trans-filter-btn" data-filter="room-cleaning">
                <i class="fa fa-sparkles mr-1"></i> Room Cleaning
              </button>
              <button type="button" class="trans-filter-btn" data-filter="pest-control">
                <i class="fa fa-bug mr-1"></i> Pest Control
              </button>
              <button type="button" class="trans-filter-btn" data-filter="sofa-cleaning">
                <i class="fa fa-couch mr-1"></i> Sofa Cleaning
              </button>
              <button type="button" class="trans-filter-btn" data-filter="room-painting">
                <i class="fa fa-paint-brush mr-1"></i> Room Painting
              </button>
              <button type="button" class="trans-filter-btn" data-filter="home-renovation">
                <i class="fa fa-wrench mr-1"></i> Home Renovation
              </button>
              <button type="button" class="trans-filter-btn" data-filter="electrical-repair">
                <i class="fa fa-bolt mr-1"></i> Electrical Repairs
              </button>
              <button type="button" class="trans-filter-btn" data-filter="plumbing-repair">
                <i class="fa fa-wrench mr-1"></i> Plumbing Repairs
              </button>
              <button type="button" class="trans-filter-btn" data-filter="ac-service">
                <i class="fa fa-snowflake-o mr-1"></i> AC Jet Service
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Parent Div: Horizontal Infinite Loop Marquee -->
    <div class="transformation-marquee-wrapper" id="transformationMarquee">
      <div class="transformation-marquee-track" id="transformationTrack">

        <!-- ================= SET 1 (8 Verified Transformation Cards) ================= -->

        <!-- 1. Room Cleaning / Laundry Area Deep Cleaning -->
        <div class="transformation-marquee-card" data-category="room-cleaning">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-clean">
              <i class="fa fa-sparkles"></i> Room Cleaning
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-laundry-cleaning.png') ?>" 
              alt="Before and After Room & Laundry Area Deep Cleaning" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-success font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;">
                  <i class="fa fa-check-circle"></i> Service Completed
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Laundry & Utility Room Deep Cleaning</h4>
              <p>Heavy wall dampness, stained utility sink, cluttered floor and detergent buildup completely eliminated with mechanized scrubbing and eco-friendly descaling.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 4.9</span>
                <span class="text-muted small ml-1">(1,200+ Homes Cleaned)</span>
              </div>
              <a href="<?= \App\Core\View::url('/cleaning-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Room Cleaning <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 2. Pest Control / Cockroach Infestation Eradication -->
        <div class="transformation-marquee-card" data-category="pest-control">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-pest">
              <i class="fa fa-bug"></i> Pest Control
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-pest-control.png') ?>" 
              alt="Before and After Kitchen Pest Control & Cockroach Eradication" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;">
                  <i class="fa fa-shield"></i> 100% Roach-Free
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Kitchen Under-Counter Pest Eradication</h4>
              <p>Severe cockroach infestation beneath kitchen cabinets eradicated using certified odorless gel-bait technology, crack sealing, and deep sanitization.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 5.0</span>
                <span class="text-muted small ml-1">(90-Day Warranty)</span>
              </div>
              <a href="<?= \App\Core\View::url('/pest-control-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Pest Control <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 3. Sofa Cleaning / Upholstery Deep Cleaning -->
        <div class="transformation-marquee-card" data-category="sofa-cleaning">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-sofa">
              <i class="fa fa-couch"></i> Sofa Cleaning
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-sofa-cleaning.png') ?>" 
              alt="Before and After Fabric Sofa Deep Cleaning" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;">
                  <i class="fa fa-sparkles"></i> Fabric Restored
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Bazpur Road</span>
              </div>
              <h4>Sectional Fabric Sofa Stain Extraction</h4>
              <p>Deep-set grime, beverage spills, sweat marks and dust mites extracted via high-suction mechanized shampooing, restoring original fabric brightness and freshness.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 4.9</span>
                <span class="text-muted small ml-1">(Quick Dry in 2-3 Hrs)</span>
              </div>
              <a href="<?= \App\Core\View::url('/sofa-cleaning-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Sofa Cleaning <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 4. Room Painting / Interior Wall Makeover -->
        <div class="transformation-marquee-card" data-category="room-painting">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-paint">
              <i class="fa fa-paint-brush"></i> Room Painting
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-room-painting.png') ?>" 
              alt="Before and After Living Room Painting Service" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-warning font-weight-bold" style="font-size: 11.5px; padding: 4px 8px; color: #b45309 !important;">
                  <i class="fa fa-paint-brush"></i> Dustless Sanding
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Nirvana Country</span>
              </div>
              <h4>Living Room Wall Painting & Makeover</h4>
              <p>Peeling plaster and rough walls transformed into smooth, luxury washable finish with mechanized dust-free sanding, 2 coats of emulsion, and clean cove lighting.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 4.8</span>
                <span class="text-muted small ml-1">(Laser Measurement)</span>
              </div>
              <a href="<?= \App\Core\View::url('/painting-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Painting <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 5. Home Renovation / Modern TV Feature Wall Carpentry -->
        <div class="transformation-marquee-card" data-category="home-renovation">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-renovation">
              <i class="fa fa-wrench"></i> Home Renovation
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-wall-renovation.png') ?>" 
              alt="Before and After Living Room TV Feature Wall & Carpentry Renovation" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border font-weight-bold" style="font-size: 11.5px; padding: 4px 8px; color: #f25b29;">
                  <i class="fa fa-home"></i> Complete Transformation
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Living Room TV Feature Wall & Carpentry Renovation</h4>
              <p>Chiseled conduit brick wall and construction debris rebuilt into a contemporary luxury media center featuring vertical fluted wood slats, warm LED backlit accents, and floating storage console.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 5.0</span>
                <span class="text-muted small ml-1">(Bespoke Woodwork)</span>
              </div>
              <a href="<?= \App\Core\View::url('/carpenter-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Carpentry & Renovation <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 6. Electrical Repairs / Switchboard & MCB Distribution Panel -->
        <div class="transformation-marquee-card" data-category="electrical-repair">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-electric">
              <i class="fa fa-bolt"></i> Electrical Repairs
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-electrical-repair.png') ?>" 
              alt="Before and After Electrical Repairs & MCB Panel Overhaul" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-warning font-weight-bold" style="font-size: 11.5px; padding: 4px 8px; color: #b45309 !important;">
                  <i class="fa fa-bolt"></i> Insulated & Safe
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Switchboard & MCB Distribution Panel Overhaul</h4>
              <p>Dangerous loose wiring, exposed fuse boxes and chiseled conduits systematically rewired, neatly enclosed in a certified MCB panel, and fitted with sleek modular switchplates.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 5.0</span>
                <span class="text-muted small ml-1">(Certified Electricians)</span>
              </div>
              <a href="<?= \App\Core\View::url('/fan-switchboard-repair-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Electrical Repair <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 7. Plumbing Services / Kitchen Under-Sink Leak Repair -->
        <div class="transformation-marquee-card" data-category="plumbing-repair">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-plumbing">
              <i class="fa fa-wrench"></i> Plumbing Repairs
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-plumbing-repair.png') ?>" 
              alt="Before and After Kitchen Under-Sink Leak Repair & Pipe Fitting" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-info font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;">
                  <i class="fa fa-tint"></i> 100% Leak-Proof
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Kitchen Under-Sink Pipe Leak & Drainage Overhaul</h4>
              <p>Corroded leaking drain pipe and water-damaged cabinet completely fixed with brand new heavy-duty PVC P-trap pipe fitting, watertight seals, and clean sanitized dry storage.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 4.9</span>
                <span class="text-muted small ml-1">(Instant Diagnosis)</span>
              </div>
              <a href="<?= \App\Core\View::url('/tap-leak-repair-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book Plumbing Repair <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- 8. AC Jet Service & Coil Deep Cleaning -->
        <div class="transformation-marquee-card" data-category="ac-service">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-ac">
              <i class="fa fa-snowflake-o"></i> AC Jet Service
            </span>
            <img 
              src="<?= \App\Core\View::asset('img/before-after-ac-service.png') ?>" 
              alt="Before and After Split AC Deep Jet Wash & Coil Cleaning" 
              loading="lazy"
              decoding="async"
            >
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;">
                  <i class="fa fa-snowflake-o"></i> 2X Better Cooling
                </span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Split AC Deep Jet Wash & Coil Cleaning</h4>
              <p>Extreme dust accumulation, blocked airflow and foul odor resolved through high-pressure antibacterial jet flush, restoring instant ice-cool airflow and pure fresh indoor air.</p>
            </div>
            <div class="transformation-meta-row">
              <div>
                <span class="text-warning font-weight-bold">★ 5.0</span>
                <span class="text-muted small ml-1">(Full Coil Jet Clean)</span>
              </div>
              <a href="<?= \App\Core\View::url('/ac-jet-service-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">
                Book AC Service <i class="fa fa-angle-right ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- ================= SET 2 (Cloned for Infinite Loop) ================= -->

        <!-- 1 Clone -->
        <div class="transformation-marquee-card" data-category="room-cleaning" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-clean"><i class="fa fa-sparkles"></i> Room Cleaning</span>
            <img src="<?= \App\Core\View::asset('img/before-after-laundry-cleaning.png') ?>" alt="Room Cleaning" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-success font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;"><i class="fa fa-check-circle"></i> Service Completed</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Laundry & Utility Room Deep Cleaning</h4>
              <p>Heavy wall dampness, stained utility sink, cluttered floor and detergent buildup completely eliminated with mechanized scrubbing and eco-friendly descaling.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 4.9</span> <span class="text-muted small ml-1">(1,200+ Homes Cleaned)</span></div>
              <a href="<?= \App\Core\View::url('/cleaning-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Room Cleaning <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 2 Clone -->
        <div class="transformation-marquee-card" data-category="pest-control" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-pest"><i class="fa fa-bug"></i> Pest Control</span>
            <img src="<?= \App\Core\View::asset('img/before-after-pest-control.png') ?>" alt="Pest Control" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;"><i class="fa fa-shield"></i> 100% Roach-Free</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Kitchen Under-Counter Pest Eradication</h4>
              <p>Severe cockroach infestation beneath kitchen cabinets eradicated using certified odorless gel-bait technology, crack sealing, and deep sanitization.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 5.0</span> <span class="text-muted small ml-1">(90-Day Warranty)</span></div>
              <a href="<?= \App\Core\View::url('/pest-control-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Pest Control <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 3 Clone -->
        <div class="transformation-marquee-card" data-category="sofa-cleaning" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-sofa"><i class="fa fa-couch"></i> Sofa Cleaning</span>
            <img src="<?= \App\Core\View::asset('img/before-after-sofa-cleaning.png') ?>" alt="Sofa Cleaning" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;"><i class="fa fa-sparkles"></i> Fabric Restored</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Bazpur Road</span>
              </div>
              <h4>Sectional Fabric Sofa Stain Extraction</h4>
              <p>Deep-set grime, beverage spills, sweat marks and dust mites extracted via high-suction mechanized shampooing, restoring original fabric brightness and freshness.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 4.9</span> <span class="text-muted small ml-1">(Quick Dry in 2-3 Hrs)</span></div>
              <a href="<?= \App\Core\View::url('/sofa-cleaning') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Sofa Cleaning <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 4 Clone -->
        <div class="transformation-marquee-card" data-category="room-painting" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-paint"><i class="fa fa-paint-brush"></i> Room Painting</span>
            <img src="<?= \App\Core\View::asset('img/before-after-room-painting.png') ?>" alt="Room Painting" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-warning font-weight-bold" style="font-size: 11.5px; padding: 4px 8px; color: #b45309 !important;"><i class="fa fa-paint-brush"></i> Dustless Sanding</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Nirvana Country</span>
              </div>
              <h4>Living Room Wall Painting & Makeover</h4>
              <p>Peeling plaster and rough walls transformed into smooth, luxury washable finish with mechanized dust-free sanding, 2 coats of emulsion, and clean cove lighting.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 4.8</span> <span class="text-muted small ml-1">(Laser Measurement)</span></div>
              <a href="<?= \App\Core\View::url('/painting-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Painting <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 5 Clone -->
        <div class="transformation-marquee-card" data-category="home-renovation" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-renovation"><i class="fa fa-wrench"></i> Home Renovation</span>
            <img src="<?= \App\Core\View::asset('img/before-after-wall-renovation.png') ?>" alt="TV Feature Wall Renovation" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border font-weight-bold" style="font-size: 11.5px; padding: 4px 8px; color: #f25b29;"><i class="fa fa-home"></i> Complete Transformation</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Living Room TV Feature Wall & Carpentry Renovation</h4>
              <p>Chiseled conduit brick wall and construction debris rebuilt into a contemporary luxury media center featuring vertical fluted wood slats, warm LED backlit accents, and floating storage console.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 5.0</span> <span class="text-muted small ml-1">(Bespoke Woodwork)</span></div>
              <a href="<?= \App\Core\View::url('/carpenter-services') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Carpentry & Renovation <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 6 Clone -->
        <div class="transformation-marquee-card" data-category="electrical-repair" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-electric"><i class="fa fa-bolt"></i> Electrical Repairs</span>
            <img src="<?= \App\Core\View::asset('img/before-after-electrical-repair.png') ?>" alt="Electrical Repair" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-warning font-weight-bold" style="font-size: 11.5px; padding: 4px 8px; color: #b45309 !important;"><i class="fa fa-bolt"></i> Insulated & Safe</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Switchboard & MCB Distribution Panel Overhaul</h4>
              <p>Dangerous loose wiring, exposed fuse boxes and chiseled conduits systematically rewired, neatly enclosed in a certified MCB panel, and fitted with sleek modular switchplates.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 5.0</span> <span class="text-muted small ml-1">(Certified Electricians)</span></div>
              <a href="<?= \App\Core\View::url('/fan-switchboard-repair-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Electrical Repair <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 7 Clone -->
        <div class="transformation-marquee-card" data-category="plumbing-repair" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-plumbing"><i class="fa fa-wrench"></i> Plumbing Repairs</span>
            <img src="<?= \App\Core\View::asset('img/before-after-plumbing-repair.png') ?>" alt="Plumbing Repair" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-info font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;"><i class="fa fa-tint"></i> 100% Leak-Proof</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Kitchen Under-Sink Pipe Leak & Drainage Overhaul</h4>
              <p>Corroded leaking drain pipe and water-damaged cabinet completely fixed with brand new heavy-duty PVC P-trap pipe fitting, watertight seals, and clean sanitized dry storage.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 4.9</span> <span class="text-muted small ml-1">(Instant Diagnosis)</span></div>
              <a href="<?= \App\Core\View::url('/tap-leak-repair-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book Plumbing Repair <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

        <!-- 8 Clone -->
        <div class="transformation-marquee-card" data-category="ac-service" aria-hidden="true">
          <div class="transformation-media-wrap">
            <span class="transformation-category-badge badge-ac"><i class="fa fa-snowflake-o"></i> AC Jet Service</span>
            <img src="<?= \App\Core\View::asset('img/before-after-ac-service.png') ?>" alt="AC Jet Service" loading="lazy" decoding="async">
          </div>
          <div class="transformation-body">
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 11.5px; padding: 4px 8px;"><i class="fa fa-snowflake-o"></i> 2X Better Cooling</span>
                <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
              </div>
              <h4>Split AC Deep Jet Wash & Coil Cleaning</h4>
              <p>Extreme dust accumulation, blocked airflow and foul odor resolved through high-pressure antibacterial jet flush, restoring instant ice-cool airflow and pure fresh indoor air.</p>
            </div>
            <div class="transformation-meta-row">
              <div><span class="text-warning font-weight-bold">★ 5.0</span> <span class="text-muted small ml-1">(Full Coil Jet Clean)</span></div>
              <a href="<?= \App\Core\View::url('/ac-jet-service-in-kashipur') ?>" class="font-weight-bold text-decoration-none" style="color: #f25b29;">Book AC Service <i class="fa fa-angle-right ml-1"></i></a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Interactive Controls for Infinite Marquee -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var track = document.getElementById('transformationTrack');
    var marquee = document.getElementById('transformationMarquee');
    var toggleBtn = document.getElementById('marqueeToggleBtn');
    var toggleIcon = document.getElementById('marqueeToggleIcon');
    var toggleText = document.getElementById('marqueeToggleText');
    var filterBtns = document.querySelectorAll('.trans-filter-btn');
    var allCards = document.querySelectorAll('.transformation-marquee-card');
    var isManuallyPaused = false;

    // Toggle Play / Pause
    if (toggleBtn && track) {
      toggleBtn.addEventListener('click', function() {
        isManuallyPaused = !isManuallyPaused;
        if (isManuallyPaused) {
          track.classList.add('is-paused');
          toggleIcon.className = 'fa fa-play mr-1';
          toggleText.textContent = 'Resume';
        } else {
          track.classList.remove('is-paused');
          toggleIcon.className = 'fa fa-pause mr-1';
          toggleText.textContent = 'Pause';
        }
      });
    }

    // =========================================================
    // Service Filter Pills Infinite Marquee Loop & Center Logic
    // =========================================================
    (function() {
      var fViewport = document.getElementById('transFilterViewport');
      var fTrack = document.getElementById('transFilterTrack');
      if (!fViewport || !fTrack) return;

      // Duplicate buttons once for seamless infinite loop wrap
      var origBtns = Array.from(fTrack.children);
      origBtns.forEach(function(b) {
        var clone = b.cloneNode(true);
        clone.setAttribute('data-clone', 'true');
        fTrack.appendChild(clone);
      });

      var isFPaused = false;
      var fSpeed = 0.55; // Gentle smooth px/frame
      var fAnimId = null;
      var resumeTimer = null;
      var isMouseDown = false;
      var mouseStartX = 0;
      var scrollLeftStart = 0;
      var hasDragged = false;

      function getFHalfWidth() {
        return fTrack.scrollWidth / 2;
      }

      function startFilterMarquee() {
        function tick() {
          if (!isFPaused) {
            fViewport.scrollLeft += fSpeed;
            var half = getFHalfWidth();
            if (half > 0 && fViewport.scrollLeft >= half) {
              fViewport.scrollLeft -= half;
            }
          }
          fAnimId = requestAnimationFrame(tick);
        }
        fAnimId = requestAnimationFrame(tick);
      }

      setTimeout(startFilterMarquee, 200);

      // Wrap-around guard on manual touch/mouse scroll
      fViewport.addEventListener('scroll', function() {
        var half = getFHalfWidth();
        if (half > 0) {
          if (fViewport.scrollLeft <= 0) {
            fViewport.scrollLeft += half;
          } else if (fViewport.scrollLeft >= half * 1.96) {
            fViewport.scrollLeft -= half;
          }
        }
      }, { passive: true });

      // Touch events (Mobile manual scrolling)
      fViewport.addEventListener('touchstart', function() {
        isFPaused = true;
        clearTimeout(resumeTimer);
      }, { passive: true });

      fViewport.addEventListener('touchend', function() {
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(function() {
          isFPaused = false;
        }, 3000);
      });

      // Desktop hover pause
      fViewport.addEventListener('mouseenter', function() {
        if (!isMouseDown) isFPaused = true;
      });
      fViewport.addEventListener('mouseleave', function() {
        if (!isMouseDown) isFPaused = false;
      });

      // Desktop mouse drag scrolling
      fViewport.addEventListener('mousedown', function(e) {
        isMouseDown = true;
        isFPaused = true;
        hasDragged = false;
        mouseStartX = e.pageX - fViewport.offsetLeft;
        scrollLeftStart = fViewport.scrollLeft;
        fViewport.style.cursor = 'grabbing';
      });

      window.addEventListener('mouseup', function() {
        if (isMouseDown) {
          isMouseDown = false;
          fViewport.style.cursor = 'grab';
          clearTimeout(resumeTimer);
          resumeTimer = setTimeout(function() {
            isFPaused = false;
          }, 3000);
        }
      });

      fViewport.addEventListener('mousemove', function(e) {
        if (!isMouseDown) return;
        var x = e.pageX - fViewport.offsetLeft;
        var walk = x - mouseStartX;
        if (Math.abs(walk) > 4) {
          hasDragged = true;
        }
        fViewport.scrollLeft = scrollLeftStart - walk;
      });

      // Click: smoothly center clicked button & activate showcase card
      fViewport.addEventListener('click', function(e) {
        var btn = e.target.closest('.trans-filter-btn');
        if (!btn || hasDragged) return;

        var cat = btn.getAttribute('data-filter');

        // Sync active state across original and cloned buttons
        fViewport.querySelectorAll('.trans-filter-btn').forEach(function(b) {
          if (b.getAttribute('data-filter') === cat) {
            b.classList.add('active');
          } else {
            b.classList.remove('active');
          }
        });

        // Smoothly center the clicked button in the viewport
        var half = getFHalfWidth();
        var targetScroll = btn.offsetLeft - (fViewport.clientWidth / 2) + (btn.offsetWidth / 2);
        if (targetScroll < 0 && half > 0) targetScroll += half;
        fViewport.scrollTo({
          left: targetScroll,
          behavior: 'smooth'
        });

        // Pause loop temporarily so user sees centered item
        isFPaused = true;
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(function() {
          isFPaused = false;
        }, 4000);

        // Highlight/filter the transformation showcase cards below
        allCards.forEach(function(card) {
          card.classList.remove('card-highlighted');
        });

        if (cat === 'all') {
          track.classList.remove('is-paused');
          if (toggleIcon && toggleText) {
            toggleIcon.className = 'fa fa-pause mr-1';
            toggleText.textContent = 'Pause';
          }
          isManuallyPaused = false;
        } else {
          track.classList.add('is-paused');
          if (toggleIcon && toggleText) {
            toggleIcon.className = 'fa fa-play mr-1';
            toggleText.textContent = 'Resume';
          }
          isManuallyPaused = true;

          var targetCard = track.querySelector('.transformation-marquee-card[data-category="' + cat + '"]');
          if (targetCard) {
            targetCard.classList.add('card-highlighted');
            targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
          }
        }
      });
    })();

    // Reviews Infinite Carousel & Navigation Controls
    (function() {
      var viewport = document.getElementById('reviewsViewport');
      var track = document.getElementById('reviewsTrack');
      var prevBtn = document.getElementById('reviewPrevBtn');
      var nextBtn = document.getElementById('reviewNextBtn');
      if (!viewport || !track) return;

      var isPaused = false;
      var speed = 0.8; // px per frame
      var animId = null;

      function getHalfWidth() {
        return track.scrollWidth / 2;
      }

      // Initialize scroll position in the center so it can wrap left and right immediately
      setTimeout(function() {
        var half = getHalfWidth();
        if (half > 0) {
          viewport.scrollLeft = half;
        }
        startLoop();
      }, 150);

      function startLoop() {
        function tick() {
          if (!isPaused) {
            // Smooth left to right auto-scroll (cards travel towards right)
            viewport.scrollLeft -= speed;
            var half = getHalfWidth();
            if (half > 0 && viewport.scrollLeft <= 0) {
              viewport.scrollLeft += half;
            }
          }
          animId = requestAnimationFrame(tick);
        }
        animId = requestAnimationFrame(tick);
      }

      // Infinite wrapping guard on manual scrolling or touch drag
      viewport.addEventListener('scroll', function() {
        var half = getHalfWidth();
        if (half > 0) {
          if (viewport.scrollLeft <= 0) {
            viewport.scrollLeft += half;
          } else if (viewport.scrollLeft >= half * 1.96) {
            viewport.scrollLeft -= half;
          }
        }
      }, { passive: true });

      // Pause on mouse hover and touch
      viewport.addEventListener('mouseenter', function() { isPaused = true; });
      viewport.addEventListener('mouseleave', function() { isPaused = false; });
      viewport.addEventListener('touchstart', function() { isPaused = true; }, { passive: true });
      viewport.addEventListener('touchend', function() {
        setTimeout(function() { isPaused = false; }, 1500);
      });

      // Left Arrow Click (Scrolls Left)
      if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
          e.preventDefault();
          isPaused = true;
          viewport.scrollBy({ left: -332, behavior: 'smooth' });
          setTimeout(function() { isPaused = false; }, 2500);
        });
      }

      // Right Arrow Click (Scrolls Right)
      if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
          e.preventDefault();
          isPaused = true;
          viewport.scrollBy({ left: 332, behavior: 'smooth' });
          setTimeout(function() { isPaused = false; }, 2500);
        });
      }
    })();

    /* ── Mobile Card 'View more' description toggle ── */
    document.querySelectorAll('.btn-card-toggle').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var card = this.closest('.why-card, .process-step-card, .value-card');
        if (!card) return;
        var isExpanded = card.classList.contains('is-expanded');
        if (isExpanded) {
          card.classList.remove('is-expanded');
          this.innerHTML = 'View more <i class="fa fa-angle-down"></i>';
          this.setAttribute('aria-expanded', 'false');
        } else {
          card.classList.add('is-expanded');
          this.innerHTML = 'View less <i class="fa fa-angle-up"></i>';
          this.setAttribute('aria-expanded', 'true');
        }
      });
    });
  });
  </script>

  <!-- 10. CTA & Newsletter Banner -->
  <section class="cta-newsletter-section" id="newsletterCta">
    <div class="container">
      <div class="cta-banner-container">
        <div class="row align-items-center">
          <!-- Left: Call to action -->
          <div class="col-lg-7 col-md-12 mb-4 mb-lg-0 cta-content-left">
            <h3 class="cta-title">
              We Do Magic with Reliable, Safe & Affordable Home Services You Can Trust.
            </h3>
            <a href="<?= \App\Core\View::url('/services') ?>" class="btn-refixel-primary" style="padding: 14px 30px; font-size: 16px;">
              Get a Free Quote <i class="fa fa-arrow-right ml-2"></i>
            </a>
          </div>

          <!-- Right: Newsletter subscription -->
          <div class="col-lg-5 col-md-12">
            <div class="subscribe-card-white">
              <h4>Subscribe to Our Newsletter</h4>
              <p>Get the latest offers, tips and updates.</p>

              <form action="<?= \App\Core\View::url('/contact') ?>" method="GET" class="subscribe-input-group">
                <input type="email" name="email" placeholder="Enter your email address" required>
                <button type="submit" class="subscribe-submit-btn" aria-label="Subscribe">
                  <i class="fa fa-arrow-right"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
