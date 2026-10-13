<?php
use App\Core\View;
$invoice = $invoice ?? [];
?>

<div class="admin-invoice-view" style="max-width: 800px; margin: 0 auto;">
  <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
    <a href="<?= View::url('/admin/payments') ?>" class="text-muted small font-weight-bold">
      <i class="fa fa-arrow-left mr-1"></i>Back to Payments
    </a>
    <div>
      <button onclick="window.print()" class="btn btn-sm btn-brand font-weight-bold">
        <i class="fa fa-print mr-1"></i>Print Invoice
      </button>
    </div>
  </div>

  <div class="stat-card p-4 bg-white" style="border: 1px solid #d1d5db;">
    <!-- Invoice Header -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
      <div class="col-sm-6">
        <h3 class="font-weight-bold text-dark mb-1" style="color: #f25b29 !important;">REFIXEL</h3>
        <p class="text-muted small mb-0">Professional Home Care & Maintenance Services</p>
        <p class="text-muted small mb-0">GSTIN: <strong>07AAAAA0000A1Z5</strong> &bull; HSN/SAC: 9987</p>
        <p class="text-muted small mb-0">Support: wearerefixel@gmail.com &bull; +91 94581 82006</p>
      </div>
      <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
        <?php 
          $advPaid = (float)($invoice['advance_paid'] ?? $invoice['booking_advance_amount'] ?? 0);
          $dueAmt = (float)($invoice['due_amount'] ?? $invoice['booking_due_amount'] ?? 0);
          $isCodAdvance = ($dueAmt > 0 || ($invoice['booking_priority'] ?? '') === 'normal' || ($invoice['booking_payment_type'] ?? '') === 'cod');
        ?>
        <div class="mb-2">
          <span class="badge badge-success px-3 py-1 font-weight-bold mr-1">TAX INVOICE</span>
          <?php if ($isCodAdvance && $dueAmt > 0): ?>
            <span class="badge px-2 py-1 font-weight-bold" style="background:#fef3c7; color:#b45309; border: 1px solid #fde68a;">
              <i class="fa fa-clock-o mr-1"></i>MEDIUM PRIORITY (COD)
            </span>
          <?php else: ?>
            <span class="badge badge-success px-2 py-1 font-weight-bold">
              <i class="fa fa-bolt mr-1"></i>HIGH PRIORITY (ONLINE)
            </span>
          <?php endif; ?>
        </div>
        <h5 class="font-weight-bold text-dark mb-1">#<?= View::e($invoice['invoice_no']) ?></h5>
        <div class="text-muted small">Date: <?= View::e(substr((string)$invoice['issued_at'], 0, 10)) ?></div>
        <div class="text-muted small">Payment: <?= strtoupper(View::e($invoice['payment_method'] ?? 'UPI')) ?></div>
        <?php if (!empty($invoice['transaction_ref'])): ?>
          <div class="text-muted small">Ref: <?= View::e($invoice['transaction_ref']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Billed To & Service Details -->
    <div class="row mb-4">
      <div class="col-sm-6">
        <span class="text-muted small text-uppercase font-weight-bold">Billed To (Customer):</span>
        <h6 class="font-weight-bold text-dark mt-1 mb-1"><?= View::e($invoice['customer_name']) ?></h6>
        <div class="text-muted small"><i class="fa fa-phone mr-1"></i><?= View::e($invoice['customer_phone']) ?></div>
        <div class="text-muted small"><i class="fa fa-map-marker mr-1"></i><?= View::e($invoice['customer_address']) ?><?= !empty($invoice['pincode']) ? ' - ' . View::e($invoice['pincode']) : '' ?></div>
      </div>
      <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
        <span class="text-muted small text-uppercase font-weight-bold">Booking Reference:</span>
        <div class="font-weight-bold text-dark mt-1">#<?= View::e($invoice['booking_no']) ?></div>
        <div class="text-muted small">Service: <?= View::e($invoice['service_name']) ?></div>
        <div class="text-muted small">Status: 
          <?php if ($dueAmt > 0): ?>
            <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">PRE-BOOKED (PARTIAL)</span>
          <?php else: ?>
            <span class="badge badge-success px-2 py-1 font-weight-bold">FULLY PAID</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Line Items Table -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered">
        <thead class="bg-light">
          <tr>
            <th>Item / Service Description</th>
            <th class="text-center" style="width: 100px;">SAC Code</th>
            <th class="text-right" style="width: 120px;">Amount (INR)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div class="font-weight-bold text-dark"><?= View::e($invoice['service_name']) ?></div>
              <small class="text-muted">Professional execution per booking #<?= View::e($invoice['booking_no']) ?></small>
            </td>
            <td class="text-center small">9987</td>
            <td class="text-right font-weight-bold">₹<?= number_format((float)$invoice['subtotal'], 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Totals & Taxes Breakdown -->
    <div class="row justify-content-end mb-4">
      <div class="col-sm-7 col-md-6">
        <div class="d-flex justify-content-between py-1 small">
          <span class="text-muted">Subtotal (Taxable Value):</span>
          <span class="font-weight-bold text-dark">₹<?= number_format((float)$invoice['subtotal'], 2) ?></span>
        </div>
        <div class="d-flex justify-content-between py-1 small">
          <span class="text-muted">CGST (9.0%):</span>
          <span class="text-dark">₹<?= number_format((float)$invoice['gst_amount'] / 2, 2) ?></span>
        </div>
        <div class="d-flex justify-content-between py-1 small">
          <span class="text-muted">SGST (9.0%):</span>
          <span class="text-dark">₹<?= number_format((float)$invoice['gst_amount'] / 2, 2) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-top mt-2">
          <strong class="text-dark font-weight-bold">Total Service Value:</strong>
          <span class="font-weight-bold text-dark">₹<?= number_format((float)$invoice['total'], 2) ?></span>
        </div>

        <?php if ($dueAmt > 0): ?>
          <div class="d-flex justify-content-between py-1 small">
            <span class="text-success font-weight-bold"><i class="fa fa-check-circle mr-1"></i>Pre-Booking Advance Paid (Online):</span>
            <span class="text-success font-weight-bold">- ₹<?= number_format($advPaid, 2) ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 border-top border-bottom mt-2" style="background: #fff8f5; padding-left: 8px; padding-right: 8px; border-radius: 6px;">
            <div>
              <strong class="text-danger font-weight-bold d-block" style="font-size: 14px;">Remaining Due to Technician:</strong>
              <small class="text-muted">Payable to technician via Cash / UPI after service</small>
            </div>
            <h5 class="font-weight-bold mb-0 text-danger align-self-center">₹<?= number_format($dueAmt, 2) ?></h5>
          </div>
        <?php else: ?>
          <div class="d-flex justify-content-between py-2 border-top border-bottom mt-2" style="background: #f0fdf4; padding-left: 8px; padding-right: 8px; border-radius: 6px;">
            <div>
              <strong class="text-success font-weight-bold d-block" style="font-size: 14px;">Paid in Full Online:</strong>
              <small class="text-muted">100% Fully Settled Online</small>
            </div>
            <h5 class="font-weight-bold mb-0 text-success align-self-center">₹<?= number_format((float)$invoice['total'], 2) ?></h5>
          </div>
          <div class="d-flex justify-content-between py-1 small mt-1">
            <span class="text-muted">Remaining Balance:</span>
            <span class="text-success font-weight-bold">₹0.00 (Nil)</span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Footer Notes -->
    <div class="border-top pt-3 small text-muted text-center">
      <p class="mb-1">This is a computer-generated tax invoice for services provided by REFIXEL.</p>
      <p class="mb-0">Thank you for trusting REFIXEL for your home care needs.</p>
    </div>
  </div>
</div>

