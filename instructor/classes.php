<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('instructor');

// Render danh sách học viên của lớp bằng PHP cho modal.
function instructor_students_html(array $students): string
{
    if ($students === []) {
        return '<tr><td colspan="4" class="text-muted-2">Lớp chưa có học viên.</td></tr>';
    }

    ob_start();
    foreach ($students as $student):
        ?>
        <tr>
            <td>
                <div class="user-inline">
                    <span>
                        <span class="d-block user-name"><?= e($student['name']) ?></span>
                        <span class="d-block user-sub"><?= e(sprintf('HV-%04d', (int) $student['member_id'])) ?></span>
                    </span>
                </div>
            </td>
            <td class="text-muted-2"><?= e($student['phone'] ?: '—') ?></td>
            <td class="text-muted-2"><?= e($student['email'] ?: '—') ?></td>
            <td class="text-muted-2"><?= e($student['registered'] ?: '—') ?></td>
        </tr>
        <?php
    endforeach;
    return (string) ob_get_clean();
}

// API AJAX: trả danh sách học viên của một lớp (chỉ lớp mình dạy).
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && ($_GET['action'] ?? '') === 'members') {
    require_api_role('instructor');

    $classId = (int) ($_GET['id'] ?? 0);
    $instructorId = (int) (data_current_instructor()['id'] ?? 0);

    // Kiểm tra lớp có thuộc giáo viên đang đăng nhập không.
    $owned = false;
    foreach (data_classes() as $class) {
        if ((int) $class['id'] === $classId && (int) $class['giao_vien_id'] === $instructorId) {
            $owned = true;
            break;
        }
    }

    if ($classId <= 0 || !$owned) {
        json_response(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
    }

    json_response([
        'success' => true,
        'html' => instructor_students_html(data_students_for_class($classId)),
    ]);
}

$menu = require __DIR__ . '/../common/menus/instructor.php';

$appRole = 'instructor';
$activeMenu = 'classes';
$pageTitle = 'Lớp tôi đang dạy';
$pageScripts = [];

$instructorId = (int) (data_current_instructor()['id'] ?? 0);
$classFilter = (string) ($_GET['status'] ?? 'current');
if (!in_array($classFilter, ['current', 'completed'], true)) {
    $classFilter = 'current';
}

$classes = array_values(array_filter(
    data_classes(),
    static fn (array $class): bool => $instructorId > 0
        && (int) $class['giao_vien_id'] === $instructorId
));
$today = new DateTimeImmutable('today');
$classes = array_values(array_filter($classes, static function (array $class) use ($classFilter, $today): bool {
    $endDate = DateTimeImmutable::createFromFormat('!d/m/Y', $class['to']);
    $isCompleted = $endDate && $endDate < $today;
    return $classFilter === 'completed' ? $isCompleted : !$isCompleted;
}));
foreach ($classes as &$class) {
    if ($class['status'] === 'dang_mo') {
        $startDate = DateTimeImmutable::createFromFormat('!d/m/Y', $class['from']);
        if ($startDate && $startDate > $today) {
            $class['status'] = 'sap_mo';
        }
    }
}
unset($class);

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
        <h1>Lớp của tôi</h1>
        <p><?= count($classes) ?> lớp được phân công · Tổng <?= array_sum(array_column($classes, 'enrolled')) ?> học viên.</p>
    </div>
</div>

<form class="filter-bar mb-3" method="get" action="<?= e(url('/instructor/classes')) ?>">
    <div class="filter-field">
        <label class="form-label" for="class-status">Trạng thái lớp</label>
        <select class="form-select" id="class-status" name="status">
            <option value="current" <?= $classFilter === 'current' ? 'selected' : '' ?>>Đang dạy</option>
            <option value="completed" <?= $classFilter === 'completed' ? 'selected' : '' ?>>Đã dạy xong</option>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Lọc</button>
    </div>
</form>

<div class="row g-3">
    <?php foreach ($classes as $class):
        $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
        $tone = $percent >= 90 ? 'amber' : '';
    ?>
        <div class="col-md-6 col-xl-4">
            <article class="class-card">
                <div class="class-card-top">
                    <div class="day-chip">
                        <strong><?= e(weekday_short($class['weekday'])) ?></strong>
                        <span><?= e($class['start']) ?></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-start gap-2">
                            <h3 class="flex-grow-1"><?= e($class['name']) ?></h3>
                            <?= status_badge($class['status']) ?>
                        </div>
                        <div class="teacher"><?= icon('bar-chart') ?> <?= e($class['level']) ?> · <?= icon('geo-alt') ?> <?= e($class['room']) ?></div>
                    </div>
                </div>
                <div class="class-card-body">
                    <div class="class-meta">
                        <div class="item"><?= icon('calendar3') ?> <?= e(weekday($class['weekday'])) ?></div>
                        <div class="item"><?= icon('clock') ?> <?= e($class['start'] . ' – ' . $class['end']) ?></div>
                        <div class="item"><?= icon('calendar-range') ?> <?= e($class['from']) ?></div>
                        <div class="item"><?= icon('calendar-check') ?> <?= e($class['to']) ?></div>
                    </div>
                    <div class="class-capacity">
                        <span>Sĩ số <?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></span>
                        <span><?= $percent ?>%</span>
                    </div>
                    <?= progress_bar($percent, $tone) ?>
                    <p class="text-muted-2 text-tiny mt-2 mb-0"><?= e($class['desc']) ?></p>
                </div>
                <div class="class-card-foot">
                    <span class="availability <?= $class['capacity'] - $class['enrolled'] <= 2 ? 'text-warning' : 'text-success' ?>">
                        Còn <?= e((string) ($class['capacity'] - $class['enrolled'])) ?> chỗ
                    </span>
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#classStudentsModal"
                            data-class-id="<?= (int) $class['id'] ?>"
                            data-class-name="<?= e($class['name']) ?>">
                        <?= icon('people') ?> Học viên
                    </button>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
<?php if (!$classes): ?>
    <div class="app-card">
        <div class="app-card-body text-center text-muted-2 py-4">
            <?= $classFilter === 'completed' ? 'Chưa có lớp đã dạy xong.' : 'Chưa có lớp đang dạy.' ?>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="classStudentsModal" tabindex="-1" aria-labelledby="classStudentsTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="classStudentsTitle">Học viên lớp học</h2>
                    <p class="text-tiny text-muted-2 mb-0">Danh sách học viên đang đăng ký</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0">
                <div class="app-table-wrap">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Điện thoại</th>
                                <th>Email</th>
                                <th>Ngày đăng ký</th>
                            </tr>
                        </thead>
                        <tbody id="class-students-list">
                            <tr><td colspan="4" class="text-muted-2">Đang tải học viên…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/instructor/instructor-classes.js')) ?>?v=9"></script>
<?php require $partials . '/foot.php'; ?>