<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

function package_find(int $id): ?array
{
    foreach (data_packages() as $package) {
        if ((int) $package['id'] === $id) {
            return $package;
        }
    }
    return null;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    $action = (string) ($_GET['action'] ?? '');
    $id = (int) ($_GET['id'] ?? 0);
    $db = common_db();

    if ($action === 'delete') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }
        $changed = admin_package_delete($id);
        json_response([
            'success' => $changed,
            'message' => $changed ? 'Đã xóa gói tập.' : 'Không tìm thấy gói tập.',
            'id' => $id,
        ], $changed ? 200 : 404);
    }

    if ($action !== 'save') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
    }

    $input = $_POST;

    $name = trim((string) ($input['ten_goi'] ?? ''));
    $days = (int) ($input['thoi_han_ngay'] ?? 0);
    $price = (float) ($input['gia'] ?? 0);
    $level = (string) ($input['cap_do'] ?? 'basic');
    $status = (string) ($input['trang_thai'] ?? 'dang_ban');
    $errors = [];
    //validate
    if (mb_strlen($name) < 2) {
        $errors['ten_goi'] = 'Tên gói không hợp lệ.';
    }
    if ($days < 1) {
        $errors['thoi_han_ngay'] = 'Thời hạn không hợp lệ.';
    }
    if ($price < 0) {
        $errors['gia'] = 'Giá gói không hợp lệ.';
    }
    if (!in_array($level, ['basic', 'standard', 'premium'], true)) {
        $errors['cap_do'] = 'Cấp độ gói không hợp lệ.';
    }
    if (!in_array($status, ['dang_ban', 'ngung_ban'], true)) {
        $errors['trang_thai'] = 'Trạng thái gói không hợp lệ.';
    }
    if ($errors) {
        json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
    }

    $values = [
        $name,
        $days,
        $price,
        ($input['mo_ta'] ?? '') !== '' ? (string) $input['mo_ta'] : null,
        $level,
        $status,
    ];
    $savedId = admin_package_save($values, $id);

    if ($id === 0) {
        json_response([
            'success' => true,
            'message' => 'Đã tạo gói tập.',
            'data' => package_find($savedId),
        ], 201);
    }
    if ($savedId === 0) {
        json_response(['success' => false, 'message' => 'Không tìm thấy gói tập.'], 404);
    }

    json_response([
        'success' => true,
        'message' => 'Đã cập nhật gói tập.',
        'data' => package_find($savedId),
    ]);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'packages';
$pageTitle = 'Quản lý gói tập';
$pageScripts = [];

$packages = data_packages();

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
        <h1>Quản lý gói tập</h1>
        <p>Danh sách toàn bộ gói tập trong hệ thống.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"><?= icon('plus-lg') ?> Thêm gói tập</button>
    </div>
</div>

<div class="filter-bar" id="package-filters">
    <div class="filter-field filter-search">
        <label class="form-label" for="pk-q">Tìm theo mã gói</label>
        <input class="form-control" id="pk-q" type="number" min="1" inputmode="numeric" placeholder="Nhập mã gói...">
    </div>
    <div class="filter-field">
        <label class="form-label" for="pk-status">Trạng thái</label>
        <select class="form-select" id="pk-status">
            <option>Tất cả</option>
            <option>Đang bán</option>
            <option>Ngừng bán</option>
        </select>
    </div>
    <div class="filter-field ms-auto" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-ghost" type="button" id="pk-reset"><?= icon('arrow-counterclockwise') ?> Đặt lại</button>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2>Danh sách gói tập</h2>
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="packages-table">
            <thead><tr><th>Gói tập</th><th>Thời hạn</th><th>Giá bán</th><th>Đã bán</th><th>Doanh thu</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr></thead>
            <tbody id="packages-body">
            <?php if (!$packages): ?>
                <tr data-empty-row><td colspan="7" class="text-center text-muted-2 py-4">Chưa có gói tập nào.</td></tr>
            <?php endif; ?>
            <?php foreach ($packages as $package): ?>
                <tr data-row data-package-id="<?= (int) $package['id'] ?>" data-package-status="<?= e($package['status']) ?>">
                    <td class="cell-strong"><?= e($package['name']) ?><div class="cell-sub"><?= e(truncate((string) $package['desc'], 46)) ?></div></td>
                    <td><?= e((string) $package['days']) ?> ngày</td>
                    <td class="cell-strong"><?= e(money($package['price'])) ?></td>
                    <td><?= e((string) $package['sold']) ?></td>
                    <td class="cell-strong"><?= e(money($package['price'] * $package['sold'])) ?></td>
                    <td><?= status_badge($package['status']) ?></td>
                    <td class="cell-actions">
                        <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"
                                data-package-id="<?= (int) $package['id'] ?>"
                                data-package-name="<?= e($package['name']) ?>"
                                data-package-days="<?= (int) $package['days'] ?>"
                                data-package-price="<?= (int) $package['price'] ?>"
                                data-package-desc="<?= e($package['desc'] ?? '') ?>"
                                data-package-status="<?= e($package['status']) ?>"><?= icon('pencil') ?></button>
                        <button class="btn btn-ghost btn-sm text-danger" type="button" data-delete-id="<?= (int) $package['id'] ?>" data-delete-name="<?= e($package['name']) ?>"><?= icon('trash3') ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="packageFormModal" tabindex="-1" aria-labelledby="packageFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="package-form">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="packageFormTitle">Thông tin gói tập</h2>
                        <p class="text-tiny text-muted-2 mb-0">Thiết lập thời hạn, giá bán và trạng thái.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="pf-name">Tên gói <span class="req">*</span></label>
                            <input class="form-control" id="pf-name" name="ten_goi" type="text" required placeholder="VD: Standard 3 tháng">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pf-days">Thời hạn (ngày) <span class="req">*</span></label>
                            <input class="form-control" id="pf-days" name="thoi_han_ngay" type="number" min="1" value="30" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pf-price">Giá (đ) <span class="req">*</span></label>
                            <input class="form-control" id="pf-price" name="gia" type="number" min="0" step="10000" value="500000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="pf-desc">Mô tả</label>
                            <textarea class="form-control" id="pf-desc" name="mo_ta" rows="3"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pf-level">Cấp độ gói</label>
                            <select class="form-select" id="pf-level" name="cap_do">
                                <option value="basic">Basic - Cơ bản</option>
                                <option value="standard">Standard - Trung cấp</option>
                                <option value="premium">Premium - Tất cả lớp</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="pf-status">Trạng thái</label>
                            <select class="form-select" id="pf-status" name="trang_thai">
                                <option value="dang_ban">Đang bán</option>
                                <option value="ngung_ban">Ngừng bán</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu gói tập</button>
                </div>
            </form>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php require $partials . '/foot.php'; ?>
<script src="<?= e(url('/common/assets/js/admin/packages.js')) ?>?v=5"></script>