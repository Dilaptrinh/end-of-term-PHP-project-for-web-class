<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    if (($_GET['action'] ?? '') !== 'confirm-package') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
    }

    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
    }

    $changed = admin_package_registration_confirm($id);

    json_response([
        'success' => $changed,
        'message' => $changed ? 'Đã xác nhận thanh toán.' : 'Không tìm thấy đăng ký.',
    ], $changed ? 200 : 404);
}

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
$packages = data_packages();
$members = data_members();
$pendingSlots = count(array_filter($classes, static fn (array $class): bool => $class['status'] === 'cho_duyet'));
$pendingSlotsList = array_values(array_filter($classes, static fn (array $class): bool => $class['status'] === 'cho_duyet'));
$pendingTotal = $pendingSlots + count($pendingPackages);
$openClasses = 0;
foreach ($classes as $class) {
    if ($class['status'] === 'dang_mo') {
        $openClasses++;
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
                    <div class="stat-label">Yêu cầu chờ duyệt</div>
                    <div class="stat-value" style="color:var(--warning);"><?= $pendingTotal ?></div>
                    <div class="stat-foot"><?= $pendingSlots ?> đề xuất lớp · <?= count($pendingPackages) ?> thanh toán</div>
                </div>
                <div class="stat-icon amber ms-auto"><?= icon('bell') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Đăng ký gói chờ thanh toán</h2>
                    <p class="sub"><?= count($pendingPackages) ?> đăng ký cần xác nhận</p>
                </div>
                <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/admin/registrations')) ?>">Xem tất cả <?= icon('arrow-right') ?></a>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Hội viên</th>
                            <th>Gói tập</th>
                            <th>Thời hạn</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th class="cell-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pendingPackages, 0, 3) as $row): ?>
                            <tr>
                                <td><?= e($row['member']) ?></td>
                                <td><?= e($row['package']) ?></td>
                                <td class="text-muted-2"><?= e($row['from']) ?> → <?= e($row['to']) ?></td>
                                <td class="cell-strong"><?= e(money($row['price'])) ?></td>
                                <td><?= status_badge($row['payment']) ?></td>
                                <td class="cell-actions">
                                    <button class="btn btn-primary btn-sm" type="button"
                                            data-confirm-package="<?= (int) $row['id'] ?>"
                                            data-confirm-text="Xác nhận đã thanh toán cho <?= e($row['member']) ?>?">Xác nhận</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="app-card">
            <div class="app-card-header">
                <h2>Yêu cầu chờ duyệt</h2>
                <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/admin/availability')) ?>">Duyệt</a>
            </div>
            <div class="app-card-body">
                <ul class="app-timeline">
                    <?php foreach (array_slice($pendingSlotsList, 0, 2) as $slot): ?>
                        <li>
                            <span class="tl-dot warning"><?= icon('calendar-plus') ?></span>
                            <div class="tl-title"><?= e($slot['instructor']) ?> · <?= e($slot['name']) ?></div>
                            <div class="tl-meta"><?= e($slot['start'] . ' – ' . $slot['end']) ?> · Chờ duyệt</div>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($pendingPackages): ?>
                        <li>
                            <span class="tl-dot warning"><?= icon('receipt') ?></span>
                            <div class="tl-title"><?= count($pendingPackages) ?> thanh toán chờ xác nhận</div>
                            <div class="tl-meta">Tổng giá trị <?= e(money(array_sum(array_column($pendingPackages, 'price')))) ?></div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/admin/index.js')) ?>?v=1"></script>
<?php require $partials . '/foot.php'; ?>