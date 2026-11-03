<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('customer');

$account = common_user();
$member = data_current_member();
$classes = data_customer_classes();
$memberName = $member['ho_ten'] ?? $account['ho_ten'] ?? 'Hội viên';

$menu = require __DIR__ . '/../common/menus/customer.php';

$appRole = 'customer';
$activeMenu = 'dashboard';
$pageTitle = 'Trang chủ hội viên';
$pageScripts = [];
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
                    <h1>Xin chào, <?= e($memberName) ?></h1>
                    <p>Đây là những thông tin bạn cần cho buổi tập tiếp theo.</p>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="app-card h-100">
                        <div class="app-card-header"><h2>Thông tin hội viên</h2></div>
                        <div class="app-card-body">
                            <div class="text-small"><strong>Tên đăng nhập:</strong> <?= e($account['ten_dang_nhap'] ?? '') ?></div>
                            <div class="text-small mt-1"><strong>Email:</strong> <?= e($member['email'] ?? '') ?></div>
                            <div class="text-small mt-1"><strong>Số điện thoại:</strong> <?= e($member['sdt'] ?? '') ?></div>
                            <div class="text-small mt-1"><strong>Giới tính:</strong> <?= e($member['gioi_tinh'] ?? '') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="app-card h-100">
                        <div class="app-card-header"><h2>Chỉ số cơ thể</h2></div>
                        <div class="app-card-body">
                            <div class="d-flex gap-4">
                                <div><div class="text-tiny text-muted-2">Cân nặng</div><strong class="fs-5"><?= e($member['can_nang'] ?? '—') ?> kg</strong></div>
                                <div><div class="text-tiny text-muted-2">Chiều cao</div><strong class="fs-5"><?= e($member['chieu_cao'] ?? '—') ?> cm</strong></div>
                            </div>
                            <div class="text-tiny text-muted-2 mt-3">Mục tiêu: <?= e($member['muc_tieu'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="app-card h-100">
                        <div class="app-card-header"><h2>Lớp đã đăng ký</h2></div>
                        <div class="app-card-body">
                            <div class="fw-700"><?= count($classes) ?> lớp</div>
                            <a class="btn btn-ghost btn-sm px-0 mt-3" href="<?= e(url('/customer/schedule')) ?>">Xem lịch tập <?= icon('arrow-right') ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-header">
                    <div>
                        <h2>Lớp đã đăng ký</h2>
                        <p class="sub">Các lớp đang có trong lịch của bạn</p>
                    </div>
                    <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/customer/schedule')) ?>">Xem lịch đầy đủ <?= icon('arrow-right') ?></a>
                </div>
                <div class="app-table-wrap">
                    <table class="app-table">
                        <thead><tr><th>Lớp học</th><th>Lịch</th><th>Giáo viên</th></tr></thead>
                        <tbody>
                            <?php foreach ($classes as $c): ?>
                                <tr>
                                    <td class="cell-strong"><?= e($c['name']) ?></td>
                                    <td><?= e(weekday((int) $c['weekday'])) ?><div class="cell-sub"><?= e($c['start'] . ' – ' . $c['end']) ?></div></td>
                                    <td><?= e($c['instructor']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$classes): ?>
                                <tr><td colspan="3" class="text-muted-2">Bạn chưa đăng ký lớp nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php require $partials . '/foot.php'; ?>
