<div class="container-fluid p-0">
  <!-- Top Banner -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div class="mb-2 mb-md-0">
      <h3 class="font-weight-bold text-dark mb-1" style="letter-spacing: -0.5px;">
        <i class="fa fa-shield mr-2" style="color: #6366f1;"></i>Super Admin Control Center
      </h3>
      <p class="text-muted small mb-0">Highest authority portal: Bulk management, payment gateways, commission models & system purging.</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
      <span class="badge badge-dark px-3 py-2 mr-2" style="font-size: 12px; border-radius: 8px; background: #0f172a;">
        <i class="fa fa-lock text-warning mr-1"></i> Root Super Admin Level
      </span>
      <a href="<?= \App\Core\View::url('/admin') ?>" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fa fa-dashboard mr-1"></i> Operations Admin
      </a>
    </div>
  </div>

  <!-- Primary Metric Cards -->
  <div class="row mb-4">
    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
          <span class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Enquiries</span>
          <span class="badge badge-warning font-weight-bold px-2 py-1" style="font-size: 11px;">New: <?= $enquiriesCount ?></span>
        </div>
        <div class="font-weight-bold text-dark" style="font-size: clamp(20px, 3.5vw, 28px); line-height: 1;"><?= $enquiriesCount ?></div>
        <div class="text-muted small mt-2 d-flex justify-content-between align-items-center flex-wrap" style="font-size: 12px;">
          <span>Total: <?= $totalBookings ?></span>
          <a href="<?= \App\Core\View::url('/superadmin/enquiries') ?>" class="text-primary font-weight-bold">Manage &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
          <span class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Technicians</span>
          <span class="badge badge-info font-weight-bold px-2 py-1" style="font-size: 11px;">Active</span>
        </div>
        <div class="font-weight-bold text-dark" style="font-size: clamp(20px, 3.5vw, 28px); line-height: 1;"><?= $techCount ?></div>
        <div class="text-muted small mt-2 d-flex justify-content-between align-items-center flex-wrap" style="font-size: 12px;">
          <span>Field Staff</span>
          <a href="<?= \App\Core\View::url('/superadmin/staff') ?>" class="text-primary font-weight-bold">Manage &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
          <span class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Customers</span>
          <span class="badge badge-secondary font-weight-bold px-2 py-1" style="font-size: 11px;">Users</span>
        </div>
        <div class="font-weight-bold text-dark" style="font-size: clamp(20px, 3.5vw, 28px); line-height: 1;"><?= $customerCount ?></div>
        <div class="text-muted small mt-2 d-flex justify-content-between align-items-center flex-wrap" style="font-size: 12px;">
          <span>Registered</span>
          <a href="<?= \App\Core\View::url('/superadmin/customers') ?>" class="text-primary font-weight-bold">Manage &rarr;</a>
        </div>
      </div>
    </div>

    <div class="col-6 col-lg-3 mb-3">
      <div class="sa-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
          <span class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Paid Revenue</span>
          <span class="badge badge-success font-weight-bold px-2 py-1" style="font-size: 11px;"><?= $activeGateways ?> GW</span>
        </div>
        <div class="font-weight-bold text-success text-truncate" style="font-size: clamp(18px, 3.2vw, 26px); line-height: 1.1;">₹<?= number_format($totalRevenue, 0) ?></div>
        <div class="text-muted small mt-2 d-flex justify-content-between align-items-center flex-wrap" style="font-size: 12px;">
          <span>Rules: <?= $commRulesCount ?></span>
          <a href="<?= \App\Core\View::url('/superadmin/gateways') ?>" class="text-success font-weight-bold">Gateways &rarr;</a>
        </div>
      </div>
    </div>
  </div>

  <!-- SINGLE-CLICK BULK ACTIONS HUB -->
  <div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
      <h5 class="font-weight-bold text-dark mb-1">
        <i class="fa fa-bolt text-danger mr-2"></i>Single-Click Bulk Management Actions
      </h5>
      <span class="badge badge-danger px-3 py-1 font-weight-bold" style="font-size: 11px;">SUPER ADMIN EXCLUSIVE</span>
    </div>

    <div class="row">
      <!-- 1. Enquiries Bulk Delete Card -->
      <div class="col-lg-4 col-md-6 mb-3">
        <div class="sa-card h-100 border-danger" style="border-width: 1.5px;">
          <div class="sa-card-header bg-light">
            <div class="d-flex align-items-center">
              <span class="badge badge-warning p-2 mr-2"><i class="fa fa-inbox fa-lg text-dark"></i></span>
              <div>
                <h6 class="mb-0 font-weight-bold text-dark">Customer Enquiries</h6>
                <small class="text-muted">Status: <?= $enquiriesCount ?> new inquiries</small>
              </div>
            </div>
            <a href="<?= \App\Core\View::url('/superadmin/enquiries') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
          </div>
          <div class="p-3">
            <p class="text-muted small mb-3">
              Instantly purge customer lead submissions and custom quote requests with a single click.
            </p>
            <button type="button" class="btn btn-danger btn-block font-weight-bold" data-toggle="modal" data-target="#modalBulkDeleteEnquiries" <?= $enquiriesCount === 0 ? 'disabled' : '' ?>>
              <i class="fa fa-trash mr-1"></i> Delete All Enquiries (<?= $enquiriesCount ?>)
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Technicians Bulk Delete Card -->
      <div class="col-lg-4 col-md-6 mb-3">
        <div class="sa-card h-100 border-danger" style="border-width: 1.5px;">
          <div class="sa-card-header bg-light">
            <div class="d-flex align-items-center">
              <span class="badge badge-info p-2 mr-2 text-white"><i class="fa fa-wrench fa-lg text-white"></i></span>
              <div>
                <h6 class="mb-0 font-weight-bold text-dark">Technicians / Staff</h6>
                <small class="text-muted">Total: <?= $techCount ?> registered staff</small>
              </div>
            </div>
            <a href="<?= \App\Core\View::url('/superadmin/staff') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
          </div>
          <div class="p-3">
            <p class="text-muted small mb-3">
              Safely delete all technician accounts, unlinking active jobs and removing skill profiles cleanly.
            </p>
            <button type="button" class="btn btn-danger btn-block font-weight-bold" data-toggle="modal" data-target="#modalBulkDeleteStaff" <?= $techCount === 0 ? 'disabled' : '' ?>>
              <i class="fa fa-trash mr-1"></i> Delete All Technicians (<?= $techCount ?>)
            </button>
          </div>
        </div>
      </div>

      <!-- 3. Customers Bulk Delete Card -->
      <div class="col-lg-4 col-md-6 mb-3">
        <div class="sa-card h-100 border-danger" style="border-width: 1.5px;">
          <div class="sa-card-header bg-light">
            <div class="d-flex align-items-center">
              <span class="badge badge-secondary p-2 mr-2 text-white"><i class="fa fa-users fa-lg text-white"></i></span>
              <div>
                <h6 class="mb-0 font-weight-bold text-dark">Customers Database</h6>
                <small class="text-muted">Total: <?= $customerCount ?> registered customers</small>
              </div>
            </div>
            <a href="<?= \App\Core\View::url('/superadmin/customers') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
          </div>
          <div class="p-3">
            <p class="text-muted small mb-3">
              Permanently delete all customer profiles, addresses, and saved details across the entire system.
            </p>
            <button type="button" class="btn btn-danger btn-block font-weight-bold" data-toggle="modal" data-target="#modalBulkDeleteCustomers" <?= $customerCount === 0 ? 'disabled' : '' ?>>
              <i class="fa fa-trash mr-1"></i> Delete All Customers (<?= $customerCount ?>)
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FINANCIAL & GOVERNANCE SHORTCUTS -->
  <div class="row mb-4">
    <div class="col-lg-6 mb-3">
      <div class="sa-card h-100">
        <div class="sa-card-header">
          <div class="d-flex align-items-center">
            <i class="fa fa-credit-card mr-2 text-primary fa-lg"></i>
            <h6 class="font-weight-bold mb-0 text-dark">Payment Gateways & Secrets</h6>
          </div>
          <a href="<?= \App\Core\View::url('/superadmin/gateways') ?>" class="btn btn-sm btn-primary font-weight-bold">Configure Gateways</a>
        </div>
        <div class="p-3">
          <p class="text-muted small mb-3">
            Moved exclusively to Super Admin. Manage Razorpay live/test credentials, Key ID, Secret, Webhook secrets, and toggle payment availability.
          </p>
          <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border">
            <div>
              <strong class="text-dark">Razorpay Gateway</strong>
              <div class="text-muted small">Auto-synced with platform checkout</div>
            </div>
            <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i> Active</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-3">
      <div class="sa-card h-100">
        <div class="sa-card-header">
          <div class="d-flex align-items-center">
            <i class="fa fa-percent mr-2 text-warning fa-lg"></i>
            <h6 class="font-weight-bold mb-0 text-dark">Commission & Profit Splits</h6>
          </div>
          <a href="<?= \App\Core\View::url('/superadmin/commissions') ?>" class="btn btn-sm btn-warning font-weight-bold text-dark">Manage Splits</a>
        </div>
        <div class="p-3">
          <p class="text-muted small mb-3">
            Moved exclusively to Super Admin. Define the company revenue cut, worker payout percentages, service-specific overrides, and settle earnings.
          </p>
          <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border">
            <div>
              <strong class="text-dark">Global Split Rule</strong>
              <div class="text-muted small">Company vs Labour standard breakdown</div>
            </div>
            <span class="badge badge-info px-2 py-1"><?= $commRulesCount ?> Active Rules</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- RECENT ENQUIRIES PREVIEW -->
  <div class="sa-card">
    <div class="sa-card-header">
      <div class="d-flex align-items-center">
        <i class="fa fa-clock-o mr-2 text-muted"></i>
        <h6 class="font-weight-bold mb-0 text-dark">Recent Customer Enquiries</h6>
      </div>
      <a href="<?= \App\Core\View::url('/superadmin/enquiries') ?>" class="btn btn-xs btn-outline-primary">View Full Enquiries Section &rarr;</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 sa-table-min">
        <thead class="bg-light">
          <tr>
            <th style="white-space: nowrap;">Booking #</th>
            <th style="white-space: nowrap;">Customer</th>
            <th style="white-space: nowrap;">Service</th>
            <th style="white-space: nowrap;">Amount</th>
            <th style="white-space: nowrap;">Date</th>
            <th style="white-space: nowrap;">Status</th>
            <th class="text-right" style="white-space: nowrap;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentEnquiries)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="fa fa-check-circle text-success fa-2x d-block mb-2"></i>
                No pending new enquiries in the database.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentEnquiries as $enq): ?>
              <tr>
                <td class="font-weight-bold text-dark" style="white-space: nowrap;"><?= \App\Core\View::e($enq['booking_no'] ?? ('#' . $enq['id'])) ?></td>
                <td>
                  <div class="font-weight-bold text-dark text-truncate" style="max-width: 180px;"><?= \App\Core\View::e(!empty($enq['customer_name']) ? $enq['customer_name'] : (!empty($enq['lead_name']) ? $enq['lead_name'] : 'Guest Customer')) ?></div>
                  <small class="text-muted"><i class="fa fa-phone mr-1"></i><?= \App\Core\View::e(!empty($enq['customer_phone']) ? $enq['customer_phone'] : (!empty($enq['lead_phone']) ? $enq['lead_phone'] : 'N/A')) ?></small>
                </td>
                <td><span class="badge badge-light border text-dark p-2" style="font-size: 12.5px;"><?= \App\Core\View::e($enq['service_name']) ?></span></td>
                <td class="font-weight-bold text-success" style="white-space: nowrap;">₹<?= number_format((float)(($enq['total_amount'] ?? 0) > 0 ? $enq['total_amount'] : ($enq['starting_price'] ?? 0)), 2) ?></td>
                <td class="text-muted small" style="white-space: nowrap;"><?= date('d M Y, h:i A', strtotime($enq['created_at'])) ?></td>
                <td style="white-space: nowrap;"><span class="badge badge-warning text-dark font-weight-bold px-2 py-1">New Enquiry</span></td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="<?= \App\Core\View::url('/admin/enquiries/' . $enq['id']) ?>" class="btn btn-xs btn-outline-secondary mr-1" title="View Enquiry Details">
                    <i class="fa fa-eye"></i>
                  </a>
                  <button type="button" class="btn btn-xs btn-outline-danger" onclick="confirmDeleteSingleEnquiry(<?= $enq['id'] ?>)" title="Delete Single Enquiry">
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

<!-- ======================================================== -->
<!-- CONFIRMATION MODALS FOR 1-CLICK BULK DELETIONS -->
<!-- ======================================================== -->

<!-- 1. Modal: Delete All Enquiries -->
<div class="modal fade" id="modalBulkDeleteEnquiries" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-exclamation-triangle mr-2"></i>Delete All Customer Enquiries?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/enquiries/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-trash-o fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Are you absolutely sure?</h5>
          <p class="text-muted small mb-3">
            This single click will permanently remove all <strong><?= $enquiriesCount ?></strong> customer lead submissions and pending inquiries from the database.
          </p>
          <div class="form-group text-left bg-light p-3 rounded border">
            <label class="small font-weight-bold text-dark mb-1">Select deletion scope:</label>
            <div class="custom-control custom-radio mb-1">
              <input type="radio" id="scopeNew" name="scope" value="new" class="custom-control-input" checked>
              <label class="custom-control-label small" for="scopeNew">Delete all <strong>New Enquiries</strong> only (Safe / Lead purge)</label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="scopeAll" name="scope" value="all" class="custom-control-input">
              <label class="custom-control-label small text-danger" for="scopeAll">Delete <strong>ALL Bookings & Enquiries</strong> entirely</label>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Enquiries
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. Modal: Delete All Technicians -->
<div class="modal fade" id="modalBulkDeleteStaff" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-exclamation-triangle mr-2"></i>Delete All Technicians?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/staff/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-user-times fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= $techCount ?> Technician accounts?</h5>
          <p class="text-muted small mb-0">
            This will permanently remove all staff accounts, their associated earnings records, availability schedules, and skill profiles. Any assigned jobs will be reset safely.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Technicians
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 3. Modal: Delete All Customers -->
<div class="modal fade" id="modalBulkDeleteCustomers" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-exclamation-triangle mr-2"></i>Delete All Customers?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/customers/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-users fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= $customerCount ?> Customer accounts?</h5>
          <p class="text-muted small mb-0">
            This single click will wipe all registered customer user records, customer profiles, saved addresses, and consent logs. Existing bookings will have customer links safely detached.
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

<!-- 4. Modal: Delete All Bookings & Jobs -->
<div class="modal fade" id="modalBulkDeleteBookings" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-exclamation-triangle mr-2"></i>Delete All Bookings & Jobs?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/bookings/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <input type="hidden" name="redirect" value="/superadmin">
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-trash fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Delete all <?= $totalBookings ?> Bookings & Jobs?</h5>
          <p class="text-muted small mb-0">
            This will permanently wipe all customer service bookings, field jobs, job attachments, invoices, and payment records across the marketplace. This action cannot be reversed.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete All Bookings & Jobs
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 5. Modal: Purge Dispatch Calendar -->
<div class="modal fade" id="modalBulkDeleteCalendar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-calendar-times-o mr-2"></i>Purge Dispatch Calendar?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= \App\Core\View::url('/superadmin/calendar/delete-all') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>
        <input type="hidden" name="redirect" value="/superadmin">
        <input type="hidden" name="scope" value="all">
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-calendar-times-o fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Purge all <?= $calendarCount ?> Scheduled Appointments?</h5>
          <p class="text-muted small mb-0">
            This will delete all scheduled calendar appointments and technician dispatch schedules across all months.
          </p>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Purge Dispatch Calendar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Form for single delete -->
<form id="singleEnquiryDeleteForm" action="" method="POST" style="display: none;">
  <?= \App\Core\View::csrfField() ?>
</form>

<script>
function confirmDeleteSingleEnquiry(id) {
  if (confirm('Are you sure you want to delete enquiry #' + id + '?')) {
    var form = document.getElementById('singleEnquiryDeleteForm');
    form.action = '<?= \App\Core\View::url('/superadmin/enquiries/') ?>' + id + '/delete';
    form.submit();
  }
}
</script>
