<?php
use App\Core\View;
$items = $items ?? [];
?>

<div class="container py-5 my-3">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0 mb-4" style="font-size: 14px;">
      <li class="breadcrumb-item"><a href="<?= View::url('/') ?>" style="color:#f25b29;">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Work Showcase & Before / After</li>
    </ol>
  </nav>

  <div class="page_heading text-center mb-4">
    <h6 style="color: #f25b29; font-weight: 600; letter-spacing: 1px;">REAL RESULTS & PROVEN QUALITY</h6>
    <h1 class="font-weight-bold" style="font-size: 36px; color: #1a1a1a;">Before & After Transformations</h1>
    <p class="text-muted" style="max-width: 720px; margin: 0 auto; font-size: 16px;">
      Explore real job completions by REFIXEL verified professionals. From mechanized deep cleaning and stubborn stain eradication to pest control, painting, and bespoke repairs.
    </p>
  </div>

  <?php
  // Extract unique categories or service names for filtering
  $filterCats = [];
  foreach ($items as $item) {
      $catKey = !empty($item['category_slug']) ? $item['category_slug'] : (!empty($item['service_slug']) ? $item['service_slug'] : 'general');
      $catName = !empty($item['category_name']) ? $item['category_name'] : (!empty($item['service_name']) ? $item['service_name'] : 'General');
      $filterCats[$catKey] = $catName;
  }
  ?>

  <!-- Category Filter Pills -->
  <div class="d-flex flex-wrap justify-content-center mb-5" style="gap: 10px;">
    <button type="button" class="btn btn-sm gallery-filter-btn active font-weight-bold px-3 py-2" data-cat="all" style="border-radius: 30px; border: 1.5px solid #f25b29; background: #f25b29; color: #fff; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
      <i class="fa fa-th-large mr-1"></i> All Work (<?= count($items) ?>)
    </button>
    <?php foreach ($filterCats as $catKey => $catName): ?>
      <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="<?= View::e($catKey) ?>" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
        <?= View::e($catName) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="row" id="galleryShowcaseGrid">
    <?php if (empty($items)): ?>
      <div class="col-12 text-center py-5">
        <div class="p-5 bg-light rounded border text-muted">
          <i class="fa fa-picture-o fa-3x mb-3 text-muted" style="opacity: 0.4;"></i>
          <h5>No gallery showcases available right now.</h5>
          <p class="small">Check back soon for new before & after transformations.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($items as $item): 
        $catKey = !empty($item['category_slug']) ? $item['category_slug'] : (!empty($item['service_slug']) ? $item['service_slug'] : 'general');
        $tagLabel = !empty($item['service_name']) ? $item['service_name'] : (!empty($item['category_name']) ? $item['category_name'] : 'Home Service');
        $isSplitImages = !empty($item['before_image']) && !empty($item['after_image']) && ($item['before_image'] !== $item['after_image']);
        $bookLink = !empty($item['service_id']) ? View::url('/book?service_id=' . $item['service_id']) : View::url('/book');
      ?>
        <div class="col-lg-6 mb-4 gallery-card-col" data-cat="<?= View::e($catKey) ?>">
          <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
            <div class="position-relative">
              <?php if ($isSplitImages): ?>
                <!-- Side-by-side Before / After Display -->
                <div class="row no-gutters bg-light">
                  <div class="col-6 position-relative border-right" style="height: 260px;">
                    <img src="<?= View::asset($item['before_image']) ?>" alt="Before <?= View::e($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <span class="badge position-absolute text-white font-weight-bold" style="bottom: 10px; left: 10px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); font-size: 11px; padding: 4px 10px; border-radius: 12px;">
                      BEFORE
                    </span>
                  </div>
                  <div class="col-6 position-relative" style="height: 260px;">
                    <img src="<?= View::asset($item['after_image']) ?>" alt="After <?= View::e($item['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <span class="badge position-absolute text-white font-weight-bold" style="bottom: 10px; right: 10px; background: rgba(16, 185, 129, 0.9); backdrop-filter: blur(4px); font-size: 11px; padding: 4px 10px; border-radius: 12px;">
                      AFTER
                    </span>
                  </div>
                </div>
              <?php else: ?>
                <!-- Single Showcase Graphic (Combined Before & After Banner) -->
                <img src="<?= View::asset($item['after_image'] ?? $item['before_image']) ?>" class="w-100" alt="<?= View::e($item['title']) ?>" style="display: block; object-fit: cover; max-height: 320px;">
              <?php endif; ?>

              <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(242, 91, 41, 0.92); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 20px; font-size: 12px;">
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
                <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">
                  <?= View::e($item['title']) ?>
                </h4>
                <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
                  Completed by verified Refixel certified specialists using professional tools, protective prep, and guaranteed post-service cleanup.
                </p>
              </div>

              <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
                <span class="text-warning font-weight-bold">★ 4.9 <small class="text-muted font-weight-normal">(Refixel Verified)</small></span>
                <a href="<?= $bookLink ?>" class="btn btn-sm btn-outline-brand font-weight-bold px-3 py-1" style="border-radius: 20px; border-color: #f25b29; color: #f25b29;">
                  Book Service &rarr;
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="text-center mt-5">
    <a href="<?= View::url('/book') ?>" class="btn text-white px-5 py-3 font-weight-bold shadow-sm" style="background:#f25b29; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);">
      Book Your Service Now &rarr;
    </a>
  </div>
</div>

<!-- Gallery Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  var filterBtns = document.querySelectorAll('.gallery-filter-btn');
  var cards = document.querySelectorAll('.gallery-card-col');
  if (filterBtns.length && cards.length) {
    filterBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        filterBtns.forEach(function(b) {
          b.classList.remove('active');
          b.style.background = '#fff';
          b.style.color = '#333';
          b.style.borderColor = '#dee2e6';
          b.style.boxShadow = 'none';
        });
        this.classList.add('active');
        this.style.background = '#f25b29';
        this.style.color = '#fff';
        this.style.borderColor = '#f25b29';
        this.style.boxShadow = '0 4px 12px rgba(242, 91, 41, 0.25)';
        
        var cat = this.getAttribute('data-cat');
        cards.forEach(function(card) {
          if (cat === 'all' || card.getAttribute('data-cat') === cat) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  }
});
</script>
