<div class="dash-card p-4 p-md-5">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-3" style="border-bottom: 1px solid #edf2f7;">
    <div>
      <h3 class="dash-heading mb-1">GST Tax Invoices & Receipts</h3>
      <p class="text-muted small mb-0" style="font-weight: 400;">Download official tax invoices for your completed service visits.</p>
    </div>
  </div>

  <?php if (!empty($invoices)): ?>
    <div class="table-responsive" style="overflow-x: auto;">
      <table class="table dash-table w-100 mb-0">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Booking #</th>
            <th>Date</th>
            <th>Total Amount</th>
            <th>GST Tax (18%)</th>
            <th>Status</th>
            <th>Download</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $inv): ?>
            <tr>
              <td class="font-weight-500 text-dark"><?= \App\Core\View::e($inv['invoice_no']) ?></td>
              <td>
                <a href="<?= \App\Core\View::url('/account/bookings/' . (int)$inv['booking_id']) ?>" class="font-weight-500 text-decoration-none" style="color:#f25b29;">
                  #<?= \App\Core\View::e($inv['booking_no'] ?? 'N/A') ?>
                </a>
              </td>
              <td class="text-muted"><?= \App\Core\View::e(substr((string)$inv['issued_at'], 0, 10)) ?></td>
              <td class="font-weight-500" style="color: #0a1c33;">₹<?= number_format((float)$inv['total'], 2) ?></td>
              <td class="text-muted">₹<?= number_format((float)$inv['gst_amount'], 2) ?></td>
              <td>
                <span class="badge-soft badge-soft-success">PAID</span>
              </td>
              <td>
                <a href="<?= \App\Core\View::url('/account/invoices/' . (int)$inv['id']) ?>" class="btn soft-btn soft-btn-outline btn-sm">
                  <i class="fa fa-file-pdf-o mr-1"></i> Receipt
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
        <i class="fa fa-file-text-o" style="font-size: 24px;"></i>
      </div>
      <h6 class="dash-heading text-dark mb-1">No invoices generated yet</h6>
      <p class="small text-muted mb-4">Invoices and formal GST receipts are automatically created when your technician completes the service.</p>
      <a href="<?= \App\Core\View::url('/services') ?>" class="btn soft-btn soft-btn-primary px-4 py-2">
        Explore Services
      </a>
    </div>
  <?php endif; ?>
</div>
