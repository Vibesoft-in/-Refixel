<?php
$user = \App\Core\Auth::user();

// Determine active state for header navigation links by reliably stripping base path
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$pathOnly = parse_url($rawUri, PHP_URL_PATH) ?? '/';

// 1. Strip APP_URL base path if configured
$appUrl = rtrim((string)\App\Core\Env::get('APP_URL', ''), '/');
$appBasePath = parse_url($appUrl, PHP_URL_PATH) ?? '';
if (!empty($appBasePath) && $appBasePath !== '/' && str_starts_with($pathOnly, $appBasePath)) {
    $pathOnly = substr($pathOnly, strlen($appBasePath));
}

// 2. Strip script root directory (/REFIXEL/public or /REFIXEL)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$publicDir  = dirname($scriptName);
$rootDir    = dirname($publicDir);

if ($publicDir !== '/' && $publicDir !== '.' && !empty($publicDir) && str_starts_with($pathOnly, $publicDir)) {
    $pathOnly = substr($pathOnly, strlen($publicDir));
} elseif ($rootDir !== '/' && $rootDir !== '.' && !empty($rootDir) && str_starts_with($pathOnly, $rootDir)) {
    $pathOnly = substr($pathOnly, strlen($rootDir));
}

// 3. Strip /public if still leading
if (str_starts_with($pathOnly, '/public')) {
    $pathOnly = substr($pathOnly, 7);
}

$cleanPath = '/' . trim($pathOnly, '/');
$cleanPathLower = strtolower($cleanPath);

// Exact match for Home only
$isHome = ($cleanPathLower === '/' || $cleanPathLower === '/index.php');

// Starts-with matching for other links so child/nested pages keep their parent link active
$isAbout = str_starts_with($cleanPathLower, '/about');
$isServices = (str_starts_with($cleanPathLower, '/services') || str_contains($cleanPathLower, '-services-in-') || str_contains($cleanPathLower, '-in-'));
$isBlogs = (str_starts_with($cleanPathLower, '/blogs') || str_starts_with($cleanPathLower, '/blog'));
$isContact = str_starts_with($cleanPathLower, '/contact');

// Initial cart items for instant header badge rendering
$initialCartRaw = class_exists('\App\Core\Cart') ? \App\Core\Cart::getRaw() : [];
$initialCartCount = 0;
if (!empty($initialCartRaw)) {
    foreach ($initialCartRaw as $q) {
        $initialCartCount += (int)$q;
    }
}
$initialCartTotal = 0;
if ($initialCartCount > 0 && class_exists('\App\Core\Cart')) {
    $cartDetails = \App\Core\Cart::getDetails();
    $initialCartTotal = (float)($cartDetails['total'] ?? 0);
}
?>
<nav class="navbar navbar-expand-lg navbar-light main_menu">
  <div class="container-fluid header-nav-container d-flex flex-wrap align-items-center justify-content-between">
    <a class="navbar-brand py-0" href="<?= \App\Core\View::url('/') ?>">
      <img src="<?= \App\Core\View::asset('img/refixel-logo-horizontal.png') ?>" alt="REFIXEL" class="nav-brand-logo" style="max-height: 48px; width: auto; object-fit: contain;">
    </a>

    <style>
      .navbar-toggler {
        display: none !important;
      }
      @media (min-width: 992px) {
        .navbar-collapse,
        .header-nav-collapse {
          display: flex !important;
          visibility: visible !important;
        }
      }
      @media (max-width: 991px) {
        .navbar-collapse,
        .header-nav-collapse,
        #navbarMainCollapse {
          display: none !important;
          visibility: hidden !important;
        }
      }

      /* Header Cart Notification Badge */
      .hdr-cart-icon-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
      }
      .hdr-cart-badge {
        position: absolute;
        top: -8px;
        right: -9px;
        background: #f25b29;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        min-width: 18px;
        height: 18px;
        line-height: 14px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(242, 91, 41, 0.45);
        z-index: 10;
        pointer-events: none;
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }
      @keyframes cartBadgePop {
        0% { transform: scale(0.3); opacity: 0; }
        60% { transform: scale(1.3); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
      }
      .hdr-cart-badge.badge-pop {
        animation: cartBadgePop 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }

      @media (min-width: 992px) {
        .header-nav-container {
          flex-wrap: nowrap !important;
        }
        .header-center-nav {
          gap: clamp(14px, 1.8vw, 28px);
          font-weight: 600;
          font-size: clamp(14px, 1.05vw, 16px);
        }
        .header-partner-wrap {
          margin-left: clamp(14px, 1.5vw, 26px);
        }
      }
      @media (max-width: 991px) {
        .header-nav-container {
          flex-wrap: wrap !important;
          align-items: center !important;
          justify-content: space-between !important;
          padding: 8px 14px !important;
        }
        .navbar-brand {
          margin: 0 !important;
          padding: 0 !important;
          display: inline-flex !important;
          align-items: center !important;
          order: 1 !important;
          flex: 0 0 auto !important;
          max-width: calc(100% - 96px) !important;
        }
        .nav-brand-logo {
          max-height: 38px !important;
          height: 38px !important;
          width: auto !important;
          object-fit: contain !important;
        }
        .header-action-btns {
          order: 2 !important;
          margin-left: auto !important;
          margin-right: 0 !important;
          display: flex !important;
          align-items: center !important;
          gap: 8px !important;
          flex: 0 0 auto !important;
        }
        /* Mobile Cart: Sleek Circular Icon Button with Notification Pop Badge */
        .hdr-cart {
          display: inline-flex !important;
          width: 38px !important;
          height: 38px !important;
          min-width: 38px !important;
          max-width: 38px !important;
          padding: 0 !important;
          border-radius: 50% !important;
          align-items: center !important;
          justify-content: center !important;
          position: relative !important;
          margin: 0 !important;
          gap: 0 !important;
          border: 1.5px solid rgba(242, 91, 41, 0.35) !important;
          background: #ffffff !important;
          flex-shrink: 0 !important;
          box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05) !important;
        }
        .hdr-cart svg {
          stroke: #f25b29 !important;
          width: 18px !important;
          height: 18px !important;
          margin: 0 !important;
        }
        .hdr-cart #hdrCartText {
          display: none !important;
        }
        .hdr-cart .hdr-cart-icon-wrap {
          position: static !important;
        }
        .hdr-cart .hdr-cart-badge {
          position: absolute !important;
          top: -4px !important;
          right: -4px !important;
          font-size: 10.5px !important;
          min-width: 18px !important;
          height: 18px !important;
          line-height: 14px !important;
          border: 2px solid #ffffff !important;
          box-shadow: 0 2px 6px rgba(242, 91, 41, 0.5) !important;
        }
        .our_cart {
          margin: 0 !important;
          flex-shrink: 0 !important;
        }
        .our_cart ul {
          margin: 0 !important;
          padding: 0 !important;
          list-style: none !important;
          display: flex !important;
          align-items: center !important;
        }
        .our_cart ul li {
          margin: 0 !important;
          padding: 0 !important;
        }
        .our_cart ul li a {
          width: 38px !important;
          height: 38px !important;
          min-width: 38px !important;
          max-width: 38px !important;
          border-radius: 50% !important;
          background: #f25b29 !important;
          display: flex !important;
          align-items: center !important;
          justify-content: center !important;
          padding: 0 !important;
          margin: 0 !important;
          box-shadow: 0 2px 6px rgba(242, 91, 41, 0.25) !important;
        }
        .our_cart ul li a strong,
        .our_cart ul li a i.fa-angle-down {
          display: none !important;
        }
        .header-nav-collapse,
        #navbarMainCollapse {
          display: none !important;
        }
      }
    </style>

    <!-- Header Action Buttons (Cart & Login) - Desktop Order 3, Mobile Order 2 (In front of logo on right) -->
    <div class="d-flex align-items-center header-action-btns order-2 order-lg-3 ml-auto">
      <a class="hdr-cart mr-2 mr-lg-3" id="hdrCart" href="<?= \App\Core\View::url('/cart') ?>" aria-label="Cart">
        <div class="hdr-cart-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"></path>
          </svg>
          <span class="hdr-cart-badge" id="hdrCartBadge" style="<?= $initialCartCount > 0 ? '' : 'display: none;' ?>">
            <?= $initialCartCount > 99 ? '99+' : $initialCartCount ?>
          </span>
        </div>
        <span id="hdrCartText" class="<?= $initialCartCount > 0 ? '' : 'hc-empty' ?>">
          <?= $initialCartCount > 0 ? ($initialCartCount . ' item' . ($initialCartCount > 1 ? 's' : '') . ' · ₹' . number_format($initialCartTotal, 0)) : 'Cart' ?>
        </span>
      </a>

      <div class="our_cart">
      <ul>
        <?php if ($user): ?>
          <li class="position-relative">
            <a href="<?= \App\Core\View::url($user['role'] === 'admin' ? '/admin' : ($user['role'] === 'staff' ? '/staff' : '/account')) ?>" class="d-flex align-items-center">
              <img src="<?= \App\Core\View::asset('img/account.webp') ?>" alt="Account">
              <strong><?= \App\Core\View::e($user['name']) ?></strong>
              <i class="fa fa-angle-down ml-2 mr-2" style="font-size: 13px; opacity: 0.85;"></i>
            </a>
            <div class="account_dropdown">
              <a href="<?= \App\Core\View::url($user['role'] === 'admin' ? '/admin' : ($user['role'] === 'staff' ? '/staff' : '/account')) ?>">
                <i class="fa fa-dashboard mr-2 text-success"></i> Dashboard
              </a>
              <?php if ($user['role'] === 'customer'): ?>
                <a href="<?= \App\Core\View::url('/account/bookings') ?>">
                  <i class="fa fa-calendar mr-2 text-success"></i> My Bookings
                </a>
                <a href="<?= \App\Core\View::url('/account/invoices') ?>">
                  <i class="fa fa-file-text-o mr-2 text-success"></i> Invoices
                </a>
                <a href="<?= \App\Core\View::url('/account/profile') ?>">
                  <i class="fa fa-user-circle mr-2 text-success"></i> Profile
                </a>
              <?php endif; ?>
              <a href="<?= \App\Core\View::url('/logout') ?>" class="text-danger">
                <i class="fa fa-sign-out mr-2"></i> Log Out
              </a>
            </div>
          </li>
        <?php else: ?>
          <li>
            <a href="javascript:void(0)" id="hdrLoginTrigger" class="hdr-login-btn">
              <img src="<?= \App\Core\View::asset('img/account.webp') ?>" alt="Login">
              <strong>Login</strong>
            </a>
          </li>
        <?php endif; ?>
      </ul>
      </div>
    </div>

    <!-- Navigation Links: Forced open at all sizes, horizontally scrollable below 992px -->
    <div class="collapse navbar-collapse header-nav-collapse d-flex flex-grow-1 justify-content-center order-3 order-lg-2" id="navbarMainCollapse">
      <div class="header-nav-scroll-wrap d-flex align-items-center">
        <!-- 5 Trackable Links with Dynamic Underline Indicator -->
        <ul class="d-flex mb-0 pl-0 list-unstyled align-items-center header-center-nav position-relative" id="hdrTrackableNav">
          <li><a href="<?= \App\Core\View::url('/') ?>" class="header-nav-link text-decoration-none <?= $isHome ? 'active' : '' ?>" data-nav="home">Home</a></li>
          <li><a href="<?= \App\Core\View::url('/about') ?>" class="header-nav-link text-decoration-none <?= $isAbout ? 'active' : '' ?>" data-nav="about">About</a></li>
          <li><a href="<?= \App\Core\View::url('/services') ?>" class="header-nav-link text-decoration-none <?= $isServices ? 'active' : '' ?>" data-nav="services">Services</a></li>
          <li><a href="<?= \App\Core\View::url('/blogs') ?>" class="header-nav-link text-decoration-none <?= $isBlogs ? 'active' : '' ?>" data-nav="blogs">Blogs</a></li>
          <li><a href="<?= \App\Core\View::url('/contact') ?>" class="header-nav-link text-decoration-none <?= $isContact ? 'active' : '' ?>" data-nav="contact">Contact Us</a></li>
          <li class="nav-indicator-line" id="hdrNavIndicator" aria-hidden="true"></li>
        </ul>

        <!-- Separate Service Partner button (not underlined / outside tracking) -->
        <div class="header-partner-wrap">
          <a href="<?= \App\Core\View::url('/partner') ?>" class="header-nav-partner text-decoration-none px-3 py-2 rounded-pill">Service Partner</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Mouse Tracking Sliding Underline Script for Header Nav (Home, About, Services, Blogs, Contact Us) -->
<script>
(function() {
  function initNavTracker() {
    var nav = document.getElementById('hdrTrackableNav');
    var indicator = document.getElementById('hdrNavIndicator');
    if (!nav || !indicator) return;

    var links = Array.from(nav.querySelectorAll('a.header-nav-link'));
    if (!links.length) return;

    // Derive active key strictly from current URL pathname after stripping base path
    function getActiveNavKey() {
      var pathname = window.location.pathname.toLowerCase().replace(/\/+$/, '') || '/';

      // Dynamically discover base path from Home link href (e.g. '/refixel' or '')
      var homeLink = nav.querySelector('a.header-nav-link[data-nav="home"]');
      var basePath = '';
      if (homeLink) {
        try {
          basePath = new URL(homeLink.href, window.location.origin).pathname.toLowerCase().replace(/\/+$/, '');
        } catch (e) {}
      }

      var clean = pathname;
      if (basePath && basePath !== '/' && clean.indexOf(basePath) === 0) {
        clean = clean.substring(basePath.length);
      }
      clean = ('/' + clean.replace(/^\/+/, '')).replace(/\/+$/, '') || '/';

      // 1. Exact match for Home only
      if (clean === '/' || clean === '/index.php') {
        return 'home';
      }
      // 2. Starts-with matching for other pages so child/nested pages keep their parent link active
      if (clean.indexOf('/about') === 0) {
        return 'about';
      }
      if (clean.indexOf('/services') === 0 || clean.indexOf('-services-in-') !== -1 || clean.indexOf('-in-') !== -1) {
        return 'services';
      }
      if (clean.indexOf('/blogs') === 0 || clean.indexOf('/blog') === 0) {
        return 'blogs';
      }
      if (clean.indexOf('/contact') === 0) {
        return 'contact';
      }

      return null;
    }

    function syncActiveFromUrl() {
      var activeKey = getActiveNavKey();
      links.forEach(function(link) {
        if (activeKey && link.getAttribute('data-nav') === activeKey) {
          link.classList.add('active');
        } else {
          link.classList.remove('active');
        }
      });
      return nav.querySelector('a.header-nav-link.active');
    }

    function moveToLink(link, smooth) {
      if (!link) {
        indicator.style.opacity = '0';
        return;
      }
      var linkRect = link.getBoundingClientRect();
      var navRect = nav.getBoundingClientRect();

      var left = linkRect.left - navRect.left;
      var width = linkRect.width;

      if (!smooth) {
        indicator.style.transition = 'none';
      } else {
        indicator.style.transition = 'left 0.28s cubic-bezier(0.25, 1, 0.5, 1), width 0.28s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.2s ease';
      }

      indicator.style.left = left + 'px';
      indicator.style.width = width + 'px';
      indicator.style.opacity = '1';
    }

    function resetToActive(smooth) {
      var active = nav.querySelector('a.header-nav-link.active') || syncActiveFromUrl();
      if (active) {
        moveToLink(active, smooth);
      } else {
        indicator.style.opacity = '0';
      }
    }

    function findClosestLink(clientX) {
      var closest = null;
      var minDistance = Infinity;
      links.forEach(function(link) {
        var rect = link.getBoundingClientRect();
        var center = rect.left + rect.width / 2;
        var dist = Math.abs(clientX - center);
        if (dist < minDistance) {
          minDistance = dist;
          closest = link;
        }
      });
      return closest;
    }

    // Initialize: synchronize active link with current URL and position underline without animation
    syncActiveFromUrl();
    resetToActive(false);

    // Re-align after fonts or layout fully load
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function() { resetToActive(false); });
    }
    window.addEventListener('load', function() { resetToActive(false); });
    window.addEventListener('resize', function() { resetToActive(false); });

    // Handle browser back/forward buttons and page restoration
    window.addEventListener('popstate', function() {
      syncActiveFromUrl();
      resetToActive(true);
    });
    window.addEventListener('pageshow', function() {
      syncActiveFromUrl();
      resetToActive(false);
    });

    // Track mouse pointer across the 5 links area
    nav.addEventListener('mousemove', function(e) {
      var closest = findClosestLink(e.clientX);
      if (closest) {
        moveToLink(closest, true);
      }
    });

    // Individual link events
    links.forEach(function(link) {
      link.addEventListener('mouseenter', function() {
        moveToLink(this, true);
      });

      // On click: lock active class and indicator to clicked link
      link.addEventListener('click', function() {
        links.forEach(function(l) { l.classList.remove('active'); });
        this.classList.add('active');
        moveToLink(this, true);
      });
    });

    // When mouse pointer leaves the 5 links container, return to active link
    nav.addEventListener('mouseleave', function() {
      resetToActive(true);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavTracker);
  } else {
    initNavTracker();
  }
})();
</script>
