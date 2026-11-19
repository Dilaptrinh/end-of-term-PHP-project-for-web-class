<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'customer') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $action = (string) ($_GET['action'] ?? '');
    $registrationId = post_int('id') ?: (int) ($_GET['id'] ?? 0);
    if ($registrationId <= 0) {
        json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
    }

    $db = common_db();

    if ($action === 'cancel-package') {
        $stmt = $db->prepare(
            'SELECT d.id, d.trang_thai, hv.tai_khoan_id
               FROM dang_ky_goi d
               JOIN hoi_vien hv ON hv.id = d.hoi_vien_id
              WHERE d.id = ?
              LIMIT 1'
        );
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $packageRegistration = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$packageRegistration) {
            json_response(['success' => false, 'message' => 'Không tìm thấy đăng ký gói.'], 404);
        }
        if ((int) $packageRegistration['tai_khoan_id'] !== (int) $user['id']) {
            json_response(['success' => false, 'message' => 'Bạn không có quyền hủy đăng ký này.'], 403);
        }
        if ($packageRegistration['trang_thai'] !== 'chua_thanh_toan') {
            json_response(['success' => false, 'message' => 'Chỉ có thể hủy đăng ký gói đang chờ thanh toán.'], 422);
        }

        $stmt = $db->prepare('UPDATE dang_ky_goi SET trang_thai = "da_huy" WHERE id = ? AND trang_thai = "chua_thanh_toan"');
        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();

        if ($changed < 1) {
            json_response(['success' => false, 'message' => 'Đăng ký gói đã được xử lý hoặc không còn hiệu lực.'], 422);
        }

        json_response([
            'success' => true,
            'message' => 'Đã hủy đăng ký gói.',
            'data' => ['id' => $registrationId, 'status' => 'da_huy'],
        ]);
    }

    if ($action !== 'cancel') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 422);
    }

    $stmt = $db->prepare('SELECT d.id, hv.tai_khoan_id FROM dang_ky_lop d JOIN hoi_vien hv ON hv.id = d.hoi_vien_id WHERE d.id = ? LIMIT 1');
    $stmt->bind_param('i', $registrationId);
    $stmt->execute();
    $registration = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$registration) {
        json_response(['success' => false, 'message' => 'Không tìm thấy đăng ký.'], 404);
    }
    if ((int) $registration['tai_khoan_id'] !== (int) $user['id']) {
        json_response(['success' => false, 'message' => 'Bạn không có quyền hủy đăng ký này.'], 403);
    }

    $stmt = $db->prepare('UPDATE dang_ky_lop SET trang_thai = "da_huy" WHERE id = ? AND trang_thai = "dang_ky"');
    $stmt->bind_param('i', $registrationId);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();

    if ($changed < 1) {
        json_response(['success' => false, 'message' => 'Đăng ký đã được hủy hoặc không còn hiệu lực.'], 422);
    }

    json_response([
        'success' => true,
        'message' => 'Đã hủy đăng ký lớp.',
        'data' => ['id' => $registrationId, 'status' => 'da_huy'],
    ]);
}

require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';

$appRole = 'customer';
$activeMenu = 'history';
$pageTitle = 'Lịch sử hoạt động';
$pageScripts = [];

$packages = data_customer_packages();
$classHistory = data_customer_class_history();

$historyTab = ($_GET['tab'] ?? '') === 'classes' ? 'classes' : 'packages';
$historyPerPage = 10;

$totalPackages = count($packages);
$packagesPage = max(1, min((int) ($_GET['page'] ?? 1), max(1, (int) ceil($totalPackages / $historyPerPage))));
$packages = array_slice($packages, ($packagesPage - 1) * $historyPerPage, $historyPerPage);

$totalClassHistory = count($classHistory);
$classesPage = max(1, min((int) ($_GET['class_page'] ?? 1), max(1, (int) ceil($totalClassHistory / $historyPerPage))));
$classHistory = array_slice($classHistory, ($classesPage - 1) * $historyPerPage, $historyPerPage);

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
        <h1>Lịch sử hoạt động</h1>
        <p>Theo dõi gói tập và lịch sử đăng ký lớp học của bạn.</p>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header pb-0" style="border-bottom:none;">
        <ul class="nav nav-tabs app-tabs w-100" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $historyTab === 'packages' ? 'active' : '' ?>" id="tab-packages" data-bs-toggle="tab" data-bs-target="#pane-packages" type="button" role="tab" aria-selected="<?= $historyTab === 'packages' ? 'true' : 'false' ?>">
                    <?= icon('box-seam') ?> Gói tập <span class="count"><?= $totalPackages ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $historyTab === 'classes' ? 'active' : '' ?>" id="tab-classes" data-bs-toggle="tab" data-bs-target="#pane-classes" type="button" role="tab" aria-selected="<?= $historyTab === 'classes' ? 'true' : 'false' ?>">
                    <?= icon('journal-bookmark') ?> Lớp học <span class="count"><?= $totalClassHistory ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade <?= $historyTab === 'packages' ? 'show active' : '' ?>" id="pane-packages" role="tabpanel" aria-labelledby="tab-packages" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Gói tập</th>
                            <th>Thời hạn</th>
                            <th>Giá</th>
                            <th>Thanh toán</th>
                            <th>Phương thức</th>
                            <th>Ngày thanh toán</th>
                            <th class="cell-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $row): ?>
                            <tr data-row>
                                <td class="cell-strong"><?= e($row['name']) ?></td>
                                <td><?= e($row['from']) ?> → <?= e($row['to']) ?></td>
                                <td class="cell-strong"><?= e(money($row['price'])) ?><?php if (($row['loai_giao_dich'] ?? '') === 'nang_cap'): ?><div class="cell-sub">Nâng cấp · khấu trừ <?= e(money((int) ($row['so_tien_giam'] ?? 0))) ?></div><?php endif; ?></td>
                                <td data-status-cell><?= status_badge($row['payment']) ?></td>
                                <td><?= e($row['method']) ?></td>
                                <td class="text-muted-2"><?= e($row['paid_at']) ?></td>
                                <td class="cell-actions">
                                    <?php if ($row['payment'] === 'chua_thanh_toan'): ?>
                                        <button class="btn btn-danger-soft btn-sm" type="button"
                                                data-cancel-package-id="<?= (int) $row['id'] ?>"
                                                data-cancel-package-name="<?= e($row['name']) ?>">Hủy</button>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" type="button" disabled>—</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination($totalPackages, $packagesPage, $historyPerPage, 'giao dịch', static function (int $page): string {
                return url('/customer/history') . '?' . http_build_query(['tab' => 'packages', 'page' => $page]);
            }) ?>
        </div>

        <div class="tab-pane fade <?= $historyTab === 'classes' ? 'show active' : '' ?>" id="pane-classes" role="tabpanel" aria-labelledby="tab-classes" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Lớp học</th>
                            <th>Giáo viên</th>
                            <th>Lịch học</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th class="cell-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classHistory as $row): ?>
                            <tr data-row>
                                <td class="cell-strong"><?= e($row['class']) ?></td>
                                <td><?= e($row['teacher']) ?></td>
                                <td class="text-muted-2"><?= e($row['schedule']) ?></td>
                                <td class="text-muted-2"><?= e($row['registered']) ?></td>
                                <td data-status-cell><?= status_badge($row['status']) ?></td>
                                <td class="cell-actions">
                                    <?php if ($row['status'] === 'dang_ky'): ?>
                                        <button class="btn btn-danger-soft btn-sm" type="button"
                                                data-cancel-id="<?= (int) $row['id'] ?>"
                                                data-cancel-class="<?= e($row['class']) ?>">Hủy</button>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" type="button" disabled>—</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination($totalClassHistory, $classesPage, $historyPerPage, 'lớp học', static function (int $page): string {
                return url('/customer/history') . '?' . http_build_query(['tab' => 'classes', 'class_page' => $page]);
            }) ?>
        </div>

    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/customer/history.js')) ?>?v=3"></script>
<?php require $partials . '/foot.php'; ?>
