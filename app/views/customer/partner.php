<?php
use App\Core\View;
?>

<style>
.partner-page-wrapper {
  width: 100%;
  padding-left: clamp(16px, 4vw, 32px);
  padding-right: clamp(16px, 4vw, 32px);
}
@media (min-width: 769px) {
  .partner-desc {
    margin-bottom: 0 !important;
  }
  .partner-timeline {
    position: relative !important;
    margin-left: 0 !important;
    margin-top: 32px !important;
    padding-left: 0 !important;
    border-left: none !important;
  }
  .partner-timeline::before {
    content: '' !important;
    position: absolute !important;
    left: 17px !important;
    top: 18px !important;
    bottom: 18px !important;
    border-left: 1.5px dashed #ffdacf !important;
    z-index: 0 !important;
  }
  .partner-step-item {
    display: flex !important;
    flex-direction: row !important;
    align-items: flex-start !important;
    gap: 16px !important;
    position: relative !important;
    margin-bottom: 28px !important;
    padding-bottom: 0 !important;
    padding-left: 0 !important;
    margin-left: 0 !important;
    transform: none !important;
  }
  .partner-step-item:last-child {
    margin-bottom: 0 !important;
  }
  .partner-step-item .timeline-circle-num {
    position: relative !important;
    left: auto !important;
    top: auto !important;
    width: 36px !important;
    height: 36px !important;
    flex: 0 0 36px !important;
    border-radius: 50% !important;
    background: #ffffff !important;
    border: 1.5px solid #f25b29 !important;
    color: #f25b29 !important;
    font-size: 16px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 1 !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .partner-step-content {
    flex: 1 !important;
    min-width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .partner-step-title {
    margin: 6px 0 4px 0 !important;
    padding: 0 !important;
  }
  .partner-step-desc {
    margin: 0 !important;
    line-height: 1.5 !important;
    padding: 0 !important;
  }
}
@media (max-width: 768px) {
  .partner-page-wrapper {
    padding-top: 20px !important;
    padding-bottom: 20px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .partner-intro-text {
    padding-left: 18px !important;
    padding-right: 18px !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    width: 100% !important;
  }
  .partner-form-col {
    padding-left: 18px !important;
    padding-right: 18px !important;
  }
  .partner-row {
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  .partner-heading {
    font-size: 1.95rem !important;
    line-height: 1.25 !important;
    margin-bottom: 12px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    width: 100% !important;
  }
  .partner-desc {
    font-size: 14px !important;
    line-height: 1.6 !important;
    margin-bottom: 24px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    width: 100% !important;
  }
  .partner-timeline {
    position: relative !important;
    border-left: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    width: 100% !important;
  }
  .partner-timeline::before {
    content: '' !important;
    position: absolute !important;
    left: 15px !important;
    top: 16px !important;
    bottom: 16px !important;
    border-left: 1.5px dashed #ffdacf !important;
    z-index: 0 !important;
  }
  .partner-step-item {
    display: flex !important;
    flex-direction: row !important;
    align-items: flex-start !important;
    gap: 14px !important;
    position: relative !important;
    margin-bottom: 20px !important;
    padding-bottom: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    width: 100% !important;
    transform: none !important;
  }
  .partner-step-item:last-child {
    margin-bottom: 0 !important;
  }
  .partner-step-item .timeline-circle-num {
    position: relative !important;
    left: auto !important;
    top: auto !important;
    width: 32px !important;
    height: 32px !important;
    flex: 0 0 32px !important;
    border-radius: 50% !important;
    background: #ffffff !important;
    border: 1.5px solid #f25b29 !important;
    color: #f25b29 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 1 !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .partner-step-content {
    flex: 1 !important;
    min-width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .partner-step-title {
    font-size: 15px !important;
    font-weight: 500 !important;
    line-height: normal !important;
    margin: 4px 0 4px 0 !important;
    padding: 0 !important;
  }
  .partner-step-desc {
    font-size: 13px !important;
    line-height: 1.5 !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #6c757d !important;
  }
}
@media (max-width: 480px) {
  .partner-heading {
    font-size: 1.75rem !important;
  }
}

/* ── Service Partner Form Inputs Distinct Gray Shadow ── */
.partner-form-col .form-control {
  background-color: #f8fafc !important;
  border: 1.5px solid #e2e8f0 !important;
  border-radius: 12px !important;
  color: #1e293b !important;
  font-size: 15px !important;
  padding: 12px 16px !important;
  height: auto !important;
  /* Distinct gray shadow */
  box-shadow: 0 3px 12px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.04) !important;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.partner-form-col .form-control:hover {
  background-color: #ffffff !important;
  border-color: #cbd5e1 !important;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12) !important;
}

.partner-form-col .form-control:focus {
  background-color: #ffffff !important;
  border-color: #f25b29 !important;
  box-shadow: 0 4px 18px rgba(242, 91, 41, 0.18), 0 0 0 3px rgba(242, 91, 41, 0.12) !important;
  outline: none !important;
}

.partner-form-col .form-control::placeholder {
  color: #94a3b8 !important;
  font-size: 14.5px !important;
}

.partner-form-col select.form-control {
  cursor: pointer;
}
</style>

<div class="partner-page-wrapper py-4 py-md-5 mt-2 mt-md-4">
  <div class="container px-0">
    <div class="row align-items-center partner-row">
      
      <!-- Left Side: Steps and Process with proper side breathing room -->
      <div class="col-lg-6 mb-5 mb-lg-0 pr-lg-5 partner-intro-text">
        <span class="badge px-3 py-2 mb-3 font-weight-bold" style="background: #fff3ec; color: #f25b29; border: 1px solid #ffdacf; font-size: 12.5px; border-radius: 6px;">BECOME A PARTNER</span>
        <h1 class="font-weight-bold mb-3 mb-md-4 partner-heading" style="color: #1a1a1a; font-size: 2.4rem;">Join REFIXEL & Grow Your Earnings</h1>
        <p class="text-muted mb-4 mb-md-5 partner-desc" style="font-size: 1.08rem; line-height: 1.7;">
          Are you a skilled professional looking for more work and better income? Partner with REFIXEL to get verified leads, flexible timings, and guaranteed payouts. Here is how our simple onboarding process works:
        </p>

        <div class="partner-timeline position-relative">
          
          <!-- Step 1 -->
          <div class="partner-step-item position-relative mb-4 pb-1">
            <div class="timeline-circle-num">
              1
            </div>
            <div class="partner-step-content">
              <h5 class="font-weight-bold text-dark mb-1 partner-step-title" style="font-size: 17px;">Submit Application</h5>
              <p class="text-muted small mb-0 partner-step-desc" style="line-height: 1.55;">Fill out the simple form on the right with your basic details and the trade you specialize in.</p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="partner-step-item position-relative mb-4 pb-1">
            <div class="timeline-circle-num">
              2
            </div>
            <div class="partner-step-content">
              <h5 class="font-weight-bold text-dark mb-1 partner-step-title" style="font-size: 17px;">Background Verification</h5>
              <p class="text-muted small mb-0 partner-step-desc" style="line-height: 1.55;">Our team will contact you to verify your identity, documents, and professional experience.</p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="partner-step-item position-relative mb-4 pb-1">
            <div class="timeline-circle-num">
              3
            </div>
            <div class="partner-step-content">
              <h5 class="font-weight-bold text-dark mb-1 partner-step-title" style="font-size: 17px;">Training & Onboarding</h5>
              <p class="text-muted small mb-0 partner-step-desc" style="line-height: 1.55;">Attend a quick training session to understand REFIXEL standards and how to use our partner app.</p>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="partner-step-item position-relative">
            <div class="timeline-circle-num">
              4
            </div>
            <div class="partner-step-content">
              <h5 class="font-weight-bold text-dark mb-1 partner-step-title" style="font-size: 17px;">Start Earning</h5>
              <p class="text-muted small mb-0 partner-step-desc" style="line-height: 1.55;">Get live booking requests in your area and start earning money with every completed job.</p>
            </div>
          </div>

        </div>
      </div>

      <!-- Right Side: Application Form -->
      <div class="col-lg-6 partner-form-col">
        <div class="card p-4 p-md-5 border-0 shadow-lg" style="border-radius: 20px;">
          <div class="text-center mb-4">
            <h3 class="font-weight-bold mb-2">Partner Application Form</h3>
            <p class="text-muted small">Fill out your details and we will call you back within 24 hours.</p>
          </div>

          <form action="<?= View::url('/partner') ?>" method="POST">
            <div class="form-group mb-3">
              <label class="font-weight-bold small text-muted">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. Rahul Kumar" required>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="font-weight-bold small text-muted">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control form-control-lg" placeholder="10-digit mobile number" required pattern="[0-9]{10}">
              </div>
              <div class="col-md-6 mb-3">
                <label class="font-weight-bold small text-muted">City <span class="text-danger">*</span></label>
                <select name="city" class="form-control form-control-lg" required>
                  <option value="">Select your city</option>
                  <?php foreach ($cities ?? [] as $city): ?>
                    <option value="<?= View::e($city['city']) ?>"><?= View::e($city['city']) ?></option>
                  <?php endforeach; ?>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>

            <div class="form-group mb-4">
              <label class="font-weight-bold small text-muted">Primary Trade / Skill <span class="text-danger">*</span></label>
              <select name="trade" class="form-control form-control-lg" required>
                <option value="">What services do you provide?</option>
                <?php foreach ($categories ?? [] as $category): ?>
                  <option value="<?= View::e($category['name']) ?>"><?= View::e($category['name']) ?></option>
                <?php endforeach; ?>
                <option value="Other">Other</option>
              </select>
            </div>
            
            <div class="form-group mb-4">
              <label class="font-weight-bold small text-muted">Years of Experience</label>
              <select name="experience" class="form-control form-control-lg">
                <option value="0-1">0 - 1 Years</option>
                <option value="2-4">2 - 4 Years</option>
                <option value="5+">5+ Years</option>
              </select>
            </div>

            <button type="submit" class="btn btn-block text-white font-weight-bold py-3 mt-2" style="background-color: #f25b29; border-radius: 10px; font-size: 16px; box-shadow: 0 4px 14px rgba(242, 91, 41, 0.3);">
              Submit Application
            </button>
            
            <p class="text-center text-muted small mt-4 mb-0">
              By submitting this form, you agree to our <a href="<?= View::url('/terms') ?>" class="text-decoration-none" style="color:#f25b29;">Terms & Conditions</a>.
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
