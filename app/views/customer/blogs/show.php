<?php
use App\Core\View;
$postUrl = View::fullUrl('/blogs/' . $post['slug']);
$encodedUrl = urlencode($postUrl);
$encodedTitle = urlencode($post['title']);
?>

<div class="bg-light py-3 border-bottom">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 bg-transparent p-0 small">
        <li class="breadcrumb-item"><a href="<?= View::url('/') ?>" class="text-decoration-none text-muted">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= View::url('/blogs') ?>" class="text-decoration-none text-muted">Blogs</a></li>
        <li class="breadcrumb-item"><a href="<?= View::url('/blogs?category=' . urlencode(strtolower($post['service_cat'] ?? 'all'))) ?>" class="text-decoration-none text-muted"><?= View::e($post['category']) ?></a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold text-truncate" style="max-width: 320px;" aria-current="page"><?= View::e($post['title']) ?></li>
      </ol>
    </nav>
  </div>
</div>

<article class="py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        
        <!-- Article Header -->
        <header class="mb-4">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <?php if (!empty($post['is_case_study'])): ?>
              <span class="badge px-3 py-1 font-weight-bold text-white" style="background: #f25b29; border-radius: 20px; font-size: 12px;">
                ⭐ VERIFIED CASE STUDY
              </span>
            <?php else: ?>
              <span class="badge px-3 py-1 font-weight-bold text-white bg-dark" style="border-radius: 20px; font-size: 12px;">
                <?= View::e($post['category']) ?>
              </span>
            <?php endif; ?>

            <span class="badge px-3 py-1 font-weight-bold" style="background: #e2e8f0; color: #1e293b; border-radius: 20px; font-size: 12px;">
              <i class="fa fa-map-marker text-danger mr-1"></i> <?= View::e($post['city']) ?> Service Hub
            </span>

            <span class="text-muted small ml-auto">
              <i class="fa fa-clock-o mr-1"></i> <?= View::e($post['reading_time']) ?>
            </span>
          </div>

          <h1 class="font-weight-bold display-6 mb-3" style="color: #0f172a; line-height: 1.3; font-size: clamp(26px, 3.5vw, 38px);">
            <?= View::e($post['title']) ?>
          </h1>

          <div class="d-flex align-items-center py-3 border-top border-bottom text-muted small">
            <div class="d-flex align-items-center mr-4">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 38px; height: 38px; font-weight: bold; background: #f25b29 !important;">
                <i class="fa fa-user-circle"></i>
              </div>
              <div>
                <strong class="text-dark d-block"><?= View::e($post['author']) ?></strong>
                <span class="text-muted" style="font-size: 11px;">Verified Technical Specialist</span>
              </div>
            </div>

            <div class="ml-auto d-flex align-items-center gap-3">
              <span class="mr-3"><i class="fa fa-calendar-o mr-1"></i> <?= date('F j, Y', strtotime($post['published_at'])) ?></span>
              
              <!-- Quick Share -->
              <a href="https://api.whatsapp.com/send?text=<?= $encodedTitle ?>%20<?= $encodedUrl ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success px-2 py-1" style="border-radius: 15px;" title="Share on WhatsApp">
                <i class="fa fa-whatsapp mr-1"></i> Share
              </a>
            </div>
          </div>
        </header>

        <!-- Featured Media -->
        <div class="featured-image mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden; max-height: 440px;">
          <img src="<?= View::asset('img/' . $post['image']) ?>" 
               alt="<?= View::e($post['title']) ?>" 
               class="w-100 h-100" 
               style="object-fit: cover;"
               onerror="this.src='<?= View::asset('img/Full-home-clean.jpg') ?>';">
        </div>

        <!-- Case Study Highlight Card (if applicable) -->
        <?php if (!empty($post['is_case_study']) && !empty($post['case_study'])): ?>
          <div class="card mb-5 border-0 shadow-sm" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-left: 5px solid #f25b29 !important; border-radius: 12px;">
            <div class="card-body p-4">
              <h4 class="font-weight-bold mb-3" style="color: #c2410c;">
                <i class="fa fa-clipboard-check mr-2"></i> Project Summary &amp; Case Study Metrics
              </h4>
              <div class="row g-3">
                <div class="col-md-6 mb-2">
                  <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Customer &amp; Location</small>
                  <p class="mb-0 font-weight-semibold text-dark"><?= View::e($post['case_study']['client']) ?></p>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Property Type</small>
                  <p class="mb-0 font-weight-semibold text-dark"><?= View::e($post['case_study']['property']) ?></p>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Service Executed</small>
                  <p class="mb-0 font-weight-semibold text-dark"><?= View::e($post['case_study']['service']) ?></p>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Turnaround Time</small>
                  <p class="mb-0 font-weight-semibold text-dark"><?= View::e($post['case_study']['duration']) ?></p>
                </div>
                <div class="col-12 mt-2 pt-2 border-top border-warning">
                  <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Core Challenge</small>
                  <p class="mb-2 text-dark"><?= View::e($post['case_study']['challenge']) ?></p>
                  <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">Final Verified Outcome</small>
                  <p class="mb-0 font-weight-bold text-success"><i class="fa fa-check-circle mr-1"></i> <?= View::e($post['case_study']['outcome']) ?></p>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Main Article Content Body (400-600 words) -->
        <div class="article-body mb-5" style="font-size: 17px; line-height: 1.8; color: #2d3748;">
          <?= $post['content'] ?>
        </div>

        <!-- Inline Doorstep Booking CTA Card -->
        <div class="card border-0 shadow text-white p-4 p-md-5 mb-5" style="background: linear-gradient(135deg, #f25b29 0%, #d9481a 100%); border-radius: 16px;">
          <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
              <span class="badge bg-white text-dark font-weight-bold px-3 py-1 mb-2" style="border-radius: 20px;">
                Verified In <?= View::e($post['city']) ?>
              </span>
              <h3 class="font-weight-bold text-white mb-2">Facing Similar Home Maintenance Issues?</h3>
              <p class="mb-0 text-white opacity-90">
                Book verified background-checked technicians across Kashipur, Jaspur, and Thakurdwara with standardized pricing &amp; 24-hour service guarantee.
              </p>
            </div>
            <div class="col-md-4 text-md-right">
              <a href="<?= View::url('/book') ?>" class="btn btn-light btn-lg font-weight-bold px-4 mb-2 d-block d-sm-inline-block" style="color: #f25b29; border-radius: 30px;">
                <i class="fa fa-calendar-check-o mr-1"></i> Book Now
              </a>
              <a href="tel:+918791154730" class="btn btn-outline-light btn-sm font-weight-bold px-3 d-block d-sm-inline-block" style="border-radius: 30px;">
                <i class="fa fa-phone mr-1"></i> Call 87911 54730
              </a>
            </div>
          </div>
        </div>

        <!-- Author Credibility Note -->
        <div class="p-4 bg-light rounded-lg d-flex align-items-center mb-5" style="border-radius: 12px; border: 1px solid #e2e8f0;">
          <div class="mr-3 d-none d-sm-block">
            <img src="<?= View::asset('img/refixel-logo-original.png') ?>" alt="REFIXEL" style="width: 60px; height: auto;">
          </div>
          <div>
            <h5 class="font-weight-bold mb-1 text-dark">About the REFIXEL Technical Review Board</h5>
            <p class="text-muted small mb-0">
              All guides and case studies are written and verified by licensed electricians, certified HVAC engineers, and environmental hygiene supervisors active in Uttarakhand and Western UP. We use only OEM genuine parts, calibrated digital diagnostic tools, and eco-friendly certified chemicals.
            </p>
          </div>
        </div>

        <!-- Social Share Footer -->
        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-5 bg-white">
          <span class="font-weight-bold text-dark"><i class="fa fa-share-alt mr-2 text-primary"></i> Share this guide:</span>
          <div class="d-flex gap-2">
            <a href="https://api.whatsapp.com/send?text=<?= $encodedTitle ?>%20<?= $encodedUrl ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" style="border-radius: 20px;">
              <i class="fa fa-whatsapp mr-1"></i> WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" style="border-radius: 20px;">
              <i class="fa fa-facebook mr-1"></i> Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= $encodedTitle ?>&url=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark" style="border-radius: 20px;">
              <i class="fa fa-twitter mr-1"></i> Twitter
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</article>

<!-- Related Guides Section -->
<?php if (!empty($relatedPosts)): ?>
<section class="py-5 bg-light border-top">
  <div class="container">
    <div class="text-center mb-4">
      <h3 class="font-weight-bold text-dark">Related Maintenance Guides &amp; Case Studies</h3>
      <p class="text-muted small">Expand your home maintenance knowledge with more verified expert articles.</p>
    </div>

    <div class="row justify-content-center">
      <?php foreach ($relatedPosts as $rel): ?>
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div style="height: 180px; overflow: hidden; background: #f1f5f9;">
              <img src="<?= View::asset('img/' . $rel['image']) ?>" alt="<?= View::e($rel['title']) ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='<?= View::asset('img/Full-home-clean.jpg') ?>';">
            </div>
            <div class="card-body p-3 d-flex flex-column">
              <small class="text-muted mb-1"><i class="fa fa-map-marker text-danger mr-1"></i> <?= View::e($rel['city']) ?> • <?= View::e($rel['reading_time']) ?></small>
              <h5 class="card-title font-weight-bold mb-2" style="font-size: 16px;">
                <a href="<?= View::url('/blogs/' . $rel['slug']) ?>" class="text-dark text-decoration-none">
                  <?= View::e($rel['title']) ?>
                </a>
              </h5>
              <p class="card-text text-muted small flex-grow-1" style="line-height: 1.5;">
                <?= View::e($rel['excerpt']) ?>
              </p>
              <a href="<?= View::url('/blogs/' . $rel['slug']) ?>" class="font-weight-bold small text-decoration-none mt-2" style="color: #f25b29;">
                Read Article &rarr;
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<style>
.article-body h2 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin-top: 36px;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid #f1f5f9;
}
.article-body h3 {
  font-size: 20px;
  font-weight: 600;
  color: #334155;
  margin-top: 24px;
  margin-bottom: 12px;
}
.article-body p {
  margin-bottom: 18px;
}
.article-body ul, .article-body ol {
  margin-bottom: 20px;
  padding-left: 24px;
}
.article-body li {
  margin-bottom: 8px;
}
</style>
