<?php
use App\Core\View;
$booking = $booking ?? [];
$staffMembers = $staffMembers ?? [];
$attachments = $attachments ?? [];
?>

<div class="admin-enquiry-show">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <a href="<?= View::url('/admin/enquiries') ?>" class="text-muted small font-weight-bold">
        <i class="fa fa-arrow-left mr-1"></i>Back to Enquiries
      </a>
      <h4 class="font-weight-bold text-dark mb-0">Enquiry #<?= View::e($booking['booking_no']) ?></h4>
    </div>
    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
      <span class="badge badge-status-<?= View::e($booking['status']) ?> px-3 py-2 font-weight-bold" style="font-size: 13px;">
        STATUS: <?= strtoupper(str_replace('_', ' ', $booking['status'])) ?>
      </span>
      <?php
        $bPayType = strtolower(trim((string)($booking['payment_type'] ?? '')));
        $bAdvAmt  = (float)($booking['advance_amount'] ?? 0);
        $bDueAmt  = (float)($booking['due_amount'] ?? 0);
        $bPaidStatus = (string)($booking['payment_paid_status'] ?? '');
        $bPriority = strtolower(trim((string)($booking['priority'] ?? '')));

        $bIsFullPaid = false;
        $bIsPrePaid  = false;
        if ($bPaidStatus === 'paid') {
            if ($bPayType === 'online' || $bDueAmt <= 0) {
                $bIsFullPaid = true;
            } else {
                $bIsPrePaid = true;
            }
        } else {
            if ($bPayType === 'online' || $bPriority === 'high') {
                $bIsFullPaid = true;
            } elseif ($bPayType === 'cod' || $bAdvAmt == 49.0 || $bPriority === 'normal') {
                $bIsPrePaid = true;
            }
        }
      ?>
      <?php if ($bIsFullPaid): ?>
        <span class="badge badge-pay-paid px-3 py-2 font-weight-bold" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 13px;" title="100% Full Payment Paid Online">
          <i class="fa fa-check-circle mr-1"></i>PAID (Full Online)
        </span>
      <?php elseif ($bIsPrePaid): ?>
        <span class="badge badge-pay-pre px-3 py-2 font-weight-bold" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: 13px;" title="Pre-booking Advance ₹49 Paid">
          PRE (₹49 Advance Paid &bull; COD)
        </span>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">
    <!-- Left Column: Details -->
    <div class="col-lg-7">
      <!-- Customer Card -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-user-circle text-primary mr-2"></i>Customer Information</h6>
        <div class="row">
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Customer Name</div>
            <div class="font-weight-bold text-dark"><?= View::e($booking['name']) ?></div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Contact Phone</div>
            <div>
              <a href="tel:<?= View::e($booking['phone']) ?>" class="text-success font-weight-bold">
                <i class="fa fa-phone mr-1"></i><?= View::e($booking['phone']) ?>
              </a>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Email Address</div>
            <div class="text-dark"><?= View::e($booking['email'] ?? 'Not provided') ?></div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="text-muted small">Preferred Schedule</div>
            <div class="text-dark font-weight-bold">
              <i class="fa fa-calendar mr-1 text-muted"></i><?= View::e($booking['preferred_date'] ?? 'Flexible') ?> (<?= View::e($booking['preferred_time'] ?? '') ?>)
            </div>
          </div>
          <div class="col-12 mt-2">
            <div class="text-muted small">Service Address</div>
            <div class="p-2 bg-light rounded text-dark mt-1">
              <i class="fa fa-map-marker text-danger mr-1"></i><?= View::e($booking['address']) ?>
              <?php if (!empty($booking['pincode'])): ?>
                &bull; Pincode: <strong><?= View::e($booking['pincode']) ?></strong>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Service Requested & Issue Details -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-wrench text-info mr-2"></i>Service Requested</h6>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <span class="badge badge-light border text-muted"><?= View::e($booking['category_name'] ?? 'General') ?></span>
            <h5 class="font-weight-bold text-dark mt-1 mb-0"><?= View::e($booking['service_name']) ?></h5>
          </div>
          <div class="text-right">
            <div class="text-muted small">Total Service Amount</div>
            <h4 class="font-weight-bold text-success mb-0">₹<?= number_format((float)(($booking['total_amount'] ?? 0) > 0 ? $booking['total_amount'] : ($booking['starting_price'] ?? 0)), 2) ?></h4>
          </div>
        </div>

        <?php 
          $items = $booking['items'] ?? \App\Models\Booking::getItems((int)$booking['id'], $booking);
          $addons = array_values(array_filter($items, fn($i) => ($i['item_type'] ?? '') === 'addon'));
          $totAmt = (float)(($booking['total_amount'] ?? 0) > 0 ? $booking['total_amount'] : ($booking['starting_price'] ?? 0));
        ?>

        <?php if (!empty($addons)): ?>
          <div class="border-top pt-3 mt-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="small font-weight-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">
                <i class="fa fa-cubes text-warning mr-1"></i>Included Add-ons & Service Breakdown (<?= count($addons) ?> Add-on<?= count($addons) > 1 ? 's' : '' ?>)
              </span>
              <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px; background: #fef08a; border: 1px solid #fde047;">
                ⚡ Customer Selected Add-ons
              </span>
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 13px;">
                <thead class="thead-light">
                  <tr>
                    <th>Item / Add-on Description</th>
                    <th style="width: 120px;" class="text-center">Type</th>
                    <th style="width: 70px;" class="text-center">Qty</th>
                    <th style="width: 120px;" class="text-right">Price</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $itm): ?>
                    <tr class="<?= ($itm['item_type'] ?? '') === 'addon' ? 'bg-light font-weight-bold' : '' ?>">
                      <td>
                        <?php if (($itm['item_type'] ?? '') === 'addon'): ?>
                          <i class="fa fa-plus-circle text-success mr-1"></i>
                          <span class="text-dark"><?= View::e($itm['name']) ?></span>
                          <?php if (!empty($itm['badge'])): ?>
                            <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;"><?= View::e($itm['badge']) ?></span>
                          <?php endif; ?>
                        <?php else: ?>
                          <i class="fa fa-wrench text-primary mr-1"></i>
                          <span><?= View::e($itm['name']) ?></span>
                          <span class="text-muted small ml-1">(Primary Service)</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <?php if (($itm['item_type'] ?? '') === 'addon'): ?>
                          <span class="badge badge-warning px-2 py-0" style="font-size: 10.5px; background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">+ Add-on</span>
                        <?php else: ?>
                          <span class="badge badge-primary px-2 py-0" style="font-size: 10.5px;">Main Service</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center"><?= (int)($itm['quantity'] ?? 1) ?></td>
                      <td class="text-right text-dark">₹<?= number_format((float)($itm['total_price'] ?? $itm['unit_price'] ?? 0), 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr style="background: #f8fafc;">
                    <th colspan="3" class="text-right text-dark font-weight-bold">Total Service Value:</th>
                    <th class="text-right text-success font-weight-bold" style="font-size: 14.5px;">₹<?= number_format($totAmt, 2) ?></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <div class="border-top pt-3 mt-3">
          <div class="small font-weight-bold text-dark mb-1">Customer Issue Notes:</div>
          <?php if (!empty($booking['issue_details'])): ?>
            <div class="p-3 bg-light rounded small text-dark"><?= nl2br(View::e($booking['issue_details'])) ?></div>
          <?php else: ?>
            <div class="text-muted small font-italic">No additional notes provided by customer.</div>
          <?php endif; ?>
        </div>

        <?php if (!empty($attachments)): ?>
          <div class="border-top pt-3 mt-3">
            <div class="small font-weight-bold text-dark mb-2">Issue Attachments / Photos:</div>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($attachments as $att): ?>
                <a href="<?= View::asset($att['file_path']) ?>" target="_blank" class="mr-2 mb-2 border rounded overflow-hidden d-inline-block shadow-sm" style="width: 80px; height: 80px;">
                  <img src="<?= View::asset($att['file_path']) ?>" alt="Attachment" style="width: 100%; height: 100%; object-fit: cover;">
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Payment & Financial Status -->
      <div class="stat-card mb-3">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-credit-card text-success mr-2"></i>Payment & Financial Status</h6>
        <div class="row text-center">
          <div class="col-4">
            <div class="text-muted small">Total Value</div>
            <div class="font-weight-bold text-dark" style="font-size: 16px;">
              ₹<?= number_format((float)(($booking['total_amount'] ?? 0) > 0 ? $booking['total_amount'] : ($booking['starting_price'] ?? 0)), 2) ?>
            </div>
          </div>
          <div class="col-4">
            <div class="text-muted small"><?= $bIsFullPaid ? 'Paid Online' : 'Advance Paid' ?></div>
            <div class="font-weight-bold text-success" style="font-size: 16px;">
              ₹<?= number_format((float)(($booking['advance_amount'] ?? 0) > 0 ? $booking['advance_amount'] : ($bIsPrePaid ? 49 : 0)), 2) ?>
            </div>
          </div>
          <div class="col-4">
            <div class="text-muted small">Due After Service</div>
            <div class="font-weight-bold <?= $bDueAmt > 0 ? 'text-danger' : 'text-muted' ?>" style="font-size: 16px;">
              ₹<?= number_format($bDueAmt, 2) ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Operations (Notes, Priority, Assign Technician) -->
    <div class="col-lg-5">
      <!-- Assign Technician Form -->
      <div class="stat-card mb-3" style="border: 2px solid #f25b29; background: #fff8f5;">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-motorcycle text-success mr-2"></i>Assign Technician</h6>
        
        <?php if (!empty($booking['staff_name'])): ?>
          <div class="alert alert-info py-2 px-3 small font-weight-bold mb-3">
            <i class="fa fa-info-circle mr-1"></i>Currently assigned to: <strong><?= View::e($booking['staff_name']) ?></strong> (Status: <?= ucfirst($booking['job_status'] ?? 'assigned') ?>)
          </div>
        <?php endif; ?>

        <?php if (!empty($addons)): ?>
          <div class="p-2 mb-3 rounded" style="background: #fffbeb; border: 1.5px dashed #f59e0b; font-size: 12.5px;">
            <div class="font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between">
              <span><i class="fa fa-cubes text-warning mr-1"></i>Customer Booked Add-ons:</span>
              <span class="badge badge-warning text-dark font-weight-bold"><?= count($addons) ?> Add-on<?= count($addons) > 1 ? 's' : '' ?></span>
            </div>
            <ul class="mb-1 pl-3 text-dark">
              <?php foreach ($addons as $ad): ?>
                <li>
                  <strong><?= View::e($ad['name']) ?></strong> 
                  <span class="text-success font-weight-bold">(+₹<?= number_format((float)($ad['total_price'] ?? $ad['unit_price'] ?? 0), 0) ?>)</span>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="text-muted" style="font-size: 11px;">Make sure the technician carries appropriate tools/consumables for these add-ons.</div>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= View::url('/admin/bookings/' . $booking['id'] . '/assign') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Select Field Technician</label>
            <select name="staff_id" class="form-control" required>
              <option value="">-- Choose active technician --</option>
              <?php foreach ($staffMembers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($booking['staff_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                  <?= View::e($s['name']) ?> (★ <?= number_format((float)($s['rating_avg'] ?? 5.0), 1) ?>) <?= !empty($s['is_available']) ? '🟢 Available' : '🔴 Busy/Off' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Dispatch / Scheduled Time</label>
            <input type="datetime-local" name="scheduled_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
          </div>

          <button type="submit" class="btn btn-brand btn-block font-weight-bold py-2">
            <i class="fa fa-check-circle mr-1"></i>Confirm & Assign Technician
          </button>
          <div class="text-muted mt-2 text-center" style="font-size: 11px; line-height: 1.3;">
            <i class="fa fa-envelope text-success mr-1"></i>Both technician & customer will automatically receive assignment confirmation emails.
          </div>
        </form>
      </div>

      <!-- Update Enquiry Status, Priority & Admin Notes -->
      <div class="stat-card">
        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-edit text-muted mr-2"></i>Update Enquiry Details</h6>
        
        <form method="POST" action="<?= View::url('/admin/enquiries/' . $booking['id'] . '/update') ?>">
          <?= View::csrfField() ?>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Priority</label>
            <select name="priority" class="form-control">
              <option value="normal" <?= ($booking['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
              <option value="high" <?= ($booking['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
              <option value="urgent" <?= ($booking['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
              <option value="low" <?= ($booking['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-dark">Internal Admin Notes</label>
            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add internal notes about customer conversation, discount discussed, special tools required..."><?= View::e($booking['admin_notes'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-outline-secondary btn-block font-weight-bold">
            <i class="fa fa-save mr-1"></i>Save Enquiry Notes
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

