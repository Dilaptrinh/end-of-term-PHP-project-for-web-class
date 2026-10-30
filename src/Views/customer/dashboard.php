<?php
require_once __DIR__ . '/../data/data.php';

$member = data_current_member();
$profile = data_customer_profile();
$upcoming = data_customer_classes();
$next = $upcoming[0] ?? null;
?>
<div class="page-head">
    <div>
        <h1>Xin chào, <?= e($member['ho_ten'] ?? 'bạn') ?></h1>
        <p>Đây là những thông tin bạn cần cho buổi tập tiếp theo.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="app-card h-100">
            <div class="app-card-header"><h2>Gói tập hiện tại</h2><?= badge('Đang hoạt động', 'success') ?></div>
            <div class="app-card-body">
                <div class="fw-700"><?= e($member['package'] ?? '—') ?></div>
                <div class="text-small text-muted-2 mt-1">Còn hạn đến <?= e($member['package_expiry'] ?? '—') ?></div>
                <a class="btn btn-ghost btn-sm px-0 mt-3" href="<?= e(url('/customer/history')) ?>">Xem lịch sử gói <?= icon('arrow-right') ?></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="app-card h-100">
            <div class="app-card-header"><h2>Chỉ số cơ thể</h2></div>
            <div class="app-card-body">
                <div class="d-flex gap-4">
                    <div><div class="text-tiny text-muted-2">Cân nặng</div><strong class="fs-5"><?= e($profile['weight']) ?> kg</strong></div>
                    <div><div class="text-tiny text-muted-2">Chiều cao</div><strong class="fs-5"><?= e($profile['height']) ?> cm</strong></div>
                </div>
                <div class="text-tiny text-muted-2 mt-3">Mục tiêu: <?= e($profile['goal']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="app-card h-100">
            <div class="app-card-header"><h2>Lớp sắp tới</h2></div>
            <div class="app-card-body">
                <?php if ($next): ?>
                    <div class="fw-700"><?= e($next['name']) ?></div>
                    <div class="text-small text-muted-2 mt-1"><?= e(weekday($next['weekday'])) ?> · <?= e($next['start']) ?> – <?= e($next['end']) ?></div>
                    <div class="text-small text-muted-2"><?= e($next['room']) ?> · <?= e($next['instructor']) ?></div>
                <?php else: ?>
                    <div class="text-muted-2 text-small">Bạn chưa đăng ký lớp học nào.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <div>
            <h2>Lớp đã đăng ký</h2>
            <p class="sub">Các lớp đang có trong lịch của bạn</p>
        </div>
        <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/customer/schedule')) ?>">Xem lịch đầy đủ <?= icon('arrow-right') ?></a>
    </div>
    <div class="app-table-wrap">
        <table class="app-table">
            <thead><tr><th>Lớp học</th><th>Lịch</th><th>Giáo viên</th><th>Phòng</th></tr></thead>
            <tbody>
                <?php foreach ($upcoming as $class): ?>
                    <tr>
                        <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                        <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                        <td><?= e($class['instructor']) ?></td>
                        <td><?= e($class['room']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$upcoming): ?>
                    <tr><td colspan="4" class="text-muted-2">Chưa có lớp đã đăng ký.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
