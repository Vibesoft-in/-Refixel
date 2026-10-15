<?php
use App\Core\View;

$invoice = $invoice ?? [];

if (!function_exists('refixelInvoiceAmountInWords')) {
    function refixelInvoiceAmountInWords(float $amount): string {
        $amount = round($amount, 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);

        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $convertBelowThousand = function (int $n) use ($ones, $tens): string {
            $result = '';
            if ($n >= 100) {
                $result .= $ones[(int)floor($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $result .= $tens[(int)floor($n / 10)] . ' ';
                $n %= 10;
            }
            if ($n > 0) {
                $result .= $ones[$n] . ' ';
            }
            return trim($result);
        };

        if ($whole === 0) {
            $words = 'Zero';
        } else {
            $crores = (int)floor($whole / 10000000);
            $whole %= 10000000;
            $lakhs = (int)floor($whole / 100000);
            $whole %= 100000;
            $thousands = (int)floor($whole / 1000);
            $whole %= 1000;
            $hundreds = $whole;

            $parts = [];
            if ($crores > 0) {
                $parts[] = $convertBelowThousand($crores) . ' Crore';
            }
            if ($lakhs > 0) {
                $parts[] = $convertBelowThousand($lakhs) . ' Lakh';
            }
            if ($thousands > 0) {
                $parts[] = $convertBelowThousand($thousands) . ' Thousand';
            }
            if ($hundreds > 0) {
                $parts[] = $convertBelowThousand($hundreds);
            }
            $words = implode(' ', $parts);
        }

        $out = 'Rupees ' . trim($words);
        if ($fraction > 0) {
            $out .= ' and ' . $convertBelowThousand($fraction) . ' Paise';
        }
        return $out . ' Only';
    }
}

$subtotal = (float)($invoice['subtotal'] ?? 0);
$gstAmount = (float)($invoice['gst_amount'] ?? 0);
$cgst = round($gstAmount / 2, 2);
$sgst = round($gstAmount - $cgst, 2);
$total = (float)($invoice['total'] ?? 0);

$advPaid = (float)($invoice['advance_paid'] ?? $invoice['booking_advance_amount'] ?? 0);
$dueAmt = (float)($invoice['due_amount'] ?? $invoice['booking_due_amount'] ?? 0);
$isCodAdvance = ($dueAmt > 0 || ($invoice['booking_priority'] ?? '') === 'normal' || ($invoice['booking_payment_type'] ?? '') === 'cod');
?>

<div class="refixel-invoice-container">
  <style>
    .inv-header {
      border-bottom: 2px solid #0f172a;
      padding-bottom: 20px;
      margin-bottom: 24px;
    }
    .inv-brand-name {
      font-size: 24px;
      font-weight: 800;
      color: #0a1c33;
      letter-spacing: -0.5px;
      margin: 0;
      line-height: 1.2;
    }
    .inv-brand-tagline {
      font-size: 13px;
      font-weight: 600;
      color: #f25b29;
      margin-bottom: 6px;
    }
    .inv-meta-label {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      font-weight: 700;
      color: #64748b;
    }
    .inv-meta-val {
      font-size: 13.5px;
      color: #0f172a;
    }
    .inv-panel {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 16px 18px;
      height: 100%;
    }
    .inv-panel-title {
      font-size: 11.5px;
      text-transform: uppercase;
      font-weight: 800;
      letter-spacing: 0.8px;
      color: #475569;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
      border-bottom: 1px dashed #cbd5e1;
      padding-bottom: 6px;
    }
    .inv-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      font-size: 13.5px;
    }
    .inv-table th {
      background: #0a1c33 !important;
      color: #ffffff !important;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 10px 12px;
      border: 1px solid #0a1c33;
    }
    .inv-table td {
      padding: 12px;
      border: 1px solid #e2e8f0;
      vertical-align: middle;
      color: #1e293b;
    }
    .inv-table tbody tr:nth-child(even) {
      background: #f8fafc;
    }
    .inv-totals-box {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #ffffff;
      padding: 14px 18px;
    }
    .inv-total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 0;
      font-size: 13.5px;
      color: #334155;
    }
    .inv-total-row.grand-total {
      border-top: 2px solid #0f172a;
      margin-top: 8px;
      padding-top: 10px;
      font-size: 16px;
      font-weight: 800;
      color: #0f172a;
    }
    .inv-words-box {
      background: #f8fafc;
      border-left: 3px solid #f25b29;
      padding: 10px 14px;
      border-radius: 0 6px 6px 0;
      font-size: 12.5px;
      color: #1e293b;
      margin-top: 12px;
    }
    .inv-seal-box {
      border: 2px dashed #10b981;
      border-radius: 10px;
      padding: 12px 14px;
      background: #f0fdf4;
      display: inline-flex;
      align-items: center;
      gap: 12px;
    }
    .inv-seal-icon {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #10b981;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }
    .inv-signatory-box {
      text-align: right;
    }
    .inv-stamp-placeholder {
      display: inline-block;
      border: 1.5px solid #f25b29;
      color: #f25b29;
      font-weight: 800;
      font-size: 11px;
      letter-spacing: 1px;
      padding: 6px 14px;
      border-radius: 6px;
      transform: rotate(-3deg);
      text-transform: uppercase;
      background: rgba(242, 91, 41, 0.04);
      margin-bottom: 8px;
    }
  </style>

  <!-- Top Header: Brand info & Tax Invoice Title -->
  <div class="row align-items-start inv-header">
    <div class="col-sm-7 mb-3 mb-sm-0">
      <div class="d-flex align-items-center mb-2" style="gap: 14px;">
        <img src="<?= View::asset('img/refixel-logo-horizontal.png') ?>" 
             alt="REFIXEL" 
             style="height: 44px; width: auto; max-width: 190px; object-fit: contain;"
             onerror="this.style.display='none';">
        <div>
          <h2 class="inv-brand-name">REFIXEL</h2>
          <div class="inv-brand-tagline">Home Services &amp; Facility Care</div>
        </div>
      </div>
      <div class="text-muted small" style="line-height: 1.5; font-size: 12.5px;">
        <div><strong>REFIXEL Home Services Pvt. Ltd.</strong></div>
        <div>Station Road, Kashipur, Uttarakhand - 244713</div>
        <div>GSTIN: <strong class="text-dark">07AAAAA0000A1Z5</strong> &bull; State Code: <strong>05 (UK) / 07 (DL)</strong></div>
        <div>Support: <strong>wearerefixel@gmail.com</strong> &bull; Helpline: <strong>+91 87911 54730</strong></div>
      </div>
    </div>

    <div class="col-sm-5 text-sm-right">
      <div class="mb-2">
        <span class="badge badge-dark px-3 py-1 font-weight-bold" style="background: #0a1c33; font-size: 12px; letter-spacing: 0.8px;">
          TAX INVOICE
        </span>
        <div class="text-muted" style="font-size: 11px; margin-top: 3px;">(Original for Recipient)</div>
      </div>

      <div class="font-weight-bold text-dark mt-2" style="font-size: 18px; font-family: 'JetBrains Mono', monospace; letter-spacing: -0.3px;">
        #<?= View::e($invoice['invoice_no']) ?>
      </div>
      <div class="text-muted small"><strong>Invoice Date:</strong> <?= View::e(substr((string)$invoice['issued_at'], 0, 10)) ?></div>
      <div class="text-muted small"><strong>Time of Issue:</strong> <?= View::e(substr((string)$invoice['issued_at'], 11, 8) ?: '10:00:00') ?></div>
      <div class="text-muted small"><strong>Place of Supply:</strong> Uttarakhand (05)</div>

      <div class="mt-2">
        <?php if ($dueAmt > 0): ?>
          <span class="badge px-2 py-1 font-weight-bold" style="background:#fef3c7; color:#92400e; font-size: 11px; border: 1px solid #fde68a;">
            <i class="fa fa-clock-o mr-1"></i>PRE-BOOKED (COD ADVANCE)
          </span>
        <?php else: ?>
          <span class="badge px-2 py-1 font-weight-bold" style="background:#dcfce7; color:#166534; font-size: 11px; border: 1px solid #bbf7d0;">
            <i class="fa fa-check-circle mr-1"></i>PAID IN FULL (ONLINE)
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Customer & Booking Panels -->
  <div class="row mb-4">
    <div class="col-sm-6 mb-3 mb-sm-0">
      <div class="inv-panel">
        <div class="inv-panel-title">
          <i class="fa fa-user-circle text-muted"></i>
          <span>Billed To (Customer Details)</span>
        </div>
        <div class="font-weight-bold text-dark" style="font-size: 15px; margin-bottom: 4px;">
          <?= View::e($invoice['customer_name']) ?>
        </div>
        <div class="small text-muted mb-1">
          <i class="fa fa-phone mr-1 text-muted" style="width: 14px;"></i>
          <strong><?= View::e($invoice['customer_phone']) ?></strong>
        </div>
        <?php if (!empty($invoice['customer_email'])): ?>
          <div class="small text-muted mb-1">
            <i class="fa fa-envelope mr-1 text-muted" style="width: 14px;"></i>
            <?= View::e($invoice['customer_email']) ?>
          </div>
        <?php endif; ?>
        <div class="small text-muted mt-2 pt-2 border-top">
          <i class="fa fa-map-marker mr-1 text-muted" style="width: 14px;"></i>
          <?= View::e($invoice['customer_address']) ?><?= !empty($invoice['pincode']) ? ' - ' . View::e($invoice['pincode']) : '' ?>
        </div>
      </div>
    </div>

    <div class="col-sm-6">
      <div class="inv-panel">
        <div class="inv-panel-title">
          <i class="fa fa-bookmark text-muted"></i>
          <span>Service Booking Information</span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="inv-meta-label">Booking Reference:</span>
          <span class="font-weight-bold" style="font-family: 'JetBrains Mono', monospace; color: #f25b29;">
            #<?= View::e($invoice['booking_no']) ?>
          </span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="inv-meta-label">Service Category:</span>
          <span class="font-weight-bold text-dark small"><?= View::e($invoice['service_name']) ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="inv-meta-label">Payment Mode:</span>
          <span class="font-weight-bold text-dark small"><?= strtoupper(View::e($invoice['payment_method'] ?? 'UPI / ONLINE')) ?></span>
        </div>
        <?php if (!empty($invoice['transaction_ref'])): ?>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="inv-meta-label">Transaction Ref / UTR:</span>
            <span class="small text-muted" style="font-family: 'JetBrains Mono', monospace; font-size: 11px;">
              <?= View::e($invoice['transaction_ref']) ?>
            </span>
          </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
          <span class="inv-meta-label">Service Execution:</span>
          <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px;">
            Doorstep Verified Service
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Itemized Taxable Services Table -->
  <div class="table-responsive mb-4">
    <table class="inv-table">
      <thead>
        <tr>
          <th style="width: 40px;" class="text-center">#</th>
          <th>Description of Service &amp; Scope</th>
          <th style="width: 90px;" class="text-center">SAC Code</th>
          <th style="width: 70px;" class="text-center">Qty</th>
          <th style="width: 120px;" class="text-right">Rate (₹)</th>
          <th style="width: 130px;" class="text-right">Taxable Value (₹)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center font-weight-bold text-muted">1</td>
          <td>
            <div class="font-weight-bold text-dark" style="font-size: 14px;"><?= View::e($invoice['service_name']) ?></div>
            <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
              Doorstep professional service fulfillment for Booking Reference #<?= View::e($invoice['booking_no']) ?>
            </div>
            <div class="text-muted" style="font-size: 11px; margin-top: 2px;">
              <span class="badge badge-light border text-muted" style="font-size: 10px;">Includes Labour, Standard Tools &amp; Service Warranty</span>
            </div>
          </td>
          <td class="text-center text-muted font-weight-bold" style="font-family: 'JetBrains Mono', monospace; font-size: 12px;">9987</td>
          <td class="text-center font-weight-bold">1 Visit</td>
          <td class="text-right font-weight-bold">₹<?= number_format($subtotal, 2) ?></td>
          <td class="text-right font-weight-bold text-dark">₹<?= number_format($subtotal, 2) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Financial Summary & GST Breakup -->
  <div class="row align-items-start mb-4">
    <!-- Left Column: Amount in Words, Bank / Digital Verification -->
    <div class="col-sm-6 mb-3 mb-sm-0">
      <div class="inv-words-box">
        <span class="inv-meta-label d-block mb-1">Invoice Value in Words:</span>
        <strong style="color: #0a1c33; font-size: 13px;"><?= refixelInvoiceAmountInWords($total) ?></strong>
      </div>

      <!-- Authenticity Seal Badge -->
      <div class="mt-4">
        <div class="inv-seal-box">
          <div class="inv-seal-icon">
            <i class="fa fa-shield"></i>
          </div>
          <div>
            <div class="font-weight-bold" style="color: #065f46; font-size: 13px; letter-spacing: 0.3px;">
              <i class="fa fa-check-circle mr-1"></i>REFIXEL VERIFIED DIGITAL INVOICE
            </div>
            <div class="text-muted" style="font-size: 11px; line-height: 1.4;">
              Digitally certified GST-compliant tax receipt issued by REFIXEL Home Services.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: GST Breakup, Advance & Balance Due -->
    <div class="col-sm-6">
      <div class="inv-totals-box">
        <div class="inv-total-row">
          <span class="text-muted">Subtotal (Taxable Value):</span>
          <span class="font-weight-bold text-dark">₹<?= number_format($subtotal, 2) ?></span>
        </div>
        <div class="inv-total-row">
          <span class="text-muted">Central GST (CGST @ 9.0%):</span>
          <span class="text-dark">₹<?= number_format($cgst, 2) ?></span>
        </div>
        <div class="inv-total-row">
          <span class="text-muted">State GST (SGST @ 9.0%):</span>
          <span class="text-dark">₹<?= number_format($sgst, 2) ?></span>
        </div>
        <div class="inv-total-row border-top pt-2">
          <span class="text-muted font-weight-bold">Total GST (18.0%):</span>
          <span class="font-weight-bold text-dark">₹<?= number_format($gstAmount, 2) ?></span>
        </div>

        <div class="inv-total-row grand-total">
          <span>Gross Service Total:</span>
          <span style="color: #0a1c33;">₹<?= number_format($total, 2) ?></span>
        </div>

        <?php if ($dueAmt > 0): ?>
          <div class="inv-total-row mt-2 pt-2 border-top" style="color: #166534;">
            <span><i class="fa fa-check-circle mr-1"></i>Pre-Booking Advance Paid:</span>
            <span class="font-weight-bold">- ₹<?= number_format($advPaid, 2) ?></span>
          </div>

          <div class="mt-2 p-2 rounded" style="background: #fff7ed; border: 1px solid #fed7aa;">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong style="color: #c2410c; font-size: 13px;">Remaining Doorstep Due:</strong>
                <div class="text-muted" style="font-size: 11px;">Payable to technician via Cash / UPI</div>
              </div>
              <h5 class="font-weight-bold mb-0" style="color: #c2410c; font-family: 'JetBrains Mono', monospace;">
                ₹<?= number_format($dueAmt, 2) ?>
              </h5>
            </div>
          </div>
        <?php else: ?>
          <div class="mt-2 p-2 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong style="color: #166534; font-size: 13px;"><i class="fa fa-check-circle mr-1"></i>Paid in Full Online:</strong>
                <div class="text-muted" style="font-size: 11px;">100% Fully Settled</div>
              </div>
              <h5 class="font-weight-bold mb-0" style="color: #166534; font-family: 'JetBrains Mono', monospace;">
                ₹<?= number_format($total, 2) ?>
              </h5>
            </div>
          </div>
          <div class="inv-total-row mt-1 small">
            <span class="text-muted">Balance Due:</span>
            <span class="text-success font-weight-bold">₹0.00 (Nil)</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Signatory & Legal Terms -->
  <div class="row align-items-end pt-3 border-top mt-2">
    <div class="col-sm-8 mb-3 mb-sm-0">
      <div class="text-muted small" style="line-height: 1.5; font-size: 11.5px;">
        <div class="font-weight-bold text-dark mb-1">Terms &amp; Conditions:</div>
        <div>1. All services are protected by the <strong>REFIXEL 7-Day Quality Guarantee</strong>. In case of any issues, contact support quoting Booking <strong>#<?= View::e($invoice['booking_no']) ?></strong>.</div>
        <div>2. If you paid an advance, the remaining balance must be settled with the assigned technician upon job completion.</div>
        <div>3. This invoice is generated digitally under Section 31 of CGST Act 2017 read with Rule 46 of CGST Rules 2017.</div>
      </div>
    </div>

    <div class="col-sm-4 inv-signatory-box">
      <div class="inv-stamp-placeholder">
        <i class="fa fa-certificate mr-1"></i>REFIXEL VERIFIED
      </div>
      <div class="font-weight-bold text-dark small">For REFIXEL Home Services Pvt. Ltd.</div>
      <div class="text-muted" style="font-size: 11px; margin-top: 2px;">
        Authorized Signatory<br>
        <span style="font-size: 10px;">(Digitally Verified &bull; IT Act 2000)</span>
      </div>
    </div>
  </div>
</div>
