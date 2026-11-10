<?php
/** @var string $appRole */
require_once __DIR__ . '/../data/user.php';

$user = app_user($appRole);

?>
<header class="app-topbar">
    <button type="button" class="app-icon-btn d-lg-none" data-sidebar-open aria-label="Mở menu" aria-controls="app-sidebar">
        <?= icon('list') ?>
    </button>

    <div class="app-user">
        <?= avatar($user['name'], 'avatar-sm') ?>
        <span class="app-user-meta">
            <strong><?= e($user['name']) ?></strong>
            <span><?= e($user['sub']) ?></span>
        </span>
    </div>
</header>
