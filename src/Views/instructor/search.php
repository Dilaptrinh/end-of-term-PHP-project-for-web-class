<?php
require_once __DIR__ . '/../data/data.php';

$students = data_students_for_class(1);
?>
<div class="page-head">
    <div>
        <h1>Tra cứu thông tin học viên</h1>
        <p>Tìm học viên trong các lớp bạn phụ trách.</p>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/instructor/search')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="stu-q">Tên hoặc số điện thoại</label>
        <div class="position-relative">
            <input class="form-control" id="stu-q" name="q" type="search" value="" placeholder="VD: Lê Hoàng Nam hoặc 0901…" autofocus>
        </div>
    </div>
    <div class="filter-field">
        <label class="form-label" for="stu-class">Lớp học</label>
        <select class="form-select" id="stu-class" name="class_id">
            <option value="">Tất cả lớp của tôi</option>
            <option value="1">Yoga cơ bản</option>
            <option value="4">Zumba Dance</option>
            <option value="7">Yoga nâng cao</option>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="stu-status">Trạng thái</label>
        <select class="form-select" id="stu-status" name="status">
            <option value="">Tất cả</option>
            <option value="hoat_dong">Đang hoạt động</option>
            <option value="ngung_hoat_dong">Ngừng hoạt động</option>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm kiếm</button>
    </div>
</form>

<div class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <div class="text-small text-muted-2">Kết quả: <strong class="text-brand"><?= count($students) ?></strong> học viên</div>
    <div class="ms-auto text-tiny text-muted-2"><?= icon('info-circle') ?> Chỉ hiển thị học viên thuộc lớp bạn dạy.</div>
</div>

<div class="app-card">
    <div class="table-toolbar">
        <input class="form-control toolbar-search" type="search" placeholder="Lọc nhanh trong kết quả…" data-table-filter="#student-results" aria-label="Lọc nhanh học viên">
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="student-results">
            <thead>
                <tr>
                    <th>Học viên</th>
                    <th>Lớp đang học</th>
                    <th>Điện thoại</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr data-row>
                        <td>
                            <div class="user-inline">
                                <?= avatar($student['name'], 'avatar-sm') ?>
                                <span>
                                    <span class="d-block user-name"><?= e($student['name']) ?></span>
                                    <span class="d-block user-sub">HV-<?= str_pad((string) $student['member_id'], 4, '0', STR_PAD_LEFT) ?></span>
                                </span>
                            </div>
                        </td>
                        <td>Yoga cơ bản<div class="cell-sub">Thứ 2 · 18:00</div></td>
                        <td class="text-muted-2"><?= e($student['phone']) ?></td>
                        <td><?= status_badge($student['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($students), 1, 10, 'học viên') ?>
</div>
