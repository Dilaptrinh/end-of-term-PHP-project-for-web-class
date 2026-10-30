<?php
require_once __DIR__ . '/../data/data.php';

$packages = data_packages();
$selling = count(array_filter($packages, static fn ($p) => $p['status'] === 'dang_ban'));
$estimatedRevenue = 0;
foreach ($packages as $package) {
    $estimatedRevenue += $package['price'] * $package['sold'];
}
$bestSeller = $packages[0] ?? ['name' => '—', 'sold' => 0];
foreach ($packages as $package) {
    if ($package['sold'] > $bestSeller['sold']) {
        $bestSeller = $package;
    }
}
$revenueLabel = $estimatedRevenue >= 1000000000
    ? number_format($estimatedRevenue / 1000000000, 2, ',', '.') . ' tỷ'
    : money($estimatedRevenue);
?>
<div class="page-head">
    <div>
        <h1>Quản lý gói tập</h1>
        <p><?= count($packages) ?> gói · <?= $selling ?> đang bán.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"><?= icon('plus-lg') ?> Thêm gói tập</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Gói đang bán</div>
            <div class="stat-value"><?= $selling ?></div>
            <div class="stat-foot"><?= count($packages) - $selling ?> gói ngừng bán</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Lượt đăng ký</div>
            <div class="stat-value"><?= e((string) array_sum(array_column($packages, 'sold'))) ?></div>
            <div class="stat-foot">Tích lũy toàn hệ thống</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Doanh thu ước tính</div>
            <div class="stat-value" style="font-size:1.35rem;"><?= e($revenueLabel) ?></div>
            <div class="stat-foot">Từ các gói đang bán</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Gói bán chạy</div>
            <div class="stat-value" style="font-size:1.05rem;"><?= e($bestSeller['name']) ?></div>
            <div class="stat-foot"><?= e((string) $bestSeller['sold']) ?> lượt đăng ký</div>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-field filter-search">
        <label class="form-label" for="pk-q">Tìm gói tập</label>
        <input class="form-control" id="pk-q" type="search" placeholder="Tên gói…" data-table-filter="#packages-table">
    </div>
    <div class="filter-field">
        <label class="form-label" for="pk-status">Trạng thái</label>
        <select class="form-select" id="pk-status">
            <option>Tất cả</option>
            <option>Đang bán</option>
            <option>Ngừng bán</option>
        </select>
    </div>
    <div class="filter-field ms-auto" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-ghost" type="button" data-toast="Đã xóa bộ lọc." data-toast-title="Bộ lọc"><?= icon('arrow-counterclockwise') ?> Đặt lại</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach (array_slice($packages, 0, 4) as $index => $package): ?>
        <div class="col-sm-6 col-xl-3">
            <div class="price-card<?= $index === 2 ? ' featured' : '' ?>">
                <?php if ($index === 2): ?><span class="ribbon">Phổ biến</span><?php endif; ?>
                <h3><?= e($package['name']) ?></h3>
                <p class="text-muted-2 text-small mb-0"><?= e((string) $package['days']) ?> ngày · <?= e((string) $package['sold']) ?> lượt</p>
                <div class="price"><?= e(number_format((float) $package['price'], 0, ',', '.')) ?><small>đ</small></div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <?= status_badge($package['status']) ?>
                    <button class="btn btn-ghost btn-sm ms-auto" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"><?= icon('pencil') ?> Sửa</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2>Danh sách gói tập</h2>
        <span class="badge-soft ms-auto"><?= count($packages) ?> gói</span>
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="packages-table">
            <thead>
                <tr><th>Gói tập</th><th>Thời hạn</th><th>Giá bán</th><th>Đã bán</th><th>Doanh thu</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                    <tr data-row>
                        <td class="cell-strong"><?= e($package['name']) ?><div class="cell-sub"><?= e(truncate($package['desc'], 46)) ?></div></td>
                        <td><?= e((string) $package['days']) ?> ngày</td>
                        <td class="cell-strong"><?= e(money($package['price'])) ?></td>
                        <td><?= e((string) $package['sold']) ?></td>
                        <td class="cell-strong"><?= e(money($package['price'] * $package['sold'])) ?></td>
                        <td><?= status_badge($package['status']) ?></td>
                        <td class="cell-actions">
                            <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"><?= icon('pencil') ?></button>
                            <button class="btn btn-ghost btn-sm text-danger" type="button" data-confirm-action="Xóa gói <?= e($package['name']) ?>? Dữ liệu sẽ không thể khôi phục." data-confirm-title="Đã xóa gói tập"><?= icon('trash3') ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($packages), 1, 10, 'gói tập') ?>
</div>

<div class="modal fade" id="packageFormModal" tabindex="-1" aria-labelledby="packageFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form >
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="packageFormTitle">Thông tin gói tập</h2>
                        <p class="text-tiny text-muted-2 mb-0">Thiết lập thời hạn, giá bán và trạng thái.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="pf-name">Tên gói <span class="req">*</span></label>
                            <input class="form-control" id="pf-name" name="ten_goi" type="text" required placeholder="VD: Standard 3 tháng">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pf-days">Thời hạn (ngày) <span class="req">*</span></label>
                            <input class="form-control" id="pf-days" name="thoi_han_ngay" type="number" min="1" value="30" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pf-price">Giá (đ) <span class="req">*</span></label>
                            <input class="form-control" id="pf-price" name="gia" type="number" min="0" step="10000" value="500000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="pf-desc">Mô tả</label>
                            <textarea class="form-control" id="pf-desc" name="mo_ta" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="pf-status">Trạng thái</label>
                            <select class="form-select" id="pf-status" name="trang_thai">
                                <option value="dang_ban">Đang bán</option>
                                <option value="ngung_ban">Ngừng bán</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu gói tập</button>
                </div>
            </form>
        </div>
    </div>
</div>
