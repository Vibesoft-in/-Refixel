<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title><?= \App\Core\View::e($title ?? 'Admin Panel | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=2" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=2" rel="icon" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary: #f25b29;
      --primary-dark: #d44d20;
      --primary-light: #fff3ef;
      --dark-sidebar: #0a1c33;
      --dark-sidebar-hover: #122844;
      --dark-sidebar-active: rgba(242, 91, 41, 0.16);
      --body-bg: #f8fafc;
      --border-color: #e2e8f0;
    }
    html, body { 
      height: 100%; 
      margin: 0; 
      padding: 0; 
      overflow: hidden; 
      font-family: 'Inter Tight', sans-serif; 
      background: var(--body-bg); 
      color: #0f172a; 
    }
    .admin-wrapper { 
      display: flex; 
      height: 100vh; 
      height: 100dvh; 
      width: 100%; 
      overflow: hidden; 
    }
    .admin-sidebar { 
      width: 260px; 
      min-width: 260px; 
      max-width: 260px; 
      height: 100vh; 
      height: 100dvh; 
      background: linear-gradient(180deg, #0a1c33 0%, #061426 100%); 
      color: #fff; 
      flex-shrink: 0; 
      display: flex; 
      flex-direction: column; 
      border-right: 1px solid rgba(255, 255, 255, 0.07); 
    }
    .admin-sidebar .brand { 
      padding: 18px 20px; 
      font-weight: 700; 
      font-size: 17px; 
      border-bottom: 1px solid rgba(255,255,255,0.08); 
      display: flex; 
      align-items: center; 
      justify-content: space-between; 
      flex-shrink: 0; 
      background: #0a1c33; 
    }
    .admin-sidebar .brand img { height: 26px; }
    .admin-nav { 
      list-style: none; 
      padding: 14px 0 40px; 
      margin: 0; 
      flex: 1 1 auto; 
      min-height: 0; 
      overflow-y: auto; 
      overflow-x: hidden; 
      overscroll-behavior: contain; 
      scroll-behavior: smooth; 
      -webkit-overflow-scrolling: touch; 
      scrollbar-width: thin; 
      scrollbar-color: rgba(255, 255, 255, 0.22) rgba(0, 0, 0, 0.15); 
    }
    /* Elegant slim scrollbar for Left Sidebar */
    .admin-nav::-webkit-scrollbar { 
      width: 5px; 
    }
    .admin-nav::-webkit-scrollbar-track { 
      background: rgba(0, 0, 0, 0.15); 
    }
    .admin-nav::-webkit-scrollbar-thumb { 
      background: rgba(255, 255, 255, 0.2); 
      border-radius: 4px; 
      transition: background 0.2s ease; 
    }
    .admin-nav::-webkit-scrollbar-thumb:hover { 
      background: rgba(242, 91, 41, 0.75); 
    }
    .admin-nav li a { display: flex; align-items: center; justify-content: space-between; padding: 11px 20px; color: #94a3b8; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); border-left: 3.5px solid transparent; }
    .admin-nav li a:hover { color: #ffffff; background: rgba(255, 255, 255, 0.05); }
    .admin-nav li.active a { color: #ffffff; font-weight: 600; background: linear-gradient(90deg, rgba(242, 91, 41, 0.18) 0%, rgba(242, 91, 41, 0.03) 100%); border-left: 3.5px solid #f25b29; }
    .admin-nav li a .nav-text { display: flex; align-items: center; }
    .admin-nav li a i { width: 22px; font-size: 15px; margin-right: 11px; color: #64748b; text-align: center; transition: color 0.2s ease; }
    .admin-nav li a:hover i, .admin-nav li.active a i { color: #f25b29; }
    .admin-nav .nav-heading { 
      padding: 18px 20px 7px; 
      font-size: 11px; 
      text-transform: uppercase; 
      letter-spacing: 1px; 
      color: #f25b29; 
      font-weight: 800; 
      display: flex; 
      align-items: center; 
    }
    .admin-nav .nav-heading::after {
      content: '';
      flex: 1;
      height: 1px;
      margin-left: 10px;
      background: linear-gradient(90deg, rgba(242, 91, 41, 0.35) 0%, rgba(242, 91, 41, 0) 100%);
    }
    
    .nav-count-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 20px; padding: 0 7px; font-size: 11px; font-weight: 700; border-radius: 9999px; line-height: 1; transition: all 0.2s ease; }
    .nav-badge-orange { background: #f25b29; color: #ffffff; box-shadow: 0 2px 7px rgba(242, 91, 41, 0.45); }
    .nav-badge-blue { background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4); }
    .nav-badge-green { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
    
    .admin-nav li.active .nav-badge-blue { background: #0284c7; color: #fff; border-color: #0284c7; }
    .admin-nav li.active .nav-badge-green { background: #059669; color: #fff; border-color: #059669; }

    .admin-content { 
      flex: 1 1 auto; 
      height: 100vh; 
      height: 100dvh; 
      display: flex; 
      flex-direction: column; 
      overflow-y: auto; 
      overflow-x: hidden; 
      min-width: 0; 
      scroll-behavior: smooth; 
      scrollbar-width: thin; 
      scrollbar-color: #cbd5e1 #f8fafc; 
    }
    /* Sleek slim custom scrollbar for Main Content */
    .admin-content::-webkit-scrollbar { 
      width: 6px; 
      height: 6px; 
    }
    .admin-content::-webkit-scrollbar-track { 
      background: #f8fafc; 
    }
    .admin-content::-webkit-scrollbar-thumb { 
      background: #cbd5e1; 
      border-radius: 4px; 
      transition: background 0.2s ease; 
    }
    .admin-content::-webkit-scrollbar-thumb:hover { 
      background: #94a3b8; 
    }

    .admin-topbar { 
      flex-shrink: 0; 
      height: 60px; 
      background: #fff; 
      border-bottom: 1px solid var(--border-color); 
      display: flex; 
      align-items: center; 
      justify-content: space-between; 
      padding: 0 24px; 
      box-shadow: 0 1px 3px rgba(0,0,0,0.02); 
      position: sticky; 
      top: 0; 
      z-index: 10; 
    }
    .admin-body { padding: 24px; flex: 1; min-height: min-content; }

    /* Responsive / Mobile off-canvas drawer */
    @media (max-width: 991.98px) {
      html, body {
        overflow: auto;
      }
      .admin-wrapper {
        height: auto;
        min-height: 100vh;
        overflow: visible;
      }
      .admin-sidebar {
        position: fixed;
        top: 0;
        left: -260px;
        bottom: 0;
        z-index: 1040;
        transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.3);
      }
      .admin-sidebar.show {
        left: 0;
      }
      .sidebar-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        z-index: 1030;
      }
      .sidebar-backdrop.show {
        display: block;
      }
      .admin-content {
        height: auto;
        max-height: none;
        overflow-y: visible;
      }
    }
    .stat-card { background: #fff; border-radius: 10px; border: 1px solid var(--border-color); padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); margin-bottom: 18px; }
    .stat-card .stat-label { font-size: 11.5px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.3px; }
    .stat-card .stat-value { font-size: 26px; font-weight: 700; color: #0f1c18; margin: 4px 0; }
    .btn-brand { background-color: var(--primary); border-color: var(--primary); color: #fff !important; font-weight: 600; border-radius: 6px; }
    .btn-brand:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
    .btn-brand-outline { background: transparent; border: 1.5px solid var(--primary); color: var(--primary) !important; font-weight: 600; border-radius: 6px; }
    .btn-brand-outline:hover { background-color: var(--primary); color: #fff !important; }
    .table thead th { background: #f8faf9; border-bottom: 1px solid var(--border-color); font-size: 12px; text-transform: uppercase; color: #475569; font-weight: 700; padding: 12px 14px; }
    .table tbody td { vertical-align: middle; font-size: 13.5px; padding: 12px 14px; border-bottom: 1px solid #edf2f0; }
    .badge-status-new { background-color: #fef3c7; color: #92400e; font-weight: 600; padding: 4px 8px; border-radius: 5px; }
    .badge-status-assigned { background-color: #e0f2fe; color: #0369a1; font-weight: 600; padding: 4px 8px; border-radius: 5px; }
    .badge-status-accepted { background-color: #ede9fe; color: #5b21b6; font-weight: 600; padding: 4px 8px; border-radius: 5px; }
    .badge-status-in_progress { background-color: #dbeafe; color: #1e40af; font-weight: 600; padding: 4px 8px; border-radius: 5px; }
    .badge-status-completed { background-color: #d1fae5; color: #065f46; font-weight: 600; padding: 4px 8px; border-radius: 5px; }
    .badge-status-cancelled { background-color: #fee2e2; color: #991b1b; font-weight: 600; padding: 4px 8px; border-radius: 5px; }
  </style>
</head>
<body>
  <?php
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $user = \App\Core\Auth::user();

    // Fetch live sidebar counters for items requiring admin attention
    $sidebarCounts = [
        'enquiries_new'    => 0,
        'bookings_pending' => 0,
        'reviews_pending'  => 0,
    ];

    try {
        $statsRow = \App\Core\Database::fetchOne(
            "SELECT 
                (SELECT COUNT(id) FROM bookings WHERE status = 'new') as enquiries_new,
                (SELECT COUNT(id) FROM bookings WHERE status = 'accepted') as bookings_pending,
                (SELECT COUNT(id) FROM reviews WHERE is_approved = 0) as reviews_pending"
        );
        if ($statsRow) {
            $sidebarCounts['enquiries_new']    = (int)($statsRow['enquiries_new'] ?? 0);
            $sidebarCounts['bookings_pending'] = (int)($statsRow['bookings_pending'] ?? 0);
            $sidebarCounts['reviews_pending']  = (int)($statsRow['reviews_pending'] ?? 0);
        }
    } catch (\Throwable $e) {}
  ?>
  <div class="admin-wrapper">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="admin-sidebar">
      <div class="brand">
        <a href="<?= \App\Core\View::url('/admin') ?>" class="text-white text-decoration-none d-flex align-items-center">
          <img src="<?= \App\Core\View::asset('img/favicon.png') ?>" alt="REFIXEL" style="height: 26px; width: 26px; object-fit: contain; margin-right: 10px;">
          <span style="font-weight: 800; font-size: 17px; letter-spacing: -0.2px; color: #fff;">REFIXEL</span>
        </a>
        <span class="badge" style="background: rgba(242, 91, 41, 0.2); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.35); font-weight: 700; font-size: 10.5px; padding: 3px 8px; border-radius: 6px;">ADMIN</span>
      </div>

      <ul class="admin-nav">
        <li class="<?= ($uri === '/website/public/admin' || $uri === '/website/admin' || str_ends_with($uri, '/admin') || str_contains($uri, '/admin/dashboard')) ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin') ?>">
            <span class="nav-text"><i class="fa fa-dashboard"></i> Dashboard</span>
          </a>
        </li>

        <li class="nav-heading">Operations</li>
        <li class="<?= str_contains($uri, '/admin/enquiries') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/enquiries') ?>">
            <span class="nav-text"><i class="fa fa-inbox"></i> Enquiries</span>
            <?php if (!empty($sidebarCounts['enquiries_new']) && $sidebarCounts['enquiries_new'] > 0): ?>
              <span class="nav-count-badge nav-badge-orange" title="<?= $sidebarCounts['enquiries_new'] ?> New Enquiries"><?= $sidebarCounts['enquiries_new'] ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= (str_contains($uri, '/admin/bookings') && !str_contains($uri, '/admin/bookings/calendar')) ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/bookings') ?>">
            <span class="nav-text"><i class="fa fa-calendar-check-o"></i> Bookings & Jobs</span>
            <?php if (!empty($sidebarCounts['bookings_pending']) && $sidebarCounts['bookings_pending'] > 0): ?>
              <span class="nav-count-badge nav-badge-blue" title="<?= $sidebarCounts['bookings_pending'] ?> Bookings Awaiting Action"><?= $sidebarCounts['bookings_pending'] ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/calendar') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/calendar') ?>">
            <span class="nav-text"><i class="fa fa-calendar"></i> Dispatch Calendar</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/staff') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/staff') ?>">
            <span class="nav-text"><i class="fa fa-users"></i> Staff & Techs</span>
          </a>
        </li>

        <li class="nav-heading">Services & Catalog</li>
        <li class="<?= str_contains($uri, '/admin/services') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/services') ?>">
            <span class="nav-text"><i class="fa fa-wrench"></i> Services & Catalog</span>
          </a>
        </li>

        <li class="nav-heading">Gateways & Commissions</li>
        <li class="<?= str_contains($uri, '/admin/gateways') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/gateways') ?>">
            <span class="nav-text"><i class="fa fa-credit-card"></i> Payment Gateways</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/commissions') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/commissions') ?>">
            <span class="nav-text"><i class="fa fa-percent"></i> Commissions & Splits</span>
          </a>
        </li>

        <li class="nav-heading">Finance & Invoices</li>
        <li class="<?= str_contains($uri, '/admin/payments') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/payments') ?>">
            <span class="nav-text"><i class="fa fa-money"></i> Payments & Invoices</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/reports') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/reports') ?>">
            <span class="nav-text"><i class="fa fa-bar-chart"></i> Analytics & Reports</span>
          </a>
        </li>

        <li class="nav-heading">CMS & Settings</li>
        <li class="<?= str_contains($uri, '/admin/content/faqs') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/faqs') ?>">
            <span class="nav-text"><i class="fa fa-question-circle"></i> FAQs</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/gallery') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/gallery') ?>">
            <span class="nav-text"><i class="fa fa-picture-o"></i> Gallery Showcase</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/reviews') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/reviews') ?>">
            <span class="nav-text"><i class="fa fa-star"></i> Reviews Approval</span>
            <?php if (!empty($sidebarCounts['reviews_pending']) && $sidebarCounts['reviews_pending'] > 0): ?>
              <span class="nav-count-badge nav-badge-orange" title="<?= $sidebarCounts['reviews_pending'] ?> Reviews Awaiting Approval"><?= $sidebarCounts['reviews_pending'] ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/areas') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/areas') ?>">
            <span class="nav-text"><i class="fa fa-map-marker"></i> Service Areas</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/steps') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/steps') ?>">
            <span class="nav-text"><i class="fa fa-list-ol"></i> Process Steps</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/checklists') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/checklists') ?>">
            <span class="nav-text"><i class="fa fa-check-square-o"></i> Service Checklists</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/settings') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/settings') ?>">
            <span class="nav-text"><i class="fa fa-cog"></i> Business Settings</span>
          </a>
        </li>

        <li class="mt-3 border-top border-secondary pt-2">
          <a href="<?= \App\Core\View::url('/logout') ?>">
            <span class="nav-text"><i class="fa fa-sign-out text-danger"></i> Logout</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="admin-content">
      <div class="admin-topbar">
        <div class="d-flex align-items-center">
          <button type="button" class="btn btn-sm btn-outline-secondary mr-2 d-lg-none" id="sidebarToggle" aria-label="Toggle navigation" style="border-radius: 6px; padding: 4px 9px;">
            <i class="fa fa-bars text-dark"></i>
          </button>
          <strong class="text-dark" style="font-size: 15px;"><i class="fa fa-shield mr-2" style="color: #f25b29;"></i>REFIXEL Operations Hub</strong>
        </div>
        <div class="d-flex align-items-center">
          <span class="mr-3 text-muted small">
            <i class="fa fa-user-circle mr-1" style="color: #f25b29;"></i><?= \App\Core\View::e($user['name'] ?? 'Super Admin') ?>
          </span>
          <a href="<?= \App\Core\View::url('/') ?>" target="_blank" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 6px;">
            <i class="fa fa-external-link mr-1"></i>Live Site
          </a>
        </div>
      </div>

      <div class="admin-body">
        <?= \App\Core\View::partial('flash') ?>
        <?= $content ?? '' ?>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var toggleBtn = document.getElementById('sidebarToggle');
      var sidebar = document.querySelector('.admin-sidebar');
      var backdrop = document.getElementById('sidebarBackdrop');

      if (toggleBtn && sidebar && backdrop) {
        toggleBtn.addEventListener('click', function () {
          sidebar.classList.toggle('show');
          backdrop.classList.toggle('show');
        });
        backdrop.addEventListener('click', function () {
          sidebar.classList.remove('show');
          backdrop.classList.remove('show');
        });
      }

      // Smoothly scroll active nav item into view in the sidebar if needed
      var activeNav = document.querySelector('.admin-nav li.active');
      if (activeNav) {
        activeNav.scrollIntoView({ block: 'nearest', inline: 'nearest' });
      }
    });
  </script>
</body>
</html>

