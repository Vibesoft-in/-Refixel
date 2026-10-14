<?php
use App\Core\View;
$enquiries = $enquiries ?? [];
$status = $status ?? 'new';
$counts = $counts ?? ['new' => 0, 'all' => 0];
?>

<div class="admin-enquiries-page">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Customer Enquiries</h4>
      <p class="text-muted small mb-0">Incoming customer service requests, issues, and booking enquiries awaiting dispatch.</p>
    </div>

    <div class="btn-group" role="group">
      <a href="<?= View::url('/admin/enquiries?status=new') ?>" class="btn btn-sm <?= $status === 'new' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold">
        New Only (<?= (int)$counts['new'] ?>)
      </a>
      <a href="<?= View::url('/admin/enquiries?status=all') ?>" class="btn btn-sm <?= $status === 'all' ? 'btn-brand' : 'btn-light border' ?> font-weight-bold">
        All Enquiries (<?= (int)$counts['all'] ?>)
      </a>
    </div>
  </div>

  <div class="stat-card p-0 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Ref #</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Service</th>
            <th>Preferred Schedule</th>
            <th>Priority</th>
            <th>Status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($enquiries)): ?>
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                <i class="fa fa-inbox fa-2x mb-2 d-block text-muted" style="opacity: 0.3;"></i>
                No enquiries found under this filter.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($enquiries as $e): ?>
              <tr>
                <td>
                  <a href="<?= View::url('/admin/enquiries/' . $e['id']) ?>" class="font-weight-bold text-dark">
                    #<?= View::e($e['booking_no']) ?>
                  </a>
                </td>
                <td>
                  <div class="font-weight-bold text-dark"><?= View::e($e['name']) ?></div>
                  <div class="text-muted small"><?= View::e($e['address']) ?></div>
                </td>
                <td>
                  <a href="tel:<?= View::e($e['phone']) ?>" class="text-success font-weight-bold">
                    <i class="fa fa-phone mr-1"></i><?= View::e($e['phone']) ?>
                  </a>
                </td>
                <td>
                  <span class="badge badge-light border text-dark font-weight-bold">
                    <?= View::e($e['service_name']) ?>
                  </span>
                </td>
                <td>
                  <div class="small font-weight-bold text-dark"><?= View::e($e['preferred_date'] ?? 'Flexible') ?></div>
                  <div class="text-muted small"><?= View::e($e['preferred_time'] ?? '') ?></div>
                </td>
                <td>
                  <?php
                    $prioClass = match ($e['priority'] ?? 'normal') {
                        'urgent' => 'badge-danger',
                        'high'   => 'badge-warning text-dark',
                        'low'    => 'badge-secondary',
                        default  => 'badge-info',
                    };
                  ?>
                  <span class="badge <?= $prioClass ?>"><?= strtoupper(View::e($e['priority'] ?? 'normal')) ?></span>
                </td>
                <td>
                  <div class="d-inline-flex align-items-center flex-wrap" style="gap: 5px;">
                    <span class="badge badge-status-<?= View::e($e['status']) ?>">
                      <?= ucfirst(str_replace('_', ' ', $e['status'])) ?>
                    </span>
                    <?php
                      $payType = strtolower(trim((string)($e['payment_type'] ?? '')));
                      $advAmt  = (float)($e['advance_amount'] ?? 0);
                      $dueAmt  = (float)($e['due_amount'] ?? 0);
                      $pPaidStatus = (string)($e['payment_paid_status'] ?? '');
                      $priority = strtolower(trim((string)($e['priority'] ?? '')));

                      $isFullPaid = false;
                      $isPrePaid  = false;

                      if ($pPaidStatus === 'paid') {
                          if ($payType === 'online' || $dueAmt <= 0) {
                              $isFullPaid = true;
                          } else {
                              $isPrePaid = true;
                          }
                      } else {
                          // Fallback matching customer selected mode
                          if ($payType === 'online' || $priority === 'high') {
                              $isFullPaid = true;
                          } elseif ($payType === 'cod' || $advAmt == 49.0 || $priority === 'normal') {
                              $isPrePaid = true;
                          }
                      }
                    ?>
                    <?php if ($isFullPaid): ?>
                      <span class="badge badge-pay-paid" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="100% Full Payment Paid Online">
                        <i class="fa fa-check mr-1"></i>Paid
                      </span>
                    <?php elseif ($isPrePaid): ?>
                      <span class="badge badge-pay-pre" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-weight: 700; font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="Pre-booking Advance ₹49 Paid (COD Remaining)">
                        Pre
                      </span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="text-right">
                  <a href="<?= View::url('/admin/enquiries/' . $e['id']) ?>" class="btn btn-sm btn-brand px-3 font-weight-bold">
                    View & Assign &rarr;
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
