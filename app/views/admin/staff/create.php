<?php
use App\Core\View;
$categories = $categories ?? [];
?>

<div class="admin-staff-create" style="max-width: 650px;">
  <div class="mb-3">
    <a href="<?= View::url('/admin/staff') ?>" class="text-muted small font-weight-bold">
      <i class="fa fa-arrow-left mr-1"></i>Back to Staff Directory
    </a>
    <h4 class="font-weight-bold text-dark mb-1 mt-1">Register New Field Technician</h4>
    <p class="text-muted small">Create a staff account with temporary credentials and configure trade skill assignments.</p>
  </div>

  <div class="stat-card">
    <form method="POST" action="<?= View::url('/admin/staff') ?>">
      <?= View::csrfField() ?>

      <div class="form-group mb-3">
        <label class="font-weight-bold small text-dark">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required placeholder="e.g. Rahul Sharma">
      </div>

      <div class="form-row">
        <div class="col-md-6 form-group mb-3">
          <label class="font-weight-bold small text-dark">Mobile Phone <span class="text-danger">*</span></label>
          <input type="text" name="phone" class="form-control" required placeholder="10-digit mobile number">
          <small class="form-text text-muted">Used for login and customer contact.</small>
        </div>

        <div class="col-md-6 form-group mb-3">
          <label class="font-weight-bold small text-dark">Email Address (Optional)</label>
          <input type="email" name="email" class="form-control" placeholder="technician@REFIXEL.com">
        </div>
      </div>

      <div class="form-group mb-3">
        <label class="font-weight-bold small text-dark">Temporary Password <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control" required placeholder="Minimum 6 characters">
        <small class="form-text text-success font-weight-bold">
          <i class="fa fa-shield mr-1"></i>The technician will be forced to change this temporary password upon first login.
        </small>
      </div>

      <!-- Skill Categories -->
      <div class="form-group mb-4">
        <label class="font-weight-bold small text-dark">Authorized Trade Categories (Skills)</label>
        <p class="small text-muted mb-2">Select the service trades this technician is certified/qualified to handle:</p>
        
        <div class="row">
          <?php foreach ($categories as $cat): ?>
            <div class="col-sm-6 mb-2">
              <div class="custom-control custom-checkbox p-2 border rounded bg-light">
                <input type="checkbox" name="skills[]" value="<?= $cat['id'] ?>" class="custom-control-input" id="cat_<?= $cat['id'] ?>">
                <label class="custom-control-label font-weight-bold small text-dark" for="cat_<?= $cat['id'] ?>">
                  <?= View::e($cat['name']) ?>
                </label>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="border-top pt-3">
        <button type="submit" class="btn btn-brand btn-block font-weight-bold py-2">
          <i class="fa fa-check-circle mr-1"></i>Create Technician Account
        </button>
        <a href="<?= View::url('/admin/staff') ?>" class="btn btn-light btn-block text-muted font-weight-bold mt-2">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

