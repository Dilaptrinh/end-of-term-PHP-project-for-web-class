<?php
require_once __DIR__ . '/../data/data.php';

$classes = data_classes();
$instructors = data_instructors();
$openCount = count(array_filter($classes, static fn ($c) => $c['status'] === 'dang_mo'));
$fullCount = count(array_filter($classes, static fn ($c) => $c['enrolled'] >= $c['capacity']));
?>
<div class="page-head">
    <div>
        <h1>Quản lý lớp học</h1>
        <p><?= count($classes) ?> lớp · <?= $openCount ?> đang mở · <?= $fullCount ?> đã đầy.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-outline-primary" type="button" data-toast="Danh sách lớp sẽ được xuất Excel khi nối API." data-toast-title="Xuất dữ liệu"><?= icon('download') ?> Xuất Excel</button>
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#classFormModal" data-form-mode="create"><?= icon('plus-lg') ?> Thêm lớp học</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-label">Tổng lớp học</div>
            <div class="stat-value"><?= count($classes) ?></div>
            <div class="stat-foot"><?= $openCount ?> lớp đang mở</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-label">Tổng học viên đăng ký</div>
            <div class="stat-value"><?= e((string) array_sum(array_column($classes, 'enrolled'))) ?></div>
            <div class="stat-foot">Sức chứa <?= e((string) array_sum(array_column($classes, 'capacity'))) ?></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-label">Lớp đã đầy</div>
            <div class="stat-value" style="color:var(--warning);"><?= $fullCount ?></div>
            <div class="stat-foot">Cần mở thêm lớp mới</div>
        </div>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/admin/classes')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="cl-q">Tìm lớp</label>
        <input class="form-control" id="cl-q" name="q" type="search" placeholder="Tên lớp, phòng…">
    </div>
    <div class="filter-field">
        <label class="form-label" for="cl-teacher">Giáo viên</label>
        <select class="form-select" id="cl-teacher" name="instructor_id">
            <option value="">Tất cả</option>
            <?php foreach ($instructors as $instructor): ?>
                <option value="<?= $instructor['id'] ?>"><?= e($instructor['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cl-day">Thứ</label>
        <select class="form-select" id="cl-day" name="weekday">
            <option value="">Tất cả</option>
            <?php for ($d = 1; $d <= 7; $d++): ?><option value="<?= $d ?>"><?= e(weekday($d)) ?></option><?php endfor; ?>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cl-status">Trạng thái</label>
        <select class="form-select" id="cl-status" name="status">
            <option value="">Tất cả</option>
            <option value="dang_mo">Đang mở</option>
            <option value="da_dong">Đã đóng</option>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm</button>
            <a class="btn btn-ghost" href="<?= e(url('/admin/classes')) ?>">Xóa lọc</a>
        </div>
    </div>
</form>

<div class="app-card">
    <div class="table-toolbar">
        <label class="form-check form-switch mb-0 d-flex align-items-center gap-2">
            <input class="form-check-input" type="checkbox">
            <span class="form-check-label text-small">Chỉ lớp còn chỗ</span>
        </label>
        <span class="ms-auto text-tiny text-muted-2">Hiển thị <?= count($classes) ?> / <?= count($classes) ?> lớp</span>
    </div>
    <div class="app-table-wrap">
        <table class="app-table">
            <thead>
                <tr>
                    <th>Lớp học</th>
                    <th>Giáo viên</th>
                    <th>Lịch học</th>
                    <th>Phòng</th>
                    <th>Sĩ số</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th class="cell-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class):
                    $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
                ?>
                    <tr data-row>
                        <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                        <td><?= e($class['instructor']) ?></td>
                        <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                        <td><?= e($class['room']) ?></td>
                        <td style="min-width:130px;">
                            <div class="d-flex justify-content-between text-tiny mb-1"><span><?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></span><span><?= $percent ?>%</span></div>
                            <?= progress_bar($percent, $percent >= 100 ? 'red' : ($percent >= 80 ? 'amber' : '')) ?>
                        </td>
                        <td class="text-muted-2 text-tiny"><?= e($class['from']) ?><br><?= e($class['to']) ?></td>
                        <td><?= status_badge($class['status']) ?></td>
                        <td class="cell-actions">
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Thao tác"><?= icon('three-dots') ?></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= e(url('/admin/classes')) ?>" data-bs-toggle="modal" data-bs-target="#classFormModal"
                                        data-form-mode="edit"
                                        data-class-id="<?= (int) $class['id'] ?>"
                                           data-class-name="<?= e($class['name']) ?>"
                                           data-class-instructor="<?= (int) $class['giao_vien_id'] ?>"
                                           data-class-day="<?= e((string) $class['weekday']) ?>"
                                           data-class-start="<?= e($class['start']) ?>"
                                           data-class-end="<?= e($class['end']) ?>"
                                           data-class-room="<?= e($class['room']) ?>"
                                           data-class-capacity="<?= e((string) $class['capacity']) ?>"
                                           data-class-level="<?= e($class['level']) ?>"
                                           data-class-desc="<?= e($class['desc']) ?>"><?= icon('pencil') ?> Sửa lớp</a></li>
                                    <li><a class="dropdown-item" href="<?= e(url('/admin/classes')) ?>"><?= icon('people') ?> Xem học viên</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button class="dropdown-item text-danger" type="button"
                                                data-api-url="/api/classes/<?= (int) $class['id'] ?>"
                                                data-api-method="DELETE"
                                                data-api-confirm="Xóa lớp <?= e($class['name']) ?>? Dữ liệu sẽ không thể khôi phục."
                                                data-api-success-title="Đã xóa lớp"><?= icon('trash3') ?> Xóa lớp</button></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($classes), 1, 10, 'lớp học') ?>
</div>

<div class="modal fade" id="classFormModal" tabindex="-1" aria-labelledby="classFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="class-form" data-api-url="/api/classes" data-api-method="POST" data-api-success-title="Đã lưu lớp học">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="classFormTitle">Thêm lớp học</h2>
                        <p class="text-tiny text-muted-2 mb-0">Điền thông tin lớp, lịch học và giáo viên phụ trách.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="cf-name">Tên lớp <span class="req">*</span></label>
                            <input class="form-control" id="cf-name" name="ten_lop" type="text" required placeholder="VD: Yoga cơ bản" data-field="name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-level">Trình độ</label>
                            <select class="form-select" id="cf-level" name="trinh_do" data-field="level">
                                <option>Cơ bản</option>
                                <option>Trung cấp</option>
                                <option>Nâng cao</option>
                                <option>Mọi trình độ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="cf-teacher">Giáo viên <span class="req">*</span></label>
                            <select class="form-select" id="cf-teacher" name="giao_vien_id" required data-field="instructor">
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= (int) $instructor['id'] ?>"><?= e($instructor['name']) ?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="cf-room">Phòng học</label>
                            <input class="form-control" id="cf-room" name="phong_hoc" type="text" placeholder="VD: P.101" data-field="room">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-day">Thứ trong tuần <span class="req">*</span></label>
                            <select class="form-select" id="cf-day" name="thu_trong_tuan" required data-field="day">
                                <?php for ($d = 1; $d <= 7; $d++): ?><option value="<?= $d ?>"><?= e(weekday($d)) ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-start">Giờ bắt đầu <span class="req">*</span></label>
                            <input class="form-control" id="cf-start" name="gio_bat_dau" type="time" required value="18:00" data-field="start">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-end">Giờ kết thúc <span class="req">*</span></label>
                            <input class="form-control" id="cf-end" name="gio_ket_thuc" type="time" required value="19:00" data-field="end">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-capacity">Sức chứa <span class="req">*</span></label>
                            <input class="form-control" id="cf-capacity" name="suc_chua" type="number" min="1" required value="20" data-field="capacity">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-from">Ngày bắt đầu</label>
                            <input class="form-control" id="cf-from" name="ngay_bat_dau" type="date" value="2026-10-26">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cf-to">Ngày kết thúc</label>
                            <input class="form-control" id="cf-to" name="ngay_ket_thuc" type="date" value="2026-12-31">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="cf-desc">Mô tả</label>
                            <textarea class="form-control" id="cf-desc" name="mo_ta" rows="3" placeholder="Mô tả nội dung lớp học…" data-field="desc"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu lớp học</button>
                </div>
            </form>
        </div>
    </div>
</div>
