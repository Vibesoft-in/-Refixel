<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= \App\Core\View::e($title ?? 'Technician Portal | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=2" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=2" rel="icon" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
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
      background: var(--primary);
      color: #fff;
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(15,110,86,0.15);
      position: sticky;
      top: 0;
      z-index: 999;
    }
    .staff-header .brand-title {
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.2px;
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .staff-header .user-badge {
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.25);
      color: #fff;
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
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
  ?>
  <div class="staff-header">
    <a href="<?= \App\Core\View::url('/staff') ?>" class="brand-title">
      <img src="<?= \App\Core\View::asset('img/favicon.png') ?>" alt="REFIXEL" style="height: 22px;">
      <span>REFIXEL Field Ops</span>
    </a>
    <div class="d-flex align-items-center">
      <span class="user-badge mr-2">
        <i class="fa fa-user-circle-o mr-1"></i><?= \App\Core\View::e($user['name'] ?? 'Technician') ?>
      </span>
      <a href="<?= \App\Core\View::url('/logout') ?>" class="text-white" title="Logout" style="font-size: 16px;">
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
</body>
</html>

