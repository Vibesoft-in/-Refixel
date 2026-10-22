<?php
$successMsg = \App\Core\View::flash('success');
$errorMsg   = \App\Core\View::flash('error');
$warningMsg = \App\Core\View::flash('warning');
$infoMsg    = \App\Core\View::flash('info');
?>
<?php if ($successMsg): ?>
  <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
    <strong>Success!</strong> <?= \App\Core\View::e($successMsg) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
  <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
    <strong>Error:</strong> <?= \App\Core\View::e($errorMsg) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>

<?php if ($warningMsg): ?>
  <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
    <strong>Notice:</strong> <?= \App\Core\View::e($warningMsg) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>

<?php if ($infoMsg): ?>
  <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
    <strong>Info:</strong> <?= \App\Core\View::e($infoMsg) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>
