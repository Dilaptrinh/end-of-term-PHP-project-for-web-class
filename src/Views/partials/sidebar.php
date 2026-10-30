<?php
/** @var string $appRole */
/** @var string $activeMenu */
/** @var array<string,string> $user */
$menu = app_menu($appRole);
?>
<aside class="app-sidebar" id="app-sidebar" aria-label="Menu chính">
    <a class="app-sidebar-brand" href="<?= e(url('/' . $appRole)) ?>">
        <span class="brand-mark"><?= icon('activity') ?></span>
        <span>
            FitZone
            <small>Gym &amp; Classes</small>
        </span>
    </a>
    <button type="button" class="app-icon-btn app-sidebar-close" data-sidebar-close aria-label="Đóng menu">
        <?= icon('x-lg') ?>
    </button>

    <nav class="app-nav">
        <?php foreach ($menu as $item): ?>
            <?php if (isset($item['group'])): ?>
                <div class="app-nav-label"><?= e($item['group']) ?></div>
                <?php continue; ?>
            <?php endif; ?>
            <a class="app-nav-link<?= $activeMenu === $item['key'] ? ' active' : '' ?>"
               href="<?= e(url($item['href'])) ?>"
               <?= $activeMenu === $item['key'] ? 'aria-current="page"' : '' ?>>
                <?= icon($item['icon']) ?>
                <span><?= e($item['label']) ?></span>
                <?php if (!empty($item['badge'])): ?>
                    <span class="nav-badge"><?= e($item['badge']) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="app-sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <?= icon('shield-check') ?>
            <span>Hệ thống FitZone 1.0</span>
        </div>
    </div>
</aside>
