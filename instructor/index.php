<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';

$appRole = 'instructor';
$activeMenu = 'schedule';
$pageTitle = 'Lịch dạy';
$pageScripts = [];

$instructor = data_current_instructor();
$instructorId = (int) ($instructor['id'] ?? 0);
$classes = array_values(array_filter(
    data_classes(),
    static fn (array $class): bool =>
        (int) $class['giao_vien_id'] === $instructorId
        && $class['status'] === 'dang_mo'
));

$timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
$today = new DateTimeImmutable('today', $timezone);
$monthStart = $today->modify('first day of this month');
$monthEnd = $today->modify('last day of this month');
$weekdayNames = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];

$dateFromDisplay = static function (string $date) use ($timezone): ?DateTimeImmutable {
    $parsed = DateTimeImmutable::createFromFormat('!d/m/Y', $date, $timezone);
    return $parsed instanceof DateTimeImmutable ? $parsed : null;
};

$schedule = [];
for ($date = $monthStart; $date <= $monthEnd; $date = $date->modify('+1 day')) {
    $dayClasses = [];
    foreach ($classes as $class) {
        $from = $dateFromDisplay($class['from']);
        $to = $dateFromDisplay($class['to']);
        if (
            (int) $class['weekday'] === (int) $date->format('N')
            && $from !== null
            && $to !== null
            && $date >= $from
            && $date <= $to
        ) {
            $dayClasses[] = $class;
        }
    }

    if ($dayClasses !== []) {
        usort($dayClasses, static fn (array $first, array $second): int => strcmp($first['start'], $second['start']));
        $schedule[] = [
            'date' => $date,
            'weekday' => $weekdayNames[(int) $date->format('N')],
            'classes' => $dayClasses,
        ];
    }
}

$partials = __DIR__ . '/../common/partials';
require $partials . '/head.php';
?>
<div class="app-shell">
    <?php require $partials . '/sidebar.php'; ?>

    <div class="app-main">
        <?php require $partials . '/topbar.php'; ?>

        <main class="app-content instructor-schedule" id="main-content">
            <?php require $partials . '/flash.php'; ?>

            <div class="page-head">
                <div>
                    <h1>Lịch dạy của tôi</h1>
                    <p>Xem nhanh các lớp đã mở trong tháng này.</p>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-header schedule-heading">
                    <div>
                        <h2>Lịch trong tháng</h2>
                        <p class="sub">Chỉ hiển thị những ngày có buổi dạy.</p>
                    </div>
                    <span class="schedule-month"><?= icon('calendar-month') ?> Tháng <?= e($today->format('m/Y')) ?></span>
                </div>
                <div class="app-card-body">
                    <?php if ($schedule === []): ?>
                        <div class="schedule-empty">
                            <span class="schedule-empty-icon"><?= icon('calendar-x') ?></span>
                            <strong>Chưa có lịch dạy trong tháng này</strong>
                            <span class="text-tiny">Các lớp đã mở sẽ xuất hiện tại đây.</span>
                        </div>
                    <?php else: ?>
                        <div class="schedule-list">
                            <?php foreach ($schedule as $day): ?>
                                <section class="schedule-day">
                                    <div class="schedule-day-head">
                                        <span class="schedule-date"><?= e($day['date']->format('d')) ?></span>
                                        <span class="schedule-weekday"><?= e($day['weekday']) ?></span>
                                        <span class="schedule-count"><?= count($day['classes']) ?> buổi</span>
                                    </div>
                                    <div class="schedule-events">
                                        <?php foreach ($day['classes'] as $class): ?>
                                            <div class="schedule-event-row">
                                                <span class="schedule-time"><?= e($class['start'] . ' – ' . $class['end']) ?></span>
                                                <div>
                                                    <div class="schedule-class-name"><?= e($class['name']) ?></div>
                                                    <?php if ($class['room'] !== ''): ?>
                                                        <div class="schedule-details"><span><?= icon('geo-alt') ?> <?= e($class['room']) ?></span></div>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="schedule-students"><?= icon('people') ?> <?= (int) $class['enrolled'] ?> học viên</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php require $partials . '/foot.php'; ?>
