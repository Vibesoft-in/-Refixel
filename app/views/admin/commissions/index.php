<div class="container-fluid p-0">
  <!-- Page Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap">
    <div>
      <h4 class="mb-1 text-dark" style="font-weight: 800; letter-spacing: -0.3px;">
        <i class="fa fa-percent mr-2" style="color: #f25b29;"></i>Commission Management & Profit Splits
      </h4>
      <p class="text-muted small mb-0">Har service ke hisaab se service charge distribution, technician labour payouts, and company profit splits.</p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
      <button type="button" class="btn btn-sm btn-brand shadow-sm font-weight-bold" data-toggle="modal" data-target="#newRuleModal">
        <i class="fa fa-plus-circle mr-1"></i> Add Custom Service / Category Rule
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
        <h6 class="mb-0 font-weight-bold text-white">Live Service Commission & Split Simulator</h6>
      </div>
      <span class="badge" style="background: rgba(242, 91, 41, 0.2); color: #f25b29; border: 1px solid rgba(242, 91, 41, 0.4); font-size: 11px;">
        Instant Real-Time Math
      </span>
    </div>
    <div class="card-body p-4 bg-white">
      <p class="text-muted small mb-4">
        Simulate how customer charges for each service are split between <strong>Company Share (Admin)</strong> and <strong>Labour Payout (Technician)</strong> according to each service's individual rupees and custom split rules.
      </p>

      <div class="row">
        <!-- Simulator Inputs -->
        <div class="col-lg-5 col-md-6 mb-3">
          <div class="bg-light p-3 rounded border">
            <h6 class="font-weight-bold text-dark small text-uppercase mb-3">
              <i class="fa fa-sliders mr-1 text-primary"></i> Simulation Inputs
            </h6>

            <!-- Service Quick Selector -->
            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">
                <i class="fa fa-wrench mr-1 text-primary"></i> Select Service to Test (Auto-populates price & split):
              </label>
              <select id="sim_service_select" class="form-control font-weight-bold" onchange="onSelectServiceSimulation(this)">
                <option value="" data-price="" data-admin-pct="" data-worker-pct="">-- Custom Amount / Manual Simulation --</option>
                <?php 
                  $currCat = '';
                  foreach ($serviceSplits as $ss): 
                    if ($ss['category_name'] !== $currCat):
                      if ($currCat !== '') echo '</optgroup>';
                      $currCat = $ss['category_name'];
                      echo '<optgroup label="' . \App\Core\View::e($currCat) . '">';
                    endif;
                ?>
                  <option value="<?= $ss['service_id'] ?>" 
                          data-price="<?= $ss['price'] ?>" 
                          data-admin-pct="<?= $ss['admin_pct'] ?>" 
                          data-worker-pct="<?= $ss['worker_pct'] ?>"
                          data-scope="<?= $ss['rule_scope'] ?>"
                          data-applied="<?= \App\Core\View::e($ss['rule_applied']) ?>"
                          data-name="<?= \App\Core\View::e($ss['service_name']) ?>">
                    <?= \App\Core\View::e($ss['service_name']) ?> — ₹<?= number_format($ss['price'], 0) ?> (Admin <?= $ss['admin_pct'] ?>% / Tech <?= $ss['worker_pct'] ?>%)
                  </option>
                <?php endforeach; if ($currCat !== '') echo '</optgroup>'; ?>
              </select>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Total Service Charge (Customer Amount) *</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                <input type="number" id="sim_gross" class="form-control font-weight-bold" value="999" min="0" step="50" oninput="runSimulation()">
              </div>
              <small class="form-text text-muted" id="sim_gross_note">Customer booking charge / service price.</small>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold text-dark">Material / Spare Parts Cost (Deduction)</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text bg-white">₹</span></div>
                <input type="number" id="sim_material" class="form-control font-weight-bold text-danger" value="0" min="0" step="20" oninput="runSimulation()">
              </div>
              <small class="form-text text-muted">Cost of industrial consumables or spare parts replaced.</small>
            </div>

            <div class="row">
              <div class="col-6 form-group mb-3">
                <label class="small font-weight-bold text-dark">Worker Tier</label>
                <select id="sim_tier" class="form-control form-control-sm" onchange="runSimulation()">
                  <option value="all">Global Standard</option>
                  <option value="senior">Senior Specialist</option>
                  <option value="standard" selected>Standard Technician</option>
                  <option value="trainee">Apprentice / Helper</option>
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
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="font-weight-bold text-dark small text-uppercase mb-0">
                <i class="fa fa-pie-chart mr-1" style="color: #f25b29;"></i> Calculated Profit & Payout Breakdown
              </h6>
              <span id="disp_rule_badge" class="badge badge-light border text-muted px-2 py-1 small">
                Global Policy (25%/75%)
              </span>
            </div>

            <!-- Math Waterfall -->
            <div class="table-responsive mb-3">
              <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 13.5px;">
                <tbody>
                  <tr>
                    <td class="text-muted"><i class="fa fa-arrow-right text-primary mr-1"></i> Gross Customer Charge</td>
                    <td class="text-right font-weight-bold text-dark" id="disp_gross">₹999.00</td>
                  </tr>
                  <tr class="table-warning">
                    <td class="text-danger font-weight-bold"><i class="fa fa-minus-circle mr-1"></i> Less: Material Used Deduction</td>
                    <td class="text-right font-weight-bold text-danger" id="disp_material">- ₹0.00</td>
                  </tr>
                  <tr class="table-light">
                    <td class="font-weight-bold text-dark"><i class="fa fa-balance-scale mr-1 text-info"></i> Net Commissionable Base</td>
                    <td class="text-right font-weight-bold text-dark" id="disp_net">₹999.00</td>
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
                  <div class="display-4 font-weight-bold mb-1" id="disp_admin_share" style="font-size: 26px; color: #f25b29;">₹249.75</div>
                  <small class="text-muted d-block" id="disp_company_note">Platform margin retained</small>
                </div>
              </div>

              <!-- Labour Payout Box -->
              <div class="col-sm-6 mb-2">
                <div class="p-3 rounded border text-center" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                  <span class="badge badge-pill text-uppercase font-weight-bold px-2 py-1 mb-2" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                    Labour / Tech Payout (<span id="disp_worker_pct">75</span>%)
                  </span>
                  <div class="display-4 font-weight-bold mb-1 text-success" id="disp_worker_payout" style="font-size: 26px;">₹749.25</div>
                  <small class="text-muted d-block" id="disp_worker_note">Technician direct payout</small>
                </div>
              </div>
            </div>

            <div class="alert alert-light border small text-muted mb-0 py-2 mt-2">
              <i class="fa fa-info-circle mr-1 text-primary"></i>
              <strong>Formula Applied:</strong> <span id="disp_formula">Gross (₹999) - Material (₹0) = Net (₹999). Admin gets 25% (₹249.75), Labour gets 75% (₹749.25)</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- NEW: Service-Wise Commission & Payout Splits Table -->
  <div class="card shadow-sm border mb-4" style="border-radius: 10px;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h6 class="mb-1 font-weight-bold text-dark">
          <i class="fa fa-th-list mr-2" style="color: #f25b29;"></i>Service-Wise Commission & Payout Splits
        </h6>
        <p class="text-muted small mb-0">Hrr service ke alag alag customer amount aur admin vs technician ka split breakdown.</p>
      </div>
      <button type="button" class="btn btn-sm btn-brand shadow-sm font-weight-bold" data-toggle="modal" data-target="#newRuleModal" onclick="prepareServiceModal(null)">
        <i class="fa fa-plus-circle mr-1"></i> Set Service Split Rule
      </button>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size: 13.5px;">
          <thead class="bg-light">
            <tr>
              <th class="py-2">Service Name</th>
              <th class="py-2">Category</th>
              <th class="py-2 text-right">Customer Price</th>
              <th class="py-2 text-center" style="color: #f25b29;">Admin Share</th>
              <th class="py-2 text-center text-success">Technician Payout</th>
              <th class="py-2 text-center">Split Policy</th>
              <th class="py-2 text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($serviceSplits as $sRow): ?>
              <tr>
                <td class="align-middle">
                  <strong class="text-dark"><?= \App\Core\View::e($sRow['service_name']) ?></strong>
                </td>
                <td class="align-middle">
                  <span class="badge badge-light border text-muted">
                    <?= \App\Core\View::e($sRow['category_name']) ?>
                  </span>
                </td>
                <td class="align-middle text-right font-weight-bold text-dark">
                  ₹<?= number_format($sRow['price'], 2) ?>
                </td>
                <td class="align-middle text-center">
                  <div class="font-weight-bold" style="color: #f25b29;">₹<?= number_format($sRow['admin_share'], 2) ?></div>
                  <span class="badge" style="background: rgba(242, 91, 41, 0.12); color: #f25b29; font-size: 11px;">
                    <?= (float)$sRow['admin_pct'] ?>%
                  </span>
                </td>
                <td class="align-middle text-center">
                  <div class="font-weight-bold text-success">₹<?= number_format($sRow['worker_share'], 2) ?></div>
                  <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 11px;">
                    <?= (float)$sRow['worker_pct'] ?>%
                  </span>
                </td>
                <td class="align-middle text-center">
                  <?php if ($sRow['rule_scope'] === 'service'): ?>
                    <span class="badge badge-success px-2 py-1 font-weight-bold" title="<?= \App\Core\View::e($sRow['rule_applied']) ?>">
                      <i class="fa fa-check-circle mr-1"></i>Service Custom Rule
                    </span>
                  <?php elseif ($sRow['rule_scope'] === 'category'): ?>
                    <span class="badge badge-info px-2 py-1" title="<?= \App\Core\View::e($sRow['rule_applied']) ?>" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
                      <i class="fa fa-folder-open-o mr-1"></i>Category Rule
                    </span>
                  <?php else: ?>
                    <span class="badge badge-light border text-muted px-2 py-1">
                      Global Base
                    </span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-right">
                  <button type="button" 
                          class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold"
                          onclick="prepareServiceModal(<?= (int)$sRow['service_id'] ?>, '<?= addslashes($sRow['service_name']) ?>', <?= (float)$sRow['price'] ?>, <?= (float)$sRow['admin_pct'] ?>, <?= (float)$sRow['worker_pct'] ?>)"
                          data-toggle="modal" 
                          data-target="#newRuleModal"
                          title="Configure custom commission split for this service">
                    <i class="fa fa-sliders mr-1"></i> <?= $sRow['rule_scope'] === 'service' ? 'Edit Split' : 'Set Custom Split' ?>
                  </button>
                  <?php if ($sRow['rule_scope'] === 'service' && !empty($sRow['rule_id'])): ?>
                    <form action="<?= \App\Core\View::url('/admin/commissions/rules/' . $sRow['rule_id'] . '/delete') ?>" method="POST" class="d-inline ml-1" onsubmit="return confirm('Reset this service back to default category/global split?');">
                      <?= \App\Core\View::csrfField() ?>
                      <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Reset to default">
                        <i class="fa fa-undo"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Global Commission Settings Form -->
    <div class="col-lg-5 mb-4">
      <div class="card shadow-sm border h-100" style="border-radius: 10px;">
        <div class="card-header bg-white py-3 border-bottom">
          <h6 class="mb-0 font-weight-bold text-dark">
            <i class="fa fa-globe mr-2" style="color: #f25b29;"></i>Global Default Commission Policy
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

    <!-- Active Overrides & Custom Rules Table -->
    <div class="col-lg-7 mb-4">
      <div class="card shadow-sm border h-100" style="border-radius: 10px;">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
          <h6 class="mb-0 font-weight-bold text-dark">
            <i class="fa fa-tags mr-2" style="color: #0284c7;"></i>Configured Custom Rules (Service & Category)
          </h6>
          <span class="badge badge-light border small text-muted"><?= count($rules) ?> Rules Configured</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 13px;">
              <thead class="bg-light">
                <tr>
                  <th class="py-2">Rule Scope / Target</th>
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
                      <?php if (!empty($r['service_id'])): ?>
                        <span class="badge badge-success px-2 py-1 mr-1">
                          <i class="fa fa-wrench mr-1"></i><?= \App\Core\View::e($r['service_name'] ?? 'Service #' . $r['service_id']) ?>
                        </span>
                        <?php if (!empty($r['service_price'])): ?>
                          <small class="text-muted">(₹<?= number_format((float)$r['service_price'], 0) ?>)</small>
                        <?php endif; ?>
                      <?php elseif (!empty($r['category_id'])): ?>
                        <span class="badge badge-primary px-2 py-1 mr-1" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">
                          <i class="fa fa-folder-open-o mr-1"></i><?= \App\Core\View::e($r['category_name']) ?>
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
                      <?php if (!($r['service_id'] === null && $r['category_id'] === null && $r['worker_tier'] === 'all')): ?>
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

<!-- Modal: Add / Configure Custom Service / Category Commission Rule -->
<div class="modal fade" id="newRuleModal" tabindex="-1" role="dialog" aria-labelledby="newRuleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <form action="<?= \App\Core\View::url('/admin/commissions/rules') ?>" method="POST" id="ruleForm">
        <?= \App\Core\View::csrfField() ?>

        <div class="modal-header text-white" style="background: linear-gradient(135deg, #0a1c33 0%, #112845 100%);">
          <h5 class="modal-title font-weight-bold" id="newRuleModalLabel">
            <i class="fa fa-sliders mr-2" style="color: #f25b29;"></i>Configure Commission Split
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body p-4">
          <!-- Scope Selector: Service vs Category vs Tier -->
          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Rule Target / Scope</label>
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
              <label class="btn btn-sm btn-outline-primary active" id="btn_scope_service" onclick="setModalScope('service')">
                <input type="radio" name="scope_type" value="service" checked> <i class="fa fa-wrench mr-1"></i> Specific Service
              </label>
              <label class="btn btn-sm btn-outline-primary" id="btn_scope_category" onclick="setModalScope('category')">
                <input type="radio" name="scope_type" value="category"> <i class="fa fa-folder-o mr-1"></i> Entire Category
              </label>
            </div>
          </div>

          <!-- Service Dropdown -->
          <div class="form-group mb-3" id="group_service">
            <label class="small font-weight-bold text-dark">Select Service *</label>
            <select name="service_id" id="modal_service_id" class="form-control" onchange="onModalServiceChange(this)">
              <option value="">-- Choose a Service --</option>
              <?php 
                $mCat = '';
                foreach ($serviceSplits as $sItem): 
                  if ($sItem['category_name'] !== $mCat):
                    if ($mCat !== '') echo '</optgroup>';
                    $mCat = $sItem['category_name'];
                    echo '<optgroup label="' . \App\Core\View::e($mCat) . '">';
                  endif;
              ?>
                <option value="<?= $sItem['service_id'] ?>" 
                        data-price="<?= $sItem['price'] ?>" 
                        data-name="<?= \App\Core\View::e($sItem['service_name']) ?>"
                        data-cat-id="<?= $sItem['category_id'] ?>">
                  <?= \App\Core\View::e($sItem['service_name']) ?> — ₹<?= number_format($sItem['price'], 0) ?>
                </option>
              <?php endforeach; if ($mCat !== '') echo '</optgroup>'; ?>
            </select>
          </div>

          <!-- Category Dropdown -->
          <div class="form-group mb-3 d-none" id="group_category">
            <label class="small font-weight-bold text-dark">Select Category *</label>
            <select name="category_id" id="modal_category_id" class="form-control">
              <option value="">-- Choose a Category --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= \App\Core\View::e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Technician Tier</label>
            <select name="worker_tier" class="form-control">
              <option value="all" selected>All Technician Tiers</option>
              <option value="senior">Senior Specialist</option>
              <option value="standard">Standard Technician</option>
              <option value="trainee">Apprentice / Helper</option>
            </select>
          </div>

          <!-- Live Commission Percentage Inputs with Auto-Sync -->
          <div class="row">
            <div class="col-6 form-group mb-3">
              <label class="small font-weight-bold text-dark">Company / Admin % *</label>
              <div class="input-group">
                <input type="number" name="admin_percent" id="modal_admin_pct" class="form-control font-weight-bold" value="25" min="0" max="100" step="0.5" oninput="syncModalWorkerPct(this.value)" required>
                <div class="input-group-append"><span class="input-group-text bg-white">%</span></div>
              </div>
            </div>
            <div class="col-6 form-group mb-3">
              <label class="small font-weight-bold text-dark">Labour / Tech % *</label>
              <div class="input-group">
                <input type="number" name="worker_percent" id="modal_worker_pct" class="form-control font-weight-bold text-success" value="75" min="0" max="100" step="0.5" oninput="syncModalAdminPct(this.value)" required>
                <div class="input-group-append"><span class="input-group-text bg-white">%</span></div>
              </div>
            </div>
          </div>

          <!-- Live Preview Box inside Modal -->
          <div class="p-3 mb-3 rounded border" style="background: #fafbfc;">
            <div class="small font-weight-bold text-muted text-uppercase mb-1">
              <i class="fa fa-calculator mr-1 text-primary"></i> Estimated Split Preview:
            </div>
            <div class="d-flex align-items-center justify-content-between" style="font-size: 13.5px;">
              <div>Customer Price: <strong id="preview_gross" class="text-dark">₹999.00</strong></div>
              <div>Admin: <strong id="preview_admin" style="color: #f25b29;">₹249.75</strong></div>
              <div>Tech: <strong id="preview_worker" class="text-success">₹749.25</strong></div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Material Deduction Handling</label>
            <select name="deduct_material_first" class="form-control form-control-sm">
              <option value="1" selected>Deduct material cost before calculating split (Recommended)</option>
              <option value="0">Do not deduct materials</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label class="small font-weight-bold text-dark">Minimum Guaranteed Technician Payout (₹)</label>
            <input type="number" name="min_visit_charge" class="form-control form-control-sm" value="200" min="0" step="10">
          </div>

          <div class="form-group mb-0">
            <label class="small font-weight-bold text-dark">Rule Notes / Description</label>
            <input type="text" name="notes" id="modal_notes" class="form-control form-control-sm" placeholder="e.g. High Demand Service Custom Split">
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-brand btn-sm font-weight-bold px-4">
            <i class="fa fa-save mr-1"></i> Save Commission Rule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Simulator state
var activeAdminPct = <?= (float)($settings['global_admin_commission_pct'] ?? 25.0) ?>;
var activeWorkerPct = <?= (float)($settings['global_worker_commission_pct'] ?? 75.0) ?>;

function onSelectServiceSimulation(selectEl) {
  var opt = selectEl.options[selectEl.selectedIndex];
  if (!opt.value) {
    document.getElementById('sim_gross_note').innerText = 'Customer booking charge / service price.';
    document.getElementById('disp_rule_badge').className = 'badge badge-light border text-muted px-2 py-1 small';
    document.getElementById('disp_rule_badge').innerText = 'Global Policy (25%/75%)';
    activeAdminPct = <?= (float)($settings['global_admin_commission_pct'] ?? 25.0) ?>;
    activeWorkerPct = <?= (float)($settings['global_worker_commission_pct'] ?? 75.0) ?>;
    runSimulation();
    return;
  }

  var price = parseFloat(opt.getAttribute('data-price')) || 0;
  var adminPct = parseFloat(opt.getAttribute('data-admin-pct')) || 25.0;
  var workerPct = parseFloat(opt.getAttribute('data-worker-pct')) || 75.0;
  var scope = opt.getAttribute('data-scope');
  var applied = opt.getAttribute('data-applied');
  var name = opt.getAttribute('data-name');

  document.getElementById('sim_gross').value = price;
  document.getElementById('sim_gross_note').innerText = 'Official catalog price for ' + name;
  
  var badge = document.getElementById('disp_rule_badge');
  if (scope === 'service') {
    badge.className = 'badge badge-success px-2 py-1 small font-weight-bold';
    badge.innerHTML = '<i class="fa fa-check-circle mr-1"></i>Service Custom Rule';
  } else if (scope === 'category') {
    badge.className = 'badge badge-info px-2 py-1 small';
    badge.style.background = '#e0f2fe';
    badge.style.color = '#0284c7';
    badge.innerHTML = '<i class="fa fa-folder-open-o mr-1"></i>Category Rule';
  } else {
    badge.className = 'badge badge-light border text-muted px-2 py-1 small';
    badge.innerText = 'Global Base';
  }

  activeAdminPct = adminPct;
  activeWorkerPct = workerPct;
  runSimulation();
}

function runSimulation() {
  var gross = parseFloat(document.getElementById('sim_gross').value) || 0;
  var material = parseFloat(document.getElementById('sim_material').value) || 0;
  var tier = document.getElementById('sim_tier').value;
  var payer = document.getElementById('sim_payer').value;

  var adminPct = activeAdminPct;
  var workerPct = activeWorkerPct;

  // Tier modifier only if manual / global tier selected
  if (tier === 'senior' && !document.getElementById('sim_service_select').value) {
    adminPct = 20.0;
    workerPct = 80.0;
  } else if (tier === 'trainee' && !document.getElementById('sim_service_select').value) {
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
    document.getElementById('disp_worker_note').innerText = '₹' + workerShare.toFixed(2) + ' technician direct payout';
  }

  document.getElementById('disp_formula').innerText = 'Gross (₹' + gross + ') - Material (₹' + material + ') = Net (₹' + netBase + '). Admin gets ' + adminPct + '% (₹' + adminShare.toFixed(2) + '), Labour gets ' + workerPct + '% (₹' + workerShare.toFixed(2) + ')' + (payer === 'worker' && material > 0 ? ' + ₹' + material + ' material reimbursement = ₹' + workerTotalPayout.toFixed(2) : '');
}

function syncWorkerPct(adminPct) {
  var p = parseFloat(adminPct) || 0;
  if (p < 0) p = 0;
  if (p > 100) p = 100;
  var worker = 100 - p;
  document.getElementById('global_worker_pct').value = worker.toFixed(1);
}

// Modal Scope handling
function setModalScope(scope) {
  if (scope === 'service') {
    document.getElementById('group_service').classList.remove('d-none');
    document.getElementById('group_category').classList.add('d-none');
    document.getElementById('btn_scope_service').classList.add('active');
    document.getElementById('btn_scope_category').classList.remove('active');
  } else {
    document.getElementById('group_service').classList.add('d-none');
    document.getElementById('group_category').classList.remove('d-none');
    document.getElementById('btn_scope_category').classList.add('active');
    document.getElementById('btn_scope_service').classList.remove('active');
    document.getElementById('modal_service_id').value = '';
  }
  updateModalPreview();
}

function onModalServiceChange(sel) {
  updateModalPreview();
}

function syncModalWorkerPct(adminPct) {
  var p = parseFloat(adminPct) || 0;
  if (p < 0) p = 0;
  if (p > 100) p = 100;
  var worker = 100 - p;
  document.getElementById('modal_worker_pct').value = worker.toFixed(1);
  updateModalPreview();
}

function syncModalAdminPct(workerPct) {
  var w = parseFloat(workerPct) || 0;
  if (w < 0) w = 0;
  if (w > 100) w = 100;
  var admin = 100 - w;
  document.getElementById('modal_admin_pct').value = admin.toFixed(1);
  updateModalPreview();
}

function updateModalPreview() {
  var sel = document.getElementById('modal_service_id');
  var opt = sel.options[sel.selectedIndex];
  var price = (opt && opt.value) ? (parseFloat(opt.getAttribute('data-price')) || 999.0) : 999.0;
  
  var adminPct = parseFloat(document.getElementById('modal_admin_pct').value) || 25.0;
  var workerPct = parseFloat(document.getElementById('modal_worker_pct').value) || 75.0;

  var adminAmt = Math.round(price * (adminPct / 100.0) * 100) / 100;
  var workerAmt = Math.round(price * (workerPct / 100.0) * 100) / 100;

  document.getElementById('preview_gross').innerText = '₹' + price.toLocaleString('en-IN', {minimumFractionDigits: 2});
  document.getElementById('preview_admin').innerText = '₹' + adminAmt.toLocaleString('en-IN', {minimumFractionDigits: 2});
  document.getElementById('preview_worker').innerText = '₹' + workerAmt.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

function prepareServiceModal(serviceId, serviceName, price, adminPct, workerPct) {
  setModalScope('service');
  if (serviceId) {
    document.getElementById('modal_service_id').value = serviceId;
    if (adminPct !== undefined) document.getElementById('modal_admin_pct').value = adminPct;
    if (workerPct !== undefined) document.getElementById('modal_worker_pct').value = workerPct;
    document.getElementById('modal_notes').value = 'Service Rate: ' + (serviceName || '');
  } else {
    document.getElementById('modal_service_id').value = '';
    document.getElementById('modal_admin_pct').value = 25;
    document.getElementById('modal_worker_pct').value = 75;
    document.getElementById('modal_notes').value = '';
  }
  updateModalPreview();
}

document.addEventListener('DOMContentLoaded', function() {
  runSimulation();
});
</script>
