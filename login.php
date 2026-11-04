<?php
require __DIR__ . '/common/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $raw = file_get_contents('php://input') ?: '';
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $username = trim((string) ($input['ten_dang_nhap'] ?? $input['username'] ?? ''));
    $password = (string) ($input['mat_khau'] ?? $input['password'] ?? '');

    $stmt = common_db()->prepare('SELECT id, mat_khau, trang_thai, vai_tro FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if (!$account || (int) $account['trang_thai'] !== 1 || !password_verify($password, (string) $account['mat_khau'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['tai_khoan_id'] = (int) $account['id'];

    echo json_encode(['success' => true, 'data' => ['vai_tro' => $account['vai_tro']], 'message' => 'Đăng nhập thành công.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pageTitle = 'Đăng nhập';
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
        <div class="auth-card">
    <h1>Chào mừng trở lại</h1>
    <p class="auth-sub">Đăng nhập một cổng cho hội viên, giáo viên và quản trị viên.</p>

    <form id="login-form" class="mt-3" novalidate
          data-login-url="<?= e(url('/login.php')) ?>"
          data-redirect-admin="<?= e(url('/admin')) ?>"
          data-redirect-instructor="<?= e(url('/instructor')) ?>"
          data-redirect-customer="<?= e(url('/customer')) ?>">
        <div class="mb-3">
            <label class="form-label" for="login-username">Tên đăng nhập <span class="req">*</span></label>
            <input class="form-control" id="login-username" name="username" type="text" placeholder="Nhập tên đăng nhập" required autocomplete="username">
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label" for="login-password">Mật khẩu <span class="req">*</span></label>
                <a class="text-tiny fw-700" href="#" data-toast="Tính năng quên mật khẩu sẽ được nối API." data-toast-title="Quên mật khẩu">Quên mật khẩu?</a>
            </div>
            <div class="input-group">
                <input class="form-control" id="login-password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
                <button class="btn btn-outline-primary" type="button" id="toggle-password" aria-label="Hiện mật khẩu">
                    <?= icon('eye') ?>
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="form-check">
                <input class="form-check-input" id="remember" name="remember" type="checkbox" checked>
                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <a class="text-tiny" href="<?= e(url('/register')) ?>">Chưa có tài khoản?</a>
        </div>

        <button class="btn btn-primary w-100 btn-lg" type="submit" id="login-submit">Đăng nhập</button>
    </form>

    <p class="text-center text-tiny text-muted-2 mt-3 mb-0">
        Bằng việc đăng nhập, bạn đồng ý với <a href="#">Điều khoản sử dụng</a> của FitZone.
    </p>
</div>
    </main>
</div>

<script src="<?= e(url('/common/assets/js/auth/login.js')) ?>?v=4"></script>
<?php require __DIR__ . '/common/partials/foot.php'; ?>

