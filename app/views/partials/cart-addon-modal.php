<?php
/**
 * REFIXEL - Flipkart / Amazon Style Cart Add-on Upsell Modal
 * Triggered automatically upon adding any service to the cart.
 */
?>
<!-- Refixel Cart Addon Modal (Flipkart / Amazon Style) -->
<div id="refixelCartAddonModal" class="rf-addon-modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="rfAddonModalTitle">
  <div class="rf-addon-backdrop" id="rfAddonBackdrop"></div>

  <div class="rf-addon-sheet" role="document">
    <!-- Drag/Sheet Handle for Mobile -->
    <div class="rf-sheet-handle-bar d-md-none">
      <span class="rf-sheet-handle"></span>
    </div>

    <!-- Modal Header -->
    <div class="rf-addon-header">
      <div class="rf-added-success-badge">
        <span class="rf-badge-check"><i class="fa fa-check"></i></span>
        <span class="rf-badge-text">Added to Cart!</span>
      </div>
      <button type="button" class="rf-addon-close-btn" id="rfAddonCloseBtn" aria-label="Close add-ons">&times;</button>
    </div>

    <div class="rf-addon-body">
      <!-- Snapshot of the Added Service -->
      <div class="rf-added-item-preview" id="rfAddedItemPreview">
        <img src="<?= \App\Core\View::asset('img/Full-home-clean.jpg') ?>" id="rfAddedItemImg" alt="Service added" class="rf-added-item-thumb">
        <div class="rf-added-item-info">
          <span class="rf-added-item-category" id="rfAddedItemCat">Cleaning Service</span>
          <h5 class="rf-added-item-title" id="rfAddedItemTitle">Service Name</h5>
          <span class="rf-added-item-price" id="rfAddedItemPrice">₹0</span>
        </div>
        <div class="rf-added-item-status">
          <span class="rf-status-pill"><i class="fa fa-check-circle"></i> In Cart</span>
        </div>
      </div>

      <!-- Upsell Section Header -->
      <div class="rf-upsell-heading-wrap">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <h4 class="rf-upsell-title" id="rfAddonModalTitle">
            <span class="rf-spark-icon"><i class="fa fa-bolt"></i></span>
            Frequently Added Together
          </h4>
          <span class="rf-upsell-pill">Refixel Smart Match</span>
        </div>
        <p class="rf-upsell-subtitle">Customers also booked these companion services for complete doorstep care.</p>
      </div>

      <!-- Add-on Items List / Grid -->
      <div class="rf-addon-cards-list" id="rfAddonCardsList">
        <!-- Injected dynamically via JS or placeholder -->
      </div>
    </div>

    <!-- Modal Footer / Checkout Bar -->
    <div class="rf-addon-footer">
      <a href="<?= \App\Core\View::url('/cart') ?>" class="btn rf-btn-checkout" id="rfBtnCheckout">
        <span class="rf-btn-checkout-label">View Cart &amp; Checkout</span>
        <span class="rf-btn-checkout-val"><strong id="rfModalCartTotal">₹0</strong> <i class="fa fa-arrow-right ml-1"></i></span>
      </a>
      <button type="button" class="btn rf-btn-continue" id="rfBtnContinue">
        <span>&larr; Continue Browsing</span>
      </button>
    </div>
  </div>
</div>

<style>
/* ========================================================
   REFIXEL SMART ADDONS MODAL (FLIPKART / AMAZON STYLE)
   ======================================================== */
.rf-addon-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.28s;
}
.rf-addon-modal-overlay.rf-show {
  opacity: 1;
  visibility: visible;
}
.rf-addon-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(10, 28, 51, 0.65);
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
  z-index: 1;
}
.rf-addon-sheet {
  position: relative;
  z-index: 2;
  background: #ffffff;
  width: 100%;
  max-width: 580px;
  border-radius: 20px;
  box-shadow: 0 25px 60px rgba(10, 28, 51, 0.25), 0 8px 24px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 40px);
  transform: scale(0.94) translateY(20px);
  transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid rgba(226, 232, 240, 0.8);
}
.rf-addon-modal-overlay.rf-show .rf-addon-sheet {
  transform: scale(1) translateY(0);
}


.rf-addon-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #edf2f7;
}
.rf-added-success-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.rf-badge-check {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #10b981;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}
.rf-badge-text {
  font-size: 15px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.2px;
}
.rf-addon-close-btn {
  background: transparent;
  border: none;
  font-size: 26px;
  color: #64748b;
  cursor: pointer;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  transition: all 0.2s ease;
}
.rf-addon-close-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}

/* Modal Body */
.rf-addon-body {
  padding: 16px 20px;
  overflow-y: auto;
  flex: 1;
}

/* Added Item Preview Row */
.rf-added-item-preview {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 10px 14px;
  margin-bottom: 18px;
}
.rf-added-item-thumb {
  width: 52px;
  height: 52px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.rf-added-item-info {
  flex: 1;
  min-width: 0;
}
.rf-added-item-category {
  font-size: 11px;
  font-weight: 700;
  color: #f25b29;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
}
.rf-added-item-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #0f172a;
  margin: 1px 0 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.rf-added-item-price {
  font-size: 13.5px;
  font-weight: 800;
  color: #0a1c33;
}
.rf-added-item-status .rf-status-pill {
  font-size: 12px;
  font-weight: 700;
  color: #059669;
  background: #d1fae5;
  border: 1px solid #a7f3d0;
  border-radius: 50px;
  padding: 4px 10px;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

/* Upsell Heading */
.rf-upsell-heading-wrap {
  margin-bottom: 14px;
}
.rf-upsell-title {
  font-size: 16px;
  font-weight: 800;
  color: #0a1c33;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}
.rf-spark-icon {
  color: #f25b29;
}
.rf-upsell-pill {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  background: #fff3ec;
  color: #f25b29;
  border: 1px solid rgba(242, 91, 41, 0.25);
  border-radius: 4px;
  padding: 2px 6px;
}
.rf-upsell-subtitle {
  font-size: 12.5px;
  color: #64748b;
  margin: 0;
}

/* Add-on Cards List */
.rf-addon-cards-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.rf-addon-card-item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 10px 12px;
  transition: all 0.22s ease;
}
.rf-addon-card-item:hover {
  border-color: rgba(242, 91, 41, 0.6);
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.1);
  transform: translateY(-1px);
}
.rf-addon-card-thumb-wrap {
  position: relative;
  flex-shrink: 0;
}
.rf-addon-card-thumb {
  width: 58px;
  height: 58px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid #e2e8f0;
}
.rf-addon-card-info {
  flex: 1;
  min-width: 0;
}
.rf-addon-card-tag {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  color: #d97706;
  background: #fef3c7;
  border-radius: 4px;
  padding: 1px 6px;
  margin-bottom: 2px;
}
.rf-addon-card-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 3px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  white-space: normal;
}
.rf-addon-card-price-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.rf-addon-card-price {
  font-size: 14px;
  font-weight: 800;
  color: #f25b29;
}
.rf-addon-card-note {
  font-size: 11px;
  color: #64748b;
}

/* 1-Click Add Button in Modal */
.rf-btn-addon-add {
  flex-shrink: 0;
  background: #0a1c33;
  color: #ffffff !important;
  border: none;
  font-size: 12.5px;
  font-weight: 700;
  padding: 7px 14px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 76px;
  text-align: center;
}
.rf-btn-addon-add:hover {
  background: #f25b29;
  color: #ffffff !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(242, 91, 41, 0.3);
}
.rf-btn-addon-add.is-added {
  background: #10b981 !important;
  color: #ffffff !important;
  border-color: #10b981 !important;
  cursor: default;
  transform: none !important;
  box-shadow: none !important;
}

/* Modal Footer Actions */
.rf-addon-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 20px;
  background: #f8fafc;
  border-top: 1px solid #edf2f7;
}
.rf-btn-continue {
  order: 1;
  background: transparent;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.rf-btn-continue:hover {
  background: #e2e8f0;
  color: #0f172a;
}
.rf-btn-checkout {
  order: 2;
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #f25b29;
  color: #ffffff !important;
  font-size: 14px;
  font-weight: 800;
  padding: 11px 18px;
  border-radius: 10px;
  text-decoration: none !important;
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);
  transition: all 0.22s ease;
  white-space: nowrap;
}
.rf-btn-checkout:hover {
  background: #d94b1c;
  color: #ffffff !important;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(242, 91, 41, 0.45);
}

/* ========================================================
   MOBILE RESPONSIVENESS & BOTTOM SHEET (< 640px)
   ======================================================== */
@media (max-width: 640px) {
  .rf-addon-modal-overlay {
    align-items: flex-end;
    padding: 0;
  }
  .rf-addon-sheet {
    max-width: 100%;
    border-radius: 22px 22px 0 0;
    max-height: 88vh;
    transform: translateY(100%);
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.25);
  }
  .rf-addon-modal-overlay.rf-show .rf-addon-sheet {
    transform: translateY(0);
  }
  .rf-sheet-handle-bar {
    display: flex;
    justify-content: center;
    padding: 10px 0 4px;
    background: #f8fafc;
  }
  .rf-sheet-handle {
    width: 44px;
    height: 4px;
    background: #cbd5e1;
    border-radius: 10px;
  }
  .rf-addon-header {
    padding: 12px 16px;
  }
  .rf-addon-body {
    padding: 12px 14px;
    max-height: calc(88vh - 170px);
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }
  .rf-added-item-preview {
    padding: 8px 10px;
    gap: 10px;
    margin-bottom: 12px;
  }
  .rf-added-item-thumb {
    width: 44px;
    height: 44px;
    min-width: 44px;
  }
  .rf-added-item-category {
    font-size: 10px;
  }
  .rf-added-item-title {
    font-size: 13.5px;
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .rf-added-item-price {
    font-size: 12.5px;
  }
  .rf-status-pill {
    font-size: 11px;
    padding: 3px 8px;
  }
  .rf-upsell-heading-wrap {
    margin-bottom: 10px;
  }
  .rf-upsell-title {
    font-size: 14.5px;
  }
  .rf-upsell-subtitle {
    font-size: 11.5px;
  }
  .rf-addon-cards-list {
    gap: 8px;
  }
  .rf-addon-card-item {
    display: flex;
    align-items: center;
    padding: 8px 10px;
    gap: 10px;
    border-radius: 12px;
  }
  .rf-addon-card-thumb-wrap {
    flex-shrink: 0;
  }
  .rf-addon-card-thumb {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 8px;
  }
  .rf-addon-card-info {
    flex: 1 1 auto;
    min-width: 0;
    padding-right: 4px;
  }
  .rf-addon-card-tag {
    font-size: 9.5px;
    padding: 1px 5px;
    margin-bottom: 2px;
  }
  .rf-addon-card-name {
    font-size: 13px !important;
    font-weight: 700 !important;
    line-height: 1.25 !important;
    white-space: normal !important;
    word-break: break-word !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    margin-bottom: 2px !important;
  }
  .rf-addon-card-price-row {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
  }
  .rf-addon-card-price {
    font-size: 13px !important;
  }
  .rf-addon-card-note {
    font-size: 10.5px !important;
  }
  .rf-btn-addon-add {
    flex-shrink: 0;
    min-width: 66px !important;
    padding: 7px 10px !important;
    font-size: 12px !important;
    border-radius: 8px;
  }

  /* Footer Action Bar: Stack vertically so buttons NEVER cut off */
  .rf-addon-footer {
    display: flex !important;
    flex-direction: column !important;
    padding: 12px 16px !important;
    padding-bottom: max(16px, env(safe-area-inset-bottom, 16px)) !important;
    gap: 8px !important;
    background: #f8fafc;
    border-top: 1px solid #edf2f7;
    width: 100%;
    box-sizing: border-box;
  }
  .rf-btn-checkout {
    order: 1 !important;
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 12px 16px !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    border-radius: 12px !important;
    white-space: normal !important;
    box-sizing: border-box !important;
  }
  .rf-btn-checkout-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .rf-btn-checkout-val {
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
  }
  .rf-btn-continue {
    order: 2 !important;
    display: block !important;
    width: 100% !important;
    text-align: center !important;
    border: none !important;
    background: transparent !important;
    color: #64748b !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    padding: 6px 12px !important;
    box-shadow: none !important;
  }
  .rf-btn-continue:hover {
    color: #0f172a !important;
    background: #f1f5f9 !important;
    border-radius: 8px !important;
  }
}

/* Extra small devices (< 380px) */
@media (max-width: 380px) {
  .rf-addon-card-thumb {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px !important;
  }
  .rf-addon-card-name {
    font-size: 12px !important;
  }
  .rf-btn-addon-add {
    min-width: 60px !important;
    padding: 6px 8px !important;
    font-size: 11px !important;
  }
  .rf-btn-checkout {
    padding: 10px 14px !important;
    font-size: 13.5px !important;
  }
  .rf-upsell-pill {
    display: none;
  }
}
</style>

<script>
(function() {
  var baseUrl = '<?= rtrim(\App\Core\View::url(), '/') ?>';
  var modalEl = document.getElementById('refixelCartAddonModal');
  var backdropEl = document.getElementById('rfAddonBackdrop');
  var closeBtn = document.getElementById('rfAddonCloseBtn');
  var continueBtn = document.getElementById('rfBtnContinue');
  var totalEl = document.getElementById('rfModalCartTotal');
  var addedTitle = document.getElementById('rfAddedItemTitle');
  var addedCat = document.getElementById('rfAddedItemCat');
  var addedPrice = document.getElementById('rfAddedItemPrice');
  var addedImg = document.getElementById('rfAddedItemImg');
  var cardsList = document.getElementById('rfAddonCardsList');

  function closeModal() {
    if (!modalEl) return;
    modalEl.classList.remove('rf-show');
    setTimeout(function() {
      modalEl.style.display = 'none';
    }, 280);
  }

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (continueBtn) continueBtn.addEventListener('click', closeModal);
  if (backdropEl) backdropEl.addEventListener('click', closeModal);

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modalEl && modalEl.classList.contains('rf-show')) {
      closeModal();
    }
  });

  // Global function called when a service is added to cart
  window.openRefixelAddonModal = function(data) {
    if (!modalEl || !data) return;

    var added = data.added_service || {};
    var addons = data.addons || [];
    var cartTotal = (data.cart && data.cart.total) ? data.cart.total : (data.cart_total || 0);

    // Populate added service info
    if (addedTitle) addedTitle.textContent = added.name || 'Service Added';
    if (addedCat) addedCat.textContent = added.category_name || 'Home Maintenance';
    if (addedPrice) addedPrice.textContent = '₹' + Number(added.starting_price || 0).toLocaleString('en-IN');
    if (addedImg) {
      var imgName = added.image || 'Full-home-clean.jpg';
      addedImg.src = baseUrl + '/assets/img/' + imgName;
    }

    if (totalEl) {
      totalEl.textContent = '₹' + Number(cartTotal).toLocaleString('en-IN');
    }

    // Populate Add-ons
    if (cardsList) {
      cardsList.innerHTML = '';

      if (addons.length === 0) {
        cardsList.innerHTML = '<p class="text-muted small py-2">No additional add-ons needed for this service.</p>';
      } else {
        addons.forEach(function(item) {
          var card = document.createElement('div');
          card.className = 'rf-addon-card-item';
          card.setAttribute('data-addon-id', item.id);

          var imgPath = baseUrl + '/assets/img/' + (item.image || 'Full-home-clean.jpg');
          var badgeText = item.badge || 'Recommended Add-on';
          var priceText = '₹' + Number(item.starting_price || 0).toLocaleString('en-IN');
          var noteText = item.duration_minutes ? ('~' + item.duration_minutes + ' mins') : 'Special rate';

          card.innerHTML = 
            '<div class="rf-addon-card-thumb-wrap">' +
              '<img src="' + imgPath + '" alt="' + (item.name || '') + '" class="rf-addon-card-thumb" onerror="this.src=\'' + baseUrl + '/assets/img/Full-home-clean.jpg\'">' +
            '</div>' +
            '<div class="rf-addon-card-info">' +
              '<span class="rf-addon-card-tag">' + badgeText + '</span>' +
              '<h5 class="rf-addon-card-name" title="' + (item.name || '') + '">' + (item.name || '') + '</h5>' +
              '<div class="rf-addon-card-price-row">' +
                '<span class="rf-addon-card-price">' + priceText + '</span>' +
                '<span class="rf-addon-card-note">' + noteText + '</span>' +
              '</div>' +
            '</div>' +
            '<button type="button" class="rf-btn-addon-add" data-service-id="' + item.id + '">' +
              '<i class="fa fa-plus mr-1"></i> Add' +
            '</button>';

          cardsList.appendChild(card);
        });
      }
    }

    // Show modal
    modalEl.style.display = 'flex';
    void modalEl.offsetWidth; // trigger reflow
    modalEl.classList.add('rf-show');
  };

  // Add-on 1-Click addition handler inside modal
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.rf-btn-addon-add');
    if (!btn || btn.classList.contains('is-added') || btn.disabled) return;

    e.preventDefault();
    e.stopPropagation();

    var serviceId = btn.getAttribute('data-service-id');
    if (!serviceId) return;

    var origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

    fetch(baseUrl + '/api/cart/add', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'service_id=' + encodeURIComponent(serviceId) + '&quantity=1'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      btn.innerHTML = '<i class="fa fa-check"></i> Added';
      btn.classList.add('is-added');

      if (totalEl && data.cart_total) {
        totalEl.textContent = '₹' + Number(data.cart_total).toLocaleString('en-IN');
      }

      if (window.syncCartUI) {
        window.syncCartUI();
      }
    })
    .catch(function(err) {
      console.error(err);
      btn.innerHTML = origHtml;
      btn.disabled = false;
    });
  });

})();
</script>
