<?php
$user = \App\Core\Auth::user();

// Determine active state for mobile drawer navigation links
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

$isHome = ($cleanPathLower === '/' || $cleanPathLower === '/index.php');
$isAbout = str_starts_with($cleanPathLower, '/about');
$isServices = (str_starts_with($cleanPathLower, '/services') || str_contains($cleanPathLower, '-services-in-') || str_contains($cleanPathLower, '-in-'));
$isBlogs = (str_starts_with($cleanPathLower, '/blogs') || str_starts_with($cleanPathLower, '/blog'));
$isContact = str_starts_with($cleanPathLower, '/contact');
?>
<div class="hometfn_popup">
  <div class="popup_footer">
    <button class="btnclose_tfn" type="button" aria-label="Close menu">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="#0F0F0F" xmlns="http://www.w3.org/2000/svg">
        <path d="M10.586 12l-6.043 6.043 1.414 1.414L12 13.414l6.043 6.043 1.414-1.414L13.414 12l6.043-6.043-1.414-1.414L12 10.586 5.957 4.543 4.543 5.957 10.586 12z" fill="#0F0F0F"></path>
      </svg>
    </button>

    <div class="menu_header">
      <div class="right_menu2">
        <img src="<?= \App\Core\View::asset('img/account.png') ?>" alt="Account">
        <h4><?= \App\Core\View::e($user['name'] ?? 'Welcome Guest') ?></h4>
        <h6><?= !empty($user['phone']) ? '+91-' . \App\Core\View::e($user['phone']) : 'Sign in to manage bookings' ?></h6>
      </div>
    </div>

    <?php if ($user): ?>
    <div class="bottom_option" style="padding-top: 5px; border-bottom: 1px solid #e2e8f0; margin-bottom: 10px;">
      <ul>
        <li><a href="<?= \App\Core\View::url('/account') ?>"><span class="menu_ico"><i class="fa fa-dashboard" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Dashboard <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/account/bookings') ?>"><span class="menu_ico"><i class="fa fa-calendar" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> My Bookings <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/account/invoices') ?>"><span class="menu_ico"><i class="fa fa-file-text-o" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Invoices & Receipts <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/account/profile') ?>"><span class="menu_ico"><i class="fa fa-user-circle" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Profile & Address <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/account/privacy') ?>"><span class="menu_ico"><i class="fa fa-shield" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Privacy & Data Rights <i class="fa fa-angle-right"></i></a></li>
      </ul>
    </div>
    <?php else: ?>
    <div class="btoption_one">
      <ul>
        <li>
          <a href="<?= \App\Core\View::url('/account/bookings') ?>">
            <span class="menu_ico"><img src="<?= \App\Core\View::asset('img/wirte.png') ?>" alt="Your Booking"></span> Your Booking
          </a>
        </li>
        <li>
          <a href="<?= \App\Core\View::url('/contact') ?>">
            <span class="menu_ico"><img src="<?= \App\Core\View::asset('img/office-building.png') ?>" alt="Need Help"></span> Need Help
          </a>
        </li>
      </ul>
    </div>
    <?php endif; ?>
    <div class="bottom_option">
      <ul>
        <li><a href="<?= \App\Core\View::url('/') ?>" class="<?= $isHome ? 'active' : '' ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/home2.png') ?>" alt="Home"></span> Home <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/about') ?>" class="<?= $isAbout ? 'active' : '' ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/info.png') ?>" alt="About"></span> About Us <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/services') ?>" class="<?= $isServices ? 'active' : '' ?>"><span class="menu_ico"><i class="fa fa-briefcase" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Services <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/blogs') ?>" class="<?= $isBlogs ? 'active' : '' ?>"><span class="menu_ico"><i class="fa fa-file-text" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Blogs <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/partner') ?>"><span class="menu_ico"><i class="fa fa-handshake-o" style="font-size:18px; width:20px; text-align:center; color:#f25b29;"></i></span> Service Partner <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/terms') ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/pages.png') ?>" alt="Terms"></span> Terms & Conditions <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/refund') ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/refund_policy.png') ?>" alt="Refund Policy"></span> Refund Policy <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/privacy') ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/privacy_policy.png') ?>" alt="Privacy Policy"></span> Privacy Policy <i class="fa fa-angle-right"></i></a></li>
        <li><a href="<?= \App\Core\View::url('/contact') ?>" class="<?= $isContact ? 'active' : '' ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/support.png') ?>" alt="Support"></span> Help & Support <i class="fa fa-angle-right"></i></a></li>
      </ul>

      <?php if ($user): ?>
        <ul class="logout_option">
          <li><a href="<?= \App\Core\View::url('/logout') ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/logout.png') ?>" alt="Log Out"></span> Log Out <i class="fa fa-angle-right"></i></a></li>
        </ul>
      <?php else: ?>
        <ul class="logout_option">
          <li><a href="<?= \App\Core\View::url('/login') ?>"><span class="menu_ico"><img src="<?= \App\Core\View::asset('img/account.png') ?>" alt="Sign In"></span> Sign In <i class="fa fa-angle-right"></i></a></li>
        </ul>
      <?php endif; ?>
      <div class="drawer_social_links text-center py-3 border-top" style="margin-top: 15px;">
        <p class="small text-muted mb-2 font-weight-bold">Follow REFIXEL</p>
        <div class="d-flex justify-content-center align-items-center" style="gap: 12px;">
          <a href="https://www.facebook.com/share/1GU16Dtfcr/" target="_blank" rel="noopener noreferrer" style="color: #64748b; font-size: 18px;" title="Facebook"><i class="fa fa-facebook-square"></i></a>
          <a href="https://www.instagram.com/letsrefixel?stkn=ajJtc20zcHRyM2Q3" target="_blank" rel="noopener noreferrer" style="color: #64748b; font-size: 18px;" title="Instagram"><i class="fa fa-instagram"></i></a>
          <a href="https://www.linkedin.com/in/lets-refixel-3aab5a43b?utm_source=share_via&amp;utm_content=profile&amp;utm_medium=member_android" target="_blank" rel="noopener noreferrer" style="color: #64748b; font-size: 18px;" title="LinkedIn"><i class="fa fa-linkedin-square"></i></a>
          <a href="https://x.com/letsrefixel" target="_blank" rel="noopener noreferrer" style="color: #64748b; font-size: 18px;" title="X (Twitter)"><i class="fa fa-twitter"></i></a>
          <a href="https://youtube.com/@letsrefixel?si=Xq0LZUFrv_yzlM5C" target="_blank" rel="noopener noreferrer" style="color: #64748b; font-size: 18px;" title="YouTube"><i class="fa fa-youtube-play"></i></a>
        </div>
      </div>
    </div>
  </div>
</div>
