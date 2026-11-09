<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' || ($_GET['action'] ?? '') === 'detail') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }
    $action = (string) ($_GET['action'] ?? '');
    $id = (int) ($_GET['id'] ?? 0);
    if ($action === 'detail') {
        $row = admin_account_find($id);
        json_response($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Không tìm thấy tài khoản.'], $row ? 200 : 404);
    }
    if (in_array($action, ['toggle', 'delete', 'update', 'reset'], true) && $id <= 0) {
        json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
    }
    if ($action === 'toggle') {
        if ($id === (int) $user['id']) {
            json_response(['success' => false, 'message' => 'Không thể khóa tài khoản đang đăng nhập.'], 422);
        }
        $changed = admin_account_status($id, (int) ($_GET['active'] ?? 0));
        json_response(['success' => $changed, 'message' => $changed ? 'Đã cập nhật trạng thái tài khoản.' : 'Không tìm thấy tài khoản.'], $changed ? 200 : 404);
    }
    if ($action === 'delete') {
        if ($id === (int) $user['id']) {
            json_response(['success' => false, 'message' => 'Không thể xóa tài khoản đang đăng nhập.'], 422);
        }
        $changed = admin_account_delete($id);
        json_response(['success' => $changed, 'message' => $changed ? 'Đã xóa tài khoản.' : 'Không tìm thấy tài khoản.'], $changed ? 200 : 404);
    }
    if ($action === 'reset') {
        $newPassword = trim((string) ($_POST['mat_khau'] ?? ''));
        if (strlen($newPassword) < 6) {
            json_response(['success' => false, 'message' => 'Mật khẩu mới tối thiểu 6 ký tự.'], 422);
        }
        $changed = admin_account_reset_password($id, $newPassword);
        json_response(['success' => $changed, 'message' => $changed ? 'Đã đặt lại mật khẩu.' : 'Không tìm thấy tài khoản.'], $changed ? 200 : 404);
    }
    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) $input = $_POST;
    $name = trim((string) ($input['ho_ten'] ?? ''));
    $username = trim((string) ($input['ten_dang_nhap'] ?? ''));
    $role = (string) ($input['vai_tro'] ?? '');
    $password = (string) ($input['mat_khau'] ?? '');
    $active = !empty($input['trang_thai']) ? 1 : 0;
    if (in_array($action, ['create', 'update'], true)) {
        $errors = [];
        if (mb_strlen($name) < 2) $errors['ho_ten'] = 'Họ tên không hợp lệ.';
        if ($action === 'create' && !preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) $errors['ten_dang_nhap'] = 'Tên đăng nhập không hợp lệ.';
        if (!in_array($role, ['admin', 'instructor', 'customer'], true)) $errors['vai_tro'] = 'Vai trò không hợp lệ.';
        if ($action === 'create' && strlen($password) < 6) $errors['mat_khau'] = 'Mật khẩu tối thiểu 6 ký tự.';
        if ($errors) json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
        if ($action === 'create') {
            if (admin_username_exists($username)) json_response(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'], 422);
            $savedId = admin_account_save($username, $name, $role, $password, $active);
            json_response(['success' => true, 'data' => ['id' => $savedId], 'message' => 'Đã tạo tài khoản.'], 201);
        }
        $savedId = admin_account_save($username, $name, $role, $password, $active, $id);
        json_response(['success' => true, 'data' => ['id' => $savedId], 'message' => 'Đã cập nhật tài khoản.']);
    }
    json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'accounts';
$pageTitle = 'Quản lý tài khoản';
$pageScripts = [];

$accounts = data_accounts();
$locked = 0;
$adminCount = 0;
foreach ($accounts as $account) {
    if ($account['status'] === 'bi_khoa') {
        $locked++;
    }
    if ($account['role'] === 'admin') {
        $adminCount++;
    }
}
$activeAccounts = count($accounts) - $locked;
$activePercent = count($accounts) > 0 ? (int) round(100 * $activeAccounts / count($accounts)) : 0;

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
        <h1>Quản lý tài khoản</h1>
        <p><?= count($accounts) ?> tài khoản · <?= $locked ?> đang bị khóa.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#accountFormModal"><?= icon('person-plus') ?> Thêm tài khoản</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Tổng tài khoản</div>
            <div class="stat-value"><?= count($accounts) ?></div>
            <div class="stat-foot">Toàn hệ thống</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đang hoạt động</div>
            <div class="stat-value text-success"><?= $activeAccounts ?></div>
            <div class="stat-foot"><?= $activePercent ?>% tài khoản</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đang khóa</div>
            <div class="stat-value" style="color:var(--danger);"><?= $locked ?></div>
            <div class="stat-foot">Có thể mở khóa</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Quản trị viên</div>
            <div class="stat-value"><?= $adminCount ?></div>
            <div class="stat-foot">Toàn quyền hệ thống</div>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-field filter-search">
        <label class="form-label" for="ac-q">Tìm tài khoản</label>
        <input class="form-control" id="ac-q" type="search" placeholder="Tên đăng nhập, họ tên…" data-table-filter="#accounts-table">
    </div>
    <div class="filter-field">
        <label class="form-label" for="ac-role">Vai trò</label>
        <select class="form-select" id="ac-role">
            <option>Tất cả vai trò</option>
            <option>Quản trị viên</option>
            <option>Giáo viên</option>
            <option>Hội viên</option>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="ac-status">Trạng thái</label>
        <select class="form-select" id="ac-status">
            <option>Tất cả</option>
            <option>Đang hoạt động</option>
            <option>Đã khóa</option>
        </select>
    </div>
    <div class="filter-field ms-auto" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-ghost" type="button" data-reload-page><?= icon('arrow-counterclockwise') ?> Đặt lại</button>
    </div>
</div>

<div class="app-card">
    <div class="app-table-wrap">
        <table class="app-table" id="accounts-table">
            <thead>
                <tr>
                    <th>Tài khoản</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Đăng nhập cuối</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th class="cell-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $account): ?>
                    <tr data-row>
                        <td>
                            <div class="user-inline">
                                <?= avatar($account['name'], 'avatar-sm') ?>
                                <span>
                                    <span class="d-block user-name"><?= e($account['name']) ?></span>
                                    <span class="d-block user-sub">TK-<?= str_pad((string) $account['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                </span>
                            </div>
                        </td>
                        <td class="cell-strong"><?= e($account['username']) ?></td>
                        <td>
                            <?php
                            $roleVariant = $account['role'] === 'admin' ? 'primary' : ($account['role'] === 'instructor' ? 'success' : 'muted');
                            ?>
                            <span class="badge-status <?= $roleVariant ?> no-dot"><?= icon(role_icon($account['role'])) ?> <?= e(role_name($account['role'])) ?></span>
                        </td>
                        <td class="text-muted-2"><?= e($account['last_login']) ?></td>
                        <td class="text-muted-2"><?= e($account['created']) ?></td>
                        <td><?= status_badge($account['status']) ?></td>
                        <td class="cell-actions">
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Thao tác"><?= icon('three-dots') ?></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" type="button" data-account-edit="<?= (int) $account['id'] ?>"><?= icon('pencil') ?> Sửa tài khoản</button></li>
                                    <li><button class="dropdown-item" type="button" data-account-reset="<?= (int) $account['id'] ?>"><?= icon('key') ?> Đặt lại mật khẩu</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php if ($account['status'] === 'bi_khoa'): ?>
                                        <li><button class="dropdown-item text-success" type="button" data-account-toggle="<?= (int) $account['id'] ?>" data-active="1"><?= icon('unlock') ?> Mở khóa</button></li>
                                    <?php else: ?>
                                        <li><button class="dropdown-item text-warning" type="button" data-account-toggle="<?= (int) $account['id'] ?>" data-active="0"><?= icon('lock') ?> Khóa tài khoản</button></li>
                                    <?php endif; ?>
                                    <li><button class="dropdown-item text-danger" type="button" data-account-delete="<?= (int) $account['id'] ?>" data-account-name="<?= e($account['username']) ?>"><?= icon('trash3') ?> Xóa</button></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($accounts), 1, 10, 'tài khoản') ?>
</div>

<div class="modal fade" id="accountFormModal" tabindex="-1" aria-labelledby="accountFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="account-form" data-id="">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="accountFormTitle">Thông tin tài khoản</h2>
                        <p class="text-tiny text-muted-2 mb-0">Tạo mới hoặc cập nhật tài khoản đăng nhập.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="af-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="af-name" name="ho_ten" type="text" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="af-username">Tên đăng nhập <span class="req">*</span></label>
                            <input class="form-control" id="af-username" name="ten_dang_nhap" type="text" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="af-role">Vai trò <span class="req">*</span></label>
                            <select class="form-select" id="af-role" name="vai_tro" required>
                                <option value="customer">Hội viên</option>
                                <option value="instructor">Giáo viên</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="af-password">Mật khẩu ban đầu <span class="req">*</span></label>
                            <input class="form-control" id="af-password" name="mat_khau" type="password" minlength="6">
                            <div class="form-text">Người dùng nên đổi mật khẩu sau lần đăng nhập đầu tiên.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" id="af-active" name="trang_thai" type="checkbox" checked>
                                <label class="form-check-label" for="af-active">Kích hoạt tài khoản ngay</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu tài khoản</button>
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
<script src="<?= e(url('/common/assets/js/admin/accounts.js')) ?>?v=1"></script>