<div class="dash-card p-2 p-md-4">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3 px-2 px-md-0" style="border-bottom: 1px solid #edf2f7;">
    <div>
      <h3 class="dash-heading mb-1">Service Bookings History</h3>
      <p class="text-muted small mb-0" style="font-weight: 400;">Track real-time field progress, view assigned technician details.</p>
    </div>
    <a href="<?= \App\Core\View::url('/services') ?>" class="btn soft-btn soft-btn-primary px-3 py-2 d-none d-md-block text-nowrap">
      Book New
    </a>
  </div>

  <?php if (!empty($bookings)): ?>
    
    <!-- Mobile Card View -->
    <div class="d-block d-md-none px-2">
      <?php foreach ($bookings as $b): ?>
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
            <th>Booking #</th>
            <th>Service</th>
            <th>Scheduled Slot</th>
            <th>Address / Area</th>
            <th>Status</th>
            <th class="text-right">Details</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td class="font-weight-500 text-dark">#<?= \App\Core\View::e($b['booking_no']) ?></td>
              <td>
                <span class="font-weight-500" style="color: #0a1c33;"><?= \App\Core\View::e($b['service_name'] ?? 'Home Service') ?></span>
              </td>
              <td>
                <div class="font-weight-500 text-dark"><?= \App\Core\View::e($b['preferred_date']) ?></div>
                <small class="text-muted"><?= \App\Core\View::e($b['preferred_time']) ?></small>
              </td>
              <td>
                <span class="text-truncate d-inline-block text-muted" style="max-width: 200px; font-size: 13px;">
                  <?= \App\Core\View::e($b['address']) ?>
                </span>
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
                  <?= str_replace('_', ' ', \App\Core\View::e($b['status'])) ?>
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
      <div class="mb-3" style="width: 64px; height: 64px; background: #f8fafc; color: #94a3b8; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
        <i class="fa fa-calendar-times-o" style="font-size: 24px;"></i>
      </div>
      <h6 class="dash-heading text-dark mb-1">No bookings recorded yet</h6>
      <p class="small text-muted mb-4">Schedule your first deep cleaning or repair service with verified professionals today.</p>
      <a href="<?= \App\Core\View::url('/services') ?>" class="btn soft-btn soft-btn-primary px-4 py-2">
        Browse Catalogue
      </a>
    </div>
  <?php endif; ?>
</div>
