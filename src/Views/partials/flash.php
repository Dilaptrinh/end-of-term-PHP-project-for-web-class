<?php
$flash = $_SESSION['_flash'] ?? null;
if (is_array($flash) && isset($flash['message'])) {
    unset($_SESSION['_flash']);
    $tone = (string) ($flash['type'] ?? 'success');
    $map = ['success' => 'alert-success', 'danger' => 'alert-danger', 'warning' => 'alert-warning'];
    $icons = ['success' => 'check-circle', 'danger' => 'exclamation-octagon', 'warning' => 'exclamation-triangle'];
?>
<div class="alert <?= e($map[$tone] ?? 'alert-info') ?> d-flex align-items-start gap-2" role="alert">
    <?= icon($icons[$tone] ?? 'info-circle') ?>
    <div><?= e((string) $flash['message']) ?></div>
</div>
<?php } ?>
