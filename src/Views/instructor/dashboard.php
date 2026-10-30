<?php
require_once __DIR__ . '/../data/data.php';

$classes = array_values(array_filter(data_classes(), static fn ($class) => $class['instructor'] === data_current_instructor_name()));
$requests = array_values(array_filter(data_availability(), static fn ($request) => $request['instructor'] === data_current_instructor_name()));
?>
<div class="page-head">
    <div>
        <h1>Xin chào, <?= e(data_current_instructor_name()) ?></h1>
        <p>Lịch làm việc và các yêu cầu dạy của bạn.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Lớp đang phụ trách</div>
            <div class="stat-value"><?= count($classes) ?></div>
            <div class="stat-foot">Theo lịch được phân công</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Tổng học viên</div>
            <div class="stat-value"><?= e((string) array_sum(array_column($classes, 'enrolled'))) ?></div>
            <div class="stat-foot">Trong các lớp hiện tại</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card h-100">
            <div class="stat-label">Yêu cầu chờ duyệt</div>
            <div class="stat-value text-warning"><?= count(array_filter($requests, static fn ($request) => $request['status'] === 'cho_duyet')) ?></div>
            <div class="stat-foot"><a href="<?= e(url('/instructor/availability')) ?>">Xem yêu cầu lịch dạy</a></div>
        </div>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <div>
            <h2>Lớp được phân công</h2>
            <p class="sub">Thông tin để chuẩn bị cho các buổi dạy</p>
        </div>
        <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/instructor/classes')) ?>">Xem tất cả <?= icon('arrow-right') ?></a>
    </div>
    <div class="app-table-wrap">
        <table class="app-table">
            <thead><tr><th>Lớp học</th><th>Lịch</th><th>Phòng</th><th>Sĩ số</th></tr></thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                        <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                        <td><?= e($class['room']) ?></td>
                        <td><?= e($class['enrolled'] . '/' . $class['capacity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
