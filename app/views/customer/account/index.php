<!-- Welcome Hero Banner -->
<div class="p-3 p-md-4 mb-4 dash-card position-relative glass-banner" style="overflow: hidden; border: none;">
  <div class="glass-blob top-right"></div>
  <div class="glass-blob bottom-right"></div>
  
  <h4 class="dash-heading mb-1 position-relative text-dark" style="z-index: 2;">Welcome back, <?= \App\Core\View::e($user['name']) ?>! 👋</h4>
  <p class="mb-0 position-relative text-muted" style="font-size: 14px; font-weight: 400; z-index: 2;">Manage your home maintenance requests, view assigned technicians, and download GST receipts.</p>
</div>

<!-- Quick Stats Cards -->
<div class="row mb-4 px-2 px-md-0">
  <div class="col-6 col-md-4 mb-3 px-2">
    <div class="dash-card p-3 p-md-4 text-center h-100" style="background: #f0fdf4; border-color: #dcfce7;">
      <span class="text-muted small font-weight-500 text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 11px;">Total Bookings</span>
      <h2 class="dash-heading mb-0 text-dark mt-1" style="font-size: 1.5rem;"><?= count($bookings ?? []) ?></h2>
    </div>
  </div>
  <div class="col-6 col-md-4 mb-3 px-2">
    <div class="dash-card p-3 p-md-4 text-center h-100" style="background: #eff6ff; border-color: #dbeafe;">
      <span class="text-muted small font-weight-500 text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 11px;">Account Status</span>
      <h2 class="dash-heading mb-0 mt-1" style="color: #1d4ed8; font-size: 1.2rem;"><i class="fa fa-check-circle mr-1" style="opacity: 0.8;"></i> Active</h2>
    </div>
  </div>
  <div class="col-12 col-md-4 mb-3 px-2">
    <div class="dash-card p-3 p-md-4 text-center h-100" style="background: #fffbeb; border-color: #fef3c7;">
      <span class="text-muted small font-weight-500 text-uppercase d-block mb-1" style="letter-spacing: 0.5px; font-size: 11px;">Protection</span>
      <h2 class="dash-heading mb-0 mt-1" style="color: #b45309; font-size: 1.2rem;"><i class="fa fa-shield mr-1" style="opacity: 0.8;"></i> 24h</h2>
    </div>
  </div>
</div>

<!-- Recent Bookings Table -->
<div class="dash-card p-2 p-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 px-2 px-md-0" style="border-bottom: 1px solid #edf2f7;">
    <h5 class="dash-heading mb-0">Recent Service Bookings</h5>
    <a href="<?= \App\Core\View::url('/account/bookings') ?>" class="btn soft-btn soft-btn-outline btn-sm px-3 py-1 text-nowrap">View All &rarr;</a>
  </div>

  <?php if (!empty($bookings)): ?>
    
    <!-- Mobile Card View -->
    <div class="d-block d-md-none px-2">
      <?php $count = 0; foreach ($bookings as $b): if ($count++ >= 3) break; ?>
        <?php
        $badgeClass = match($b['status']) {
          'new'         => 'badge-soft-warning',
          'assigned'    => 'badge-soft-info',
          'accepted'    => 'badge-soft-info',
          'in_progress' => 'badge-soft-info',
          'completed'   => 'badge-soft-success',
          'invoiced'    => 'badge-soft-success',
          'reviewed'    => 'badge-soft-success',
          'cancelled'   => 'badge-soft-danger',
          default       => 'badge-soft-warning'
        };
        ?>
        <div class="dash-card p-3 mb-3" style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
          <!-- Header: Booking Number & Status -->
          <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid #f1f5f9;">
            <div>
              <span class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Booking ID</span>
              <h5 class="dash-heading mb-0 text-dark" style="font-size: 16px;">#<?= \App\Core\View::e($b['booking_no']) ?></h5>
            </div>
            <span class="badge-soft <?= $badgeClass ?>" style="font-size: 11px; padding: 5px 10px; border-radius: 6px;">
              <?= strtoupper(str_replace('_', ' ', \App\Core\View::e($b['status']))) ?>
            </span>
          </div>
          
          <!-- Content -->
          <div class="mb-3">
            <div class="font-weight-600 mb-2" style="color: #0a1c33; font-size: 14px;"><?= \App\Core\View::e($b['service_name'] ?? 'Home Service') ?></div>
            <div class="d-flex align-items-center text-muted" style="font-size: 12px;">
              <div class="mr-3"><i class="fa fa-calendar text-primary opacity-75 mr-1"></i> <?= \App\Core\View::e($b['preferred_date']) ?></div>
              <div><i class="fa fa-clock-o text-primary opacity-75 mr-1"></i> <?= \App\Core\View::e($b['preferred_time']) ?></div>
            </div>
          </div>

          <!-- Bottom Action -->
          <a href="<?= \App\Core\View::url('/account/bookings/' . (int)$b['id']) ?>" class="btn soft-btn soft-btn-outline w-100 py-2 d-flex justify-content-between align-items-center" style="font-size: 13px; border-radius: 8px;">
            <span>View Job Details</span>
            <i class="fa fa-arrow-right"></i>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Desktop Table View -->
    <div class="d-none d-md-block table-responsive" style="overflow-x: auto;">
      <table class="table dash-table w-100 mb-0">
        <thead>
          <tr>
            <th>Booking No</th>
            <th>Service</th>
            <th>Date & Time</th>
            <th>Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $count = 0; foreach ($bookings as $b): if ($count++ >= 3) break; ?>
            <tr>
              <td class="font-weight-500 text-dark">#<?= \App\Core\View::e($b['booking_no']) ?></td>
              <td class="font-weight-500"><span style="color: #0a1c33;"><?= \App\Core\View::e($b['service_name'] ?? 'Home Service') ?></span></td>
              <td>
                <div class="text-dark font-weight-500"><?= \App\Core\View::e($b['preferred_date']) ?></div>
                <small class="text-muted"><?= \App\Core\View::e($b['preferred_time']) ?></small>
              </td>
              <td>
                <?php
                $badgeClass = match($b['status']) {
                  'new'         => 'badge-soft-warning',
                  'assigned'    => 'badge-soft-info',
                  'accepted'    => 'badge-soft-info',
                  'in_progress' => 'badge-soft-info',
                  'completed'   => 'badge-soft-success',
                  'invoiced'    => 'badge-soft-success',
                  'reviewed'    => 'badge-soft-success',
                  'cancelled'   => 'badge-soft-danger',
                  default       => 'badge-soft-warning'
                };
                ?>
                <span class="badge-soft <?= $badgeClass ?>">
                  <?= strtoupper(str_replace('_', ' ', \App\Core\View::e($b['status']))) ?>
                </span>
              </td>
              <td class="text-right">
                <a href="<?= \App\Core\View::url('/account/bookings/' . (int)$b['id']) ?>" class="btn soft-btn soft-btn-outline btn-sm px-3">
                  View &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="text-center py-5 text-muted">
      <div class="mb-3" style="width: 64px; height: 64px; background: #f0fdf4; color: #16a34a; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
        <i class="fa fa-calendar-o" style="font-size: 24px;"></i>
      </div>
      <h6 class="dash-heading text-dark mb-1">No bookings yet</h6>
      <p class="mb-4 small" style="opacity: 0.8;">You haven't scheduled any service bookings yet.</p>
      <a href="<?= \App\Core\View::url('/services') ?>" class="btn soft-btn soft-btn-primary px-4 py-2">
        Explore Services
      </a>
    </div>
  <?php endif; ?>
</div>
