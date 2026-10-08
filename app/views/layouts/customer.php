<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \App\Core\View::e($title ?? 'Professional Home and Commercial Services | REFIXEL') ?></title>
  <meta name="keywords" content="home cleaning services, commercial cleaning services, office cleaning services, professional cleaning company, REFIXEL" />
  <meta name="description" content="<?= \App\Core\View::e($description ?? 'REFIXEL provides professional home and commercial cleaning services, including deep cleaning, painting, plumbing, pest control and trusted maintenance solutions. Book Now!') ?>">
  <meta name="author" content="REFIXEL">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="<?= \App\Core\View::e($canonicalUrl ?? \App\Core\View::url($_SERVER['REQUEST_URI'] ?? '/')) ?>">

  <meta property="og:title" content="<?= \App\Core\View::e($title ?? 'Professional Home & Commercial Services | REFIXEL') ?>">
  <meta property="og:description" content="<?= \App\Core\View::e($description ?? 'REFIXEL provides professional home and commercial cleaning services. Book Now!') ?>">
  <meta property="og:image" content="<?= \App\Core\View::e($ogImage ?? \App\Core\View::asset('img/logo.svg')) ?>">
  <meta property="og:url" content="<?= \App\Core\View::e($canonicalUrl ?? \App\Core\View::url($_SERVER['REQUEST_URI'] ?? '/')) ?>">
  <meta property="og:type" content="website">

  <?php
    $appUrl = \App\Core\Env::get('APP_URL', 'https://www.REFIXEL.com');
    $defaultSchema = [
      '@context' => 'https://schema.org',
      '@type'    => 'HomeAndConstructionBusiness',
      'name'     => \App\Models\Setting::get('company_name', 'REFIXEL Home Services'),
      'url'      => $appUrl,
      'logo'     => $appUrl . '/assets/img/refixel-logo-horizontal.png',
      'image'    => $appUrl . '/assets/img/hero-banner.webp',
      'telephone'=> \App\Models\Setting::get('company_phone', '+91 99533 58855'),
      'email'    => \App\Models\Setting::get('company_email', 'wearerefixel@gmail.com'),
      'priceRange'=> '₹₹',
      'address'  => [
        '@type'          => 'PostalAddress',
        'streetAddress'  => \App\Models\Setting::get('company_address', 'Cyber City, DLF Phase 2'),
        'addressLocality'=> 'Gurugram',
        'addressRegion'  => 'Haryana',
        'postalCode'     => '122002',
        'addressCountry' => 'IN',
      ],
      'openingHoursSpecification' => [
        '@type'     => 'OpeningHoursSpecification',
        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        'opens'     => '08:00',
        'closes'    => '20:00',
      ],
    ];
    $activeSchema = !empty($schemaData) ? $schemaData : $defaultSchema;
  ?>
  <script type="application/ld+json">
    <?= json_encode($activeSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>" rel="shortcut icon" type="image/x-icon" />

  <!-- Fonts & Libraries -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

  <!-- REFIXEL Live Visual Stylesheets -->
  <link rel="stylesheet" href="<?= \App\Core\View::asset('css/style.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/style.css') ?>" />
  <link rel="stylesheet" href="<?= \App\Core\View::asset('css/page-style.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/page-style.css') ?>" />
</head>
<body>
  <!-- Promo Announcement Strip -->
  <div class="top_offer_bar py-2 text-center text-white" style="background-color: #0a1c33; font-size: 13.5px; font-weight: 500; letter-spacing: 0.2px;">
    <?= \App\Core\View::e(\App\Models\Setting::get('promo_strip_text', 'Exclusive Special : Flat 20% Off on all deep-cleaning services.')) ?> 
    <a href="<?= \App\Core\View::url('/services') ?>" class="text-white font-weight-bold ml-1 text-decoration-none" style="background: rgba(242,91,41,0.9); padding: 2px 10px; border-radius: 4px; font-size: 12px;">Book Now <i class="fa fa-arrow-right ml-1"></i></a>
  </div>

  <!-- Header Navigation -->
  <?= \App\Core\View::partial('navbar') ?>

  <!-- Shared Modals & Drawers -->
  <?= \App\Core\View::partial('location-modal') ?>
  <?= \App\Core\View::partial('login-modal') ?>
  <?= \App\Core\View::partial('mobile-drawer') ?>
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
    var hdrLoginTrigger = document.getElementById("hdrLoginTrigger");
    var hdrLoginPopup = document.getElementById("hdrLoginPopup");
    var hdrCloseLoginPopup = document.getElementById("hdrCloseLoginPopup");

    window.openLoginModal = function() {
      if (hdrLoginPopup) hdrLoginPopup.style.display = "flex";
    };

    if (hdrLoginTrigger && hdrLoginPopup) {
      hdrLoginTrigger.addEventListener("click", function(e) {
        e.preventDefault();
        hdrLoginPopup.style.display = "flex";
      });
    }
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
  });
  </script>
</body>
</html>


