<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \App\Core\View::e($title ?? 'Admin Panel | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>" rel="shortcut icon" type="image/x-icon" />
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary: #f25b29;
      --primary-dark: #d44d20;
      --primary-light: #fff3ef;
      --dark-sidebar: #1a1210;
      --dark-sidebar-hover: #2d1f18;
      --body-bg: #f6f4f3;
      --border-color: #f0e8e4;
    }
    body { font-family: 'Inter Tight', sans-serif; background: var(--body-bg); min-height: 100vh; color: #1e2925; margin: 0; }
    .admin-wrapper { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 255px; background: var(--dark-sidebar); color: #fff; flex-shrink: 0; display: flex; flex-direction: column; }
    .admin-sidebar .brand { padding: 18px 20px; font-weight: 700; font-size: 17px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: space-between; }
    .admin-sidebar .brand img { height: 26px; }
    .admin-nav { list-style: none; padding: 12px 0; margin: 0; flex: 1; overflow-y: auto; }
    .admin-nav li a { display: flex; align-items: center; padding: 11px 20px; color: #c0a898; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.15s ease; }
    .admin-nav li a:hover, .admin-nav li.active a { color: #fff; background: var(--dark-sidebar-hover); border-left: 3px solid #f25b29; }
    .admin-nav li a i { width: 22px; font-size: 15px; margin-right: 10px; color: #9a7a68; }
    .admin-nav li.active a i { color: #f25b29; }
    .admin-nav .nav-heading { padding: 12px 20px 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #587970; font-weight: 700; }
    .admin-content { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; min-width: 0; }
    .admin-topbar { height: 60px; background: #fff; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    .admin-body { padding: 24px; flex: 1; }
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
  ?>
  <div class="admin-wrapper">
    <div class="admin-sidebar">
      <div class="brand">
        <a href="<?= \App\Core\View::url('/admin') ?>" class="text-white text-decoration-none d-flex align-items-center">
          <img src="<?= \App\Core\View::asset('img/favicon.png') ?>" alt="REFIXEL" style="height: 24px; margin-right: 8px;">
          <span style="font-weight: 700; font-size: 16px; color: #fff;">REFIXEL</span>
        </a>
        <span class="badge badge-success px-2" style="font-size: 10px;">HQ</span>
      </div>

      <ul class="admin-nav">
        <li class="<?= ($uri === '/website/public/admin' || $uri === '/website/admin' || str_ends_with($uri, '/admin') || str_contains($uri, '/admin/dashboard')) ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin') ?>"><i class="fa fa-dashboard"></i> Dashboard</a>
        </li>

        <li class="nav-heading">Operations</li>
        <li class="<?= str_contains($uri, '/admin/enquiries') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/enquiries') ?>"><i class="fa fa-inbox"></i> Enquiries</a>
        </li>
        <li class="<?= (str_contains($uri, '/admin/bookings') && !str_contains($uri, '/admin/bookings/calendar')) ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/bookings') ?>"><i class="fa fa-calendar-check-o"></i> Bookings & Jobs</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/calendar') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/calendar') ?>"><i class="fa fa-calendar"></i> Dispatch Calendar</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/staff') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/staff') ?>"><i class="fa fa-users"></i> Staff & Techs</a>
        </li>

        <li class="nav-heading">Catalog & Finance</li>
        <li class="<?= str_contains($uri, '/admin/services') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/services') ?>"><i class="fa fa-wrench"></i> Services & Catalog</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/payments') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/payments') ?>"><i class="fa fa-money"></i> Payments & Invoices</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/reports') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/reports') ?>"><i class="fa fa-bar-chart"></i> Analytics & Reports</a>
        </li>

        <li class="nav-heading">CMS & Settings</li>
        <li class="<?= str_contains($uri, '/admin/content/faqs') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/faqs') ?>"><i class="fa fa-question-circle"></i> FAQs</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/gallery') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/gallery') ?>"><i class="fa fa-picture-o"></i> Gallery Showcase</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/reviews') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/reviews') ?>"><i class="fa fa-star"></i> Reviews Approval</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/areas') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/areas') ?>"><i class="fa fa-map-marker"></i> Service Areas</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/steps') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/steps') ?>"><i class="fa fa-list-ol"></i> Process Steps</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/content/checklists') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/content/checklists') ?>"><i class="fa fa-check-square-o"></i> Service Checklists</a>
        </li>
        <li class="<?= str_contains($uri, '/admin/settings') ? 'active' : '' ?>">
          <a href="<?= \App\Core\View::url('/admin/settings') ?>"><i class="fa fa-cog"></i> Business Settings</a>
        </li>

        <li class="mt-3 border-top border-secondary pt-2">
          <a href="<?= \App\Core\View::url('/logout') ?>"><i class="fa fa-sign-out text-danger"></i> Logout</a>
        </li>
      </ul>
    </div>

    <div class="admin-content">
      <div class="admin-topbar">
        <div class="d-flex align-items-center">
          <strong class="text-dark" style="font-size: 15px;"><i class="fa fa-shield text-success mr-2"></i>REFIXEL Operations Hub</strong>
        </div>
        <div class="d-flex align-items-center">
          <span class="mr-3 text-muted small">
            <i class="fa fa-user-circle text-primary mr-1"></i><?= \App\Core\View::e($user['name'] ?? 'Super Admin') ?>
          </span>
          <a href="<?= \App\Core\View::url('/') ?>" target="_blank" class="btn btn-sm btn-outline-secondary font-weight-bold">
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
</body>
</html>

