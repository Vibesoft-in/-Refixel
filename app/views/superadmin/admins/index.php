<div class="container-fluid p-0">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div class="mb-2 mb-md-0">
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-user-secret text-danger mr-2"></i>Admin Accounts Governance
      </h3>
      <p class="text-muted small mb-0">Manage personnel with administrative access. Super Admin can add or revoke regular Admin credentials.</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-sm-0">
      <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreateAdmin">
        <i class="fa fa-plus-circle mr-1"></i> Create Admin Account
      </button>
    </div>
  </div>

  <div class="alert alert-info border d-flex align-items-center mb-4 p-3" style="border-radius: 8px; background: #eef2ff; border-color: #c7d2fe !important;">
    <i class="fa fa-shield fa-2x text-primary mr-3" style="color: #6366f1 !important;"></i>
    <div>
      <h6 class="font-weight-bold text-dark mb-1">Exclusive Administrator Provisioning Policy</h6>
      <p class="small text-muted mb-0">
        Public self-registration for Admin accounts is strictly forbidden across the website. <strong>Only logged-in Super Admins</strong> can create or revoke Operations Admin credentials.
      </p>
    </div>
  </div>

  <div class="sa-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 sa-table-min-lg">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">ID</th>
            <th style="white-space: nowrap;">Name</th>
            <th style="white-space: nowrap;">Role</th>
            <th style="white-space: nowrap;">Email</th>
            <th style="white-space: nowrap;">Phone</th>
            <th style="white-space: nowrap;">Status</th>
            <th style="white-space: nowrap;">Last Login</th>
            <th style="white-space: nowrap;">Created</th>
            <th class="text-right" style="white-space: nowrap;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $a): ?>
            <tr>
              <td class="font-weight-bold text-dark" style="white-space: nowrap;">#<?= $a['id'] ?></td>
              <td>
                <div class="font-weight-bold text-dark text-truncate" style="max-width: 170px;"><?= \App\Core\View::e($a['name']) ?></div>
              </td>
              <td style="white-space: nowrap;">
                <?php if ($a['role'] === 'super_admin'): ?>
                  <span class="badge badge-danger font-weight-bold px-2 py-1"><i class="fa fa-shield mr-1"></i>SUPER ADMIN</span>
                <?php else: ?>
                  <span class="badge badge-primary font-weight-bold px-2 py-1">ADMIN</span>
                <?php endif; ?>
              </td>
              <td><?= \App\Core\View::e($a['email'] ?? '—') ?></td>
              <td style="white-space: nowrap;"><?= \App\Core\View::e($a['phone'] ?? '—') ?></td>
              <td style="white-space: nowrap;">
                <span class="badge badge-success px-2 py-1"><?= ucfirst($a['status']) ?></span>
              </td>
              <td class="text-muted small" style="white-space: nowrap;">
                <?= !empty($a['last_login_at']) ? date('d M Y, h:i A', strtotime($a['last_login_at'])) : 'Never' ?>
              </td>
              <td class="text-muted small" style="white-space: nowrap;"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
              <td class="text-right" style="white-space: nowrap;">
                <?php if ($a['role'] !== 'super_admin'): ?>
                  <form action="<?= \App\Core\View::url('/superadmin/admins/' . $a['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Revoke and delete Admin account for <?= \App\Core\View::e($a['name']) ?>?');">
                    <?= \App\Core\View::csrfField() ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Revoke Admin Access">
                      <i class="fa fa-trash"></i> Revoke
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted small font-italic">Protected</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Create Admin Account -->
<div class="modal fade" id="modalCreateAdmin" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-user-plus mr-2"></i>Create New Administrative User</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/admins') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4">
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Operations Manager" required>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Email Address *</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. manager@refixel.com" required>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Mobile Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Password *</label>
            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold text-dark">Role Authorization</label>
            <select name="role" class="form-control">
              <option value="admin" selected>Standard Admin (Operations, Bookings, Staff, CMS)</option>
              <option value="super_admin">Super Admin (Root access: Gateways, Commissions, Purge)</option>
            </select>
            <small class="form-text text-muted">Standard Admins cannot touch payment secrets or delete databases.</small>
          </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4">
            <i class="fa fa-check mr-1"></i> Create Account
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
