<?php
use App\Core\Auth;
use App\Core\View;

$currentUser = Auth::user();
$currentRole = Auth::role();
$totpActive  = !empty($currentUser['totp_enabled']);

// Display only for administrators whose accounts do not yet have 2FA enabled
if (!empty($currentUser) && in_array($currentRole, ['admin', 'super_admin'], true) && !$totpActive):
?>
<div class="alert alert-warning alert-dismissible fade show d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 mb-4 shadow-sm" role="alert" style="border: 1px solid #fcd34d; background: linear-gradient(135deg, #fffdfa 0%, #fef3c7 100%); color: #78350f; border-radius: 10px;">
  <div class="d-flex align-items-center mb-2 mb-md-0 mr-md-3">
    <div class="mr-3 text-warning d-flex align-items-center justify-content-center" style="font-size: 26px; width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border-radius: 10px; flex-shrink: 0;">
      <i class="fa fa-shield" style="color: #d97706;"></i>
    </div>
    <div>
      <div class="font-weight-bold" style="font-size: 14px; color: #78350f;">
        <i class="fa fa-lock mr-1" style="color: #d97706;"></i> Two-Factor Authentication (2FA) Recommended
      </div>
      <div style="font-size: 12.5px; color: #92400e; line-height: 1.45;">
        Your administrative account does not have Two-Factor Authentication enabled. You can freely use and manage all features in this dashboard, but enabling 2FA is strongly recommended to safeguard administrative access.
      </div>
    </div>
  </div>
  <div class="d-flex align-items-center flex-shrink-0 mt-2 mt-md-0">
    <a href="<?= View::url('/2fa/setup') ?>" class="btn btn-sm btn-warning font-weight-bold text-dark px-3 py-1 mr-2" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); white-space: nowrap; font-size: 12.5px; background-color: #f59e0b; border-color: #d97706;">
      <i class="fa fa-shield mr-1"></i> Enable 2FA Now
    </a>
    <button type="button" class="close position-static p-0 ml-1" data-dismiss="alert" aria-label="Dismiss alert" style="opacity: 0.6; font-size: 22px; line-height: 1; outline: none; border: none; background: transparent; cursor: pointer; color: #78350f;" title="Dismiss notice">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
</div>
<?php endif; ?>
