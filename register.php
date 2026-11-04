<?php
require __DIR__ . '/common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $username = trim((string) ($input['ten_dang_nhap'] ?? ''));
    $password = (string) ($input['mat_khau'] ?? '');
    $confirm = (string) ($input['mat_khau_xac_nhan'] ?? '');
    $name = trim((string) ($input['ho_ten'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $phone = trim((string) ($input['sdt'] ?? ''));
    $gender = (string) ($input['gioi_tinh'] ?? 'khac');
    $birth = trim((string) ($input['ngay_sinh'] ?? ''));
    $address = trim((string) ($input['dia_chi'] ?? ''));

    $errors = [];
    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors['ten_dang_nhap'] = 'Tên đăng nhập 3–50 ký tự, chỉ chữ/số/gạch dưới.';
    }
    if (strlen($password) < 6) {
        $errors['mat_khau'] = 'Mật khẩu tối thiểu 6 ký tự.';
    }
    if ($password !== $confirm) {
        $errors['mat_khau_xac_nhan'] = 'Mật khẩu xác nhận không khớp.';
    }
    if (mb_strlen($name) < 2) {
        $errors['ho_ten'] = 'Họ tên không hợp lệ.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    if ($phone === '') {
        $errors['sdt'] = 'Số điện thoại không hợp lệ.';
    }
    if ($errors) {
        json_response(['success' => false, 'message' => 'Thông tin đăng ký không hợp lệ.', 'errors' => $errors], 422);
    }

    $db = common_db();

    $stmt = $db->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($exists) {
        json_response(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.', 'errors' => ['ten_dang_nhap' => 'Tên đăng nhập đã tồn tại.']], 422);
    }

    $stmt = $db->prepare('SELECT id FROM hoi_vien WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($exists) {
        json_response(['success' => false, 'message' => 'Email đã được sử dụng.', 'errors' => ['email' => 'Email đã được sử dụng.']], 422);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, "customer", 1)');
    $stmt->bind_param('sss', $username, $hash, $name);
    $stmt->execute();
    $accountId = $stmt->insert_id;
    $stmt->close();

    $birth = $birth !== '' ? $birth : null;
    $address = $address !== '' ? $address : null;

    $stmt = $db->prepare('INSERT INTO hoi_vien (tai_khoan_id, ho_ten, sdt, email, gioi_tinh, ngay_sinh, dia_chi, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, "hoat_dong")');
    $stmt->bind_param('issssss', $accountId, $name, $phone, $email, $gender, $birth, $address);
    $stmt->execute();
    $stmt->close();

    json_response(['success' => true, 'message' => 'Tạo tài khoản thành công.'], 201);
}

$pageTitle = 'Đăng ký hội viên';
$pageScripts = [];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · FitZone</title>
    <link rel="stylesheet" href="<?= e(url('/common/assets/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/common/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/common/assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/common/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/common/assets/css/pages.css')) ?>">
</head>
<body>
<div class="auth-shell">
    <aside class="auth-aside">
        <a class="auth-brand" href="<?= e(url('/')) ?>">
            <span class="brand-mark"><?= icon('activity') ?></span>
            <span>FitZone <small>Gym management</small></span>
        </a>

        <div class="auth-aside-content">
            <p class="auth-kicker">CỔNG NỘI BỘ · FITZONE</p>
            <h2>Quản lý phòng tập<br>rõ ràng, ở một nơi.</h2>
            <p>Đăng nhập để tiếp tục công việc theo đúng vai trò của bạn.</p>

            <ul class="auth-feature-list">
                <li><span class="check"><?= icon('check-lg') ?></span> Đăng nhập một lần, tự động chuyển đúng khu vực theo vai trò</li>
                <li><span class="check"><?= icon('check-lg') ?></span> Lịch tập và đăng ký lớp được cập nhật rõ ràng</li>
                <li><span class="check"><?= icon('check-lg') ?></span> Báo cáo doanh thu và tình trạng lớp cho quản trị viên</li>
            </ul>
        </div>

        <div class="auth-aside-footer">
            © <?= date('Y') ?> FitZone · Hệ thống quản lý phòng tập &amp; lớp học
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card auth-card-wide">
    <h1>Tạo tài khoản hội viên</h1>
    <p class="auth-sub">Điền thông tin bên dưới để bắt đầu tập luyện cùng FitZone.</p>

    <form id="register-form">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="reg-username">Tên đăng nhập <span class="req">*</span></label>
                <input class="form-control" id="reg-username" name="ten_dang_nhap" type="text" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+" placeholder="vd: nguyenvana" autocomplete="username">
                <div class="form-text">Chỉ chữ, số, dấu gạch dưới; tối thiểu 3 ký tự.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-name">Họ và tên <span class="req">*</span></label>
                <input class="form-control" id="reg-name" name="ho_ten" type="text" required placeholder="Nguyễn Văn A">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-phone">Số điện thoại <span class="req">*</span></label>
                <input class="form-control" id="reg-phone" name="sdt" type="tel" required placeholder="0901 234 567">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-email">Email <span class="req">*</span></label>
                <input class="form-control" id="reg-email" name="email" type="email" required placeholder="ban@email.com">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-birth">Ngày sinh</label>
                <input class="form-control" id="reg-birth" name="ngay_sinh" type="date">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-gender">Giới tính</label>
                <select class="form-select" id="reg-gender" name="gioi_tinh">
                    <option value="khac">Khác</option>
                    <option value="nam">Nam</option>
                    <option value="nu">Nữ</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="reg-address">Địa chỉ</label>
                <input class="form-control" id="reg-address" name="dia_chi" type="text" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-password">Mật khẩu <span class="req">*</span></label>
                <input class="form-control" id="reg-password" name="mat_khau" type="password" required minlength="6" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
                <div class="form-text">Dùng ít nhất 6 ký tự, nên có chữ và số.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-confirm">Xác nhận mật khẩu <span class="req">*</span></label>
                <input class="form-control" id="reg-confirm" name="mat_khau_xac_nhan" type="password" required minlength="6" placeholder="Nhập lại mật khẩu" autocomplete="new-password">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" id="reg-terms" name="dieu_khoan" type="checkbox" required>
                    <label class="form-check-label" for="reg-terms">
                        Tôi đồng ý với <a href="#">điều khoản sử dụng</a> và <a href="#">chính sách bảo mật</a> của FitZone.
                    </label>
                </div>
            </div>
        </div>

        <button class="btn btn-primary w-100 btn-lg mt-3" type="submit">Tạo tài khoản</button>
    </form>

    <p class="text-center text-small text-muted-2 mt-3 mb-0">
        Đã có tài khoản? <a class="fw-700" href="<?= e(url('/login')) ?>">Đăng nhập</a>
    </p>
</div>
    </main>
</div>

<script src="<?= e(url('/common/assets/js/auth/register.js')) ?>?v=1"></script>
<?php require __DIR__ . '/common/partials/foot.php'; ?>

