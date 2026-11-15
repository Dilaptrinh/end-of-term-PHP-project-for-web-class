<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

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
        ], $changed ? 200 : 404);
    }

    if ($action === 'create-package') {
        $input = $_POST;
        $memberId = (int) ($input['hoi_vien_id'] ?? 0);
        $packageId = (int) ($input['goi_tap_id'] ?? 0);
        $from = trim((string) ($input['ngay_bat_dau'] ?? ''));
        $to = trim((string) ($input['ngay_ket_thuc'] ?? ''));
        $payMethod = trim((string) ($input['phuong_thuc_thanh_toan'] ?? ''));

        if ($memberId <= 0 || $packageId <= 0 || $from === '' || $to === '') {
            json_response(['success' => false, 'message' => 'Vui lòng chọn hội viên, gói tập và thời hạn.'], 422);
        }

        $newId = admin_package_registration_create($memberId, $packageId, $from, $to, $payMethod !== '' ? $payMethod : null);
        if ($newId === 0) {
            json_response(['success' => false, 'message' => 'Gói tập không tồn tại.'], 404);
        }

        json_response(['success' => true, 'message' => 'Đã tạo đăng ký gói.'], 201);
    }

    if ($action === 'confirm-package' || $action === 'cancel-package') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }
    }

    if ($action === 'confirm-package') {
        admin_package_registration_confirm($id);
        json_response(['success' => true, 'message' => 'Đã xác nhận thanh toán.']);
    }

    if ($action === 'cancel-package') {
        admin_package_registration_cancel($id);
        json_response(['success' => true, 'message' => 'Đã hủy đăng ký gói.']);
    }

    if ($action !== 'save') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
    }

    $input = $_POST;

    $name = trim((string) ($input['ten_goi'] ?? ''));
    $days = (int) ($input['thoi_han_ngay'] ?? 0);
    $errors = [];
    if (mb_strlen($name) < 2) {
        $errors['ten_goi'] = 'Tên gói không hợp lệ.';
    }
    if ($days < 1) {
        $errors['thoi_han_ngay'] = 'Thời hạn không hợp lệ.';
    }
    if ($errors) {
        json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
    }

    $values = [
        $name,
        $days,
        (float) ($input['gia'] ?? 0),
        ($input['mo_ta'] ?? '') !== '' ? (string) $input['mo_ta'] : null,
        (string) ($input['cap_do'] ?? 'basic'),
        (string) ($input['trang_thai'] ?? 'dang_ban'),
    ];
    $savedId = admin_package_save($values, $id);

    if ($id === 0) {
        json_response(['success' => true, 'data' => ['id' => $savedId], 'message' => 'Đã tạo gói tập.'], 201);
    }
    if ($savedId === 0) {
        json_response(['success' => false, 'message' => 'Không tìm thấy gói tập.'], 404);
    }

    json_response(['success' => true, 'message' => 'Đã cập nhật gói tập.']);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'packages';
$pageTitle = 'Quản lý gói tập';
$pageScripts = [];

$packages = data_packages();
$packageRegs = data_package_registrations();
$members = data_members();
$packageId = trim((string) ($_GET['package_id'] ?? ''));
$packageFilterId = preg_match('/^(?:DK-)?(\d+)$/i', $packageId, $packageMatch) ? $packageMatch[1] : '';
if ($packageFilterId !== '') {
    $packageRegs = array_values(array_filter($packageRegs, static function (array $registration) use ($packageFilterId): bool {
        return (string) $registration['id'] === $packageFilterId;
    }));
}
$pending = 0;
$paid = 0;
$paidRevenue = 0;
foreach ($packageRegs as $reg) {
    if ($reg['payment'] === 'da_thanh_toan') {
        $paid++;
        $paidRevenue += $reg['price'];
    }
    if ($reg['payment'] === 'chua_thanh_toan') {
        $pending++;
    }
}
$selling = 0;
foreach ($packages as $package) {
    if ($package['status'] === 'dang_ban') {
        $selling++;
    }
}
$estimatedRevenue = 0;
foreach ($packages as $package) {
    $estimatedRevenue += $package['price'] * $package['sold'];
}
$bestSeller = $packages[0] ?? ['name' => '—', 'sold' => 0];
foreach ($packages as $package) {
    if ($package['sold'] > $bestSeller['sold']) {
        $bestSeller = $package;
    }
}
$revenueLabel = $estimatedRevenue >= 1000000000
    ? number_format($estimatedRevenue / 1000000000, 2, ',', '.') . ' tỷ'
    : money($estimatedRevenue);

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
        <p><?= count($packages) ?> gói · <?= $selling ?> đang bán · <?= count($packageRegs) ?> đăng ký.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"><?= icon('plus-lg') ?> Thêm gói tập</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Gói đang bán</div>
            <div class="stat-value"><?= $selling ?></div>
            <div class="stat-foot"><?= count($packages) - $selling ?> gói ngừng bán</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Lượt đăng ký</div>
            <div class="stat-value"><?= e((string) array_sum(array_column($packages, 'sold'))) ?></div>
            <div class="stat-foot">Tích lũy toàn hệ thống</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Doanh thu ước tính</div>
            <div class="stat-value" style="font-size:1.35rem;"><?= e($revenueLabel) ?></div>
            <div class="stat-foot">Từ các gói đang bán</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Gói bán chạy</div>
            <div class="stat-value" style="font-size:1.05rem;"><?= e($bestSeller['name']) ?></div>
            <div class="stat-foot"><?= e((string) $bestSeller['sold']) ?> lượt đăng ký</div>
        </div>
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
        <span class="badge-soft ms-auto"><?= count($packages) ?> gói</span>
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="packages-table">
            <thead><tr><th>Gói tập</th><th>Thời hạn</th><th>Giá bán</th><th>Đã bán</th><th>Doanh thu</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($packages as $package): ?>
                <tr data-row data-package-id="<?= (int) $package['id'] ?>" data-package-status="<?= e($package['status']) ?>">
                    <td class="cell-strong"><?= e($package['name']) ?><div class="cell-sub"><?= e(truncate($package['desc'], 46)) ?></div></td>
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

<div class="app-card mt-3" id="package-registrations">
    <div class="app-card-header">
        <h2>Đăng ký gói của hội viên</h2>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#registrationFormModal"><?= icon('plus-lg') ?> Tạo đăng ký</button>
    </div>
    <div class="row g-3 px-3 pt-3">
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-label">Tổng đăng ký</div>
                <div class="stat-value"><?= count($packageRegs) ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-label">Chờ thanh toán</div>
                <div class="stat-value" style="color:var(--warning);"><?= $pending ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-label">Đã thanh toán</div>
                <div class="stat-value text-success"><?= $paid ?></div>
                <div class="stat-foot">Doanh thu <?= e(money($paidRevenue)) ?></div>
            </div>
        </div>
    </div>
    <form class="table-toolbar mx-3 mt-3" method="get" action="<?= e(url('/admin/packages')) ?>">
        <input class="form-control toolbar-search" name="package_id" type="search" inputmode="numeric" value="<?= e($packageId) ?>" placeholder="Nhập mã đăng ký, VD: 1" aria-label="Tìm theo mã đăng ký">
        <button class="btn btn-primary" type="submit"><?= icon('funnel') ?> Lọc</button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/packages#package-registrations')) ?>">Xóa lọc</a>
    </form>
    <div class="app-table-wrap">
        <table class="app-table" id="package-regs-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Hội viên</th>
                    <th>Gói tập</th>
                    <th>Thời hạn</th>
                    <th>Số tiền</th>
                    <th>Thanh toán</th>
                    <th>Người xác nhận</th>
                    <th class="cell-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packageRegs as $reg): ?>
                    <tr data-row>
                        <td class="cell-strong">DK-<?= e((string) $reg['id']) ?></td>
                        <td><?= e($reg['member']) ?></td>
                        <td><?= e($reg['package']) ?></td>
                        <td class="text-muted-2 text-tiny"><?= e($reg['from']) ?><br><?= e($reg['to']) ?></td>
                        <td class="cell-strong"><?= e(money($reg['price'])) ?></td>
                        <td><?= status_badge($reg['payment']) ?><div class="cell-sub"><?= e($reg['method']) ?></div></td>
                        <td class="text-muted-2 text-tiny"><?= e($reg['staff']) ?><?php if ($reg['paid_at'] !== '—'): ?><br><?= e($reg['paid_at']) ?><?php endif; ?></td>
                        <td class="cell-actions">
                            <?php if ($reg['payment'] === 'chua_thanh_toan'): ?>
                                <button class="btn btn-primary btn-sm" type="button"
                                        data-reg-action="confirm-package"
                                        data-reg-id="<?= (int) $reg['id'] ?>"
                                        data-reg-confirm="Xác nhận thanh toán <?= e(money($reg['price'])) ?> cho <?= e($reg['member']) ?>?"><?= icon('check-lg') ?> Xác nhận</button>
                                <button class="btn btn-danger-soft btn-sm" type="button"
                                        data-reg-action="cancel-package"
                                        data-reg-id="<?= (int) $reg['id'] ?>"
                                        data-reg-confirm="Hủy đăng ký của <?= e($reg['member']) ?>?">Hủy</button>
                            <?php else: ?>
                                <button class="btn btn-ghost btn-sm" type="button" data-detail="<?= e($reg['member'] . ' · ' . $reg['package'] . ' · ' . money($reg['price'])) ?>"><?= icon('eye') ?> Xem</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($packageRegs), 1, 10, 'đăng ký') ?>
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

<div class="modal fade" id="registrationFormModal" tabindex="-1" aria-labelledby="registrationFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="registration-form">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="registrationFormTitle">Tạo đăng ký gói</h2>
                        <p class="text-tiny text-muted-2 mb-0">Đăng ký thay cho hội viên</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="rf-member">Hội viên <span class="req">*</span></label>
                            <select class="form-select" id="rf-member" name="hoi_vien_id" required>
                                <option value="">Chọn hội viên</option>
                                <?php foreach ($members as $member): ?><option value="<?= $member['id'] ?>"><?= e($member['name'] . ' · ' . $member['phone']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="rf-package">Gói tập <span class="req">*</span></label>
                            <select class="form-select" id="rf-package" name="goi_tap_id" required>
                                <option value="">Chọn gói</option>
                                <?php foreach ($packages as $package): ?><option value="<?= $package['id'] ?>"><?= e($package['name'] . ' · ' . money($package['price'])) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rf-from">Bắt đầu <span class="req">*</span></label>
                            <input class="form-control" id="rf-from" name="ngay_bat_dau" type="date" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rf-to">Kết thúc <span class="req">*</span></label>
                            <input class="form-control" id="rf-to" name="ngay_ket_thuc" type="date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="rf-method">Phương thức thanh toán</label>
                            <select class="form-select" id="rf-method" name="phuong_thuc_thanh_toan">
                                <option value="tien_mat">Tiền mặt</option>
                                <option value="chuyen_khoan">Chuyển khoản</option>
                                <option value="the">Thẻ</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Tạo đăng ký</button>
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
<script src="<?= e(url('/common/assets/js/admin/packages.js')) ?>?v=2"></script>