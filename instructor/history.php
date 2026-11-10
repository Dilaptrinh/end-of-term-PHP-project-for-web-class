<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';

$appRole = 'instructor';
$activeMenu = 'history';
$pageTitle = 'Lịch sử đã dạy';
$pageScripts = [];

$today = new DateTimeImmutable('today');
$instructorName = data_current_instructor_name();
$history = [];
foreach (data_classes() as $class) {
    if ($class['instructor'] !== $instructorName) {
        continue;
    }

    $endDate = DateTimeImmutable::createFromFormat('!d/m/Y', $class['to']);
    if (!$endDate || $endDate >= $today) {
        continue;
    }

    $class['end_date'] = $endDate;
    $history[] = $class;
}

usort($history, static function (array $first, array $second): int {
    return $second['end_date'] <=> $first['end_date'];
});

$totalStudents = 0;
foreach ($history as $class) {
    $totalStudents += (int) $class['enrolled'];
}

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
                    <h1>Lịch sử đã dạy</h1>
                    <p>Các lớp đã kết thúc mà bạn từng được phân công.</p>
                </div>
                <div class="page-head-actions">
                    <a class="btn btn-ghost" href="<?= e(url('/instructor/schedule')) ?>"><?= icon('calendar-week') ?> Lịch dạy hiện tại</a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-4">
                    <div class="stat-card">
                        <div class="stat-label">Số lớp đã kết thúc</div>
                        <div class="stat-value"><?= count($history) ?></div>
                        <div class="stat-foot">Theo phân công giảng dạy</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="stat-card">
                        <div class="stat-label">Tổng lượt học viên</div>
                        <div class="stat-value"><?= $totalStudents ?></div>
                        <div class="stat-foot">Tổng số đăng ký trong các lớp</div>
                    </div>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-header">
                    <h2>Các lớp đã dạy</h2>
                    <span class="badge-soft ms-auto"><?= count($history) ?> lớp</span>
                </div>
                <div class="app-table-wrap">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Mã lớp</th>
                                <th>Lớp học</th>
                                <th>Lịch học</th>
                                <th>Phòng</th>
                                <th>Sĩ số</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$history): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted-2 py-4">Chưa có lớp nào đã kết thúc.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history as $class): ?>
                                    <tr>
                                        <td class="cell-strong">LH-<?= (int) $class['id'] ?></td>
                                        <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                                        <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                                        <td><?= e($class['room']) ?></td>
                                        <td><?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></td>
                                        <td class="text-muted-2 text-tiny"><?= e($class['from']) ?><br><?= e($class['to']) ?></td>
                                        <td><?= status_badge('da_dong') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<?php require $partials . '/foot.php'; ?>
