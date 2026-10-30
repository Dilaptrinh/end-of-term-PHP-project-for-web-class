<?php
require_once __DIR__ . '/../data/data.php';

$revenue = data_revenue();
$max = max(array_column($revenue, 'value')) ?: 1;
$registrations = data_package_registrations();
$pendingPackages = array_values(array_filter($registrations, static fn ($r) => $r['payment'] === 'chua_thanh_toan'));
$classes = data_classes();
$packages = data_packages();
$members = data_members();
$availability = data_availability();
$pendingSlots = count(array_filter($availability, static fn ($r) => $r['status'] === 'cho_duyet'));
$pendingTotal = $pendingSlots + count($pendingPackages);
$currentRevenue = 0;
$prevRevenue = 0;
foreach ($revenue as $row) {
    if ($row['month'] === 'T10') {
        $currentRevenue = $row['value'];
    }
    if ($row['month'] === 'T9') {
        $prevRevenue = $row['value'];
    }
}
$revenueDelta = $prevRevenue > 0 ? round(100 * ($currentRevenue - $prevRevenue) / $prevRevenue, 1) : 0;
$totalEnrolled = (int) array_sum(array_column($classes, 'enrolled'));
$totalCapacity = (int) array_sum(array_column($classes, 'capacity'));
$occupancy = $totalCapacity > 0 ? (int) round(100 * $totalEnrolled / $totalCapacity) : 0;
$fullClasses = count(array_filter($classes, static fn ($c) => (int) $c['capacity'] > 0 && (int) $c['enrolled'] >= (int) $c['capacity']));
$pendingSlotsList = array_values(array_filter($availability, static fn ($r) => $r['status'] === 'cho_duyet'));
?>
<div class="page-head">
    <div>
        <h1>Tổng quan hệ thống</h1>
        <p>Cập nhật lúc <?= e(date('d/m/Y H:i')) ?> · Tháng 10/2026</p>
    </div>
    <div class="page-head-actions">
        <select class="form-select form-select-sm" aria-label="Khoảng thời gian" style="width:auto;">
            <option>Tháng này</option>
            <option>Quý này</option>
            <option>Năm nay</option>
        </select>
        <a class="btn btn-primary" href="<?= e(url('/admin/reports')) ?>"><?= icon('bar-chart-line') ?> Xem thống kê</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Doanh thu tháng 10</div>
                    <div class="stat-value" style="font-size:1.45rem;"><?= e(number_format($currentRevenue, 1, ',', '.')) ?>tr</div>
                    <div class="stat-foot"><span class="trend <?= $revenueDelta >= 0 ? 'up' : 'down' ?>"><?= icon($revenueDelta >= 0 ? 'arrow-up-right' : 'arrow-down-right') ?> <?= e(number_format($revenueDelta, 1, ',', '.')) ?>%</span> so với tháng 9</div>
                </div>
                <div class="stat-icon ms-auto"><?= icon('cash-stack') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Hội viên</div>
                    <div class="stat-value"><?= count($members) ?></div>
                    <div class="stat-foot">Tổng hồ sơ hội viên</div>
                </div>
                <div class="stat-icon green ms-auto"><?= icon('people') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Lớp đang mở</div>
                    <div class="stat-value"><?= count(array_filter($classes, static fn ($c) => $c['status'] === 'dang_mo')) ?></div>
                    <div class="stat-foot">Tỷ lệ lấp đầy trung bình 78%</div>
                </div>
                <div class="stat-icon blue ms-auto"><?= icon('calendar2-week') ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-start">
                <div>
                    <div class="stat-label">Yêu cầu chờ duyệt</div>
                    <div class="stat-value" style="color:var(--warning);"><?= $pendingTotal ?></div>
                    <div class="stat-foot"><?= $pendingSlots ?> lịch dạy · <?= count($pendingPackages) ?> thanh toán</div>
                </div>
                <div class="stat-icon amber ms-auto"><?= icon('bell') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="app-card mb-3">
            <div class="app-card-header">
                <div>
                    <h2>Doanh thu theo tháng</h2>
                    <p class="sub">Đơn vị: triệu đồng · 10 tháng gần nhất</p>
                </div>
                <div class="ms-auto chart-legend">
                    <span class="lg"><span class="sw"></span> Doanh thu</span>
                </div>
            </div>
            <div class="app-card-body">
                <div class="chart-bars" role="img" aria-label="Biểu đồ doanh thu theo tháng">
                    <?php foreach ($revenue as $item): ?>
                        <div class="bar-col">
                            <span class="bar-value"><?= $item['value'] > 0 ? e((string) $item['value']) : '' ?></span>
                            <div class="bar" style="height:<?= $item['value'] > 0 ? (int) round($item['value'] / $max * 100) : 2 ?>%;" title="<?= e($item['month']) ?>: <?= e((string) $item['value']) ?> triệu"></div>
                            <span class="bar-label"><?= e($item['month']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Đăng ký gói chờ thanh toán</h2>
                    <p class="sub"><?= count($pendingPackages) ?> đăng ký cần xác nhận</p>
                </div>
                <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/admin/registrations')) ?>">Xem tất cả <?= icon('arrow-right') ?></a>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Hội viên</th>
                            <th>Gói tập</th>
                            <th>Thời hạn</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th class="cell-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pendingPackages, 0, 3) as $row): ?>
                            <tr>
                                <td><?= e($row['member']) ?></td>
                                <td><?= e($row['package']) ?></td>
                                <td class="text-muted-2"><?= e($row['from']) ?> → <?= e($row['to']) ?></td>
                                <td class="cell-strong"><?= e(money($row['price'])) ?></td>
                                <td><?= status_badge($row['payment']) ?></td>
                                <td class="cell-actions">
                                    <button class="btn btn-primary btn-sm" type="button"
                                            data-api-url="/api/registrations/packages/<?= (int) $row['id'] ?>/confirm"
                                            data-api-method="POST"
                                            data-api-confirm="Xác nhận đã thanh toán cho <?= e($row['member']) ?>?"
                                            data-api-success-title="Đã xác nhận thanh toán">Xác nhận</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="app-card mb-3">
            <div class="app-card-header"><h2>Tỷ lệ lấp đầy</h2></div>
            <div class="app-card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="donut" style="--p:<?= $occupancy ?>;">
                        <span class="donut-value"><?= $occupancy ?>%<small>trung bình</small></span>
                    </div>
                    <div class="text-small flex-grow-1">
                        <div class="d-flex justify-content-between mb-1"><span class="text-muted-2">Đã đăng ký</span><strong><?= number_format($totalEnrolled, 0, ',', '.') ?></strong></div>
                        <div class="d-flex justify-content-between mb-1"><span class="text-muted-2">Sức chứa</span><strong><?= number_format($totalCapacity, 0, ',', '.') ?></strong></div>
                        <div class="d-flex justify-content-between"><span class="text-muted-2">Lớp đầy</span><strong class="text-warning"><?= $fullClasses ?></strong></div>
                    </div>
                </div>
                <div class="mt-3">
                    <?php foreach (array_slice($classes, 0, 3) as $class):
                        $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
                    ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between text-tiny mb-1">
                                <span><?= e($class['name']) ?></span>
                                <span class="fw-700"><?= $percent ?>%</span>
                            </div>
                            <?= progress_bar($percent, $percent >= 90 ? 'amber' : '') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="app-card mb-3">
            <div class="app-card-header">
                <h2>Yêu cầu chờ duyệt</h2>
                <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/admin/availability')) ?>">Duyệt</a>
            </div>
            <div class="app-card-body">
                <ul class="app-timeline">
                    <?php foreach (array_slice($pendingSlotsList, 0, 2) as $slot): ?>
                        <li>
                            <span class="tl-dot warning"><?= icon('calendar-plus') ?></span>
                            <div class="tl-title"><?= e($slot['instructor']) ?> · <?= e($slot['date']) ?></div>
                            <div class="tl-meta"><?= e($slot['start'] . ' – ' . $slot['end']) ?> · Chờ duyệt</div>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($pendingPackages): ?>
                        <li>
                            <span class="tl-dot warning"><?= icon('receipt') ?></span>
                            <div class="tl-title"><?= count($pendingPackages) ?> thanh toán chờ xác nhận</div>
                            <div class="tl-meta">Tổng giá trị <?= e(money(array_sum(array_column($pendingPackages, 'price')))) ?></div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header"><h2>Hoạt động gần đây</h2></div>
            <div class="app-card-body">
                <ul class="app-timeline">
                    <li>
                        <span class="tl-dot success"><?= icon('check-lg') ?></span>
                        <div class="tl-title">Xác nhận thanh toán Premium</div>
                        <div class="tl-meta">Bùi Ngọc Hân · 10 phút trước</div>
                    </li>
                    <li>
                        <span class="tl-dot"><?= icon('person-plus') ?></span>
                        <div class="tl-title">Hội viên mới: Phạm Anh Tuấn</div>
                        <div class="tl-meta">32 phút trước</div>
                    </li>
                    <li>
                        <span class="tl-dot warning"><?= icon('calendar-check') ?></span>
                        <div class="tl-title">Duyệt lịch dạy 18:00</div>
                        <div class="tl-meta">1 giờ trước</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
