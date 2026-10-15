<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title><?= \App\Core\View::e($title ?? 'Super Admin Panel | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=3" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=3" rel="icon" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --sa-primary: #6366f1;
      --sa-primary-dark: #4f46e5;
      --sa-danger: #ef4444;
      --sa-sidebar-bg: #090d16;
      --sa-sidebar-grad: linear-gradient(180deg, #090d16 0%, #0e1726 100%);
      --sa-body-bg: #f1f5f9;
      --sa-border: #e2e8f0;
      --sa-card-bg: #ffffff;
    }
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
      font-family: 'Inter Tight', sans-serif;
      background: var(--sa-body-bg);
      color: #0f172a;
    }
    .super-wrapper {
      display: flex;
      height: 100vh;
      height: 100dvh;
      width: 100%;
      overflow: hidden;
    }
    .super-sidebar {
      width: 270px;
      min-width: 270px;
      max-width: 270px;
      height: 100vh;
      height: 100dvh;
      background: var(--sa-sidebar-grad);
      color: #fff;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      border-right: 1px solid rgba(255, 255, 255, 0.08);
      position: relative;
      z-index: 20;
    }
    .super-sidebar .brand {
      padding: 12px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      background: #090d16;
      min-height: 56px;
    }
    .super-sidebar .brand a.brand-logo {
      display: flex;
      align-items: center;
      min-width: 0;
      flex: 1;
      text-decoration: none;
    }
    .super-sidebar .brand img {
      height: 24px;
      max-width: 110px;
      width: auto;
      object-fit: contain;
      display: block;
    }
    .sa-shield-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 6px;
      font-size: 9px;
      font-weight: 800;
      letter-spacing: 0.3px;
      color: #ffffff;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      border-radius: 5px;
      box-shadow: 0 2px 6px rgba(239, 68, 68, 0.45);
      text-transform: uppercase;
      white-space: nowrap;
      flex-shrink: 0;
      line-height: 1.2;
    }
    .super-nav {
      list-style: none;
      padding: 14px 0 40px;
      margin: 0;
      flex: 1 1 auto;
      min-height: 0;
      overflow-y: auto;
      overflow-x: hidden;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.2) rgba(0, 0, 0, 0.2);
    }
    .super-nav::-webkit-scrollbar { width: 5px; }
    .super-nav::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
    .super-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.25); border-radius: 4px; }
    .super-nav::-webkit-scrollbar-thumb:hover { background: var(--sa-primary); }

    .super-nav li a {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 11px 20px;
      color: #94a3b8;
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 500;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      border-left: 3.5px solid transparent;
    }
    .super-nav li a:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.05);
    }
    .super-nav li.active a {
      color: #ffffff;
      font-weight: 600;
      background: linear-gradient(90deg, rgba(99, 102, 241, 0.22) 0%, rgba(99, 102, 241, 0.04) 100%);
      border-left: 3.5px solid #6366f1;
    }
    .super-nav li a i {
      width: 22px;
      font-size: 15px;
      margin-right: 11px;
      color: #64748b;
      text-align: center;
      transition: color 0.2s ease;
    }
    .super-nav li a:hover i,
    .super-nav li.active a i {
      color: #818cf8;
    }
    .super-nav .nav-heading {
      padding: 18px 20px 7px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #818cf8;
      font-weight: 800;
      display: flex;
      align-items: center;
    }
    .super-nav .nav-heading::after {
      content: '';
      flex: 1;
      height: 1px;
      margin-left: 10px;
      background: linear-gradient(90deg, rgba(129, 140, 248, 0.35) 0%, rgba(129, 140, 248, 0) 100%);
    }

    .super-content {
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
      scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .super-content::-webkit-scrollbar { width: 6px; height: 6px; }
    .super-content::-webkit-scrollbar-track { background: #f1f5f9; }
    .super-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .super-content::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .super-topbar {
      flex-shrink: 0;
      height: 60px;
      background: #ffffff;
      border-bottom: 1px solid var(--sa-border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .super-body {
      padding: 24px;
      flex: 1;
      min-height: min-content;
    }

    /* Cards & Stats */
    .sa-card {
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid var(--sa-border);
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
      margin-bottom: 20px;
      overflow: hidden;
    }
    .sa-card-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--sa-border);
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* Responsive Tables */
    .table-responsive {
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      scrollbar-color: #cbd5e1 transparent;
    }
    .table-responsive::-webkit-scrollbar { height: 6px; width: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .sa-table-min-sm { min-width: 540px; }
    .sa-table-min { min-width: 660px; }
    .sa-table-min-lg { min-width: 820px; }

    /* Scrollable pill navigation */
    .sa-nav-scroll {
      display: flex;
      flex-wrap: nowrap;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      white-space: nowrap;
      max-width: 100%;
      scrollbar-width: none;
    }
    .sa-nav-scroll::-webkit-scrollbar { display: none; }
    .sa-nav-scroll .nav-item { flex: 0 0 auto; }

    /* Responsive Breakpoints */
    @media (max-width: 991.98px) {
      html, body { overflow: auto; }
      .super-wrapper { height: auto; min-height: 100vh; overflow: visible; }
      .super-sidebar {
        position: fixed;
        top: 0;
        left: -290px;
        bottom: 0;
        width: 280px;
        max-width: 85vw;
        min-width: 0;
        z-index: 1050;
        transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 4px 0 28px rgba(0, 0, 0, 0.5);
      }
      .super-sidebar.show { left: 0; }
      .super-sidebar-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        z-index: 1040;
      }
      .super-sidebar-backdrop.show { display: block; }
      .super-content { height: auto; max-height: none; overflow-y: visible; }
      .super-topbar { padding: 0 16px; }
      .super-body { padding: 18px 16px; }
    }

    @media (max-width: 575.98px) {
      .super-topbar { padding: 0 12px; height: 56px; }
      .super-body { padding: 12px 10px; }
      .sa-card { margin-bottom: 14px; border-radius: 10px; }
      .sa-card-header { padding: 12px 14px; flex-wrap: wrap; gap: 8px; }
      .sa-card-header h5, .sa-card-header h6 { font-size: 14.5px; }

      /* Modals on Mobile */
      .modal-dialog {
        margin: 10px auto;
        max-width: calc(100% - 20px);
      }
      .modal-header {
        padding: 12px 14px;
      }
      .modal-body {
        padding: 16px 14px !important;
      }
      .modal-footer {
        padding: 12px 14px;
        flex-direction: column-reverse;
        gap: 8px;
      }
      .modal-footer .btn {
        width: 100%;
        margin: 0 !important;
      }
    }
  </style>
</head>
<body>
  <?php
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $user = \App\Core\Auth::user();

    // Quick counts for Super Admin badges
    $saCounts = [
        'enquiries'       => 0,
        'partner_pending' => 0,
        'staff'           => 0,
        'customers'       => 0,
        'gateways'        => 0,
    ];
    try {
        $cRow = \App\Core\Database::fetchOne(
            "SELECT 
                (SELECT COUNT(id) FROM bookings WHERE status = 'new') as enquiries,
                (SELECT COUNT(id) FROM users WHERE role = 'staff' AND status = 'pending') as partner_pending,
                (SELECT COUNT(id) FROM users WHERE role = 'staff' AND status IN ('active', 'disabled')) as staff,
                (SELECT COUNT(id) FROM users WHERE role = 'customer') as customers,
                (SELECT COUNT(id) FROM payment_gateways WHERE is_active = 1) as gateways"
        );
        if ($cRow) {
            $saCounts['enquiries']       = (int)($cRow['enquiries'] ?? 0);
            $saCounts['partner_pending'] = (int)($cRow['partner_pending'] ?? 0);
            $saCounts['staff']           = (int)($cRow['staff'] ?? 0);
            $saCounts['customers']       = (int)($cRow['customers'] ?? 0);
            $saCounts['gateways']        = (int)($cRow['gateways'] ?? 0);
        }
    } catch (\Throwable $e) {}
  ?>
  <div class="super-wrapper">
    <div class="super-sidebar-backdrop" id="superSidebarBackdrop"></div>
    <div class="super-sidebar" id="superSidebar">
      <div class="brand">
        <a href="<?= \App\Core\View::url('/superadmin') ?>" class="brand-logo" title="REFIXEL Super Admin">
          <img src="<?= \App\Core\View::asset('img/refixel-logo-white-orange.png') ?>?v=3" 
               alt="REFIXEL" 
               onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/refixel-logo.png') ?>';">
        </a>
        <div class="d-flex align-items-center flex-shrink-0">
          <span class="sa-shield-badge"><i class="fa fa-shield"></i> SUPER ADMIN</span>
          <button type="button" class="btn btn-sm text-white-50 p-1 ml-1 d-lg-none" id="superSidebarClose" aria-label="Close Sidebar" style="font-size: 16px; line-height: 1;">
            <i class="fa fa-times"></i>
          </button>
        </div>
      </div>

      <ul class="super-nav">
        <li class="<?= ($uri === '/superadmin' || str_ends_with($uri, '/superadmin') || str_contains($uri, '/superadmin/dashboard')) ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-tachometer"></i> Control Center</span>
          </a>
        </li>

        <li class="nav-heading">Platform Data & Purge</li>
        <li class="<?= str_contains($uri, '/superadmin/enquiries') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/enquiries') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-inbox"></i> Enquiries</span>
            <?php if ($saCounts['enquiries'] > 0): ?>
              <span class="badge badge-pill badge-warning" style="font-size: 11px;"><?= $saCounts['enquiries'] ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/superadmin/partner-applications') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/partner-applications') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-id-card-o"></i> Partner Applications</span>
            <?php if (!empty($saCounts['partner_pending']) && $saCounts['partner_pending'] > 0): ?>
              <span class="badge badge-pill badge-warning" style="font-size: 11px;"><?= $saCounts['partner_pending'] ?> Pending</span>
            <?php endif; ?>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/superadmin/staff') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/staff') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-wrench"></i> Technicians</span>
            <span class="badge badge-pill badge-info" style="font-size: 11px;"><?= $saCounts['staff'] ?></span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/superadmin/customers') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/customers') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-users"></i> Customers</span>
            <span class="badge badge-pill badge-secondary" style="font-size: 11px;"><?= $saCounts['customers'] ?></span>
          </a>
        </li>

        <li class="nav-heading">Financial Core</li>
        <li class="<?= str_contains($uri, '/superadmin/gateways') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/gateways') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-credit-card"></i> Payment Gateways</span>
            <span class="badge badge-pill badge-success" style="font-size: 11px;"><?= $saCounts['gateways'] ?> Active</span>
          </a>
        </li>
        <li class="<?= str_contains($uri, '/superadmin/commissions') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/commissions') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-percent"></i> Commissions & Splits</span>
          </a>
        </li>

        <li class="nav-heading">Governance</li>
        <li class="<?= str_contains($uri, '/superadmin/admins') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/superadmin/admins') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-user-secret"></i> Admin Accounts</span>
          </a>
        </li>

        <li class="nav-heading">Portals & Exit</li>
        <li>
          <a href="<?= \App\Core\View::url('/admin') ?>">
            <span class="d-flex align-items-center"><i class="fa fa-cubes text-warning"></i> Operations Admin</span>
            <i class="fa fa-external-link" style="font-size: 12px; margin: 0;"></i>
          </a>
        </li>
        <li>
          <a href="<?= \App\Core\View::url('/') ?>" target="_blank">
            <span class="d-flex align-items-center"><i class="fa fa-globe"></i> View Website</span>
            <i class="fa fa-external-link" style="font-size: 12px; margin: 0;"></i>
          </a>
        </li>
        <li>
          <a href="<?= \App\Core\View::url('/logout') ?>" class="text-danger">
            <span class="d-flex align-items-center"><i class="fa fa-sign-out text-danger"></i> Log Out</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="super-content">
      <div class="super-topbar">
        <div class="d-flex align-items-center">
          <button type="button" class="btn btn-sm btn-light border d-lg-none mr-2 mr-sm-3" id="superSidebarToggle" aria-label="Toggle Sidebar">
            <i class="fa fa-bars"></i>
          </button>
          <div class="d-flex align-items-center">
            <span class="badge badge-danger mr-2 px-2 py-1" style="font-size: 11px; letter-spacing: 0.5px;">
              <span class="d-none d-sm-inline"><i class="fa fa-shield mr-1"></i>ROOT PRIVILEGES</span>
              <span class="d-sm-none"><i class="fa fa-shield"></i> ROOT</span>
            </span>
            <span class="text-muted small d-none d-md-inline">Global Architecture & Database Controls</span>
          </div>
        </div>

        <div class="d-flex align-items-center">
          <a href="<?= \App\Core\View::url('/admin') ?>" class="btn btn-sm btn-outline-primary mr-2 mr-sm-3 font-weight-bold" title="Switch to Operations Admin">
            <i class="fa fa-dashboard mr-sm-1"></i>
            <span class="d-none d-sm-inline">Operations Admin</span>
          </a>

          <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center" type="button" id="superUserMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-user-circle text-danger mr-1 mr-sm-2" style="font-size: 16px;"></i>
              <span class="font-weight-bold text-dark d-none d-sm-inline"><?= \App\Core\View::e($user['name'] ?? 'Super Admin') ?></span>
              <span class="font-weight-bold text-dark d-sm-none"><?= \App\Core\View::e(mb_substr($user['name'] ?? 'SA', 0, 8)) ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="superUserMenu" style="border-radius: 8px;">
              <div class="px-3 py-2 border-bottom">
                <div class="font-weight-bold text-dark"><?= \App\Core\View::e($user['name'] ?? 'Super Admin') ?></div>
                <div class="text-muted small text-truncate" style="max-width: 200px;"><?= \App\Core\View::e($user['email'] ?? 'superadmin@refixel.com') ?></div>
                <span class="badge badge-danger mt-1">Super Administrator</span>
              </div>
              <a class="dropdown-item py-2" href="<?= \App\Core\View::url('/admin') ?>">
                <i class="fa fa-dashboard mr-2 text-primary"></i> Operations Admin
              </a>
              <a class="dropdown-item py-2" href="<?= \App\Core\View::url('/superadmin/admins') ?>">
                <i class="fa fa-user-secret mr-2 text-warning"></i> Admin Accounts
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item py-2 text-danger" href="<?= \App\Core\View::url('/logout') ?>">
                <i class="fa fa-sign-out mr-2"></i> Log Out
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="super-body">
        <?= \App\Core\View::partial('flash') ?>
        <?= $content ?? '' ?>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var sidebar = document.getElementById('superSidebar');
      var backdrop = document.getElementById('superSidebarBackdrop');
      var toggleBtn = document.getElementById('superSidebarToggle');
      var closeBtn = document.getElementById('superSidebarClose');

      function openSidebar() {
        if (sidebar && backdrop) {
          sidebar.classList.add('show');
          backdrop.classList.add('show');
          document.body.style.overflow = 'hidden';
        }
      }

      function closeSidebar() {
        if (sidebar && backdrop) {
          sidebar.classList.remove('show');
          backdrop.classList.remove('show');
          document.body.style.overflow = '';
        }
      }

      if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          if (sidebar && sidebar.classList.contains('show')) {
            closeSidebar();
          } else {
            openSidebar();
          }
        });
      }

      if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          closeSidebar();
        });
      }

      if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
      }

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeSidebar();
        }
      });

      // Auto close sidebar on clicking links on mobile
      var navLinks = document.querySelectorAll('#superSidebar .super-nav a');
      navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
          if (window.innerWidth < 992) {
            closeSidebar();
          }
        });
      });
    });
  </script>
</body>
</html>
