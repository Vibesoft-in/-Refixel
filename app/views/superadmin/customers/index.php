<div class="container-fluid p-0">
  <!-- Section Header with Single-Click Delete All Button -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div class="mb-2 mb-md-0">
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-users text-secondary mr-2"></i>Customers Registry
      </h3>
      <p class="text-muted small mb-0">Manage customer accounts, verify saved addresses, and perform complete user purges when needed.</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-sm-0">
      <button type="button" class="btn btn-danger font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalSectionDeleteCustomers" <?= empty($customers) ? 'disabled' : '' ?>>
        <i class="fa fa-trash mr-1"></i> Delete All Customers (<?= $totalCount ?>)
      </button>
    </div>
  </div>

  <!-- Customers Table -->
  <div class="sa-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 sa-table-min-lg">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">ID</th>
            <th style="white-space: nowrap;">Customer</th>
            <th style="white-space: nowrap;">Contact</th>
            <th style="white-space: nowrap;">City / Address</th>
            <th style="white-space: nowrap;">Bookings</th>
            <th style="white-space: nowrap;">Saved Addresses</th>
            <th style="white-space: nowrap;">Registered On</th>
            <th class="text-right" style="white-space: nowrap;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="fa fa-user-times fa-3x text-muted mb-2 d-block"></i>
                No customer accounts registered in the database.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($customers as $c): ?>
              <tr>
                <td class="font-weight-bold text-dark" style="white-space: nowrap;">#<?= $c['id'] ?></td>
                <td>
                  <div class="font-weight-bold text-dark text-truncate" style="max-width: 170px;"><?= \App\Core\View::e($c['name']) ?></div>
                  <span class="badge badge-success px-2 py-0" style="font-size: 10.5px;"><?= ucfirst($c['status']) ?></span>
                </td>
                <td style="white-space: nowrap;">
                  <div><i class="fa fa-phone text-muted mr-1"></i><?= \App\Core\View::e($c['phone'] ?? 'N/A') ?></div>
                  <small class="text-muted"><i class="fa fa-envelope text-muted mr-1"></i><?= \App\Core\View::e($c['email'] ?? 'N/A') ?></small>
                </td>
                <td>
                  <div class="font-weight-bold text-dark"><?= \App\Core\View::e($c['city'] ?? 'Kashipur') ?></div>
                  <small class="text-muted text-truncate d-inline-block" style="max-width: 220px;">
                    <?= \App\Core\View::e($c['address'] ?? 'No saved street address') ?>
                  </small>
                </td>
                <td style="white-space: nowrap;">
                  <span class="badge badge-primary font-weight-bold px-2 py-1">
                    <?= $c['bookings_count'] ?? 0 ?> Bookings
                  </span>
                </td>
                <td style="white-space: nowrap;">
                  <span class="badge badge-light border text-dark font-weight-bold">
                    <?= $c['addresses_count'] ?? 0 ?> Saved
                  </span>
                </td>
                <td class="text-muted small" style="white-space: nowrap;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td class="text-right" style="white-space: nowrap;">
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteCustomer(<?= $c['id'] ?>)" title="Delete Customer">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Delete All Customers -->
<div class="modal fade" id="modalSectionDeleteCustomers" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-users mr-2"></i>Delete All Customer Accounts?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/customers/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-exclamation-triangle fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= $totalCount ?> Customer accounts?</h5>
          <p class="text-muted small mb-0">
            This will permanently remove every customer profile, saved address, and login credentials. Existing bookings will be safely unlinked from customer IDs.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Customers
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<form id="deleteSingleCustForm" action="" method="POST" style="display:none;">
  <?= \App\Core\View::csrfField() ?>
</form>

<script>
function confirmDeleteCustomer(id) {
  if (confirm('Permanently delete customer #' + id + '?')) {
    var f = document.getElementById('deleteSingleCustForm');
    f.action = '<?= \App\Core\View::url('/superadmin/customers/') ?>' + id + '/delete';
    f.submit();
  }
}
</script>
