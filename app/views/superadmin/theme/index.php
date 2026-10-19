<?php
use App\Core\View;
use App\Core\Theme;

$themeData = $themeData ?? [];
$presets = $presets ?? [];
$fonts = $fonts ?? [];
?>

<div class="super-theme-page">
  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h3 class="font-weight-bold text-dark mb-1" style="letter-spacing: -0.5px;">
        <i class="fa fa-paint-brush mr-2" style="color: <?= View::e($themeData['primary_color']) ?>;"></i>
        Theme & White-Label Customizer
      </h3>
      <p class="text-muted mb-0 small">
        Easily rebrand and customize colors, typography, and styling across the entire platform to deploy or sell to different clients.
      </p>
    </div>
    <div class="mt-2 mt-md-0 d-flex gap-2">
      <form action="<?= View::url('/superadmin/theme/reset') ?>" method="POST" onsubmit="return confirm('Reset the entire platform back to default REFIXEL brand identity (Signature Orange & Navy)?');" class="d-inline mr-2">
        <?= View::csrfField() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
          <i class="fa fa-undo mr-1"></i> Reset to Default
        </button>
      </form>
      <a href="<?= View::url('/') ?>" target="_blank" class="btn btn-sm btn-outline-primary px-3 shadow-sm">
        <i class="fa fa-external-link mr-1"></i> Preview Live Site
      </a>
    </div>
  </div>

  <form method="POST" action="<?= View::url('/superadmin/theme/save') ?>" enctype="multipart/form-data" id="themeForm">
    <?= View::csrfField() ?>
    <input type="hidden" name="apply_preset_defaults" id="apply_preset_defaults" value="0">

    <div class="row">
      <!-- Left Column: Controls & Presets -->
      <div class="col-lg-7">
        
        <!-- SECTION 1: 1-Click Client Presets -->
        <div class="card border-0 shadow-sm rounded-lg mb-4" style="border-radius: 12px; overflow: hidden;">
          <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <span class="font-weight-bold text-dark"><i class="fa fa-magic mr-2 text-primary"></i> 1-Click Client Presets</span>
            <span class="badge badge-light text-muted small px-2 py-1">Instant Rebranding</span>
          </div>
          <div class="card-body p-3">
            <p class="text-muted small mb-3">
              Click any client preset below to automatically configure harmonious colors, typography, and styling for that industry:
            </p>

            <div class="row" id="presetCardsContainer">
              <?php foreach ($presets as $pKey => $p): 
                $isSelected = ($themeData['preset'] === $pKey);
              ?>
                <div class="col-sm-6 mb-3">
                  <div class="preset-card p-3 border rounded h-100 <?= $isSelected ? 'selected' : '' ?>" 
                       data-preset="<?= View::e($pKey) ?>"
                       data-primary="<?= View::e($p['primary_color']) ?>"
                       data-hover="<?= View::e($p['primary_hover']) ?>"
                       data-secondary="<?= View::e($p['secondary_color']) ?>"
                       data-accent="<?= View::e($p['accent_color']) ?>"
                       data-font="<?= View::e($p['font_slug']) ?>"
                       data-radius="<?= View::e($p['border_radius']) ?>"
                       style="cursor: pointer; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); border-radius: 10px; border-width: <?= $isSelected ? '2px' : '1px' ?> !important; border-color: <?= $isSelected ? $p['primary_color'] : '#e2e8f0' ?> !important; background: <?= $isSelected ? 'rgba(0,0,0,0.015)' : '#ffffff' ?>;">
                    
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="badge px-2 py-1" style="background: <?= View::e($p['secondary_color']) ?>; color: #fff; font-size: 11px;">
                        <?= View::e($p['tag']) ?>
                      </span>
                      <!-- Color Swatches -->
                      <div class="d-flex gap-1" style="gap: 4px;">
                        <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background:<?= View::e($p['primary_color']) ?>; border:1px solid rgba(0,0,0,0.1);" title="Primary: <?= View::e($p['primary_color']) ?>"></span>
                        <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background:<?= View::e($p['secondary_color']) ?>; border:1px solid rgba(0,0,0,0.1);" title="Secondary: <?= View::e($p['secondary_color']) ?>"></span>
                        <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background:<?= View::e($p['accent_color']) ?>; border:1px solid rgba(0,0,0,0.1);" title="Accent: <?= View::e($p['accent_color']) ?>"></span>
                      </div>
                    </div>

                    <h6 class="font-weight-bold text-dark mb-1" style="font-size: 14px;">
                      <?= View::e($p['name']) ?>
                    </h6>
                    <p class="text-muted mb-0" style="font-size: 12px; line-height: 1.4;">
                      <?= View::e($p['description']) ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <input type="hidden" name="theme_preset" id="theme_preset" value="<?= View::e($themeData['preset']) ?>">
          </div>
        </div>

        <!-- SECTION 2: Custom Color Palette -->
        <div class="card border-0 shadow-sm rounded-lg mb-4" style="border-radius: 12px;">
          <div class="card-header bg-white border-bottom py-3">
            <span class="font-weight-bold text-dark"><i class="fa fa-sliders mr-2 text-primary"></i> Custom Color Palette</span>
          </div>
          <div class="card-body p-4">
            <div class="row">
              <!-- Primary Brand Color -->
              <div class="col-md-6 mb-3">
                <label class="font-weight-bold small text-dark d-flex align-items-center justify-content-between">
                  <span>Primary Brand Color</span>
                  <small class="text-muted">Buttons, badges, highlights</small>
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <input type="color" class="form-control p-1" id="picker_primary" value="<?= View::e($themeData['primary_color']) ?>" style="width: 44px; height: 38px; cursor: pointer;">
                  </div>
                  <input type="text" name="theme_primary_color" id="input_primary" class="form-control font-weight-bold" value="<?= View::e($themeData['primary_color']) ?>">
                </div>
              </div>

              <!-- Primary Hover Color -->
              <div class="col-md-6 mb-3">
                <label class="font-weight-bold small text-dark d-flex align-items-center justify-content-between">
                  <span>Primary Hover Shade</span>
                  <small class="text-muted">Button hover state</small>
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <input type="color" class="form-control p-1" id="picker_hover" value="<?= View::e($themeData['primary_hover']) ?>" style="width: 44px; height: 38px; cursor: pointer;">
                  </div>
                  <input type="text" name="theme_primary_hover" id="input_hover" class="form-control font-weight-bold" value="<?= View::e($themeData['primary_hover']) ?>">
                </div>
              </div>

              <!-- Secondary Brand Color -->
              <div class="col-md-6 mb-3">
                <label class="font-weight-bold small text-dark d-flex align-items-center justify-content-between">
                  <span>Secondary / Dark Color</span>
                  <small class="text-muted">Top bar, footer, dark cards</small>
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <input type="color" class="form-control p-1" id="picker_secondary" value="<?= View::e($themeData['secondary_color']) ?>" style="width: 44px; height: 38px; cursor: pointer;">
                  </div>
                  <input type="text" name="theme_secondary_color" id="input_secondary" class="form-control font-weight-bold" value="<?= View::e($themeData['secondary_color']) ?>">
                </div>
              </div>

              <!-- Accent Color -->
              <div class="col-md-6 mb-3">
                <label class="font-weight-bold small text-dark d-flex align-items-center justify-content-between">
                  <span>Accent / Tint Color</span>
                  <small class="text-muted">Badges, glows, subtitles</small>
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <input type="color" class="form-control p-1" id="picker_accent" value="<?= View::e($themeData['accent_color']) ?>" style="width: 44px; height: 38px; cursor: pointer;">
                  </div>
                  <input type="text" name="theme_accent_color" id="input_accent" class="form-control font-weight-bold" value="<?= View::e($themeData['accent_color']) ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 3: Typography & Fonts -->
        <div class="card border-0 shadow-sm rounded-lg mb-4" style="border-radius: 12px;">
          <div class="card-header bg-white border-bottom py-3">
            <span class="font-weight-bold text-dark"><i class="fa fa-font mr-2 text-primary"></i> Typography & Google Font Family</span>
          </div>
          <div class="card-body p-4">
            <div class="form-group mb-3">
              <label class="font-weight-bold small text-dark">Primary Font Family (Applied Across Entire Website)</label>
              <select name="theme_font_slug" id="select_font" class="form-control font-weight-bold" style="height: 44px;">
                <?php foreach ($fonts as $fKey => $f): ?>
                  <option value="<?= View::e($fKey) ?>" <?= ($themeData['font_slug'] === $fKey) ? 'selected' : '' ?>>
                    <?= View::e($f['name']) ?> — <?= View::e($f['desc']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted mt-2">
                Loads via optimized Google Fonts CDN with preconnect and zero-FOUC display swap.
              </small>
            </div>

            <div class="form-group mb-0">
              <label class="font-weight-bold small text-dark">Component Corner Radius</label>
              <select name="theme_border_radius" id="select_radius" class="form-control">
                <option value="6px" <?= ($themeData['border_radius'] === '6px') ? 'selected' : '' ?>>6px (Crisp, Formal & Compact)</option>
                <option value="8px" <?= ($themeData['border_radius'] === '8px') ? 'selected' : '' ?>>8px (Modern Standard)</option>
                <option value="10px" <?= ($themeData['border_radius'] === '10px') ? 'selected' : '' ?>>10px (Refixel Default — Smooth)</option>
                <option value="14px" <?= ($themeData['border_radius'] === '14px') ? 'selected' : '' ?>>14px (Rounded Modern)</option>
                <option value="20px" <?= ($themeData['border_radius'] === '20px') ? 'selected' : '' ?>>20px (Friendly Soft Pills)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- SECTION 4: Brand Identity & Media Assets -->
        <div class="card border-0 shadow-sm rounded-lg mb-4" style="border-radius: 12px;">
          <div class="card-header bg-white border-bottom py-3">
            <span class="font-weight-bold text-dark"><i class="fa fa-id-badge mr-2 text-primary"></i> Client Branding & Media</span>
          </div>
          <div class="card-body p-4">
            <div class="form-row">
              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Client Brand / Platform Name</label>
                <input type="text" name="business_name" id="input_brand_name" class="form-control font-weight-bold" value="<?= View::e($themeData['brand_name']) ?>">
                <small class="form-text text-muted">Replaces 'REFIXEL' in titles, emails, invoices & footer.</small>
              </div>

              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Brand Tagline</label>
                <input type="text" name="hero_tagline" id="input_brand_tagline" class="form-control" value="<?= View::e($themeData['brand_tagline']) ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Client Brand Logo</label>
                <input type="file" name="site_logo" class="form-control-file" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                <small class="form-text text-muted">Upload high-res transparent PNG or SVG logo.</small>
                <?php if (!empty($themeData['site_logo'])): ?>
                  <div class="mt-2 p-2 border rounded bg-light d-inline-block">
                    <img src="<?= View::asset($themeData['site_logo']) ?>" alt="Logo" style="max-height: 36px; max-width: 140px;">
                  </div>
                <?php endif; ?>
              </div>

              <div class="col-md-6 form-group mb-3">
                <label class="font-weight-bold small text-dark">Client Favicon</label>
                <input type="file" name="site_favicon" class="form-control-file" accept="image/png,image/x-icon,image/svg+xml">
                <small class="form-text text-muted">Browser tab icon (32x32 PNG or ICO).</small>
                <?php if (!empty($themeData['site_favicon'])): ?>
                  <div class="mt-2 p-2 border rounded bg-light d-inline-block">
                    <img src="<?= View::asset($themeData['site_favicon']) ?>" alt="Favicon" style="max-height: 24px; max-width: 24px;">
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button Sticky Bar -->
        <div class="p-3 bg-white border rounded shadow-sm d-flex align-items-center justify-content-between mb-4" style="position: sticky; bottom: 20px; z-index: 10; border-radius: 12px;">
          <span class="text-muted small">
            <i class="fa fa-info-circle text-primary mr-1"></i> Changes take effect immediately across all customer & admin portals.
          </span>
          <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="background-color: <?= View::e($themeData['primary_color']) ?>; border-color: <?= View::e($themeData['primary_color']) ?>;">
            <i class="fa fa-check mr-1"></i> Save & Apply Theme
          </button>
        </div>

      </div>

      <!-- Right Column: Sticky Live Interactive Preview -->
      <div class="col-lg-5">
        <div style="position: sticky; top: 90px;">
          
          <div class="card border-0 shadow rounded-lg mb-3" style="border-radius: 14px; overflow: hidden;">
            <div class="card-header bg-dark text-white py-2 px-3 d-flex align-items-center justify-content-between">
              <span class="small font-weight-bold"><i class="fa fa-eye mr-1 text-warning"></i> Real-time Client Preview</span>
              <span class="badge badge-secondary" style="font-size: 10px;">Live Simulator</span>
            </div>

            <div class="card-body p-0" id="previewContainer" style="background: #f8fafc;">
              
              <!-- Simulated Header -->
              <div id="previewHeader" style="background: <?= View::e($themeData['secondary_color']) ?>; padding: 12px 16px; color: #fff;" class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <span class="font-weight-bold" id="previewBrandText" style="font-size: 15px; letter-spacing: -0.3px;">
                    <?= View::e($themeData['brand_name']) ?>
                  </span>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                  <span style="font-size: 11px; opacity: 0.85;">Services</span>
                  <span style="font-size: 11px; opacity: 0.85;">Pricing</span>
                  <span class="badge badge-pill text-white" id="previewOfferBadge" style="background: <?= View::e($themeData['primary_color']) ?>; font-size: 10px; padding: 3px 8px;">
                    Offer 25% Off
                  </span>
                </div>
              </div>

              <!-- Simulated Hero Section -->
              <div class="p-3 text-white text-center" id="previewHero" style="background: linear-gradient(135deg, <?= View::e($themeData['secondary_color']) ?> 0%, rgba(10,28,51,0.85) 100%);">
                <span class="badge px-2 py-1 mb-2 text-white" id="previewPill" style="background: rgba(255,255,255,0.15); font-size: 10px; border: 1px solid rgba(255,255,255,0.25);">
                  ✦ Premium Home Solutions
                </span>
                <h5 class="font-weight-bold mb-1" id="previewHeading" style="font-size: 16px;">
                  Expert Deep Cleaning & Repairs
                </h5>
                <p class="mb-3 text-white-50" id="previewTagline" style="font-size: 11px;">
                  <?= View::e($themeData['brand_tagline']) ?>
                </p>
                <button type="button" class="btn btn-sm font-weight-bold px-3 py-1 shadow" id="previewHeroBtn" style="background: <?= View::e($themeData['primary_color']) ?>; color: #fff; border: none; border-radius: <?= View::e($themeData['border_radius']) ?>; font-size: 12px;">
                  Book Doorstep Service →
                </button>
              </div>

              <!-- Simulated Service Card -->
              <div class="p-3">
                <div class="card border shadow-sm" style="border-radius: <?= View::e($themeData['border_radius']) ?>; overflow: hidden; background: #fff;" id="previewCard">
                  <div style="background: #e2e8f0; height: 110px; position: relative;" class="d-flex align-items-center justify-content-center text-muted">
                    <i class="fa fa-picture-o fa-2x opacity-50"></i>
                    <span class="badge position-absolute text-white" id="previewCardBadge" style="top: 8px; left: 8px; background: <?= View::e($themeData['primary_color']) ?>; font-size: 10px;">
                      Popular
                    </span>
                  </div>
                  <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <h6 class="font-weight-bold text-dark mb-0" id="previewCardTitle" style="font-size: 13px;">Full Kitchen Deep Cleaning</h6>
                      <span class="font-weight-bold" id="previewCardPrice" style="color: <?= View::e($themeData['primary_color']) ?>; font-size: 14px;">₹1,499</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size: 11px;">Includes chimney degreasing & interior tile scrub.</p>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                      <span class="small text-muted" style="font-size: 11px;"><i class="fa fa-star text-warning"></i> 4.9 (1.2k)</span>
                      <button type="button" class="btn btn-xs px-2 py-1 font-weight-bold text-white shadow-sm" id="previewCardBtn" style="background: <?= View::e($themeData['primary_color']) ?>; border: none; border-radius: <?= View::e($themeData['border_radius']) ?>; font-size: 11px;">
                        + Add to Cart
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Simulated Footer Bar -->
              <div class="p-2 border-top text-center text-muted small" style="background: #fff; font-size: 11px;" id="previewFooter">
                © <?= date('Y') ?> <strong id="previewFooterBrand"><?= View::e($themeData['brand_name']) ?></strong>. All rights reserved.
              </div>

            </div>
          </div>

          <!-- Quick Tip Card -->
          <div class="card border-0 bg-light p-3" style="border-radius: 12px;">
            <div class="d-flex">
              <i class="fa fa-lightbulb-o text-warning mr-2 mt-1" style="font-size: 16px;"></i>
              <div class="small text-muted">
                <strong>White-Label Multi-Client Tip:</strong><br>
                To onboard a new client, choose an industry preset above, enter the client's business name, upload their transparent logo, and click <strong>Save & Apply Theme</strong>. The entire booking funnel, customer cart, invoices, and dashboards update instantly!
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const inputPrimary = document.getElementById('input_primary');
  const pickerPrimary = document.getElementById('picker_primary');
  const inputHover = document.getElementById('input_hover');
  const pickerHover = document.getElementById('picker_hover');
  const inputSecondary = document.getElementById('input_secondary');
  const pickerSecondary = document.getElementById('picker_secondary');
  const inputAccent = document.getElementById('input_accent');
  const pickerAccent = document.getElementById('picker_accent');
  const selectFont = document.getElementById('select_font');
  const selectRadius = document.getElementById('select_radius');
  const inputBrandName = document.getElementById('input_brand_name');
  const inputBrandTagline = document.getElementById('input_brand_tagline');
  const themePresetInput = document.getElementById('theme_preset');
  const applyPresetDefaults = document.getElementById('apply_preset_defaults');

  // Preview elements
  const previewHeader = document.getElementById('previewHeader');
  const previewBrandText = document.getElementById('previewBrandText');
  const previewFooterBrand = document.getElementById('previewFooterBrand');
  const previewOfferBadge = document.getElementById('previewOfferBadge');
  const previewHero = document.getElementById('previewHero');
  const previewHeroBtn = document.getElementById('previewHeroBtn');
  const previewTagline = document.getElementById('previewTagline');
  const previewCardBadge = document.getElementById('previewCardBadge');
  const previewCardPrice = document.getElementById('previewCardPrice');
  const previewCardBtn = document.getElementById('previewCardBtn');
  const previewCard = document.getElementById('previewCard');
  const previewContainer = document.getElementById('previewContainer');

  // Fonts map
  const fontFamilies = {
    'outfit': "'Outfit', sans-serif",
    'inter': "'Inter', sans-serif",
    'inter_tight': "'Inter Tight', sans-serif",
    'poppins': "'Poppins', sans-serif",
    'plus_jakarta_sans': "'Plus Jakarta Sans', sans-serif",
    'montserrat': "'Montserrat', sans-serif",
    'dm_sans': "'DM Sans', sans-serif",
    'roboto': "'Roboto', sans-serif"
  };

  function updateLivePreview() {
    const primary = inputPrimary.value;
    const secondary = inputSecondary.value;
    const radius = selectRadius.value;
    const fontSlug = selectFont.value;
    const fontFamily = fontFamilies[fontSlug] || 'sans-serif';
    const brandName = inputBrandName.value || 'REFIXEL';
    const tagline = inputBrandTagline.value || '';

    // Apply colors to preview
    if (previewHeader) previewHeader.style.background = secondary;
    if (previewHero) previewHero.style.background = 'linear-gradient(135deg, ' + secondary + ' 0%, rgba(10,28,51,0.85) 100%)';
    if (previewOfferBadge) previewOfferBadge.style.background = primary;
    if (previewHeroBtn) {
      previewHeroBtn.style.background = primary;
      previewHeroBtn.style.borderRadius = radius;
    }
    if (previewCardBadge) previewCardBadge.style.background = primary;
    if (previewCardPrice) previewCardPrice.style.color = primary;
    if (previewCardBtn) {
      previewCardBtn.style.background = primary;
      previewCardBtn.style.borderRadius = radius;
    }
    if (previewCard) previewCard.style.borderRadius = radius;

    // Apply typography to preview container
    if (previewContainer) previewContainer.style.fontFamily = fontFamily;

    // Apply text
    if (previewBrandText) previewBrandText.innerText = brandName;
    if (previewFooterBrand) previewFooterBrand.innerText = brandName;
    if (previewTagline) previewTagline.innerText = tagline;
  }

  // Color picker sync
  function syncColor(textInput, colorPicker) {
    colorPicker.addEventListener('input', function() {
      textInput.value = colorPicker.value;
      updateLivePreview();
    });
    textInput.addEventListener('input', function() {
      if (/^#[0-9A-Fa-f]{6}$/.test(textInput.value)) {
        colorPicker.value = textInput.value;
      }
      updateLivePreview();
    });
  }

  syncColor(inputPrimary, pickerPrimary);
  syncColor(inputHover, pickerHover);
  syncColor(inputSecondary, pickerSecondary);
  syncColor(inputAccent, pickerAccent);

  selectFont.addEventListener('change', updateLivePreview);
  selectRadius.addEventListener('change', updateLivePreview);
  inputBrandName.addEventListener('input', updateLivePreview);
  inputBrandTagline.addEventListener('input', updateLivePreview);

  // Preset Card Clicks
  const presetCards = document.querySelectorAll('.preset-card');
  presetCards.forEach(function(card) {
    card.addEventListener('click', function() {
      presetCards.forEach(function(c) {
        c.classList.remove('selected');
        c.style.borderWidth = '1px';
        c.style.borderColor = '#e2e8f0';
        c.style.background = '#ffffff';
      });

      card.classList.add('selected');
      card.style.borderWidth = '2px';
      card.style.borderColor = card.dataset.primary;
      card.style.background = 'rgba(0,0,0,0.015)';

      // Apply values to inputs
      const pKey = card.dataset.preset;
      themePresetInput.value = pKey;
      applyPresetDefaults.value = '1';

      inputPrimary.value = card.dataset.primary;
      pickerPrimary.value = card.dataset.primary;

      inputHover.value = card.dataset.hover;
      pickerHover.value = card.dataset.hover;

      inputSecondary.value = card.dataset.secondary;
      pickerSecondary.value = card.dataset.secondary;

      inputAccent.value = card.dataset.accent;
      pickerAccent.value = card.dataset.accent;

      selectFont.value = card.dataset.font;
      selectRadius.value = card.dataset.radius;

      updateLivePreview();
    });
  });

  // Initial preview update
  updateLivePreview();
});
</script>
