<?php
require_once __DIR__ . '/../data/data.php';

$packages = data_customer_packages();
$classHistory = data_customer_class_history();
?>
<div class="page-head">
    <div>
        <h1>Lịch sử hoạt động</h1>
        <p>Theo dõi gói tập và lịch sử đăng ký lớp học của bạn.</p>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header pb-0" style="border-bottom:none;">
        <ul class="nav nav-tabs app-tabs w-100" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-packages" data-bs-toggle="tab" data-bs-target="#pane-packages" type="button" role="tab" aria-selected="true">
                    <?= icon('box-seam') ?> Gói tập <span class="count"><?= count($packages) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-classes" data-bs-toggle="tab" data-bs-target="#pane-classes" type="button" role="tab" aria-selected="false">
                    <?= icon('journal-bookmark') ?> Lớp học <span class="count"><?= count($classHistory) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="pane-packages" role="tabpanel" aria-labelledby="tab-packages" tabindex="0">
            <div class="table-toolbar">
                <div class="input-group" style="max-width:260px;">
                    <span class="input-group-text bg-white border-end-0"><?= icon('calendar3') ?></span>
                    <input class="form-control border-start-0" type="text" placeholder="Từ ngày — đến ngày" aria-label="Lọc theo ngày">
                </div>
                <select class="form-select" aria-label="Trạng thái thanh toán">
                    <option>Tất cả trạng thái</option>
                    <option>Đã thanh toán</option>
                    <option>Chưa thanh toán</option>
                </select>
                <button class="btn btn-outline-primary btn-sm ms-auto" type="button"><?= icon('funnel') ?> Lọc</button>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Gói tập</th>
                            <th>Thời hạn</th>
                            <th>Giá</th>
                            <th>Thanh toán</th>
                            <th>Phương thức</th>
                            <th>Ngày thanh toán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $row): ?>
                            <tr data-row>
                                <td class="cell-strong"><?= e($row['name']) ?></td>
                                <td><?= e($row['from']) ?> → <?= e($row['to']) ?></td>
                                <td class="cell-strong"><?= e(money($row['price'])) ?></td>
                                <td><?= status_badge($row['payment']) ?></td>
                                <td><?= e($row['method']) ?></td>
                                <td class="text-muted-2"><?= e($row['paid_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(3, 1, 10, 'giao dịch') ?>
        </div>

        <div class="tab-pane fade" id="pane-classes" role="tabpanel" aria-labelledby="tab-classes" tabindex="0">
            <div class="table-toolbar">
                <input class="form-control toolbar-search" type="search" placeholder="Tìm lớp học…" aria-label="Tìm lớp học">
                <select class="form-select" aria-label="Trạng thái lớp">
                    <option>Tất cả trạng thái</option>
                    <option>Đã đăng ký</option>
                    <option>Đã hủy</option>
                </select>
                <button class="btn btn-outline-primary btn-sm ms-auto" type="button"><?= icon('funnel') ?> Lọc</button>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Lớp học</th>
                            <th>Giáo viên</th>
                            <th>Lịch học</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th class="cell-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classHistory as $row): ?>
                            <tr data-row>
                                <td class="cell-strong"><?= e($row['class']) ?></td>
                                <td><?= e($row['teacher']) ?></td>
                                <td class="text-muted-2"><?= e($row['schedule']) ?></td>
                                <td class="text-muted-2"><?= e($row['registered']) ?></td>
                                <td><?= status_badge($row['status']) ?></td>
                                <td class="cell-actions">
                                    <?php if ($row['status'] === 'dang_ky'): ?>
                                        <button class="btn btn-danger-soft btn-sm" type="button" data-confirm-action="Hủy đăng ký lớp <?= e($row['class']) ?>?">Hủy</button>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" type="button" disabled>—</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(4, 1, 10, 'lớp học') ?>
        </div>

    </div>
</div>
