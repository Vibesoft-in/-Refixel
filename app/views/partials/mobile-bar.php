<!-- Bottom Mobile Bar Matching Live REFIXEL Exactly -->
<div class="bottomBarNavbar">
  <ul>
    <li>
      <a href="<?= \App\Core\View::url('/') ?>">
        <img src="<?= \App\Core\View::asset('img/home2.png') ?>" alt="Home" width="22" height="22" loading="lazy" decoding="async">
        <h6>Home</h6>
      </a>
    </li>
    <li>
      <a href="<?= \App\Core\Auth::check() ? \App\Core\View::url('/account/bookings') : \App\Core\View::url('/login') ?>">
        <img src="<?= \App\Core\View::asset('img/write-book.png') ?>" alt="Booking" width="22" height="22" loading="lazy" decoding="async">
        <h6>Booking</h6>
      </a>
    </li>
    <li>
      <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%20Support" target="_blank" rel="noopener">
        <img src="<?= \App\Core\View::asset('img/whatsapp_icons_1.png') ?>" alt="WhatsApp" width="22" height="22" loading="lazy" decoding="async">
        <h6>WhatsApp</h6>
      </a>
    </li>
    <li>
      <a href="<?= \App\Core\View::url('/cart') ?>">
        <span id="footer_cart_badge" style="display:none;">0</span>
        <img src="<?= \App\Core\View::asset('img/shopping-cart.png') ?>" alt="Cart" width="22" height="22" loading="lazy" decoding="async">
        <h6>Cart</h6>
      </a>
    </li>
    <li>
      <a href="javascript:void(0)" class="menu_btn" id="mobileMenuBtn">
        <img src="<?= \App\Core\View::asset('img/menu.png') ?>" alt="Menu" width="22" height="22" loading="lazy" decoding="async">
        <h6>Menu</h6>
      </a>
    </li>
  </ul>
</div>

<!-- Floating WhatsApp Chat -->
<div class="whatsapchat">
  <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%20Support" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <i class="fa fa-whatsapp"></i>
  </a>
</div>

