<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';
$appRole = 'instructor';
$activeMenu = 'dashboard';
$pageTitle = 'Tổng quan giáo viên';
$pageScripts = [];

$instructor = data_current_instructor();
$classes = array_values(array_filter(
    data_classes(),
    function (array $class) use ($instructor): bool {
        $id_giao_vien_cua_lop = (int) $class['giao_vien_id'];
        $id_giao_vien_dang_dang_nhap = (int) ($instructor['id'] ?? 0);

        if ($id_giao_vien_cua_lop === $id_giao_vien_dang_dang_nhap) {
            return true;
        } else {
            return false;
        }
    }
));
$approved = count(array_filter($classes, static fn (array $class): bool => $class['status'] === 'dang_mo'));

$students = (int) ($instructor['students'] ?? 0);
$totalEnrollments = array_sum(array_map(static fn (array $class): int => (int) $class['enrolled'], $classes));
$totalCapacity = array_sum(array_map(static fn (array $class): int => (int) $class['capacity'], $classes));
$fillRate = $totalCapacity > 0 ? (int) round($totalEnrollments / $totalCapacity * 100) : 0;

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
                    <h1>Xin chào, <?= e($instructor['name'] ?? 'Giáo viên') ?></h1>
                    <p>Tổng quan lớp học, học viên và lịch dạy của bạn.</p>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Lớp đang dạy</div><div class="stat-value"><?= count($classes) ?></div><div class="stat-foot">Theo phân công hiện tại</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Học viên đang học</div><div class="stat-value"><?= $students ?></div><div class="stat-foot">Số người riêng biệt</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Lớp đã mở</div><div class="stat-value text-success"><?= $approved ?></div><div class="stat-foot">Học viên có thể đăng ký</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Tỷ lệ lấp đầy</div><div class="stat-value text-success"><?= $fillRate ?>%</div><div class="stat-foot"><?= $totalEnrollments ?>/<?= $totalCapacity ?> chỗ đã đăng ký</div></div></div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<?php require $partials . '/foot.php'; ?>