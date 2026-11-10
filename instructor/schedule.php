<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';

$appRole = 'instructor';
$activeMenu = 'schedule';
$pageTitle = 'Lịch dạy';
$pageScripts = [];

$instructorName = data_current_instructor_name();
$classes = array_values(array_filter(data_classes(), static fn ($c) => $c['instructor'] === $instructorName));

$timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
$today = new DateTimeImmutable('today', $timezone);
$weekStart = $today->modify('-' . ((int) $today->format('N') - 1) . ' days');
$weekEnd = $weekStart->modify('+6 days');
$monthStart = $today->modify('first day of this month');
$monthEnd = $today->modify('last day of this month');
$weekdayNames = [1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN'];
$dateFromDisplay = static function (string $date) use ($timezone): ?DateTimeImmutable {
    $parsed = DateTimeImmutable::createFromFormat('!d/m/Y', $date, $timezone);
    return $parsed instanceof DateTimeImmutable ? $parsed : null;
};
$classIsActiveOn = static function (array $class, DateTimeImmutable $date) use ($dateFromDisplay): bool {
    $from = $dateFromDisplay($class['from']);
    $to = $dateFromDisplay($class['to']);
    return $from !== null && $to !== null && $date >= $from && $date <= $to;
};

$weekDates = [];
$weekClasses = [];
$slots = [];
for ($offset = 0; $offset < 7; $offset++) {
    $date = $weekStart->modify('+' . $offset . ' days');
    $weekDates[(int) $date->format('N')] = ['date' => $date, 'label' => $weekdayNames[(int) $date->format('N')]];
    $weekClasses[$date->format('Y-m-d')] = [];
}

$weekClassCount = 0;
$weekTeachingMinutes = 0;
$weekStudentCount = 0;
foreach ($weekDates as $info) {
    $date = $info['date'];
    $dateKey = $date->format('Y-m-d');
    foreach ($classes as $class) {
        if ((int) $class['weekday'] !== (int) $date->format('N') || !$classIsActiveOn($class, $date)) {
            continue;
        }
        $weekClasses[$dateKey][] = $class;
        $weekClassCount++;
        $weekTeachingMinutes += max(0, (strtotime($class['end']) - strtotime($class['start'])) / 60);
        $weekStudentCount += (int) $class['enrolled'];
        $slots[$class['start']] = true;
    }
}

ksort($slots);
$slots = array_keys($slots);
$weekTeachingHours = (int) round($weekTeachingMinutes / 60);

$monthDays = [];
for ($date = $monthStart; $date <= $monthEnd; $date = $date->modify('+1 day')) {
    $events = [];
    foreach ($classes as $class) {
        if ((int) $class['weekday'] === (int) $date->format('N') && $classIsActiveOn($class, $date)) {
            $events[] = ['start' => $class['start'], 'end' => $class['end'], 'name' => $class['name'], 'room' => $class['room'], 'tone' => 'primary', 'status' => 'Lớp dạy', 'students' => $class['enrolled']];
        }
    }
    usort($events, static fn (array $first, array $second): int => strcmp($first['start'], $second['start']));
    $monthDays[] = ['date' => $date, 'weekday' => $weekdayNames[(int) $date->format('N')], 'events' => $events];
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
        <h1>Lịch dạy của tôi</h1>
        <p>Lịch các lớp đã được admin duyệt và mở đăng ký.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-primary" href="<?= e(url('/instructor/availability')) ?>"><?= icon('calendar-plus') ?> Đề xuất lớp</a>
    </div>
</div>

<ul class="nav nav-tabs app-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="itab-week" data-bs-toggle="tab" data-bs-target="#ipane-week" type="button" role="tab" aria-selected="true"><?= icon('calendar-week') ?> Tuần</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="itab-month" data-bs-toggle="tab" data-bs-target="#ipane-month" type="button" role="tab" aria-selected="false"><?= icon('calendar-month') ?> Tháng</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="ipane-week" role="tabpanel" aria-labelledby="itab-week" tabindex="0">
        <div class="row g-3">
            <div class="col-xl-9">
                <div class="app-card">
                    <div class="app-card-header">
                        <div>
                            <h2>Tuần <?= e($weekStart->format('d/m')) ?> – <?= e($weekEnd->format('d/m/Y')) ?></h2>
                            <p class="sub"><?= $weekClassCount ?> buổi dạy · Chỉ hiển thị lớp đã mở</p>
                        </div>
                        <div class="ms-auto chart-legend">
                            <span class="lg"><span class="sw"></span> Lớp dạy</span>
                        </div>
                    </div>
                    <div class="app-card-body">
                        <div class="schedule-shell">
                            <div class="schedule-grid">
                                <div class="sg-head">Giờ</div>
                                <?php foreach ($weekDates as $d => $info): ?>
                                    <div class="sg-head"><?= e($info['label']) ?><span class="d"><?= e($info['date']->format('d/m')) ?></span></div>
                                <?php endforeach; ?>

                                <?php foreach ($slots as $slot): ?>
                                    <div class="sg-time"><?= e($slot) ?></div>
                                    <?php foreach ($weekDates as $d => $info): ?>
                                        <div class="sg-cell">
                                            <?php $dateKey = $info['date']->format('Y-m-d'); ?>
                                            <?php foreach ($weekClasses[$dateKey] as $class): ?>
                                                <?php if ($class['start'] === $slot): ?>
                                                    <span class="schedule-event">
                                                        <strong><?= e($class['name']) ?></strong>
                                                        <span class="t"><?= e($class['start'] . ' – ' . $class['end']) ?> · <?= e($class['room']) ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="app-card mb-3">
                    <div class="app-card-header"><h2>Tóm tắt tuần</h2></div>
                    <div class="app-card-body">
                        <div class="kv-list">
                            <div class="kv"><span>Số buổi dạy</span><span><?= $weekClassCount ?></span></div>
                            <div class="kv"><span>Tổng giờ dạy</span><span><?= $weekTeachingHours ?> giờ</span></div>
                            <div class="kv"><span>Tổng lượt học viên</span><span><?= $weekStudentCount ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="app-card">
                    <div class="app-card-header"><h2>Lớp trong tuần</h2></div>
                    <div class="app-card-body">
                        <ul class="app-timeline">
                            <?php foreach ($weekClasses as $dateKey => $dayClasses): ?>
                                <?php foreach ($dayClasses as $class): ?>
                                <li>
                                    <span class="tl-dot success"><?= icon('check-lg') ?></span>
                                    <div class="tl-title"><?= e($class['name']) ?></div>
                                    <div class="tl-meta"><?= e($class['start'] . ' – ' . $class['end']) ?> · <?= e($class['room']) ?></div>
                                </li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <?php if ($weekClassCount === 0): ?>
                                <li><div class="text-tiny text-muted-2">Chưa có lớp trong tuần này.</div></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="ipane-month" role="tabpanel" aria-labelledby="itab-month" tabindex="0">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Tháng <?= e($today->format('m/Y')) ?></h2>
                    <p class="sub">Tổng hợp lớp dạy và lịch đã duyệt theo ngày</p>
                </div>
            </div>
            <div class="app-card-body">
                <div class="day-column">
                    <?php foreach ($monthDays as $day): ?>
                        <?php if ($day['events'] === []) continue; ?>
                        <div class="day-block">
                            <div class="day-head">
                                <span class="date"><?= e($day['date']->format('d/m')) ?></span>
                                <span class="text-muted-2"><?= e($day['weekday']) ?></span>
                                <span class="ms-auto text-tiny text-muted-2"><?= count($day['events']) ?> hoạt động</span>
                            </div>
                            <div class="day-body">
                                <?php foreach ($day['events'] as $event): ?>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge-soft"><?= e($event['start'] . ' – ' . $event['end']) ?></span>
                                        <span class="fw-700 text-small"><?= e($event['name']) ?></span>
                                        <?php if ($event['room'] !== '' || $event['students'] !== null): ?>
                                            <span class="text-tiny text-muted-2"><?= icon('geo-alt') ?><?= $event['room'] !== '' ? ' ' . e($event['room']) : '' ?><?= $event['students'] !== null ? ' · ' . (int) $event['students'] . ' học viên' : '' ?></span>
                                        <?php endif; ?>
                                        <span class="ms-auto"><?= badge($event['status'] === 'Lớp dạy' ? 'Lớp dạy' : 'Đã duyệt', $event['tone']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php require $partials . '/foot.php'; ?>