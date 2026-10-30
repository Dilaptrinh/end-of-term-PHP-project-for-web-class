<?php
require_once __DIR__ . '/../data/data.php';

$requests = array_values(array_filter(data_availability(), static fn ($a) => $a['instructor'] === data_current_instructor_name()));
$pending = count(array_filter($requests, static fn ($r) => $r['status'] === 'cho_duyet'));
$approved = count(array_filter($requests, static fn ($r) => $r['status'] === 'da_duyet'));
$rejected = count(array_filter($requests, static fn ($r) => $r['status'] === 'tu_choi'));
$totalHours = 0;
foreach ($requests as $request) {
    $totalHours += max(0, (strtotime($request['end']) - strtotime($request['start'])) / 3600);
}
$totalHours = (int) round($totalHours);
?>
<div class="page-head">
    <div>
        <h1>Đăng ký lịch dạy</h1>
        <p>Gửi khung giờ bạn có thể dạy, quản trị viên sẽ duyệt hoặc từ chối.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-outline-primary" href="<?= e(url('/instructor/schedule')) ?>"><?= icon('calendar-week') ?> Xem lịch dạy</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Chờ duyệt</div>
            <div class="stat-value" style="color:var(--warning);"><?= $pending ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Đã duyệt</div>
            <div class="stat-value text-success"><?= $approved ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Từ chối</div>
            <div class="stat-value" style="color:var(--danger);"><?= $rejected ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Tổng giờ đăng ký</div>
            <div class="stat-value"><?= $totalHours ?> <span class="text-tiny text-muted-2 fw-500">giờ</span></div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start mb-3">
    <?= icon('info-circle') ?>
    <div class="text-small">Khung giờ không được trùng hoặc chồng lấn với lịch dạy hiện có. Sau khi admin duyệt, khung giờ sẽ xuất hiện trong lịch dạy của bạn.</div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="app-card" id="availability-form">
            <div class="app-card-header">
                <div>
                    <h2>Gửi yêu cầu mới</h2>
                    <p class="sub">Chọn ngày và khung giờ</p>
                </div>
                <span class="stat-icon ms-auto"><?= icon('calendar-plus') ?></span>
            </div>
            <div class="app-card-body">
                <form >
                    <div class="mb-3">
                        <label class="form-label" for="av-date">Ngày dạy <span class="req">*</span></label>
                        <input class="form-control" id="av-date" name="ngay_day" type="date" required min="2026-10-23">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="av-start">Bắt đầu <span class="req">*</span></label>
                            <input class="form-control" id="av-start" name="gio_bat_dau" type="time" value="07:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="av-end">Kết thúc <span class="req">*</span></label>
                            <input class="form-control" id="av-end" name="gio_ket_thuc" type="time" value="09:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="av-note">Ghi chú</label>
                        <textarea class="form-control" id="av-note" name="ghi_chu" rows="3" placeholder="VD: Có thể nhận lớp Yoga bổ sung…"></textarea>
                        <div class="form-text">Tối đa 255 ký tự.</div>
                    </div>
                    <div class="alert alert-warning d-flex gap-2 py-2 mb-3">
                        <?= icon('exclamation-triangle') ?>
                        <span class="text-tiny">Kiểm tra kỹ khung giờ trước khi gửi. Yêu cầu đã duyệt chỉ có thể hủy trước 24 giờ.</span>
                    </div>
                    <button class="btn btn-primary w-100" type="submit"><?= icon('send') ?> Gửi yêu cầu</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Yêu cầu của tôi</h2>
                    <p class="sub"><?= count($requests) ?> yêu cầu gần đây</p>
                </div>
                <select class="form-select form-select-sm ms-auto" aria-label="Lọc trạng thái" style="width:auto;">
                    <option>Tất cả trạng thái</option>
                    <option>Chờ duyệt</option>
                    <option>Đã duyệt</option>
                    <option>Từ chối</option>
                    <option>Đã hủy</option>
                </select>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
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
                                <td class="cell-strong"><?= e($request['date']) ?><div class="cell-sub"><?= e($request['weekday']) ?></div></td>
                                <td><?= e($request['start'] . ' – ' . $request['end']) ?></td>
                                <td class="text-muted-2" style="max-width:220px;"><?= e(truncate($request['note'], 48)) ?></td>
                                <td><?= status_badge($request['status']) ?></td>
                                <td class="text-muted-2"><?= e($request['approved_by']) ?></td>
                                <td class="cell-actions">
                                    <?php if (in_array($request['status'], ['cho_duyet', 'da_duyet'], true)): ?>
                                        <button class="btn btn-danger-soft btn-sm" type="button"
                                                data-confirm-action="Hủy yêu cầu lịch dạy ngày <?= e($request['date']) ?>?"
                                                data-confirm-title="Đã hủy yêu cầu">
                                            <?= icon('x-lg') ?> Hủy
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" type="button" disabled>—</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($requests), 1, 10, 'yêu cầu') ?>
        </div>
    </div>
</div>
