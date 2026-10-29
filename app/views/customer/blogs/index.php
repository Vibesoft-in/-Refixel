<?php
use App\Core\View;
?>
<section class="blog-hero-section py-5" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-bottom: 1px solid #e2e8f0;">
  <div class="container text-center">
    <div class="badge px-3 py-2 text-uppercase mb-2" style="background: rgba(242, 91, 41, 0.1); color: #f25b29; font-weight: 700; letter-spacing: 1px; border-radius: 30px;">
      Verified Engineering & Field Insights
    </div>
    <h1 class="font-weight-bold display-5 mb-3" style="color: #1a1a1a; font-size: clamp(28px, 4vw, 42px);">
      The REFIXEL Knowledge Journal
    </h1>
    <p class="text-muted mx-auto" style="max-width: 720px; font-size: 16px; line-height: 1.6;">
      Practical home care checklists, troubleshooting guides, and real client case studies curated by Refixel founders <strong>Shanu Kumar</strong>, <strong>Manish Kumar</strong> &amp; <strong>Aakash Kumar</strong> together with our certified technicians across <strong>Kashipur, Jaspur &amp; Thakurdwara</strong>.
    </p>

    <!-- Search & Filter Bar -->
    <div class="row justify-content-center mt-4">
      <div class="col-md-8 col-lg-6">
        <form action="<?= View::url('/blogs') ?>" method="GET" class="d-flex shadow-sm bg-white p-2" style="border-radius: 50px; border: 1px solid #cbd5e1;">
          <?php if (!empty($selectedCat) && $selectedCat !== 'all'): ?>
            <input type="hidden" name="category" value="<?= View::e($selectedCat) ?>">
          <?php endif; ?>
          <input type="text" name="q" value="<?= View::e($searchQuery) ?>" class="form-control border-0 shadow-none px-3" placeholder="Search guides (e.g. AC Jet Wash, Tank Cleaning, Wiring)..." style="border-radius: 50px;">
          <button type="submit" class="btn px-4 text-white font-weight-bold" style="background: #f25b29; border-radius: 50px;">
            <i class="fa fa-search mr-1"></i> Search
          </button>
        </form>
      </div>
    </div>

    <!-- Category Pills -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4 pt-2">
      <?php
      $categoriesList = [
        'all'                 => 'All Articles',
        'case-study'          => '⭐ Real Case Studies',
        'cleaning'            => 'Deep Cleaning',
        'ac-services'         => 'AC Repair & Jet Wash',
        'plumbers'            => 'Plumbing & Water Tanks',
        'electrician'         => 'Electrical Safety',
        'painting-services'   => 'Painting & Damp Proofing',
        'pest-control'        => 'Pest Control',
        'fall-ceiling-services' => 'False Ceiling',
      ];
      ?>
      <?php foreach ($categoriesList as $catKey => $catLabel): ?>
        <?php $isActive = ($selectedCat === $catKey || (empty($selectedCat) && $catKey === 'all')); ?>
        <a href="<?= View::url('/blogs' . ($catKey !== 'all' ? '?category=' . $catKey : '')) ?>" 
           class="btn btn-sm m-1 font-weight-bold" 
           style="border-radius: 20px; transition: all 0.2s; <?= $isActive ? 'background: #f25b29; color: #fff; border: 1px solid #f25b29;' : 'background: #fff; color: #4b5563; border: 1px solid #cbd5e1;' ?>">
          <?= $catLabel ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Main Blog Articles Grid -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
      <div>
        <h2 class="h4 font-weight-bold mb-1" style="color: #1a202c;">
          <?php if (!empty($searchQuery)): ?>
            Search Results for "<?= View::e($searchQuery) ?>" (<?= count($posts) ?>)
          <?php elseif ($selectedCat === 'case-study'): ?>
            Client Case Studies & Real Home Transformations (<?= count($posts) ?>)
          <?php elseif ($selectedCat !== 'all'): ?>
            <?= View::e(ucwords(str_replace('-', ' ', $selectedCat))) ?> Articles (<?= count($posts) ?>)
          <?php else: ?>
            All Published Guides & Case Studies (<?= count($posts) ?>)
          <?php endif; ?>
        </h2>
        <p class="text-muted small mb-0">Actionable advice for homeowners in Kashipur, Jaspur &amp; Thakurdwara.</p>
      </div>

      <!-- City Quick Filter -->
      <div class="mt-3 mt-md-0 d-flex align-items-center">
        <label class="small text-muted font-weight-bold mr-2 mb-0">City:</label>
        <div class="btn-group btn-group-sm">
          <a href="<?= View::url('/blogs' . (!empty($selectedCat) && $selectedCat !== 'all' ? '?category=' . $selectedCat : '')) ?>" 
             class="btn <?= empty($selectedCity) || $selectedCity === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
          <a href="<?= View::url('/blogs?city=kashipur' . (!empty($selectedCat) && $selectedCat !== 'all' ? '&category=' . $selectedCat : '')) ?>" 
             class="btn <?= $selectedCity === 'kashipur' ? 'btn-dark' : 'btn-outline-secondary' ?>">Kashipur</a>
          <a href="<?= View::url('/blogs?city=jaspur' . (!empty($selectedCat) && $selectedCat !== 'all' ? '&category=' . $selectedCat : '')) ?>" 
             class="btn <?= $selectedCity === 'jaspur' ? 'btn-dark' : 'btn-outline-secondary' ?>">Jaspur</a>
          <a href="<?= View::url('/blogs?city=thakurdwara' . (!empty($selectedCat) && $selectedCat !== 'all' ? '&category=' . $selectedCat : '')) ?>" 
             class="btn <?= $selectedCity === 'thakurdwara' ? 'btn-dark' : 'btn-outline-secondary' ?>">Thakurdwara</a>
        </div>
      </div>
    </div>

    <?php if (empty($posts)): ?>
      <div class="text-center py-5 my-4">
        <div class="mb-3 text-muted" style="font-size: 48px;"><i class="fa fa-folder-open-o"></i></div>
        <h4 class="font-weight-bold text-dark">No Articles Found</h4>
        <p class="text-muted">No guides matched your current filters. Try searching for something else or clear filters.</p>
        <a href="<?= View::url('/blogs') ?>" class="btn btn-primary mt-2" style="background: #f25b29; border-color: #f25b29;">View All Articles</a>
      </div>
    <?php else: ?>
      <div class="row">
        <?php foreach ($posts as $post): ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm blog-card" style="border-radius: 16px; overflow: hidden; transition: transform 0.25s ease, box-shadow 0.25s ease;">
              <div class="position-relative" style="height: 220px; overflow: hidden; background: #f1f5f9;">
                <img src="<?= View::asset('img/' . $post['image']) ?>" 
                     alt="<?= View::e($post['title']) ?>" 
                     class="w-100 h-100" 
                     style="object-fit: cover; transition: transform 0.35s ease;"
                     onerror="this.src='<?= View::asset('img/Full-home-clean.jpg') ?>';">
                
                <div class="position-absolute d-flex justify-content-between w-100 px-3" style="top: 12px; left: 0;">
                  <?php if (!empty($post['is_case_study'])): ?>
                    <span class="badge px-3 py-1 font-weight-bold" style="background: #f25b29; color: #fff; font-size: 11px; border-radius: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                      ⭐ CASE STUDY
                    </span>
                  <?php else: ?>
                    <span class="badge px-3 py-1 font-weight-bold" style="background: #1e293b; color: #fff; font-size: 11px; border-radius: 20px;">
                      <?= View::e($post['category']) ?>
                    </span>
                  <?php endif; ?>

                  <span class="badge px-2 py-1 font-weight-bold bg-white text-dark shadow-sm" style="font-size: 11px; border-radius: 20px;">
                    <i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($post['city']) ?>
                  </span>
                </div>
              </div>

              <div class="card-body d-flex flex-column p-4">
                <div class="d-flex align-items-center text-muted small mb-2">
                  <span><i class="fa fa-calendar-o mr-1"></i><?= date('M d, Y', strtotime($post['published_at'])) ?></span>
                  <span class="mx-2">•</span>
                  <span><i class="fa fa-clock-o mr-1"></i><?= View::e($post['reading_time']) ?></span>
                </div>

                <h3 class="card-title font-weight-bold mb-2" style="font-size: 18px; line-height: 1.4;">
                  <a href="<?= View::url('/blogs/' . $post['slug']) ?>" class="text-dark text-decoration-none hover-orange">
                    <?= View::e($post['title']) ?>
                  </a>
                </h3>

                <p class="card-text text-muted small flex-grow-1" style="line-height: 1.6;">
                  <?= View::e($post['excerpt']) ?>
                </p>

                <div class="pt-3 border-top mt-3 d-flex align-items-center justify-content-between">
                  <div class="small text-muted font-weight-semibold">
                    <i class="fa fa-user-circle-o mr-1 text-primary"></i> <?= View::e($post['author']) ?>
                  </div>
                  <a href="<?= View::url('/blogs/' . $post['slug']) ?>" class="font-weight-bold small text-decoration-none" style="color: #f25b29;">
                    Read Full &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Local Consultation CTA Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
  <div class="container text-center py-3">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <span class="badge px-3 py-2 text-uppercase mb-3" style="background: rgba(242, 91, 41, 0.2); color: #ff7849; font-weight: 700; border-radius: 20px;">
          Direct Doorstep Service
        </span>
        <h2 class="font-weight-bold mb-3 display-6" style="color: #fff;">Need Expert Home Maintenance in Kashipur, Jaspur, or Thakurdwara?</h2>
        <p class="lead text-light opacity-90 mb-4" style="font-size: 17px;">
          From deep home cleaning to AC jet wash, electrical safety, plumbing, and wall painting — REFIXEL verified professionals are at your doorstep within 60 minutes.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
          <a href="<?= View::url('/services') ?>" class="btn btn-lg px-4 font-weight-bold text-white shadow" style="background: #f25b29; border-radius: 30px;">
            <i class="fa fa-calendar-check-o mr-2"></i> Book a Service Online
          </a>
          <a href="tel:+918791154730" class="btn btn-lg btn-outline-light px-4 font-weight-bold" style="border-radius: 30px;">
            <i class="fa fa-phone mr-2"></i> Call: +91 87911 54730
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.blog-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 28px rgba(0, 0, 0, 0.08) !important;
}
.blog-card:hover img {
  transform: scale(1.04);
}
.hover-orange:hover {
  color: #f25b29 !important;
}
</style>
