<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';
$appRole = 'admin';
$activeMenu = 'reports';
$pageTitle = 'Thống kê';
$pageScripts = [];

$members = data_members();
$instructors = data_instructors();
$classes = data_classes();
$classRegistrations = data_class_registrations();
$packageRegistrations = data_package_registrations();

$activeMembers = 0;
foreach ($members as $member) {
    if ($member['status'] === 'hoat_dong') {
        $activeMembers++;
    }
}

$openClasses = 0;
foreach ($classes as $class) {
    if ($class['status'] === 'dang_mo') {
        $openClasses++;
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
foreach ($packageRegistrations as $registration) {
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
                    <h1>Thống kê</h1>
                    <p>Các số liệu tổng hợp hiện có trong hệ thống.</p>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Doanh thu đã thu</div>
                        <div class="stat-value" style="font-size:1.35rem;"><?= e(money($totalRevenue)) ?></div>
                        <div class="stat-foot"><?= $paidPackages ?> gói đã thanh toán</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Doanh thu chờ thu</div>
                        <div class="stat-value" style="font-size:1.35rem;"><?= e(money($pendingRevenue)) ?></div>
                        <div class="stat-foot">Gói chưa thanh toán</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Tổng hội viên</div>
                        <div class="stat-value"><?= count($members) ?></div>
                        <div class="stat-foot"><?= $activeMembers ?> đang hoạt động</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Lớp đang mở</div>
                        <div class="stat-value"><?= $openClasses ?></div>
                        <div class="stat-foot"><?= count($classes) ?> lớp trong hệ thống</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="app-card h-100">
                        <div class="app-card-header"><h2>Tóm tắt hội viên</h2></div>
                        <div class="app-card-body">
                            <div class="kv-list">
                                <div class="kv"><span>Tổng hội viên</span><strong><?= count($members) ?></strong></div>
                                <div class="kv"><span>Đang hoạt động</span><strong><?= $activeMembers ?></strong></div>
                                <div class="kv"><span>Ngừng hoạt động</span><strong><?= count($members) - $activeMembers ?></strong></div>
                                <div class="kv"><span>Doanh thu đã thu</span><strong><?= e(money($totalRevenue)) ?></strong></div>
                                <div class="kv"><span>Doanh thu chờ thu</span><strong><?= e(money($pendingRevenue)) ?></strong></div>
                                <div class="kv"><span>Đã đăng ký gói</span><strong><?= count($packageRegistrations) ?></strong></div>
                                <div class="kv"><span>Gói đã thanh toán</span><strong><?= $paidPackages ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="app-card h-100">
                        <div class="app-card-header"><h2>Tóm tắt lớp học</h2></div>
                        <div class="app-card-body">
                            <div class="kv-list">
                                <div class="kv"><span>Tổng số lớp</span><strong><?= count($classes) ?></strong></div>
                                <div class="kv"><span>Lớp đang mở</span><strong><?= $openClasses ?></strong></div>
                                <div class="kv"><span>Lượt đăng ký đang hiệu lực</span><strong><?= $activeClassRegistrations ?></strong></div>
                                <div class="kv"><span>Lượt đăng ký đã hủy</span><strong><?= count($classRegistrations) - $activeClassRegistrations ?></strong></div>
                                <div class="kv"><span>Giáo viên phụ trách</span><strong><?= count($instructors) ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<?php require $partials . '/foot.php'; ?>
