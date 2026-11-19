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
    $id = post_int('id') ?: (int) ($_GET['id'] ?? 0);

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
    $savedPackage = $savedId > 0 ? package_find($savedId) : null;

    if ($id === 0 && $savedPackage !== null) {
        json_response([
            'success' => true,
            'message' => 'Đã tạo gói tập.',
            'data' => $savedPackage,
        ], 201);
    }
    if ($savedPackage === null) {
        json_response(['success' => false, 'message' => 'Không tìm thấy gói tập.'], 404);
    }

    json_response([
        'success' => true,
        'message' => 'Đã cập nhật gói tập.',
        'data' => $savedPackage,
    ]);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'packages';
$pageTitle = 'Quản lý gói tập';
$pageScripts = [];

$packages = data_packages();
$packageFilter = trim((string) ($_GET['package_id'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
if ($packageFilter !== '' && !ctype_digit($packageFilter)) {
    $packageFilter = '';
}
if (!in_array($statusFilter, ['', 'dang_ban', 'ngung_ban'], true)) {
    $statusFilter = '';
}
$packages = array_values(array_filter($packages, static function (array $package) use ($packageFilter, $statusFilter): bool {
    if ($packageFilter !== '' && (string) $package['id'] !== $packageFilter) {
        return false;
    }
    return $statusFilter === '' || $package['status'] === $statusFilter;
}));

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

<form class="filter-bar" method="get" action="<?= e(url('/admin/packages')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="pk-q">Tìm theo mã gói</label>
        <input class="form-control" id="pk-q" name="package_id" type="number" min="1" inputmode="numeric" value="<?= e($packageFilter) ?>" placeholder="Nhập mã gói...">
    </div>
    <div class="filter-field">
        <label class="form-label" for="pk-status">Trạng thái</label>
        <select class="form-select" id="pk-status" name="status">
            <option value="">Tất cả</option>
            <option value="dang_ban" <?= $statusFilter === 'dang_ban' ? 'selected' : '' ?>>Đang bán</option>
            <option value="ngung_ban" <?= $statusFilter === 'ngung_ban' ? 'selected' : '' ?>>Ngừng bán</option>
        </select>
    </div>
    <div class="filter-actions">
        <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm</button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/packages')) ?>"><?= icon('arrow-counterclockwise') ?> Đặt lại</a>
    </div>
</form>

<div class="app-card">
    <div class="app-card-header">
        <h2>Danh sách gói tập</h2>
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="packages-table">
            <thead><tr><th>Gói tập</th><th>Thời hạn</th><th>Giá bán</th><th>Đã bán</th><th>Doanh thu</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr></thead>
            <tbody id="packages-body"></tbody>
        </table>
    </div>
</div>
<script type="application/json" id="package-init"><?= json_encode($packages, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

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

<script src="<?= e(url('/common/assets/js/admin/packages.js')) ?>?v=13"></script>
<?php require $partials . '/foot.php'; ?>