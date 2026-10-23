<?php
use App\Core\View;
$month = $month ?? date('Y-m');
$jobs = $jobs ?? [];

$prevMonth = date('Y-m', strtotime($month . '-01 -1 month'));
$nextMonth = date('Y-m', strtotime($month . '-01 +1 month'));
$monthTitle = date('F Y', strtotime($month . '-01'));

// Group jobs by date
$jobsByDate = [];
foreach ($jobs as $j) {
    $d = $j['preferred_date'] ?? 'Unscheduled';
    if (!isset($jobsByDate[$d])) {
        $jobsByDate[$d] = [];
    }
    $jobsByDate[$d][] = $j;
}
ksort($jobsByDate);
?>

<div class="admin-calendar-page">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="font-weight-bold mb-1 text-dark">Dispatch & Field Operations Calendar</h4>
      <p class="text-muted small mb-0">Scheduled service appointments, technician dispatches, and daily workload distribution.</p>
    </div>

    <!-- Month Navigation -->
    <div class="d-flex align-items-center flex-wrap gap-2">
      <a href="<?= View::url('/admin/calendar?month=' . $prevMonth) ?>" class="btn btn-sm btn-light border font-weight-bold">
        <i class="fa fa-chevron-left mr-1"></i>Prev
      </a>
      <span class="font-weight-bold text-dark px-3 py-1 bg-white border rounded" style="font-size: 15px;">
        <?= View::e($monthTitle) ?>
      </span>
      <a href="<?= View::url('/admin/calendar?month=' . $nextMonth) ?>" class="btn btn-sm btn-light border font-weight-bold">
        Next<i class="fa fa-chevron-right ml-1"></i>
      </a>
      <a href="<?= View::url('/admin/calendar?month=' . date('Y-m')) ?>" class="btn btn-sm btn-brand ml-2 font-weight-bold">
        Current Month
      </a>
      <?php if (\App\Core\Auth::isAdmin()): ?>
        <button type="button" class="btn btn-sm btn-danger font-weight-bold ml-md-2 mt-2 mt-md-0" data-toggle="modal" data-target="#modalBulkDeleteCalendar">
          <i class="fa fa-trash mr-1"></i>Clean Dispatch Calendar
        </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($jobsByDate)): ?>
    <div class="stat-card text-center py-5">
      <i class="fa fa-calendar-o fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
      <h5 class="font-weight-bold text-dark">No appointments scheduled for <?= View::e($monthTitle) ?></h5>
      <p class="text-muted small mb-3">Incoming bookings will populate on their respective scheduled dates automatically.</p>
      <a href="<?= View::url('/admin/bookings') ?>" class="btn btn-sm btn-brand font-weight-bold px-3">
        View All Bookings
      </a>
    </div>
  <?php else: ?>
    <?php foreach ($jobsByDate as $date => $dayJobs): ?>
      <div class="stat-card mb-3 p-0 overflow-hidden">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
          <div class="font-weight-bold text-dark">
            <i class="fa fa-calendar text-primary mr-2"></i>
            <?= View::e(date('l, d F Y', strtotime($date))) ?>
          </div>
          <span class="badge badge-light border text-muted font-weight-bold">
            <?= count($dayJobs) ?> appointments
          </span>
        </div>

        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <tbody>
              <?php foreach ($dayJobs as $j): ?>
                <tr>
                  <td style="width: 130px;">
                    <a href="<?= View::url('/admin/bookings/' . $j['booking_id']) ?>" class="font-weight-bold text-dark">
                      #<?= View::e($j['booking_no']) ?>
                    </a>
                  </td>
                  <td style="width: 180px;">
                    <span class="badge badge-light border text-dark font-weight-bold">
                      <i class="fa fa-clock-o mr-1 text-muted"></i><?= View::e($j['preferred_time'] ?? 'Flexible') ?>
                    </span>
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark"><?= View::e($j['customer_name']) ?></div>
                    <div class="text-muted small"><?= View::e($j['service_name']) ?></div>
                  </td>
                  <td>
                    <?php if (!empty($j['staff_name'])): ?>
                      <span class="badge badge-success text-white font-weight-bold px-2 py-1">
                        <i class="fa fa-user mr-1"></i><?= View::e($j['staff_name']) ?>
                      </span>
                    <?php else: ?>
                      <span class="badge badge-warning text-dark px-2 py-1">
                        <i class="fa fa-exclamation-triangle mr-1"></i>Unassigned
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge badge-status-<?= View::e($j['booking_status']) ?>">
                      <?= ucfirst(str_replace('_', ' ', $j['booking_status'])) ?>
                    </span>
                  </td>
                  <td class="text-right">
                    <a href="<?= View::url('/admin/bookings/' . $j['booking_id']) ?>" class="btn btn-sm btn-brand-outline py-1 px-2 font-weight-bold">
                      Manage &rarr;
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if (\App\Core\Auth::isAdmin()): ?>
<!-- Modal: Purge Dispatch Calendar -->
<div class="modal fade" id="modalBulkDeleteCalendar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fa fa-calendar-times-o mr-2"></i>Clean Dispatch Calendar?</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form action="<?= View::url('/admin/calendar/delete-all') ?>" method="POST">
        <?= View::csrfField() ?>
        <input type="hidden" name="redirect" value="/admin/calendar?month=<?= View::e($month) ?>">
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3"><i class="fa fa-calendar-times-o fa-4x"></i></div>
          <h5 class="font-weight-bold text-dark">Purge Dispatch Appointments?</h5>
          <p class="text-muted small mb-3">
            Choose whether to delete only appointments in the currently viewed month (<strong><?= View::e($monthTitle) ?></strong>) or wipe the entire dispatch calendar across all dates.
          </p>

          <div class="text-left bg-light p-3 rounded border mb-3">
            <div class="custom-control custom-radio mb-2">
              <input type="radio" id="scopeMonth" name="scope" value="month" class="custom-control-input" checked>
              <label class="custom-control-label font-weight-bold text-dark" for="scopeMonth">
                Delete only appointments in <?= View::e($monthTitle) ?> (<?= count($jobs) ?> bookings)
              </label>
              <input type="hidden" name="month" value="<?= View::e($month) ?>">
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="scopeAll" name="scope" value="all" class="custom-control-input">
              <label class="custom-control-label font-weight-bold text-danger" for="scopeAll">
                Delete ALL scheduled calendar appointments across all months
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger font-weight-bold px-4">
            <i class="fa fa-trash mr-1"></i> Yes, Delete Dispatches
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
