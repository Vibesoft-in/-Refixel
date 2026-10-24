<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <?php
    $pageTitle = $title ?? 'REFIXEL: Best Home Services in Kashipur, Jaspur | Cleaning, AC Repair';
    $pageDesc = $description ?? 'REFIXEL offers the best home services in Kashipur, Jaspur & Thakurdwara. Verified pros for deep cleaning, AC jet service, plumbing, electrician, painting & pest control. Book Now!';
    $ogPageTitle = $ogTitle ?? $pageTitle;
    $ogPageDesc = $ogDescription ?? $pageDesc;

    // Single source base URL config for absolute URLs
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $cleanCanonical = $canonicalUrl ?? \App\Core\View::fullUrl($reqPath);
    $cleanOgUrl = $ogUrl ?? $cleanCanonical;
    $cleanOgImage = $ogImage ?? \App\Core\View::fullUrl('assets/img/refixel-logo-horizontal.png');

    // Robots meta controlled by config flag (defaults to noindex on non-production)
    $robotsDirective = $robots ?? \App\Core\Env::get('APP_ROBOTS', (\App\Core\Env::get('APP_ENV') === 'production' ? 'index, follow' : 'noindex, nofollow'));
  ?>
  <title><?= \App\Core\View::e($pageTitle) ?></title>
  <meta name="description" content="<?= \App\Core\View::e($pageDesc) ?>">
  <meta name="author" content="REFIXEL">
  <meta name="robots" content="<?= \App\Core\View::e($robotsDirective) ?>">
  <link rel="canonical" href="<?= \App\Core\View::e($cleanCanonical) ?>">
  <?= \App\Core\View::partial('partials.csrf-init') ?>

  <meta property="og:title" content="<?= \App\Core\View::e($ogPageTitle) ?>">
  <meta property="og:description" content="<?= \App\Core\View::e($ogPageDesc) ?>">
  <meta property="og:image" content="<?= \App\Core\View::e($cleanOgImage) ?>">
  <meta property="og:url" content="<?= \App\Core\View::e($cleanOgUrl) ?>">
  <meta property="og:type" content="website">

  <?php
    $defaultSchema = [
      '@context' => 'https://schema.org',
      '@type'    => 'HomeAndConstructionBusiness',
      'name'     => 'REFIXEL',
      'url'      => \App\Core\View::fullUrl('/'),
      'logo'     => \App\Core\View::fullUrl('assets/img/refixel-logo-horizontal.png'),
      'image'    => \App\Core\View::fullUrl('assets/img/refixel-storefront.jpg'),
      'telephone'=> '+91-8791154730',
      'email'    => 'contact@refixel.com',
      'priceRange'=> '₹₹',
      'address'  => [
        '@type'          => 'PostalAddress',
        'streetAddress'  => 'Jaspur - Kashipur Road, in front of BSV Girls Degree College',
        'addressLocality'=> 'Jaspur',
        'addressRegion'  => 'Uttarakhand',
        'postalCode'     => '244712',
        'addressCountry' => 'IN',
      ],
      'openingHours' => 'Mon-Sun 08:00-21:00',
      'openingHoursSpecification' => [
        [
          '@type'     => 'OpeningHoursSpecification',
          'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
          'opens'     => '08:00',
          'closes'    => '21:00',
        ],
      ],
      'areaServed'   => ['Kashipur', 'Jaspur', 'Thakurdwara'],
      'sameAs'       => [
        'https://www.facebook.com/share/1GU16Dtfcr/',
        'https://www.instagram.com/letsrefixel?stkn=ajJtc20zcHRyM2Q3',
        'https://www.linkedin.com/in/lets-refixel-3aab5a43b?utm_source=share_via&utm_content=profile&utm_medium=member_android',
        'https://x.com/letsrefixel',
        'https://youtube.com/@letsrefixel?si=Xq0LZUFrv_yzlM5C'
      ],
    ];
    $activeSchema = !empty($schemaData) ? $schemaData : $defaultSchema;
  ?>
  <script type="application/ld+json">
    <?= json_encode($activeSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=2" rel="icon" type="image/png" />
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=2" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=2" rel="apple-touch-icon" />

  <!-- Fonts & Libraries -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

  <!-- REFIXEL Live Visual Stylesheets -->
  <link rel="stylesheet" href="<?= \App\Core\View::asset('css/style.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/style.css') ?>" />
  <link rel="stylesheet" href="<?= \App\Core\View::asset('css/page-style.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/page-style.css') ?>" />

  <!-- Dynamic White-Label Brand Theme & Fonts -->
  <?= \App\Core\Theme::renderHead() ?>

  <style>
  .top_offer_bar {
    background-color: #0a1c33;
    font-size: 13.5px;
    font-weight: 500;
    letter-spacing: 0.2px;
  }
  .top_offer_btn {
    background: rgba(242,91,41,0.95);
    padding: 2.5px 11px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    transition: opacity 0.2s ease;
  }
  .top_offer_btn:hover {
    opacity: 0.9;
    color: #fff !important;
  }
  @media (max-width: 576px) {
    .top_offer_bar {
      font-size: 11px !important;
      padding-top: 4px !important;
      padding-bottom: 4px !important;
      letter-spacing: -0.2px !important;
    }
    .top_offer_text {
      font-size: 10.5px !important;
      white-space: nowrap !important;
    }
    .top_offer_btn {
      font-size: 10px !important;
      padding: 1.5px 7px !important;
    }
  }
  @media (max-width: 380px) {
    .top_offer_bar {
      font-size: 9.5px !important;
    }
    .top_offer_text {
      font-size: 9.5px !important;
    }
    .top_offer_btn {
      font-size: 9px !important;
      padding: 1px 5px !important;
    }
  }
  </style>
  <script>
    window.REFIXEL_AUTH = {
      isLoggedIn: <?= \App\Core\Auth::check() ? 'true' : 'false' ?>,
      role: <?= json_encode(\App\Core\Auth::role() ?? '') ?>,
      userId: <?= json_encode(\App\Core\Auth::id() ?? null) ?>
    };
    window.REFIXEL_BASE_URL = '<?= rtrim(\App\Core\View::url(), '/') ?>';

    // Prevent cross-user data leakage and clean up stale addresses on logout / user switch
    try {
      if (window.location.search.indexOf('logged_out=1') !== -1) {
        localStorage.removeItem('refixel_saved_addresses');
        localStorage.removeItem('refixel_user_location');
        localStorage.removeItem('refixel_saved_contact');
        localStorage.removeItem('refixel_last_user_id');
      }
      var lastLoggedInUser = localStorage.getItem('refixel_last_user_id');
      var currentUidStr = window.REFIXEL_AUTH.userId ? String(window.REFIXEL_AUTH.userId) : '';
      if (lastLoggedInUser && currentUidStr && lastLoggedInUser !== currentUidStr) {
        // Different user logged in on this browser - clear stale unscoped addresses/location
        localStorage.removeItem('refixel_saved_addresses');
        localStorage.removeItem('refixel_user_location');
      }
      if (currentUidStr) {
        localStorage.setItem('refixel_last_user_id', currentUidStr);
        // Clear legacy unscoped key so it never pollutes logged-in accounts
        localStorage.removeItem('refixel_saved_addresses');
      } else if (!window.REFIXEL_AUTH.isLoggedIn && lastLoggedInUser) {
        localStorage.removeItem('refixel_last_user_id');
        localStorage.removeItem('refixel_saved_addresses');
        localStorage.removeItem('refixel_user_location');
      }
    } catch(e) {}
  </script>
</head>
<body>
  <!-- Promo Announcement Strip (Responsive Single Line on Mobile) -->
  <div class="top_offer_bar py-1 py-md-2 text-white">
    <div class="container d-flex align-items-center justify-content-center flex-nowrap" style="gap: 8px;">
      <span class="top_offer_text text-truncate">
        <?= \App\Core\View::e(\App\Models\Setting::get('promo_strip_text', 'Festival Special • Flat 20% off on REFIXEL Deep Cleaning in Kashipur & Jaspur')) ?>
      </span>
      <a href="<?= \App\Core\View::url('/services') ?>" class="top_offer_btn text-white font-weight-bold text-decoration-none flex-shrink-0">
        Book Now <i class="fa fa-arrow-right ml-1"></i>
      </a>
    </div>
  </div>

  <!-- Header Navigation -->
  <?= \App\Core\View::partial('navbar') ?>

  <!-- Shared Modals & Drawers -->
  <?= \App\Core\View::partial('location-modal') ?>
  <?= \App\Core\View::partial('login-modal') ?>
  <?= \App\Core\View::partial('mobile-drawer') ?>
  <?= \App\Core\View::partial('cart-addon-modal') ?>
  <?= \App\Core\View::partial('flash') ?>

  <main id="mainContent">
    <?= $content ?? '' ?>
  </main>

  <!-- Footer & Bottom Mobile Thumb Navigation -->
  <?= \App\Core\View::partial('footer') ?>
  <?= \App\Core\View::partial('mobile-bar') ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

  <!-- Global REFIXEL Interactive Script -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var baseUrl = '<?= rtrim(\App\Core\View::url(), '/') ?>';

    // 1. Mobile Drawer Toggle
    var drawerTriggers = document.querySelectorAll(".open-mobile-drawer, #mobileMenuBtn");
    var mobileDrawer = document.querySelector(".hometfn_popup");
    var closeDrawerBtn = document.querySelector(".btnclose_tfn");

    if (drawerTriggers.length && mobileDrawer) {
      drawerTriggers.forEach(function(trigger) {
        trigger.addEventListener("click", function(e) {
          e.preventDefault();
          mobileDrawer.classList.add("show");
        });
      });
    }
    if (closeDrawerBtn && mobileDrawer) {
      closeDrawerBtn.addEventListener("click", function(e) {
        e.preventDefault();
        mobileDrawer.classList.remove("show");
      });
    }

    // 2. Header Login Trigger Modal
    var hdrLoginPopup = document.getElementById("hdrLoginPopup");
    var hdrCloseLoginPopup = document.getElementById("hdrCloseLoginPopup");

    window.openLoginModal = function() {
      window.location.href = baseUrl + '/login';
    };

    if (hdrCloseLoginPopup && hdrLoginPopup) {
      hdrCloseLoginPopup.addEventListener("click", function() {
        hdrLoginPopup.style.display = "none";
      });
    }

    // 3. Location Modal Toggle & City Selection
    var locModal = document.getElementById("popupmodal_gate");
    var locBtn = document.getElementById("headerChangeCityBtn");
    var locClose = document.getElementById("locGateCloseBtn");
    var locManualBtn = document.getElementById("locGateManualBtn");
    var locBackBtn = document.getElementById("locGateBackBtn");
    var locChoiceStep = document.getElementById("locGateChoiceStep");
    var locManualStep = document.getElementById("locGateManualStep");

    if (locBtn && locModal) {
      locBtn.addEventListener("click", function() {
        locModal.style.display = "flex";
        if (locChoiceStep) locChoiceStep.style.display = "block";
        if (locManualStep) locManualStep.style.display = "none";
      });
    }
    if (locClose && locModal) {
      locClose.addEventListener("click", function() {
        locModal.style.display = "none";
      });
    }
    if (locManualBtn && locChoiceStep && locManualStep) {
      locManualBtn.addEventListener("click", function() {
        locChoiceStep.style.display = "none";
        locManualStep.style.display = "block";
      });
    }
    if (locBackBtn && locChoiceStep && locManualStep) {
      locBackBtn.addEventListener("click", function() {
        locManualStep.style.display = "none";
        locChoiceStep.style.display = "block";
      });
    }

    // City Selection Buttons
    document.querySelectorAll(".select-city-btn").forEach(function(btn) {
      btn.addEventListener("click", function() {
        var city = this.getAttribute("data-city");
        if (city) {
          try { localStorage.setItem('refixel_selected_city', city); } catch(e) {}
          fetch(baseUrl + '/api/service-areas', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({city: city})
          }).then(function() {
            window.location.reload();
          }).catch(function() {
            window.location.reload();
          });
        }
      });
    });

    // 4. Global Cart UI Synchronizer
    function syncCartUI() {
      fetch(baseUrl + '/api/cart')
        .then(function(r) { return r.json(); })
        .then(function(d) {
          var count = d.cart_count || 0;
          var total = d.cart_total || 0;

          // Header Cart
          var hdrText = document.getElementById('hdrCartText');
          var hdrBadge = document.getElementById('hdrCartBadge');

          // Header Cart Notification Pop Badge
          if (hdrBadge) {
            if (count > 0) {
              hdrBadge.textContent = count > 99 ? '99+' : count;
              hdrBadge.style.display = 'inline-flex';
              hdrBadge.classList.remove('badge-pop');
              void hdrBadge.offsetWidth; // Trigger reflow for CSS pop animation
              hdrBadge.classList.add('badge-pop');
            } else {
              hdrBadge.textContent = '0';
              hdrBadge.style.display = 'none';
            }
          }

          if (hdrText) {
            if (count > 0) {
              hdrText.className = '';
              hdrText.innerHTML = count + ' item' + (count > 1 ? 's' : '') + ' · ₹' + Number(total).toLocaleString('en-IN');
            } else {
              hdrText.className = 'hc-empty';
              hdrText.textContent = 'Cart';
            }
          }

          // Footer Badge
          var footerBadge = document.getElementById('footer_cart_badge');
          if (footerBadge) {
            if (count > 0) {
              footerBadge.textContent = count;
              footerBadge.style.display = 'inline-block';
            } else {
              footerBadge.style.display = 'none';
            }
          }

          // Homepage Floating Cart Bar
          var homeCart = document.getElementById('homeCartBar');
          var homeCount = document.getElementById('cartItemCount');
          var homePrice = document.getElementById('cartTotalPrice');
          if (homeCart && homeCount && homePrice) {
            if (count > 0) {
              homeCount.textContent = count;
              homePrice.textContent = '₹' + Number(total).toLocaleString('en-IN');
              homeCart.classList.add('show');
            } else {
              homeCart.classList.remove('show');
            }
          }
        })
        .catch(function() {});
    }

    syncCartUI();
    window.syncCartUI = syncCartUI;

    // Form double-submission prevention and loading state
    document.querySelectorAll('form').forEach(function(form) {
      if (form.method && form.method.toUpperCase() === 'POST' && !form.dataset.noLoading) {
        form.addEventListener('submit', function() {
          var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
          if (submitBtn && !submitBtn.disabled) {
            setTimeout(function() {
              submitBtn.disabled = true;
              if (submitBtn.tagName === 'BUTTON') {
                submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing...';
              }
            }, 10);
          }
        });
      }
    });

    // Global FAQ accordion click handler (single source of truth for all pages)
    document.addEventListener('click', function(e) {
      var questionEl = e.target.closest('.faq-question, .contact-faq-question');
      var itemEl = e.target.closest('.faq-item, .contact-faq-item');

      // Do not collapse if user is clicking or selecting text inside the open answer
      if (e.target.closest('.faq-answer, .contact-faq-answer') && !questionEl) {
        return;
      }

      var trigger = questionEl || itemEl;
      if (!trigger) return;

      var item = trigger.closest('.faq-item, .contact-faq-item');
      if (!item) return;

      var isActive = item.classList.contains('active');
      var container = item.closest('.faq-container, .contact-faq-container') || item.parentElement;

      // Close all other items in this accordion
      if (container) {
        container.querySelectorAll('.faq-item, .contact-faq-item').forEach(function(other) {
          other.classList.remove('active');
          var ans = other.querySelector('.faq-answer, .contact-faq-answer');
          if (ans) {
            ans.style.display = 'none';
            ans.style.maxHeight = '0px';
            ans.style.opacity = '0';
          }
          var icon = other.querySelector('.faq-icon, .contact-faq-icon');
          if (icon) icon.textContent = '+';
        });
      }

      // If clicked item was not active, open it
      if (!isActive) {
        item.classList.add('active');
        var ans = item.querySelector('.faq-answer, .contact-faq-answer');
        if (ans) {
          ans.style.display = 'block';
          ans.style.maxHeight = '1200px';
          ans.style.opacity = '1';
        }
        var icon = item.querySelector('.faq-icon, .contact-faq-icon');
        if (icon) icon.textContent = '−';
      }
    });
  });
  </script>
</body>
</html>


