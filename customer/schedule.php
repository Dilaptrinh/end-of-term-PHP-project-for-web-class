<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';

$appRole = 'customer';
$activeMenu = 'schedule';
$pageTitle = 'Lịch tập';
$pageScripts = [];

$classes = data_customer_classes();

$timezone = new DateTimeZone('Asia/Ho_Chi_Minh');
$today = new DateTimeImmutable('today', $timezone);
$monthStart = $today->modify('first day of this month');
$monthEnd = $today->modify('last day of this month');
$weekdayNames = [
    1 => 'Thứ 2',
    2 => 'Thứ 3',
    3 => 'Thứ 4',
    4 => 'Thứ 5',
    5 => 'Thứ 6',
    6 => 'Thứ 7',
    7 => 'Chủ nhật',
];

$schedule = [];
foreach ($classes as $class) {
    $from = DateTimeImmutable::createFromFormat('!d/m/Y', $class['from'], $timezone);
    $to = DateTimeImmutable::createFromFormat('!d/m/Y', $class['to'], $timezone);

    if (!$from || !$to) {
        continue;
    }

    for ($date = $monthStart; $date <= $monthEnd; $date = $date->modify('+1 day')) {
        if ((int) $class['weekday'] !== (int) $date->format('N')) {
            continue;
        }
        if ($date < $from || $date > $to) {
            continue;
        }

        $key = $date->format('Y-m-d');
        if (!isset($schedule[$key])) {
            $schedule[$key] = [
                'date' => $date,
                'weekday' => $weekdayNames[(int) $date->format('N')],
                'classes' => [],
            ];
        }
        $schedule[$key]['classes'][] = $class;
    }
}

foreach ($schedule as &$day) {
    usort($day['classes'], static function (array $first, array $second): int {
        return strcmp($first['start'], $second['start']);
    });
}
unset($day);

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
                    <h1>Lịch tập của tôi</h1>
                    <p>Xem nhanh các lớp bạn đã đăng ký trong tháng này.</p>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-header schedule-heading">
                    <div>
                        <h2>Lịch trong tháng</h2>
                        <p class="sub">Chỉ hiển thị những ngày có buổi tập.</p>
                    </div>
                    <span class="schedule-month"><?= icon('calendar-month') ?> Tháng <?= e($today->format('m/Y')) ?></span>
                </div>
                <div class="app-card-body">
                    <?php if (!$schedule): ?>
                        <div class="schedule-empty">
                            <span class="schedule-empty-icon"><?= icon('calendar-x') ?></span>
                            <strong>Chưa có lịch tập trong tháng này</strong>
                            <span class="text-tiny">Các lớp bạn đăng ký sẽ xuất hiện tại đây.</span>
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
                                                    <div class="schedule-details">
                                                        <span><?= icon('person') ?> <?= e($class['instructor']) ?></span>
                                                        <?php if ($class['room'] !== ''): ?>
                                                            <span><?= icon('geo-alt') ?> <?= e($class['room']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
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
