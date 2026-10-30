<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var string $activeMenu */
/** @var string $appRole */
/** @var array<int,array{0:string,1?:string}> $breadcrumbs */
/** @var string $pageActions */
/** @var array<int,string> $pageScripts */

require_once __DIR__ . '/../components/ui.php';
require_once __DIR__ . '/../data/data.php';
require_once __DIR__ . '/../partials/menu.php';

$user = app_user($appRole);
$notifications = app_notifications($appRole);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle) ?> · FitZone</title>
    <link rel="stylesheet" href="<?= e(url('/assets/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/pages.css')) ?>">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="app-main">
        <?php require __DIR__ . '/../partials/topbar.php'; ?>

        <main class="app-content" id="main-content">
            <?php require __DIR__ . '/../partials/flash.php'; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script>
    window.APP_BASE = <?= json_encode(base_url(), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= e(url('/assets/js/api.js')) ?>?v=5"></script>
<script src="<?= e(url('/assets/js/ui.js')) ?>?v=6"></script>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= e(url('/assets/js/pages/' . $script . '?v=6')) ?>"></script>
<?php endforeach; ?>
</body>
</html>
