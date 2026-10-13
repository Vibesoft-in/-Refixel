<div class="container-fluid p-0">
  <!-- Page Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div>
      <h4 class="mb-1 text-dark" style="font-weight: 800; letter-spacing: -0.3px;">
        <i class="fa fa-percent mr-2" style="color: #f25b29;"></i>Commission Management & Profit Splits
      </h4>
      <p class="text-muted small mb-0">Manage service charge distribution, labour shares, company profit margins, and material cost deductions.</p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
      <button type="button" class="btn btn-sm btn-brand shadow-sm font-weight-bold" data-toggle="modal" data-target="#newRuleModal">
        <i class="fa fa-plus-circle mr-1"></i> Add Category / Tier Rule
      </button>
    </div>
  </div>

  <!-- Summary KPI Cards -->
  <div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="stat-card p-3 bg-white rounded border shadow-sm">
        <div class="stat-label text-muted small font-weight-bold">Global Split Ratio</div>
        <div class="stat-value text-dark" style="font-size: 22px; font-weight: 800;">
          <span style="color: #f25b29;"><?= (float)($settings['global_admin_commission_pct'] ?? 25) ?>%</span> 
          <small class="text-muted font-weight-normal">/</small> 
          <span style="color: #10b981;"><?= (float)($settings['global_worker_commission_pct'] ?? 75) ?>%</span>
        </div>
        <span class="small text-muted">Company vs Technician</span>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="stat-card p-3 bg-white rounded border shadow-sm">
        <div class="stat-label text-muted small font-weight-bold">Total Labour Payouts</div>
        <div class="stat-value text-success" style="font-size: 22px; font-weight: 800;">
          ₹<?= number_format($totalPayouts, 2) ?>
        </div>
        <span class="small text-muted">Disbursed to technicians</span>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="stat-card p-3 bg-white rounded border shadow-sm">
        <div class="stat-label text-muted small font-weight-bold">Total Company Retained</div>
        <div class="stat-value" style="font-size: 22px; font-weight: 800; color: #0284c7;">
          ₹<?= number_format($totalAdminFees, 2) ?>
        </div>
        <span class="small text-muted">Platform margin retained</span>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="stat-card p-3 bg-white rounded border shadow-sm">
        <div class="stat-label text-muted small font-weight-bold">Pending Settlements</div>
        <div class="stat-value text-warning" style="font-size: 22px; font-weight: 800;">
          ₹<?= number_format($pendingPayouts, 2) ?>
        </div>
        <span class="small text-muted">Awaiting weekly/monthly payout</span>
      </div>
    </div>
  </div>

  <!-- Interactive Commission Split Simulator & Calculator -->
  <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px; overflow: hidden;">
    <div class="card-header text-white d-flex align-items-center justify-content-between py-3" style="background: linear-gradient(135deg, #0a1c33 0%, #112845 100%); border-bottom: 2px solid #f25b29;">
      <div class="d-flex align-items-center">
        <i class="fa fa-calculator mr-2" style="font-size: 18px; color: #f25b29;"></i>
        <h6 class="mb-0 font-weight-bold text-white">Live Commission & Material Deduction Simulator</h6>
      </div>
      <span class="badge" style="background: rgba(242, 91, 41, 0.2); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.4); font-size: 11px;">
        Instant Real-Time Math
      </span>
    </div>
    <div class="card-body p-4 bg-white">
      <p class="text-muted small mb-4">
        Simulate how any booking charge is split between <strong>Company Share</strong> and <strong>Labour Payout</strong> after deducting the cost of <strong>Material / Spare Parts used</strong>.
      </p>

      <div class="row">
        <!-- Simulator Inputs -->
        <div class="col-lg-5 col-md-6 mb-3">
          <div class="bg-light p-3 rounded border">
            <h6 class="font-weight-bold text-dark small text-uppercase mb-3">
              <i class="fa fa-sliders mr-1 text-primary"></i> Simulation Inputs
            </h6>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Total Service Charge (Gross Amount) *</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                <input type="number" id="sim_gross" class="form-control font-weight-bold" value="2000" min="0" step="50" oninput="runSimulation()">
              </div>
              <small class="form-text text-muted">Customer invoice amount / service charge.</small>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Material / Spare Parts Cost (Material Used) *</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                <input type="number" id="sim_material" class="form-control font-weight-bold text-danger" value="400" min="0" step="20" oninput="runSimulation()">
              </div>
              <small class="form-text text-muted">Cost of industrial consumables or spare parts replaced.</small>
            </div>

            <div class="row">
              <div class="col-6 form-group mb-3">
                <label class="small font-weight-bold text-dark">Worker Tier</label>
                <select id="sim_tier" class="form-control form-control-sm" onchange="runSimulation()">
                  <option value="all">Global Default</option>
                  <option value="senior">Senior Specialist (80% / 20%)</option>
                  <option value="standard" selected>Standard Technician (75% / 25%)</option>
                  <option value="trainee">Apprentice / Helper (65% / 35%)</option>
                </select>
              </div>
              <div class="col-6 form-group mb-3">
                <label class="small font-weight-bold text-dark">Material Paid By</label>
                <select id="sim_payer" class="form-control form-control-sm" onchange="runSimulation()">
                  <option value="worker" selected>Technician (Upfront)</option>
                  <option value="company">Company Inventory</option>
                  <option value="customer">Customer Supplied</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Simulator Live Split Output Cards -->
        <div class="col-lg-7 col-md-6 mb-3">
          <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between" style="background: #fafbfc;">
            <h6 class="font-weight-bold text-dark small text-uppercase mb-3">
              <i class="fa fa-pie-chart mr-1" style="color: #f25b29;"></i> Calculated Profit & Payout Breakdown
            </h6>

            <!-- Math Waterfall -->
            <div class="table-responsive mb-3">
              <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 13.5px;">
                <tbody>
                  <tr>
                    <td class="text-muted"><i class="fa fa-arrow-right text-primary mr-1"></i> Gross Service Charge</td>
                    <td class="text-right font-weight-bold text-dark" id="disp_gross">₹2,000.00</td>
                  </tr>
                  <tr class="table-warning">
                    <td class="text-danger font-weight-bold"><i class="fa fa-minus-circle mr-1"></i> Less: Material Used Deduction</td>
                    <td class="text-right font-weight-bold text-danger" id="disp_material">- ₹400.00</td>
                  </tr>
                  <tr class="table-light">
                    <td class="font-weight-bold text-dark"><i class="fa fa-balance-scale mr-1 text-info"></i> Net Commissionable Base</td>
                    <td class="text-right font-weight-bold text-dark" id="disp_net">₹1,600.00</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Big Split Outcome Badges -->
            <div class="row">
              <!-- Company Share Box -->
              <div class="col-sm-6 mb-2">
                <div class="p-3 rounded border text-center" style="background: #fff8f5; border-color: #ffd8cc !important;">
                  <span class="badge badge-pill text-uppercase font-weight-bold px-2 py-1 mb-2" style="background: rgba(242, 91, 41, 0.15); color: #f25b29;">
                    Company / Admin Share (<span id="disp_admin_pct">25</span>%)
                  </span>
                  <div class="display-4 font-weight-bold mb-1" id="disp_admin_share" style="font-size: 26px; color: #f25b29;">₹400.00</div>
                  <small class="text-muted d-block" id="disp_company_note">Retained platform gross profit</small>
                </div>
              </div>

              <!-- Labour Payout Box -->
              <div class="col-sm-6 mb-2">
                <div class="p-3 rounded border text-center" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                  <span class="badge badge-pill text-uppercase font-weight-bold px-2 py-1 mb-2" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                    Labour / Tech Payout (<span id="disp_worker_pct">75</span>%)
                  </span>
                  <div class="display-4 font-weight-bold mb-1 text-success" id="disp_worker_payout" style="font-size: 26px;">₹1,600.00</div>
                  <small class="text-muted d-block" id="disp_worker_note">₹1,200 split + ₹400 material reimbursement</small>
                </div>
              </div>
            </div>

            <div class="alert alert-light border small text-muted mb-0 py-2 mt-2">
              <i class="fa fa-info-circle mr-1 text-primary"></i>
              <strong>Formula Applied:</strong> <span id="disp_formula">Gross (₹2000) - Material (₹400) = Net (₹1600). Admin gets 25% (₹400), Labour gets 75% (₹1200) + Material (₹400) = ₹1,600.00</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Global Commission Settings Form -->
    <div class="col-lg-5 mb-4">
      <div class="card shadow-sm border h-100" style="border-radius: 10px;">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="mb-0 font-weight-bold text-dark">
            <i class="fa fa-globe mr-2" style="color: #f25b29;"></i>Global Commission Policy
          </h6>
        </div>
        <div class="card-body p-4">
          <form action="<?= \App\Core\View::url('/admin/commissions/global') ?>" method="POST">
            <?= \App\Core\View::csrfField() ?>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Company / Admin Commission (%) *</label>
              <div class="input-group">
                <input type="number" name="global_admin_commission_pct" id="global_admin_pct" class="form-control font-weight-bold" value="<?= (float)($settings['global_admin_commission_pct'] ?? 25.0) ?>" min="0" max="100" step="0.5" oninput="syncWorkerPct(this.value)" required>
                <div class="input-group-append"><span class="input-group-text bg-white">%</span></div>
              </div>
              <small class="form-text text-muted">Percentage of net service charge retained by company.</small>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Technician / Labour Share (%) *</label>
              <div class="input-group">
                <input type="number" id="global_worker_pct" class="form-control font-weight-bold text-success" value="<?= (float)($settings['global_worker_commission_pct'] ?? 75.0) ?>" readonly>
                <div class="input-group-append"><span class="input-group-text bg-white">%</span></div>
              </div>
              <small class="form-text text-muted">Automatically calculated (100% - Company %).</small>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Material Deduction Policy *</label>
              <select name="global_material_deduction_rule" class="form-control">
                <option value="deduct_before_split" <?= ($settings['global_material_deduction_rule'] ?? 'deduct_before_split') === 'deduct_before_split' ? 'selected' : '' ?>>
                  Deduct material cost before split (Standard & Recommended)
                </option>
                <option value="no_deduct" <?= ($settings['global_material_deduction_rule'] ?? '') === 'no_deduct' ? 'selected' : '' ?>>
                  Split gross charge directly without deducting materials
                </option>
              </select>
              <small class="form-text text-muted">Guarantees that consumable costs do not dilute profit margins.</small>
            </div>

            <div class="form-group mb-4">
              <label class="small font-weight-bold text-dark">Minimum Guaranteed Technician Payout (₹) *</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                <input type="number" name="global_min_worker_payout" class="form-control font-weight-bold" value="<?= (float)($settings['global_min_worker_payout'] ?? 250.0) ?>" min="0" step="10" required>
              </div>
              <small class="form-text text-muted">Floor amount paid to technicians per attended visit.</small>
            </div>

            <button type="submit" class="btn btn-brand btn-block font-weight-bold shadow-sm">
              <i class="fa fa-save mr-1"></i> Update Global Policy
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Category & Tier Overrides Table -->
    <div class="col-lg-7 mb-4">
      <div class="card shadow-sm border h-100" style="border-radius: 10px;">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 font-weight-bold text-dark">
            <i class="fa fa-tags mr-2" style="color: #0284c7;"></i>Category & Worker Tier Rules
          </h6>
          <span class="badge badge-light border small text-muted"><?= count($rules) ?> Rules Configured</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 13px;">
              <thead class="bg-light">
                <tr>
                  <th class="py-2">Scope / Category</th>
                  <th class="py-2">Tier</th>
                  <th class="py-2 text-center">Company %</th>
                  <th class="py-2 text-center">Labour %</th>
                  <th class="py-2 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rules as $r): ?>
                  <tr>
                    <td class="font-weight-bold text-dark align-middle">
                      <?php if ($r['category_id']): ?>
                        <span class="badge badge-primary px-2 py-1 mr-1" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
                          <?= \App\Core\View::e($r['category_name']) ?>
                        </span>
                      <?php else: ?>
                        <span class="badge badge-secondary px-2 py-1 mr-1">Global Base</span>
                      <?php endif; ?>
                      <div class="small text-muted font-weight-normal"><?= \App\Core\View::e($r['notes'] ?? '') ?></div>
                    </td>
                    <td class="align-middle">
                      <span class="badge badge-light border text-uppercase" style="font-size: 10.5px;">
                        <?= \App\Core\View::e($r['worker_tier']) ?>
                      </span>
                    </td>
                    <td class="align-middle text-center font-weight-bold" style="color: #f25b29;">
                      <?= (float)$r['admin_percent'] ?>%
                    </td>
                    <td class="align-middle text-center font-weight-bold text-success">
                      <?= (float)$r['worker_percent'] ?>%
                    </td>
                    <td class="align-middle text-right">
                      <?php if (!($r['category_id'] === null && $r['worker_tier'] === 'all')): ?>
                        <form action="<?= \App\Core\View::url('/admin/commissions/rules/' . $r['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this custom commission rule?');">
                          <?= \App\Core\View::csrfField() ?>
                          <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                            <i class="fa fa-trash"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted small">Default</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Historical Commission & Payouts Ledger Table -->
  <div class="card shadow-sm border mb-4" style="border-radius: 10px;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h6 class="mb-1 font-weight-bold text-dark">
          <i class="fa fa-list-alt mr-2" style="color: #10b981;"></i>Technician Payouts & Commission Audit Ledger
        </h6>
        <p class="text-muted small mb-0">Record of completed jobs, material cost deductions, company fees, and technician payouts.</p>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size: 13.5px;">
          <thead class="bg-light">
            <tr>
              <th>Job / Booking</th>
              <th>Technician</th>
              <th>Service</th>
              <th class="text-right">Gross Charge</th>
              <th class="text-right">Material Used</th>
              <th class="text-right">Company Fee</th>
              <th class="text-right">Labour Payout</th>
              <th class="text-center">Settlement</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($ledger)): ?>
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                  <i class="fa fa-folder-open-o fa-2x d-block mb-2 text-muted"></i>
                  No technician completed jobs recorded in earnings ledger yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($ledger as $item): ?>
                <tr>
                  <td class="align-middle">
                    <span class="font-weight-bold text-dark">#<?= \App\Core\View::e($item['booking_no']) ?></span>
                    <small class="text-muted d-block">Job #<?= (int)$item['job_id'] ?></small>
                  </td>
                  <td class="align-middle">
                    <strong><?= \App\Core\View::e($item['staff_name']) ?></strong>
                    <small class="text-muted d-block"><?= \App\Core\View::e($item['staff_phone'] ?? '') ?></small>
                  </td>
                  <td class="align-middle">
                    <?= \App\Core\View::e($item['service_name']) ?>
                    <?php if (!empty($item['category_name'])): ?>
                      <small class="text-muted d-block"><?= \App\Core\View::e($item['category_name']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle text-right font-weight-medium">
                    ₹<?= number_format((float)($item['gross_amount'] > 0 ? $item['gross_amount'] : ($item['amount'] + $item['admin_fee'])), 2) ?>
                  </td>
                  <td class="align-middle text-right text-danger font-weight-medium">
                    <?php if ((float)$item['material_cost'] > 0): ?>
                      - ₹<?= number_format((float)$item['material_cost'], 2) ?>
                    <?php else: ?>
                      <span class="text-muted">₹0.00</span>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle text-right font-weight-bold" style="color: #f25b29;">
                    ₹<?= number_format((float)$item['admin_fee'], 2) ?>
                  </td>
                  <td class="align-middle text-right font-weight-bold text-success">
                    ₹<?= number_format((float)$item['amount'], 2) ?>
                  </td>
                  <td class="align-middle text-center">
                    <?php if ($item['is_settled']): ?>
                      <span class="badge badge-success px-2 py-1" style="font-size: 11px;">SETTLED</span>
                    <?php else: ?>
                      <span class="badge badge-warning px-2 py-1" style="font-size: 11px;">PENDING</span>
                    <?php endif; ?>
                  </td>
                  <td class="align-middle text-center">
                    <?php if (!$item['is_settled']): ?>
                      <form action="<?= \App\Core\View::url('/admin/commissions/settle/' . $item['id']) ?>" method="POST" class="d-inline">
                        <?= \App\Core\View::csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" title="Mark as settled / paid out">
                          <i class="fa fa-check mr-1"></i> Settle
                        </button>
                      </form>
                    <?php else: ?>
                      <small class="text-muted"><i class="fa fa-check-circle text-success"></i> Done</small>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Add Custom Category / Tier Rule -->
<div class="modal fade" id="newRuleModal" tabindex="-1" role="dialog" aria-labelledby="newRuleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <form action="<?= \App\Core\View::url('/admin/commissions/rules') ?>" method="POST">
        <?= \App\Core\View::csrfField() ?>

        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0a1c33 0%, #112845 100%);">
          <h5 class="modal-title font-weight-bold" id="newRuleModalLabel">
            <i class="fa fa-plus-circle mr-2" style="color: #f25b29;"></i>Add Custom Commission Rule
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Applicable Service Category</label>
            <select name="category_id" class="form-control">
              <option value="">All Categories (Global Role Rule)</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= \App\Core\View::e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Technician Tier</label>
            <select name="worker_tier" class="form-control">
              <option value="all">All Tiers</option>
              <option value="senior">Senior Specialist</option>
              <option value="standard" selected>Standard Technician</option>
              <option value="trainee">Apprentice / Helper</option>
            </select>
          </div>

          <div class="row">
            <div class="col-6 form-group mb-3">
              <label class="small font-weight-bold text-dark">Company % *</label>
              <input type="number" name="admin_percent" class="form-control font-weight-bold" value="25" min="0" max="100" step="1" required>
            </div>
            <div class="col-6 form-group mb-3">
              <label class="small font-weight-bold text-dark">Labour % *</label>
              <input type="number" name="worker_percent" class="form-control font-weight-bold text-success" value="75" min="0" max="100" step="1" required>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Material Deduction Handling</label>
            <select name="deduct_material_first" class="form-control">
              <option value="1" selected>Deduct material cost before calculating split</option>
              <option value="0">Do not deduct materials</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Minimum Visit Charge Guarantee (₹)</label>
            <input type="number" name="min_visit_charge" class="form-control" value="250" min="0" step="10">
          </div>

          <div class="form-group mb-0">
            <label class="small font-weight-bold text-dark">Rule Notes / Description</label>
            <input type="text" name="notes" class="form-control" placeholder="e.g. AC Jet Wash Specialist Rate">
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand btn-sm font-weight-bold px-4">
            <i class="fa fa-save mr-1"></i> Save Custom Rule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function syncWorkerPct(adminPct) {
  var p = parseFloat(adminPct) || 0;
  if (p < 0) p = 0;
  if (p > 100) p = 100;
  var worker = 100 - p;
  document.getElementById('global_worker_pct').value = worker.toFixed(1);
}

function runSimulation() {
  var gross = parseFloat(document.getElementById('sim_gross').value) || 0;
  var material = parseFloat(document.getElementById('sim_material').value) || 0;
  var tier = document.getElementById('sim_tier').value;
  var payer = document.getElementById('sim_payer').value;

  var adminPct = 25.0;
  var workerPct = 75.0;

  if (tier === 'senior') {
    adminPct = 20.0;
    workerPct = 80.0;
  } else if (tier === 'trainee') {
    adminPct = 35.0;
    workerPct = 65.0;
  }

  var netBase = Math.max(0, gross - material);
  var adminShare = Math.round(netBase * (adminPct / 100.0) * 100) / 100;
  var workerShare = Math.round(netBase * (workerPct / 100.0) * 100) / 100;

  var workerTotalPayout = workerShare;
  if (payer === 'worker') {
    workerTotalPayout = workerShare + material;
  }

  document.getElementById('disp_gross').innerText = '₹' + gross.toLocaleString('en-IN', {minimumFractionDigits: 2});
  document.getElementById('disp_material').innerText = '- ₹' + material.toLocaleString('en-IN', {minimumFractionDigits: 2});
  document.getElementById('disp_net').innerText = '₹' + netBase.toLocaleString('en-IN', {minimumFractionDigits: 2});
  
  document.getElementById('disp_admin_pct').innerText = adminPct;
  document.getElementById('disp_worker_pct').innerText = workerPct;
  
  document.getElementById('disp_admin_share').innerText = '₹' + adminShare.toLocaleString('en-IN', {minimumFractionDigits: 2});
  document.getElementById('disp_worker_payout').innerText = '₹' + workerTotalPayout.toLocaleString('en-IN', {minimumFractionDigits: 2});

  if (payer === 'worker' && material > 0) {
    document.getElementById('disp_worker_note').innerText = '₹' + workerShare.toFixed(2) + ' service share + ₹' + material.toFixed(2) + ' material reimbursement';
  } else if (payer === 'company') {
    document.getElementById('disp_worker_note').innerText = '₹' + workerShare.toFixed(2) + ' labour payout (Company provided parts)';
  } else {
    document.getElementById('disp_worker_note').innerText = '₹' + workerShare.toFixed(2) + ' pure service payout';
  }

  document.getElementById('disp_formula').innerText = 'Gross (₹' + gross + ') - Material (₹' + material + ') = Net (₹' + netBase + '). Admin gets ' + adminPct + '% (₹' + adminShare + '), Labour gets ' + workerPct + '% (₹' + workerShare + ')' + (payer === 'worker' && material > 0 ? ' + ₹' + material + ' material reimbursement = ₹' + workerTotalPayout.toFixed(2) : '');
}

// Run simulation once at load
document.addEventListener('DOMContentLoaded', function() {
  runSimulation();
});
</script>
