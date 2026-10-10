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
/* Active taskbar icon styling with brand orange background */
.bottomBarNavbar ul li a .bm-icon-circle {
  width: 32px;
  height: 32px;
  min-width: 32px;
  min-height: 32px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2px;
  transition: all 0.24s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  background: transparent;
}
.bottomBarNavbar ul li a.active .bm-icon-circle {
  background: #f25b29 !important;
  box-shadow: 0 3px 10px rgba(242, 91, 41, 0.45) !important;
}
.bottomBarNavbar ul li a.active .bm-icon-circle img {
  filter: brightness(0) invert(1) !important;
  transform: scale(1.05);
}
.bottomBarNavbar ul li a.active h6 {
  color: #f25b29 !important;
  font-weight: 700 !important;
}
.bottomBarNavbar ul li a:hover .bm-icon-circle {
  transform: translateY(-2px);
}
</style>

<!-- Floating WhatsApp Chat -->
<div class="whatsapchat">
  <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%20Support" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <i class="fa fa-whatsapp"></i>
  </a>
</div>
