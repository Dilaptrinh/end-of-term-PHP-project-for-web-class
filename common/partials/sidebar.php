<?php
/** @var string $appRole */
/** @var string $activeMenu */
/** @var array<int,array<string,mixed>>|null $menu */

if (!isset($menu)) {
    $menuFile = __DIR__ . '/../menus/' . $appRole . '.php';
    $menu = is_file($menuFile) ? (require $menuFile) : [];
}
?>
<aside class="app-sidebar" id="app-sidebar" aria-label="Menu chính">
    <a class="app-sidebar-brand" href="<?= e(url('/' . $appRole)) ?>">
        <span class="brand-mark"><?= icon('activity') ?></span>
        <span>FitZone <small>Gym &amp; Classes</small></span>
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
