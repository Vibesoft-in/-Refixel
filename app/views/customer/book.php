<?php
$user = \App\Core\Auth::user();
$customerProfile = null;
if ($user) {
    $dbUser = \App\Models\User::find((int)$user['id']);
    if ($dbUser) {
        $user = array_merge($user, $dbUser);
    }
    $customerProfile = \App\Core\Database::fetchOne("SELECT * FROM customer_profiles WHERE user_id = ?", [$user['id']]);
    if (!$customerProfile) {
        $latestBooking = \App\Core\Database::fetchOne(
            "SELECT name, phone, email, address, pincode FROM bookings WHERE customer_id = ? ORDER BY id DESC LIMIT 1",
            [$user['id']]
        );
        if ($latestBooking) {
            $customerProfile = [
                'house_no'     => '',
                'street'       => '',
                'address'      => $latestBooking['address'] ?? '',
                'city'         => \App\Models\ServiceArea::DEFAULT_CITY,
                'state'        => 'Uttarakhand',
                'pincode'      => $latestBooking['pincode'] ?? '',
                'address_type' => 'Home',
            ];
        }
    }
}
if (!$user && !$customerProfile && !empty($_SESSION['customer_profile'])) {
    $customerProfile = $_SESSION['customer_profile'];
}
$currentCity = \App\Models\ServiceArea::normalizeCity($_GET['city'] ?? ($_SESSION['selected_city'] ?? ($customerProfile['city'] ?? null)));
$selectedServiceId = $service['id'] ?? 0;

$savedAddresses = [];
if ($user) {
    try {
        $savedAddresses = \App\Core\Database::fetchAll(
            "SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC, id DESC",
            [$user['id']]
        ) ?: [];
    } catch (\Throwable $e) {}

    if ($customerProfile && (!empty($customerProfile['street']) || !empty($customerProfile['address']))) {
        $found = false;
        foreach ($savedAddresses as $sa) {
            if (($sa['street'] ?? '') === ($customerProfile['street'] ?? '') && ($sa['house_no'] ?? '') === ($customerProfile['house_no'] ?? '')) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            array_unshift($savedAddresses, [
                'id'           => 0,
                'label'        => $customerProfile['address_type'] ?? 'Home',
                'address_type' => $customerProfile['address_type'] ?? 'Home',
                'house_no'     => $customerProfile['house_no'] ?? '',
                'street'       => $customerProfile['street'] ?? '',
                'city'         => $customerProfile['city'] ?? $currentCity,
                'state'        => $customerProfile['state'] ?? 'Uttarakhand',
                'pincode'      => $customerProfile['pincode'] ?? '',
                'address'      => $customerProfile['address'] ?? '',
                'is_default'   => 1,
            ]);
        }
    }

    try {
        $pastBookings = \App\Core\Database::fetchAll(
            "SELECT DISTINCT address, pincode FROM bookings WHERE customer_id = ? AND address IS NOT NULL AND TRIM(address) != '' ORDER BY id DESC LIMIT 5",
            [$user['id']]
        ) ?: [];
        foreach ($pastBookings as $pb) {
            $addrText = trim((string)$pb['address']);
            if (!$addrText) continue;
            $alreadyExists = false;
            foreach ($savedAddresses as $sa) {
                if (trim((string)($sa['address'] ?? '')) === $addrText || trim((string)($sa['street'] ?? '')) === $addrText) {
                    $alreadyExists = true;
                    break;
                }
            }
            if (!$alreadyExists) {
                $savedAddresses[] = [
                    'id'           => 'bk_' . substr(md5($addrText), 0, 8),
                    'label'        => 'Previous Booking',
                    'address_type' => 'Other',
                    'house_no'     => '',
                    'street'       => $addrText,
                    'city'         => $customerProfile['city'] ?? $currentCity,
                    'state'        => $customerProfile['state'] ?? 'Uttarakhand',
                    'pincode'      => $pb['pincode'] ?? '',
                    'address'      => $addrText,
                    'is_default'   => 0,
                ];
            }
        }
    } catch (\Throwable $e) {}
}
if (!$user && empty($savedAddresses) && !empty($_SESSION['customer_addresses'])) {
    $savedAddresses = $_SESSION['customer_addresses'];
}

$hasSavedAddress = !empty($customerProfile) && (
    !empty($customerProfile['street']) ||
    !empty($customerProfile['address'])
);
$initialHouseNo = !empty($customerProfile['house_no']) ? $customerProfile['house_no'] : '';
$initialStreet = $customerProfile['street'] ?? '';
$initialCity = $customerProfile['city'] ?? $currentCity;
$initialState = $customerProfile['state'] ?? (($currentCity === 'Thakurdwara') ? 'Uttar Pradesh' : 'Uttarakhand');
$initialPincode = $customerProfile['pincode'] ?? '';
$initialSummaryParts = array_filter([$initialHouseNo, $initialStreet, $initialCity, $initialState]);
$initialSummaryLine = implode(', ', $initialSummaryParts) . (!empty($initialPincode) ? ' - ' . $initialPincode : '');
?>
<div class="container py-5 my-3">
  <div class="row">
    <!-- Booking Form -->
    <div class="col-lg-8 mb-4">
      <div class="card p-4 p-md-5 border-0 shadow-sm doorstep-form-card" style="border-radius: 16px;">
        <h2 class="font-weight-bold mb-2" style="font-size: 26px; color: #1a1a1a;">Book Doorstep Service</h2>
        <p class="text-muted small mb-4">A background-verified technician equipped with mechanized tools will arrive at your scheduled slot.</p>

        <style>
        .booking-section-card {
          background: #ffffff !important;
          color: #1e293b !important;
          border: 1.5px solid rgba(242, 91, 41, 0.45) !important;
          border-radius: 16px !important;
          box-shadow: 0 0 16px rgba(242, 91, 41, 0.18), 0 4px 12px rgba(242, 91, 41, 0.08) !important;
          margin-bottom: 22px !important;
          transition: border-color 0.2s ease, box-shadow 0.2s ease;
          overflow: visible !important;
        }
        .booking-section-card:hover,
        .booking-section-card:focus-within {
          border-color: rgba(242, 91, 41, 0.8) !important;
          box-shadow: 0 0 22px rgba(242, 91, 41, 0.26), 0 6px 16px rgba(242, 91, 41, 0.12) !important;
        }
        @media (max-width: 991px) {
          .doorstep-form-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
          }
          .doorstep-form-card > h2 {
            font-size: 20px !important;
            padding: 0 2px !important;
          }
          .doorstep-form-card > p {
            padding: 0 2px !important;
          }
          .booking-section-card {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
          }
        }
        @media (max-width: 576px) {
          .card.p-4 {
            padding: 18px 16px !important;
          }
          .booking-section-card {
            padding: 18px 16px !important;
          }
        }
        .btn-confirm-booking {
          background: #f25b29 !important;
          color: #ffffff !important;
          border-radius: 10px;
          font-size: 15px;
          font-weight: 700;
          padding: 12px 18px;
          box-shadow: 0 4px 14px rgba(242, 91, 41, 0.28);
          white-space: nowrap !important;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: none;
          transition: all 0.2s ease;
          line-height: 1.25;
          text-decoration: none;
          cursor: pointer;
          width: 100%;
        }
        .btn-confirm-booking:hover {
          background: #e04816 !important;
          box-shadow: 0 6px 18px rgba(242, 91, 41, 0.38);
          color: #ffffff !important;
        }
        @media (max-width: 576px) {
          .btn-confirm-booking {
            font-size: 14px !important;
            padding: 11px 12px !important;
            white-space: nowrap !important;
          }
        }
        @media (max-width: 380px) {
          .btn-confirm-booking {
            font-size: 13px !important;
            padding: 10px 8px !important;
            white-space: nowrap !important;
          }
        }
        .booking-section-card h5 {
          color: #0a1c33 !important;
        }
        .booking-section-card label {
          color: #334155 !important;
        }
        .booking-section-card .form-control {
          background: #ffffff !important;
          color: #0f172a !important;
          border: 1.5px solid #cbd5e1 !important;
        }
        .booking-section-card .form-control:focus {
          border-color: #f25b29 !important;
          box-shadow: 0 0 0 3px rgba(242, 91, 41, 0.2) !important;
        }
        .booking-section-card .custom-control-label {
          color: #1e293b !important;
          cursor: pointer;
        }
        .booking-section-card .text-muted {
          color: #64748b !important;
        }
        .street-label-row {
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          gap: 6px;
          min-height: 28px;
          margin-bottom: 4px;
        }
        .btn-use-curr-loc {
          background: #fff3ec !important;
          color: #f25b29 !important;
          border: 1.5px solid #ffdacf !important;
          font-weight: 600 !important;
          border-radius: 6px !important;
          padding: 2px 7px !important;
          font-size: 11px !important;
          display: inline-flex !important;
          align-items: center !important;
          gap: 4px !important;
          transition: all 0.2s ease !important;
          box-shadow: 0 1px 4px rgba(242, 91, 41, 0.08) !important;
          cursor: pointer !important;
          white-space: nowrap !important;
          line-height: 1.35 !important;
          max-width: 100% !important;
        }
        .btn-use-curr-loc:hover {
          background: #ffede3 !important;
          border-color: #f25b29 !important;
          color: #e04816 !important;
        }
        @media (max-width: 576px) {
          .street-label-row {
            gap: 4px;
          }
          .btn-use-curr-loc {
            padding: 2px 6px !important;
            font-size: 10.5px !important;
          }
        }
        .booking-section-card .service-summary-pill {
          background: #f8fafc !important;
          border-color: #e2e8f0 !important;
        }
        .booking-section-card .service-summary-pill h6 {
          color: #0f172a !important;
        }
        .booking-section-card .schedule-summary-box {
          background: #ffffff !important;
        }
        </style>

        <form action="<?= \App\Core\View::url('/book') ?>" method="POST" id="bookingForm">
          <?= \App\Core\View::csrf() ?>

          <div class="booking-form-sections-parent">
            <!-- 1st Section: Service Selection & Contact Information -->
            <div class="booking-section-card p-3 p-md-4">
              <!-- Service Selection -->
              <div class="form-group mb-4">
                <label class="font-weight-bold small">Selected Service <span class="text-danger">*</span></label>
                <?php if ($service): ?>
                  <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>" id="serviceIdInput">
                  <div class="p-3 rounded bg-light border d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="font-weight-bold mb-0"><?= \App\Core\View::e($service['name']) ?></h6>
                      <small class="text-muted">Estimated duration: ~<?= (int)($service['duration_minutes'] ?? 60) ?> mins</small>
                    </div>
                    <h5 class="font-weight-bold mb-0" style="color: #f25b29;">₹<?= number_format((float)$service['starting_price'], 0) ?></h5>
                  </div>
                <?php else: ?>
                  <select name="service_id" id="serviceIdSelect" class="form-control" required>
                    <option value="">-- Choose a service package --</option>
                    <?php foreach ($allServices ?? [] as $s): ?>
                      <option value="<?= (int)$s['id'] ?>" data-price="<?= (float)$s['starting_price'] ?>">
                        <?= \App\Core\View::e($s['name']) ?> (Starting at ₹<?= number_format((float)$s['starting_price'], 0) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
              </div>

              <!-- Contact Details -->
              <h5 class="font-weight-bold mt-4 mb-3" style="font-size: 18px; color: #0a1c33;">1. Contact Details</h5>
              <div class="form-row">
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold small">Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" value="<?= \App\Core\View::e($user['name'] ?? '') ?>" placeholder="e.g. Ananya Sharma" required>
                </div>
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold small">Mobile Number <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">+91</span></div>
                    <input type="tel" name="phone" class="form-control" value="<?= \App\Core\View::e($user['phone'] ?? '') ?>" placeholder="10-digit number" pattern="[6-9][0-9]{9}" required>
                  </div>
                </div>
              </div>
              <div class="form-group mb-0">
                <label class="font-weight-bold small">Email Address (Optional for GST Invoice)</label>
                <input type="email" name="email" class="form-control" value="<?= \App\Core\View::e($user['email'] ?? '') ?>" placeholder="yourname@example.com">
              </div>
            </div>

            <!-- 2nd Section: Service Address -->
            <div class="booking-section-card p-3 p-md-4">
              <div class="d-flex align-items-center flex-wrap mb-2" style="gap: 8px;">
                <h5 class="font-weight-bold mb-0" style="font-size: 18px; color: #0a1c33;">2. Service Address</h5>
                <span id="bookingAutoFillBadge" class="badge" style="display: none; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 11.5px; font-weight: 600; padding: 4px 9px; border-radius: 6px;">
                  <i class="fa fa-check-circle mr-1"></i> Auto-filled from Home Page
                </span>
              </div>
              <div id="bookingLocStatus" class="small mb-3" style="display: none;"></div>
              
              <div class="form-group mb-3">
                <label class="font-weight-bold small d-block mb-1">Address Type</label>
                <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                  <div class="custom-control custom-radio custom-control-inline mb-0 mr-0">
                    <input type="radio" id="typeHomeBook" name="address_type" class="custom-control-input" value="Home" <?= ($customerProfile['address_type'] ?? 'Home') === 'Home' ? 'checked' : '' ?>>
                    <label class="custom-control-label small" for="typeHomeBook">Home</label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline mb-0 mr-0">
                    <input type="radio" id="typeOfficeBook" name="address_type" class="custom-control-input" value="Office" <?= ($customerProfile['address_type'] ?? '') === 'Office' ? 'checked' : '' ?>>
                    <label class="custom-control-label small" for="typeOfficeBook">Office</label>
                  </div>
                  <div class="custom-control custom-radio custom-control-inline mb-0 mr-0">
                    <input type="radio" id="typeOtherBook" name="address_type" class="custom-control-input" value="Other" <?= ($customerProfile['address_type'] ?? '') === 'Other' ? 'checked' : '' ?>>
                    <label class="custom-control-label small" for="typeOtherBook">Other</label>
                  </div>
                </div>
              </div>

              <!-- Choose Saved Address Action Bar (Below Address Type) -->
              <div class="choose-saved-address-bar mb-3 p-2 px-3 rounded" style="background: #f8fafc; border: 1.5px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div class="d-flex align-items-center" style="gap: 9px; min-width: 0;">
                  <div style="width: 32px; height: 32px; border-radius: 8px; background: #fff3ec; color: #f25b29; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                    <i class="fa fa-map-marker"></i>
                  </div>
                  <div style="min-width: 0;">
                    <div class="font-weight-bold" style="font-size: 13px; color: #0a1c33; line-height: 1.2;">
                      Choose Saved Address
                    </div>
                    <div class="text-muted text-truncate" id="activeAddressSubtext" style="font-size: 11.5px; line-height: 1.2;">
                      <?= !empty($customerProfile['street']) ? ('Active: ' . \App\Core\View::e($customerProfile['address_type'] ?? 'Home') . ' - ' . \App\Core\View::e($customerProfile['street'])) : 'Pick Home, Office, or another saved location' ?>
                    </div>
                  </div>
                </div>
                <button type="button" id="btnOpenAddressPicker" class="btn btn-sm btn-choose-address" style="background: #ffffff; color: #f25b29; border: 1.5px solid #f25b29; font-weight: 700; font-size: 11.5px; border-radius: 6px; padding: 4px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; white-space: nowrap; flex-shrink: 0; box-shadow: 0 1px 3px rgba(242, 91, 41, 0.1); transition: all 0.2s ease;">
                  <i class="fa fa-list-ul"></i> <span>Choose Address</span>
                  <span class="badge badge-pill badge-primary ml-1" id="savedAddressesCountBadge" style="background: #f25b29; color: #fff; font-size: 10.5px; padding: 2px 6px;"><?= count($savedAddresses) ?></span>
                </button>
              </div>

              <!-- Modal: Choose From Saved Addresses -->
              <div class="modal fade" id="savedAddressesModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                  <div class="modal-content" style="border-radius: 16px; border: 1.5px solid #e2e8f0; box-shadow: 0 12px 40px rgba(0,0,0,0.18); overflow: hidden;">
                    <div class="modal-header py-3 px-4" style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                      <div>
                        <h5 class="modal-title font-weight-bold mb-1" style="color: #0a1c33; font-size: 17px;">
                          <i class="fa fa-map-marker mr-2 text-danger"></i> Choose Service Address
                        </h5>
                        <p class="text-muted small mb-0">Select which saved location you want REFIXEL technicians to visit:</p>
                      </div>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 26px; outline: none;">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body p-3 p-md-4" style="max-height: 60vh; overflow-y: auto;">
                      <div id="savedAddressesList" class="d-flex flex-column" style="gap: 10px;">
                        <!-- Populated dynamically via JS -->
                      </div>
                    </div>
                    <div class="modal-footer py-2 px-4 d-flex justify-content-between align-items-center" style="background: #f8fafc; border-top: 1.5px solid #e2e8f0;">
                      <button type="button" id="btnAddNewAddressFromModal" class="btn btn-sm" style="background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 600; border-radius: 6px; padding: 6px 14px;">
                        <i class="fa fa-plus-circle mr-1"></i> + Enter New Address
                      </button>
                      <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" style="font-weight: 600; border-radius: 6px; padding: 6px 16px;">
                        Close
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Saved Address Summary Box (Visible when address exists) -->
              <div id="savedAddressSummaryBox" class="p-3 mb-3" style="<?= $hasSavedAddress ? 'display: block;' : 'display: none;' ?> background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; transition: all 0.2s ease;">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                  <div style="flex: 1; min-width: 220px;">
                    <div class="d-flex align-items-center mb-1" style="gap: 7px;">
                      <span class="badge" id="savedAddressTypeBadge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px; text-transform: uppercase;">
                        <?= \App\Core\View::e($customerProfile['address_type'] ?? 'Home') ?>
                      </span>
                      <span style="font-size: 12px; color: #059669; font-weight: 600;">
                        <i class="fa fa-map-marker mr-1"></i> Saved Address
                      </span>
                    </div>
                    <div id="savedAddressSummaryText" style="font-size: 13.5px; color: #1e293b; font-weight: 600; line-height: 1.4;">
                      <?= \App\Core\View::e($initialSummaryLine) ?>
                    </div>
                  </div>
                  <div>
                    <button type="button" id="btnToggleAddressDetails" class="btn btn-sm" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; font-weight: 600; font-size: 12px; border-radius: 6px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                      <span id="toggleAddressBtnText">Change / Edit Address</span>
                      <i id="toggleAddressBtnIcon" class="fa fa-chevron-down" style="font-size: 10.5px;"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Collapsible Address Detail Fields (House No, Street, City, State, Pincode, Landmark) -->
              <div id="addressDetailedFields" style="<?= $hasSavedAddress ? 'display: none;' : 'display: block;' ?> transition: all 0.2s ease;">
                <div class="form-row">
                  <div class="col-md-6 form-group">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="min-height: 28px;">
                      <label class="font-weight-bold small mb-0" for="inputHouseNo">House / Flat / Office No. <span class="text-danger">*</span></label>
                    </div>
                    <input type="text" name="house_no" id="inputHouseNo" class="form-control" value="<?= \App\Core\View::e($customerProfile['house_no'] ?? '') ?>" placeholder="e.g. Flat 604 / House No." required>
                  </div>
                  <div class="col-md-6 form-group">
                    <div class="street-label-row">
                      <label class="font-weight-bold small mb-0" for="inputStreet">Street / Society / Area <span class="text-danger">*</span></label>
                      <button type="button" id="btnUseCurrentLocation" class="btn btn-sm btn-use-curr-loc">
                        <i class="fa fa-crosshairs"></i> <span>Detect Location</span>
                      </button>
                    </div>
                    <input type="text" name="street" id="inputStreet" class="form-control" value="<?= \App\Core\View::e($customerProfile['street'] ?? '') ?>" placeholder="e.g. Colony, Street, Apartment" required autocomplete="off">
                  </div>
                </div>

                <div class="form-row">
                  <div class="col-md-4 form-group">
                    <label class="font-weight-bold small">City <span class="text-danger">*</span></label>
                    <input type="text" name="city" id="inputCity" class="form-control" value="<?= \App\Core\View::e($customerProfile['city'] ?? $currentCity) ?>" placeholder="e.g. Kashipur, Jaspur, Thakurdwara" required>
                  </div>
                  <div class="col-md-4 form-group">
                    <label class="font-weight-bold small">State <span class="text-danger">*</span></label>
                    <input type="text" name="state" id="inputState" class="form-control" value="<?= \App\Core\View::e($customerProfile['state'] ?? (($currentCity === 'Thakurdwara') ? 'Uttar Pradesh' : 'Uttarakhand')) ?>" placeholder="e.g. State / Province / Region" required>
                  </div>
                  <div class="col-md-4 form-group">
                    <label class="font-weight-bold small">Pincode <span class="text-danger">*</span></label>
                    <input type="text" name="pincode" id="inputPincode" class="form-control" placeholder="Postal Code / PIN" value="<?= \App\Core\View::e($customerProfile['pincode'] ?? '') ?>" required>
                  </div>
                </div>
                <div class="form-group mb-0">
                  <label class="font-weight-bold small">Complete Address / Landmark (Optional)</label>
                  <textarea name="address" id="inputAddress" rows="2" class="form-control" placeholder="Any extra landmark details"><?= \App\Core\View::e($customerProfile['address'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top: 1px dashed #e2e8f0; gap: 10px; flex-wrap: wrap;">
                  <div id="saveAddressFeedback" style="font-size: 13px; font-weight: 600; display: none;"></div>
                  <button type="button" id="btnSaveAddress" class="btn btn-sm ml-auto" style="background: linear-gradient(135deg, #f25b29 0%, #e04816 100%); color: #ffffff; border: none; font-weight: 600; padding: 6px 18px; border-radius: 6px; box-shadow: 0 2px 6px rgba(242, 91, 41, 0.25); display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s ease;">
                    <i class="fa fa-save"></i> <span>Save Address</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- 3rd Section: Preferred Schedule & Booking Confirmation -->
            <div class="booking-section-card p-3 p-md-4">
              <h5 class="font-weight-bold mb-3" style="font-size: 18px; color: #0a1c33;">3. Preferred Schedule</h5>
              
              <!-- Hidden inputs for backend form processing -->
              <input type="hidden" name="preferred_date" id="preferredDateInput" value="<?= date('Y-m-d') ?>" required>
              <input type="hidden" name="preferred_time" id="preferredTimeInput" value="" required>

              <div class="row mb-4">
                <!-- Calendar Input with Popover Dropdown (Matching reference image) -->
                <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                  <label class="font-weight-bold small text-muted text-uppercase mb-2 d-block" style="letter-spacing: 0.5px; font-size: 12px;">
                    Select Service Date <span class="text-danger">*</span>
                  </label>

                  <div class="position-relative" id="datePickerContainer">
                    <!-- Clickable Date Input Trigger -->
                    <div class="cal-input-trigger" id="dateInputTrigger" role="button" tabindex="0">
                      <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                        <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                          <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" id="displayDateInput" class="cal-custom-input" readonly value="<?= date('d M Y') ?>" placeholder="Click to select date...">
                      </div>
                      <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="calChevronIcon"></i>
                    </div>

                    <!-- Dropdown / Popover Calendar -->
                    <div id="calendarDropdown" class="calendar-dropdown-popover">
                      <div class="refixel-calendar-card">
                        <!-- Top Header: Day Number, Month Name & Navigation -->
                        <div class="cal-top-header">
                          <div class="cal-title-wrap">
                            <div id="calDayNumber" class="cal-day-num"><?= date('j') ?></div>
                            <div id="calMonthName" class="cal-month-name"><?= date('F') ?></div>
                          </div>
                          <div class="cal-nav-wrap d-flex align-items-center" style="gap: 6px;">
                            <button type="button" id="calPrevMonthBtn" class="cal-nav-btn" title="Previous Month" aria-label="Previous Month">
                              <i class="fa fa-chevron-left" style="font-size: 11px;"></i>
                            </button>
                            <!-- Mini 3D Calendar Icon Badge from reference image -->
                            <div class="cal-icon-badge" title="Calendar">
                              <div class="cal-badge-bar"></div>
                              <div class="cal-badge-dots">
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot red"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                                <span class="cal-badge-dot"></span>
                              </div>
                            </div>
                            <button type="button" id="calNextMonthBtn" class="cal-nav-btn" title="Next Month" aria-label="Next Month">
                              <i class="fa fa-chevron-right" style="font-size: 11px;"></i>
                            </button>
                          </div>
                        </div>

                        <!-- 3-Segment Accent Bar (matching reference image) -->
                        <div class="cal-accent-divider">
                          <span class="bar-segment" style="flex: 1.2;"></span>
                          <span class="bar-segment" style="flex: 1.8;"></span>
                          <span class="bar-segment muted" style="flex: 2.2;"></span>
                        </div>

                        <!-- Weekday Headers (M T W T F S S) -->
                        <div class="cal-weekdays">
                          <span class="cal-weekday">M</span>
                          <span class="cal-weekday">T</span>
                          <span class="cal-weekday">W</span>
                          <span class="cal-weekday">T</span>
                          <span class="cal-weekday">F</span>
                          <span class="cal-weekday">S</span>
                          <span class="cal-weekday">S</span>
                        </div>

                        <!-- Monthly Days Grid -->
                        <div id="calDaysGrid" class="cal-days-grid">
                          <!-- Generated dynamically via JS -->
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Time Slot Dropdown Selection -->
                <div class="col-lg-6 col-md-12">
                  <label class="font-weight-bold small text-muted text-uppercase mb-2 d-block" style="letter-spacing: 0.5px; font-size: 12px;">
                    Select Time Slot <span class="text-danger">*</span>
                  </label>

                  <div class="position-relative" id="timePickerContainer">
                    <!-- Clickable Time Input Trigger -->
                    <div class="cal-input-trigger" id="timeInputTrigger" role="button" tabindex="0">
                      <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                        <div class="cal-trigger-icon" style="color: #ff5238; font-size: 16px;">
                          <i class="fa fa-clock-o"></i>
                        </div>
                        <input type="text" id="displayTimeInput" class="cal-custom-input" readonly value="" placeholder="Click to select time slot...">
                      </div>
                      <i class="fa fa-chevron-down text-muted cal-trigger-chevron" id="timeChevronIcon"></i>
                    </div>

                    <!-- Dropdown / Popover Time Slot List -->
                    <div id="timeDropdown" class="time-dropdown-popover">
                      <div class="time-dropdown-menu-card">
                        <div class="time-dropdown-header pb-2 mb-2 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #f1f5f9; padding: 4px 6px;">
                          <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: #64748b;">Available Arrival Slots</span>
                          <span class="badge" style="background: #fff3ec; color: #ff5238; font-size: 10.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px;">Doorstep Visit</span>
                        </div>
                        <div class="time-options-list" id="timeSlotsGroup">
                          <div class="time-option-item" role="button" tabindex="0" data-slot="08:00 - 10:00 AM" data-start-minutes="480">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                              <div class="time-opt-icon"><i class="fa fa-sun-o"></i></div>
                              <div>
                                <div class="time-opt-title font-weight-bold">08:00 - 10:00 AM</div>
                                <div class="time-opt-sub">Early Morning Slot</div>
                              </div>
                            </div>
                            <i class="fa fa-check time-opt-check"></i>
                          </div>

                          <div class="time-option-item" role="button" tabindex="0" data-slot="10:00 AM - 12:00 PM" data-start-minutes="600">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                              <div class="time-opt-icon"><i class="fa fa-sun-o"></i></div>
                              <div>
                                <div class="time-opt-title font-weight-bold">10:00 AM - 12:00 PM</div>
                                <div class="time-opt-sub">Morning Slot</div>
                              </div>
                            </div>
                            <i class="fa fa-check time-opt-check"></i>
                          </div>

                          <div class="time-option-item" role="button" tabindex="0" data-slot="12:00 - 02:00 PM" data-start-minutes="720">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                              <div class="time-opt-icon"><i class="fa fa-sun-o"></i></div>
                              <div>
                                <div class="time-opt-title font-weight-bold">12:00 - 02:00 PM</div>
                                <div class="time-opt-sub">Noon Slot</div>
                              </div>
                            </div>
                            <i class="fa fa-check time-opt-check"></i>
                          </div>

                          <div class="time-option-item" role="button" tabindex="0" data-slot="02:00 - 04:00 PM" data-start-minutes="840">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                              <div class="time-opt-icon"><i class="fa fa-cloud"></i></div>
                              <div>
                                <div class="time-opt-title font-weight-bold">02:00 - 04:00 PM</div>
                                <div class="time-opt-sub">Afternoon Slot</div>
                              </div>
                            </div>
                            <i class="fa fa-check time-opt-check"></i>
                          </div>

                          <div class="time-option-item" role="button" tabindex="0" data-slot="04:00 - 06:00 PM" data-start-minutes="960">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                              <div class="time-opt-icon"><i class="fa fa-moon-o"></i></div>
                              <div>
                                <div class="time-opt-title font-weight-bold">04:00 - 06:00 PM</div>
                                <div class="time-opt-sub">Evening Slot</div>
                              </div>
                            </div>
                            <i class="fa fa-check time-opt-check"></i>
                          </div>

                          <div class="time-option-item" role="button" tabindex="0" data-slot="06:00 - 08:00 PM" data-start-minutes="1080">
                            <div class="d-flex align-items-center" style="gap: 12px;">
                              <div class="time-opt-icon"><i class="fa fa-moon-o"></i></div>
                              <div>
                                <div class="time-opt-title font-weight-bold">06:00 - 08:00 PM</div>
                                <div class="time-opt-sub">Late Evening Slot</div>
                              </div>
                            </div>
                            <i class="fa fa-check time-opt-check"></i>
                          </div>
                        </div>

                        <!-- Notice when no slots remain for today -->
                        <div id="timeSlotsNoAvailableMsg" class="p-3 text-center small mt-2" style="display:none; background:#fff8f5; border:1.5px dashed #ffdacf; border-radius:12px; color:#c2410c;">
                          <i class="fa fa-clock-o text-danger mb-1" style="font-size:18px;"></i>
                          <div class="font-weight-bold text-dark" style="font-size: 13px;">No slots available for today</div>
                          <div class="text-muted mt-1" style="font-size: 11.5px; line-height: 1.4;">
                            Bookings require at least 30 minutes advance notice. All time slots for today have closed.
                          </div>
                          <button type="button" class="btn btn-sm text-white font-weight-bold mt-2" style="background:#f25b29; border-radius:8px; font-size:12px; padding: 6px 14px;" id="btnPickTomorrowFromSlot">
                            <i class="fa fa-calendar mr-1"></i> Pick Tomorrow
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Scheduled Slot Badge (Full width below both dropdowns) -->
                <div class="col-12 mt-3">
                  <div class="schedule-summary-box p-3 rounded-lg" style="background: #fff8f5; border: 1.5px dashed #ffdacf; border-radius: 14px;">
                    <div class="d-flex align-items-center">
                      <div class="schedule-summary-icon mr-3" style="width: 40px; height: 40px; border-radius: 12px; background: #ff5238; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(255, 82, 56, 0.3);">
                        <i class="fa fa-calendar-check-o"></i>
                      </div>
                      <div>
                        <div class="small" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; color: #f25b29;">Confirmed Slot</div>
                        <div class="font-weight-bold text-dark" id="calSummaryDisplay" style="font-size: 14px;">Loading schedule...</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Special instructions -->
              <div class="form-group mb-4">
                <label class="font-weight-bold small">Specific Instructions or Issues (Optional)</label>
                <textarea name="issue_details" rows="2" class="form-control" placeholder="e.g. Please bring extra tile descaler, parking available in basement"></textarea>
              </div>

              <button type="submit" class="btn text-white font-weight-bold btn-confirm-booking" id="bookSubmitBtn">
                <span>Confirm &amp; Schedule Booking &rarr;</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Booking Summary Sticky Card -->
    <div class="col-lg-4">
      <div class="card p-4 border-0 shadow-sm sticky-top" style="top: 90px; border-radius: 16px; background:#fff8f5; border:1px solid #ffdacf;">
        <h4 class="font-weight-bold mb-3" style="font-size: 20px; color: #0a1c33;">Booking Summary</h4>

        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Service Base Fee:</span>
          <span class="font-weight-bold text-dark" id="summaryBasePrice">₹<?= $service ? number_format((float)$service['starting_price'], 0) : '0' ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Safety & Hygiene Gear:</span>
          <span class="font-weight-bold" style="color: #f25b29;">FREE</span>
        </div>
        <div class="d-flex justify-content-between mb-2 small text-muted">
          <span>Taxes & GST (18%):</span>
          <span>Included</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-baseline mb-4">
          <span class="font-weight-bold">Estimated Total:</span>
          <h3 class="font-weight-bold mb-0" style="color: #f25b29;" id="summaryTotal">₹<?= $service ? number_format((float)$service['starting_price'], 0) : '0' ?></h3>
        </div>

        <div class="p-3 rounded bg-white border mb-3 small" style="line-height: 1.8;">
          <div class="d-flex align-items-center mb-1 font-weight-bold" style="color: #f25b29;">
            <i class="fa fa-shield mr-2"></i> REFIXEL Promise
          </div>
          <p class="text-muted mb-0">Pay securely after completion. No advance required. Full 24-hour re-clean guarantee on all packages.</p>
        </div>

        <small class="text-muted text-center d-block">
          Need immediate assistance? Call <a href="tel:+918791154730" class="text-dark font-weight-bold">+91 87911 54730</a>
        </small>
      </div>
    </div>
  </div>
</div>

<?= \App\Core\View::partial('booking-payment-modal') ?>

<style>
/* Modern Reference Calendar Card */
.refixel-calendar-card {
  background: #ffffff;
  border-radius: 26px;
  border: 1.5px solid #edf0f5;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
  padding: 22px 20px 22px;
  width: 100%;
  max-width: 330px;
  user-select: none;
}
.cal-top-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}
.cal-day-num {
  font-size: 38px;
  font-weight: 700;
  line-height: 1;
  color: #111827;
  letter-spacing: -0.5px;
}
.cal-month-name {
  font-size: 16px;
  font-weight: 500;
  color: #8a92a0;
  margin-top: 5px;
}
.cal-nav-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  cursor: pointer;
  transition: all 0.2s ease;
}
.cal-nav-btn:hover:not(:disabled) {
  background: #ff5238;
  color: #ffffff;
  border-color: #ff5238;
}
.cal-nav-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
  pointer-events: none;
}
.cal-icon-badge {
  width: 42px;
  height: 42px;
  background: #ffffff;
  border: 1.5px solid #eef2f6;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.cal-icon-badge .cal-badge-bar {
  background: #ff5238;
  height: 12px;
  width: 100%;
}
.cal-icon-badge .cal-badge-dots {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(5, 3px);
  grid-gap: 3px;
  align-content: center;
  justify-content: center;
  padding: 3px;
}
.cal-icon-badge .cal-badge-dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: #d1d5db;
}
.cal-icon-badge .cal-badge-dot.red {
  background: #ff5238;
}
.cal-accent-divider {
  display: flex;
  gap: 5px;
  margin-bottom: 16px;
}
.cal-accent-divider .bar-segment {
  height: 3px;
  border-radius: 3px;
  background: #ff5238;
}
.cal-accent-divider .bar-segment.muted {
  background: #eef2f6;
}
.cal-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 8px;
}
.cal-weekday {
  font-size: 13px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
}
.cal-days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  row-gap: 6px;
  column-gap: 2px;
  text-align: center;
}
.cal-day-cell {
  width: 34px;
  height: 34px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14.5px;
  font-weight: 600;
  color: #1f2937;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.cal-day-cell:hover:not(.disabled):not(.selected) {
  background: #fff0eb;
  color: #ff5238;
}
.cal-day-cell.selected {
  background: #ff5238 !important;
  color: #ffffff !important;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(255, 82, 56, 0.4);
  border-radius: 8px;
}
.cal-day-cell.today:not(.selected) {
  border: 1.5px solid #ff5238;
  color: #ff5238;
}
.cal-day-cell.disabled {
  color: #cbd5e1;
  cursor: not-allowed;
  opacity: 0.35;
  pointer-events: none;
}
.cal-day-cell.empty {
  cursor: default;
  pointer-events: none;
}

/* Date Input Trigger */
.cal-input-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}
.cal-input-trigger:hover {
  border-color: #ff5238;
}
.cal-input-trigger.active {
  border-color: #ff5238;
  box-shadow: 0 0 0 3px rgba(255, 82, 56, 0.15);
}
.cal-custom-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  cursor: pointer;
  width: 100%;
}
.cal-trigger-chevron {
  font-size: 12px;
  transition: transform 0.25s ease;
}
.cal-input-trigger.active .cal-trigger-chevron {
  transform: rotate(180deg);
  color: #ff5238 !important;
}

/* Calendar Popover Dropdown */
.calendar-dropdown-popover {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  z-index: 1050;
  display: none;
  filter: drop-shadow(0 14px 30px rgba(15, 23, 42, 0.15));
  width: 100%;
  max-width: 330px;
}
@media (max-width: 767px) {
  .calendar-dropdown-popover {
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
  }
  .refixel-calendar-card {
    width: 100% !important;
    max-width: 100% !important;
    padding: 16px 10px !important;
    border-radius: 18px !important;
    box-sizing: border-box !important;
  }
  .cal-top-header {
    margin-bottom: 10px !important;
  }
  .cal-day-num {
    font-size: 30px !important;
  }
  .cal-month-name {
    font-size: 13.5px !important;
  }
  .cal-nav-btn {
    width: 26px !important;
    height: 26px !important;
  }
  .cal-icon-badge {
    width: 36px !important;
    height: 36px !important;
  }
  .cal-weekdays {
    margin-bottom: 6px !important;
    width: 100% !important;
  }
  .cal-weekday {
    font-size: 11.5px !important;
  }
  .cal-days-grid {
    row-gap: 4px !important;
    column-gap: 1px !important;
    width: 100% !important;
  }
  .cal-day-cell {
    width: 30px !important;
    height: 30px !important;
    font-size: 12.5px !important;
  }
}
.calendar-dropdown-popover.show {
  display: block;
  animation: calDropdownFade 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes calDropdownFade {
  from {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Time Dropdown Popover */
.time-dropdown-popover {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 0;
  z-index: 1050;
  display: none;
  filter: drop-shadow(0 14px 30px rgba(15, 23, 42, 0.15));
}
.time-dropdown-popover.show {
  display: block;
  animation: calDropdownFade 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.time-dropdown-menu-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1.5px solid #f1f5f9;
  padding: 12px;
}
.time-option-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s ease;
  margin-bottom: 6px;
  border: 1.5px solid transparent;
  user-select: none;
}
.time-option-item:last-child {
  margin-bottom: 0;
}
.time-option-item:hover {
  background: #fff8f5;
  border-color: #ffe3d9;
}
.time-option-item.active {
  background: #fff5f2;
  border-color: #ffdacf;
  box-shadow: 0 2px 6px rgba(255, 82, 56, 0.1);
}
.time-opt-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all 0.2s ease;
}
.time-option-item:hover .time-opt-icon {
  background: #fff0eb;
  color: #ff5238;
}
.time-option-item.active .time-opt-icon {
  background: #ff5238;
  color: #ffffff;
}
.time-opt-title {
  font-size: 13.5px;
  color: #1e293b;
  line-height: 1.2;
}
.time-option-item.active .time-opt-title {
  color: #e0452d;
}
.time-opt-sub {
  font-size: 11px;
  color: #64748b;
}
.time-option-item.active .time-opt-sub {
  color: #e0452d;
  font-weight: 600;
}
.time-opt-check {
  color: #ff5238;
  font-size: 13px;
  display: none;
}
.time-option-item.active .time-opt-check {
  display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Service selection price update
  var serviceSelect = document.getElementById('serviceIdSelect');
  var summaryBase = document.getElementById('summaryBasePrice');
  var summaryTotal = document.getElementById('summaryTotal');

  if (serviceSelect) {
    serviceSelect.addEventListener('change', function() {
      var selected = this.options[this.selectedIndex];
      var price = selected.getAttribute('data-price') || 0;
      if (summaryBase) summaryBase.textContent = '₹' + Number(price).toLocaleString('en-IN');
      if (summaryTotal) summaryTotal.textContent = '₹' + Number(price).toLocaleString('en-IN');
    });
  }

  // Interactive Modern Calendar with Dropdown / Popover
  var datePickerContainer = document.getElementById('datePickerContainer');
  var dateInputTrigger = document.getElementById('dateInputTrigger');
  var displayDateInput = document.getElementById('displayDateInput');
  var calendarDropdown = document.getElementById('calendarDropdown');
  var preferredDateInput = document.getElementById('preferredDateInput');
  var preferredTimeInput = document.getElementById('preferredTimeInput');
  var calDayNumber = document.getElementById('calDayNumber');
  var calMonthName = document.getElementById('calMonthName');
  var calDaysGrid = document.getElementById('calDaysGrid');
  var calPrevMonthBtn = document.getElementById('calPrevMonthBtn');
  var calNextMonthBtn = document.getElementById('calNextMonthBtn');
  var calSummaryDisplay = document.getElementById('calSummaryDisplay');
  var timePickerContainer = document.getElementById('timePickerContainer');
  var timeInputTrigger = document.getElementById('timeInputTrigger');
  var displayTimeInput = document.getElementById('displayTimeInput');
  var timeDropdown = document.getElementById('timeDropdown');
  var timeOptionItems = document.querySelectorAll('.time-option-item');

  if (calDaysGrid && preferredDateInput && dateInputTrigger && calendarDropdown) {
    var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    var today = new Date();
    today.setHours(0, 0, 0, 0);

    // Initial selected date (auto-advance to tomorrow if all slots for today are already past +30m cutoff)
    var realNow = new Date();
    var currentCutoffMinutes = realNow.getHours() * 60 + realNow.getMinutes() + 30;
    var selectedDate = new Date(today.getTime());
    if (currentCutoffMinutes > 1080) {
      // 06:00 PM (1080 min) is the last slot start. Past 05:30 PM, today has 0 valid slots.
      selectedDate = new Date(today.getTime() + 86400000);
    }
    var viewYear = selectedDate.getFullYear();
    var viewMonth = selectedDate.getMonth();

    function formatYYYYMMDD(d) {
      var y = d.getFullYear();
      var m = String(d.getMonth() + 1).padStart(2, '0');
      var day = String(d.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + day;
    }

    function formatInputDate(d) {
      var day = String(d.getDate()).padStart(2, '0');
      var m = monthNames[d.getMonth()].substring(0, 3);
      return day + ' ' + m + ' ' + d.getFullYear();
    }

    function formatDisplayDate(d) {
      var isToday = (d.getFullYear() === today.getFullYear() && d.getMonth() === today.getMonth() && d.getDate() === today.getDate());
      var prefix = isToday ? 'Today, ' : (dayNames[d.getDay()] + ', ');
      return prefix + d.getDate() + ' ' + monthNames[d.getMonth()].substring(0, 3) + ' ' + d.getFullYear();
    }

    function updateSummary() {
      if (!calSummaryDisplay) return;
      var timeSlot = preferredTimeInput ? preferredTimeInput.value : '';
      if (!timeSlot) {
        calSummaryDisplay.textContent = formatDisplayDate(selectedDate) + ' (No slot selected)';
      } else {
        calSummaryDisplay.textContent = formatDisplayDate(selectedDate) + ' (' + timeSlot + ')';
      }
    }

    function refreshTimeSlots() {
      var isToday = (
        selectedDate.getFullYear() === today.getFullYear() &&
        selectedDate.getMonth() === today.getMonth() &&
        selectedDate.getDate() === today.getDate()
      );

      var liveNow = new Date();
      var cutoff = isToday ? (liveNow.getHours() * 60 + liveNow.getMinutes() + 30) : 0;
      var visibleOptions = [];

      timeOptionItems.forEach(function(opt) {
        var startMin = parseInt(opt.getAttribute('data-start-minutes') || '0', 10);
        if (isToday && startMin < cutoff) {
          opt.style.display = 'none';
          opt.classList.remove('active');
        } else {
          opt.style.display = 'flex';
          visibleOptions.push(opt);
        }
      });

      var noSlotsMsg = document.getElementById('timeSlotsNoAvailableMsg');
      if (visibleOptions.length === 0) {
        if (noSlotsMsg) noSlotsMsg.style.display = 'block';
        if (preferredTimeInput) preferredTimeInput.value = '';
        if (displayTimeInput) displayTimeInput.value = 'No slots available today - Pick date';
        if (calSummaryDisplay) {
          calSummaryDisplay.textContent = formatDisplayDate(selectedDate) + ' (No slots available - Please pick tomorrow)';
        }
      } else {
        if (noSlotsMsg) noSlotsMsg.style.display = 'none';
        var currentSlotVal = preferredTimeInput ? preferredTimeInput.value : '';
        var matchingVisible = null;
        for (var i = 0; i < visibleOptions.length; i++) {
          if (visibleOptions[i].getAttribute('data-slot') === currentSlotVal) {
            matchingVisible = visibleOptions[i];
            break;
          }
        }

        if (!matchingVisible) {
          timeOptionItems.forEach(function(b) { b.classList.remove('active'); });
          var firstOpt = visibleOptions[0];
          firstOpt.classList.add('active');
          var firstSlot = firstOpt.getAttribute('data-slot');
          if (preferredTimeInput) preferredTimeInput.value = firstSlot;
          if (displayTimeInput) displayTimeInput.value = firstSlot;
        } else {
          timeOptionItems.forEach(function(b) { b.classList.remove('active'); });
          matchingVisible.classList.add('active');
        }
        updateSummary();
      }
    }

    function toggleTimeDropdown(show) {
      if (!timeDropdown || !timeInputTrigger) return;
      if (typeof show === 'boolean') {
        if (show) {
          timeDropdown.classList.add('show');
          timeInputTrigger.classList.add('active');
          if (calendarDropdown && dateInputTrigger) {
            calendarDropdown.classList.remove('show');
            dateInputTrigger.classList.remove('active');
          }
        } else {
          timeDropdown.classList.remove('show');
          timeInputTrigger.classList.remove('active');
        }
      } else {
        var isOpen = timeDropdown.classList.contains('show');
        toggleTimeDropdown(!isOpen);
      }
    }

    function toggleDropdown(show) {
      if (typeof show === 'boolean') {
        if (show) {
          calendarDropdown.classList.add('show');
          dateInputTrigger.classList.add('active');
          toggleTimeDropdown(false);
        } else {
          calendarDropdown.classList.remove('show');
          dateInputTrigger.classList.remove('active');
        }
      } else {
        var isOpen = calendarDropdown.classList.contains('show');
        if (isOpen) {
          calendarDropdown.classList.remove('show');
          dateInputTrigger.classList.remove('active');
        } else {
          calendarDropdown.classList.add('show');
          dateInputTrigger.classList.add('active');
          toggleTimeDropdown(false);
        }
      }
    }

    // Trigger open/close when clicking date input box
    dateInputTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      toggleDropdown();
    });

    // Trigger open/close when clicking time input box
    if (timeInputTrigger) {
      timeInputTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleTimeDropdown();
      });
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
      if (datePickerContainer && !datePickerContainer.contains(e.target)) {
        toggleDropdown(false);
      }
      if (timePickerContainer && !timePickerContainer.contains(e.target)) {
        toggleTimeDropdown(false);
      }
    });

    function renderCalendar() {
      calDaysGrid.innerHTML = '';

      calDayNumber.textContent = selectedDate.getDate();
      calMonthName.textContent = monthNames[viewMonth];

      var isCurrentMonth = (viewYear === today.getFullYear() && viewMonth === today.getMonth());
      calPrevMonthBtn.disabled = isCurrentMonth;

      var firstDayOfMonth = new Date(viewYear, viewMonth, 1);
      // Monday = 0, Sunday = 6
      var startDayIndex = (firstDayOfMonth.getDay() + 6) % 7;
      var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

      // Blank cells before first day
      for (var b = 0; b < startDayIndex; b++) {
        var blank = document.createElement('div');
        blank.className = 'cal-day-cell empty';
        calDaysGrid.appendChild(blank);
      }

      // Days of month
      for (var d = 1; d <= daysInMonth; d++) {
        var cellDate = new Date(viewYear, viewMonth, d);
        cellDate.setHours(0, 0, 0, 0);

        var cell = document.createElement('div');
        cell.className = 'cal-day-cell';
        cell.textContent = d;

        var isPast = cellDate.getTime() < today.getTime();
        var isToday = cellDate.getTime() === today.getTime();
        var isSelected = (
          cellDate.getFullYear() === selectedDate.getFullYear() &&
          cellDate.getMonth() === selectedDate.getMonth() &&
          cellDate.getDate() === selectedDate.getDate()
        );

        if (isPast) {
          cell.classList.add('disabled');
        } else {
          if (isToday) cell.classList.add('today');
          if (isSelected) cell.classList.add('selected');

          (function(cDate, dayNum) {
            cell.addEventListener('click', function(ev) {
              ev.stopPropagation();
              selectedDate = new Date(cDate.getTime());
              preferredDateInput.value = formatYYYYMMDD(selectedDate);
              displayDateInput.value = formatInputDate(selectedDate);
              calDayNumber.textContent = dayNum;
              calMonthName.textContent = monthNames[selectedDate.getMonth()];
              renderCalendar();
              refreshTimeSlots();
              toggleDropdown(false);
            });
          })(cellDate, d);
        }

        calDaysGrid.appendChild(cell);
      }
    }

    if (calNextMonthBtn) {
      calNextMonthBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        viewMonth++;
        if (viewMonth > 11) {
          viewMonth = 0;
          viewYear++;
        }
        renderCalendar();
      });
    }

    if (calPrevMonthBtn) {
      calPrevMonthBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        var isCurrentMonth = (viewYear === today.getFullYear() && viewMonth === today.getMonth());
        if (isCurrentMonth) return;
        viewMonth--;
        if (viewMonth < 0) {
          viewMonth = 11;
          viewYear--;
        }
        renderCalendar();
      });
    }

    // Time Slot dropdown option clicks
    timeOptionItems.forEach(function(opt) {
      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        if (opt.style.display === 'none') return;
        timeOptionItems.forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        var slot = this.getAttribute('data-slot');
        if (displayTimeInput) displayTimeInput.value = slot;
        if (preferredTimeInput) preferredTimeInput.value = slot;
        updateSummary();
        toggleTimeDropdown(false);
      });
    });

    // Wire Pick Tomorrow button inside time slots dropdown
    var btnPickTomorrow = document.getElementById('btnPickTomorrowFromSlot');
    if (btnPickTomorrow) {
      btnPickTomorrow.addEventListener('click', function(e) {
        e.stopPropagation();
        selectedDate = new Date(today.getTime() + 86400000);
        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth();
        preferredDateInput.value = formatYYYYMMDD(selectedDate);
        displayDateInput.value = formatInputDate(selectedDate);
        renderCalendar();
        refreshTimeSlots();
      });
    }

    // Initial setup
    preferredDateInput.value = formatYYYYMMDD(selectedDate);
    displayDateInput.value = formatInputDate(selectedDate);
    renderCalendar();
    refreshTimeSlots();
  }

  /* ══════════════════════════════════════════════════════════════════
     AUTO-FILL & "USE MY CURRENT LOCATION" LOGIC
     ══════════════════════════════════════════════════════════════════ */
  window.CUSTOMER_SAVED_PROFILE = <?= json_encode([
      'name'         => $user['name'] ?? '',
      'phone'        => $user['phone'] ?? '',
      'email'        => $user['email'] ?? '',
      'address_type' => $customerProfile['address_type'] ?? 'Home',
      'house_no'     => $customerProfile['house_no'] ?? '',
      'street'       => $customerProfile['street'] ?? '',
      'city'         => $customerProfile['city'] ?? $currentCity,
      'state'        => $customerProfile['state'] ?? (($currentCity === 'Thakurdwara') ? 'Uttar Pradesh' : 'Uttarakhand'),
      'pincode'      => $customerProfile['pincode'] ?? '',
      'address'      => $customerProfile['address'] ?? '',
  ]) ?>;

  window.CUSTOMER_SAVED_ADDRESSES = <?= json_encode($savedAddresses ?? []) ?>;

  var btnUseCurrentLoc     = document.getElementById('btnUseCurrentLocation');
  var btnAddNewLoc         = document.getElementById('btnAddNewLocation');
  var btnToggleAddress     = document.getElementById('btnToggleAddressDetails');
  var btnSaveAddress       = document.getElementById('btnSaveAddress');
  var addressDetailedBox   = document.getElementById('addressDetailedFields');
  var savedAddressBox      = document.getElementById('savedAddressSummaryBox');
  var toggleAddressText    = document.getElementById('toggleAddressBtnText');
  var toggleAddressIcon    = document.getElementById('toggleAddressBtnIcon');
  var bookingLocStatus     = document.getElementById('bookingLocStatus');
  var bookingAutoFillBadge = document.getElementById('bookingAutoFillBadge');
  var isNewLocationMode    = false;

  function updateAddressSummaryDisplay() {
    var house = inputHouseNo ? inputHouseNo.value.trim() : '';
    var street = inputStreet ? inputStreet.value.trim() : '';
    var city = inputCity ? inputCity.value.trim() : '';
    var state = inputState ? inputState.value.trim() : '';
    var pin = inputPincode ? inputPincode.value.trim() : '';
    var landmark = inputAddress ? inputAddress.value.trim() : '';
    var typeRadio = document.querySelector('input[name="address_type"]:checked');
    var type = typeRadio ? typeRadio.value : 'Home';

    var parts = [];
    if (house) parts.push(house);
    if (street) parts.push(street);
    if (city) parts.push(city);
    if (state) parts.push(state);
    var line = parts.join(', ') + (pin ? ' - ' + pin : '');

    var sumEl = document.getElementById('savedAddressSummaryText');
    if (sumEl && line) sumEl.textContent = line;

    var typeBadge = document.getElementById('savedAddressTypeBadge');
    if (typeBadge) typeBadge.textContent = type;

    var landBox = document.getElementById('savedAddressLandmarkText');
    var landSpan = document.getElementById('savedAddressLandmarkSpan');
    if (landBox && landSpan) {
      if (landmark) {
        landSpan.textContent = landmark;
        landBox.style.display = 'block';
      } else {
        landBox.style.display = 'none';
      }
    }
  }

  // ══════════════════════════════════════════════════════════════════
  // MULTI-ADDRESS REGISTRY & "CHOOSE SAVED ADDRESS" PICKER
  // ══════════════════════════════════════════════════════════════════
  var isCustomerLoggedIn = !!(window.REFIXEL_AUTH && window.REFIXEL_AUTH.isLoggedIn);
  var currentUserId = isCustomerLoggedIn ? window.REFIXEL_AUTH.userId : null;
  var userStorageKey = isCustomerLoggedIn ? ('refixel_saved_addresses_u' + currentUserId) : 'refixel_guest_saved_addresses';

  // For logged-in users, savedAddresses is strictly authoritative from their own server/database account
  var savedAddresses = Array.isArray(window.CUSTOMER_SAVED_ADDRESSES) ? window.CUSTOMER_SAVED_ADDRESSES.slice() : [];

  // Remove legacy shared unscoped key so old users' addresses never pollute the active session
  try {
    localStorage.removeItem('refixel_saved_addresses');
  } catch(e) {}

  // Only merge guest-scoped localStorage for guest users (NEVER for authenticated users)
  if (!isCustomerLoggedIn) {
    try {
      var localAddresses = JSON.parse(localStorage.getItem(userStorageKey) || '[]');
      if (Array.isArray(localAddresses)) {
        localAddresses.forEach(function(la) {
          var exists = savedAddresses.some(function(sa) {
            return (sa.street && sa.street.toLowerCase() === (la.street || '').toLowerCase()) &&
                   (sa.house_no && sa.house_no.toLowerCase() === (la.house_no || '').toLowerCase());
          });
          if (!exists && (la.street || la.address)) {
            savedAddresses.push(la);
          }
        });
      }
    } catch(e) {}
  }

  function syncSavedAddressesBadge() {
    var count = savedAddresses.length;
    var badge = document.getElementById('savedAddressesCountBadge');
    if (badge) badge.textContent = count;
  }
  syncSavedAddressesBadge();

  function triggerNewAddressEntry() {
    isNewLocationMode = true;
    if (btnAddNewLoc) {
      btnAddNewLoc.innerHTML = '<i class="fa fa-undo"></i> <span>Use Saved Address</span>';
      btnAddNewLoc.style.background = '#fef2f2';
      btnAddNewLoc.style.color = '#dc2626';
      btnAddNewLoc.style.borderColor = '#fecaca';
    }

    if (inputHouseNo) { inputHouseNo.value = ''; inputHouseNo.placeholder = 'e.g. Flat 604 / House No.'; }
    if (inputStreet) { inputStreet.value = ''; inputStreet.placeholder = 'e.g. Colony, Street, Apartment'; }
    if (inputAddress) { inputAddress.value = ''; inputAddress.placeholder = 'Any extra landmark details'; }
    if (inputPincode) { inputPincode.value = ''; inputPincode.placeholder = '6-digit PIN code'; }

    var radioOther = document.querySelector('input[name="address_type"][value="Other"]');
    if (radioOther) radioOther.checked = true;

    if (addressDetailedBox) addressDetailedBox.style.display = 'block';
    if (toggleAddressText) toggleAddressText.textContent = 'Hide Details';
    if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-up';
    if (savedAddressBox) savedAddressBox.style.display = 'none';

    if (btnUseCurrentLoc) {
      btnUseCurrentLoc.innerHTML = '<i class="fa fa-crosshairs"></i> <span>Detect Location</span>';
      btnUseCurrentLoc.style.background = '#fff3ec';
      btnUseCurrentLoc.style.borderColor = '#ffdacf';
      btnUseCurrentLoc.style.color = '#f25b29';
      btnUseCurrentLoc.disabled = false;
    }

    if (bookingAutoFillBadge) {
      bookingAutoFillBadge.innerHTML = '<i class="fa fa-plus-circle mr-1"></i> Entering New Location';
      bookingAutoFillBadge.style.background = '#eff6ff';
      bookingAutoFillBadge.style.color = '#2563eb';
      bookingAutoFillBadge.style.borderColor = '#bfdbfe';
      bookingAutoFillBadge.style.display = 'inline-block';
    }
    if (bookingLocStatus) {
      bookingLocStatus.style.display = 'block';
      bookingLocStatus.innerHTML = '<span style="color: #2563eb;"><i class="fa fa-map-pin mr-1"></i> Enter your new address details below or click Use Current Location</span>';
    }

    if (inputHouseNo) {
      inputHouseNo.focus();
    }
  }

  function renderSavedAddressesModal() {
    var container = document.getElementById('savedAddressesList');
    if (!container) return;

    if (!savedAddresses || savedAddresses.length === 0) {
      container.innerHTML = `
        <div class="text-center py-4">
          <div class="mb-2" style="font-size: 28px; color: #94a3b8;"><i class="fa fa-map-marker"></i></div>
          <p class="text-muted small mb-3">No saved addresses found.</p>
          <button type="button" class="btn btn-sm btn-primary-custom" id="modalEmptyAddBtn">
            <i class="fa fa-plus-circle mr-1"></i> Add New Address
          </button>
        </div>
      `;
      var emptyAddBtn = document.getElementById('modalEmptyAddBtn');
      if (emptyAddBtn) {
        emptyAddBtn.addEventListener('click', function() {
          $('#savedAddressesModal').modal('hide');
          triggerNewAddressEntry();
        });
      }
      return;
    }

    var currStreet = inputStreet ? inputStreet.value.trim().toLowerCase() : '';
    var currHouse = inputHouseNo ? inputHouseNo.value.trim().toLowerCase() : '';

    var html = '';
    savedAddresses.forEach(function(addr, idx) {
      var addrStreet = (addr.street || '').trim().toLowerCase();
      var addrHouse = (addr.house_no || '').trim().toLowerCase();
      var isCurrent = (currStreet && addrStreet === currStreet && (!currHouse || addrHouse === currHouse));

      var type = (addr.address_type || addr.label || 'Home');

      // Compose clean single-line / plain text full address - NO small text
      var addressParts = [
        addr.house_no,
        addr.street,
        addr.city,
        addr.state,
        addr.pincode
      ].filter(Boolean);
      var plainAddress = addressParts.join(', ');
      if (!plainAddress && addr.address) {
        plainAddress = addr.address;
      }

      html += `
        <div class="saved-address-plain-item p-2 px-3 select-saved-addr-btn" data-index="${idx}" style="cursor: pointer; border: 1.5px solid ${isCurrent ? '#f25b29' : '#e2e8f0'}; background: ${isCurrent ? '#fff8f5' : '#ffffff'}; border-radius: 8px; transition: all 0.15s ease; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
          <div class="d-flex align-items-center" style="gap: 10px; flex: 1; min-width: 0;">
            <span class="badge" style="background: ${isCurrent ? '#ffede3' : '#f1f5f9'}; color: ${isCurrent ? '#f25b29' : '#475569'}; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; flex-shrink: 0;">
              ${type}
            </span>
            <div style="color: #1e293b; font-size: 13.5px; line-height: 1.4; font-weight: 600; word-break: break-word;">
              ${plainAddress}
            </div>
          </div>
          <div class="d-flex align-items-center" style="gap: 8px; flex-shrink: 0;">
            <button type="button" class="btn btn-sm btn-delete-saved-addr" data-index="${idx}" title="Delete Address" style="width: 34px; height: 34px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0; cursor: pointer; transition: all 0.15s ease;">
              <i class="fa fa-trash-o" style="font-size: 15px;"></i>
            </button>
            <button type="button" class="btn btn-sm btn-select-saved-addr-icon" data-index="${idx}" title="${isCurrent ? 'Active Address' : 'Select Address'}" style="width: 34px; height: 34px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: ${isCurrent ? '#059669' : '#ffffff'}; color: ${isCurrent ? '#ffffff' : '#f25b29'}; border: 1.5px solid ${isCurrent ? '#059669' : '#f25b29'}; padding: 0; cursor: pointer; transition: all 0.15s ease;">
              <i class="fa fa-check" style="font-size: 14px;"></i>
            </button>
          </div>
        </div>
      `;
    });

    container.innerHTML = html;

    // Attach click listeners to select address (row click or select icon click)
    container.querySelectorAll('.select-saved-addr-btn').forEach(function(row) {
      row.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-saved-addr')) return;
        var idx = parseInt(this.getAttribute('data-index'), 10);
        if (!isNaN(idx) && savedAddresses[idx]) {
          applySelectedSavedAddress(savedAddresses[idx]);
          $('#savedAddressesModal').modal('hide');
        }
      });
      row.addEventListener('mouseenter', function() {
        if (!this.querySelector('.btn-select-saved-addr-icon[style*="#059669"]')) {
          this.style.borderColor = '#cbd5e1';
          this.style.backgroundColor = '#f8fafc';
        }
      });
      row.addEventListener('mouseleave', function() {
        var isCurr = this.querySelector('.btn-select-saved-addr-icon[style*="#059669"]');
        this.style.borderColor = isCurr ? '#f25b29' : '#e2e8f0';
        this.style.backgroundColor = isCurr ? '#fff8f5' : '#ffffff';
      });
    });

    // Attach click listeners to delete address icon button
    container.querySelectorAll('.btn-delete-saved-addr').forEach(function(delBtn) {
      delBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var idx = parseInt(this.getAttribute('data-index'), 10);
        if (isNaN(idx) || !savedAddresses[idx]) return;
        if (!confirm('Are you sure you want to delete this saved address?')) return;

        var targetAddr = savedAddresses[idx];
        savedAddresses.splice(idx, 1);
        try {
          localStorage.setItem(userStorageKey, JSON.stringify(savedAddresses));
        } catch(err) {}

        fetch((window.REFIXEL_BASE_URL || '') + '/api/customer/delete-address', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: targetAddr.id, street: targetAddr.street })
        }).catch(function(){});

        syncSavedAddressesBadge();
        renderSavedAddressesModal();

        var currStreet = inputStreet ? inputStreet.value.trim().toLowerCase() : '';
        if (targetAddr.street && targetAddr.street.trim().toLowerCase() === currStreet) {
          if (savedAddresses.length > 0) {
            applySelectedSavedAddress(savedAddresses[0]);
          } else {
            if (inputStreet) inputStreet.value = '';
            if (inputHouseNo) inputHouseNo.value = '';
            if (inputAddress) inputAddress.value = '';
            var sumText = document.getElementById('savedAddressSummaryText');
            if (sumText) sumText.textContent = 'No saved address selected';
            updateAddressSummaryDisplay();
          }
        }
      });
    });
  }

  function applySelectedSavedAddress(addr) {
    if (!addr) return;
    if (inputHouseNo) inputHouseNo.value = addr.house_no || '';
    if (inputStreet) inputStreet.value = addr.street || '';
    if (inputCity) inputCity.value = addr.city || '<?= addslashes($currentCity) ?>';
    if (inputState) inputState.value = addr.state || 'Uttarakhand';
    if (inputPincode) inputPincode.value = addr.pincode || '';
    if (inputAddress) inputAddress.value = addr.address || '';

    var targetType = addr.address_type || addr.label || 'Home';
    var radioToSelect = document.querySelector('input[name="address_type"][value="' + targetType + '"]');
    if (radioToSelect) {
      radioToSelect.checked = true;
    } else {
      var radioOther = document.querySelector('input[name="address_type"][value="Other"]');
      if (radioOther) radioOther.checked = true;
    }

    var subtext = document.getElementById('activeAddressSubtext');
    if (subtext) {
      subtext.textContent = 'Active: ' + targetType + ' - ' + (addr.street || addr.city);
    }

    if (bookingAutoFillBadge) {
      bookingAutoFillBadge.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Selected: ' + targetType + ' Address';
      bookingAutoFillBadge.style.display = 'inline-block';
    }

    if (savedAddressBox) savedAddressBox.style.display = 'block';
    if (addressDetailedBox) addressDetailedBox.style.display = 'none';
    if (toggleAddressText) toggleAddressText.textContent = 'Change / Edit Address';
    if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-down';

    updateAddressSummaryDisplay();
    queueAddressPersist();
  }

  // Open Saved Address Modal
  var btnOpenAddrPicker = document.getElementById('btnOpenAddressPicker');
  if (btnOpenAddrPicker) {
    btnOpenAddrPicker.addEventListener('click', function(e) {
      e.preventDefault();
      renderSavedAddressesModal();
      $('#savedAddressesModal').modal('show');
    });
  }

  var btnAddFromModal = document.getElementById('btnAddNewAddressFromModal');
  if (btnAddFromModal) {
    btnAddFromModal.addEventListener('click', function() {
      $('#savedAddressesModal').modal('hide');
      triggerNewAddressEntry();
    });
  }

  // When Address Type radio (Home, Office, Other) is clicked, check if we have an existing address for that type
  document.querySelectorAll('input[name="address_type"]').forEach(function(r) {
    r.addEventListener('change', function() {
      if (isNewLocationMode) return;
      var chosenType = this.value;
      var match = savedAddresses.find(function(sa) {
        return (sa.address_type && sa.address_type.toLowerCase() === chosenType.toLowerCase()) ||
               (sa.label && sa.label.toLowerCase() === chosenType.toLowerCase());
      });
      if (match && (!inputStreet.value.trim() || inputStreet.value.trim() !== match.street)) {
        applySelectedSavedAddress(match);
      }
    });
  });

  var inputHouseNo = document.getElementById('inputHouseNo');
  var inputStreet  = document.getElementById('inputStreet');
  var inputCity    = document.getElementById('inputCity');
  var inputState   = document.getElementById('inputState');
  var inputPincode = document.getElementById('inputPincode');
  var inputAddress = document.getElementById('inputAddress');

  var inputName = document.querySelector('input[name="name"]');
  var inputPhone = document.querySelector('input[name="phone"]');
  var inputEmail = document.querySelector('input[name="email"]');

  function flashHighlight(el) {
    if (!el) return;
    el.classList.add('is-valid');
    el.style.transition = 'background-color 0.4s ease, border-color 0.4s ease';
    el.style.backgroundColor = '#f0fdf4';
    el.style.borderColor = '#86efac';
    setTimeout(function() {
      el.style.backgroundColor = '';
      el.style.borderColor = '';
    }, 1500);
  }

  // Auto-Save Address & Contact info into localStorage and Customer Account Profile
  var saveAddressTimer = null;
  function persistCurrentAddress() {
    var houseVal = inputHouseNo ? inputHouseNo.value.trim() : '';
    var streetVal = inputStreet ? inputStreet.value.trim() : '';
    var cityVal = inputCity ? inputCity.value.trim() : '';
    var stateVal = inputState ? inputState.value.trim() : '';
    var pinVal = inputPincode ? inputPincode.value.trim() : '';
    var addrVal = inputAddress ? inputAddress.value.trim() : '';
    var typeRadio = document.querySelector('input[name="address_type"]:checked');
    var typeVal = typeRadio ? typeRadio.value : 'Home';

    var nameVal = inputName ? inputName.value.trim() : '';
    var emailVal = inputEmail ? inputEmail.value.trim() : '';
    var phoneVal = inputPhone ? inputPhone.value.trim() : '';

    var fullLoc = {
      houseNo: houseVal,
      street: streetVal,
      city: cityVal,
      state: stateVal,
      pincode: pinVal,
      fullAddress: addrVal || [streetVal, cityVal, stateVal, pinVal].filter(Boolean).join(', '),
      addressType: typeVal,
      name: nameVal,
      email: emailVal,
      phone: phoneVal,
      timestamp: Date.now()
    };

    try {
      localStorage.setItem('refixel_user_location', JSON.stringify(fullLoc));
      localStorage.setItem('refixel_saved_contact', JSON.stringify({
        name: nameVal,
        phone: phoneVal,
        email: emailVal
      }));
    } catch (e) {}

    if (window.REFIXEL_AUTH && window.REFIXEL_AUTH.isLoggedIn) {
      var apiEndpoint = (window.REFIXEL_BASE_URL || '') + '/api/customer/save-address';
      fetch(apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          house_no: houseVal,
          street: streetVal,
          city: cityVal,
          state: stateVal,
          pincode: pinVal,
          address: addrVal,
          address_type: typeVal,
          name: nameVal,
          email: emailVal
        })
      }).catch(function(err) {
        console.warn('Auto-save address error:', err);
      });
    }
    updateAddressSummaryDisplay();
  }

  function queueAddressPersist() {
    clearTimeout(saveAddressTimer);
    saveAddressTimer = setTimeout(persistCurrentAddress, 400);
  }

  // Attach live auto-save listeners to address & contact fields
  [inputHouseNo, inputStreet, inputCity, inputState, inputPincode, inputAddress].forEach(function(inp) {
    if (inp) {
      inp.addEventListener('input', queueAddressPersist);
      inp.addEventListener('change', queueAddressPersist);
    }
  });

  document.querySelectorAll('input[name="address_type"]').forEach(function(r) {
    r.addEventListener('change', queueAddressPersist);
  });

  if (inputName) {
    inputName.addEventListener('input', queueAddressPersist);
    inputName.addEventListener('change', queueAddressPersist);
  }
  if (inputEmail) {
    inputEmail.addEventListener('input', queueAddressPersist);
    inputEmail.addEventListener('change', queueAddressPersist);
  }
  if (inputPhone) {
    inputPhone.addEventListener('input', queueAddressPersist);
    inputPhone.addEventListener('change', queueAddressPersist);
  }

  function applyLocationData(loc, sourceBadge) {
    if (!loc) return;
    var filledCount = 0;

    if (inputCity && loc.city) {
      inputCity.value = loc.city;
      flashHighlight(inputCity);
      filledCount++;
    }
    if (inputState && loc.state) {
      inputState.value = loc.state;
      flashHighlight(inputState);
      filledCount++;
    }
    if (inputStreet) {
      var resolvedStreet = (loc.street || '').trim();
      var cityName = (loc.city || '').trim().toLowerCase();
      var districtName = (loc.district || '').trim().toLowerCase();
      var stateName = (loc.state || '').trim().toLowerCase();
      var countryName = (loc.country || '').trim().toLowerCase();

      function isInvalidStreet(str) {
        if (!str) return true;
        var s = str.trim().toLowerCase();
        if (s === cityName || s === districtName || s === stateName || s === countryName) return true;
        if (districtName && (s === districtName + ' area' || s.indexOf(districtName) !== -1)) return true;
        if (s === 'doorstep service area' || s === 'india' || /^\d+$/.test(s)) return true;
        return false;
      }

      if (isInvalidStreet(resolvedStreet)) {
        resolvedStreet = '';
      }

      // If no valid street, fallback to area or fullAddress tokens
      if (!resolvedStreet) {
        if (loc.area && !isInvalidStreet(loc.area)) {
          resolvedStreet = loc.area.trim();
        } else if (loc.fullAddress) {
          var parts = loc.fullAddress.split(',').map(function(s) { return s.trim(); });
          for (var p = 0; p < parts.length; p++) {
            if (!isInvalidStreet(parts[p])) {
              resolvedStreet = parts[p];
              break;
            }
          }
        }
      }

      if (resolvedStreet) {
        inputStreet.value = resolvedStreet;
        flashHighlight(inputStreet);
        filledCount++;
      } else {
        if (isInvalidStreet(inputStreet.value)) {
          inputStreet.value = '';
          inputStreet.placeholder = 'e.g. Colony, Street, Apartment';
        }
      }
    }
    if (inputHouseNo) {
      var resolvedHouseNo = (loc.houseNo || '').trim();
      if (!resolvedHouseNo || resolvedHouseNo === 'Doorstep Visit') {
        resolvedHouseNo = inputHouseNo.value.trim() || '';
      }
      inputHouseNo.value = resolvedHouseNo;
      flashHighlight(inputHouseNo);
      filledCount++;
    }
    if (loc.addressType) {
      var radioToSelect = document.querySelector('input[name="address_type"][value="' + loc.addressType + '"]');
      if (radioToSelect) {
        radioToSelect.checked = true;
        filledCount++;
      }
    }
    if (inputPincode && loc.pincode && loc.pincode.toString().trim()) {
      inputPincode.value = loc.pincode.toString().trim();
      flashHighlight(inputPincode);
      filledCount++;
    }
    if (inputAddress && loc.fullAddress && loc.fullAddress.toLowerCase() !== (loc.city || '').toLowerCase()) {
      inputAddress.value = loc.fullAddress;
      flashHighlight(inputAddress);
      filledCount++;
    }

    if (filledCount > 0) {
      if (bookingAutoFillBadge) {
        bookingAutoFillBadge.innerHTML = '<i class="fa fa-check-circle mr-1"></i> ' + (sourceBadge || 'Location Detected');
        bookingAutoFillBadge.style.display = 'inline-block';
      }
      if (btnUseCurrentLoc) {
        btnUseCurrentLoc.innerHTML = '<i class="fa fa-check text-success"></i> <span>Location Applied</span>';
        btnUseCurrentLoc.style.background = '#ecfdf5';
        btnUseCurrentLoc.style.borderColor = '#a7f3d0';
        btnUseCurrentLoc.style.color = '#059669';
      }
      if (bookingLocStatus) {
        bookingLocStatus.style.display = 'block';
        var locDisplay = [loc.city, loc.state, loc.country].filter(Boolean).join(', ') || loc.fullAddress || 'Location Applied';
        bookingLocStatus.innerHTML = '<span style="color: #059669;"><i class="fa fa-map-marker text-success mr-1"></i> Detected: <strong>' + locDisplay + (loc.pincode ? ' (' + loc.pincode + ')' : '') + '</strong></span>';
      }
      if (btnAddNewLoc && isNewLocationMode) {
        isNewLocationMode = false;
        btnAddNewLoc.innerHTML = '<i class="fa fa-plus-circle"></i> <span>+ Add New Location</span>';
        btnAddNewLoc.style.background = '#eff6ff';
        btnAddNewLoc.style.color = '#1d4ed8';
        btnAddNewLoc.style.borderColor = '#bfdbfe';
      }
      updateAddressSummaryDisplay();
      if (savedAddressBox) savedAddressBox.style.display = 'block';
      queueAddressPersist();
    }
  }

  // 1. AUTO-FILL ON PAGE LOAD: Saved Customer Profile or LocalStorage
  var profile = window.CUSTOMER_SAVED_PROFILE || null;
  var hasProfile = profile && (profile.street || profile.address);
  if (hasProfile) {
    var houseVal = (profile.house_no || '').trim() || (inputHouseNo ? inputHouseNo.value.trim() : '');
    if (inputHouseNo) inputHouseNo.value = houseVal;
    if (inputStreet && !inputStreet.value.trim() && profile.street) inputStreet.value = profile.street;
    if (inputCity && !inputCity.value.trim() && profile.city) inputCity.value = profile.city;
    if (inputState && !inputState.value.trim() && profile.state) inputState.value = profile.state;
    if (inputPincode && !inputPincode.value.trim() && profile.pincode) inputPincode.value = profile.pincode;
    if (inputAddress && !inputAddress.value.trim() && profile.address) inputAddress.value = profile.address;
    if (profile.address_type) {
      var radioToSelect = document.querySelector('input[name="address_type"][value="' + profile.address_type + '"]');
      if (radioToSelect) radioToSelect.checked = true;
    }

    try {
      if (isCustomerLoggedIn) {
        localStorage.setItem('refixel_user_location_u' + currentUserId, JSON.stringify({
          houseNo: inputHouseNo ? inputHouseNo.value : '',
          street: inputStreet ? inputStreet.value : '',
          city: inputCity ? inputCity.value : '',
          state: inputState ? inputState.value : '',
          pincode: inputPincode ? inputPincode.value : '',
          fullAddress: inputAddress ? inputAddress.value : '',
          addressType: profile.address_type || 'Home',
          timestamp: Date.now()
        }));
      }
    } catch (e) {}

    if (bookingAutoFillBadge) {
      bookingAutoFillBadge.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Auto-filled from Saved Profile';
      bookingAutoFillBadge.style.display = 'inline-block';
    }
    if (btnUseCurrentLoc) {
      btnUseCurrentLoc.innerHTML = '<i class="fa fa-check text-success"></i> <span>Location Applied</span>';
      btnUseCurrentLoc.style.background = '#ecfdf5';
      btnUseCurrentLoc.style.borderColor = '#a7f3d0';
      btnUseCurrentLoc.style.color = '#059669';
    }
    if (bookingLocStatus) {
      bookingLocStatus.style.display = 'block';
      var locDisplay = [inputCity ? inputCity.value : '', inputState ? inputState.value : ''].filter(Boolean).join(', ');
      bookingLocStatus.innerHTML = '<span style="color: #059669;"><i class="fa fa-map-marker text-success mr-1"></i> Saved Profile: <strong>' + (locDisplay || 'Location Applied') + (inputPincode && inputPincode.value ? ' (' + inputPincode.value + ')' : '') + '</strong></span>';
    }
    updateAddressSummaryDisplay();
    if (savedAddressBox) savedAddressBox.style.display = 'block';
  } else {
    // For logged-in users without a saved profile, do NOT auto-fill stale localStorage from previous users!
    if (!isCustomerLoggedIn) {
      try {
        var rawLoc = localStorage.getItem('refixel_user_location');
        if (rawLoc) {
          var savedLoc = JSON.parse(rawLoc);
          if (savedLoc && (savedLoc.fullAddress || savedLoc.city || savedLoc.street || savedLoc.houseNo)) {
            var streetEmpty = (!inputStreet || !inputStreet.value.trim());
            if (streetEmpty) {
              applyLocationData(savedLoc, 'Auto-filled from Saved Location');
            }
          }
        }
        var rawContact = localStorage.getItem('refixel_saved_contact');
        if (rawContact) {
          var savedContact = JSON.parse(rawContact);
          if (savedContact) {
            if (inputName && !inputName.value.trim() && savedContact.name) inputName.value = savedContact.name;
            if (inputEmail && !inputEmail.value.trim() && savedContact.email) inputEmail.value = savedContact.email;
            if (inputPhone && !inputPhone.value.trim() && savedContact.phone) inputPhone.value = savedContact.phone;
          }
        }
      } catch (e) {
        console.warn('Error reading saved location:', e);
      }
    }
  }

  // Handle "+ Add New Location" / "Use Saved Address"
  if (btnAddNewLoc) {
    btnAddNewLoc.addEventListener('click', function() {
      if (!isNewLocationMode) {
        isNewLocationMode = true;
        btnAddNewLoc.innerHTML = '<i class="fa fa-undo"></i> <span>Use Saved Address</span>';
        btnAddNewLoc.style.background = '#fef2f2';
        btnAddNewLoc.style.color = '#dc2626';
        btnAddNewLoc.style.borderColor = '#fecaca';

        // Clear current address inputs for clean entry
        if (inputHouseNo) { inputHouseNo.value = ''; inputHouseNo.placeholder = 'e.g. Flat 604 / House No.'; }
        if (inputStreet) { inputStreet.value = ''; inputStreet.placeholder = 'e.g. Colony, Street, Apartment'; }
        if (inputAddress) { inputAddress.value = ''; inputAddress.placeholder = 'Any extra landmark details'; }
        if (inputPincode) { inputPincode.value = ''; inputPincode.placeholder = '6-digit PIN code'; }

        var radioOther = document.querySelector('input[name="address_type"][value="Other"]');
        if (radioOther) radioOther.checked = true;

        if (addressDetailedBox) addressDetailedBox.style.display = 'block';
        if (toggleAddressText) toggleAddressText.textContent = 'Hide Details';
        if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-up';
        if (savedAddressBox) savedAddressBox.style.display = 'none';

        if (btnUseCurrentLoc) {
          btnUseCurrentLoc.innerHTML = '<i class="fa fa-crosshairs"></i> <span>Detect Location</span>';
          btnUseCurrentLoc.style.background = '#fff3ec';
          btnUseCurrentLoc.style.borderColor = '#ffdacf';
          btnUseCurrentLoc.style.color = '#f25b29';
          btnUseCurrentLoc.disabled = false;
        }

        if (bookingAutoFillBadge) {
          bookingAutoFillBadge.innerHTML = '<i class="fa fa-plus-circle mr-1"></i> Entering New Location';
          bookingAutoFillBadge.style.background = '#eff6ff';
          bookingAutoFillBadge.style.color = '#2563eb';
          bookingAutoFillBadge.style.borderColor = '#bfdbfe';
          bookingAutoFillBadge.style.display = 'inline-block';
        }
        if (bookingLocStatus) {
          bookingLocStatus.style.display = 'block';
          bookingLocStatus.innerHTML = '<span style="color: #2563eb;"><i class="fa fa-map-pin mr-1"></i> Enter your new address details below or click Use Current Location</span>';
        }

        if (inputHouseNo) {
          inputHouseNo.focus();
        }
      } else {
        isNewLocationMode = false;
        btnAddNewLoc.innerHTML = '<i class="fa fa-plus-circle"></i> <span>+ Add New Location</span>';
        btnAddNewLoc.style.background = '#eff6ff';
        btnAddNewLoc.style.color = '#1d4ed8';
        btnAddNewLoc.style.borderColor = '#bfdbfe';

        var p = window.CUSTOMER_SAVED_PROFILE || null;
        if (p) {
          if (inputHouseNo) inputHouseNo.value = p.house_no || '';
          if (inputStreet) inputStreet.value = p.street || '';
          if (inputCity) inputCity.value = p.city || '';
          if (inputState) inputState.value = p.state || '';
          if (inputPincode) inputPincode.value = p.pincode || '';
          if (inputAddress) inputAddress.value = p.address || '';
          if (p.address_type) {
            var r = document.querySelector('input[name="address_type"][value="' + p.address_type + '"]');
            if (r) r.checked = true;
          }
          if (bookingAutoFillBadge) {
            bookingAutoFillBadge.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Auto-filled from Saved Profile';
            bookingAutoFillBadge.style.background = '#ecfdf5';
            bookingAutoFillBadge.style.color = '#059669';
            bookingAutoFillBadge.style.borderColor = '#a7f3d0';
            bookingAutoFillBadge.style.display = 'inline-block';
          }
          if (btnUseCurrentLoc) {
            btnUseCurrentLoc.innerHTML = '<i class="fa fa-check text-success"></i> <span>Location Applied</span>';
            btnUseCurrentLoc.style.background = '#ecfdf5';
            btnUseCurrentLoc.style.borderColor = '#a7f3d0';
            btnUseCurrentLoc.style.color = '#059669';
          }
          if (bookingLocStatus) {
            var locDisp = [inputCity ? inputCity.value : '', inputState ? inputState.value : ''].filter(Boolean).join(', ');
            bookingLocStatus.innerHTML = '<span style="color: #059669;"><i class="fa fa-map-marker text-success mr-1"></i> Saved Profile: <strong>' + (locDisp || 'Location Applied') + (inputPincode && inputPincode.value ? ' (' + inputPincode.value + ')' : '') + '</strong></span>';
          }
          if (addressDetailedBox) addressDetailedBox.style.display = 'none';
          if (toggleAddressText) toggleAddressText.textContent = 'Change / Edit Address';
          if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-down';
          if (savedAddressBox) savedAddressBox.style.display = 'block';
          updateAddressSummaryDisplay();
          queueAddressPersist();
        }
      }
    });
  }

  // Handle Dropdown Toggle for Detailed Address Fields
  if (btnToggleAddress && addressDetailedBox) {
    btnToggleAddress.addEventListener('click', function() {
      var isHidden = (addressDetailedBox.style.display === 'none');
      if (isHidden) {
        addressDetailedBox.style.display = 'block';
        if (toggleAddressText) toggleAddressText.textContent = 'Hide Details';
        if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-up';
        btnToggleAddress.style.background = '#f1f5f9';
      } else {
        addressDetailedBox.style.display = 'none';
        if (toggleAddressText) toggleAddressText.textContent = 'Change / Edit Address';
        if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-down';
        btnToggleAddress.style.background = '#ffffff';
        updateAddressSummaryDisplay();
      }
    });
  }

  // Handle Explicit "Save Address" Button Click
  function showSaveAddressFeedback(msg, type) {
    var fb = document.getElementById('saveAddressFeedback');
    if (!fb) return;
    fb.style.display = 'inline-block';
    fb.style.color = (type === 'danger') ? '#dc2626' : '#059669';
    fb.innerHTML = (type === 'danger' ? '<i class="fa fa-exclamation-circle mr-1"></i> ' : '<i class="fa fa-check-circle mr-1"></i> ') + msg;
    setTimeout(function() { fb.style.display = 'none'; }, 4000);
  }

  if (btnSaveAddress) {
    btnSaveAddress.addEventListener('click', function(e) {
      e.preventDefault();
      var houseVal = inputHouseNo ? inputHouseNo.value.trim() : '';
      var streetVal = inputStreet ? inputStreet.value.trim() : '';
      var cityVal = inputCity ? inputCity.value.trim() : '';
      var stateVal = inputState ? inputState.value.trim() : '';
      var pinVal = inputPincode ? inputPincode.value.trim() : '';
      var addrVal = inputAddress ? inputAddress.value.trim() : '';
      var typeRadio = document.querySelector('input[name="address_type"]:checked');
      var typeVal = typeRadio ? typeRadio.value : 'Home';

      if (!houseVal && inputHouseNo) {
        houseVal = inputHouseNo.value.trim();
      }
      if (!streetVal) {
        if (inputStreet) {
          inputStreet.focus();
          inputStreet.classList.add('is-invalid');
        }
        showSaveAddressFeedback('Please enter your Street / Area.', 'danger');
        return;
      }
      if (!cityVal) {
        if (inputCity) inputCity.focus();
        showSaveAddressFeedback('Please enter your City.', 'danger');
        return;
      }
      if (!pinVal) {
        if (inputPincode) inputPincode.focus();
        showSaveAddressFeedback('Please enter your Pincode.', 'danger');
        return;
      }

      var nameVal = inputName ? inputName.value.trim() : '';
      var emailVal = inputEmail ? inputEmail.value.trim() : '';

      var originalBtnHtml = btnSaveAddress.innerHTML;
      btnSaveAddress.disabled = true;
      btnSaveAddress.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> <span>Saving...</span>';

      var apiEndpoint = (window.REFIXEL_BASE_URL || '') + '/api/customer/save-address';
      fetch(apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          house_no: houseVal,
          street: streetVal,
          city: cityVal,
          state: stateVal,
          pincode: pinVal,
          address: addrVal,
          address_type: typeVal,
          name: nameVal,
          email: emailVal
        })
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        btnSaveAddress.disabled = false;
        btnSaveAddress.innerHTML = originalBtnHtml;

        var fullLoc = {
          houseNo: houseVal,
          street: streetVal,
          city: cityVal,
          state: stateVal,
          pincode: pinVal,
          fullAddress: addrVal || [streetVal, cityVal, stateVal, pinVal].filter(Boolean).join(', '),
          addressType: typeVal,
          timestamp: Date.now()
        };
        try {
          localStorage.setItem('refixel_user_location', JSON.stringify(fullLoc));
        } catch(e) {}

        var existingIdx = savedAddresses.findIndex(function(sa) {
          return (sa.street && sa.street.toLowerCase() === streetVal.toLowerCase()) &&
                 ((sa.house_no || '').toLowerCase() === houseVal.toLowerCase());
        });
        var newEntry = {
          label: typeVal,
          address_type: typeVal,
          house_no: houseVal,
          street: streetVal,
          city: cityVal,
          state: stateVal,
          pincode: pinVal,
          address: addrVal,
          is_default: 1
        };
        if (existingIdx !== -1) {
          savedAddresses[existingIdx] = Object.assign(savedAddresses[existingIdx], newEntry);
        } else {
          savedAddresses.unshift(newEntry);
        }
        try {
          localStorage.setItem(userStorageKey, JSON.stringify(savedAddresses));
        } catch(e) {}
        syncSavedAddressesBadge();
        var subtext = document.getElementById('activeAddressSubtext');
        if (subtext) {
          subtext.textContent = 'Active: ' + typeVal + ' - ' + (streetVal || cityVal);
        }

        window.CUSTOMER_SAVED_PROFILE = {
          house_no: houseVal,
          street: streetVal,
          city: cityVal,
          state: stateVal,
          pincode: pinVal,
          address: addrVal,
          address_type: typeVal
        };

        updateAddressSummaryDisplay();

        if (savedAddressBox) {
          savedAddressBox.style.display = 'block';
          flashHighlight(savedAddressBox);
        }
        if (addressDetailedBox) {
          addressDetailedBox.style.display = 'none';
        }
        if (toggleAddressText) toggleAddressText.textContent = 'Change / Edit Address';
        if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-down';
        if (btnToggleAddress) btnToggleAddress.style.background = '#ffffff';

        if (btnAddNewLoc) {
          isNewLocationMode = false;
          btnAddNewLoc.innerHTML = '<i class="fa fa-plus-circle"></i> <span>+ Add New Location</span>';
          btnAddNewLoc.style.background = '#eff6ff';
          btnAddNewLoc.style.color = '#1d4ed8';
          btnAddNewLoc.style.borderColor = '#bfdbfe';
        }

        if (bookingLocStatus) {
          bookingLocStatus.style.display = 'block';
          bookingLocStatus.innerHTML = '<span style="color: #059669;"><i class="fa fa-check-circle text-success mr-1"></i> Address saved successfully!</span>';
        }
      })
      .catch(function(err) {
        btnSaveAddress.disabled = false;
        btnSaveAddress.innerHTML = originalBtnHtml;
        showSaveAddressFeedback('Address saved in browser.', 'success');
        if (savedAddressBox) savedAddressBox.style.display = 'block';
        if (addressDetailedBox) addressDetailedBox.style.display = 'none';
        if (toggleAddressText) toggleAddressText.textContent = 'Change / Edit Address';
        if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-down';
      });
    });
  }

  // Safeguard: Ensure hidden fields don't block HTML5 validation if unexpectedly empty
  var bookingFormEl = document.getElementById('bookingForm');
  if (bookingFormEl) {
    bookingFormEl.addEventListener('submit', function() {
      if (addressDetailedBox && addressDetailedBox.style.display === 'none') {
        var reqs = addressDetailedBox.querySelectorAll('[required]');
        var missing = false;
        reqs.forEach(function(inp) { if (!inp.value.trim()) missing = true; });
        if (missing) {
          addressDetailedBox.style.display = 'block';
          if (toggleAddressText) toggleAddressText.textContent = 'Hide Details';
          if (toggleAddressIcon) toggleAddressIcon.className = 'fa fa-chevron-up';
        }
      }
    });
  }

  // 2. "Use My Current Location" button click handler
  if (btnUseCurrentLoc) {
    btnUseCurrentLoc.addEventListener('click', function() {
      btnUseCurrentLoc.disabled = true;
      btnUseCurrentLoc.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span>Detecting GPS...</span>';
      if (bookingLocStatus) {
        bookingLocStatus.style.display = 'block';
        bookingLocStatus.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Requesting GPS coordinates...</span>';
      }

      function tryIpFallback(reasonMsg) {
        if (bookingLocStatus) {
          bookingLocStatus.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Detecting location via network IP...</span>';
        }
        fetch('https://freeipapi.com/api/json')
          .then(function(res) { return res.json(); })
          .then(function(data) {
            var city = (data.cityName || '').trim();
            var region = (data.regionName || '').trim();
            var country = (data.countryName || '').trim();
            var zip = (data.zipCode || '').trim();
            if (city || region || country) {
              var locObj = {
                houseNo: (inputHouseNo && inputHouseNo.value.trim()) ? inputHouseNo.value.trim() : '',
                street: '',
                area: '',
                city: city,
                state: region,
                country: country,
                pincode: zip,
                fullAddress: [city, region, country].filter(Boolean).join(', ')
              };
              localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
              applyLocationData(locObj, 'Filled via Network IP (' + (city || country) + ')');
            } else {
              throw new Error('No location from IP');
            }
          })
          .catch(function() {
            btnUseCurrentLoc.disabled = false;
            btnUseCurrentLoc.innerHTML = '<i class="fa fa-crosshairs"></i> <span>Detect Location</span>';
            if (bookingLocStatus) {
              bookingLocStatus.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-circle mr-1"></i> Could not detect location automatically. Please enter your address manually.</span>';
            }
          });
      }

      if (!('geolocation' in navigator)) {
        tryIpFallback('Geolocation not supported');
        return;
      }

      navigator.geolocation.getCurrentPosition(
        function(pos) {
          var lat = pos.coords.latitude;
          var lon = pos.coords.longitude;

          if (bookingLocStatus) {
            bookingLocStatus.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Resolving your location...</span>';
          }

          var nominatimUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&zoom=18&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon);
          var photonUrl = 'https://photon.komoot.io/reverse?lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lon);

          Promise.allSettled([
            fetch(nominatimUrl).then(function(res) { return res.ok ? res.json() : null; }),
            fetch(photonUrl).then(function(res) { return res.ok ? res.json() : null; })
          ]).then(function(results) {
            var data = (results[0].status === 'fulfilled') ? results[0].value : null;
            var phoData = (results[1].status === 'fulfilled') ? results[1].value : null;

            if (!data && !phoData) {
              throw new Error('Both reverse geocoders failed');
            }

            var addr = (data && data.address) || {};
            var phoProps = (phoData && phoData.features && phoData.features[0] && phoData.features[0].properties) || {};

            var houseNo = addr.house_number || addr.building || addr.flat || addr.house_name || phoProps.housenumber || '';
            if (!houseNo || houseNo === 'Doorstep Visit') {
              houseNo = (inputHouseNo && inputHouseNo.value.trim()) ? inputHouseNo.value.trim() : '';
            }
            var locality = addr.city || addr.town || addr.village || addr.municipality || addr.city_district || phoProps.city || '';
            var district = addr.state_district || addr.district || '';
            var state = addr.state || addr.region || addr.province || phoProps.state || '';
            var country = addr.country || phoProps.country || '';
            var postcode = (addr.postcode || phoProps.postcode || '').trim();

            // Extract POI (shop, amenity, landmark)
            var poi = (addr.shop || addr.amenity || addr.office || addr.commercial || addr.building || addr.tourism || addr.leisure || (data && data.name) || phoProps.name || '').trim();
            if (poi && (poi.toLowerCase() === (locality || '').toLowerCase() || poi.toLowerCase() === (district || '').toLowerCase() || poi.toLowerCase() === (state || '').toLowerCase())) {
              poi = '';
            }

            // Extract Road
            var road = (addr.road || addr.street || addr.pedestrian || addr.footway || addr.path || phoProps.street || '').trim();
            if (road && (road.toLowerCase() === (locality || '').toLowerCase() || road.toLowerCase() === (district || '').toLowerCase())) {
              road = '';
            }

            // Extract SubLocality / Colony / Neighbourhood
            var subLocality = (addr.suburb || addr.neighbourhood || addr.residential || addr.subdistrict || addr.quarter || addr.hamlet || (phoProps.district && phoProps.district !== district ? phoProps.district : '') || '').trim();
            if (subLocality && (subLocality.toLowerCase() === (locality || '').toLowerCase() || subLocality.toLowerCase() === (district || '').toLowerCase())) {
              subLocality = '';
            }

            var street = '';
            if (poi && road) {
              street = poi + ', ' + road;
            } else if (poi && subLocality) {
              street = poi + ', ' + subLocality;
            } else if (poi) {
              street = poi;
            } else if (road && subLocality) {
              street = road + ', ' + subLocality;
            } else if (road) {
              street = road;
            } else if (subLocality) {
              street = subLocality;
            } else {
              street = '';
            }

            var cityVal = locality || district || subLocality || '';
            var stateVal = state || '';

            var fullAddr = (data && data.display_name) || [street, cityVal, stateVal, country].filter(Boolean).join(', ');

            var locObj = {
              houseNo: houseNo,
              street: street,
              area: subLocality || poi || road || '',
              district: district,
              city: cityVal,
              state: stateVal,
              country: country,
              pincode: postcode,
              fullAddress: fullAddr
            };

            localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
            applyLocationData(locObj, 'Filled via GPS (' + (cityVal || country || 'Live') + ')');
          })
          .catch(function(geoErr) {
            console.warn('Primary geocoders failed, falling back to BigDataCloud:', geoErr);
            var bdcUrl = 'https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + encodeURIComponent(lat) + '&longitude=' + encodeURIComponent(lon) + '&localityLanguage=en';
            fetch(bdcUrl)
              .then(function(r) { return r.json(); })
              .then(function(bdc) {
                var city = (bdc.city || bdc.locality || '').trim();
                var state = (bdc.principalSubdivision || '').trim();
                var country = (bdc.countryName || '').trim();
                var zip = (bdc.postcode || '').trim();
                var streetVal = (bdc.locality && bdc.locality !== city) ? bdc.locality : '';
                var locObj = {
                  houseNo: (inputHouseNo && inputHouseNo.value.trim()) ? inputHouseNo.value.trim() : '',
                  street: streetVal,
                  area: streetVal || city,
                  city: city || state,
                  state: state,
                  country: country,
                  pincode: zip,
                  fullAddress: [city, state, country].filter(Boolean).join(', ')
                };
                localStorage.setItem('refixel_user_location', JSON.stringify(locObj));
                applyLocationData(locObj, 'Filled via GPS (' + (city || country || 'Live') + ')');
              })
              .catch(function() {
                tryIpFallback('Reverse geocode failed');
              });
          });
        },
        function(err) {
          console.warn('GPS error, using IP fallback:', err);
          tryIpFallback('GPS permission denied or timeout');
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
      );
    });
  }
});
</script>
