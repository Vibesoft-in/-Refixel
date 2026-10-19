<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title><?= \App\Core\View::e($title ?? 'Authentication | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=2" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=2" rel="icon" type="image/png" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link rel="stylesheet" href="<?= \App\Core\View::asset('css/style.css') ?>?v=<?= filemtime(ROOT_PATH . '/public/assets/css/style.css') ?>" />
  <?= \App\Core\Theme::renderHead() ?>
  <style>
    body { background-color: #f7faf9; }
    .auth-card { max-width: 440px; margin: 60px auto; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .auth-logo { text-align: center; margin-bottom: 24px; }
    .auth-logo img { height: 42px; }
    .btn-primary-custom { background-color: var(--primary-color, #f25b29); border-color: var(--primary-color, #f25b29); color: #fff; font-weight: 600; width: 100%; border-radius: 6px; padding: 10px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25); }
    .btn-primary-custom:hover { background-color: var(--primary-hover, #db4918); border-color: var(--primary-hover, #db4918); color: #fff; }
    .password-toggle-btn { background: #fff; border-color: #ced4da; border-left: none; color: #94a3b8; cursor: pointer; transition: all 0.2s ease; }
    .password-toggle-btn:hover { color: var(--primary-color, #f25b29); background-color: #f8fafc; }
    .password-toggle-btn:focus { outline: none; box-shadow: none; }
  </style>
</head>
<body>
  <div class="container">
    <?= \App\Core\View::partial('flash') ?>
    <div class="auth-card">
      <div class="auth-logo">
        <a href="<?= \App\Core\View::url('/') ?>">
          <img src="<?= \App\Core\View::asset(\App\Core\Theme::getLogo()) ?>" alt="<?= \App\Core\View::e(\App\Core\Theme::getBrandName()) ?>" style="height: 48px; width: auto; object-fit: contain;">
        </a>
      </div>
      <?= $content ?? '' ?>
    </div>
  </div>

  <!-- Shared Mobile Drawer & Bottom Navigation Bar -->
  <?= \App\Core\View::partial('mobile-drawer') ?>
  <?= \App\Core\View::partial('mobile-bar') ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <script>
  function togglePasswordVisibility(targetId, btn) {
    var input = document.getElementById(targetId);
    if (!input) return;
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      if (icon) {
        icon.className = 'fa fa-eye-slash';
        icon.style.color = '#f25b29';
      }
      btn.setAttribute('title', 'Hide password');
    } else {
      input.type = 'password';
      if (icon) {
        icon.className = 'fa fa-eye';
        icon.style.color = '#94a3b8';
      }
      btn.setAttribute('title', 'Show password');
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Mobile Drawer Toggle
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
  });
  </script>
</body>
</html>

