<?php
require_once __DIR__ . '/../data/data.php';

$classes = array_values(array_filter(data_classes(), static fn ($c) => $c['instructor'] === data_current_instructor_name()));
$availability = array_values(array_filter(data_availability(), static fn ($a) => $a['instructor'] === data_current_instructor_name() && $a['status'] === 'da_duyet'));

$weekDates = [
    1 => ['19', 'T2'],
    2 => ['20', 'T3'],
    3 => ['21', 'T4'],
    4 => ['22', 'T5'],
    5 => ['23', 'T6'],
    6 => ['24', 'T7'],
    7 => ['25', 'CN'],
];
$slots = ['07:00', '08:00', '09:30', '17:30', '18:00', '19:00'];
?>
<div class="page-head">
    <div>
        <h1>Lịch dạy của tôi</h1>
        <p>Lịch lớp được phân công và các khung giờ đã được admin duyệt.</p>
    </div>
    <div class="page-head-actions">
        <div class="btn-group" role="group" aria-label="Điều hướng tuần">
            <button class="btn btn-outline-primary btn-sm" type="button" aria-label="Tuần trước"><?= icon('chevron-left') ?></button>
            <button class="btn btn-outline-primary btn-sm" type="button">Tuần này</button>
            <button class="btn btn-outline-primary btn-sm" type="button" aria-label="Tuần sau"><?= icon('chevron-right') ?></button>
        </div>
        <a class="btn btn-primary" href="<?= e(url('/instructor/availability')) ?>"><?= icon('calendar-plus') ?> Đăng ký lịch</a>
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
                            <h2>Tuần 19/10 – 25/10/2026</h2>
                            <p class="sub">3 lớp dạy · 1 khung giờ đã duyệt</p>
                        </div>
                        <div class="ms-auto chart-legend">
                            <span class="lg"><span class="sw"></span> Lớp dạy</span>
                            <span class="lg"><span class="sw" style="background:var(--success)"></span> Lịch đã duyệt</span>
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
                                            <?php foreach ($classes as $class): ?>
                                                <?php if ((int) $class['weekday'] === $d && $class['start'] === $slot): ?>
                                                    <span class="schedule-event">
                                                        <strong><?= e($class['name']) ?></strong>
                                                        <span class="t"><?= e($class['start'] . ' – ' . $class['end']) ?> · <?= e($class['room']) ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php if ($d === 6 && $slot === '09:30'): ?>
                                                <span class="schedule-event green">
                                                    <strong>Lịch đã duyệt</strong>
                                                    <span class="t">09:00 – 11:00 · Yoga bổ sung</span>
                                                </span>
                                            <?php endif; ?>
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
                            <div class="kv"><span>Số lớp dạy</span><span>3 lớp</span></div>
                            <div class="kv"><span>Tổng giờ dạy</span><span>3 giờ</span></div>
                            <div class="kv"><span>Tổng học viên</span><span>62</span></div>
                            <div class="kv"><span>Lịch đã duyệt</span><span class="text-success">1</span></div>
                            <div class="kv"><span>Chờ duyệt</span><span class="text-warning">1</span></div>
                        </div>
                    </div>
                </div>

                <div class="app-card">
                    <div class="app-card-header"><h2>Khung giờ đã duyệt</h2></div>
                    <div class="app-card-body">
                        <ul class="app-timeline">
                            <?php foreach ($availability as $request): ?>
                                <li>
                                    <span class="tl-dot success"><?= icon('check-lg') ?></span>
                                    <div class="tl-title"><?= e($request['date']) ?> · <?= e($request['start'] . ' – ' . $request['end']) ?></div>
                                    <div class="tl-meta"><?= e($request['note']) ?></div>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <span class="tl-dot warning"><?= icon('clock') ?></span>
                                <div class="tl-title">26/10/2026 · 07:00 – 09:00</div>
                                <div class="tl-meta">Đang chờ admin duyệt</div>
                            </li>
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
                    <h2>Tháng 10/2026</h2>
                    <p class="sub">Tổng hợp lớp dạy và lịch đã duyệt theo ngày</p>
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
                    $days = [
                        ['19/10', 'Thứ 2', [['18:00 – 19:00', 'Yoga cơ bản', 'P.101', 'primary', '18 học viên']]],
                        ['20/10', 'Thứ 3', []],
                        ['21/10', 'Thứ 4', [['19:00 – 20:00', 'Zumba Dance', 'P.101', 'primary', '22 học viên']]],
                        ['22/10', 'Thứ 5', []],
                        ['23/10', 'Thứ 6', [['07:00 – 09:00', 'Lịch đã duyệt', 'P.101', 'success', 'Yoga bổ sung']]],
                        ['24/10', 'Thứ 7', [['09:30 – 10:30', 'Yoga nâng cao', 'P.101', 'primary', '16 học viên']]],
                        ['25/10', 'Chủ nhật', []],
                    ];
                    foreach ($days as $day):
                    ?>
                        <div class="day-block">
                            <div class="day-head">
                                <span class="date"><?= e($day[0]) ?></span>
                                <span class="text-muted-2"><?= e($day[1]) ?></span>
                                <span class="ms-auto text-tiny text-muted-2"><?= count($day[2]) ?> hoạt động</span>
                            </div>
                            <div class="day-body">
                                <?php if ($day[2] === []): ?>
                                    <div class="text-tiny text-muted-2">Không có lịch dạy.</div>
                                <?php else: ?>
                                    <?php foreach ($day[2] as $event): ?>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge-soft"><?= e($event[0]) ?></span>
                                            <span class="fw-700 text-small"><?= e($event[1]) ?></span>
                                            <span class="text-tiny text-muted-2"><?= icon('geo-alt') ?> <?= e($event[2]) ?> · <?= e($event[4]) ?></span>
                                            <span class="ms-auto"><?= badge($event[3] === 'success' ? 'Đã duyệt' : 'Lớp dạy', $event[3] === 'success' ? 'success' : 'primary') ?></span>
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
