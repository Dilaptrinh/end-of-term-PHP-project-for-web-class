<?php
require __DIR__ . '/../common/bootstrap.php';
require_once __DIR__ . '/../admin/queries.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'instructor') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $action = (string) ($_GET['action'] ?? '');
    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $db = common_db();
    $accountId = (int) $user['id'];

    if ($action === 'profile') {
        $name = trim((string) ($input['ho_ten'] ?? ''));
        $phone = trim((string) ($input['sdt'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $specialtyIds = array_values(array_filter(array_map('intval', (array) ($input['chuyen_mon_ids'] ?? []))));
        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['ho_ten'] = 'Họ tên không hợp lệ.';
        }
        if ($phone === '') {
            $errors['sdt'] = 'Số điện thoại không hợp lệ.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if ($errors) {
            json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
        }

        $stmt = $db->prepare('SELECT id FROM giao_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $instructorRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$instructorRow) {
            json_response(['success' => false, 'message' => 'Không tìm thấy hồ sơ giáo viên của tài khoản này.'], 404);
        }

        $stmt = $db->prepare('UPDATE tai_khoan SET ho_ten = ? WHERE id = ?');
        $stmt->bind_param('si', $name, $accountId);
        $stmt->execute();
        $stmt->close();

        admin_instructor_sync_specialties((int) $instructorRow['id'], $specialtyIds);

        $stmt = $db->prepare('UPDATE giao_vien SET ho_ten = ?, sdt = ?, email = ? WHERE tai_khoan_id = ? AND deleted_at IS NULL');
        $stmt->bind_param('sssi', $name, $phone, $email, $accountId);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'message' => 'Đã cập nhật hồ sơ.']);
    }

    if ($action === 'password') {
        $old = (string) ($input['mat_khau_cu'] ?? '');
        $new = (string) ($input['mat_khau_moi'] ?? '');
        $confirm = (string) ($input['mat_khau_xac_nhan'] ?? '');

        $stmt = $db->prepare('SELECT mat_khau FROM tai_khoan WHERE id = ? LIMIT 1');
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
        $stmt = $db->prepare('UPDATE tai_khoan SET mat_khau = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $accountId);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'message' => 'Đã đổi mật khẩu.']);
    }

    json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
}

require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';

$appRole = 'instructor';
$activeMenu = 'account';
$pageTitle = 'Quản lý tài khoản';
$pageScripts = [];

$specialties = data_specialties();
$instructor = data_current_instructor();
$instructorCode = 'GV-' . str_pad((string) ($instructor['id'] ?? 0), 4, '0', STR_PAD_LEFT);

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
        <p>Thông tin cá nhân, chuyên môn và bảo mật tài khoản giáo viên.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-ghost" href="<?= e(url('/instructor')) ?>"><?= icon('arrow-left') ?> Về tổng quan</a>
        <a class="btn btn-danger-soft" href="<?= e(url('/common/logout.php')) ?>"><?= icon('box-arrow-right') ?> Đăng xuất</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="app-card mb-3">
            <div class="app-card-body text-center">
                <?= avatar($instructor['name'] ?? 'Giáo viên', 'avatar-xl', 'mx-auto') ?>
                <h2 class="mt-3 mb-1" id="account-name" style="font-size:1.05rem;"><?= e($instructor['name'] ?? '') ?></h2>
                <p class="text-muted-2 text-small mb-2">Giáo viên · Mã <?= e($instructorCode) ?></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <?= badge(($instructor['status'] ?? '') === 'dang_lam' ? 'Đang hoạt động' : 'Ngừng hoạt động', ($instructor['status'] ?? '') === 'dang_lam' ? 'success' : 'secondary') ?>
                    <span id="account-specialty-badge"><?= badge($instructor['specialty'] ?? '—', 'primary') ?></span>
                </div>
                <button class="btn btn-outline-primary btn-sm" type="button" data-toast="Tính năng tải ảnh sẽ được nối API." data-toast-title="Đổi ảnh đại diện">
                    <?= icon('camera') ?> Đổi ảnh đại diện
                </button>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header"><h2>Thông tin giảng dạy</h2></div>
            <div class="app-card-body">
                <div class="kv-list">
                    <div class="kv"><span>Mã giáo viên</span><span><?= e($instructorCode) ?></span></div>
                    <div class="kv"><span>Chuyên môn</span><span id="account-specialty-value"><?= e($instructor['specialty'] ?? '—') ?></span></div>
                    <div class="kv"><span>Số lớp phụ trách</span><span><?= e((string) ($instructor['classes'] ?? 0)) ?></span></div>
                    <div class="kv"><span>Tổng học viên</span><span><?= e((string) ($instructor['students'] ?? 0)) ?></span></div>
                </div>
                <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(url('/instructor/classes')) ?>"><?= icon('journal-bookmark') ?> Lớp đang dạy</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="app-card mb-3">
            <div class="app-card-header">
                <div>
                    <h2>Hồ sơ giáo viên</h2>
                    <p class="sub">Thông tin liên hệ và chuyên môn</p>
                </div>
            </div>
            <div class="app-card-body">
                <form id="profile-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="ins-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="ins-name" name="ho_ten" type="text" value="<?= e($instructor['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-phone">Số điện thoại <span class="req">*</span></label>
                            <input class="form-control" id="ins-phone" name="sdt" type="tel" value="<?= e($instructor['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-email">Email <span class="req">*</span></label>
                            <input class="form-control" id="ins-email" name="email" type="email" value="<?= e($instructor['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <span class="form-label d-block">Chuyên môn</span>
                            <details class="specialty-picker" id="ins-specialty-picker">
                                <summary id="ins-specialty-summary"><?= e($instructor['specialty'] ?? '—') ?></summary>
                                <div class="specialty-picker-options" role="group" aria-label="Chọn chuyên môn">
                                    <?php foreach ($specialties as $specialty): ?>
                                        <label class="form-check specialty-picker-option">
                                            <input class="form-check-input" type="checkbox" name="chuyen_mon_ids[]" value="<?= (int) $specialty['id'] ?>" <?= in_array((int) $specialty['id'], $instructor['specialty_ids'] ?? [], true) ? 'checked' : '' ?>>
                                            <span class="form-check-label"><?= e($specialty['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ins-desc">Giới thiệu</label>
                            <textarea class="form-control" id="ins-desc" name="gioi_thieu" rows="3"><?= e($instructor['gioi_thieu'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-account">Tên đăng nhập</label>
                            <input class="form-control" id="ins-account" type="text" value="thuha" disabled>
                            <div class="form-text">Liên hệ quản trị viên để thay đổi.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-last-login">Đăng nhập cuối</label>
                            <input class="form-control" id="ins-last-login" type="text" value="<?= e(date('d/m/Y H:i')) ?>" disabled>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary" type="submit"><?= icon('check-lg') ?> Lưu thay đổi</button>
                        <button class="btn btn-ghost" type="reset">Hủy</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Đổi mật khẩu</h2>
                    <p class="sub">Bảo vệ tài khoản giảng dạy của bạn</p>
                </div>
                <span class="stat-icon ms-auto"><?= icon('shield-lock') ?></span>
            </div>
            <div class="app-card-body">
                <form id="password-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="ins-pw-current">Mật khẩu hiện tại <span class="req">*</span></label>
                            <input class="form-control" id="ins-pw-current" name="mat_khau_cu" type="password" required autocomplete="current-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ins-pw-new">Mật khẩu mới <span class="req">*</span></label>
                            <input class="form-control" id="ins-pw-new" name="mat_khau_moi" type="password" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ins-pw-confirm">Nhập lại mật khẩu <span class="req">*</span></label>
                            <input class="form-control" id="ins-pw-confirm" name="mat_khau_xac_nhan" type="password" required minlength="6" autocomplete="new-password">
                        </div>
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

<script src="<?= e(url('/common/assets/js/instructor/account.js')) ?>?v=3"></script>
<?php require $partials . '/foot.php'; ?>