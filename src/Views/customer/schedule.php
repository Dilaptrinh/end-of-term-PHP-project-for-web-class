<?php
require_once __DIR__ . '/../data/data.php';

$classes = data_classes();
$myClassIds = [1, 2, 4];
$mine = array_values(array_filter($classes, static fn ($c) => in_array($c['id'], $myClassIds, true)));

$weekDates = [
    1 => ['19', 'T2'],
    2 => ['20', 'T3'],
    3 => ['21', 'T4'],
    4 => ['22', 'T5'],
    5 => ['23', 'T6'],
    6 => ['24', 'T7'],
    7 => ['25', 'CN'],
];
$slots = ['06:00', '07:00', '08:00', '09:30', '16:00', '17:30', '18:00', '19:00'];
?>
<div class="page-head">
    <div>
        <h1>Lịch tập của tôi</h1>
        <p>Các lớp bạn đã đăng ký trong tuần 19/10 – 25/10/2026.</p>
    </div>
    <div class="page-head-actions">
        <div class="btn-group" role="group" aria-label="Điều hướng tuần">
            <button class="btn btn-outline-primary btn-sm" type="button" aria-label="Tuần trước"><?= icon('chevron-left') ?></button>
            <button class="btn btn-outline-primary btn-sm" type="button">Tuần này</button>
            <button class="btn btn-outline-primary btn-sm" type="button" aria-label="Tuần sau"><?= icon('chevron-right') ?></button>
        </div>
        <a class="btn btn-primary" href="<?= e(url('/customer/class-registration')) ?>"><?= icon('plus-lg') ?> Thêm lớp</a>
    </div>
</div>

<ul class="nav nav-tabs app-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-week" data-bs-toggle="tab" data-bs-target="#pane-week" type="button" role="tab" aria-controls="pane-week" aria-selected="true">
            <?= icon('calendar-week') ?> Tuần
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-month" data-bs-toggle="tab" data-bs-target="#pane-month" type="button" role="tab" aria-controls="pane-month" aria-selected="false">
            <?= icon('calendar-month') ?> Tháng
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="pane-week" role="tabpanel" aria-labelledby="tab-week" tabindex="0">
        <div class="row g-3">
            <div class="col-xl-9">
                <div class="app-card">
                    <div class="app-card-header">
                        <div>
                            <h2>Tuần 19/10 – 25/10</h2>
                            <p class="sub">3 lớp · 4 buổi tập</p>
                        </div>
                        <div class="ms-auto chart-legend">
                            <span class="lg"><span class="sw"></span> Đã đăng ký</span>
                            <span class="lg"><span class="sw" style="background:var(--warning)"></span> Sắp tới</span>
                        </div>
                    </div>
                    <div class="app-card-body">
                        <div class="schedule-shell">
                            <div class="schedule-grid">
                                <div class="sg-head">Giờ</div>
                                <?php foreach ($weekDates as $d => $info): ?>
                                    <div class="sg-head"><?= e($info[1]) ?><span class="d"><?= e($info[0]) ?>/10</span></div>
                                <?php endforeach; ?>

                                <?php foreach ($slots as $slot): ?>
                                    <div class="sg-time"><?= e($slot) ?></div>
                                    <?php foreach ($weekDates as $d => $info): ?>
                                        <div class="sg-cell">
                                            <?php foreach ($mine as $class): ?>
                                                <?php if ((int) $class['weekday'] === $d && $class['start'] === $slot): ?>
                                                    <span class="schedule-event <?= $d === 5 ? 'amber' : '' ?>">
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
                    <div class="app-card-header"><h2>Buổi tập sắp tới</h2></div>
                    <div class="app-card-body">
                        <ul class="app-timeline">
                            <li>
                                <span class="tl-dot warning"><?= icon('clock') ?></span>
                                <div class="tl-title">HIIT đốt mỡ</div>
                                <div class="tl-meta">Thứ 3, 20/10 · 06:00 – 07:00</div>
                                <div class="tl-meta">Nguyễn Đức Anh · P.202</div>
                            </li>
                            <li>
                                <span class="tl-dot"><?= icon('calendar-event') ?></span>
                                <div class="tl-title">Zumba Dance</div>
                                <div class="tl-meta">Thứ 5, 22/10 · 19:00 – 20:00</div>
                                <div class="tl-meta">Trần Thu Hà · P.101</div>
                            </li>
                            <li>
                                <span class="tl-dot"><?= icon('calendar-event') ?></span>
                                <div class="tl-title">Yoga cơ bản</div>
                                <div class="tl-meta">Thứ 2, 19/10 · 18:00 – 19:00</div>
                                <div class="tl-meta">Trần Thu Hà · P.101</div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="pane-month" role="tabpanel" aria-labelledby="tab-month" tabindex="0">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Tháng 10/2026</h2>
                    <p class="sub">Các lớp đã đăng ký và sắp diễn ra</p>
                </div>
                <div class="btn-group ms-auto">
                    <button class="btn btn-outline-primary btn-sm" type="button"><?= icon('chevron-left') ?></button>
                    <button class="btn btn-outline-primary btn-sm" type="button">Tháng 10</button>
                    <button class="btn btn-outline-primary btn-sm" type="button"><?= icon('chevron-right') ?></button>
                </div>
            </div>
            <div class="app-card-body">
                <div class="day-column">
                    <?php
                    $monthDays = [
                        ['19/10', 'Thứ 2', [['18:00', 'Yoga cơ bản', 'Trần Thu Hà', 'P.101', '']]],
                        ['20/10', 'Thứ 3', [['06:00', 'HIIT đốt mỡ', 'Nguyễn Đức Anh', 'P.202', 'green'], ['18:00', 'Gym & Strength', 'Phạm Quốc Bảo', 'Khu A', '']]],
                        ['21/10', 'Thứ 4', []],
                        ['22/10', 'Thứ 5', [['19:00', 'Zumba Dance', 'Trần Thu Hà', 'P.101', 'amber']]],
                        ['23/10', 'Thứ 6', [['06:00', 'HIIT đốt mỡ', 'Nguyễn Đức Anh', 'P.202', 'green']]],
                        ['24/10', 'Thứ 7', []],
                        ['25/10', 'Chủ nhật', [['16:00', 'Cardio & Core', 'Lê Ngọc Mai', 'P.202', '']]],
                    ];
                    foreach ($monthDays as $day):
                    ?>
                        <div class="day-block">
                            <div class="day-head">
                                <span class="date"><?= e($day[0]) ?></span>
                                <span class="text-muted-2"><?= e($day[1]) ?></span>
                                <span class="ms-auto text-tiny text-muted-2"><?= count($day[2]) ?> buổi</span>
                            </div>
                            <div class="day-body">
                                <?php if ($day[2] === []): ?>
                                    <div class="text-tiny text-muted-2">Không có lớp.</div>
                                <?php else: ?>
                                    <?php foreach ($day[2] as $event): ?>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge-soft"><?= e($event[0]) ?></span>
                                            <span class="fw-700 text-small"><?= e($event[1]) ?></span>
                                            <span class="text-tiny text-muted-2"><?= icon('person') ?> <?= e($event[2]) ?> · <?= icon('geo-alt') ?> <?= e($event[3]) ?></span>
                                            <span class="ms-auto"><?= badge($event[4] === 'green' ? 'Đã diễn ra' : 'Sắp tới', $event[4] === 'green' ? 'muted' : 'warning') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
