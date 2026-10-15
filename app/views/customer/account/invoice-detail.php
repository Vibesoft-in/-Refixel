<?php
use App\Core\View;
$invoice = $invoice ?? [];
?>

<?= View::partial('invoice-card', ['invoice' => $invoice]) ?>
