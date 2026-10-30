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
<div class="public-shell">
    <header class="public-header">
        <div class="public-header-inner">
            <a class="public-brand" href="<?= e(url('/')) ?>">
                <span class="brand-mark"><?= icon('activity') ?></span>
                FitZone
            </a>
            <nav class="public-nav" aria-label="Điều hướng công khai">
                <a href="<?= e(url('/#features')) ?>">Về chúng tôi</a>
                <a href="<?= e(url('/#packages')) ?>">Gói tập</a>
                <a href="<?= e(url('/#classes')) ?>">Lớp học</a>
                <a href="<?= e(url('/#contact')) ?>">Liên hệ</a>
            </nav>
            <div class="header-actions">
                <a class="btn btn-ghost btn-sm" href="<?= e(url('/login')) ?>">Đăng nhập</a>
                <a class="btn btn-primary btn-sm" href="<?= e(url('/register')) ?>">Đăng ký ngay</a>
            </div>
        </div>
    </header>

    <main id="main-content">
        <?= $content ?>
    </main>

    <footer class="public-footer" id="contact">
        <div class="public-footer-inner">
            <div>
                <div class="brand"><span class="brand-mark" style="width:32px;height:32px;"><?= icon('activity') ?></span> FitZone</div>
                <p style="font-size:.82rem;max-width:300px;">Phòng tập hiện đại với lớp học đa dạng, giáo viên chuyên nghiệp và trải nghiệm tập luyện được cá nhân hóa.</p>
            </div>
            <div>
                <h4>Khám phá</h4>
                <a href="<?= e(url('/#features')) ?>">Về chúng tôi</a>
                <a href="<?= e(url('/#packages')) ?>">Gói tập</a>
                <a href="<?= e(url('/#classes')) ?>">Lớp học</a>
            </div>
            <div>
                <h4>Hỗ trợ</h4>
                <a href="<?= e(url('/login')) ?>">Đăng nhập</a>
                <a href="<?= e(url('/register')) ?>">Đăng ký hội viên</a>
                <a href="<?= e(url('/#contact')) ?>">Liên hệ</a>
            </div>
            <div>
                <h4>Liên hệ</h4>
                <a href="tel:0123456789"><?= icon('telephone') ?> 0123 456 789</a>
                <a href="mailto:hello@fitzone.vn"><?= icon('envelope') ?> hello@fitzone.vn</a>
                <a href="#"><?= icon('geo-alt') ?> 25 Lý Thường Kiệt, Hà Nội</a>
            </div>
        </div>
        <div class="copy">© <?= date('Y') ?> FitZone. Hệ thống quản lý phòng tập &amp; lớp học.</div>
    </footer>
</div>

<script src="<?= e(url('/assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(url('/assets/js/api.js')) ?>?v=5"></script>
<script src="<?= e(url('/assets/js/ui.js')) ?>?v=5"></script>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= e(url('/assets/js/pages/' . $script . '?v=5')) ?>"></script>
<?php endforeach; ?>
</body>
</html>
