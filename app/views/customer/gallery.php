<?php
use App\Core\View;

$items = $items ?? [];
$categories = $categories ?? [];
$services = $services ?? [];
$activeCategory = $activeCategory ?? 'all';
$activeService = $activeService ?? '';

// Group services by category_id and category_slug for children tags
$servicesByCat = [];
foreach ($services as $svc) {
    $cId = (int)$svc['category_id'];
    $cSlug = '';
    foreach ($categories as $cat) {
        if ((int)$cat['id'] === $cId) {
            $cSlug = $cat['slug'];
            break;
        }
    }
    if ($cSlug) {
        $servicesByCat[$cSlug][] = $svc;
    }
}
?>

<style>
/* ── Parent Category Pills ── */
.gallery-parent-btn {
  border-radius: 30px;
  border: 1.5px solid #e2e8f0;
  background: #ffffff;
  color: #334155;
  font-weight: 600;
  font-size: 13.5px;
  padding: 8px 18px;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
  white-space: nowrap;
}
.gallery-parent-btn:hover {
  border-color: #ffdacf;
  color: #f25b29;
  background: #fff8f5;
  transform: translateY(-1px);
}
.gallery-parent-btn.active {
  border-color: #f25b29 !important;
  background: #f25b29 !important;
  color: #ffffff !important;
  box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35) !important;
  font-weight: 700;
}

/* ── Children Sub-Service Tags ── */
.sub-services-wrapper {
  background: #f8fafc;
  border: 1px solid #edf2f7;
  border-radius: 16px;
  padding: 14px 18px;
  max-width: 1040px;
  margin: 0 auto 36px auto;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
  animation: fadeInSubTags 0.25s ease-out;
}
@keyframes fadeInSubTags {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
.sub-services-eyebrow {
  font-size: 11.5px;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.sub-services-eyebrow i {
  color: #f25b29;
}
.gallery-child-btn {
  border-radius: 20px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  font-weight: 600;
  font-size: 12.5px;
  padding: 6px 14px;
  transition: all 0.2s ease;
  cursor: pointer;
  white-space: nowrap;
}
.gallery-child-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #94a3b8;
}
.gallery-child-btn.active {
  background: #0f172a !important;
  border-color: #0f172a !important;
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25) !important;
  font-weight: 700;
}
.gallery-child-btn.active .child-check-icon {
  display: inline-block !important;
}
.child-check-icon {
  display: none;
  margin-right: 4px;
  color: #f25b29;
}

/* ── Card Styling ── */
.gallery-card {
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
  transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}
.gallery-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.1);
  border-color: #ffdacf;
}
.active-service-highlight {
  border: 2px solid #f25b29 !important;
  box-shadow: 0 8px 26px rgba(242, 91, 41, 0.18) !important;
}
</style>

<div class="container py-5 my-3">
  <!-- Page Header -->
  <div class="page_heading text-center mb-4">
    <h6 style="color: #f25b29; font-weight: 700; letter-spacing: 1.2px;">REAL RESULTS & PROVEN QUALITY</h6>
    <h1 class="font-weight-bold" style="font-size: 36px; color: #0b1a2d; letter-spacing: -0.5px;">Before &amp; After Transformations</h1>
    <p class="text-muted" style="max-width: 720px; margin: 0 auto; font-size: 15.5px; line-height: 1.6;">
      Explore real job completions by REFIXEL verified professionals. From mechanized deep cleaning and stubborn stain eradication to pest control, painting, and bespoke repairs.
    </p>
  </div>

  <!-- Row 1: Primary Parent Category Tabs -->
  <div class="d-flex flex-wrap justify-content-center mb-3" style="gap: 10px;" id="parentCategoryTabs">
    <button type="button" 
            class="gallery-parent-btn <?= ($activeCategory === 'all') ? 'active' : '' ?>" 
            data-cat="all">
      <i class="fa fa-th-large mr-1"></i> All Work (<?= count($items) ?>)
    </button>
    <?php foreach ($categories as $cat): 
      $cSlug = View::e($cat['slug']);
      $cName = View::e($cat['name']);
      $isCatActive = ($activeCategory === $cSlug);
    ?>
      <button type="button" 
              class="gallery-parent-btn <?= $isCatActive ? 'active' : '' ?>" 
              data-cat="<?= $cSlug ?>">
        <?= $cName ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Row 2: Secondary / Children Sub-Service Tags (Appears when a parent category is selected) -->
  <div id="subServicesContainer" class="sub-services-wrapper" style="<?= ($activeCategory === 'all') ? 'display: none;' : '' ?>">
    <div class="sub-services-eyebrow">
      <i class="fa fa-filter"></i> <span id="subServicesCategoryName">Specific Services</span>
    </div>
    <div class="d-flex flex-wrap" style="gap: 8px;" id="subServicesTagsList">
      <!-- Populated dynamically via JS or server rendered -->
      <?php if ($activeCategory !== 'all' && !empty($servicesByCat[$activeCategory])): 
        $activeCatServices = $servicesByCat[$activeCategory];
        $isChildAllActive = empty($activeService) || $activeService === 'all';
      ?>
        <button type="button" 
                class="gallery-child-btn <?= $isChildAllActive ? 'active' : '' ?>" 
                data-parent-cat="<?= View::e($activeCategory) ?>" 
                data-svc="all">
          <i class="fa fa-check child-check-icon"></i> All <?= View::e(ucfirst($activeCategory)) ?>
        </button>
        <?php foreach ($activeCatServices as $csvc): 
          $sSlug = View::e($csvc['slug']);
          $sName = View::e($csvc['name']);
          $isThisSvcActive = ($activeService === $sSlug);
        ?>
          <button type="button" 
                  class="gallery-child-btn <?= $isThisSvcActive ? 'active' : '' ?>" 
                  data-parent-cat="<?= View::e($activeCategory) ?>" 
                  data-svc="<?= $sSlug ?>">
            <i class="fa fa-check child-check-icon"></i> <?= $sName ?>
          </button>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Gallery Showcase Grid -->
  <div class="row" id="galleryShowcaseGrid">
    <?php if (empty($items)): ?>
      <div class="col-12 text-center py-5">
        <div class="p-5 bg-light rounded border text-muted">
          <i class="fa fa-picture-o fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
          <h5>No gallery showcases available right now.</h5>
          <p class="small">Check back soon for new before &amp; after transformations.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($items as $item): 
        $catSlug = !empty($item['category_slug']) ? $item['category_slug'] : 'general';
        $svcSlug = !empty($item['service_slug']) ? $item['service_slug'] : 'general';
        $tagLabel = !empty($item['service_name']) ? $item['service_name'] : (!empty($item['category_name']) ? $item['category_name'] : 'Home Service');
        $isSplitImages = !empty($item['before_image']) && !empty($item['after_image']) && ($item['before_image'] !== $item['after_image']);
        $bookLink = !empty($item['service_id']) ? View::url('/book?service_id=' . $item['service_id']) : View::url('/book');
        $isHighlighted = (!empty($activeService) && $svcSlug === $activeService);
      ?>
        <div class="col-lg-6 mb-4 gallery-card-col" 
             data-cat="<?= View::e($catSlug) ?>" 
             data-svc="<?= View::e($svcSlug) ?>">
          <div class="card h-100 gallery-card <?= $isHighlighted ? 'active-service-highlight' : '' ?>">
            <div class="position-relative">
              <?php if ($isSplitImages): ?>
                <!-- Side-by-side Before / After Display -->
                <div class="row no-gutters bg-light">
                  <div class="col-6 position-relative border-right" style="height: 270px;">
                    <img src="<?= View::asset($item['before_image']) ?>" 
                         alt="Before <?= View::e($item['title']) ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.onerror=null; this.src='<?= View::asset('img/08_recent_work_before_after.png') ?>';"
                         loading="lazy">
                    <span class="badge position-absolute text-white font-weight-bold" style="bottom: 12px; left: 12px; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); font-size: 11px; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.5px;">
                      BEFORE
                    </span>
                  </div>
                  <div class="col-6 position-relative" style="height: 270px;">
                    <img src="<?= View::asset($item['after_image']) ?>" 
                         alt="After <?= View::e($item['title']) ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.onerror=null; this.src='<?= View::asset('img/08_recent_work_before_after.png') ?>';"
                         loading="lazy">
                    <span class="badge position-absolute text-white font-weight-bold" style="bottom: 12px; right: 12px; background: rgba(16, 185, 129, 0.95); backdrop-filter: blur(4px); font-size: 11px; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.5px;">
                      AFTER
                    </span>
                  </div>
                </div>
              <?php else: ?>
                <!-- Single Showcase Graphic -->
                <img src="<?= View::asset($item['after_image'] ?? $item['before_image']) ?>" 
                     class="w-100" 
                     alt="<?= View::e($item['title']) ?>" 
                     style="display: block; object-fit: cover; max-height: 320px;"
                     onerror="this.onerror=null; this.src='<?= View::asset('img/08_recent_work_before_after.png') ?>';"
                     loading="lazy">
              <?php endif; ?>

              <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(242, 91, 41, 0.94); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 20px; font-size: 12px;">
                <i class="fa fa-sparkles mr-1"></i><?= View::e($tagLabel) ?>
              </span>
            </div>

            <div class="card-body p-4 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="badge badge-light border text-success font-weight-bold">
                    <i class="fa fa-check-circle"></i> Verified Job Completion
                  </span>
                  <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
                </div>
                <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #0b1a2d; line-height: 1.3;">
                  <?= View::e($item['title']) ?>
                </h4>
                <p class="card-text text-muted" style="font-size: 14px; line-height: 1.6;">
                  Completed by verified Refixel specialists using mechanized equipment, surface-safe descalers, and audited multi-point quality protocols.
                </p>
              </div>

              <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
                <span class="text-warning font-weight-bold">★ 4.9 <small class="text-muted font-weight-normal">(Refixel Verified)</small></span>
                <a href="<?= $bookLink ?>" class="btn btn-sm text-white font-weight-bold px-3 py-1" style="border-radius: 20px; background: #f25b29; box-shadow: 0 2px 8px rgba(242, 91, 41, 0.25);">
                  Book Service &rarr;
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <div id="galleryNoMatches" class="col-12 text-center py-5" style="display: none;">
        <div class="p-5 bg-light rounded border text-muted">
          <i class="fa fa-picture-o fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
          <h5>No transformation photos available for this specific service yet.</h5>
          <p class="small">Please check our other services in this category or view all work.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-5">
    <a href="<?= View::url('/book') ?>" class="btn text-white px-5 py-3 font-weight-bold shadow-sm" style="background:#f25b29; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);">
      Book Your Service Now &rarr;
    </a>
  </div>
</div>

<!-- Category & Children Tag Interactive Hierarchy Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  var parentBtns = document.querySelectorAll('.gallery-parent-btn');
  var subContainer = document.getElementById('subServicesContainer');
  var subTagsList = document.getElementById('subServicesTagsList');
  var subCategoryName = document.getElementById('subServicesCategoryName');
  var cards = document.querySelectorAll('.gallery-card-col');

  // JSON dictionary of services grouped by parent category
  var servicesByCatMap = <?= json_encode($servicesByCat, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var categoriesMap = <?= json_encode(array_column($categories, 'name', 'slug'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

  var currentCat = '<?= addslashes($activeCategory) ?>';
  var currentSvc = '<?= addslashes($activeService) ?>';

  function filterCards(cat, svc) {
    var grid = document.getElementById('galleryShowcaseGrid');
    var noMatches = document.getElementById('galleryNoMatches');
    var matchedCards = [];

    cards.forEach(function(card) {
      var cardCat = card.getAttribute('data-cat');
      var cardSvc = card.getAttribute('data-svc');
      var cardInner = card.querySelector('.gallery-card');

      var catMatches = (cat === 'all' || cardCat === cat);
      var svcMatches = (!svc || svc === 'all' || cardSvc === svc);

      if (catMatches && svcMatches) {
        card.style.display = 'block';
        if (cardInner) {
          if (svc && svc !== 'all') {
            cardInner.classList.add('active-service-highlight');
          } else {
            cardInner.classList.remove('active-service-highlight');
          }
        }
        matchedCards.push(card);
      } else {
        card.style.display = 'none';
        if (cardInner) cardInner.classList.remove('active-service-highlight');
      }
    });

    if (noMatches) {
      noMatches.style.display = (matchedCards.length === 0) ? 'block' : 'none';
    }

    // Reorder DOM so that matchedCards are displayed FIRST
    if (grid && matchedCards.length > 0) {
      matchedCards.forEach(function(c) {
        grid.prepend(c);
      });
    }
  }

  function renderSubTags(catSlug, activeSvcSlug) {
    if (!subContainer || !subTagsList) return;

    if (catSlug === 'all' || !servicesByCatMap[catSlug] || servicesByCatMap[catSlug].length === 0) {
      subContainer.style.display = 'none';
      return;
    }

    var catName = categoriesMap[catSlug] || (catSlug.charAt(0).toUpperCase() + catSlug.slice(1));
    if (subCategoryName) {
      subCategoryName.textContent = catName + ' Sub-Services';
    }

    var catServices = servicesByCatMap[catSlug];
    var isAllActive = (!activeSvcSlug || activeSvcSlug === 'all');

    var html = '<button type="button" class="gallery-child-btn ' + (isAllActive ? 'active' : '') + '" data-parent-cat="' + catSlug + '" data-svc="all">' +
               '<i class="fa fa-check child-check-icon"></i> All ' + catName + '</button>';

    catServices.forEach(function(s) {
      var isThisActive = (activeSvcSlug === s.slug);
      html += '<button type="button" class="gallery-child-btn ' + (isThisActive ? 'active' : '') + '" data-parent-cat="' + catSlug + '" data-svc="' + s.slug + '">' +
              '<i class="fa fa-check child-check-icon"></i> ' + s.name + '</button>';
    });

    subTagsList.innerHTML = html;
    subContainer.style.display = 'block';
  }

  // Handle Parent Category Click
  parentBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      parentBtns.forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');

      currentCat = this.getAttribute('data-cat') || 'all';
      currentSvc = 'all'; // Reset child filter when clicking parent

      renderSubTags(currentCat, currentSvc);
      filterCards(currentCat, currentSvc);
    });
  });

  // Handle Sub-Service Tag Click (delegated event)
  if (subTagsList) {
    subTagsList.addEventListener('click', function(e) {
      var childBtn = e.target.closest('.gallery-child-btn');
      if (!childBtn) return;

      var allChildBtns = subTagsList.querySelectorAll('.gallery-child-btn');
      allChildBtns.forEach(function(b) { b.classList.remove('active'); });
      childBtn.classList.add('active');

      currentSvc = childBtn.getAttribute('data-svc') || 'all';
      filterCards(currentCat, currentSvc);
    });
  }

  // Initial Filter on page load (if navigated with ?category=...&service=...)
  if (currentCat && currentCat !== 'all') {
    renderSubTags(currentCat, currentSvc);
    filterCards(currentCat, currentSvc);
  }
});
</script>
