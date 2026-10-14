<?php
use App\Core\View;
$bookings = $bookings ?? [];
$currentStatus = $currentStatus ?? 'all';
$counts = $counts ?? ['all' => 0, 'new' => 0, 'assigned' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
$search = $search ?? '';
?>

<div class="admin-bookings-page">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Bookings & Dispatch Operations</h4>
      <p class="text-muted small mb-0">Manage customer bookings, technician job dispatches, and work order lifecycles.</p>
    </div>

    <!-- Search form -->
    <form method="GET" action="<?= View::url('/admin/bookings') ?>" class="form-inline mt-2 mt-md-0">
      <div class="input-group input-group-sm">
        <input type="text" name="q" class="form-control" placeholder="Search BK#, customer, phone..." value="<?= View::e($search) ?>">
        <div class="input-group-append">
          <button class="btn btn-brand" type="submit"><i class="fa fa-search"></i></button>
        </div>
      </div>
    </form>
  </div>

  <!-- Status Filter Tabs -->
  <div class="d-flex overflow-auto pb-2 mb-3" style="gap: 6px;">
    <a href="<?= View::url('/admin/bookings?status=all') ?>" class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentStatus === 'all' ? 'btn-brand' : 'btn-light border text-muted' ?>">
      All Bookings (<?= (int)$counts['all'] ?>)
    </a>
    <a href="<?= View::url('/admin/bookings?status=new') ?>" class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentStatus === 'new' ? 'btn-warning text-dark' : 'btn-light border text-muted' ?>">
      New (<?= (int)$counts['new'] ?>)
    </a>
    <a href="<?= View::url('/admin/bookings?status=assigned') ?>" class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentStatus === 'assigned' ? 'btn-info text-white' : 'btn-light border text-muted' ?>">
      Assigned (<?= (int)$counts['assigned'] ?>)
    </a>
    <a href="<?= View::url('/admin/bookings?status=in_progress') ?>" class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentStatus === 'in_progress' ? 'btn-primary text-white' : 'btn-light border text-muted' ?>">
      In Progress (<?= (int)$counts['in_progress'] ?>)
    </a>
    <a href="<?= View::url('/admin/bookings?status=completed') ?>" class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentStatus === 'completed' ? 'btn-success text-white' : 'btn-light border text-muted' ?>">
      Completed (<?= (int)$counts['completed'] ?>)
    </a>
    <a href="<?= View::url('/admin/bookings?status=cancelled') ?>" class="btn btn-sm rounded-pill font-weight-bold px-3 <?= $currentStatus === 'cancelled' ? 'btn-danger text-white' : 'btn-light border text-muted' ?>">
      Cancelled (<?= (int)$counts['cancelled'] ?>)
    </a>
  </div>

  <!-- Bookings Table -->
  <div class="stat-card p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Booking #</th>
            <th>Customer & Contact</th>
            <th>Service Requested</th>
            <th>Scheduled Slot</th>
            <th>Assigned Technician</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="fa fa-folder-open-o fa-2x mb-2 d-block text-muted" style="opacity: 0.3;"></i>
                No bookings found matching current filters.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($bookings as $b): ?>
              <tr>
                <td>
                  <a href="<?= View::url('/admin/bookings/' . $b['id']) ?>" class="font-weight-bold text-dark">
                    #<?= View::e($b['booking_no']) ?>
                  </a>
                  <div class="mt-1">
                    <?php 
                      $bDue = (float)($b['due_amount'] ?? 0);
                      $isBHigh = (($b['priority'] ?? '') === 'high' || ($b['payment_type'] ?? '') === 'online');
                    ?>
                    <?php if ($isBHigh && $bDue <= 0): ?>
                      <span class="badge badge-success font-weight-bold" style="font-size: 10px; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                        <i class="fa fa-bolt mr-1"></i>High Priority
                      </span>
                    <?php else: ?>
                      <span class="badge font-weight-bold" style="font-size: 10px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                        <i class="fa fa-clock-o mr-1"></i>Medium Priority (COD)
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="text-muted" style="font-size: 11px;"><?= View::e(substr((string)$b['created_at'], 0, 10)) ?></div>
                </td>
                <td>
                  <div class="font-weight-bold text-dark"><?= View::e($b['name']) ?></div>
                  <div class="small">
                    <a href="tel:<?= View::e($b['phone']) ?>" class="text-success"><i class="fa fa-phone mr-1"></i><?= View::e($b['phone']) ?></a>
                  </div>
                  <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?= View::e($b['address']) ?>">
                    <i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($b['address']) ?>
                  </div>
                </td>
                <td>
                  <div class="font-weight-bold text-dark small"><?= View::e($b['service_name']) ?></div>
                  <?php 
                    $bTotal = (float)($b['total_amount'] ?? $b['starting_price'] ?? 0);
                    $bAdv = (float)($b['advance_amount'] ?? 0);
                  ?>
                  <?php if ($bDue > 0): ?>
                    <div class="text-dark small font-weight-bold">Total: ₹<?= number_format($bTotal > 0 ? $bTotal : 899, 0) ?></div>
                    <div class="small" style="font-size: 11px;">
                      <span class="text-success font-weight-bold">Adv: ₹<?= number_format($bAdv > 0 ? $bAdv : 49, 0) ?></span> &bull; 
                      <span class="text-danger font-weight-bold">Due: ₹<?= number_format($bDue, 0) ?></span>
                    </div>
                  <?php else: ?>
                    <div class="text-success small font-weight-bold">₹<?= number_format($bTotal > 0 ? $bTotal : (float)($b['starting_price'] ?? 0), 0) ?> (Full Online)</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="font-weight-bold text-dark small"><i class="fa fa-calendar mr-1 text-muted"></i><?= View::e($b['preferred_date'] ?? 'Flexible') ?></div>
                  <div class="text-muted small"><?= View::e($b['preferred_time'] ?? '') ?></div>
                </td>
                <td>
                  <?php if (!empty($b['staff_name'])): ?>
                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                      <i class="fa fa-user-circle mr-1 text-primary"></i><?= View::e($b['staff_name']) ?>
                    </span>
                    <div class="text-muted" style="font-size: 11px;"><?= ucfirst($b['job_status'] ?? '') ?></div>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark px-2 py-1">
                      <i class="fa fa-warning mr-1"></i>Unassigned
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-inline-flex align-items-center flex-wrap" style="gap: 5px;">
                    <span class="badge badge-status-<?= View::e($b['status']) ?>">
                      <?= ucfirst(str_replace('_', ' ', $b['status'])) ?>
                    </span>
                    <?php if ($isBHigh && $bDue <= 0): ?>
                      <span class="badge badge-pay-paid" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="100% Full Payment Paid Online">
                        <i class="fa fa-check mr-1"></i>Paid
                      </span>
                    <?php else: ?>
                      <span class="badge badge-pay-pre" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="Pre-booking Advance ₹49 Paid (COD Remaining)">
                        Pre
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="text-right">
                  <a href="<?= View::url('/admin/bookings/' . $b['id']) ?>" class="btn btn-sm btn-brand px-3 font-weight-bold">
                    Manage &rarr;
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
