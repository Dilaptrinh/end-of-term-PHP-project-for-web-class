<?php
require_once __DIR__ . '/../data/data.php';

$requests = data_availability();
$pending = count(array_filter($requests, static fn ($r) => $r['status'] === 'cho_duyet'));
$approved = count(array_filter($requests, static fn ($r) => $r['status'] === 'da_duyet'));
$rejected = count(array_filter($requests, static fn ($r) => $r['status'] === 'tu_choi'));
$cancelled = count(array_filter($requests, static fn ($r) => $r['status'] === 'da_huy'));
?>
<div class="page-head">
    <div>
        <h1>Duyệt lịch dạy giáo viên</h1>
        <p>Xem xét các khung giờ giáo viên đăng ký và phản hồi kịp thời.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-outline-primary" type="button" data-toast="Đã làm mới danh sách yêu cầu." data-toast-title="Làm mới"><?= icon('arrow-clockwise') ?> Làm mới</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Chờ duyệt</div>
            <div class="stat-value" style="color:var(--warning);"><?= $pending ?></div>
            <div class="stat-foot">Cần xử lý sớm</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đã duyệt</div>
            <div class="stat-value text-success"><?= $approved ?></div>
            <div class="stat-foot">Trong 30 ngày gần đây</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Từ chối</div>
            <div class="stat-value" style="color:var(--danger);"><?= $rejected ?></div>
            <div class="stat-foot">Do trùng lịch</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đã hủy</div>
            <div class="stat-value text-muted-2"><?= $cancelled ?></div>
            <div class="stat-foot">Giáo viên tự hủy</div>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-field">
        <label class="form-label" for="av-status">Trạng thái</label>
        <select class="form-select" id="av-status">
            <option>Tất cả trạng thái</option>
            <option>Chờ duyệt</option>
            <option>Đã duyệt</option>
            <option>Từ chối</option>
            <option>Đã hủy</option>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="av-teacher">Giáo viên</label>
        <select class="form-select" id="av-teacher">
            <option>Tất cả giáo viên</option>
            <?php foreach (data_instructors() as $instructor): ?><option><?= e($instructor['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="av-from">Từ ngày</label>
        <input class="form-control" id="av-from" type="date" value="2026-10-20">
    </div>
    <div class="filter-field">
        <label class="form-label" for="av-to">Đến ngày</label>
        <input class="form-control" id="av-to" type="date" value="2026-10-31">
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary" type="button" data-toast="Đã áp dụng bộ lọc." data-toast-title="Bộ lọc"><?= icon('funnel') ?> Lọc</button>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2>Yêu cầu lịch dạy</h2>
        <span class="badge-soft ms-auto"><?= $pending ?> yêu cầu mới</span>
    </div>
    <div class="app-table-wrap">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Giáo viên</th>
                    <th>Ngày dạy</th>
                    <th>Khung giờ</th>
                    <th>Ghi chú</th>
                    <th>Trạng thái</th>
                    <th>Người duyệt</th>
                    <th class="cell-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr data-row>
                        <td><div class="user-inline"><?= avatar($request['instructor'], 'avatar-sm') ?><span class="user-name"><?= e($request['instructor']) ?></span></div></td>
                        <td class="cell-strong"><?= e($request['date']) ?><div class="cell-sub"><?= e($request['weekday']) ?></div></td>
                        <td><?= e($request['start'] . ' – ' . $request['end']) ?></td>
                        <td class="text-muted-2" style="max-width:220px;"><?= e($request['note']) ?></td>
                        <td><?= status_badge($request['status']) ?></td>
                        <td class="text-muted-2 text-tiny"><?= e($request['approved_by']) ?></td>
                        <td class="cell-actions">
                            <?php if ($request['status'] === 'cho_duyet'): ?>
                                <button class="btn btn-primary btn-sm" type="button"
                                        data-confirm-action="Duyệt lịch dạy của <?= e($request['instructor']) ?> ngày <?= e($request['date']) ?>?"
                                        data-confirm-title="Đã duyệt lịch dạy"><?= icon('check-lg') ?> Duyệt</button>
                                <button class="btn btn-danger-soft btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal"
                                        data-instructor="<?= e($request['instructor']) ?>" data-date="<?= e($request['date']) ?>"><?= icon('x-lg') ?> Từ chối</button>
                            <?php else: ?>
                                <button class="btn btn-ghost btn-sm" type="button" data-toast="Chi tiết lịch dạy sẽ được nối API."><?= icon('eye') ?> Xem</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($requests), 1, 10, 'yêu cầu') ?>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form >
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="rejectModalTitle">Từ chối lịch dạy</h2>
                        <p class="text-tiny text-muted-2 mb-0">Cho giáo viên biết lý do từ chối</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex gap-2 align-items-start">
                        <?= icon('exclamation-triangle') ?>
                        <span class="text-small">Yêu cầu của <strong data-reject-instructor>Trần Thu Hà</strong> ngày <strong data-reject-date>26/10/2026</strong> sẽ bị từ chối.</span>
                    </div>
                    <label class="form-label" for="reject-reason">Lý do <span class="req">*</span></label>
                    <textarea class="form-control" id="reject-reason" name="ly_do" rows="3" required placeholder="VD: Trùng lịch dạy lớp Yoga cơ bản…"></textarea>
                    <div class="form-text">Lý do sẽ được gửi tới giáo viên.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger"><?= icon('x-lg') ?> Từ chối yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>
