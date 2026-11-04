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

    if ($action === 'create-package') {
        $input = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($input)) {
            $input = $_POST;
        }
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

    if ($action === 'confirm-package' || $action === 'cancel-package' || $action === 'cancel-class') {
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

    if ($action === 'cancel-class') {
        admin_class_registration_cancel($id);
        json_response(['success' => true, 'message' => 'Đã hủy đăng ký lớp.']);
    }

    json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'registrations';
$pageTitle = 'Quản lý đăng ký';
$pageScripts = [];

$packageRegs = data_package_registrations();
$classRegs = data_class_registrations();
$members = data_members();
$packages = data_packages();
$pending = count(array_filter($packageRegs, static fn ($r) => $r['payment'] === 'chua_thanh_toan'));
$paid = count(array_filter($packageRegs, static fn ($r) => $r['payment'] === 'da_thanh_toan'));
$paidRevenue = 0;
foreach ($packageRegs as $reg) {
    if ($reg['payment'] === 'da_thanh_toan') {
        $paidRevenue += $reg['price'];
    }
}
$cancelledClasses = count(array_filter($classRegs, static fn ($r) => $r['status'] === 'da_huy'));

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
        <h1>Quản lý đăng ký</h1>
        <p>Đăng ký gói tập, lớp học và xác nhận thanh toán.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#registrationFormModal"><?= icon('plus-lg') ?> Tạo đăng ký</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đăng ký gói</div>
            <div class="stat-value"><?= count($packageRegs) ?></div>
            <div class="stat-foot">Trong tháng 10</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Chờ thanh toán</div>
            <div class="stat-value" style="color:var(--warning);"><?= $pending ?></div>
            <div class="stat-foot">Cần xác nhận</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đã thanh toán</div>
            <div class="stat-value text-success"><?= $paid ?></div>
            <div class="stat-foot">Doanh thu <?= e(money($paidRevenue)) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đăng ký lớp</div>
            <div class="stat-value"><?= count($classRegs) ?></div>
            <div class="stat-foot"><?= $cancelledClasses ?> đăng ký đã hủy</div>
        </div>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header pb-0" style="border-bottom:none;">
        <ul class="nav nav-tabs app-tabs w-100" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#reg-packages" type="button" role="tab" aria-selected="true">Đăng ký gói <span class="count"><?= count($packageRegs) ?></span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reg-classes" type="button" role="tab" aria-selected="false">Đăng ký lớp <span class="count"><?= count($classRegs) ?></span></button></li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="reg-packages" role="tabpanel" tabindex="0">
            <div class="table-toolbar">
                <input class="form-control toolbar-search" type="search" placeholder="Tìm hội viên…" data-table-filter="#package-regs-table" aria-label="Tìm hội viên">
                <select class="form-select" aria-label="Trạng thái thanh toán">
                    <option>Tất cả thanh toán</option>
                    <option>Chưa thanh toán</option>
                    <option>Đã thanh toán</option>
                    <option>Đã hủy</option>
                </select>
                <select class="form-select" aria-label="Phương thức">
                    <option>Tất cả phương thức</option>
                    <option>Tiền mặt</option>
                    <option>Chuyển khoản</option>
                    <option>Thẻ</option>
                </select>
            </div>
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

        <div class="tab-pane fade" id="reg-classes" role="tabpanel" tabindex="0">
            <div class="table-toolbar">
                <input class="form-control toolbar-search" type="search" placeholder="Tìm hội viên / lớp…" data-table-filter="#class-regs-table" aria-label="Tìm đăng ký lớp">
                <select class="form-select" aria-label="Trạng thái">
                    <option>Tất cả trạng thái</option>
                    <option>Đã đăng ký</option>
                    <option>Đã hủy</option>
                </select>
            </div>
            <div class="app-table-wrap">
                <table class="app-table" id="class-regs-table">
                    <thead>
                        <tr><th>Mã</th><th>Hội viên</th><th>Lớp học</th><th>Ngày đăng ký</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classRegs as $reg): ?>
                            <tr data-row>
                                <td class="cell-strong">DL-<?= e((string) $reg['id']) ?></td>
                                <td><?= e($reg['member']) ?></td>
                                <td><?= e($reg['class']) ?></td>
                                <td class="text-muted-2"><?= e($reg['registered']) ?></td>
                                <td><?= status_badge($reg['status']) ?></td>
                                <td class="cell-actions">
                                    <?php if ($reg['status'] === 'dang_ky'): ?>
                                        <button class="btn btn-danger-soft btn-sm" type="button"
                                                data-reg-action="cancel-class"
                                                data-reg-id="<?= (int) $reg['id'] ?>"
                                                data-reg-confirm="Hủy đăng ký lớp <?= e($reg['class']) ?> của <?= e($reg['member']) ?>?">Hủy</button>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" type="button" disabled>—</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($classRegs), 1, 10, 'đăng ký lớp') ?>
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
                            <input class="form-control" id="rf-from" name="ngay_bat_dau" type="date" value="2026-10-24" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="rf-to">Kết thúc <span class="req">*</span></label>
                            <input class="form-control" id="rf-to" name="ngay_ket_thuc" type="date" value="2026-11-24" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="rf-method">Phương thức thanh toán</label>
                            <select class="form-select" id="rf-method" name="phuong_thuc_thanh_toan">
                                <option value="tien_mat">Tiền mặt</option>
                                <option value="chuyen_khoan">Chuyển khoản</option>
                                <option value="the">Thẻ</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mb-0 d-flex gap-2 py-2">
                                <?= icon('info-circle') ?>
                                <span class="text-tiny">Đăng ký mới sẽ ở trạng thái <strong>Chưa thanh toán</strong>. Xác nhận thanh toán sau khi nhận tiền.</span>
                            </div>
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

<script src="<?= e(url('/common/assets/js/admin/registrations.js')) ?>?v=1"></script>
<?php require $partials . '/foot.php'; ?>