<?php 
$welcomeData = $_SESSION['new_customer_welcome_modal'] ?? null;
if ($welcomeData) {
    unset($_SESSION['new_customer_welcome_modal']);
}
?>

<?php if ($welcomeData): ?>
<!-- First-Time Customer Celebratory Credentials Modal -->
<div class="modal fade show" id="refixelWelcomeModal" tabindex="-1" role="dialog" style="display: block; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(5px); z-index: 10500;" aria-modal="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 480px; margin: 1.5rem auto;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.15);">
      
      <!-- Top Celebratory Banner -->
      <div class="p-4 text-center text-white position-relative" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border-bottom: 3px solid #f25b29;">
        <button type="button" class="close text-white position-absolute" style="top: 14px; right: 18px; opacity: 0.85; font-size: 24px;" onclick="closeWelcomeModal()">&times;</button>
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(242, 91, 41, 0.15); border: 2px solid #f25b29; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);">
          🎉
        </div>
        <h4 class="font-weight-bold mb-1 text-white" style="letter-spacing: -0.3px; font-size: 22px;">Booking &amp; Account Confirmed!</h4>
        <p class="mb-0 text-light small" style="opacity: 0.9;">Welcome to REFIXEL, <?= \App\Core\View::e($welcomeData['name'] ?? 'Customer') ?>! Your customer portal is active.</p>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4 bg-white">
        
        <div class="alert alert-success border-0 small mb-3 d-flex align-items-center" style="background: #ecfdf5; color: #065f46; border-radius: 10px; padding: 10px 14px;">
          <i class="fa fa-check-circle mr-2" style="font-size: 18px;"></i>
          <div>Booking <strong>#<?= \App\Core\View::e($welcomeData['booking_no'] ?? '') ?></strong> is registered and dispatched for door-step service.</div>
        </div>

        <p class="text-muted small mb-3" style="font-size: 13px; line-height: 1.5;">
          We have generated your login credentials so you can track your technician in real time, view invoices, and book future services with 1 click:
        </p>

        <!-- Credentials Card -->
        <div class="p-3 mb-3" style="background: #0f172a; border-radius: 14px; color: #ffffff; box-shadow: 0 4px 15px rgba(15,23,42,0.15);">
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.12);">
            <span class="small text-uppercase font-weight-bold" style="color: #94a3b8; font-size: 10.5px; letter-spacing: 0.5px;">Login Email / Mobile</span>
            <div class="d-flex align-items-center" style="gap: 8px;">
              <span class="font-weight-bold font-monospace" id="wLoginId" style="color: #38bdf8; font-size: 13.5px;"><?= \App\Core\View::e($welcomeData['identifier'] ?? ($welcomeData['email'] ?? $welcomeData['phone'])) ?></span>
              <button type="button" class="btn btn-sm py-0.5 px-2 text-light" onclick="copyCred('wLoginId', this)" title="Copy ID" style="background: rgba(255,255,255,0.15); border-radius: 5px; font-size: 11px;">
                <i class="fa fa-copy"></i> Copy
              </button>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-1">
            <span class="small text-uppercase font-weight-bold" style="color: #94a3b8; font-size: 10.5px; letter-spacing: 0.5px;">Generated Password</span>
            <div class="d-flex align-items-center" style="gap: 6px;">
              <span class="font-weight-bold font-monospace px-2.5 py-1 rounded" id="wLoginPass" style="background: #f25b29; color: #ffffff; font-size: 13.5px; letter-spacing: 0.5px;"><?= \App\Core\View::e($welcomeData['generated_password'] ?? 'Refixel@2026') ?></span>
              <button type="button" class="btn btn-sm py-0.5 px-2 text-light" onclick="togglePassVisibility('wLoginPass', this)" title="Show/Hide Password" style="background: rgba(255,255,255,0.15); border-radius: 5px; font-size: 11px;">
                <i class="fa fa-eye"></i>
              </button>
              <button type="button" class="btn btn-sm py-0.5 px-2 text-light" onclick="copyCred('wLoginPass', this)" title="Copy Password" style="background: rgba(255,255,255,0.15); border-radius: 5px; font-size: 11px;">
                <i class="fa fa-copy"></i> Copy
              </button>
            </div>
          </div>
        </div>

        <?php if (!empty($welcomeData['email'])): ?>
        <div class="text-muted small text-center mb-3" style="font-size: 12px; line-height: 1.4;">
          <i class="fa fa-envelope-o text-primary mr-1"></i> We have also sent your login ID &amp; password to <strong><?= \App\Core\View::e($welcomeData['email']) ?></strong>.
        </div>
        <?php endif; ?>

        <button type="button" class="btn btn-block text-white font-weight-bold py-2.5 shadow-sm" style="background: #f25b29; border-radius: 10px; font-size: 14.5px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);" onclick="closeWelcomeModal()">
          Continue to My Bookings &rarr;
        </button>

      </div>

    </div>
  </div>
</div>
<script>
function closeWelcomeModal() {
  var m = document.getElementById('refixelWelcomeModal');
  if (m) {
    m.classList.remove('show');
    m.style.display = 'none';
  }
}
function copyCred(elemId, btn) {
  var el = document.getElementById(elemId);
  if (!el) return;
  var txt = (el.dataset.realPass || el.textContent).trim();
  navigator.clipboard.writeText(txt).then(function() {
    var orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-check text-success"></i> Copied!';
    setTimeout(function() { btn.innerHTML = orig; }, 1600);
  });
}
function togglePassVisibility(elemId, btn) {
  var el = document.getElementById(elemId);
  if (!el) return;
  if (el.dataset.hidden === '1') {
    el.textContent = el.dataset.realPass;
    el.dataset.hidden = '0';
    btn.innerHTML = '<i class="fa fa-eye"></i>';
  } else {
    el.dataset.realPass = el.textContent;
    el.textContent = '••••••••';
    el.dataset.hidden = '1';
    btn.innerHTML = '<i class="fa fa-eye-slash"></i>';
  }
}
</script>
<?php endif; ?>

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
