<div class="container py-5 my-3">


  <div class="page_heading text-center mb-5">
    <h6 style="color: #f25b29; font-weight: 600; letter-spacing: 1px;">HELP & SUPPORT</h6>
    <h1 class="font-weight-bold" style="font-size: 36px; color: #1a1a1a;">Frequently Asked Questions</h1>
    <p class="text-muted" style="max-width: 600px; margin: 0 auto;">Everything you need to know about our home cleaning, maintenance services, pricing, and booking guarantees.</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="faq-container">
        <?php if (!empty($faqs)): ?>
          <?php foreach ($faqs as $idx => $faq): ?>
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
        <?php else: ?>
          <div class="faq-item active">
            <div class="faq-question">
              <span>What is included in a full home deep cleaning service?</span>
              <div class="faq-icon">−</div>
            </div>
            <div class="faq-answer" style="display:block;">
              Our full home deep cleaning covers bedrooms, bathrooms, kitchen, living areas, floors, furniture, appliances, windows (inside), dusting, degreasing, sanitization, and more.
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Still have questions card -->
      <div class="text-center p-4 mt-5 rounded bg-light border">
        <h4 class="font-weight-bold" style="font-size: 20px;">Still have questions?</h4>
        <p class="text-muted mb-3">Our customer support specialists are ready to help you 7 days a week.</p>
        <a href="https://api.whatsapp.com/send?phone=+919458182006&text=Hello%20Refixel%2C%20I%20have%20a%20question." target="_blank" class="btn text-white px-4 py-2 font-weight-bold" style="background:#f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">
          <i class="fa fa-whatsapp mr-1"></i> Chat with Us on WhatsApp
        </a>
      </div>
    </div>
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
});
</script>
