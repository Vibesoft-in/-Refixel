<div class="container py-5 my-3">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent p-0 mb-4" style="font-size: 14px;">
      <li class="breadcrumb-item"><a href="<?= \App\Core\View::url('/') ?>" style="color:#f25b29;">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Work Showcase & Before / After</li>
    </ol>
  </nav>

  <div class="page_heading text-center mb-4">
    <h6 style="color: #f25b29; font-weight: 600; letter-spacing: 1px;">REAL RESULTS & PROVEN QUALITY</h6>
    <h1 class="font-weight-bold" style="font-size: 36px; color: #1a1a1a;">Before & After Transformations</h1>
    <p class="text-muted" style="max-width: 720px; margin: 0 auto; font-size: 16px;">
      Explore real job completions by REFIXEL verified professionals. From mechanized deep cleaning and stubborn stain eradication to pest control and bespoke carpentry.
    </p>
  </div>

  <!-- Category Filter Pills -->
  <div class="d-flex flex-wrap justify-content-center mb-5" style="gap: 10px;">
    <button type="button" class="btn btn-sm gallery-filter-btn active font-weight-bold px-3 py-2" data-cat="all" style="border-radius: 30px; border: 1.5px solid #f25b29; background: #f25b29; color: #fff; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
      <i class="fa fa-th-large mr-1"></i> All Work (8)
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="room-cleaning" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-sparkles mr-1 text-success"></i> Room Cleaning
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="pest-control" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-bug mr-1 text-danger"></i> Pest Control
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="sofa-cleaning" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-couch mr-1 text-primary"></i> Sofa Cleaning
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="room-painting" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-paint-brush mr-1 text-warning"></i> Room Painting
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="home-renovation" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-wrench mr-1" style="color: #f25b29;"></i> Home Renovation
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="electrical-repair" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-bolt mr-1 text-warning"></i> Electrical Repairs
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="plumbing-repair" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-wrench mr-1 text-info"></i> Plumbing Repairs
    </button>
    <button type="button" class="btn btn-sm gallery-filter-btn font-weight-bold px-3 py-2" data-cat="ac-service" style="border-radius: 30px; border: 1.5px solid #dee2e6; background: #fff; color: #333;">
      <i class="fa fa-snowflake-o mr-1 text-primary"></i> AC Jet Service
    </button>
  </div>

  <div class="row" id="galleryShowcaseGrid">
    <!-- 1. Room Cleaning / Laundry Area Deep Cleaning -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="room-cleaning">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-laundry-cleaning.png') ?>" class="w-100" alt="Before & After Room & Laundry Area Cleaning" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(13, 148, 136, 0.92); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: 20px; font-size: 12px;">
            <i class="fa fa-sparkles"></i> Room Cleaning
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
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Laundry & Utility Room Deep Cleaning</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Tackled heavy lime-scale encrustations on the utility sink, scrubbed moldy and grimed walls, sanitized tiled flooring, and neatened laundry setup with eco-friendly antibacterial agents.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 4.9 <small class="text-muted font-weight-normal">(1,200+ Cleaned)</small></span>
            <a href="<?= \App\Core\View::url('/cleaning-services-in-gurugram') ?>" class="btn btn-sm btn-outline-success font-weight-bold px-3" style="border-radius: 20px;">
              Book Room Cleaning &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Pest Control / Cockroach Infestation Eradication -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="pest-control">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-pest-control.png') ?>" class="w-100" alt="Before & After Kitchen Pest Control Cockroach Infestation" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(220, 38, 38, 0.92); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: 20px; font-size: 12px;">
            <i class="fa fa-bug"></i> Pest Control
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border text-danger font-weight-bold">
                <i class="fa fa-shield"></i> 100% Roach Eradication
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Kitchen Under-Counter Pest Eradication</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Severe cockroach nest and pest debris lurking underneath kitchen cabinetry eradicated using targeted odorless gel baiting, residual spray, crack sealing, and antimicrobial wipe-down.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 5.0 <small class="text-muted font-weight-normal">(90-Day Warranty)</small></span>
            <a href="<?= \App\Core\View::url('/pest-control-services-in-gurugram') ?>" class="btn btn-sm btn-outline-danger font-weight-bold px-3" style="border-radius: 20px;">
              Book Pest Control &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Sofa Cleaning / Upholstery Deep Cleaning -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="sofa-cleaning">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-sofa-cleaning.png') ?>" class="w-100" alt="Before & After Fabric Sofa Deep Cleaning" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(79, 70, 229, 0.92); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: 20px; font-size: 12px;">
            <i class="fa fa-couch"></i> Sofa Cleaning
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border text-primary font-weight-bold">
                <i class="fa fa-sparkles"></i> Fabric Restored
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Sectional Fabric Sofa Stain Extraction</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Deep fiber shampooing and heavy suction extraction removed dark sweat patches, stubborn beverage spills, pet dander, and deep micro-dust, restoring original plush texture and color.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 4.9 <small class="text-muted font-weight-normal">(Fast 2-Hr Drying)</small></span>
            <a href="<?= \App\Core\View::url('/sofa-cleaning-in-gurugram') ?>" class="btn btn-sm btn-outline-primary font-weight-bold px-3" style="border-radius: 20px;">
              Book Sofa Cleaning &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Room Painting / Interior Wall Makeover -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="room-painting">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-room-painting.png') ?>" class="w-100" alt="Before & After Living Room Painting & Wall Makeover" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(217, 119, 6, 0.92); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: 20px; font-size: 12px;">
            <i class="fa fa-paint-brush"></i> Room Painting
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border text-warning font-weight-bold" style="color: #b45309 !important;">
                <i class="fa fa-paint-brush"></i> Dustless Sanding & 2 Coats
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Living Room Wall Painting & Refurbishment</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Scraped off flaking wall paint, applied acrylic putty, laser-aligned surfaces, and applied 2 coats of washable royal luxury emulsion with false ceiling lighting installation.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 4.8 <small class="text-muted font-weight-normal">(Royal Luxury Finish)</small></span>
            <a href="<?= \App\Core\View::url('/painting-services-services-in-gurugram') ?>" class="btn btn-sm btn-outline-warning font-weight-bold px-3" style="border-radius: 20px; color: #b45309; border-color: #b45309;">
              Book Painting &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Home Renovation / Modern TV Feature Wall Carpentry -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="home-renovation">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-wall-renovation.png') ?>" class="w-100" alt="Before & After TV Feature Wall & Carpentry Renovation" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(242, 91, 41, 0.92); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 20px; font-size: 13px;">
            <i class="fa fa-wrench"></i> Home Renovation
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border font-weight-bold" style="color: #f25b29;">
                <i class="fa fa-home"></i> Full Interior Remodel
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Living Room TV Feature Wall & Carpentry Renovation</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Raw masonry, chiseled electrical conduits, and construction debris completely transformed into a designer feature entertainment wall with vertical acoustic wood louvers, warm concealed LED cove illumination, and floating storage cabinetry.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 5.0 <small class="text-muted font-weight-normal">(Bespoke Woodwork)</small></span>
            <a href="<?= \App\Core\View::url('/carpenter-services-in-gurugram') ?>" class="btn btn-sm text-white font-weight-bold px-3 py-1" style="border-radius: 20px; background: #f25b29;">
              Book Carpentry &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. Electrical Repairs / Switchboard & MCB Distribution Panel -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="electrical-repair">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-electrical-repair.png') ?>" class="w-100" alt="Before & After Electrical Repairs & MCB Panel Overhaul" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(217, 119, 6, 0.92); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 20px; font-size: 13px;">
            <i class="fa fa-bolt"></i> Electrical Repairs
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border text-warning font-weight-bold" style="color: #b45309 !important;">
                <i class="fa fa-bolt"></i> Insulated & Safe
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Switchboard & MCB Distribution Panel Overhaul</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Dangerous loose wiring, exposed fuse boxes and chiseled conduits systematically rewired, neatly enclosed in a certified MCB panel, and fitted with sleek modular switchplates.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 5.0 <small class="text-muted font-weight-normal">(Certified Electricians)</small></span>
            <a href="<?= \App\Core\View::url('/fan-switchboard-repair-in-gurugram') ?>" class="btn btn-sm btn-outline-warning font-weight-bold px-3 py-1" style="border-radius: 20px; color: #b45309; border-color: #b45309;">
              Book Electrical &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. Plumbing Services / Kitchen Under-Sink Leak Repair -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="plumbing-repair">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-plumbing-repair.png') ?>" class="w-100" alt="Before & After Kitchen Under-Sink Leak Repair" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(2, 132, 199, 0.92); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 20px; font-size: 13px;">
            <i class="fa fa-wrench"></i> Plumbing Repairs
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border text-info font-weight-bold">
                <i class="fa fa-tint"></i> 100% Leak-Proof
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Kitchen Under-Sink Pipe Leak & Drainage Overhaul</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Corroded leaking drain pipe and water-damaged cabinet completely fixed with brand new heavy-duty PVC P-trap pipe fitting, watertight seals, and clean sanitized dry storage.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 4.9 <small class="text-muted font-weight-normal">(Instant Diagnosis)</small></span>
            <a href="<?= \App\Core\View::url('/tap-leak-repair-in-gurugram') ?>" class="btn btn-sm btn-outline-info font-weight-bold px-3 py-1" style="border-radius: 20px;">
              Book Plumbing &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- 8. AC Jet Service & Coil Deep Cleaning -->
    <div class="col-lg-6 mb-4 gallery-card-col" data-cat="ac-service">
      <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow:hidden; border: 1px solid #e2e8f0;">
        <div class="position-relative">
          <img src="<?= \App\Core\View::asset('img/before-after-ac-service.png') ?>" class="w-100" alt="Before & After Split AC Deep Jet Wash & Coil Cleaning" style="display: block; object-fit: cover;">
          <span class="badge position-absolute text-white font-weight-bold shadow-sm" style="top: 14px; right: 14px; background: rgba(14, 116, 144, 0.92); backdrop-filter: blur(4px); padding: 6px 14px; border-radius: 20px; font-size: 13px;">
            <i class="fa fa-snowflake-o"></i> AC Jet Service
          </span>
        </div>
        <div class="card-body p-4 d-flex flex-column justify-content-between">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge badge-light border text-primary font-weight-bold">
                <i class="fa fa-snowflake-o"></i> 2X Better Cooling
              </span>
              <small class="text-muted"><i class="fa fa-map-marker text-danger mr-1"></i> Available in your location</small>
            </div>
            <h4 class="card-title font-weight-bold mb-2" style="font-size: 20px; color: #1e293b;">Split AC Deep Jet Wash & Coil Cleaning</h4>
            <p class="card-text text-muted" style="font-size: 14.5px; line-height: 1.6;">
              Extreme dust accumulation, blocked airflow and foul odor resolved through high-pressure antibacterial jet flush, restoring instant ice-cool airflow and pure fresh indoor air.
            </p>
          </div>
          <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
            <span class="text-warning font-weight-bold">★ 5.0 <small class="text-muted font-weight-normal">(Full Coil Jet Clean)</small></span>
            <a href="<?= \App\Core\View::url('/ac-jet-service-in-gurugram') ?>" class="btn btn-sm btn-outline-primary font-weight-bold px-3 py-1" style="border-radius: 20px;">
              Book AC Service &rarr;
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center mt-5">
    <a href="<?= \App\Core\View::url('/book') ?>" class="btn text-white px-5 py-3 font-weight-bold shadow-sm" style="background:#f25b29; border-radius: 50px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.35);">
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
        });
        this.classList.add('active');
        this.style.background = '#f25b29';
        this.style.color = '#fff';
        this.style.borderColor = '#f25b29';
        
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

