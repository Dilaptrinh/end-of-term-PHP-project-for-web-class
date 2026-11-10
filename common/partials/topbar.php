<?php
/** @var string $appRole */
require_once __DIR__ . '/../data/user.php';

$user = app_user($appRole);
$accountHref = '/' . $appRole . '/account';
?>
<header class="app-topbar">
    <button type="button" class="app-icon-btn d-lg-none" data-sidebar-open aria-label="Mở menu" aria-controls="app-sidebar">
        <?= icon('list') ?>
    </button>

    <a class="app-user text-decoration-none" href="<?= e(url($accountHref)) ?>" aria-label="Mở trang quản lý tài khoản">
        <?= avatar($user['name'], 'avatar-sm') ?>
        <span class="app-user-meta">
            <strong><?= e($user['name']) ?></strong>
            <span><?= e($user['sub']) ?></span>
        </span>
    </a>
</header>
