<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'dashboard';
$pageTitle = 'Tổng quan hệ thống';
$pageScripts = [];

$registrations = data_package_registrations();
$pendingPackages = [];
foreach ($registrations as $registration) {
    if ($registration['payment'] === 'chua_thanh_toan') {
        $pendingPackages[] = $registration;
    }
}
$classes = data_classes();
$members = data_members();
$classRegistrations = data_class_registrations();
$pendingTotal = count($pendingPackages);
$openClasses = 0;
foreach ($classes as $class) {
    if ($class['status'] === 'dang_mo') {
        $openClasses++;
    }
}
$activeMembers = 0;
foreach ($members as $member) {
    if ($member['status'] === 'hoat_dong') {
        $activeMembers++;
    }
}
$activeClassRegistrations = 0;
foreach ($classRegistrations as $registration) {
    if ($registration['status'] === 'dang_ky') {
        $activeClassRegistrations++;
    }
}
$paidPackages = 0;
$totalRevenue = 0;
$pendingRevenue = 0;
foreach ($registrations as $registration) {
    if ($registration['payment'] === 'da_thanh_toan') {
        $paidPackages++;
        $totalRevenue += (int) $registration['price'];
    }
    if ($registration['payment'] === 'chua_thanh_toan') {
        $pendingRevenue += (int) $registration['price'];
    }
}

$partials = __DIR__ . '/../common/partials';

require $partials . '/head.php';
?>
<div class="app-shell">
    <?php require $partials . '/sidebar.php'; ?>

    <div class="app-main">
        <?php require $partials . '/topbar.php'; ?>

        <main class="app-content" id="main-content">
            <?php require $partials . '/flash.php'; ?>
<div class="page-head">
    <div>
        <h1>Tổng quan hệ thống</h1>
        <p>Cập nhật lúc <?= e(date('d/m/Y H:i')) ?></p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-primary" href="<?= e(url('/admin/registrations')) ?>"><?= icon('receipt') ?> Xem đăng ký</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Hội viên</div>
                    <div class="stat-value"><?= count($members) ?></div>
                    <div class="stat-foot">Tổng hồ sơ hội viên</div>
                </div>
                <div class="stat-icon green ms-auto"><?= icon('people') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Giáo viên</div>
                    <div class="stat-value"><?= count(data_instructors()) ?></div>
                    <div class="stat-foot">Đang quản lý</div>
                </div>
                <div class="stat-icon blue ms-auto"><?= icon('person-badge') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Lớp đang mở</div>
                    <div class="stat-value"><?= $openClasses ?></div>
                    <div class="stat-foot">Các lớp đang mở</div>
                </div>
                <div class="stat-icon blue ms-auto"><?= icon('calendar2-week') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Thanh toán chờ xử lý</div>
                    <div class="stat-value" style="color:var(--warning);" id="dash-pending-total"><?= $pendingTotal ?></div>
                    <div class="stat-foot" id="dash-pending-foot"><?= count($pendingPackages) ?> gói chưa thanh toán</div>
                </div>
                <div class="stat-icon amber ms-auto"><?= icon('bell') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Doanh thu đã thu</div>
            <div class="stat-value" style="font-size:1.35rem;" id="dash-paid-revenue"><?= e(money($totalRevenue)) ?></div>
            <div class="stat-foot" id="dash-paid-foot"><?= $paidPackages ?> gói đã thanh toán</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Doanh thu chờ thu</div>
            <div class="stat-value" style="font-size:1.35rem;" id="dash-pending-revenue"><?= e(money($pendingRevenue)) ?></div>
            <div class="stat-foot">Gói chưa thanh toán</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đăng ký lớp đang hiệu lực</div>
            <div class="stat-value"><?= $activeClassRegistrations ?></div>
            <div class="stat-foot"><?= count($classRegistrations) ?> lượt đăng ký</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Hội viên hoạt động</div>
            <div class="stat-value"><?= $activeMembers ?></div>
            <div class="stat-foot"><?= count($members) ?> hội viên trong hệ thống</div>
        </div>
    </div>
</div>

        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php require $partials . '/foot.php'; ?>