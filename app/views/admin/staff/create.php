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
          <label class="font-weight-bold small text-dark">Email Address <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required placeholder="technician@gmail.com">
          <small class="form-text text-muted">Required so assigned jobs & alerts reach this technician.</small>
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
      <div class="form-group mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="font-weight-bold small text-dark mb-0">Authorized Trade Categories (Skills)</label>
          <div class="d-flex gap-1" style="gap: 4px;">
            <button type="button" class="btn btn-xs btn-light border py-0 px-2 font-weight-bold text-muted" onclick="selectAllCreateSkills(true)" style="font-size: 11px;">All</button>
            <button type="button" class="btn btn-xs btn-light border py-0 px-2 font-weight-bold text-muted" onclick="selectAllCreateSkills(false)" style="font-size: 11px;">Clear</button>
          </div>
        </div>
        <p class="small text-muted mb-2" style="font-size: 11.5px;">Select service trades this technician is certified or qualified to handle:</p>
        
        <div class="create-skills-grid mb-2">
          <?php foreach ($categories as $cat): ?>
            <label class="skill-chip" for="cat_<?= $cat['id'] ?>">
              <input type="checkbox" name="skills[]" value="<?= $cat['id'] ?>" class="create-skill-checkbox" id="cat_<?= $cat['id'] ?>" onchange="toggleCreateSkillChip(this)">
              <span class="skill-check-icon"><i class="fa fa-check"></i></span>
              <span class="skill-title text-truncate"><?= View::e($cat['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="border-top pt-3">
        <button type="submit" class="btn btn-brand btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 6px;">
          <i class="fa fa-check-circle mr-1"></i>Create Technician Account
        </button>
        <a href="<?= View::url('/admin/staff') ?>" class="btn btn-light btn-block text-muted font-weight-bold mt-2" style="border-radius: 6px;">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<style>
.create-skills-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 7px;
  max-height: 240px;
  overflow-y: auto;
  padding: 2px 1px;
}
@media (max-width: 575.98px) {
  .create-skills-grid {
    grid-template-columns: 1fr;
  }
}
.skill-chip {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 6px 10px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.16s ease;
  user-select: none;
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0;
  min-height: 36px;
}
.skill-chip:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}
.skill-chip input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 0;
  height: 0;
}
.skill-chip .skill-check-icon {
  width: 17px;
  height: 17px;
  border-radius: 4px;
  border: 1.5px solid #94a3b8;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  color: transparent;
  background: #ffffff;
  transition: all 0.16s ease;
  flex-shrink: 0;
}
.skill-chip.active {
  background: #fff7ed;
  border-color: #f25b29;
  color: #c2410c;
  box-shadow: 0 1px 4px rgba(242, 91, 41, 0.12);
}
.skill-chip.active .skill-check-icon {
  background: #f25b29;
  border-color: #f25b29;
  color: #ffffff;
}
.skill-chip .skill-title {
  min-width: 0;
  flex: 1;
}
</style>

<script>
function toggleCreateSkillChip(input) {
  const chip = input.closest('.skill-chip');
  if (chip) {
    if (input.checked) chip.classList.add('active');
    else chip.classList.remove('active');
  }
}

function selectAllCreateSkills(select) {
  const checkboxes = document.querySelectorAll('.create-skill-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = select;
    const chip = cb.closest('.skill-chip');
    if (chip) {
      if (select) chip.classList.add('active');
      else chip.classList.remove('active');
    }
  });
}
</script>

