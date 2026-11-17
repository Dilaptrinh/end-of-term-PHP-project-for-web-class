<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';
$appRole = 'customer';
$activeMenu = 'dashboard';
$pageTitle = 'Tổng quan hội viên';
$pageScripts = [];

$member = data_current_member();
$classes = data_customer_classes();
$classHistory = data_customer_class_history();

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
                    <h1>Xin chào, <?= e($member['ho_ten'] ?? 'Hội viên') ?></h1>
                    <p>Theo dõi gói tập và lịch tập của bạn.</p>
                </div>
                <div class="page-head-actions">
                    <a class="btn btn-primary" href="<?= e(url('/customer/class-registration')) ?>">Đăng ký lớp <?= icon('arrow-right') ?></a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Lượt đăng ký lớp</div>
                        <div class="stat-value"><?= count($classHistory) ?></div>
                        <div class="stat-foot">Tính cả lịch sử đăng ký</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Lớp đang học</div>
                        <div class="stat-value text-success"><?= count($classes) ?></div>
                        <div class="stat-foot">Đang đăng ký</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Gói hiện tại</div>
                        <div class="stat-value" style="font-size:1.15rem;"><?= e($member['package'] ?? 'Chưa có gói') ?></div>
                        <div class="stat-foot">Gói tập đang sử dụng</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-label">Hạn sử dụng</div>
                        <div class="stat-value" style="font-size:1.35rem;"><?= e($member['package_expiry'] ?? '—') ?></div>
                        <div class="stat-foot">Ngày hết hạn gói</div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<?php require $partials . '/foot.php'; ?>
