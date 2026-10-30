<?php
require_once __DIR__ . '/../data/data.php';

$revenue = data_revenue();
$max = max(array_column($revenue, 'value')) ?: 1;
$packages = data_packages();
$classes = data_classes();
$topPackages = array_slice($packages, 0, 4);
$topClasses = array_slice($classes, 0, 5);
$packageRegs = data_package_registrations();
$paidInvoices = count(array_filter($packageRegs, static fn ($r) => $r['payment'] === 'da_thanh_toan'));
$pendingInvoices = count(array_filter($packageRegs, static fn ($r) => $r['payment'] === 'chua_thanh_toan'));
$totalEnrolled = (int) array_sum(array_column($classes, 'enrolled'));
$totalCapacity = (int) array_sum(array_column($classes, 'capacity'));
$occupancy = $totalCapacity > 0 ? (int) round(100 * $totalEnrolled / $totalCapacity) : 0;
$openClasses = count(array_filter($classes, static fn ($c) => $c['status'] === 'dang_mo'));
$fullClasses = count(array_filter($classes, static fn ($c) => (int) $c['capacity'] > 0 && (int) $c['enrolled'] >= (int) $c['capacity']));
$currentRevenue = 0;
foreach ($revenue as $row) {
    if ($row['month'] === 'T10') {
        $currentRevenue = $row['value'];
    }
}
?>
<div class="page-head">
    <div>
        <h1>Thống kê &amp; báo cáo</h1>
        <p>Số liệu từ 01/10/2026 đến 23/10/2026.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-outline-primary" type="button" data-toast="Báo cáo PDF sẽ được tạo khi nối API." data-toast-title="Đang xuất báo cáo"><?= icon('file-earmark-pdf') ?> Xuất PDF</button>
        <button class="btn btn-primary" type="button" data-toast="Đã làm mới số liệu." data-toast-title="Làm mới"><?= icon('arrow-clockwise') ?> Làm mới</button>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/admin/reports')) ?>">
    <div class="filter-field">
        <label class="form-label" for="rp-from">Từ ngày</label>
        <input class="form-control" id="rp-from" name="from_date" type="date" value="2026-10-01">
    </div>
    <div class="filter-field">
        <label class="form-label" for="rp-to">Đến ngày</label>
        <input class="form-control" id="rp-to" name="to_date" type="date" value="2026-10-23">
    </div>
    <div class="filter-field">
        <label class="form-label" for="rp-group">Nhóm theo</label>
        <select class="form-select" id="rp-group" name="group_by">
            <option value="month">Tháng</option>
            <option value="week">Tuần</option>
            <option value="day">Ngày</option>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary" type="submit"><?= icon('funnel') ?> Áp dụng</button>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Doanh thu</div>
            <div class="stat-value" style="font-size:1.45rem;"><?= e(number_format($currentRevenue, 1, ',', '.')) ?>tr</div>
            <div class="stat-foot">Doanh thu tháng 10/2026</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Hóa đơn đã thu</div>
            <div class="stat-value"><?= $paidInvoices ?></div>
            <div class="stat-foot"><?= $pendingInvoices ?> hóa đơn chờ xác nhận</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Tỷ lệ lấp đầy</div>
            <div class="stat-value"><?= $occupancy ?>%</div>
            <div class="stat-foot"><?= $totalEnrolled ?> / <?= $totalCapacity ?> chỗ</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="app-card h-100">
            <div class="app-card-header">
                <div>
                    <h2>Doanh thu theo tháng</h2>
                    <p class="sub">Đơn vị: triệu đồng</p>
                </div>
                <div class="ms-auto chart-legend"><span class="lg"><span class="sw"></span> Doanh thu</span></div>
            </div>
            <div class="app-card-body">
                <div class="chart-bars">
                    <?php foreach ($revenue as $item): ?>
                        <div class="bar-col">
                            <span class="bar-value"><?= $item['value'] > 0 ? e((string) $item['value']) : '' ?></span>
                            <div class="bar" style="height:<?= $item['value'] > 0 ? (int) round($item['value'] / $max * 100) : 2 ?>%;"></div>
                            <span class="bar-label"><?= e($item['month']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="app-card h-100">
            <div class="app-card-header"><h2>Tình trạng lớp học</h2></div>
            <div class="app-card-body">
                <div class="text-center mb-3">
                    <div class="donut mx-auto" style="--p:<?= $occupancy ?>;width:104px;height:104px;">
                        <span class="donut-value" style="font-size:1.1rem;"><?= $occupancy ?>%<small>lấp đầy</small></span>
                    </div>
                </div>
                <div class="kv-list">
                    <div class="kv"><span>Lớp đang mở</span><span><?= $openClasses ?></span></div>
                    <div class="kv"><span>Lớp đầy</span><span class="text-warning"><?= $fullClasses ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="app-card h-100">
            <div class="app-card-header">
                <h2>Top gói tập</h2>
                <span class="badge-soft ms-auto">Theo doanh thu</span>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead><tr><th>Gói tập</th><th>Đã bán</th><th>Doanh thu</th><th>Tỷ trọng</th></tr></thead>
                    <tbody>
                        <?php foreach ($topPackages as $package):
                            $revenueValue = $package['price'] * max(1, (int) round($package['sold'] / 8));
                        ?>
                            <tr>
                                <td class="cell-strong"><?= e($package['name']) ?><div class="cell-sub"><?= e((string) $package['days']) ?> ngày</div></td>
                                <td><?= e((string) $package['sold']) ?></td>
                                <td class="cell-strong"><?= e(money($revenueValue)) ?></td>
                                <td style="min-width:110px;">
                                    <?= progress_bar(min(100, (int) round($package['sold'] / 1.3)), 'green') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="app-card h-100">
            <div class="app-card-header">
                <h2>Top lớp học</h2>
                <span class="badge-soft ms-auto">Theo sĩ số</span>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead><tr><th>Lớp học</th><th>Giáo viên</th><th>Sĩ số</th><th>Lấp đầy</th></tr></thead>
                    <tbody>
                        <?php foreach ($topClasses as $class):
                            $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
                        ?>
                            <tr>
                                <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e(weekday($class['weekday'])) ?> · <?= e($class['start']) ?></div></td>
                                <td><?= e($class['instructor']) ?></td>
                                <td><?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></td>
                                <td style="min-width:110px;"><?= progress_bar($percent, $percent >= 100 ? 'red' : ($percent >= 80 ? 'amber' : '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
