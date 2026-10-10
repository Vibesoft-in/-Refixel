<?php
$svcName = \App\Core\View::e($service['name']);
$cityName = \App\Core\View::e($city);
$price = number_format((float)$service['starting_price'], 0);
$duration = (int)($service['duration_minutes'] ?? 60);
$imgFile = \App\Models\Service::resolveImage($service['slug'] ?? '', $service['image'] ?? null);

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


  <div class="row">
    <!-- Main Service Information -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm mb-4 service-detail-hero-card" style="border-radius: 16px; overflow:hidden;">
        <div class="service-hero-img-wrap">
          <img src="<?= \App\Core\View::asset('img/' . $imgFile) ?>?v=20261009"
               alt="<?= $svcName ?>"
               class="service-detail-hero-img"
               onerror="this.onerror=null; this.src='<?= \App\Core\View::asset('img/refixel-cleaning.jpg') ?>';">
        </div>

        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center mb-2">
            <span class="badge mr-2 font-weight-bold px-2.5 py-1.5" style="background:#fff3ec; color:#f25b29; border: 1px solid #ffdacf;">Verified Service</span>
            <span class="text-muted small"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</span>
          </div>

          <h1 class="font-weight-bold mb-2 service-detail-title">
            <?= $svcName ?>
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

          $allShowcasePool = [
              'balcony' => [
                  'key'      => 'balcony',
                  'before'   => 'transformations/balcony-before.jpg',
                  'after'    => 'transformations/balcony-after.jpg',
                  'category' => 'Balcony Jet Wash',
                  'badge'    => '100% Moss Eradicated',
                  'pill'     => 'BALCONY RESTORATION',
                  'title'    => 'Balcony Deep Pressure Washing & Railing Restoration',
                  'desc'     => 'Outdoor algae, bird droppings, and weather grime removed with 160-bar pressure jet and glass descaling.',
              ],
              'homeclean' => [
                  'key'      => 'homeclean',
                  'before'   => 'transformations/homeclean-before.jpg',
                  'after'    => 'transformations/homeclean-after.jpg',
                  'category' => 'Full Home Clean',
                  'badge'    => 'Mirror Polish Finish',
                  'pill'     => 'FULL HOME CARE',
                  'title'    => 'Professional Full Home Deep Scrubbing & Buffing',
                  'desc'     => 'Single-disc floor buffing, stubborn grime removal, and hospital-grade multi-room sanitization.',
              ],
              'kitchen' => [
                  'key'      => 'kitchen',
                  'before'   => 'transformations/kitchen-before.jpg',
                  'after'    => 'transformations/kitchen-after.jpg',
                  'category' => 'Kitchen Deep Clean',
                  'badge'    => 'Zero Burnt Grease',
                  'pill'     => 'KITCHEN RESTORATION',
                  'title'    => 'Kitchen Stove, Chimney & Counter Degreasing',
                  'desc'     => 'Heavy cooking grease, sticky oil crusts, and stained tile grout stripped with eco-degreasers.',
              ],
              'bathroom' => [
                  'key'      => 'bathroom',
                  'before'   => 'transformations/bathroom-before.jpg',
                  'after'    => 'transformations/bathroom-after.jpg',
                  'category' => 'Bathroom Deep Clean',
                  'badge'    => 'Crystal Clear Glass',
                  'pill'     => 'BATHROOM HYGIENE',
                  'title'    => 'Shower Glass & Hard-Water Calcium Descaling',
                  'desc'     => 'Tough white water stains, limescale, and soap scum dissolved back to pristine crystal transparency.',
              ],
              'sofa' => [
                  'key'      => 'sofa',
                  'before'   => 'transformations/sofa-before.jpg',
                  'after'    => 'transformations/sofa-after.jpg',
                  'category' => 'Sofa Shampooing',
                  'badge'    => '100% Stains Extracted',
                  'pill'     => 'SOFA RESTORATION',
                  'title'    => 'Fabric Sofa Deep Shampoo & Extraction',
                  'desc'     => 'Deep foam injection extracting dark drink spills, sweat stains, and dust mites from fabric upholstery.',
              ],
              'office' => [
                  'key'      => 'office',
                  'before'   => 'transformations/office-before.jpg',
                  'after'    => 'transformations/office-after.jpg',
                  'category' => 'Office Sanitization',
                  'badge'    => '100% Sanitized & Buffed',
                  'pill'     => 'COMMERCIAL SPACE',
                  'title'    => 'Corporate Office Space Deep Sanitization',
                  'desc'     => 'Deep scrubbing of workstations, polished anti-static flooring, and streak-free partition glass.',
              ],
              'ac' => [
                  'key'      => 'ac',
                  'before'   => 'transformations/ac-before.jpg',
                  'after'    => 'transformations/ac-after.jpg',
                  'category' => 'AC Jet Service',
                  'badge'    => '2x Airflow Boosted',
                  'pill'     => 'AC RESTORATION',
                  'title'    => 'Split AC Deep High-Pressure Jet Wash',
                  'desc'     => 'Cooling coil mould, fine dust, and foul smell eradicated with antimicrobial pressure wash.',
              ],
              'painting' => [
                  'key'      => 'painting',
                  'before'   => 'transformations/painting-before.jpg',
                  'after'    => 'transformations/painting-after.jpg',
                  'category' => 'Interior Painting',
                  'badge'    => 'Royal Smooth Finish',
                  'pill'     => 'WALL MAKEOVER',
                  'title'    => 'Wall Seepage Waterproofing & Royal Emulsion',
                  'desc'     => 'Damp wall treatment, dustless wall leveling, and 3 coats of washable royal emulsion paint.',
              ],
              'pest' => [
                  'key'      => 'pest',
                  'before'   => 'transformations/pest-before.jpg',
                  'after'    => 'transformations/pest-after.jpg',
                  'category' => 'Pest Eradication',
                  'badge'    => '100% Nest Eradication',
                  'pill'     => 'PEST CONTROL',
                  'title'    => 'Kitchen Under-Counter Cockroach Nest Eradication',
                  'desc'     => '100% nest elimination under kitchen cabinets with certified odorless gel-baiting technology.',
              ],
              'plumbing' => [
                  'key'      => 'plumbing',
                  'before'   => 'transformations/plumbing-before.jpg',
                  'after'    => 'transformations/plumbing-after.jpg',
                  'category' => 'Plumbing Repair',
                  'badge'    => '100% Leak-Proof',
                  'pill'     => 'PLUMBING CARE',
                  'title'    => 'Under-Sink Pipe Leak & Drainage Replacement',
                  'desc'     => 'Dripping joint repair, new heavy-duty anti-drip P-trap installation, and under-sink organization.',
              ],
              'carpenter' => [
                  'key'      => 'carpenter',
                  'before'   => 'transformations/carpenter-before.jpg',
                  'after'    => 'transformations/carpenter-after.jpg',
                  'category' => 'Furniture Assembly',
                  'badge'    => 'Precision Alignment',
                  'pill'     => 'CARPENTRY CARE',
                  'title'    => 'Flatpack Wardrobe Assembly & Hinge Overhaul',
                  'desc'     => 'Laser alignment, soft-close hydraulic hinge installation, and scratch restoration on furniture.',
              ],
              'electrical' => [
                  'key'      => 'electrical',
                  'before'   => 'transformations/electrical-before.jpg',
                  'after'    => 'transformations/electrical-after.jpg',
                  'category' => 'Electrical Overhaul',
                  'badge'    => 'Certified Safe & Tested',
                  'pill'     => 'ELECTRICAL SAFETY',
                  'title'    => 'MCB Distribution Board & Switchboard Overhaul',
                  'desc'     => 'Tangled risky conduits replaced with modular switchboards and certified overload trip breakers.',
              ],
              'appliance' => [
                  'key'      => 'appliance',
                  'before'   => 'transformations/appliance-before.jpg',
                  'after'    => 'transformations/appliance-after.jpg',
                  'category' => 'Appliance Repair',
                  'badge'    => '100% Repaired & Tested',
                  'pill'     => 'APPLIANCE FIX',
                  'title'    => 'Washing Machine & Appliance Diagnostic Repair',
                  'desc'     => 'Motor drive belt repair, circuit board error clearance, drainage pump sealing, and live cycle testing.',
              ],
              'ceiling' => [
                  'key'      => 'ceiling',
                  'before'   => 'transformations/ceiling-before.jpg',
                  'after'    => 'transformations/ceiling-after.jpg',
                  'category' => 'Fall Ceiling Design',
                  'badge'    => '5-Yr Workmanship Guarantee',
                  'pill'     => 'CEILING ARCHITECTURE',
                  'title'    => 'Designer POP False Ceiling & Ambient Cove Lights',
                  'desc'     => 'Saint-Gobain gypsum channel framing with warm concealed LED ambient cove light troughs.',
              ],
              'ceilingrepair' => [
                  'key'      => 'ceilingrepair',
                  'before'   => 'transformations/ceilingrepair-before.jpg',
                  'after'    => 'transformations/ceilingrepair-after.jpg',
                  'category' => 'Ceiling Restoration',
                  'badge'    => 'Crack-Free & Seamless',
                  'pill'     => 'CEILING REPAIR',
                  'title'    => 'POP Gypsum Ceiling Damp Crack Restoration',
                  'desc'     => 'Sagging damp ceiling panel re-framing, seamless crack taping, and spot profile light trough cutting.',
              ],
          ];

          // Determine primary showcase key based on service keywords
          $primaryKey = 'homeclean';
          if (preg_match('/\b(balcony|terrace|patio)\b/i', $checkText)) {
              $primaryKey = 'balcony';
          } elseif (str_contains($svcSlugLower, 'office-cleaning') || preg_match('/\b(office|commercial|workplace)\b/i', $checkText)) {
              $primaryKey = 'office';
          } elseif (preg_match('/\b(kitchen|stove|chimney|hob|degrease|cooktop)\b/i', $checkText)) {
              $primaryKey = 'kitchen';
          } elseif (preg_match('/\b(bath|bathroom|toilet|shower|washroom|descale|commode)\b/i', $checkText)) {
              $primaryKey = 'bathroom';
          } elseif (preg_match('/\b(sofa|upholstery|carpet|couch|mattress|cushion)\b/i', $checkText)) {
              $primaryKey = 'sofa';
          } elseif (str_contains($svcSlugLower, 'repair-modification') || preg_match('/\b(cove light|repair-modification|ceiling repair)\b/i', $checkText)) {
              $primaryKey = 'ceilingrepair';
          } elseif (preg_match('/\b(ceiling|pop|gypsum|false-ceiling|cove)\b/i', $checkText)) {
              $primaryKey = 'ceiling';
          } elseif (preg_match('/\b(paint|painting|painter|whitewash|wall-painting|wall makeover)\b/i', $checkText)) {
              $primaryKey = 'painting';
          } elseif (preg_match('/\b(pest|cockroach|termite|bedbug|bed-bug|rodent|fumigation)\b/i', $checkText)) {
              $primaryKey = 'pest';
          } elseif (preg_match('/\b(plumb|plumbing|plumber|leak|pipe|tap|drain|sink|basin)\b/i', $checkText)) {
              $primaryKey = 'plumbing';
          } elseif (preg_match('/\b(carpent|carpenter|carpentry|furniture|wood|cabinet|door|wardrobe)\b/i', $checkText)) {
              $primaryKey = 'carpenter';
          } elseif (preg_match('/\b(ac|air conditioner|air-conditioner|air conditioning|split ac|hvac)\b/i', $checkText) || str_contains($checkText, 'ac-jet')) {
              $primaryKey = 'ac';
          } elseif (preg_match('/\b(electric|electrical|electrician|switch|switchboard|mcb|fan|wiring)\b/i', $checkText)) {
              $primaryKey = 'electrical';
          } elseif (preg_match('/\b(appliance|washing machine|refrigerator|fridge|microwave)\b/i', $checkText)) {
              $primaryKey = 'appliance';
          }

          // Related showcase sequences
          $relatedMap = [
              'balcony'       => ['homeclean', 'kitchen', 'bathroom'],
              'office'        => ['homeclean', 'balcony', 'sofa'],
              'homeclean'     => ['kitchen', 'bathroom', 'balcony'],
              'kitchen'       => ['homeclean', 'bathroom', 'pest'],
              'bathroom'      => ['homeclean', 'kitchen', 'balcony'],
              'sofa'          => ['homeclean', 'office', 'balcony'],
              'ceiling'       => ['ceilingrepair', 'painting', 'electrical'],
              'ceilingrepair' => ['ceiling', 'painting', 'electrical'],
              'painting'      => ['ceiling', 'carpenter', 'homeclean'],
              'pest'          => ['kitchen', 'homeclean', 'bathroom'],
              'plumbing'      => ['bathroom', 'kitchen', 'appliance'],
              'carpenter'     => ['painting', 'ceiling', 'homeclean'],
              'ac'            => ['electrical', 'appliance', 'homeclean'],
              'electrical'    => ['ac', 'appliance', 'ceiling'],
              'appliance'     => ['electrical', 'plumbing', 'ac'],
          ];

          $showcaseOrder = array_merge([$primaryKey], $relatedMap[$primaryKey] ?? ['homeclean', 'kitchen', 'bathroom']);
          $showcaseSlides = [];
          foreach ($showcaseOrder as $k) {
              if (isset($allShowcasePool[$k])) {
                  $showcaseSlides[] = $allShowcasePool[$k];
              }
          }
          ?>
          <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h3 class="font-weight-bold mb-0" style="font-size: 22px;">Transformation Showcase</h3>
              <span class="badge px-3 py-1 font-weight-bold" style="background:#fff3ec; color:#f25b29; border: 1px solid #ffdacf; font-size:12px;">
                <i class="fa fa-sparkles mr-1"></i> Verified Results
              </span>
            </div>
            <p class="text-muted small mb-3">Slide to inspect real Before &amp; After transformations completed by Refixel verified specialists.</p>

            <!-- Multi-Transformation Carousel Wrapper with Next/Prev & Auto-Scroll -->
            <div class="ba_carousel_wrapper" id="baTransformationsCarousel">
              <div class="ba_carousel_slides">
                <?php foreach ($showcaseSlides as $sIdx => $slide): ?>
                  <div class="ba_carousel_slide <?= $sIdx === 0 ? 'active' : '' ?>" data-slide-index="<?= $sIdx ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h4 class="font-weight-bold mb-0 text-truncate mr-2" style="font-size: 16.5px; color: #0b1a2d; max-width: 65%;">
                        <?= \App\Core\View::e($slide['title']) ?>
                      </h4>
                      <span class="badge px-2.5 py-1 font-weight-bold" style="background:#f1f5f9; color:#334155; border: 1px solid #cbd5e1; font-size:11px; white-space:nowrap;">
                        <?= \App\Core\View::e($slide['pill']) ?>
                      </span>
                    </div>
                    <p class="text-muted small mb-2" style="font-size: 13px; line-height: 1.5;"><?= \App\Core\View::e($slide['desc']) ?></p>

                    <div class="ba_service_showcase_wrap border rounded overflow-hidden shadow-sm" style="border-radius: 14px; background: #fff;">
                      <div class="position-relative">
                        <!-- Interactive Slideable Viewer -->
                        <div class="ba_compare_viewer" style="--pos: 50%; aspect-ratio: 16 / 9; border-radius: 14px 14px 0 0;">
                          <!-- Base Image: After -->
                          <img src="<?= \App\Core\View::asset('img/' . $slide['after']) ?>" alt="<?= \App\Core\View::e($slide['title']) ?> After" class="ba_img_base" loading="lazy">
                          <span class="ba_badge_pill ba_badge_after">AFTER</span>

                          <!-- Clipped Overlay: Before -->
                          <div class="ba_layer_before">
                            <img src="<?= \App\Core\View::asset('img/' . $slide['before']) ?>" alt="<?= \App\Core\View::e($slide['title']) ?> Before" class="ba_img_clip" loading="lazy">
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
                          <span class="ba_service_pill"><?= \App\Core\View::e($slide['pill']) ?></span>
                          <span class="ba_hint_pill"><i class="fa fa-arrows-h"></i> Slide</span>

                          <input type="range" min="0" max="100" value="50" class="ba_range_input" aria-label="Slide to compare before and after">
                        </div>

                        <!-- Left & Right Carousel Slide Buttons -->
                        <button type="button" class="ba_carousel_nav_btn ba_carousel_prev" aria-label="Previous transformation">
                          <i class="fa fa-chevron-left"></i>
                        </button>
                        <button type="button" class="ba_carousel_nav_btn ba_carousel_next" aria-label="Next transformation">
                          <i class="fa fa-chevron-right"></i>
                        </button>
                      </div>

                      <!-- Quick Snap Controls & Bottom Info (100% Mobile Responsive) -->
                      <div class="p-2 p-md-3 bg-light border-top ba_bottom_controls">
                        <div class="ba_controls_row d-flex justify-content-between align-items-center w-100">
                          <div class="ba_quick_controls m-0">
                            <button type="button" class="ba_quick_btn" data-set-pos="100">Before</button>
                            <button type="button" class="ba_quick_btn active" data-set-pos="50">Split</button>
                            <button type="button" class="ba_quick_btn" data-set-pos="0">After</button>
                          </div>
                          <span class="badge badge-success ba_verified_badge font-weight-bold">
                            <i class="fa fa-check-circle mr-1"></i> <?= \App\Core\View::e($slide['badge']) ?>
                          </span>
                        </div>
                        <div class="ba_gallery_link_row w-100 text-center text-md-right mt-2 mt-md-0">
                          <a href="<?= \App\Core\View::url('/gallery?category=' . urlencode($category['slug'] ?? 'cleaning') . '&service=' . urlencode($service['slug'] ?? '')) ?>" class="btn btn-sm btn-outline-dark font-weight-bold ba_gallery_btn">
                            View Full Gallery &rarr;
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Carousel Indicators (Dots & Slide Count) -->
              <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                <div class="ba_carousel_dots d-flex align-items-center" style="gap: 6px;">
                  <?php foreach ($showcaseSlides as $sIdx => $slide): ?>
                    <button type="button" class="ba_carousel_dot <?= $sIdx === 0 ? 'active' : '' ?>" data-to-slide="<?= $sIdx ?>" aria-label="Go to slide <?= $sIdx + 1 ?>"></button>
                  <?php endforeach; ?>
                </div>
                <span class="text-muted small ba_slide_counter" style="font-size: 11.5px; font-weight: 600;">
                  <span class="ba_current_slide_num">1</span> / <?= count($showcaseSlides) ?> Transformations
                </span>
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

<!-- Mobile Fixed Quick Book Taskbar (Sticky right above bottom navigation bar on mobile) -->
<div class="service-mobile-book-bar d-lg-none">
  <div class="container d-flex align-items-center justify-content-between px-3">
    <div class="d-flex flex-column">
      <span class="smb-label">Starting Price</span>
      <span class="smb-price">₹<?= $price ?></span>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hi%20Refixel%2C%20I%20would%20like%20to%20book%20<?= urlencode($service['name']) ?>%20in%20<?= urlencode($city) ?>" target="_blank" class="btn btn-outline-success smb-wa-btn" aria-label="Book on WhatsApp" title="WhatsApp Enquiry">
        <i class="fa fa-whatsapp"></i>
      </a>
      <a href="<?= \App\Core\View::url('/book?service_id=' . (int)$service['id'] . '&city=' . urlencode($city)) ?>" class="btn text-white font-weight-bold smb-book-btn">
        Book Now &rarr;
      </a>
    </div>
  </div>
</div>

<style>
/* Service Detail Title Responsive Typography (Fits in max 2 lines on phone view) */
.service-detail-title {
  font-size: 28px;
  color: #0b1a2d;
  line-height: 1.25;
  letter-spacing: -0.4px;
}
@media (max-width: 576px) {
  .service-detail-title {
    font-size: 20px !important;
    line-height: 1.25 !important;
    margin-bottom: 6px !important;
  }
  .service-detail-hero-card .lead {
    font-size: 13.5px !important;
    line-height: 1.5 !important;
    margin-bottom: 14px !important;
  }
  .service-detail-hero-card .card-body {
    padding: 16px !important;
  }
}

/* Mobile Sticky Action Bar (Sits immediately above .bottomBarNavbar on phone view) */
.service-mobile-book-bar {
  display: none;
}
@media (max-width: 991px) {
  .service-mobile-book-bar {
    display: flex;
    position: fixed;
    bottom: 60px; /* Sits right above .bottomBarNavbar (height: 60px) */
    left: 0;
    right: 0;
    z-index: 1045;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    box-shadow: 0 -4px 18px rgba(15, 23, 42, 0.08);
    padding: 8px 0;
  }
  .smb-label {
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .smb-price {
    font-size: 20px;
    font-weight: 800;
    color: #f25b29;
    line-height: 1.1;
  }
  .smb-wa-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    color: #25d366;
    border-color: #25d366;
    font-size: 18px;
  }
  .smb-wa-btn:hover {
    background: #25d366;
    color: #fff;
  }
  .smb-book-btn {
    background: #f25b29;
    border-radius: 24px;
    padding: 8px 22px;
    font-size: 14px;
    box-shadow: 0 3px 10px rgba(242, 91, 41, 0.35);
  }
  /* Extra bottom padding on main container so sticky bar doesn't obstruct content */
  .container.py-5.my-3 {
    padding-bottom: 130px !important;
  }
}

/* Multi-Transformation Carousel & Responsive Controls */
.ba_carousel_wrapper {
  position: relative;
}
.ba_carousel_slide {
  display: none;
}
.ba_carousel_slide.active {
  display: block;
  animation: baFadeSlide 0.3s ease;
}
@keyframes baFadeSlide {
  from { opacity: 0; transform: translateY(3px); }
  to { opacity: 1; transform: translateY(0); }
}
.ba_carousel_nav_btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(0,0,0,0.12);
  box-shadow: 0 4px 14px rgba(0,0,0,0.2);
  color: #0b1a2d;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 25;
  transition: all 0.2s ease;
  font-size: 14px;
}
.ba_carousel_nav_btn:hover {
  background: #f25b29;
  color: #fff;
  transform: translateY(-50%) scale(1.08);
}
.ba_carousel_prev {
  left: 12px;
}
.ba_carousel_next {
  right: 12px;
}
.ba_carousel_dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #cbd5e1;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: all 0.25s ease;
}
.ba_carousel_dot.active {
  width: 24px;
  border-radius: 10px;
  background: #f25b29;
}
.ba_bottom_controls {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
}
.ba_verified_badge {
  background: #10b981 !important;
  color: #fff !important;
  font-size: 11px !important;
  padding: 4px 10px !important;
  border-radius: 20px !important;
  white-space: nowrap !important;
}
.ba_gallery_btn {
  border-radius: 20px;
  white-space: nowrap;
}

@media (max-width: 576px) {
  .ba_carousel_nav_btn {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
  .ba_carousel_prev {
    left: 8px;
  }
  .ba_carousel_next {
    right: 8px;
  }
  .ba_bottom_controls {
    flex-direction: column;
    align-items: stretch;
    padding: 8px 10px !important;
  }
  .ba_controls_row {
    margin-bottom: 8px;
  }
  .ba_quick_controls {
    min-width: unset !important;
  }
  .ba_quick_btn {
    padding: 3px 8px !important;
    font-size: 11px !important;
  }
  .ba_verified_badge {
    font-size: 9.5px !important;
    padding: 3px 7px !important;
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .ba_gallery_link_row {
    width: 100%;
  }
  .ba_gallery_btn {
    width: 100%;
    display: block;
    text-align: center;
    font-size: 12px !important;
    padding: 6px 12px !important;
  }
}
</style>

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

  // Multi-Transformation Carousel Controller
  var carouselWrap = document.getElementById('baTransformationsCarousel');
  if (carouselWrap) {
    var slides = carouselWrap.querySelectorAll('.ba_carousel_slide');
    var dots = carouselWrap.querySelectorAll('.ba_carousel_dot');
    var currentNumEl = carouselWrap.querySelector('.ba_current_slide_num');
    var totalSlides = slides.length;
    var currentSlideIdx = 0;
    var autoPlayTimer = null;
    var isUserInteracting = false;

    function goToSlide(idx) {
      if (totalSlides <= 1) return;
      currentSlideIdx = (idx + totalSlides) % totalSlides;

      slides.forEach(function (slide, sIndex) {
        if (sIndex === currentSlideIdx) {
          slide.classList.add('active');
        } else {
          slide.classList.remove('active');
        }
      });

      dots.forEach(function (dot, dIndex) {
        if (dIndex === currentSlideIdx) {
          dot.classList.add('active');
        } else {
          dot.classList.remove('active');
        }
      });

      if (currentNumEl) {
        currentNumEl.textContent = String(currentSlideIdx + 1);
      }
    }

    function startAutoPlay() {
      stopAutoPlay();
      if (totalSlides > 1) {
        autoPlayTimer = setInterval(function () {
          if (!isUserInteracting) {
            goToSlide(currentSlideIdx + 1);
          }
        }, 6000);
      }
    }

    function stopAutoPlay() {
      if (autoPlayTimer) {
        clearInterval(autoPlayTimer);
        autoPlayTimer = null;
      }
    }

    carouselWrap.querySelectorAll('.ba_carousel_prev').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        isUserInteracting = true;
        goToSlide(currentSlideIdx - 1);
        setTimeout(function () { isUserInteracting = false; }, 4000);
      });
    });

    carouselWrap.querySelectorAll('.ba_carousel_next').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        isUserInteracting = true;
        goToSlide(currentSlideIdx + 1);
        setTimeout(function () { isUserInteracting = false; }, 4000);
      });
    });

    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var to = parseInt(this.getAttribute('data-to-slide'), 10);
        isUserInteracting = true;
        goToSlide(to);
        setTimeout(function () { isUserInteracting = false; }, 4000);
      });
    });

    carouselWrap.addEventListener('mouseenter', function () { isUserInteracting = true; });
    carouselWrap.addEventListener('mouseleave', function () { isUserInteracting = false; });
    carouselWrap.addEventListener('touchstart', function () { isUserInteracting = true; }, { passive: true });
    carouselWrap.addEventListener('touchend', function () {
      setTimeout(function () { isUserInteracting = false; }, 4000);
    });

    startAutoPlay();
  }
});
</script>
