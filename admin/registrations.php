<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        redirect('/login.php');
    }

    $action = (string) ($_GET['action'] ?? '');
    $id = post_int('id');

    if ($action === 'confirm-package' || $action === 'cancel-package') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }

        $changed = $action === 'confirm-package'
            ? admin_package_registration_confirm($id)
            : admin_package_registration_cancel($id);
        $registration = $changed ? admin_package_registration_detail($id) : null;
        if (!$registration) {
            json_response(['success' => false, 'message' => 'Không tìm thấy đăng ký hoặc đăng ký đã được xử lý.'], 404);
        }

        json_response([
            'success' => true,
            'message' => $action === 'confirm-package' ? 'Đã xác nhận thanh toán.' : 'Đã hủy đăng ký gói.',
            'data' => $registration,
        ]);
    }

    json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'registrations';
$pageTitle = 'Quản lý đăng ký gói';
$pageScripts = [];

$packageRegs = data_package_registrations();
$packageId = trim((string) ($_GET['package_id'] ?? ''));
$packageFilterId = preg_match('/^(?:DK-)?(\d+)$/i', $packageId, $packageMatch) ? $packageMatch[1] : '';
if ($packageFilterId !== '') {
    $packageRegs = array_values(array_filter($packageRegs, static function (array $registration) use ($packageFilterId): bool {
        return (string) $registration['id'] === $packageFilterId;
    }));
}

// Lọc theo trạng thái thanh toán: chua_thanh_toan, da_thanh_toan, da_huy.
$paymentFilter = trim((string) ($_GET['payment'] ?? ''));
$paymentOptions = ['chua_thanh_toan', 'da_thanh_toan', 'da_huy'];
if ($paymentFilter !== '' && in_array($paymentFilter, $paymentOptions, true)) {
    $packageRegs = array_values(array_filter($packageRegs, static function (array $registration) use ($paymentFilter): bool {
        return $registration['payment'] === $paymentFilter;
    }));
} else {
    $paymentFilter = '';
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
        <h1>Quản lý đăng ký gói</h1>
        <p>Quản lý đăng ký gói tập và xác nhận thanh toán.</p>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header pb-0" style="border-bottom:none;">
        <ul class="nav nav-tabs app-tabs w-100" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#reg-packages" type="button" role="tab" aria-selected="true">Đăng ký gói</button></li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="reg-packages" role="tabpanel" tabindex="0">
            <form class="table-toolbar" method="get" action="<?= e(url('/admin/registrations')) ?>">
                <input class="form-control toolbar-search" name="package_id" type="search" inputmode="numeric" value="<?= e($packageId) ?>" placeholder="Nhập mã đăng ký, VD: 1" aria-label="Tìm theo mã đăng ký">
                <select class="form-select" name="payment" aria-label="Lọc theo trạng thái thanh toán">
                    <option value="">Tất cả trạng thái</option>
                    <option value="chua_thanh_toan" <?= $paymentFilter === 'chua_thanh_toan' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="da_thanh_toan" <?= $paymentFilter === 'da_thanh_toan' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="da_huy" <?= $paymentFilter === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
                <button class="btn btn-primary" type="submit"><?= icon('funnel') ?> Lọc</button>
                <a class="btn btn-ghost" href="<?= e(url('/admin/registrations')) ?>">Xóa lọc</a>
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
                    <tbody id="package-regs-body">
                        <?php if (!$packageRegs): ?>
                            <tr data-empty-row><td colspan="8" class="text-center text-muted-2 py-4">Chưa có đăng ký gói nào.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($packageRegs as $reg): ?>
                            <tr data-row data-reg-row="<?= (int) $reg['id'] ?>">
                                <td class="cell-strong">DK-<?= e((string) $reg['id']) ?></td>
                                <td><?= e($reg['member']) ?></td>
                                <td><?= e($reg['package']) ?></td>
                                <td class="text-muted-2 text-tiny"><?= e($reg['from']) ?><br><?= e($reg['to']) ?></td>
                                <td class="cell-strong"><?= e(money($reg['price'])) ?></td>
                                <td><?= status_badge($reg['payment']) ?><div class="cell-sub"><?= e($reg['method']) ?></div></td>
                                <td class="text-muted-2 text-tiny"><?= e($reg['staff']) ?><?php if ($reg['paid_at'] !== '—'): ?><br><?= e($reg['paid_at']) ?><?php endif; ?></td>
                                <td class="cell-actions">
                                    <?php if ($reg['payment'] === 'chua_thanh_toan'): ?>
                                        <button class="btn btn-primary btn-sm" type="button" data-reg-confirm="<?= (int) $reg['id'] ?>"><?= icon('check-lg') ?> Xác nhận</button>
                                        <button class="btn btn-danger-soft btn-sm" type="button" data-reg-cancel="<?= (int) $reg['id'] ?>">Hủy</button>
                                    <?php else: ?>
                                        <span class="text-muted-2 text-tiny">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($packageRegs), 1, 10, 'đăng ký') ?>
        </div>

    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/admin/registrations.js')) ?>?v=2"></script>
<?php require $partials . '/foot.php'; ?>