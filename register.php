<?php
require __DIR__ . '/common/bootstrap.php';

$current = common_user();
if ($current !== null) {
    redirect('/' . $current['vai_tro']);
}

$errors = [];
$old = [
    'ten_dang_nhap' => '',
    'ho_ten' => '',
    'sdt' => '',
    'email' => '',
    'ngay_sinh' => '',
    'gioi_tinh' => 'khac',
    'dia_chi' => '',
    'dieu_khoan' => false,
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = [
        'ten_dang_nhap' => trim((string) ($_POST['ten_dang_nhap'] ?? '')),
        'ho_ten' => trim((string) ($_POST['ho_ten'] ?? '')),
        'sdt' => trim((string) ($_POST['sdt'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'ngay_sinh' => trim((string) ($_POST['ngay_sinh'] ?? '')),
        'gioi_tinh' => (string) ($_POST['gioi_tinh'] ?? 'khac'),
        'dia_chi' => trim((string) ($_POST['dia_chi'] ?? '')),
        'dieu_khoan' => !empty($_POST['dieu_khoan']),
    ];

    $password = (string) ($_POST['mat_khau'] ?? '');
    $confirm = (string) ($_POST['mat_khau_xac_nhan'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $old['ten_dang_nhap'])) {
        $errors['ten_dang_nhap'] = 'Tên đăng nhập 3–50 ký tự, chỉ chữ/số/gạch dưới.';
    }
    if (strlen($password) < 6) {
        $errors['mat_khau'] = 'Mật khẩu tối thiểu 6 ký tự.';
    }
    if ($password !== $confirm) {
        $errors['mat_khau_xac_nhan'] = 'Mật khẩu xác nhận không khớp.';
    }
    if (mb_strlen($old['ho_ten']) < 2) {
        $errors['ho_ten'] = 'Họ tên không hợp lệ.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    if ($old['sdt'] === '') {
        $errors['sdt'] = 'Số điện thoại không hợp lệ.';
    }
    if (!in_array($old['gioi_tinh'], ['nam', 'nu', 'khac'], true)) {
        $errors['gioi_tinh'] = 'Giới tính không hợp lệ.';
    }
    if (!$old['dieu_khoan']) {
        $errors['dieu_khoan'] = 'Bạn cần đồng ý với điều khoản sử dụng.';
    }

    if (!$errors) {
        $db = common_db();

        $stmt = $db->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
        $stmt->bind_param('s', $old['ten_dang_nhap']);
        $stmt->execute();
        $usernameExists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($usernameExists) {
            $errors['ten_dang_nhap'] = 'Tên đăng nhập đã tồn tại.';
        }

        $stmt = $db->prepare('SELECT id FROM hoi_vien WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $emailExists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($emailExists) {
            $errors['email'] = 'Email đã được sử dụng.';
        }

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $birth = $old['ngay_sinh'] !== '' ? $old['ngay_sinh'] : null;
            $address = $old['dia_chi'] !== '' ? $old['dia_chi'] : null;

            try {
                $db->begin_transaction();

                $stmt = $db->prepare('INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, "customer", 1)');
                $stmt->bind_param('sss', $old['ten_dang_nhap'], $hash, $old['ho_ten']);
                $stmt->execute();
                $accountId = $stmt->insert_id;
                $stmt->close();

                $stmt = $db->prepare('INSERT INTO hoi_vien (tai_khoan_id, ho_ten, sdt, email, gioi_tinh, ngay_sinh, dia_chi, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, "hoat_dong")');
                $stmt->bind_param('issssss', $accountId, $old['ho_ten'], $old['sdt'], $old['email'], $old['gioi_tinh'], $birth, $address);
                $stmt->execute();
                $stmt->close();

                $db->commit();

                $_SESSION['_flash'] = [
                    'type' => 'success',
                    'message' => 'Tạo tài khoản thành công. Vui lòng đăng nhập.',
                ];
                redirect('/login.php');
            } catch (mysqli_sql_exception $e) {
                $db->rollback();
                $errors['general'] = 'Không thể tạo tài khoản. Tên đăng nhập hoặc email có thể đã tồn tại.';
            }
        }
    }
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

    <?php if ($errors): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
            <?= icon('exclamation-octagon') ?>
            <div><?= e($errors['general'] ?? 'Vui lòng kiểm tra lại thông tin đăng ký.') ?></div>
        </div>
    <?php endif; ?>

    <form id="register-form" method="post" action="" novalidate>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="reg-username">Tên đăng nhập <span class="req">*</span></label>
                <input class="form-control<?= isset($errors['ten_dang_nhap']) ? ' is-invalid' : '' ?>" id="reg-username" name="ten_dang_nhap" type="text" value="<?= e($old['ten_dang_nhap']) ?>" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+" placeholder="vd: nguyenvana" autocomplete="username">
                <?php if (isset($errors['ten_dang_nhap'])): ?><div class="invalid-feedback d-block"><?= e($errors['ten_dang_nhap']) ?></div><?php endif; ?>
                <div class="form-text">Chỉ chữ, số, dấu gạch dưới; tối thiểu 3 ký tự.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-name">Họ và tên <span class="req">*</span></label>
                <input class="form-control<?= isset($errors['ho_ten']) ? ' is-invalid' : '' ?>" id="reg-name" name="ho_ten" type="text" value="<?= e($old['ho_ten']) ?>" required placeholder="Nguyễn Văn A">
                <?php if (isset($errors['ho_ten'])): ?><div class="invalid-feedback d-block"><?= e($errors['ho_ten']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-phone">Số điện thoại <span class="req">*</span></label>
                <input class="form-control<?= isset($errors['sdt']) ? ' is-invalid' : '' ?>" id="reg-phone" name="sdt" type="tel" value="<?= e($old['sdt']) ?>" required placeholder="0901 234 567">
                <?php if (isset($errors['sdt'])): ?><div class="invalid-feedback d-block"><?= e($errors['sdt']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-email">Email <span class="req">*</span></label>
                <input class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="reg-email" name="email" type="email" value="<?= e($old['email']) ?>" required placeholder="ban@email.com">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback d-block"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-birth">Ngày sinh</label>
                <input class="form-control" id="reg-birth" name="ngay_sinh" type="date" value="<?= e($old['ngay_sinh']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-gender">Giới tính</label>
                <select class="form-select<?= isset($errors['gioi_tinh']) ? ' is-invalid' : '' ?>" id="reg-gender" name="gioi_tinh">
                    <option value="khac" <?= $old['gioi_tinh'] === 'khac' ? 'selected' : '' ?>>Khác</option>
                    <option value="nam" <?= $old['gioi_tinh'] === 'nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="nu" <?= $old['gioi_tinh'] === 'nu' ? 'selected' : '' ?>>Nữ</option>
                </select>
                <?php if (isset($errors['gioi_tinh'])): ?><div class="invalid-feedback d-block"><?= e($errors['gioi_tinh']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
                <label class="form-label" for="reg-address">Địa chỉ</label>
                <input class="form-control" id="reg-address" name="dia_chi" type="text" value="<?= e($old['dia_chi']) ?>" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-password">Mật khẩu <span class="req">*</span></label>
                <input class="form-control<?= isset($errors['mat_khau']) ? ' is-invalid' : '' ?>" id="reg-password" name="mat_khau" type="password" required minlength="6" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
                <?php if (isset($errors['mat_khau'])): ?><div class="invalid-feedback d-block"><?= e($errors['mat_khau']) ?></div><?php endif; ?>
                <div class="form-text">Dùng ít nhất 6 ký tự, nên có chữ và số.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reg-confirm">Xác nhận mật khẩu <span class="req">*</span></label>
                <input class="form-control<?= isset($errors['mat_khau_xac_nhan']) ? ' is-invalid' : '' ?>" id="reg-confirm" name="mat_khau_xac_nhan" type="password" required minlength="6" placeholder="Nhập lại mật khẩu" autocomplete="new-password">
                <?php if (isset($errors['mat_khau_xac_nhan'])): ?><div class="invalid-feedback d-block"><?= e($errors['mat_khau_xac_nhan']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input<?= isset($errors['dieu_khoan']) ? ' is-invalid' : '' ?>" id="reg-terms" name="dieu_khoan" type="checkbox" value="1" <?= $old['dieu_khoan'] ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="reg-terms">
                        Tôi đồng ý với <a href="#">điều khoản sử dụng</a> và <a href="#">chính sách bảo mật</a> của FitZone.
                    </label>
                    <?php if (isset($errors['dieu_khoan'])): ?><div class="invalid-feedback d-block"><?= e($errors['dieu_khoan']) ?></div><?php endif; ?>
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

<?php require __DIR__ . '/common/partials/foot.php'; ?>
