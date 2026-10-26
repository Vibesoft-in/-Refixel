<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="robots" content="noindex, nofollow">
  <title><?= \App\Core\View::e($title ?? 'Technician Portal | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=3" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=3" rel="icon" type="image/png" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <?= \App\Core\Theme::renderHead() ?>
  <?= \App\Core\View::partial('partials.csrf-init') ?>
  <style>
    :root {
      --primary: #f25b29;
      --primary-dark: #d44d20;
      --primary-light: #fff3ef;
      --accent: #e59819;
      --dark: #1e1a18;
      --gray-bg: #f9f8f7;
      --card-border: #f0e8e4;
    }
    body {
      font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--gray-bg);
      color: #2c3e38;
      padding-bottom: 75px;
      margin: 0;
    }
    .staff-header {
      background: #0b2341;
      border-bottom: 2px solid var(--primary);
      color: #fff;
      padding: 8px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      box-shadow: 0 2px 10px rgba(11,35,65,0.18);
      position: sticky;
      top: 0;
      z-index: 999;
      width: 100%;
      box-sizing: border-box;
    }
    @media (min-width: 576px) {
      .staff-header {
        padding: 10px 18px;
        gap: 12px;
      }
    }
    .staff-header .staff-header-left {
      display: flex;
      align-items: center;
      gap: 6px;
      min-width: 0;
      flex: 1 1 auto;
      overflow: hidden;
      text-decoration: none;
    }
    .staff-header .staff-header-left:hover {
      text-decoration: none;
    }
    .staff-header .brand-logo-img {
      height: 28px;
      max-width: 96px;
      width: auto;
      object-fit: contain;
      border-radius: 4px;
      display: block;
      flex-shrink: 0;
    }
    @media (min-width: 576px) {
      .staff-header .brand-logo-img {
        height: 34px;
        max-width: 130px;
      }
    }
    .staff-header .brand-divider {
      color: rgba(255, 255, 255, 0.35);
      font-weight: 300;
      font-size: 14px;
      user-select: none;
      flex-shrink: 0;
      margin: 0 2px;
    }
    .staff-header .trade-badge {
      background: rgba(242, 91, 41, 0.22);
      border: 1px solid rgba(242, 91, 41, 0.55);
      color: #ff9d75;
      font-size: 10px;
      font-weight: 700;
      padding: 2.5px 7px;
      border-radius: 5px;
      letter-spacing: 0.3px;
      text-transform: uppercase;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      flex-shrink: 0;
      max-width: 110px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    @media (min-width: 576px) {
      .staff-header .trade-badge {
        font-size: 11.5px;
        padding: 3px 9px;
        border-radius: 6px;
        max-width: 180px;
      }
    }
    .staff-header .trade-badge i {
      font-size: 9.5px;
      margin-right: 4px;
      flex-shrink: 0;
    }
    .staff-header .staff-header-right {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }
    .staff-header .user-badge {
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.22);
      color: #fff;
      font-size: 11px;
      padding: 3px 8px;
      border-radius: 20px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      white-space: nowrap;
      max-width: 110px;
      overflow: hidden;
      text-overflow: ellipsis;
      flex-shrink: 0;
    }
    @media (min-width: 576px) {
      .staff-header .user-badge {
        font-size: 12px;
        padding: 4px 10px;
        max-width: 180px;
      }
    }
    .staff-header .user-badge i {
      font-size: 12px;
      margin-right: 4px;
      flex-shrink: 0;
    }
    .staff-header .user-badge:hover {
      background: rgba(255,255,255,0.18);
    }
    .staff-header .logout-link {
      color: rgba(255, 255, 255, 0.8);
      font-size: 15px;
      padding: 4px 6px;
      border-radius: 6px;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s ease;
      text-decoration: none;
      flex-shrink: 0;
    }
    .staff-header .logout-link:hover {
      color: #ef4444;
      background: rgba(239, 68, 68, 0.18);
      text-decoration: none;
    }
    .staff-bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #ffffff;
      border-top: 1px solid #e1e7ec;
      height: 62px;
      display: flex;
      z-index: 1000;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    .staff-bottom-nav a {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #64748b;
      font-size: 11px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.15s ease;
    }
    .staff-bottom-nav a.active, .staff-bottom-nav a:hover {
      color: var(--primary);
    }
    .staff-bottom-nav a i {
      font-size: 18px;
      margin-bottom: 3px;
    }
    .job-card {
      background: #fff;
      border-radius: 12px;
      border: 1px solid var(--card-border);
      padding: 16px;
      margin-bottom: 14px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.03);
      transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    .job-card:active {
      transform: scale(0.99);
    }
    .btn-brand {
      background-color: var(--primary);
      border-color: var(--primary);
      color: #ffffff !important;
      font-weight: 600;
      border-radius: 8px;
    }
    .btn-brand:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }
    .btn-brand-outline {
      background-color: transparent;
      border: 1.5px solid var(--primary);
      color: var(--primary) !important;
      font-weight: 600;
      border-radius: 8px;
    }
    .btn-brand-outline:hover {
      background-color: var(--primary);
      color: #fff !important;
    }
    .badge-status-assigned {
      background-color: #fef3c7;
      color: #92400e;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 6px;
    }
    .badge-status-accepted {
      background-color: #e0f2fe;
      color: #0369a1;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 6px;
    }
    .badge-status-in_progress {
      background-color: #dbeafe;
      color: #1d4ed8;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 6px;
    }
    .badge-status-completed {
      background-color: #d1fae5;
      color: #065f46;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 6px;
    }
    .badge-status-cancelled {
      background-color: #fee2e2;
      color: #991b1b;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <?php
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    $user = \App\Core\Auth::user();

    // Determine technician's trade / category skills dynamically
    $staffTradeTitle = 'Technician';
    $staffTradeFull = 'Technician';
    $userId = (int)($user['id'] ?? 0);
    if ($userId > 0) {
        try {
            $skillsList = \App\Core\Database::fetchAll(
                "SELECT c.name FROM staff_skills ss 
                 JOIN categories c ON ss.category_id = c.id 
                 WHERE ss.staff_id = :uid 
                 ORDER BY c.name ASC",
                ['uid' => $userId]
            );
            if (!empty($skillsList)) {
                $tradeNames = array_column($skillsList, 'name');
                $staffTradeFull = implode(', ', $tradeNames);
                if (count($tradeNames) === 1) {
                    $staffTradeTitle = $tradeNames[0];
                } elseif (count($tradeNames) === 2) {
                    $staffTradeTitle = implode(' & ', $tradeNames);
                } else {
                    $staffTradeTitle = $tradeNames[0] . ' +' . (count($tradeNames) - 1);
                }
            }
        } catch (\Throwable $e) {}
    }
  ?>
  <div class="staff-header">
    <a href="<?= \App\Core\View::url('/staff') ?>" class="staff-header-left" title="REFIXEL · <?= \App\Core\View::e($staffTradeFull) ?>">
      <img src="<?= \App\Core\View::asset('img/logo blue bg color.png') ?>?v=3" 
           alt="REFIXEL" 
           class="brand-logo-img"
           onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/refixel-logo-white-orange.png') ?>';">
      <span class="brand-divider d-none d-sm-inline">|</span>
      <span class="trade-badge" title="<?= \App\Core\View::e($staffTradeFull) ?>">
        <i class="fa fa-wrench"></i><?= \App\Core\View::e($staffTradeTitle) ?>
      </span>
    </a>
    <div class="staff-header-right">
      <?php
        $fullName = trim($user['name'] ?? 'Technician');
        $nameParts = explode(' ', $fullName);
        $firstName = $nameParts[0] ?? 'Tech';
      ?>
      <span class="user-badge" title="<?= \App\Core\View::e($fullName) ?>">
        <i class="fa fa-user-circle-o"></i>
        <span class="d-none d-sm-inline"><?= \App\Core\View::e($fullName) ?></span>
        <span class="d-inline d-sm-none"><?= \App\Core\View::e($firstName) ?></span>
      </span>
      <a href="<?= \App\Core\View::url('/logout') ?>" class="logout-link" title="Logout">
        <i class="fa fa-power-off"></i>
      </a>
    </div>
  </div>

  <div class="container py-3" style="max-width: 680px;">
    <?= \App\Core\View::partial('flash') ?>
    <?= $content ?? '' ?>
  </div>

  <nav class="staff-bottom-nav">
    <a href="<?= \App\Core\View::url('/staff') ?>" class="<?= (str_ends_with($currentUri, '/staff') || str_contains($currentUri, '/staff/dashboard')) ? 'active' : '' ?>">
      <i class="fa fa-dashboard"></i>
      <span>Dashboard</span>
    </a>
    <a href="<?= \App\Core\View::url('/staff/jobs') ?>" class="<?= str_contains($currentUri, '/staff/jobs') ? 'active' : '' ?>">
      <i class="fa fa-briefcase"></i>
      <span>My Jobs</span>
    </a>
    <a href="<?= \App\Core\View::url('/staff/earnings') ?>" class="<?= str_contains($currentUri, '/staff/earnings') ? 'active' : '' ?>">
      <i class="fa fa-inr"></i>
      <span>Earnings</span>
    </a>
    <a href="<?= \App\Core\View::url('/staff/profile') ?>" class="<?= str_contains($currentUri, '/staff/profile') ? 'active' : '' ?>">
      <i class="fa fa-user"></i>
      <span>Profile</span>
    </a>
  </nav>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</body>
</html>

