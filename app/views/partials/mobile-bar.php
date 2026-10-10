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
      <a href="<?= \App\Core\View::url('/cart') ?>" class="<?= $isCart ? 'active' : '' ?>" data-nav="cart">
        <span class="bm-icon-circle">
          <span id="footer_cart_badge" style="display:none;">0</span>
          <img src="<?= \App\Core\View::asset('img/shopping-cart.png') ?>" alt="Cart" width="22" height="22" loading="lazy" decoding="async">
        </span>
        <h6>Cart</h6>
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
/* Modern Bottom Taskbar Navigation Styling */
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
</style>

<!-- Floating WhatsApp Chat -->
<div class="whatsapchat">
  <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%20Support" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <i class="fa fa-whatsapp"></i>
  </a>
</div>
