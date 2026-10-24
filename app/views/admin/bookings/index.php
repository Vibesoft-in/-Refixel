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

    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0">
      <!-- Search form -->
      <form method="GET" action="<?= View::url('/admin/bookings') ?>" class="form-inline">
        <div class="input-group input-group-sm">
          <input type="text" name="q" class="form-control" placeholder="Search BK#, customer, phone..." value="<?= View::e($search) ?>">
          <div class="input-group-append">
            <button class="btn btn-brand" type="submit"><i class="fa fa-search"></i></button>
          </div>
        </div>
      </form>
      <?php if (\App\Core\Auth::isAdmin()): ?>
        <button type="button" class="btn btn-sm btn-danger font-weight-bold ml-md-2 mt-2 mt-md-0" data-toggle="modal" data-target="#modalBulkDeleteBookings">
          <i class="fa fa-trash mr-1"></i>Delete All Bookings & Jobs (<?= (int)($counts['all'] ?? 0) ?>)
        </button>
      <?php endif; ?>
    </div>
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
                      <?php if ($bAdv > 0): ?>
                        <span class="text-success font-weight-bold">Adv: ₹<?= number_format($bAdv, 0) ?></span> &bull; 
                      <?php endif; ?>
                      <span class="text-danger font-weight-bold">Due: ₹<?= number_format($bDue, 0) ?></span>
                      <?php if ($bAdv <= 0): ?>
                        <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;">COD</span>
                      <?php endif; ?>
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
                    <?php elseif ($bAdv > 0): ?>
                      <span class="badge badge-pay-pre" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="Pre-booking Advance ₹<?= round($bAdv) ?> Paid">
                        Pre (₹<?= round($bAdv) ?>)
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="Pure Cash on Delivery (No Advance)">
                        COD
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="text-right text-nowrap">
                  <a href="<?= View::url('/admin/bookings/' . $b['id']) ?>" class="btn btn-sm btn-brand px-2 px-sm-3 font-weight-bold">
                    Manage &rarr;
                  </a>
                  <?php if (\App\Core\Auth::isSuperAdmin()): ?>
                    <form action="<?= View::url('/superadmin/bookings/' . $b['id'] . '/delete') ?>" method="POST" class="d-inline ml-1" onsubmit="return confirm('Super Admin: Permanently delete booking #<?= View::e($b['booking_no']) ?>?');">
                      <?= View::csrfField() ?>
                      <input type="hidden" name="redirect" value="/admin/bookings">
                      <button type="submit" class="btn btn-sm btn-outline-danger px-2 font-weight-bold" title="Delete Booking (Super Admin)">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (\App\Core\Auth::isAdmin()): ?>
<!-- Modal: Delete All Bookings & Jobs -->
<div class="modal fade" id="modalBulkDeleteBookings" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-exclamation-triangle mr-2"></i>Delete All Bookings & Jobs?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= View::url('/admin/bookings/delete-all') ?>" method="POST">
        <?= View::csrfField() ?>
        <input type="hidden" name="redirect" value="/admin/bookings">
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-trash fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= (int)($counts['all'] ?? 0) ?> Bookings & Jobs?</h5>
          <p class="text-muted small mb-0">
            This will permanently wipe all customer service bookings, field jobs, job attachments, invoices, and payment records across the marketplace. This action cannot be reversed.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Bookings & Jobs
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
