<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title><?= \App\Core\View::e($title ?? 'Admin Panel | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=3" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset(\App\Core\Theme::getFavicon()) ?>?v=3" rel="icon" type="image/png" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <?= \App\Core\Theme::renderHead() ?>
  <?= \App\Core\View::partial('partials.csrf-init') ?>
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
      padding: 12px 14px; 
      font-weight: 700; 
      font-size: 17px; 
      border-bottom: 1px solid rgba(255,255,255,0.08); 
      display: flex; 
      align-items: center; 
      justify-content: space-between; 
      gap: 8px;
      flex-shrink: 0; 
      background: #0a1c33; 
      min-height: 56px;
    }
    .admin-sidebar .brand a.brand-logo {
      display: flex;
      align-items: center;
      min-width: 0;
      flex: 1;
      text-decoration: none;
    }
    .admin-sidebar .brand img { 
      height: 24px; 
      max-width: 112px;
      width: auto;
      object-fit: contain;
      display: block;
    }
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
        left: -270px;
        bottom: 0;
        width: 270px;
        max-width: 85vw;
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
        background: rgba(0, 0, 0, 0.55);
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
      .admin-topbar { padding: 0 16px; }
      .admin-body { padding: 18px 14px; }
    }

    @media (max-width: 575.98px) {
      .admin-topbar { padding: 0 8px; height: 54px; }
      .admin-body { padding: 10px 8px; }
      .stat-card { padding: 10px; margin-bottom: 10px; border-radius: 8px; }
    }

    .admin-table-min { min-width: 680px; }
    .admin-table-min-lg { min-width: 820px; }
    .dash-stat-card {
      background: #fff;
      border-radius: 10px;
      border: 1px solid var(--border-color);
      padding: 14px 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      position: relative;
    }
    .dash-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .dash-stat-card .stat-icon-circle {
      width: 36px;
      height: 36px;
      min-width: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }
    @media (max-width: 575.98px) {
      .dash-stat-card {
        padding: 10px 8px;
        margin-bottom: 8px;
      }
      .dash-stat-card .stat-icon-circle {
        width: 30px;
        height: 30px;
        min-width: 30px;
        font-size: 12px;
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
    .badge-pay-paid { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700; padding: 3px 8px; border-radius: 5px; font-size: 11px; letter-spacing: 0.3px; display: inline-flex; align-items: center; }
    .badge-pay-pre { background-color: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: 700; padding: 3px 8px; border-radius: 5px; font-size: 11px; letter-spacing: 0.3px; display: inline-flex; align-items: center; }
  </style>
</head>
<body>
  <?php
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $user = \App\Core\Auth::user();

    // Fetch live sidebar counters for items requiring admin attention
    $sidebarCounts = [
        'enquiries_new'        => 0,
        'bookings_pending'     => 0,
        'reviews_pending'      => 0,
        'partner_pending'      => 0,
        'payouts_pending'      => 0,
        'dropoffs_unrecovered' => 0,
    ];

    try {
        $statsRow = \App\Core\Database::fetchOne(
            "SELECT 
                (SELECT COUNT(id) FROM bookings WHERE status = 'new' AND id NOT IN (SELECT booking_id FROM abandoned_checkouts WHERE is_recovered = 0 AND booking_id IS NOT NULL)) as enquiries_new,
                (SELECT COUNT(id) FROM bookings WHERE status = 'accepted') as bookings_pending,
                (SELECT COUNT(id) FROM reviews WHERE is_approved = 0) as reviews_pending,
                (SELECT COUNT(id) FROM users WHERE role = 'staff' AND status = 'pending') as partner_pending,
                (SELECT COUNT(id) FROM staff_earnings WHERE is_settled = 0) as payouts_pending,
                (SELECT COUNT(id) FROM abandoned_checkouts WHERE is_recovered = 0) as dropoffs_unrecovered"
        );
        if ($statsRow) {
            $sidebarCounts['enquiries_new']        = (int)($statsRow['enquiries_new'] ?? 0);
            $sidebarCounts['bookings_pending']     = (int)($statsRow['bookings_pending'] ?? 0);
            $sidebarCounts['reviews_pending']      = (int)($statsRow['reviews_pending'] ?? 0);
            $sidebarCounts['partner_pending']      = (int)($statsRow['partner_pending'] ?? 0);
            $sidebarCounts['payouts_pending']      = (int)($statsRow['payouts_pending'] ?? 0);
            $sidebarCounts['dropoffs_unrecovered'] = (int)($statsRow['dropoffs_unrecovered'] ?? 0);
        }
    } catch (\Throwable $e) {}
  ?>
  <div class="admin-wrapper">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="admin-sidebar">
      <div class="brand">
        <a href="<?= \App\Core\View::url('/admin') ?>" class="brand-logo" title="REFIXEL Admin Panel">
          <img src="<?= \App\Core\View::asset('img/refixel-logo-white-orange.png') ?>?v=3" 
               alt="REFIXEL" 
               onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/refixel-logo.png') ?>';">
        </a>
        <?php if (($user['role'] ?? '') === 'super_admin'): ?>
          <a href="<?= \App\Core\View::url('/superadmin') ?>" class="badge badge-danger flex-shrink-0" style="font-weight: 800; font-size: 9px; padding: 3px 6px; border-radius: 5px; letter-spacing: 0.3px; text-decoration: none; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 2px 6px rgba(239, 68, 68, 0.45); white-space: nowrap;" title="Switch to Super Admin"><i class="fa fa-shield mr-1"></i>SUPER ADMIN</a>
        <?php else: ?>
          <span class="badge flex-shrink-0" style="background: rgba(242, 91, 41, 0.2); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.35); font-weight: 700; font-size: 9.5px; padding: 3px 6px; border-radius: 5px; letter-spacing: 0.4px;">ADMIN</span>
        <?php endif; ?>
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
        <li class="<?= str_contains($uri, '/admin/dropoffs') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/dropoffs') ?>">
            <span class="nav-text"><i class="fa fa-exclamation-triangle" style="color: #f59e0b;"></i> Abandoned & Drop-offs</span>
            <?php if (!empty($sidebarCounts['dropoffs_unrecovered']) && $sidebarCounts['dropoffs_unrecovered'] > 0): ?>
              <span class="nav-count-badge" style="background: #f59e0b; color: #ffffff;" title="<?= $sidebarCounts['dropoffs_unrecovered'] ?> Unrecovered Drop-offs"><?= $sidebarCounts['dropoffs_unrecovered'] ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/staff') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/staff') ?>">
            <span class="nav-text"><i class="fa fa-users"></i> Staff & Techs</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/partner-applications') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/partner-applications') ?>">
            <span class="nav-text"><i class="fa fa-id-card-o"></i> Partner Applications</span>
            <?php if (!empty($sidebarCounts['partner_pending']) && $sidebarCounts['partner_pending'] > 0): ?>
              <span class="nav-count-badge nav-badge-orange" title="<?= $sidebarCounts['partner_pending'] ?> Pending Partner Applications"><?= $sidebarCounts['partner_pending'] ?></span>
            <?php endif; ?>
          </a>
        </li>

        <li class="nav-heading">Services & Catalog</li>
        <li class="<?= ($uri === '/admin/services' || str_starts_with($uri, '/admin/services/categories')) ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/services') ?>">
            <span class="nav-text"><i class="fa fa-wrench"></i> Services & Catalog</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/admin/services/addons') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/services/addons') ?>">
            <span class="nav-text"><i class="fa fa-puzzle-piece"></i> Add-ons Control</span>
            <span class="nav-count-badge" style="background: rgba(242, 91, 41, 0.15); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.3);">Smart</span>
          </a>
        </li>

        <?php if (($user['role'] ?? '') === 'super_admin'): ?>
        <li class="nav-heading" style="color: #818cf8;">Super Admin</li>
        <li>
          <a href="<?= \App\Core\View::url('/superadmin') ?>" style="background: rgba(99, 102, 241, 0.15); color: #c7d2fe; border-left: 3.5px solid #6366f1;">
            <span class="nav-text"><i class="fa fa-shield" style="color: #818cf8;"></i> Super Admin Center &rarr;</span>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-heading">Finance & Invoices</li>
        <li class="<?= str_contains($uri, '/admin/payouts') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/payouts') ?>">
            <span class="nav-text"><i class="fa fa-handshake-o"></i> Technician Payouts</span>
            <?php if (!empty($sidebarCounts['payouts_pending']) && $sidebarCounts['payouts_pending'] > 0): ?>
              <span class="nav-count-badge nav-badge-orange" title="<?= $sidebarCounts['payouts_pending'] ?> Pending Technician Payouts"><?= $sidebarCounts['payouts_pending'] ?></span>
            <?php endif; ?>
          </a>
        </li>
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

        <li class="<?= str_contains($uri, '/change-password') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/change-password') ?>">
            <span class="nav-text"><i class="fa fa-key"></i> Change Password</span>
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
          <button type="button" class="btn btn-sm btn-outline-secondary mr-2 d-lg-none" id="sidebarToggle" aria-label="Toggle navigation" style="border-radius: 6px; padding: 4px 8px;">
            <i class="fa fa-bars text-dark"></i>
          </button>
          <strong class="text-dark d-none d-sm-inline" style="font-size: 14.5px; white-space: nowrap;">
            <i class="fa fa-cubes mr-1" style="color: #f25b29;"></i>REFIXEL Operations Hub
          </strong>
          <strong class="text-dark d-sm-none" style="font-size: 13.5px; white-space: nowrap;">
            <i class="fa fa-cubes mr-1" style="color: #f25b29;"></i>Ops Hub
          </strong>
        </div>
        <div class="d-flex align-items-center flex-shrink-0">
          <?php if (($user['role'] ?? '') === 'super_admin'): ?>
            <a href="<?= \App\Core\View::url('/superadmin') ?>" class="btn btn-sm btn-danger font-weight-bold mr-1 mr-sm-2 px-2 py-1" style="border-radius: 6px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.35); white-space: nowrap; font-size: 11.5px;" title="Super Admin Control Center">
              <i class="fa fa-shield mr-1"></i><span class="d-none d-sm-inline">Super Admin</span><span class="d-sm-none">Super</span>
            </a>
          <?php endif; ?>
          <a href="<?= \App\Core\View::url('/change-password') ?>" class="btn btn-sm btn-outline-secondary font-weight-bold mr-1 mr-sm-2 px-2 py-1" style="border-radius: 6px; font-size: 11.5px;" title="Change Password">
            <i class="fa fa-key text-warning"></i><span class="d-none d-sm-inline ml-1">Password</span>
          </a>
          <span class="mr-2 mr-sm-3 text-muted small d-none d-lg-inline text-truncate" style="max-width: 140px;">
            <i class="fa fa-user-circle mr-1" style="color: #f25b29;"></i><?= \App\Core\View::e($user['name'] ?? 'Admin') ?>
          </span>
          <a href="<?= \App\Core\View::url('/') ?>" target="_blank" class="btn btn-sm btn-outline-secondary font-weight-bold px-2 py-1" style="border-radius: 6px; white-space: nowrap; font-size: 11.5px;" title="View Live Website">
            <i class="fa fa-external-link"></i><span class="d-none d-sm-inline ml-1">Live Site</span>
          </a>
        </div>
      </div>

      <div class="admin-body">
        <?= \App\Core\View::partial('2fa-notice') ?>
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
  </script>
</body>
</html>

