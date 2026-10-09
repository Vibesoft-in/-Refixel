<link rel="stylesheet" href="<?= \App\Core\View::asset('css/contact-new.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/contact-new.css') ?>">

<div class="contact-page-wrapper">

  <!-- ==========================================================================
       1. HERO SECTION
       ========================================================================== -->
  <section class="contact-hero-section">
    <div class="container contact-hero-container">
      <div class="row align-items-center">
        <!-- Left: Heading & Subtext -->
        <div class="col-lg-7 col-md-12">
          <h1 class="contact-hero-title">
            Contact <span class="text-orange">Us</span>
          </h1>
          <p class="contact-hero-subtext">
            Let's build something great together. Get in touch with our team for any home service, enquiry or support.
          </p>
        </div>

        <!-- Right: Arched Overlapping Hero Image -->
        <div class="col-lg-5 col-md-12 d-none d-lg-block">
          <div class="contact-arched-img-wrap">
            <img 
              src="<?= \App\Core\View::asset('img/contact-hero-tech.jpg') ?>" 
              alt="Professional Home Services Technician" 
              loading="eager"
            >
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Mobile Hero Image Display -->
  <div class="d-lg-none text-center bg-white pt-4 pb-2">
    <div class="contact-arched-img-wrap" style="position: static; margin: 0 auto;">
      <img 
        src="<?= \App\Core\View::asset('img/contact-hero-tech.jpg') ?>" 
        alt="Professional Home Services Technician" 
        loading="lazy"
      >
    </div>
  </div>

  <!-- ==========================================================================
       2. CONTACT INFORMATION SECTION (3 Horizontal Items)
       ========================================================================== -->
  <section class="contact-info-section">
    <div class="container">
      <!-- 3 Horizontally Aligned Items with Vertical Separators -->
      <div class="contact-items-row">
        <?php
          $contactPhone = \App\Models\Setting::get('support_phone', '+91 94581 82006');
          $contactCleanPhone = preg_replace('/[^0-9+]/', '', $contactPhone);
          $contactEmail = \App\Models\Setting::get('support_email', 'wearerefixel@gmail.com');
          $contactLandmark = \App\Models\Setting::get('office_landmark', 'Jaspur - Kashipur Road');
          $contactSubtext = \App\Models\Setting::get('office_subtext', 'In front of BSV Girls Degree College, Jaspur');
          $contactAddress = \App\Models\Setting::get('office_address', 'Jaspur - Kashipur Road, in front of BSV Girls Degree College, Jaspur, Uttarakhand (PIN: 244712)');
          $contactMapQuery = \App\Models\Setting::get('office_map_query', 'BSV Girls Degree College, Kashipur Road, Jaspur, Uttarakhand');
        ?>
        <!-- Item 1: Phone -->
        <div class="contact-item-col">
          <div class="contact-item-icon-box">
            <i class="fa fa-phone"></i>
          </div>
          <div class="contact-item-content">
            <div class="contact-item-value">
              <a href="tel:<?= \App\Core\View::e($contactCleanPhone) ?>"><?= \App\Core\View::e($contactPhone) ?></a>
            </div>
            <div class="contact-item-subtext">Mon - Sat, 9AM - 6PM</div>
          </div>
        </div>

        <!-- Item 2: Email -->
        <div class="contact-item-col">
          <div class="contact-item-icon-box">
            <i class="fa fa-envelope-o"></i>
          </div>
          <div class="contact-item-content">
            <div class="contact-item-value">
              <a href="mailto:<?= \App\Core\View::e($contactEmail) ?>"><?= \App\Core\View::e($contactEmail) ?></a>
            </div>
            <div class="contact-item-subtext">We reply within 24 hours</div>
          </div>
        </div>

        <!-- Item 3: Location -->
        <div class="contact-item-col">
          <div class="contact-item-icon-box">
            <i class="fa fa-map-marker"></i>
          </div>
          <div class="contact-item-content">
            <div class="contact-item-value"><?= \App\Core\View::e($contactLandmark) ?></div>
            <div class="contact-item-subtext"><?= \App\Core\View::e($contactSubtext) ?></div>
          </div>
        </div>
      </div>

      <!-- Become a Partner CTA Banner -->
      <div class="contact-partner-card">
        <div class="partner-card-left">
          <div class="partner-card-icon">
            <i class="fa fa-handshake-o"></i>
          </div>
          <div class="partner-card-text">
            <div class="partner-card-badge">Partner Program</div>
            <h4 class="partner-card-title">Want to provide home services with Refixel?</h4>
            <p class="partner-card-subtext">Join 60+ verified service professionals. Get regular bookings & grow your business.</p>
          </div>
        </div>
        <div class="partner-card-action">
          <a href="<?= \App\Core\View::url('/partner') ?>" class="btn-partner-cta">
            Become a Partner <i class="fa fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       3. MAIN CONTACT AREA (Two-Column: Form & Location)
       ========================================================================== -->
  <section class="contact-main-section">
    <div class="container">
      <div class="row">
        <!-- Left: Dark Navy Contact Form Card (50%) -->
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="contact-form-card">
            <h3 class="form-card-title">Get In Touch !</h3>
            <p class="form-card-desc">
              Have a project in mind or want to know more about our services? Send us a message and we'll get back to you soon.
            </p>

            <form action="<?= \App\Core\View::url('/contact') ?>" method="POST">
              <?= \App\Core\View::csrf() ?>

              <!-- Email Address -->
              <div class="ref-form-group">
                <input 
                  type="email" 
                  name="email" 
                  class="ref-input-pill" 
                  placeholder="Email Address" 
                  required
                >
                <i class="fa fa-envelope-o form-icon"></i>
              </div>

              <!-- Your Name -->
              <div class="ref-form-group">
                <input 
                  type="text" 
                  name="name" 
                  class="ref-input-pill" 
                  placeholder="Your Name" 
                  required
                >
                <i class="fa fa-user-o form-icon"></i>
              </div>

              <!-- Your Message -->
              <div class="ref-form-group has-textarea">
                <textarea 
                  name="message" 
                  class="ref-textarea" 
                  placeholder="Your Message" 
                  required
                ></textarea>
                <i class="fa fa-comment-o form-icon"></i>
              </div>

              <button type="submit" class="btn-submit-contact">
                Submit Message &rarr;
              </button>
            </form>
          </div>
        </div>

        <!-- Right: Location & Demo Map Section (50%) -->
        <div class="col-lg-6 d-flex flex-column">
          <div class="contact-location-wrap">
            <h3 class="location-title">Our Location</h3>
            <p class="location-desc">
              Visit our office or get in touch online. We'd love to hear from you and discuss how we can support you.<br>
              <span class="d-inline-block mt-2 font-weight-bold" style="color: var(--ref-navy-dark);">
                <i class="fa fa-map-marker text-danger mr-1"></i> Office Address: <?= \App\Core\View::e($contactAddress) ?>
              </span>
            </p>

            <!-- Interactive Map Card with Google Maps & Floating Info Card -->
            <div class="demo-map-card">
              <iframe
                src="https://maps.google.com/maps?q=<?= urlencode($contactMapQuery) ?>&amp;t=&amp;z=16&amp;ie=UTF8&amp;iwloc=&amp;output=embed"
                width="100%"
                height="100%"
                style="border:0; width:100%; height:100%; min-height:380px; display:block;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                aria-label="Refixel Office Location Map"
              ></iframe>

              <!-- Floating Map Badge -->
              <div class="map-floating-badge">
                <div class="map-pin-icon">
                  <i class="fa fa-map-marker"></i>
                </div>
                <div class="map-badge-info">
                  <h6>Refixel Home Services</h6>
                  <p><?= \App\Core\View::e($contactLandmark) ?></p>
                  <div class="map-badge-rating d-flex align-items-center justify-content-between">
                    <span>★★★★★ <strong>4.8</strong></span>
                    <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= urlencode($contactMapQuery) ?>" target="_blank" rel="noopener noreferrer" class="map-directions-link ml-2" title="Open in Google Maps">
                      <i class="fa fa-external-link"></i> Map
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Social Media Block -->
            <div class="contact-social-block">
              <h4 class="social-section-title">Social Media</h4>
              <div class="contact-social-row">
                <a href="https://www.linkedin.com/in/lets-refixel-3aab5a43b?utm_source=share_via&amp;utm_content=profile&amp;utm_medium=member_android" target="_blank" rel="noopener noreferrer" class="contact-social-btn" title="LinkedIn" aria-label="LinkedIn">
                  <i class="fa fa-linkedin"></i>
                </a>
                <a href="https://x.com/letsrefixel" target="_blank" rel="noopener noreferrer" class="contact-social-btn" title="X (Twitter)" aria-label="X">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="display:inline-block; vertical-align:-2px;"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="https://www.instagram.com/letsrefixel?stkn=ajJtc20zcHRyM2Q3" target="_blank" rel="noopener noreferrer" class="contact-social-btn" title="Instagram" aria-label="Instagram">
                  <i class="fa fa-instagram"></i>
                </a>
                <a href="https://youtube.com/@letsrefixel?si=Xq0LZUFrv_yzlM5C" target="_blank" rel="noopener noreferrer" class="contact-social-btn" title="YouTube" aria-label="YouTube">
                  <i class="fa fa-youtube-play"></i>
                </a>
                <a href="https://www.facebook.com/share/1GU16Dtfcr/" target="_blank" rel="noopener noreferrer" class="contact-social-btn" title="Facebook" aria-label="Facebook">
                  <i class="fa fa-facebook"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       4. FREQUENTLY ASKED QUESTIONS (FAQ) SECTION
       ========================================================================== -->
  <section class="contact-faq-section">
    <div class="container">
      <div class="contact-faq-header text-center">
        <span class="contact-faq-badge">
          <i class="fa fa-question-circle"></i> Got Questions?
        </span>
        <h2 class="contact-faq-title">
          Frequently Asked <span class="text-orange">Questions</span>
        </h2>
        <p class="contact-faq-subtext">
          Everything you need to know about booking, payments, service warranty, and technician verification.
        </p>
      </div>

      <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
          <div class="contact-faq-grid" id="contactFaqGrid">

            <!-- LEFT COLUMN (4 Questions: Min 3, Max 4) -->
            <div class="contact-faq-col">
              <!-- FAQ Item 1 -->
              <div class="contact-faq-item active">
                <button type="button" class="contact-faq-question" aria-expanded="true">
                  <span class="faq-q-text">How do I book a home service with Refixel?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    Booking is quick and seamless! Simply explore our service categories, choose the service you need, select a convenient date and time slot, and confirm. A certified Refixel technician will arrive right at your doorstep.
                  </div>
                </div>
              </div>

              <!-- FAQ Item 2 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">Are Refixel service technicians verified and background-checked?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    Yes, 100%. Every technician and service partner undergoes strict background screening, government ID verification, and hands-on skill assessment before onboarding to ensure your safety and utmost satisfaction.
                  </div>
                </div>
              </div>

              <!-- FAQ Item 3 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">How does pricing work? Are there any hidden fees?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    We maintain complete upfront transparency. The pricing shown during booking or consultation is clear with zero hidden charges. If any extra spare parts are needed, our technician will provide an estimate for your prior approval.
                  </div>
                </div>
              </div>

              <!-- FAQ Item 4 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">What payment methods do you accept?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    We accept all convenient payment options including UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards, Net Banking, and Cash on Delivery (COD) once the work is completed to your satisfaction.
                  </div>
                </div>
              </div>
            </div>

            <!-- RIGHT COLUMN (4 Questions: Min 3, Max 4) -->
            <div class="contact-faq-col">
              <!-- FAQ Item 5 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">Can I reschedule or cancel my booking?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    Yes, you can easily reschedule or cancel your appointment free of charge up to 3 hours prior to your scheduled slot. You can do this directly from your account dashboard or by contacting our support team.
                  </div>
                </div>
              </div>

              <!-- FAQ Item 6 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">Is there a service warranty or satisfaction guarantee?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    Yes! All Refixel repairs and maintenance come with a 30-Day Service Guarantee. If you face any issues after service completion, our team will re-inspect and fix it at zero additional cost.
                  </div>
                </div>
              </div>

              <!-- FAQ Item 7 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">How quickly can a technician arrive at my home?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    We offer same-day urgent slots as well as advance scheduling up to 7 days ahead. In our standard service zones, certified technicians typically arrive within 60 to 90 minutes.
                  </div>
                </div>
              </div>

              <!-- FAQ Item 8 -->
              <div class="contact-faq-item">
                <button type="button" class="contact-faq-question" aria-expanded="false">
                  <span class="faq-q-text">How can I register as a Refixel Service Partner?</span>
                  <span class="faq-toggle-icon"><i class="fa fa-plus"></i></span>
                </button>
                <div class="contact-faq-answer">
                  <div class="faq-answer-inner">
                    If you are a professional technician or service business owner, click on the <a href="<?= \App\Core\View::url('/partner') ?>" class="text-orange font-weight-bold">"Become a Partner"</a> button on this page, fill in your details, and our onboarding team will reach out within 24 hours.
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       5. NEWSLETTER SECTION
       ========================================================================== -->
  <section class="contact-newsletter-section">
    <div class="container">
      <div class="contact-newsletter-row">
        <div>
          <h4 class="newsletter-title">Our Newsletters</h4>
          <p class="newsletter-subtext">
            Stay updated with our latest projects, offers and home service tips.
          </p>
        </div>

        <div>
          <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to REFIXEL Newsletters!'); this.reset();" class="newsletter-form-pill">
            <input type="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe &rarr;</button>
          </form>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var faqItems = document.querySelectorAll('.contact-faq-item');
  faqItems.forEach(function(item) {
    var questionBtn = item.querySelector('.contact-faq-question');
    if (questionBtn) {
      questionBtn.addEventListener('click', function() {
        var isOpen = item.classList.contains('active');
        faqItems.forEach(function(other) {
          other.classList.remove('active');
          var otherBtn = other.querySelector('.contact-faq-question');
          if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
        });
        if (!isOpen) {
          item.classList.add('active');
          questionBtn.setAttribute('aria-expanded', 'true');
        }
      });
    }
  });
});
</script>
