<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $db = common_db();
    $accountId = (int) $user['id'];
    $action = (string) ($_GET['action'] ?? '');

    if ($action === 'profile') {
        $name = trim((string) ($input['ho_ten'] ?? ''));
        if (mb_strlen($name) < 2) {
            json_response(['success' => false, 'message' => 'Họ tên không hợp lệ.'], 422);
        }

        $stmt = $db->prepare('UPDATE tai_khoan SET ho_ten = ? WHERE id = ? AND vai_tro = "admin"');
        $stmt->bind_param('si', $name, $accountId);
        $stmt->execute();
        $stmt->close();
        json_response(['success' => true, 'message' => 'Đã cập nhật thông tin admin.']);
    }

    if ($action === 'password') {
        $old = (string) ($input['mat_khau_cu'] ?? '');
        $new = (string) ($input['mat_khau_moi'] ?? '');
        $confirm = (string) ($input['mat_khau_xac_nhan'] ?? '');

        $stmt = $db->prepare('SELECT mat_khau FROM tai_khoan WHERE id = ? AND vai_tro = "admin" LIMIT 1');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($old, (string) $row['mat_khau'])) {
            json_response(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng.'], 422);
        }
        if (strlen($new) < 6) {
            json_response(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'], 422);
        }
        if ($new !== $confirm) {
            json_response(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp.'], 422);
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE tai_khoan SET mat_khau = ? WHERE id = ? AND vai_tro = "admin"');
        $stmt->bind_param('si', $hash, $accountId);
        $stmt->execute();
        $stmt->close();
        json_response(['success' => true, 'message' => 'Đã đổi mật khẩu.']);
    }

    json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
}

require_role('admin');
$menu = require __DIR__ . '/../common/menus/admin.php';
$appRole = 'admin';
$activeMenu = 'account';
$pageTitle = 'Thông tin admin';
$pageScripts = [];
$account = common_user();
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
                    <h1>Thông tin admin</h1>
                    <p>Cập nhật thông tin cá nhân và bảo mật tài khoản quản trị.</p>
                </div>
                <div class="page-head-actions">
                    <a class="btn btn-ghost" href="<?= e(url('/admin')) ?>"><?= icon('arrow-left') ?> Về tổng quan</a>
                    <a class="btn btn-danger-soft" href="<?= e(url('/common/logout.php')) ?>"><?= icon('box-arrow-right') ?> Đăng xuất</a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="app-card">
                        <div class="app-card-body text-center">
                            <?= avatar($account['ho_ten'] ?? 'Quản trị viên', 'avatar-xl', 'mx-auto') ?>
                            <h2 class="mt-3 mb-1" id="account-name" style="font-size:1.05rem;"><?= e($account['ho_ten'] ?? '') ?></h2>
                            <p class="text-muted-2 text-small mb-2">Quản trị viên</p>
                            <?= badge('Đang hoạt động', 'success') ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="app-card mb-3">
                        <div class="app-card-header"><h2>Thông tin cá nhân</h2></div>
                        <div class="app-card-body">
                            <form id="profile-form">
                                <label class="form-label" for="admin-name">Họ và tên <span class="req">*</span></label>
                                <input class="form-control" id="admin-name" name="ho_ten" type="text" value="<?= e($account['ho_ten'] ?? '') ?>" required>
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-primary" type="submit"><?= icon('check-lg') ?> Lưu thay đổi</button>
                                </div>
                            </form>
                            <div class="kv-list mt-3">
                                <div class="kv"><span>Tên đăng nhập</span><span><?= e($account['ten_dang_nhap'] ?? '') ?></span></div>
                                <div class="kv"><span>Vai trò</span><span>Quản trị viên</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="app-card">
                        <div class="app-card-header"><h2>Đổi mật khẩu</h2></div>
                        <div class="app-card-body">
                            <form id="password-form">
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label" for="pw-current">Mật khẩu hiện tại</label><input class="form-control" id="pw-current" type="password" required></div>
                                    <div class="col-md-4"><label class="form-label" for="pw-new">Mật khẩu mới</label><input class="form-control" id="pw-new" type="password" minlength="6" required></div>
                                    <div class="col-md-4"><label class="form-label" for="pw-confirm">Nhập lại mật khẩu</label><input class="form-control" id="pw-confirm" type="password" minlength="6" required></div>
                                </div>
                                <button class="btn btn-primary mt-3" type="submit"><?= icon('key') ?> Cập nhật mật khẩu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<script src="<?= e(url('/common/assets/js/admin/account.js')) ?>?v=1"></script>
<?php require $partials . '/foot.php'; ?>
