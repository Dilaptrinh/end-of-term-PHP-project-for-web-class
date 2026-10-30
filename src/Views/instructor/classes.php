<?php
require_once __DIR__ . '/../data/data.php';

$classes = array_values(array_filter(data_classes(), static fn ($c) => $c['instructor'] === data_current_instructor_name()));
$students = data_students_for_class(1);
?>
<div class="page-head">
    <div>
        <h1>Lớp tôi đang dạy</h1>
        <p><?= count($classes) ?> lớp được phân công · Tổng <?= array_sum(array_column($classes, 'enrolled')) ?> học viên.</p>
    </div>
</div>

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
                            data-class-name="<?= e($class['name']) ?>">
                        <?= icon('people') ?> Học viên
                    </button>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<div class="app-card mt-3">
    <div class="app-card-header">
        <div>
            <h2>Tổng quan lớp học</h2>
            <p class="sub">Sức chứa và tỷ lệ lấp đầy</p>
        </div>
    </div>
    <div class="app-table-wrap">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Lớp học</th>
                    <th>Lịch</th>
                    <th>Phòng</th>
                    <th>Sĩ số</th>
                    <th>Tỷ lệ lấp đầy</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class):
                    $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
                ?>
                    <tr data-row>
                        <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                        <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                        <td><?= e($class['room']) ?></td>
                        <td><?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></td>
                        <td style="min-width:160px;">
                            <div class="d-flex align-items-center gap-2">
                                <?= progress_bar($percent, $percent >= 90 ? 'amber' : '') ?>
                                <span class="text-tiny fw-700"><?= $percent ?>%</span>
                            </div>
                        </td>
                        <td><?= status_badge($class['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($students, 0, 8) as $student): ?>
                                <tr>
                                    <td>
                                        <div class="user-inline">
                                            <?= avatar($student['name'], 'avatar-sm') ?>
                                            <span>
                                                <span class="d-block user-name"><?= e($student['name']) ?></span>
                                                <span class="d-block user-sub">HV-<?= str_pad((string) $student['member_id'], 4, '0', STR_PAD_LEFT) ?></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-muted-2"><?= e($student['phone']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-tiny text-muted-2 me-auto">Hiển thị 8 / <?= count($students) ?> học viên</span>
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
