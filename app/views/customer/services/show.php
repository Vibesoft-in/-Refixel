<?php
$svcName = \App\Core\View::e($service['name']);
$cityName = \App\Core\View::e($city);
$price = number_format((float)$service['starting_price'], 0);
$duration = (int)($service['duration_minutes'] ?? 60);
$imgFile = !empty($service['image']) ? $service['image'] : 'Full-home-clean.jpg';

// Split checklist into included and excluded
$includedItems = [];
$excludedItems = [];
foreach ($checklist ?? [] as $item) {
    if (!empty($item['is_included'])) {
        $includedItems[] = $item['label'];
    } else {
        $excludedItems[] = $item['label'];
    }
}
?>
<div class="container py-5 my-3">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0 mb-4" style="font-size: 14px;">
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/') ?>" style="color:#f25b29;">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/services') ?>" style="color:#f25b29;">Services</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= $svcName ?> - Available in your location</li>
    </ol>
  </nav>

  <div class="row">
    <!-- Main Service Information -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4 service-detail-hero-card" style="border-radius: 16px; overflow:hidden;">
        <div class="service-hero-img-wrap">
          <img src="<?= \App\Core\View::asset('img/' . $imgFile) ?>"
               alt="<?= $svcName ?>"
               class="service-detail-hero-img"
               onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/Full-home-clean.jpg') ?>';">
        </div>

        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center mb-2">
            <span class="badge mr-2 font-weight-bold px-2.5 py-1.5" style="background:#fff3ec; color:#f25b29; border: 1px solid #ffdacf;">Verified Service</span>
            <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
          </div>

          <h1 class="font-weight-bold mb-3" style="font-size: 32px; color: #1a1a1a;">
            <?= $svcName ?> - Available in your location
          </h1>

          <p class="text-muted lead mb-4" style="font-size: 16px; line-height: 1.7;">
            <?= \App\Core\View::e($service['description'] ?? 'Top-rated professional service with industrial grade equipment and verified specialists.') ?>
          </p>

          <!-- Highlights strip -->
          <div class="row py-3 mb-4 rounded bg-light border">
            <div class="col-4 text-center border-right">
              <span class="text-muted small d-block">Starting Price</span>
              <h4 class="font-weight-bold mb-0" style="color: #f25b29;">₹<?= $price ?></h4>
            </div>
            <div class="col-4 text-center border-right">
              <span class="text-muted small d-block">Duration</span>
              <h4 class="font-weight-bold mb-0">~<?= $duration ?> mins</h4>
            </div>
            <div class="col-4 text-center">
              <span class="text-muted small d-block">Customer Rating</span>
              <h4 class="font-weight-bold mb-0" style="color: #f59e0b;">4.85 ★</h4>
            </div>
          </div>

          <!-- What's Included & What's Excluded Checklist -->
          <div class="mb-5">
            <h3 class="font-weight-bold mb-3" style="font-size: 22px;">Multi-Point Hygiene Checklist</h3>
            <p class="text-muted small mb-4">Every technician is required to complete and photograph each task before generating your invoice.</p>

            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                  <h5 class="font-weight-bold text-success mb-3" style="font-size: 16px;">
                    <i class="fa fa-check-circle mr-1"></i> What's Included
                  </h5>
                  <ul class="list-unstyled mb-0" style="font-size: 14px; line-height: 1.8;">
                    <?php if (!empty($includedItems)): ?>
                      <?php foreach ($includedItems as $item): ?>
                        <li class="mb-2"><i class="fa fa-check text-success mr-2"></i> <?= \App\Core\View::e($item) ?></li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="mb-2"><i class="fa fa-check text-success mr-2"></i> Mechanized high-pressure deep cleaning and scrubbing</li>
                      <li class="mb-2"><i class="fa fa-check text-success mr-2"></i> Surface descaling, degreasing, and dust vacuuming</li>
                      <li class="mb-2"><i class="fa fa-check text-success mr-2"></i> Eco-friendly antibacterial sanitization spray</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: #fef2f2; border: 1px solid #fecaca;">
                  <h5 class="font-weight-bold text-danger mb-3" style="font-size: 16px;">
                    <i class="fa fa-times-circle mr-1"></i> What's Excluded
                  </h5>
                  <ul class="list-unstyled mb-0" style="font-size: 14px; line-height: 1.8;">
                    <?php if (!empty($excludedItems)): ?>
                      <?php foreach ($excludedItems as $item): ?>
                        <li class="mb-2"><i class="fa fa-times text-danger mr-2"></i> <?= \App\Core\View::e($item) ?></li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="mb-2"><i class="fa fa-times text-danger mr-2"></i> Moving heavy structural furniture (>40kg) without assistance</li>
                      <li class="mb-2"><i class="fa fa-times text-danger mr-2"></i> Civil masonry repair, structural wall replastering</li>
                      <li class="mb-2"><i class="fa fa-times text-danger mr-2"></i> Replacement of broken hardware components</li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Before / After Showcase Section -->
          <?php
          $svcSlugLower = strtolower($service['slug'] ?? '');
          $svcNameLower = strtolower($service['name'] ?? '');
          $catSlugLower = strtolower($service['category_slug'] ?? '');
          $catNameLower = strtolower($service['category_name'] ?? '');
          $checkText    = ' ' . $svcSlugLower . ' ' . $svcNameLower . ' ' . $catSlugLower . ' ' . $catNameLower . ' ';

          // Default: Laundry / Room Deep Cleaning
          $beforeImg = 'transformations/laundry-before.jpg';
          $afterImg  = 'transformations/laundry-after.jpg';
          $transCategory = 'Deep Cleaning';
          $transBadge = '100% Sanitized';
          $transPill = 'ROOM CLEANING';
          $transTitle = 'Laundry & Room Deep Cleaning Transformation';
          $transDesc = 'Real results achieved by REFIXEL mechanized deep scrubbing, wall degreasing, and sanitized tile restoration.';

          if (str_contains($svcSlugLower, 'office-cleaning') || preg_match('/\b(office|commercial|workplace)\b/i', $checkText)) {
              $beforeImg = 'transformations/office-before.jpg';
              $afterImg  = 'transformations/office-after.jpg';
              $transCategory = 'Office Deep Clean';
              $transBadge = '100% Sanitized & Floor Buffed';
              $transPill = 'COMMERCIAL SPACE';
              $transTitle = 'Commercial Space & Corporate Office Deep Cleaning Transformation';
              $transDesc = 'Deep scrubbing of workstations, polished anti-static vinyl flooring, streak-free glass cabin partitions, and sanitized pantry areas.';
          } elseif (str_contains($svcSlugLower, 'full-home') || preg_match('/\b(full home|full-home|whole home|house clean)\b/i', $checkText)) {
              $beforeImg = 'transformations/homeclean-before.jpg';
              $afterImg  = 'transformations/homeclean-after.jpg';
              $transCategory = 'Full Home Clean';
              $transBadge = 'Mirror Polish Finish';
              $transPill = 'WHOLE HOME CARE';
              $transTitle = 'Professional Full Home Mechanized Deep Scrubbing Transformation';
              $transDesc = 'Single-disc floor buffing, stubborn grime removal, dustless wall cobweb clearing, window glass restoration, and hospital-grade sanitization.';
          } elseif (str_contains($svcSlugLower, 'repair-modification') || preg_match('/\b(cove light|repair-modification|ceiling repair)\b/i', $checkText)) {
              $beforeImg = 'transformations/ceilingrepair-before.jpg';
              $afterImg  = 'transformations/ceilingrepair-after.jpg';
              $transCategory = 'Ceiling & Cove Repair';
              $transBadge = 'Crack-Free & Ambient Glow';
              $transPill = 'COVE LIGHT MODIFICATION';
              $transTitle = 'POP Fall Ceiling Damp Crack Repair & Ambient Cove Light Modification';
              $transDesc = 'Sagging water-damp gypsum board re-framing, seamless joint plastering, and custom laser-aligned warm LED cove light trough installation.';
          } elseif (preg_match('/\b(ceiling|pop|gypsum|false-ceiling|cove)\b/i', $checkText)) {
              $beforeImg = 'transformations/ceiling-before.jpg';
              $afterImg  = 'transformations/ceiling-after.jpg';
              $transCategory = 'Fall Ceiling & POP';
              $transBadge = '5-Yr Workmanship Guarantee';
              $transPill = 'CEILING ARCHITECTURE';
              $transTitle = 'Luxury False Ceiling & Architectural Cove Light Transformation';
              $transDesc = 'Zero-crack Saint-Gobain gypsum false ceiling installation with heavy GI steel channel framing, warm LED ambient cove troughs, and laser-aligned profile spotlight channels.';
          } elseif (preg_match('/\b(appliance|washing machine|refrigerator|fridge|microwave)\b/i', $checkText)) {
              $beforeImg = 'transformations/appliance-before.jpg';
              $afterImg  = 'transformations/appliance-after.jpg';
              $transCategory = 'Appliance Repair';
              $transBadge = '100% Repaired & Tested';
              $transPill = 'APPLIANCE RESTORATION';
              $transTitle = 'Washing Machine Leak Diagnostic & Utility Nook Restoration';
              $transDesc = 'Motor drive belt replacement, diagnostic circuit board error code clearing, drainage pump leak sealing, and clean machine testing.';
          } elseif (preg_match('/\b(paint|painting|painter|whitewash|wall-painting|wall makeover|primer|distemper)\b/i', $checkText)) {
              $beforeImg = 'transformations/painting-before.jpg';
              $afterImg  = 'transformations/painting-after.jpg';
              $transCategory = 'Interior Painting';
              $transBadge = 'Royal Smooth Finish';
              $transPill = 'WALL MAKEOVER';
              $transTitle = 'Living Room Wall Plaster Seepage Repair & Emulsion Painting Transformation';
              $transDesc = 'Waterproofing seepage treatment, mechanized dustless sanding, putty leveling, and 3-coat washable royal emulsion paint with ambient warm lighting.';
          } elseif (preg_match('/\b(carpent|carpenter|carpentry|furniture|wood|cabinet|door|wardrobe|assembly)\b/i', $checkText)) {
              $beforeImg = 'transformations/carpenter-before.jpg';
              $afterImg  = 'transformations/carpenter-after.jpg';
              $transCategory = 'Carpentry & Furniture';
              $transBadge = 'Precision Assembly Guaranteed';
              $transPill = 'FURNITURE ASSEMBLY';
              $transTitle = 'Flatpack Furniture Assembly & Hinge Restoration Transformation';
              $transDesc = 'Flatpack wardrobe and bed assembly with laser leveling, heavy-duty soft-close hydraulic hinges, and wooden furniture scratch restoration.';
          } elseif (preg_match('/\b(pest|cockroach|termite|bedbug|bed-bug|rodent|fumigation)\b/i', $checkText)) {
              $beforeImg = 'transformations/pest-before.jpg';
              $afterImg  = 'transformations/pest-after.jpg';
              $transCategory = 'Pest Control';
              $transBadge = '100% Nest Eradication';
              $transPill = 'PEST CONTROL';
              $transTitle = 'Kitchen Under-Counter Pest Eradication Transformation';
              $transDesc = '100% German cockroach nest eradication under kitchen cabinets using certified odorless gel-baiting and deep sanitization.';
          } elseif (preg_match('/\b(ac|air conditioner|air-conditioner|air conditioning|split ac|window ac|hvac|compressor)\b/i', $checkText) || str_contains($checkText, 'ac-jet') || str_contains($checkText, 'ac jet')) {
              $beforeImg = 'transformations/ac-before.jpg';
              $afterImg  = 'transformations/ac-after.jpg';
              $transCategory = 'AC Jet Service';
              $transBadge = '2x Airflow Boost';
              $transPill = 'AC SERVICE';
              $transTitle = 'Split AC Deep Foam Jet Wash & Cooling Coil Decontamination';
              $transDesc = 'Clogged dust, mold, and odor eradicated with high-pressure water jet and antimicrobial foam wash, restoring instant ice-cold airflow.';
          } elseif (preg_match('/\b(plumb|plumbing|plumber|leak|pipe|tap|drain|sink|basin|faucet|cistern|flush)\b/i', $checkText)) {
              $beforeImg = 'transformations/plumbing-before.jpg';
              $afterImg  = 'transformations/plumbing-after.jpg';
              $transCategory = 'Plumbing Repairs';
              $transBadge = '100% Leak-Proof';
              $transPill = 'PLUMBING CARE';
              $transTitle = 'Under-Sink Pipe Leak & Sanitary Drainage Repair';
              $transDesc = 'Rusted dripping joints and stagnant mold cleared out, replaced with heavy-duty anti-leak PVC P-traps and clean under-sink organization.';
          } elseif (preg_match('/\b(electric|electrical|electrician|switch|switchboard|mcb|fan|wiring|fuse|socket|light|inverter)\b/i', $checkText)) {
              $beforeImg = 'transformations/electrical-before.jpg';
              $afterImg  = 'transformations/electrical-after.jpg';
              $transCategory = 'Electrical Repairs';
              $transBadge = 'Certified Safe';
              $transPill = 'ELECTRICAL CARE';
              $transTitle = 'MCB Distribution Board & Concealed Switchboard Overhaul';
              $transDesc = 'Exposed tangled conduits and hazardous live wiring replaced with flush-mount modular switchplates and safety-certified MCBs.';
          } elseif (preg_match('/\b(sofa|upholstery|carpet|couch|mattress|cushion)\b/i', $checkText)) {
              $beforeImg = 'transformations/sofa-before.jpg';
              $afterImg  = 'transformations/sofa-after.jpg';
              $transCategory = 'Sofa Cleaning';
              $transBadge = '100% Stains Lifted';
              $transPill = 'SOFA CARE';
              $transTitle = 'Fabric Sofa Stain Extraction Transformation';
              $transDesc = 'Deep fiber shampooing extracting dark stains, sweat marks, and allergens, restoring original soft texture and brightness.';
          } elseif (preg_match('/\b(kitchen|stove|chimney|hob|degrease|cooktop)\b/i', $checkText)) {
              $beforeImg = 'transformations/kitchen-before.jpg';
              $afterImg  = 'transformations/kitchen-after.jpg';
              $transCategory = 'Kitchen Clean';
              $transBadge = 'Zero Grease';
              $transPill = 'KITCHEN CLEAN';
              $transTitle = 'Stove, Tiles & Countertop Deep Degreasing';
              $transDesc = 'Burnt grease and oil crust stripped with eco-degreaser & high-pressure steam flush.';
          } elseif (preg_match('/\b(balcony|terrace|patio)\b/i', $checkText)) {
              $beforeImg = 'transformations/balcony-before.jpg';
              $afterImg  = 'transformations/balcony-after.jpg';
              $transCategory = 'Balcony Jet Wash';
              $transBadge = '100% Moss & Grime Eradicated';
              $transPill = 'BALCONY RESTORATION';
              $transTitle = 'Balcony Deep Pressure Washing & Glass Railing Restoration';
              $transDesc = 'Stubborn outdoor algae, bird droppings, and weather grime removed with 160-bar high-pressure water jet and glass descaling treatment.';
          } elseif (preg_match('/\b(bath|bathroom|toilet|shower|washroom|descale|commode)\b/i', $checkText)) {
              $beforeImg = 'transformations/bathroom-before.jpg';
              $afterImg  = 'transformations/bathroom-after.jpg';
              $transCategory = 'Bathroom Care';
              $transBadge = 'Crystal Clear Glass';
              $transPill = 'BATHROOM CARE';
              $transTitle = 'Shower Glass & Hard-Water Descaling';
              $transDesc = 'Hard-water calcium & soap scum dissolved to crystal transparency.';
          }
          ?>
          <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h3 class="font-weight-bold mb-0" style="font-size: 22px;">Transformation Showcase</h3>
              <span class="badge px-3 py-1 font-weight-bold" style="background:#fff3ec; color:#f25b29; border: 1px solid #ffdacf; font-size:12px;">
                <i class="fa fa-sparkles"></i> <?= $transCategory ?> Proof
              </span>
            </div>
            <p class="text-muted small mb-3"><?= $transDesc ?></p>

            <div class="ba_service_showcase_wrap border rounded overflow-hidden shadow-sm" style="border-radius: 14px; background: #fff;">
              <!-- Interactive Slideable Viewer -->
              <div class="ba_compare_viewer" style="--pos: 50%; aspect-ratio: 16 / 9; border-radius: 14px 14px 0 0;">
                <!-- Base Image: After -->
                <img src="<?= \App\Core\View::asset('img/' . $afterImg) ?>" alt="<?= $transTitle ?> After" class="ba_img_base" loading="lazy">
                <span class="ba_badge_pill ba_badge_after">AFTER</span>

                <!-- Clipped Overlay: Before -->
                <div class="ba_layer_before">
                  <img src="<?= \App\Core\View::asset('img/' . $beforeImg) ?>" alt="<?= $transTitle ?> Before" class="ba_img_clip" loading="lazy">
                  <span class="ba_badge_pill ba_badge_before">BEFORE</span>
                </div>

                <!-- Divider Line and Draggable Knob -->
                <div class="ba_slider_handle">
                  <div class="ba_slider_button">
                    <i class="fa fa-chevron-left"></i>
                    <i class="fa fa-chevron-right"></i>
                  </div>
                </div>

                <!-- Service & Hint Badges -->
                <span class="ba_service_pill"><?= $transPill ?></span>
                <span class="ba_hint_pill"><i class="fa fa-arrows-h"></i> Slide</span>

                <input type="range" min="0" max="100" value="50" class="ba_range_input" aria-label="Slide to compare before and after">
              </div>

              <!-- Quick Snap Controls & Bottom Info -->
              <div class="p-3 bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 border-top">
                <div class="ba_quick_controls m-0" style="min-width: 200px;">
                  <button type="button" class="ba_quick_btn" data-set-pos="100">Before</button>
                  <button type="button" class="ba_quick_btn active" data-set-pos="50">Split</button>
                  <button type="button" class="ba_quick_btn" data-set-pos="0">After</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge badge-success px-3 py-2 font-weight-bold" style="background: #10b981; color: #fff; font-size: 11.5px; border-radius: 20px;">
                    <i class="fa fa-check-circle mr-1"></i> <?= $transBadge ?>
                  </span>
                  <a href="<?= \App\Core\View::url('/gallery') ?>" class="btn btn-sm btn-outline-dark font-weight-bold ml-2">
                    View Full Gallery &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>

          <?php if (str_contains($svcSlugLower, 'ceiling') || str_contains($svcSlugLower, 'pop')): ?>
          <!-- Refixel Signature Fall Ceiling Craftsmanship Guide -->
          <div class="mb-5 p-4 rounded shadow-sm border" style="background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 16px;">
            <div class="d-flex align-items-center mb-3">
              <span class="badge mr-2 px-3 py-1 font-weight-bold" style="background: rgba(242, 91, 41, 0.25); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.5); border-radius: 20px; font-size: 12px;">
                ✦ Refixel Master Craftsmanship
              </span>
              <span class="text-white-50 small">5-Year Structural Workmanship Guarantee</span>
            </div>
            
            <h3 class="font-weight-bold mb-2 text-white" style="font-size: 24px;">Precision Fall Ceiling Engineering by Refixel</h3>
            <p class="text-white-50 mb-4" style="font-size: 14.5px; line-height: 1.7;">
              At Refixel, we treat ceilings as the "fifth wall" of modern homes. Our expert fall ceiling teams execute complete end-to-end installations designed for architectural beauty, longevity, and thermal insulation. Discover why homeowners and interior architects trust Refixel:
            </p>

            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                  <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f25b29; color: #fff; font-size: 12px; font-weight: bold;">1</span>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15px;">360° Digital Laser Leveling</h5>
                  </div>
                  <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                    We map every room perimeter with precision green-beam rotary lasers to maintain true horizontal alignment, eliminating ceiling dips, uneven corners, and optical distortion.
                  </p>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                  <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f25b29; color: #fff; font-size: 12px; font-weight: bold;">2</span>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15px;">Heavy-Gauge GI Steel Framing</h5>
                  </div>
                  <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                    Only commercial-grade 0.50mm+ galvanized iron (GI) channels and intermediate suspension brackets are bolted with rawl plugs to guarantee zero sagging and vibration immunity.
                  </p>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                  <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f25b29; color: #fff; font-size: 12px; font-weight: bold;">3</span>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15px;">Saint-Gobain Gyproc & MR Boards</h5>
                  </div>
                  <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                    We install genuine Saint-Gobain gypsum boards. In kitchens and bathrooms, green moisture-resistant (MR) boards prevent humidity expansion, warping, and mold buildup.
                  </p>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                  <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f25b29; color: #fff; font-size: 12px; font-weight: bold;">4</span>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15px;">Concealed LED Cove & Magnetic Tracks</h5>
                  </div>
                  <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                    Specially handcrafted light troughs create smooth indirect illumination without hotspot glare. Pre-cut channels accommodate modern magnetic track lights and flush spotlights.
                  </p>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                  <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f25b29; color: #fff; font-size: 12px; font-weight: bold;">5</span>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15px;">Anti-Crack Fiberglass Jointing</h5>
                  </div>
                  <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                    Tapered-edge boards are reinforced with fiberglass self-adhesive mesh tape and polymer jointing compound that flexes with seasonal temperature shifts, preventing hairline cracks.
                  </p>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                  <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle mr-2 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #f25b29; color: #fff; font-size: 12px; font-weight: bold;">6</span>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size: 15px;">Dustless Sanding & Thermal Cooling</h5>
                  </div>
                  <p class="text-white-50 small mb-0" style="line-height: 1.6;">
                    Vacuum-assisted machine sanding creates a super-smooth paint-ready surface. The overhead trapped air pocket naturally insulates your room, reducing summer AC cooling load by up to 25%.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <!-- Verified Customer Reviews Section -->
          <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h3 class="font-weight-bold mb-0" style="font-size: 22px;">Verified Customer Reviews</h3>
              <span class="text-warning font-weight-bold">4.85 ★★★★★ (128+ Reviews)</span>
            </div>

            <?php if (!empty($reviews)): ?>
              <div class="row">
                <?php foreach (array_slice($reviews, 0, 4) as $rev): ?>
                  <div class="col-md-6 mb-3">
                    <div class="p-3 rounded bg-light border h-100">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong style="font-size: 14.5px;"><?= \App\Core\View::e($rev['customer_name'] ?? 'Homeowner') ?></strong>
                        <span class="text-warning font-weight-bold"><?= (int)$rev['rating'] ?>★</span>
                      </div>
                      <p class="text-muted small mb-1 fst-italic">"<?= \App\Core\View::e($rev['review_text'] ?? 'Excellent work, very professional and punctual.') ?>"</p>
                      <small class="text-success"><i class="fa fa-check-circle"></i> Verified Booking - Available in your location</small>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="p-4 rounded bg-light border text-center">
                <p class="text-muted mb-0 small">Be the first to review <?= $svcName ?> in your location after your service completion!</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- Service Areas Covered in this City -->
          <div class="mb-5">
            <h3 class="font-weight-bold mb-3" style="font-size: 22px;">
              <i class="fa fa-map-marker mr-2" style="color:#f25b29;"></i> Service Coverage - Available in your location
            </h3>
            <div class="p-3 rounded bg-white border">
              <p class="text-muted small mb-2">Immediate technician dispatch - Available in your location:</p>
              <div class="d-flex flex-wrap" style="gap: 6px;">
                <?php if (!empty($serviceAreas)): ?>
                  <?php foreach ($serviceAreas as $area): ?>
                    <span class="badge badge-light p-2 border font-weight-normal">
                      <?= \App\Core\View::e($area['area_name']) ?> (<?= \App\Core\View::e($area['pincode']) ?>)
                    </span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="badge badge-light p-2 border">DLF Phase 1–5</span>
                  <span class="badge badge-light p-2 border">Cyber City</span>
                  <span class="badge badge-light p-2 border">Sohna Road</span>
                  <span class="badge badge-light p-2 border">Golf Course Ext.</span>
                  <span class="badge badge-light p-2 border">All Major Sectors</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Service FAQs -->
          <?php if (!empty($faqs)): ?>
            <div class="mb-4">
              <h3 class="font-weight-bold mb-3" style="font-size: 22px;">Frequently Asked Questions</h3>
              <div class="faq-container">
                <?php foreach (array_slice($faqs, 0, 5) as $idx => $faq): ?>
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
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Booking Sticky Action Card -->
    <div class="col-lg-4">
      <div class="card p-4 shadow-sm border-0 sticky-top" style="top: 90px; border-radius: 16px;">
        <span class="badge mb-2 font-weight-bold" style="letter-spacing: 0.5px; color:#f25b29; background: #fff3ec; border: 1px solid #ffdacf; width: fit-content;">Instant Doorstep Slot</span>
        <h4 class="font-weight-bold mb-1" style="font-size: 22px;"><?= $svcName ?></h4>
        <p class="text-muted small mb-3">Location: <strong><?= $cityName ?></strong></p>

        <div class="d-flex align-items-baseline mb-3 pb-3 border-bottom">
          <h2 class="font-weight-bold mb-0" style="color: #f25b29;">₹<?= $price ?></h2>
          <span class="text-muted ml-2 small">Starting price • Inclusive of GST</span>
        </div>

        <ul class="list-unstyled mb-4 small" style="line-height: 1.9;">
          <li><i class="fa fa-check mr-2" style="color:#f25b29;"></i> 100% Background-verified technician</li>
          <li><i class="fa fa-check mr-2" style="color:#f25b29;"></i> Zero advance payment needed</li>
          <li><i class="fa fa-check mr-2" style="color:#f25b29;"></i> Cash or UPI payment on service completion</li>
          <li><i class="fa fa-check mr-2" style="color:#f25b29;"></i> 24-Hour satisfaction re-clean guarantee</li>
        </ul>

        <a href="<?= \App\Core\View::url('/book?service_id=' . (int)$service['id'] . '&city=' . urlencode($city)) ?>" class="btn text-white py-3 font-weight-bold text-center w-100 mb-2" style="background:#f25b29; border-radius: 8px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);">
          Book Doorstep Visit
        </a>

        <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hi%20Refixel%2C%20I%20would%20like%20to%20book%20<?= urlencode($service['name']) ?>%20in%20<?= urlencode($city) ?>" target="_blank" class="btn btn-outline-success py-2 font-weight-bold text-center w-100 small" style="border-radius: 8px;">
          <i class="fa fa-whatsapp mr-1"></i> Quick Book via WhatsApp
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var viewers = document.querySelectorAll('.ba_compare_viewer');

  viewers.forEach(function (viewer) {
    var range = viewer.querySelector('.ba_range_input');
    var wrap = viewer.closest('.ba_service_showcase_wrap') || viewer.parentElement;
    var quickBtns = wrap ? wrap.querySelectorAll('.ba_quick_btn') : [];
    var isDragging = false;

    function updatePosition(val) {
      var numVal = Math.max(0, Math.min(100, Math.round(Number(val))));
      viewer.style.setProperty('--pos', numVal + '%');
      if (range) {
        range.value = numVal;
      }
      if (quickBtns.length > 0) {
        quickBtns.forEach(function (btn) {
          if (btn.getAttribute('data-set-pos') === String(numVal)) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        });
      }
    }

    function calcPercent(clientX) {
      var rect = viewer.getBoundingClientRect();
      if (!rect.width) return 50;
      var offsetX = clientX - rect.left;
      var percent = (offsetX / rect.width) * 100;
      return Math.max(0, Math.min(100, percent));
    }

    function onPointerDown(e) {
      isDragging = true;
      viewer.classList.add('is-dragging');
      if (viewer.setPointerCapture && e.pointerId) {
        try {
          viewer.setPointerCapture(e.pointerId);
        } catch (err) {}
      }
      updatePosition(calcPercent(e.clientX));
      if (e.cancelable) e.preventDefault();
    }

    function onPointerMove(e) {
      if (!isDragging) return;
      updatePosition(calcPercent(e.clientX));
      if (e.cancelable) e.preventDefault();
    }

    function onPointerUp(e) {
      if (!isDragging) return;
      isDragging = false;
      viewer.classList.remove('is-dragging');
      if (viewer.releasePointerCapture && e.pointerId) {
        try {
          viewer.releasePointerCapture(e.pointerId);
        } catch (err) {}
      }
    }

    if (window.PointerEvent) {
      viewer.addEventListener('pointerdown', onPointerDown);
      viewer.addEventListener('pointermove', onPointerMove);
      viewer.addEventListener('pointerup', onPointerUp);
      viewer.addEventListener('pointercancel', onPointerUp);
    } else {
      viewer.addEventListener('mousedown', function (e) {
        isDragging = true;
        viewer.classList.add('is-dragging');
        updatePosition(calcPercent(e.clientX));
      });
      window.addEventListener('mousemove', function (e) {
        if (isDragging) updatePosition(calcPercent(e.clientX));
      });
      window.addEventListener('mouseup', function () {
        if (isDragging) {
          isDragging = false;
          viewer.classList.remove('is-dragging');
        }
      });
      viewer.addEventListener('touchstart', function (e) {
        if (e.touches && e.touches.length > 0) {
          isDragging = true;
          viewer.classList.add('is-dragging');
          updatePosition(calcPercent(e.touches[0].clientX));
        }
      }, { passive: false });
      viewer.addEventListener('touchmove', function (e) {
        if (isDragging && e.touches && e.touches.length > 0) {
          updatePosition(calcPercent(e.touches[0].clientX));
          if (e.cancelable) e.preventDefault();
        }
      }, { passive: false });
      viewer.addEventListener('touchend', function () {
        isDragging = false;
        viewer.classList.remove('is-dragging');
      });
    }

    if (range) {
      range.addEventListener('input', function () {
        updatePosition(this.value);
      });
      range.addEventListener('change', function () {
        updatePosition(this.value);
      });
    }

    if (quickBtns.length > 0) {
      quickBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          var targetVal = this.getAttribute('data-set-pos');
          updatePosition(targetVal);
        });
      });
    }
  });
});
</script>
