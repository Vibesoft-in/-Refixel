<div class="container py-5 text-center my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <h1 class="display-3 font-weight-bold" style="color: #0a1c33;">500</h1>
      <h3 class="mb-3 font-weight-bold" style="color: #1e293b;">Something Went Wrong</h3>
      <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.6;">Our engineering team has been notified and is looking into this issue. Please try again shortly or return to our homepage.</p>
      <div class="d-flex justify-content-center gap-3">
        <a href="<?= \App\Core\View::url('/') ?>" class="btn text-white px-4 py-2 mr-2" style="background-color: #f25b29; border-radius: 8px; box-shadow: 0 4px 12px rgba(242, 91, 41, 0.25);">Return to Homepage</a>
        <a href="<?= \App\Core\View::url('/contact') ?>" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 8px;">Contact Support</a>
      </div>
    </div>
  </div>
</div>
