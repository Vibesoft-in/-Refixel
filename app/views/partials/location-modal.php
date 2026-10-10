<!-- Location Gate / Selector Modal matching live site -->
<div class="loacation_popup" id="popupmodal_gate" style="display:none;">
  <div class="select_located loc-gate-card">
    <div class="locat_header">
      <h4>Select Your Location</h4>
      <span class="btn_close_loc" id="locGateCloseBtn" style="cursor:pointer; font-size:24px; line-height:1;">&times;</span>
    </div>

    <div class="loc-gate-body">
      <!-- Step 1: Choice -->
      <div id="locGateChoiceStep">
        <div class="loc-gate-msg">
          <p>Please share your location to discover services and verified professionals available in your area.</p>
        </div>
        <div class="loc-gate-actions">
          <button type="button" class="loc-gate-btn primary" id="locGateUseCurrentBtn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
            </svg>
            Use Current Location
          </button>
          <button type="button" class="loc-gate-btn outline" id="locGateManualBtn">
            <i class="fa fa-search"></i>
            Search City or Pincode Manually
          </button>
        </div>
      </div>

      <!-- Step 2: Manual Search / City Grid -->
      <div id="locGateManualStep" style="display:none;">
        <button type="button" class="loc-gate-manual-back" id="locGateBackBtn">&larr; Back to location options</button>
        <div class="loc-gate-manual">
          <div class="loc-search-wrap mb-3">
            <i class="fa fa-search loc-search-icon"></i>
            <input type="text" id="locManualInput" placeholder="Enter your city or 6-digit pincode" autocomplete="off">
          </div>
          <h6 class="text-muted small font-weight-bold mb-2">Popular Cities</h6>
          <div class="row no-gutters city-chip-grid" style="display:flex; flex-wrap:wrap; gap:8px;">
            <?php
            $popularCities = \App\Models\ServiceArea::ALLOWED_CITIES;
            foreach ($popularCities as $c):
            ?>
              <button type="button" class="btn btn-sm btn-outline-secondary select-city-btn" data-city="<?= \App\Core\View::e($c) ?>">
                <?= \App\Core\View::e($c) ?>
              </button>
            <?php endforeach; ?>
          </div>
          <div id="locGateNoResult" style="display:none;" class="text-danger small mt-2">
            We are coming soon to this area! Currently serving Kashipur, Jaspur, and Thakurdwara.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
