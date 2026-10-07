<?php
use App\Core\View;
$invoice = $invoice ?? [];
?>

  <!-- Top Navigation & Actions -->
  <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap" style="gap: 10px;">
    <h3 class="m-0">Invoice Details</h3>
    <div class="d-flex" style="gap: 8px;">
      <a href="<?= View::url('/account/invoices') ?>" class="btn btn-outline-secondary font-weight-bold btn-sm">
        <i class="fa fa-arrow-left mr-1"></i> Back to Invoices
      </a>
      <button onclick="window.print()" class="btn text-white font-weight-bold btn-sm" style="background:#f25b29; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
        <i class="fa fa-print mr-1"></i> Print Receipt
      </button>
    </div>
  </div>

  <!-- Printable Invoice Document -->
  <div class="card p-4 p-md-5 border-0 shadow-sm mx-auto" style="max-width: 820px; border-radius: 16px; border: 1px solid #e2e8f0 !important;">
    <!-- Invoice Header -->
    <div class="row align-items-center mb-4 pb-3 border-bottom">
      <div class="col-sm-7">
        <h3 class="font-weight-bold mb-1" style="color: #f25b29; font-size: 26px;">REFIXEL</h3>
        <p class="text-muted small mb-0 font-weight-bold">Professional Home Care & Maintenance Services</p>
        <p class="text-muted small mb-0">GSTIN: <strong>07AAAAA0000A1Z5</strong> &bull; SAC Code: 9987</p>
        <p class="text-muted small mb-0">Support: care@refixel.com &bull; +91 94581 82006</p>
      </div>
      <div class="col-sm-5 text-sm-right mt-3 mt-sm-0">
        <span class="badge badge-success px-3 py-1 font-weight-bold mb-2" style="font-size: 12px; letter-spacing: 0.5px;">TAX INVOICE</span>
        <h5 class="font-weight-bold text-dark mb-1">#<?= View::e($invoice['invoice_no']) ?></h5>
        <div class="text-muted small">Date: <?= View::e(substr((string)$invoice['issued_at'], 0, 10)) ?></div>
        <div class="text-muted small">Payment Mode: <strong><?= strtoupper(View::e($invoice['payment_method'] ?? 'CASH')) ?></strong></div>
        <?php if (!empty($invoice['transaction_ref'])): ?>
          <div class="text-muted small">Ref: <?= View::e($invoice['transaction_ref']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Billed To & Service Booking Info -->
    <div class="row mb-4">
      <div class="col-sm-6 mb-3 mb-sm-0">
        <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Customer / Billed To:</span>
        <h6 class="font-weight-bold text-dark mb-1"><?= View::e($invoice['customer_name']) ?></h6>
        <div class="text-muted small"><i class="fa fa-phone mr-1"></i><?= View::e($invoice['customer_phone']) ?></div>
        <?php if (!empty($invoice['customer_email'])): ?>
          <div class="text-muted small"><i class="fa fa-envelope mr-1"></i><?= View::e($invoice['customer_email']) ?></div>
        <?php endif; ?>
        <div class="text-muted small mt-1">
          <i class="fa fa-map-marker mr-1"></i><?= View::e($invoice['customer_address']) ?><?= !empty($invoice['pincode']) ? ' - ' . View::e($invoice['pincode']) : '' ?>
        </div>
      </div>
      <div class="col-sm-6 text-sm-right">
        <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Booking Reference:</span>
        <div class="font-weight-bold text-dark mb-1" style="font-size: 16px;">
          <a href="<?= View::url('/account/bookings/' . (int)$invoice['booking_id']) ?>" style="color: #f25b29;">
            #<?= View::e($invoice['booking_no']) ?>
          </a>
        </div>
        <div class="text-muted small">Service: <strong><?= View::e($invoice['service_name']) ?></strong></div>
        <div class="text-muted small">Status: <span class="badge badge-success px-2 py-1">PAID</span></div>
      </div>
    </div>

    <!-- Itemized Services Table -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered mb-0" style="font-size: 14.5px;">
        <thead class="bg-light text-dark">
          <tr>
            <th class="border-0">Service Description</th>
            <th class="border-0 text-center" style="width: 110px;">SAC</th>
            <th class="border-0 text-right" style="width: 140px;">Taxable Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="py-3">
              <div class="font-weight-bold text-dark"><?= View::e($invoice['service_name']) ?></div>
              <small class="text-muted">Doorstep service execution per booking #<?= View::e($invoice['booking_no']) ?></small>
            </td>
            <td class="text-center py-3 text-muted">9987</td>
            <td class="text-right py-3 font-weight-bold text-dark">₹<?= number_format((float)$invoice['subtotal'], 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Tax & Amount Calculations -->
    <div class="row justify-content-end mb-4">
      <div class="col-sm-6 col-md-5">
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
        <div class="d-flex justify-content-between py-2 border-top border-bottom mt-2">
          <strong class="text-dark font-weight-bold" style="font-size: 15px;">Total Paid:</strong>
          <h5 class="font-weight-bold mb-0" style="color: #f25b29;">₹<?= number_format((float)$invoice['total'], 2) ?></h5>
        </div>
      </div>
    </div>

    <!-- Notes & Terms -->
    <div class="border-top pt-3 small text-muted text-center">
      <p class="mb-1">This is an authentic computer-generated GST tax invoice for services fulfilled by REFIXEL.</p>
      <p class="mb-0">For warranty claims or service queries, quote your booking number #<?= View::e($invoice['booking_no']) ?> to care@refixel.com.</p>
    </div>
  </div>
</div>
