<div class="container-fluid p-0">
  <!-- Section Header with Single-Click Delete All Button -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div class="mb-2 mb-md-0">
      <h3 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-inbox text-warning mr-2"></i>Customer Enquiries Section
      </h3>
      <p class="text-muted small mb-0">Review incoming quote inquiries, customer leads, and purge records on demand.</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-sm-0">
      <button type="button" class="btn btn-danger font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalSectionDeleteEnquiries" <?= empty($enquiries) ? 'disabled' : '' ?>>
        <i class="fa fa-trash mr-1"></i> Delete All Enquiries
      </button>
    </div>
  </div>

  <!-- Filter Tabs (Scrollable on Mobile) -->
  <ul class="nav nav-pills sa-nav-scroll mb-3 bg-white p-2 rounded border">
    <li class="nav-item mr-1">
      <a class="nav-link <?= $status === 'new' ? 'active font-weight-bold' : '' ?>" href="<?= \App\Core\View::url('/superadmin/enquiries?status=new') ?>">
        New Enquiries (<?= $counts['new'] ?? 0 ?>)
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $status === 'all' ? 'active font-weight-bold' : '' ?>" href="<?= \App\Core\View::url('/superadmin/enquiries?status=all') ?>">
        All Enquiries & Bookings (<?= $counts['all'] ?? 0 ?>)
      </a>
    </li>
  </ul>

  <!-- Enquiries Table -->
  <div class="sa-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 sa-table-min">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">ID</th>
            <th style="white-space: nowrap;">Customer</th>
            <th style="white-space: nowrap;">Service Requested</th>
            <th style="white-space: nowrap;">Est. Price</th>
            <th style="white-space: nowrap;">Submitted On</th>
            <th style="white-space: nowrap;">Status</th>
            <th class="text-right" style="white-space: nowrap;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($enquiries)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="fa fa-inbox fa-3x text-muted mb-2 d-block"></i>
                No enquiries found for this filter.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($enquiries as $e): ?>
              <tr>
                <td class="font-weight-bold text-dark" style="white-space: nowrap;">#<?= $e['id'] ?></td>
                <td>
                  <div class="font-weight-bold text-dark text-truncate" style="max-width: 180px;"><?= \App\Core\View::e($e['customer_name'] ?? $e['name'] ?? 'Guest Customer') ?></div>
                  <small class="text-muted"><i class="fa fa-phone mr-1"></i><?= \App\Core\View::e($e['customer_phone'] ?? $e['phone'] ?? 'N/A') ?></small>
                </td>
                <td>
                  <span class="badge badge-light border text-dark p-2" style="font-size: 13px;">
                    <?= \App\Core\View::e($e['service_name']) ?>
                  </span>
                </td>
                <td class="font-weight-bold text-success" style="white-space: nowrap;">₹<?= number_format((float)(($e['total_amount'] ?? 0) > 0 ? $e['total_amount'] : ($e['starting_price'] ?? 0)), 2) ?></td>
                <td class="text-muted small" style="white-space: nowrap;"><?= date('d M Y, h:i A', strtotime($e['created_at'])) ?></td>
                <td style="white-space: nowrap;">
                  <?php if ($e['status'] === 'new'): ?>
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">New</span>
                  <?php else: ?>
                    <span class="badge badge-info font-weight-bold px-2 py-1"><?= ucfirst($e['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="<?= \App\Core\View::url('/admin/enquiries/' . $e['id']) ?>" class="btn btn-sm btn-outline-secondary mr-1" title="View Enquiry Details">
                    <i class="fa fa-eye"></i> View
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteEnquiry(<?= $e['id'] ?>)" title="Delete Enquiry">
                    <i class="fa fa-trash"></i>
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

<!-- Modal: Delete All Enquiries -->
<div class="modal fade" id="modalSectionDeleteEnquiries" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-trash mr-2"></i>Delete All Customer Enquiries</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/enquiries/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-exclamation-circle fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Confirm Bulk Enquiries Deletion</h5>
          <p class="text-muted small mb-3">
            You are about to delete enquiries from the system. This action cannot be undone.
          </p>
          <div class="form-group text-left bg-light p-3 rounded border">
            <label class="small font-weight-bold text-dark mb-1">Choose deletion scope:</label>
            <div class="custom-control custom-radio mb-1">
              <input type="radio" id="sectionScopeNew" name="scope" value="new" class="custom-control-input" checked>
              <label class="custom-control-label small" for="sectionScopeNew">Delete all <strong>New Enquiries</strong> only</label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="sectionScopeAll" name="scope" value="all" class="custom-control-input">
              <label class="custom-control-label small text-danger font-weight-bold" for="sectionScopeAll">Delete ALL Enquiries & Bookings</label>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Now
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<form id="deleteSingleEnquiryForm" action="" method="POST" style="display:none;">
  <?= \App\Core\View::csrfField() ?>
</form>

<script>
function confirmDeleteEnquiry(id) {
  if (confirm('Permanently delete enquiry #' + id + '?')) {
    var f = document.getElementById('deleteSingleEnquiryForm');
    f.action = '<?= \App\Core\View::url('/superadmin/enquiries/') ?>' + id + '/delete';
    f.submit();
  }
}
</script>
