<?php
$currentPath = \App\Core\Request::class ? (new \App\Core\Request())->getPath() : '';
$user = \App\Core\Auth::user();
?>
<!-- Account Sidebar Navigation -->
<div class="card p-3 border-0 shadow-sm" style="border-radius: 14px; position: sticky; top: 90px;">
  <div class="d-flex align-items-center p-2 mb-3 border-bottom pb-3">
    <img src="<?= \App\Core\View::asset('img/account.webp') ?>" alt="User" class="rounded-circle mr-3" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #f25b29;">
    <div>
      <h6 class="font-weight-bold mb-0 text-dark"><?= \App\Core\View::e($user['name'] ?? 'Guest') ?></h6>
      <small class="text-muted"><?= \App\Core\View::e($user['phone'] ?? $user['email'] ?? '') ?></small>
    </div>
  </div>

  <div class="list-group list-group-flush account-sidebar-nav" style="font-size: 15px; font-weight: 500;">
    <a href="<?= \App\Core\View::url('/account') ?>" class="list-group-item list-group-item-action border-0 <?= $currentPath === '/account' ? 'text-white active-tab' : 'text-dark' ?>" <?= $currentPath === '/account' ? 'style="background: #f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);"' : '' ?>>
      <i class="fa fa-dashboard mr-3 <?= $currentPath === '/account' ? 'text-white' : '' ?>" style="<?= $currentPath === '/account' ? '' : 'color: #f25b29;' ?>"></i> Dashboard Overview
    </a>
    <a href="<?= \App\Core\View::url('/account/bookings') ?>" class="list-group-item list-group-item-action border-0 mt-1 <?= str_starts_with($currentPath, '/account/bookings') ? 'text-white active-tab' : 'text-dark' ?>" <?= str_starts_with($currentPath, '/account/bookings') ? 'style="background: #f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);"' : '' ?>>
      <i class="fa fa-calendar mr-3 <?= str_starts_with($currentPath, '/account/bookings') ? 'text-white' : '' ?>" style="<?= str_starts_with($currentPath, '/account/bookings') ? '' : 'color: #f25b29;' ?>"></i> My Bookings
    </a>
    <a href="<?= \App\Core\View::url('/account/invoices') ?>" class="list-group-item list-group-item-action border-0 mt-1 <?= str_starts_with($currentPath, '/account/invoices') ? 'text-white active-tab' : 'text-dark' ?>" <?= str_starts_with($currentPath, '/account/invoices') ? 'style="background: #f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);"' : '' ?>>
      <i class="fa fa-file-text-o mr-3 <?= str_starts_with($currentPath, '/account/invoices') ? 'text-white' : '' ?>" style="<?= str_starts_with($currentPath, '/account/invoices') ? '' : 'color: #f25b29;' ?>"></i> Invoices & Receipts
    </a>
    <a href="<?= \App\Core\View::url('/account/profile') ?>" class="list-group-item list-group-item-action border-0 mt-1 <?= $currentPath === '/account/profile' ? 'text-white active-tab' : 'text-dark' ?>" <?= $currentPath === '/account/profile' ? 'style="background: #f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);"' : '' ?>>
      <i class="fa fa-user-circle mr-3 <?= $currentPath === '/account/profile' ? 'text-white' : '' ?>" style="<?= $currentPath === '/account/profile' ? '' : 'color: #f25b29;' ?>"></i> Profile & Address
    </a>
    <a href="<?= \App\Core\View::url('/account/privacy') ?>" class="list-group-item list-group-item-action border-0 mt-1 <?= $currentPath === '/account/privacy' ? 'text-white active-tab' : 'text-dark' ?>" <?= $currentPath === '/account/privacy' ? 'style="background: #f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);"' : '' ?>>
      <i class="fa fa-shield mr-3 <?= $currentPath === '/account/privacy' ? 'text-white' : '' ?>" style="<?= $currentPath === '/account/privacy' ? '' : 'color: #f25b29;' ?>"></i> Privacy & Data Rights
    </a>
    <a href="<?= \App\Core\View::url('/logout') ?>" class="list-group-item list-group-item-action border-0 mt-4 text-danger font-weight-bold" style="border-radius: 8px; background: #fff5f5;">
      <i class="fa fa-sign-out mr-3 text-danger"></i> Sign Out
    </a>
  </div>
</div>

<script>
// AJAX Navigation Script
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.account-sidebar-nav a:not([href$="logout"])');
    const mainContentArea = document.getElementById('account-main-content');
    
    if (!mainContentArea) return;

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            // Highlight active tab visually
            sidebarLinks.forEach(l => {
                l.classList.remove('text-white', 'active-tab');
                l.classList.add('text-dark');
                l.style.background = 'transparent';
                l.style.boxShadow = 'none';
                const icon = l.querySelector('i');
                if(icon) { icon.classList.remove('text-white'); icon.style.color = '#f25b29'; }
            });
            this.classList.remove('text-dark');
            this.classList.add('text-white', 'active-tab');
            this.style.background = '#f25b29';
            this.style.boxShadow = '0 4px 12px rgba(242, 91, 41, 0.25)';
            this.style.borderRadius = '8px';
            const icon = this.querySelector('i');
            if(icon) { icon.classList.add('text-white'); icon.style.color = '#ffffff'; }

            // Show loading state
            mainContentArea.innerHTML = '<div class="text-center py-5"><i class="fa fa-spinner fa-spin text-success" style="font-size: 3rem;"></i><p class="mt-3 text-muted">Loading...</p></div>';
            
            // Push state
            window.history.pushState({path: url}, '', url);
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                mainContentArea.innerHTML = html;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(err => {
                mainContentArea.innerHTML = '<div class="alert alert-danger">Error loading content. Please refresh.</div>';
            });
        });
    });

    window.addEventListener('popstate', function() {
        window.location.reload();
    });
});
</script>
