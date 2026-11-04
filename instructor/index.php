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
    static fn (array $class): bool => (int) $class['instructor_id'] === (int) ($instructor['id'] ?? 0)
));
$requests = array_values(array_filter(
    data_availability(),
    static fn (array $request): bool => $request['instructor'] === ($instructor['name'] ?? '')
));
$pending = count(array_filter($requests, static fn (array $request): bool => $request['status'] === 'cho_duyet'));
$approved = count(array_filter($requests, static fn (array $request): bool => $request['status'] === 'da_duyet'));
$students = (int) ($instructor['students'] ?? 0);

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
                <div class="page-head-actions">
                    <a class="btn btn-primary" href="<?= e(url('/instructor/availability')) ?>"><?= icon('calendar-plus') ?> Đăng ký lịch dạy</a>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Lớp đang dạy</div><div class="stat-value"><?= count($classes) ?></div><div class="stat-foot">Theo phân công hiện tại</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Học viên</div><div class="stat-value"><?= $students ?></div><div class="stat-foot">Đang đăng ký lớp</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Lịch đã duyệt</div><div class="stat-value text-success"><?= $approved ?></div><div class="stat-foot">Khung giờ khả dụng</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Chờ duyệt</div><div class="stat-value" style="color:var(--warning);"><?= $pending ?></div><div class="stat-foot">Yêu cầu đang chờ admin</div></div></div>
            </div>
            <div class="app-card">
                <div class="app-card-header">
                    <div><h2>Lớp đang phụ trách</h2><p class="sub">Dữ liệu lấy trực tiếp từ cơ sở dữ liệu</p></div>
                    <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/instructor/classes')) ?>">Xem tất cả <?= icon('arrow-right') ?></a>
                </div>
                <div class="app-table-wrap">
                    <table class="app-table">
                        <thead><tr><th>Lớp học</th><th>Lịch</th><th>Phòng</th><th>Sĩ số</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                                <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                                <td><?= e($class['room']) ?></td>
                                <td><?= e($class['enrolled'] . '/' . $class['capacity']) ?></td>
                                <td><?= status_badge($class['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$classes): ?><tr><td colspan="5" class="text-muted-2">Chưa có lớp được phân công.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<?php require $partials . '/foot.php'; ?>
