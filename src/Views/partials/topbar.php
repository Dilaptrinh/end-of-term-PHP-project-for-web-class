<?php
/** @var string $pageTitle */
/** @var array<int,array{0:string,1?:string}> $breadcrumbs */
/** @var string $appRole */
/** @var array<string,string> $user */
/** @var array<int,array<string,string>> $notifications */

$searchAction = match ($appRole) {
    'admin' => '/admin/search',
    'instructor' => '/instructor/search',
    default => '/customer/class-registration',
};
$accountHref = $appRole === 'admin' ? '/admin/accounts' : '/' . $appRole . '/account';
$profileHref = $appRole === 'admin' ? '/admin/accounts' : '/' . $appRole . '/account';
?>
<header class="app-topbar">
    <button type="button" class="app-icon-btn d-lg-none" data-sidebar-open aria-label="Mở menu" aria-controls="app-sidebar">
        <?= icon('list') ?>
    </button>

    <form class="app-topbar-search" role="search" action="<?= e(url($searchAction)) ?>" method="get">
        <label class="sr-only" for="topbar-search">Tìm kiếm nhanh</label>
        <?= icon('search') ?>
        <input id="topbar-search" name="q" type="search" placeholder="Tìm kiếm nhanh…" autocomplete="off">
    </form>

    <div class="dropdown">
        <button class="app-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo">
            <?= icon('bell') ?>
            <span class="dot"></span>
        </button>
        <div class="dropdown-menu dropdown-menu-end" style="width: 320px;">
            <div class="dropdown-header d-flex justify-content-between align-items-center">
                <span>Thông báo</span>
                <span class="badge-soft"><?= count($notifications) ?> mới</span>
            </div>
            <?php foreach ($notifications as $note): ?>
                <a class="dropdown-item align-items-start" href="#">
                    <span class="stat-icon <?= e($note['tone']) ?>" style="width:32px;height:32px;border-radius:9px;flex-shrink:0;">
                        <?= icon($note['icon'], 'text-small') ?>
                    </span>
                    <span class="flex-grow-1">
                        <span class="d-block text-small fw-700"><?= e($note['title']) ?></span>
                        <span class="d-block text-tiny text-muted-2"><?= e($note['time']) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item justify-content-center text-brand fw-700" href="#">Xem tất cả thông báo</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="app-user" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= avatar($user['name'], 'avatar-sm') ?>
            <span class="app-user-meta">
                <strong><?= e($user['name']) ?></strong>
                <span><?= e($user['sub']) ?></span>
            </span>
            <?= icon('chevron-down', 'text-muted-2') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 230px;">
            <li class="px-2 py-2">
                <div class="user-inline">
                    <?= avatar($user['name']) ?>
                    <span class="min-w-0">
                        <span class="d-block user-name"><?= e($user['name']) ?></span>
                        <span class="d-block user-sub"><?= e($user['email']) ?></span>
                    </span>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= e(url($profileHref)) ?>"><?= icon('person') ?> Hồ sơ cá nhân</a></li>
            <li><a class="dropdown-item" href="<?= e(url($accountHref)) ?>"><?= icon('gear') ?> Quản lý tài khoản</a></li>
            <li><a class="dropdown-item" href="#" data-toast="Tính năng đổi mật khẩu sẽ được nối API."><?= icon('key') ?> Đổi mật khẩu</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= e(url('/login')) ?>"><?= icon('box-arrow-right') ?> Đăng xuất</a></li>
        </ul>
    </div>
</header>
