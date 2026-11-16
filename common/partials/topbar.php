<?php
/** @var string $appRole */
require_once __DIR__ . '/../data/user.php';

$user = app_user($appRole);
$accountHref = '/' . $appRole . '/account';
?>
<header class="app-topbar">
    <button type="button" class="app-icon-btn d-xl-none" data-sidebar-open aria-label="Mở menu" aria-controls="app-sidebar">
        <?= icon('list') ?>
    </button>

    <a class="app-topbar-brand" href="<?= e(url('/' . $appRole)) ?>">
        <span class="brand-mark"><?= icon('activity') ?></span>
        <span>FitZone</span>
    </a>

    <nav class="app-topbar-nav" aria-label="Điều hướng chính">
        <?php foreach ($menu as $item): ?>
            <?php if (isset($item['group'])) continue; ?>
            <a class="app-topbar-link<?= $activeMenu === $item['key'] ? ' active' : '' ?>"
               href="<?= e(url($item['href'])) ?>"
               <?= $activeMenu === $item['key'] ? 'aria-current="page"' : '' ?>>
                <?= icon($item['icon']) ?>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <a class="app-user text-decoration-none" href="<?= e(url($accountHref)) ?>" aria-label="Mở trang quản lý tài khoản">
        <?= avatar($user['name'], 'avatar-sm') ?>
        <span class="app-user-meta">
            <strong><?= e($user['name']) ?></strong>
            <span><?= e($user['sub']) ?></span>
        </span>
    </a>
</header>
