<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var array<int,string> $pageScripts */
require_once __DIR__ . '/../components/ui.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> · FitZone</title>
    <link rel="stylesheet" href="<?= e(url('/assets/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/pages.css')) ?>">
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
        <?= $content ?>
    </main>
</div>

<script src="<?= e(url('/assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(url('/assets/js/api.js')) ?>?v=4"></script>
<script src="<?= e(url('/assets/js/ui.js')) ?>"></script>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= e(url('/assets/js/pages/' . $script . '?v=2')) ?>"></script>
<?php endforeach; ?>
</body>
</html>
