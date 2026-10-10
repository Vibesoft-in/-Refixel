<?php
// Wrap the content inside the customer layout, but add the account sidebar grid.
ob_start();
?>
<style>
  /* Premium Dashboard Styles */
  body {
    background-color: #f8fafc;
  }
  .dash-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
  }
  .dash-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
  }
  .soft-btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s;
    letter-spacing: 0.3px;
  }
  .soft-btn-primary {
    background-color: #f25b29;
    color: #ffffff;
    border: none;
  }
  .soft-btn-primary:hover {
    background-color: #db4918;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);
  }
  .soft-btn-outline {
    background-color: transparent;
    color: #f25b29;
    border: 1px solid #ffdacf;
  }
  .soft-btn-outline:hover {
    background-color: #fff3ec;
    border-color: #f25b29;
  }
  .dash-heading {
    font-weight: 600;
    color: #1e293b;
    letter-spacing: -0.3px;
    font-size: clamp(1.1rem, 2.5vw, 1.4rem); /* responsive sizing */
  }
  .dash-table th {
    border-top: none;
    border-bottom: 1px solid #e2e8f0;
    color: #64748b;
    font-weight: 500;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .dash-table td {
    vertical-align: middle;
    color: #334155;
    border-bottom: 1px dashed #e2e8f0; /* Soft border */
  }
  .dash-table tbody tr:nth-of-type(odd) {
    background-color: #f8fafc; /* Alternating row color */
  }
  .dash-table tbody tr:hover {
    background-color: #f1f5f9;
  }
  .glass-banner {
    background: rgba(255, 243, 236, 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(242, 91, 41, 0.15) !important;
  }
  .glass-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    z-index: 1;
    opacity: 0.4;
  }
  .glass-blob.top-right {
    top: -30px; right: 10%; width: 120px; height: 120px; background: #f25b29;
  }
  .glass-blob.bottom-right {
    bottom: -30px; right: -20px; width: 100px; height: 100px; background: #fb923c;
  }
  .badge-soft {
    border-radius: 6px;
    font-weight: 500;
    padding: 6px 12px;
    font-size: 12px;
  }
  .badge-soft-success { background: #fff3ec; color: #f25b29; border: 1px solid #ffdacf; }
  .badge-soft-warning { background: #fef3c7; color: #92400e; }
  .badge-soft-info { background: #e0f2fe; color: #075985; }
  .badge-soft-danger { background: #fee2e2; color: #991b1b; }
  .font-weight-500 { font-weight: 500 !important; }
</style>
<div class="container py-4 my-2" style="max-width: 1200px;">


  <div class="row">
    <!-- Fixed Account Sidebar Navigation -->
    <div class="col-lg-3 mb-4 d-none d-lg-block">
      <?= \App\Core\View::partial('account-sidebar') ?>
    </div>

    <!-- Main Account Content -->
    <div class="col-lg-9" id="account-main-content" style="min-height: 600px;">
      <?= $content ?? '' ?>
    </div>
  </div>
</div>


<?php
$wrappedContent = ob_get_clean();

// Now render the main customer layout with this wrapped content
echo \App\Core\View::renderTemplate('layouts/customer', array_merge($data ?? [], ['content' => $wrappedContent]));
?>
