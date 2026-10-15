<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title><?= \App\Core\View::e($title ?? 'GST Tax Invoice | REFIXEL') ?></title>
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=3" rel="shortcut icon" type="image/png" />
  <link href="<?= \App\Core\View::asset('img/favicon.png') ?>?v=3" rel="icon" type="image/png" />

  <!-- Typography & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

  <style>
    :root {
      --brand-primary: #f25b29;
      --brand-primary-dark: #dc4a18;
      --brand-dark: #0a1c33;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --border-soft: #e2e8f0;
      --border-dark: #cbd5e1;
      --card-bg: #ffffff;
      --page-bg: #f1f5f9;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      background-color: var(--page-bg);
      font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      color: var(--text-main);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* Top Action Bar (Hidden on Print) */
    .invoice-top-bar {
      position: sticky;
      top: 0;
      z-index: 1000;
      background: #ffffff;
      border-bottom: 1px solid var(--border-soft);
      box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
      padding: 12px 24px;
    }

    .invoice-top-bar-inner {
      max-width: 920px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn-invoice-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: #f8fafc;
      color: #334155;
      font-size: 13.5px;
      font-weight: 600;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .btn-invoice-back:hover {
      background: #e2e8f0;
      color: #0f172a;
      text-decoration: none;
      transform: translateX(-2px);
    }

    .btn-invoice-print {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 20px;
      background: var(--brand-primary);
      color: #ffffff;
      font-size: 14px;
      font-weight: 600;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);
      transition: all 0.2s ease;
    }

    .btn-invoice-print:hover {
      background: var(--brand-primary-dark);
      color: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(242, 91, 41, 0.35);
    }

    .invoice-top-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--brand-dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .invoice-page-wrapper {
      padding: 24px 16px 48px;
    }

    /* Print instruction banner */
    .invoice-tip-banner {
      max-width: 860px;
      margin: 0 auto 16px;
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 8px;
      padding: 10px 16px;
      font-size: 13px;
      color: #1e40af;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* A4 Paper Document Styling */
    .invoice-sheet {
      position: relative;
      max-width: 860px;
      margin: 0 auto;
      background: var(--card-bg);
      border: 1px solid var(--border-soft);
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.04);
      padding: 44px 48px;
      overflow: hidden;
    }

    /* Authentic REFIXEL Watermark Background */
    .invoice-watermark {
      position: absolute;
      top: 52%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-26deg);
      pointer-events: none;
      user-select: none;
      z-index: 1;
      width: 480px;
      max-width: 90%;
      text-align: center;
      opacity: 0.055;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .invoice-watermark img {
      width: 400px;
      max-width: 100%;
      height: auto;
      filter: grayscale(100%);
    }

    .invoice-watermark-text {
      margin-top: 14px;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 5px;
      color: #0f172a;
      text-transform: uppercase;
    }

    .invoice-content {
      position: relative;
      z-index: 2;
    }

    /* PRINT SPECIFIC OPTIMIZATIONS */
    @media print {
      @page {
        size: A4 portrait;
        margin: 8mm 10mm;
      }

      html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
      }

      .d-print-none,
      .invoice-top-bar,
      .invoice-tip-banner {
        display: none !important;
      }

      .invoice-page-wrapper {
        padding: 0 !important;
        margin: 0 !important;
      }

      .invoice-sheet {
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 auto !important;
        max-width: 100% !important;
        padding: 24px 28px !important;
      }

      /* Force background colors, borders and watermark in print */
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .invoice-watermark {
        opacity: 0.06 !important;
      }

      .table {
        background: transparent !important;
      }
    }

    @media (max-width: 768px) {
      .invoice-sheet {
        padding: 24px 18px;
        border-radius: 8px;
      }
      .invoice-top-bar {
        padding: 10px 14px;
      }
    }
  </style>
</head>
<body class="invoice-page-body">

  <!-- Clean Top Bar (Screen Only) -->
  <div class="invoice-top-bar d-print-none">
    <div class="invoice-top-bar-inner">
      <div>
        <a href="<?= \App\Core\View::e($backUrl ?? \App\Core\View::url('/account/invoices')) ?>" class="btn-invoice-back">
          <i class="fa fa-arrow-left"></i>
          <span><?= \App\Core\View::e($backLabel ?? 'Back to Invoices') ?></span>
        </a>
      </div>

      <div class="invoice-top-title">
        <span>GST Invoice</span>
        <span class="badge badge-light border text-dark font-weight-bold" style="font-family: 'JetBrains Mono', monospace; font-size: 13px;">
          #<?= \App\Core\View::e($invoice['invoice_no'] ?? '') ?>
        </span>
      </div>

      <div>
        <button type="button" onclick="window.print()" class="btn-invoice-print">
          <i class="fa fa-print"></i>
          <span>Print / Save as PDF</span>
        </button>
      </div>
    </div>
  </div>

  <div class="invoice-page-wrapper">
    <!-- Quick helper notice on screen -->
    <div class="invoice-tip-banner d-print-none">
      <div>
        <i class="fa fa-file-pdf-o mr-2"></i>
        <span><strong>Download Instructions:</strong> Click <strong>"Print / Save as PDF"</strong>, then select <em>"Save as PDF"</em> in your browser destination menu.</span>
      </div>
      <button type="button" class="close text-muted" onclick="this.parentElement.style.display='none';" style="font-size: 18px; line-height: 1;">&times;</button>
    </div>

    <!-- The Invoice Document -->
    <div class="invoice-sheet">
      <!-- Watermark Background -->
      <div class="invoice-watermark" aria-hidden="true">
        <img src="<?= \App\Core\View::asset('img/refixel-logo-horizontal.png') ?>" alt="REFIXEL" />
        <div class="invoice-watermark-text">ORIGINAL TAX INVOICE &bull; REFIXEL HOME SERVICES</div>
      </div>

      <!-- Main Document Content -->
      <div class="invoice-content">
        <?= $content ?? '' ?>
      </div>
    </div>
  </div>

</body>
</html>
