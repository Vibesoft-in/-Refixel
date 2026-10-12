<?php
// Determine active state for bottom taskbar navigation
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$pathOnly = parse_url($rawUri, PHP_URL_PATH) ?? '/';

$appUrl = rtrim((string)\App\Core\Env::get('APP_URL', ''), '/');
$appBasePath = parse_url($appUrl, PHP_URL_PATH) ?? '';
if (!empty($appBasePath) && $appBasePath !== '/' && str_starts_with($pathOnly, $appBasePath)) {
    $pathOnly = substr($pathOnly, strlen($appBasePath));
}

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$publicDir  = dirname($scriptName);
$rootDir    = dirname($publicDir);

if ($publicDir !== '/' && $publicDir !== '.' && !empty($publicDir) && str_starts_with($pathOnly, $publicDir)) {
    $pathOnly = substr($pathOnly, strlen($publicDir));
} elseif ($rootDir !== '/' && $rootDir !== '.' && !empty($rootDir) && str_starts_with($pathOnly, $rootDir)) {
    $pathOnly = substr($pathOnly, strlen($rootDir));
}

if (str_starts_with($pathOnly, '/public')) {
    $pathOnly = substr($pathOnly, 7);
}

$cleanPath = '/' . trim($pathOnly, '/');
$cleanPathLower = strtolower($cleanPath);

$redirectParam = $_GET['redirect'] ?? '';
$isHome = ($cleanPathLower === '/' || $cleanPathLower === '/index.php');
$isBooking = (str_starts_with($cleanPathLower, '/account/bookings') || str_starts_with($cleanPathLower, '/book') || ($cleanPathLower === '/login' && str_contains($redirectParam, 'booking')));
$isCart = str_starts_with($cleanPathLower, '/cart');
$isSearch = (str_starts_with($cleanPathLower, '/services') && !empty($_GET['q']));
$currentCity = \App\Models\ServiceArea::getSessionCity();
$currentCitySlug = strtolower($currentCity ?: 'kashipur');
?>
<!-- Bottom Mobile Bar Matching Live REFIXEL Exactly -->
<div class="bottomBarNavbar">
  <ul>
    <li>
      <a href="<?= \App\Core\View::url('/') ?>" class="<?= $isHome ? 'active' : '' ?>" data-nav="home">
        <span class="bm-icon-circle">
          <img src="<?= \App\Core\View::asset('img/home2.png') ?>" alt="Home" width="22" height="22" loading="lazy" decoding="async">
        </span>
        <h6>Home</h6>
      </a>
    </li>
    <li>
      <a href="<?= \App\Core\Auth::check() ? \App\Core\View::url('/account/bookings') : \App\Core\View::url('/login?redirect=' . urlencode(\App\Core\View::url('/account/bookings'))) ?>" class="<?= $isBooking ? 'active' : '' ?>" data-nav="booking">
        <span class="bm-icon-circle">
          <img src="<?= \App\Core\View::asset('img/write-book.png') ?>" alt="Booking" width="22" height="22" loading="lazy" decoding="async">
        </span>
        <h6>Booking</h6>
      </a>
    </li>
    <li>
      <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%20Support" target="_blank" rel="noopener" data-nav="whatsapp">
        <span class="bm-icon-circle">
          <img src="<?= \App\Core\View::asset('img/whatsapp_icons_1.png') ?>" alt="WhatsApp" width="22" height="22" loading="lazy" decoding="async">
        </span>
        <h6>WhatsApp</h6>
      </a>
    </li>
    <li>
      <a href="javascript:void(0)" id="mobileSearchTrigger" class="<?= $isSearch ? 'active' : '' ?>" data-nav="search" aria-label="Search Services">
        <span class="bm-icon-circle">
          <svg class="bm-search-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7.5"></circle>
            <line x1="21" y1="21" x2="16.5" y2="16.5"></line>
          </svg>
        </span>
        <h6>Search</h6>
      </a>
    </li>
    <li>
      <a href="javascript:void(0)" class="menu_btn" id="mobileMenuBtn" data-nav="menu">
        <span class="bm-icon-circle">
          <img src="<?= \App\Core\View::asset('img/menu.png') ?>" alt="Menu" width="22" height="22" loading="lazy" decoding="async">
        </span>
        <h6>Menu</h6>
      </a>
    </li>
  </ul>
</div>

<style>
/* Bottom Taskbar Navigation - Hidden on Desktop/Big Screens, Visible only on Mobile & Tablets */
.bottomBarNavbar {
  display: none !important;
}

@media (max-width: 991px) {
  .bottomBarNavbar {
    height: 62px !important;
    background: #ffffff !important;
    box-shadow: 0 -2px 14px rgba(15, 23, 42, 0.08) !important;
    border-top: 1px solid #eef2f6 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 5px 8px !important;
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 1050 !important;
    box-sizing: border-box !important;
  }
}

.bottomBarNavbar ul {
  list-style: none !important;
  padding: 0 !important;
  margin: 0 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-around !important;
  width: 100% !important;
  height: 100% !important;
}

.bottomBarNavbar ul li {
  flex: 1 !important;
  text-align: center !important;
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  margin: 0 !important;
  padding: 0 !important;
}

.bottomBarNavbar ul li a {
  text-decoration: none !important;
  color: #64748b !important;
  position: relative !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  padding: 2px 0 !important;
  min-height: auto !important;
  transition: all 0.2s ease !important;
}

/* Bordered Icon Circle Container (Structured badge look) */
.bottomBarNavbar ul li a .bm-icon-circle {
  width: 33px !important;
  height: 33px !important;
  min-width: 33px !important;
  min-height: 33px !important;
  border-radius: 50% !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  position: relative !important;
  top: auto !important;
  right: auto !important;
  left: auto !important;
  bottom: auto !important;
  margin: 0 0 2px 0 !important;
  padding: 0 !important;
  background: #f8fafc !important;
  border: 1.5px solid #e2e8f0 !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.bottomBarNavbar ul li a .bm-icon-circle img {
  width: 17px !important;
  height: 17px !important;
  max-width: 17px !important;
  max-height: 17px !important;
  margin: 0 !important;
  padding: 0 !important;
  display: block !important;
  object-fit: contain !important;
  transition: transform 0.2s ease, filter 0.2s ease !important;
}

/* Active Taskbar Item */
.bottomBarNavbar ul li a.active .bm-icon-circle {
  background: #f25b29 !important;
  border-color: #f25b29 !important;
  box-shadow: 0 3px 10px rgba(242, 91, 41, 0.4) !important;
}

.bottomBarNavbar ul li a.active .bm-icon-circle img {
  filter: brightness(0) invert(1) !important;
  transform: scale(1.06) !important;
}

/* Hover/Touch State */
.bottomBarNavbar ul li a:hover .bm-icon-circle {
  border-color: #f25b29 !important;
  background: #fff8f5 !important;
  transform: translateY(-1px) !important;
}

.bottomBarNavbar ul li a.active:hover .bm-icon-circle {
  background: #f25b29 !important;
  border-color: #f25b29 !important;
  transform: none !important;
}

/* Text Label */
.bottomBarNavbar ul li a h6 {
  margin: 0 !important;
  padding: 0 !important;
  font-size: 10.5px !important;
  font-weight: 600 !important;
  line-height: 1.15 !important;
  color: #64748b !important;
  letter-spacing: -0.2px !important;
  transition: color 0.2s ease !important;
}

.bottomBarNavbar ul li a.active h6 {
  color: #f25b29 !important;
  font-weight: 700 !important;
}

.bottomBarNavbar ul li a:hover h6 {
  color: #f25b29 !important;
}

/* Cart Badge */
.bottomBarNavbar ul li a #footer_cart_badge {
  position: absolute !important;
  top: -4px !important;
  right: -4px !important;
  min-width: 16px !important;
  height: 16px !important;
  padding: 0 4px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: #f25b29 !important;
  font-size: 9.5px !important;
  font-weight: 700 !important;
  color: #ffffff !important;
  border-radius: 10px !important;
  border: 1.5px solid #ffffff !important;
  line-height: 1 !important;
  z-index: 2 !important;
}
/* Search Icon in Mobile Bar */
.bottomBarNavbar ul li a .bm-icon-circle .bm-search-svg {
  width: 17px !important;
  height: 17px !important;
  color: #475569 !important;
  stroke: #475569 !important;
  display: block !important;
  transition: transform 0.2s ease, stroke 0.2s ease, color 0.2s ease !important;
}

.bottomBarNavbar ul li a.active .bm-icon-circle .bm-search-svg,
.bottomBarNavbar ul li a.search-active .bm-icon-circle .bm-search-svg {
  color: #ffffff !important;
  stroke: #ffffff !important;
  transform: scale(1.08) !important;
}

.bottomBarNavbar ul li a.search-active .bm-icon-circle {
  background: #f25b29 !important;
  border-color: #f25b29 !important;
  box-shadow: 0 3px 10px rgba(242, 91, 41, 0.4) !important;
}

.bottomBarNavbar ul li a.search-active h6 {
  color: #f25b29 !important;
  font-weight: 700 !important;
}

.bottomBarNavbar ul li a:hover .bm-icon-circle .bm-search-svg {
  color: #f25b29 !important;
  stroke: #f25b29 !important;
}

/* ========================================================
   FLOATING MOBILE SEARCH BAR (Docks above bottom nav / keyboard)
   ======================================================== */
.refixel-ms-backdrop {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100% !important;
  height: 100% !important;
  background: rgba(15, 23, 42, 0.35) !important;
  backdrop-filter: blur(2px) !important;
  -webkit-backdrop-filter: blur(2px) !important;
  z-index: 1055 !important;
  display: none;
  opacity: 0;
  transition: opacity 0.2s ease !important;
}

.refixel-ms-backdrop.show {
  display: block !important;
  opacity: 1 !important;
}

.refixel-ms-float {
  position: fixed !important;
  bottom: 72px;
  left: 12px !important;
  right: 12px !important;
  max-width: 480px !important;
  margin: 0 auto !important;
  z-index: 1060 !important;
  display: none;
  flex-direction: column !important;
  align-items: stretch !important;
  opacity: 0;
  transform: translateY(12px) scale(0.98);
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease, bottom 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
  pointer-events: auto !important;
}

.refixel-ms-float.show {
  display: flex !important;
  opacity: 1 !important;
  transform: translateY(0) scale(1) !important;
}

/* When focused / typing */
.refixel-ms-float.is-focused {
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.22) !important;
}

/* The Search Input Pill Form */
.ms-float-box {
  display: flex !important;
  align-items: center !important;
  background: #ffffff !important;
  border: 1.5px solid #e2e8f0 !important;
  border-radius: 28px !important;
  padding: 4px 6px 4px 14px !important;
  box-shadow: 0 8px 28px rgba(15, 23, 42, 0.16), 0 2px 6px rgba(0, 0, 0, 0.06) !important;
  gap: 8px !important;
  position: relative !important;
  z-index: 3 !important;
  margin: 0 !important;
  transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
}

.refixel-ms-float.is-focused .ms-float-box {
  border-color: #f25b29 !important;
  box-shadow: 0 8px 30px rgba(242, 91, 41, 0.28), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
}

.ms-float-icon-btn {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #f25b29 !important;
  background: none !important;
  border: none !important;
  padding: 0 !important;
  margin: 0 !important;
  cursor: pointer !important;
  flex-shrink: 0 !important;
}

.ms-float-input {
  flex: 1 !important;
  border: none !important;
  outline: none !important;
  background: transparent !important;
  font-size: 15px !important;
  color: #0f172a !important;
  padding: 8px 0 !important;
  min-width: 0 !important;
  -webkit-appearance: none !important;
}

.ms-float-input::-webkit-search-cancel-button,
.ms-float-input::-webkit-search-decoration {
  -webkit-appearance: none !important;
  display: none !important;
}

/* Single clean cross button */
.ms-float-close {
  width: 28px !important;
  height: 28px !important;
  min-width: 28px !important;
  border-radius: 50% !important;
  border: none !important;
  background: #f1f5f9 !important;
  color: #64748b !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  cursor: pointer !important;
  padding: 0 !important;
  flex-shrink: 0 !important;
  transition: all 0.15s ease !important;
}

.ms-float-close:hover,
.ms-float-close:active {
  background: #f25b29 !important;
  color: #ffffff !important;
}

/* Little pointer arrow towards Search icon in bottom bar */
.ms-float-arrow {
  position: absolute !important;
  bottom: -6px !important;
  right: calc(30% - 7px) !important;
  width: 12px !important;
  height: 12px !important;
  background: #ffffff !important;
  border-right: 1.5px solid #e2e8f0 !important;
  border-bottom: 1.5px solid #e2e8f0 !important;
  transform: rotate(45deg) !important;
  z-index: 2 !important;
  transition: opacity 0.2s ease !important;
}

.refixel-ms-float.keyboard-open .ms-float-arrow {
  display: none !important;
}
</style>

<!-- Floating Search Bar (Only search bar, lifts on keyboard focus) -->
<div id="mobileSearchBackdrop" class="refixel-ms-backdrop"></div>
<div id="mobileSearchFloat" class="refixel-ms-float" role="search" aria-label="Quick Search">
  <form id="mobileSearchFloatForm" action="<?= \App\Core\View::url('/services') ?>" method="GET" class="ms-float-box">
    <button type="submit" class="ms-float-icon-btn" aria-label="Search">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="7.5"></circle>
        <line x1="21" y1="21" x2="16.5" y2="16.5"></line>
      </svg>
    </button>

    <input 
      type="search" 
      name="q"
      id="mobileSearchInput" 
      class="ms-float-input" 
      placeholder="Search services (e.g. AC, Cleaning...)" 
      autocomplete="off" 
      autocorrect="off" 
      autocapitalize="off" 
      spellcheck="false"
      enterkeyhint="search"
    >

    <button type="button" id="mobileSearchCloseBtn" class="ms-float-close" aria-label="Close search">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  </form>

  <!-- Pointer arrow pointing down to Search button in bottom bar -->
  <div class="ms-float-arrow"></div>
</div>
<!-- Floating WhatsApp Chat -->
<div class="whatsapchat">
  <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%20Support" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <i class="fa fa-whatsapp"></i>
  </a>
</div>

<script>
(function() {
  var searchTrigger = document.getElementById('mobileSearchTrigger');
  var floatBox = document.getElementById('mobileSearchFloat');
  var backdrop = document.getElementById('mobileSearchBackdrop');
  var searchInput = document.getElementById('mobileSearchInput');
  var closeBtn = document.getElementById('mobileSearchCloseBtn');
  var searchForm = document.getElementById('mobileSearchFloatForm');

  var searchBase = '<?= \App\Core\View::url('/services?q=') ?>';
  var isOpen = false;

  function openSearch() {
    if (!floatBox) return;
    isOpen = true;
    floatBox.style.display = 'flex';
    if (backdrop) backdrop.style.display = 'block';

    requestAnimationFrame(function() {
      floatBox.classList.add('show');
      if (backdrop) backdrop.classList.add('show');
    });

    if (searchTrigger) searchTrigger.classList.add('search-active');

    // Auto-focus input so keyboard opens immediately
    setTimeout(function() {
      if (searchInput) {
        searchInput.focus();
        if (searchInput.value) {
          searchInput.select();
        }
      }
    }, 80);
  }

  function closeSearch() {
    if (!floatBox) return;
    isOpen = false;
    floatBox.classList.remove('show', 'is-focused', 'keyboard-open');
    if (backdrop) backdrop.classList.remove('show');
    if (searchTrigger) searchTrigger.classList.remove('search-active');
    if (searchInput) {
      searchInput.value = '';
      searchInput.blur();
    }

    setTimeout(function() {
      if (!isOpen) {
        floatBox.style.display = 'none';
        floatBox.style.bottom = '72px';
        if (backdrop) backdrop.style.display = 'none';
      }
    }, 200);
  }

  function toggleSearch() {
    if (isOpen) {
      closeSearch();
    } else {
      openSearch();
    }
  }

  if (searchTrigger) {
    searchTrigger.addEventListener('click', function(e) {
      e.preventDefault();
      toggleSearch();
    });
  }

  // Also bind any extra search buttons if present
  document.querySelectorAll('[data-open-search]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      openSearch();
    });
  });

  var mobileMenuBtn = document.getElementById('mobileMenuBtn');
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function() {
      closeSearch();
    });
  }

  // Single cross button closes the search bar
  if (closeBtn) {
    closeBtn.addEventListener('click', function(e) {
      e.preventDefault();
      closeSearch();
    });
  }

  // Tapping backdrop closes the search bar
  if (backdrop) {
    backdrop.addEventListener('click', closeSearch);
  }

  // Handle Visual Viewport for mobile virtual keyboard
  // When keyboard opens, dynamically lift the search box above the keyboard!
  function handleViewportResize() {
    if (!isOpen || !floatBox) return;
    if (window.visualViewport) {
      var keyboardHeight = window.innerHeight - window.visualViewport.height;
      if (keyboardHeight > 100) {
        // Keyboard is up! Position right on top of virtual keyboard
        floatBox.style.bottom = (keyboardHeight + 8) + 'px';
        floatBox.classList.add('keyboard-open');
      } else {
        floatBox.style.bottom = '72px';
        floatBox.classList.remove('keyboard-open');
      }
    }
  }

  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', handleViewportResize);
    window.visualViewport.addEventListener('scroll', handleViewportResize);
  }

  if (searchInput) {
    searchInput.addEventListener('focus', function() {
      if (floatBox) floatBox.classList.add('is-focused');
      setTimeout(handleViewportResize, 250);
    });

    searchInput.addEventListener('blur', function() {
      if (floatBox) {
        floatBox.classList.remove('is-focused');
        setTimeout(handleViewportResize, 200);
      }
    });

    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var q = this.value.trim();
        if (q) {
          window.location.href = searchBase + encodeURIComponent(q);
        }
      }
    });
  }

  if (searchForm) {
    searchForm.addEventListener('submit', function(e) {
      var q = searchInput ? searchInput.value.trim() : '';
      if (!q) {
        e.preventDefault();
        return false;
      }
      window.location.href = searchBase + encodeURIComponent(q);
      e.preventDefault();
      return false;
    });
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isOpen) {
      closeSearch();
    }
  });
})();
</script>
