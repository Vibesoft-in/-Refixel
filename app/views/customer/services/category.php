<?php
$catName = \App\Core\View::e($category['name']);
$catSlug = \App\Core\View::e($category['slug']);
$cityName = \App\Core\View::e($city);
$currentCitySlug = \App\Core\View::e($citySlug ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $city), '-')));

$refixelTrustData = [
    'fall-ceiling-services' => [
        'badge'     => '✦ Refixel Signature Interior Works',
        'assurance' => '5-Year Structural Workmanship Assurance',
        'title'     => 'Why Homeowners Trust Refixel for Fall Ceiling',
        'desc'      => 'At Refixel, our architectural fall ceiling installations combine high-strength galvanized iron (GI) framework, genuine Saint-Gobain Gyproc plasterboards, and 360° laser-guided leveling. Designed for modern Indian lifestyle spaces, each ceiling integrates custom LED ambient cove troughs, flush magnetic tracks, acoustic dampening, and flexible fiberglass jointing that eliminates cracks forever.',
        'points'    => [
            ['title' => 'Laser-Guided Alignment:', 'desc' => 'True 90° corners & zero sag'],
            ['title' => 'Concealed LED Coves:', 'desc' => 'Seamless indirect ambient glow'],
            ['title' => 'Thermal Insulation:', 'desc' => 'Keeps rooms up to 4°C cooler'],
        ],
    ],
    'fall-ceiling' => [
        'badge'     => '✦ Refixel Signature Interior Works',
        'assurance' => '5-Year Structural Workmanship Assurance',
        'title'     => 'Why Homeowners Trust Refixel for Fall Ceiling',
        'desc'      => 'At Refixel, our architectural fall ceiling installations combine high-strength galvanized iron (GI) framework, genuine Saint-Gobain Gyproc plasterboards, and 360° laser-guided leveling. Designed for modern Indian lifestyle spaces, each ceiling integrates custom LED ambient cove troughs, flush magnetic tracks, acoustic dampening, and flexible fiberglass jointing that eliminates cracks forever.',
        'points'    => [
            ['title' => 'Laser-Guided Alignment:', 'desc' => 'True 90° corners & zero sag'],
            ['title' => 'Concealed LED Coves:', 'desc' => 'Seamless indirect ambient glow'],
            ['title' => 'Thermal Insulation:', 'desc' => 'Keeps rooms up to 4°C cooler'],
        ],
    ],
    'cleaning' => [
        'badge'     => '✦ Refixel Deep Hygiene Protocol',
        'assurance' => '100% Eco-Friendly & Pet-Safe Assurance',
        'title'     => 'Why Homeowners Trust Refixel for Cleaning Services',
        'desc'      => 'At Refixel, our professional deep cleaning protocol eliminates 99.9% of bacteria, allergens, and stubborn grime without harming delicate surfaces. We deploy high-RPM mechanized floor scrubbers, industrial vacuum extractors, and hospital-grade eco-friendly formulations. Every cleaning session follows an audited 60-point hygiene checklist under certified field supervisor verification.',
        'points'    => [
            ['title' => 'Hospital-Grade Disinfection:', 'desc' => 'Non-toxic, safe for infants & pets'],
            ['title' => 'Mechanized Deep Scrubbing:', 'desc' => 'Industrial rotary scrubbers for tile & grout'],
            ['title' => 'Audited 60-Point Checklist:', 'desc' => 'Pre & post service inspection sign-off'],
        ],
    ],
    'painting-services' => [
        'badge'     => '✦ Refixel Precision Finishing Standard',
        'assurance' => '3-Year Anti-Peeling & Moisture Warranty',
        'title'     => 'Why Homeowners Trust Refixel for Painting Services',
        'desc'      => 'Refixel transforms residential and commercial walls with dust-free mechanized sanding, moisture-meter wall testing, and premium low-VOC paints from Asian Paints, Berger, and Dulux. Our painters protect every furniture piece and flooring with heavy-duty masking film, ensuring flawless wall coats, zero mess, and vibrant colors that last for years.',
        'points'    => [
            ['title' => 'Laser Moisture Inspection:', 'desc' => 'Prevents paint bubbling & dampness'],
            ['title' => 'Mechanized Sanding:', 'desc' => '90% less dust with ultra-smooth wall finish'],
            ['title' => 'Full Furniture Masking:', 'desc' => 'Zero paint stains on floors & decor'],
        ],
    ],
    'ac-services' => [
        'badge'     => '✦ Refixel Pro Climate Tech',
        'assurance' => '30-Day Post-Service Cooling Guarantee',
        'title'     => 'Why Homeowners Trust Refixel for AC Service & Repair',
        'desc'      => 'Our certified HVAC technicians use high-pressure jet-pump washing with protective water-jackets to deep-clean indoor cooling coils and outdoor condenser fins without dirtying your walls. We perform digital micron leak checks, pressure tests, and genuine OEM spare replacements to restore peak cooling efficiency and lower electricity consumption.',
        'points'    => [
            ['title' => 'Jet-Pump Deep Wash:', 'desc' => '2x better airflow & rapid cooling restore'],
            ['title' => 'Pressure & Leak Testing:', 'desc' => 'Genuine refrigerant top-ups without gas loss'],
            ['title' => 'Energy Efficiency Tune-Up:', 'desc' => 'Cuts compressor load & power bills'],
        ],
    ],
    'electrician' => [
        'badge'     => '✦ Refixel Certified Power Safety',
        'assurance' => '100% Fire-Safe & Genuine Spares Guarantee',
        'title'     => 'Why Homeowners Trust Refixel for Electrical Services',
        'desc'      => 'Every electrical installation and fault repair by Refixel is carried out by government-licensed wiremen using insulated tools, circuit multimeters, and heat-resistant ISI-certified wiring. From heavy appliance load balancing to smart switches and short-circuit troubleshooting, we prioritize zero fire hazards and complete shock-proof home protection.',
        'points'    => [
            ['title' => 'Certified Wiremen:', 'desc' => 'Government-licensed experts for all voltage tasks'],
            ['title' => 'ISI-Marked Spares:', 'desc' => 'Heavy-duty copper wiring & genuine MCBs'],
            ['title' => 'Thermal Load Audits:', 'desc' => 'Prevents short circuits & appliance damage'],
        ],
    ],
    'plumbers' => [
        'badge'     => '✦ Refixel Hydro-Seal Diagnostics',
        'assurance' => 'Zero-Leakage & Pressure Warranty',
        'title'     => 'Why Homeowners Trust Refixel for Plumbing Services',
        'desc'      => 'Refixel brings industrial-grade pipe cameras, acoustic leak detectors, and high-torque plumbing tools right to your doorstep. Whether diagnosing concealed wall seepages, installing designer bathroom CP fittings, or clearing stubborn mainline drain clogs, our technicians deliver permanent repairs without unnecessary wall damage.',
        'points'    => [
            ['title' => 'Concealed Leak Detection:', 'desc' => 'Pinpoint diagnosis without breaking tiles'],
            ['title' => 'Pressure-Tested Joints:', 'desc' => 'High-grade CPVC/UPVC solvent welds'],
            ['title' => 'Clear Transparent Pricing:', 'desc' => 'Standard rate card with zero surprise costs'],
        ],
    ],
    'carpenter' => [
        'badge'     => '✦ Refixel Master Craftsmanship',
        'assurance' => 'Precision Hardware & Durability Promise',
        'title'     => 'Why Homeowners Trust Refixel for Carpentry Services',
        'desc'      => 'From custom modular furniture assembly and soft-close hydraulic hinge adjustments to solid hardwood restorations, Refixel carpenters bring master craftsmanship and laser alignment tools. We use moisture-resistant marine-ply and branded hardware fittings (Hettich, Hafele, Ebco) to guarantee silent, smooth, and long-lasting woodwork.',
        'points'    => [
            ['title' => 'Micro-Tolerance Alignment:', 'desc' => 'Laser leveling for doors & cabinets'],
            ['title' => 'Branded Hardware Upgrades:', 'desc' => 'Durable soft-close hinges & drawer channels'],
            ['title' => 'Clean Onsite Cutting:', 'desc' => 'Vacuum dust collection during indoor cutting'],
        ],
    ],
    'pest-control' => [
        'badge'     => '✦ Refixel Bio-Shield Defense',
        'assurance' => '100% Odorless & CIB-Approved Formulation',
        'title'     => 'Why Homeowners Trust Refixel for Pest Control',
        'desc'      => 'Refixel deploys government CIB-certified gel baits, cold thermal fogging, and targeted crack injections to eradicate cockroaches, termites, bedbugs, and rodents at the root colony level. Our odorless, herbal-derived treatments require zero kitchen evacuation, keeping children, elders, and pets safe while providing long-lasting barrier defense.',
        'points'    => [
            ['title' => 'Root Colony Elimination:', 'desc' => 'Eradicates nests rather than temporary repelling'],
            ['title' => 'Zero-Smell Formulations:', 'desc' => 'No need to vacate your home or kitchen'],
            ['title' => 'Multi-Stage Revisit Defense:', 'desc' => 'Scheduled follow-up checks for zero recurrence'],
        ],
    ],
    'appliance-repair' => [
        'badge'     => '✦ Refixel Appliance Diagnostics',
        'assurance' => '90-Day Spare Parts & Labor Warranty',
        'title'     => 'Why Homeowners Trust Refixel for Appliance Repair',
        'desc'      => 'Refixel provides certified multi-brand repair for refrigerators, washing machines, microwaves, water purifiers, and kitchen chimneys. Our technicians carry genuine OEM components, digital diagnostic scanners, and perform comprehensive component tests before and after repair to extend your appliance lifespan.',
        'points'    => [
            ['title' => '100% Genuine OEM Parts:', 'desc' => 'Sourced directly from certified suppliers'],
            ['title' => 'On-Spot Digital Diagnosis:', 'desc' => 'Accurate fault detection in 30 minutes'],
            ['title' => '90-Day Service Warranty:', 'desc' => 'Free re-visit assurance if issues persist'],
        ],
    ],
];
?>
<div class="container py-5 my-3">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0 mb-4" style="font-size: 14px;">
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/') ?>" style="color:#f25b29;">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/services') ?>" style="color:#f25b29;">Services</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= $catName ?> - Available in your location</li>
    </ol>
  </nav>

  <!-- Hero Header for Category & City -->
  <div class="p-4 p-md-5 rounded mb-5" style="background: linear-gradient(135deg, #fff7f3 0%, #ffefe8 100%); border: 1px solid #ffdacf; border-radius: 16px;">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge px-3 py-2 mb-2 text-white font-weight-bold" style="background:#f25b29; font-size:12.5px; border-radius: 8px;">Verified & Insured - Available in your location</span>
        <h1 class="font-weight-bold mb-3" style="font-size: 34px; color: #1a1a1a;">
          Professional <?= $catName ?> Services - Available in your location
        </h1>
        <p class="lead text-muted mb-4" style="font-size: 16px; line-height: 1.7;">
          <?= \App\Core\View::e($category['description'] ?? 'Mechanized tools, eco-friendly consumables, and background-verified technicians delivering top-rated service at your doorstep.') ?>
        </p>
        <div class="d-flex flex-wrap align-items-center" style="gap: 15px;">
          <a href="#servicesList" class="btn text-white px-4 py-2 font-weight-bold" style="background:#f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
            View Available Packages & Pricing
          </a>
          <span class="text-muted small"><i class="fa fa-clock-o mr-1" style="color:#f25b29;"></i> Slots available today - Available in your location</span>
        </div>
      </div>
      <div class="col-lg-4 text-center mt-4 mt-lg-0">
        <img src="<?= \App\Core\View::asset('img/' . (!empty($category['icon']) ? $category['icon'] : 'Full-home-clean.jpg')) ?>"
             alt="<?= $catName ?> - Available in your location"
             class="img-fluid rounded" style="max-height: 220px; object-fit: contain;"
             onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/Full-home-clean.jpg') ?>';">
      </div>
    </div>
  </div>

  <!-- Services Grid -->
  <div id="servicesList" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="font-weight-bold mb-1" style="font-size: 26px;">Available <?= $catName ?> Packages - Available in your location</h2>
        <p class="text-muted small mb-0">Select your package to view full checklist and instant booking options.</p>
      </div>
    </div>

    <div class="row">
      <?php if (!empty($services)): ?>
        <?php foreach ($services as $svc):
          $svcSlug = \App\Core\View::e($svc['slug']);
          $svcName = \App\Core\View::e($svc['name']);
          $svcUrl = \App\Core\View::url("/{$svcSlug}-in-{$currentCitySlug}");
          $imgFile = !empty($svc['image']) ? $svc['image'] : 'Full-home-clean.jpg';
        ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-0 service-package-card" data-service-url="<?= $svcUrl ?>" style="border-radius: 14px; overflow:hidden; cursor:pointer; box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04); border: 1px solid #e2e8f0;">
              <div class="position-relative" style="aspect-ratio: 16 / 10; height: auto; max-height: 200px; overflow: hidden; background: #f1f5f9;">
                <img src="<?= \App\Core\View::asset('img/' . $imgFile) ?>"
                     alt="<?= $svcName ?>"
                     style="height: 100%; width: 100%; object-fit: cover; object-position: center top !important;"
                     onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/Full-home-clean.jpg') ?>';">
                <button type="button" 
                        class="btn-add-to-cart card-img-cart-btn" 
                        data-service-id="<?= (int)$svc['id'] ?>" 
                        title="Add to Cart" 
                        aria-label="Add <?= $svcName ?> to cart"
                        style="position: absolute; right: 10px; bottom: 10px; z-index: 6; width: 34px; height: 34px; border-radius: 50%; background: #ffffff; color: #f25b29; border: 1.5px solid #ffffff; box-shadow: 0 3px 10px rgba(0,0,0,0.22); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;">
                  <i class="fa fa-shopping-cart" style="font-size: 13.5px;"></i>
                </button>
              </div>
              <div class="card-body d-flex flex-column p-3">
                <h4 class="font-weight-bold mb-1" style="font-size: 15.5px; color: #1a1a1a; letter-spacing: -0.2px;">
                  <a href="<?= $svcUrl ?>" style="color: inherit; text-decoration: none;"><?= $svcName ?></a>
                </h4>
                <p class="text-muted small flex-grow-1 mb-2" style="font-size: 12px; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 34px;"><?= \App\Core\View::e($svc['description'] ?? '') ?></p>

                <div class="d-flex justify-content-between align-items-center mb-2 pt-1">
                  <div>
                    <span class="text-muted small d-block" style="font-size: 11px; line-height: 1.2;">Starting at</span>
                    <h5 class="font-weight-bold mb-0" style="color: #f25b29; font-size: 16px;">₹<?= number_format((float)$svc['starting_price'], 0) ?></h5>
                  </div>
                  <?php if (!empty($svc['duration_minutes'])): ?>
                    <span class="badge badge-light px-2 py-1" style="font-size: 11px; font-weight: 600; color: #475569; background: #f1f5f9;">
                      <i class="fa fa-clock-o mr-1"></i> <?= (int)$svc['duration_minutes'] ?> mins
                    </span>
                  <?php endif; ?>
                </div>

                <div class="d-flex align-items-center mt-1" style="gap: 8px;">
                  <!-- Checklist & Details on the Left -->
                  <a href="<?= $svcUrl ?>" class="btn btn-outline-secondary btn-sm flex-grow-1 font-weight-bold btn-package-details text-center" style="border-radius: 7px; font-size: 12px; padding: 6px 10px; white-space: nowrap;">
                    <i class="fa fa-list-ul mr-1"></i> Checklist
                  </a>
                  <!-- Book Now on the Right -->
                  <a href="<?= \App\Core\View::url('/book?service_id=' . (int)$svc['id'] . '&city=' . urlencode($city)) ?>" class="btn text-white btn-sm px-2 font-weight-bold btn-package-book text-center" style="background:#f25b29; border-radius: 7px; box-shadow: 0 2px 8px rgba(242, 91, 41, 0.2); font-size: 12px; padding: 6px 12px; white-space: nowrap;">
                    Book Now
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <p class="text-muted">No specific services listed under this category for <?= $cityName ?> yet. Contact us for custom arrangements!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php 
    $trust = $refixelTrustData[$catSlug] ?? [
      'badge'     => '✦ Refixel Verified Quality Standard',
      'assurance' => '100% Satisfaction & Workmanship Guarantee',
      'title'     => 'Why Homeowners Trust Refixel for ' . $catName,
      'desc'      => 'At Refixel, every home and commercial service is performed by background-verified professionals using standardized tools, genuine materials, and audited checklists. We combine transparent upfront pricing with our verified satisfaction guarantee, ensuring dependable craftsmanship and total peace of mind for every homeowner.',
      'points'    => [
        ['title' => 'Vetted & Trained Pros:', 'desc' => 'Rigorous background checks & skill audits'],
        ['title' => 'Transparent Pricing:', 'desc' => 'Fixed rate cards with zero hidden charges'],
        ['title' => 'Dedicated Support:', 'desc' => 'Real-time tracking & customer satisfaction guarantee'],
      ],
    ];
  ?>
  <!-- Refixel Signature Highlight Card for this Service Category -->
  <div class="card border-0 p-4 mb-5 shadow-sm" style="border-radius: 14px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
    <div class="d-flex align-items-center mb-3 flex-wrap" style="gap: 8px;">
      <span class="badge mr-2 px-3 py-1 font-weight-bold" style="background: rgba(242, 91, 41, 0.25); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.45); border-radius: 20px; font-size: 11.5px;">
        <?= $trust['badge'] ?>
      </span>
      <span class="text-white-50 small"><?= $trust['assurance'] ?></span>
    </div>
    <h3 class="font-weight-bold mb-2 text-white" style="font-size: 20px;"><?= $trust['title'] ?></h3>
    <p class="text-white-50 mb-3" style="font-size: 13.5px; line-height: 1.6; max-width: 820px;">
      <?= $trust['desc'] ?>
    </p>
    <div class="row pt-2" style="font-size: 12.5px; color: #cbd5e1;">
      <?php foreach ($trust['points'] as $pt): ?>
        <div class="col-md-4 mb-2">
          <i class="fa fa-check text-warning mr-2"></i> <strong><?= $pt['title'] ?></strong> <?= $pt['desc'] ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Service Areas Covered in this City -->
  <div class="mb-5">
    <h3 class="font-weight-bold mb-3" style="font-size: 22px;">
      <i class="fa fa-map-marker mr-2" style="color:#f25b29;"></i> <?= $catName ?> Coverage Areas - Available in your location
    </h3>
    <div class="p-4 rounded bg-white border">
      <p class="text-muted small mb-3">Our mobile teams and vetted field technicians service all major localities - Available in your location:</p>
      <div class="d-flex flex-wrap" style="gap: 8px;">
        <?php if (!empty($serviceAreas)): ?>
          <?php foreach ($serviceAreas as $area): ?>
            <span class="badge badge-light p-2 border font-weight-normal" style="font-size: 13px;">
              <i class="fa fa-map-pin mr-1" style="color:#f25b29;"></i> <?= \App\Core\View::e($area['area_name']) ?> (<?= \App\Core\View::e($area['pincode']) ?>)
            </span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="badge badge-light p-2 border">City Center & All Primary Sectors</span>
          <span class="badge badge-light p-2 border">Residential Colonies & Gated Societies</span>
          <span class="badge badge-light p-2 border">Commercial Hubs & Office Parks</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Related Services / Categories -->
  <?php if (!empty($relatedCategories)): ?>
    <div class="mb-5">
      <h3 class="font-weight-bold mb-3" style="font-size: 22px;">Explore Related Home Services</h3>
      <div class="row">
        <?php foreach ($relatedCategories as $rel):
          $relSlug = \App\Core\View::e($rel['slug']);
          $relUrl = \App\Core\View::url("/{$relSlug}-services-in-{$currentCitySlug}");
        ?>
          <div class="col-6 col-md-3 mb-3">
            <a href="<?= $relUrl ?>" class="card p-3 text-center text-dark text-decoration-none h-100 shadow-sm border-0" style="border-radius: 12px; transition: transform 0.15s ease;">
              <h6 class="font-weight-bold mb-1"><?= \App\Core\View::e($rel['name']) ?></h6>
              <small class="font-weight-bold" style="color:#f25b29;">Available in your location &rarr;</small>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Category FAQ Accordion -->
  <div class="mb-5">
    <div class="page_heading text-center mb-4">
      <h6 style="color:#f25b29; font-weight:700; letter-spacing: 1.2px;">QUESTIONS & ANSWERS</h6>
      <h3 class="font-weight-bold">Frequently Asked Questions</h3>
    </div>
    <div class="faq-container">
      <?php foreach (array_slice($faqs ?? [], 0, 4) as $idx => $faq): ?>
        <div class="faq-item <?= $idx === 0 ? 'active' : '' ?>">
          <div class="faq-question">
            <span><?= \App\Core\View::e($faq['question']) ?></span>
            <div class="faq-icon"><?= $idx === 0 ? '−' : '+' ?></div>
          </div>
          <div class="faq-answer" style="<?= $idx === 0 ? 'display:block;' : 'display:none;' ?>">
            <?= nl2br(\App\Core\View::e($faq['answer'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Booking CTA Banner -->
  <div class="p-4 p-md-5 rounded text-center text-white" style="background: linear-gradient(135deg, #07172c 0%, #0d2749 100%); border-radius: 18px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 10px 30px rgba(7, 23, 44, 0.2);">
    <h2 class="font-weight-bold mb-2">Ready to transform your space in <?= $cityName ?>?</h2>
    <p class="mb-4" style="opacity: 0.9;">Book now with zero advance payment and a 24-hour customer satisfaction guarantee.</p>
    <a href="<?= \App\Core\View::url('/book?city=' . urlencode($city)) ?>" class="btn text-white px-5 py-3 font-weight-bold" style="background: #f25b29; border-radius: 50px; font-size: 16px; box-shadow: 0 6px 20px rgba(242, 91, 41, 0.4);">
      Schedule <?= $catName ?> Now &rarr;
    </a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll(".faq-item").forEach(function(item) {
    var q = item.querySelector(".faq-question");
    if (q) {
      q.addEventListener("click", function() {
        var isOpen = item.classList.contains("active");
        document.querySelectorAll(".faq-item").forEach(function(other) {
          other.classList.remove("active");
          var ans = other.querySelector(".faq-answer");
          var ico = other.querySelector(".faq-icon");
          if (ans) ans.style.display = "none";
          if (ico) ico.textContent = "+";
        });
        if (!isOpen) {
          item.classList.add("active");
          var ans = item.querySelector(".faq-answer");
          var ico = item.querySelector(".faq-icon");
          if (ans) ans.style.display = "block";
          if (ico) ico.textContent = "−";
        }
      });
    }
  });

  /* Touch / Click anywhere on package card opens service details */
  document.addEventListener('click', function(e) {
    var card = e.target.closest('.service-package-card');
    if (!card) return;

    if (e.target.closest('.btn-add-to-cart') || e.target.closest('.btn-package-book') || e.target.closest('.btn-package-details')) {
      return;
    }

    var serviceUrl = card.getAttribute('data-service-url');
    if (serviceUrl) {
      window.location.href = serviceUrl;
    }
  });

  /* Add to Cart button handler */
  document.addEventListener('click', function(e) {
    var cartBtn = e.target.closest('.btn-add-to-cart');
    if (!cartBtn) return;
    e.preventDefault();
    e.stopPropagation();

    var serviceId = cartBtn.getAttribute('data-service-id');
    if (!serviceId || serviceId === '0') return;

    var origHtml = cartBtn.innerHTML;
    cartBtn.disabled = true;
    cartBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

    fetch('<?= \App\Core\View::url('/api/cart/add') ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'service_id=' + encodeURIComponent(serviceId) + '&quantity=1'
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      cartBtn.innerHTML = '<i class="fa fa-check"></i>';
      cartBtn.style.background = '#10b981';
      cartBtn.style.borderColor = '#10b981';
      cartBtn.style.color = '#ffffff';

      if (window.syncCartUI) {
        window.syncCartUI();
      }

      setTimeout(function() {
        cartBtn.innerHTML = origHtml;
        cartBtn.style.background = '';
        cartBtn.style.borderColor = '';
        cartBtn.style.color = '';
        cartBtn.disabled = false;
      }, 1600);
    })
    .catch(function() {
      cartBtn.innerHTML = origHtml;
      cartBtn.disabled = false;
    });
  });
});
</script>
