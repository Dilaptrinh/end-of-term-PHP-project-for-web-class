<?php
require_once __DIR__ . '/../data/data.php';

$members = data_members();
$instructors = data_instructors();
$classes = data_classes();
$classRegs = data_class_registrations();
$packageRegs = data_package_registrations();
?>
<div class="page-head">
    <div>
        <h1>Tra cứu toàn hệ thống</h1>
        <p>Tìm kiếm nhanh hội viên, giáo viên, lớp học và giao dịch.</p>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/admin/search')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="admin-q">Từ khóa</label>
        <input class="form-control" id="admin-q" name="q" type="search" placeholder="Nhập tên, email, số điện thoại, mã lớp…" autofocus>
    </div>
    <div class="filter-field">
        <label class="form-label" for="admin-type">Loại dữ liệu</label>
        <select class="form-select" id="admin-type" name="type">
            <option value="member">Hội viên</option>
            <option value="instructor">Giáo viên</option>
            <option value="class">Lớp học</option>
            <option value="registration">Đăng ký</option>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm kiếm</button>
    </div>
</form>

<div class="app-card">
    <div class="app-card-header pb-0" style="border-bottom:none;">
        <ul class="nav nav-tabs app-tabs w-100" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#search-members" type="button" role="tab" aria-selected="true">Hội viên <span class="count"><?= count($members) ?></span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#search-instructors" type="button" role="tab" aria-selected="false">Giáo viên <span class="count"><?= count($instructors) ?></span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#search-classes" type="button" role="tab" aria-selected="false">Lớp học <span class="count"><?= count($classes) ?></span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#search-registrations" type="button" role="tab" aria-selected="false">Đăng ký <span class="count"><?= count($classRegs) ?></span></button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#search-payments" type="button" role="tab" aria-selected="false">Thanh toán <span class="count"><?= count($packageRegs) ?></span></button></li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="search-members" role="tabpanel" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table" id="search-members-table">
                    <thead><tr><th>Hội viên</th><th>Liên hệ</th><th>Gói tập</th><th>Ngày tham gia</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr></thead>
                    <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr data-row>
                            <td><div class="user-inline"><?= avatar($member['name'], 'avatar-sm') ?><span><span class="d-block user-name"><?= e($member['name']) ?></span><span class="d-block user-sub">HV-<?= str_pad((string) $member['id'], 4, '0', STR_PAD_LEFT) ?></span></span></div></td>
                            <td><?= e($member['phone']) ?><div class="cell-sub"><?= e($member['email']) ?></div></td>
                            <td><?= e($member['package']) ?></td>
                            <td class="text-muted-2"><?= e($member['joined']) ?></td>
                            <td><?= status_badge($member['status']) ?></td>
                            <td class="cell-actions"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/members')) ?>"><?= icon('eye') ?> Xem</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($members), 1, 10, 'hội viên') ?>
        </div>

        <div class="tab-pane fade" id="search-instructors" role="tabpanel" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead><tr><th>Giáo viên</th><th>Liên hệ</th><th>Chuyên môn</th><th>Lớp</th><th>Học viên</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($instructors as $instructor): ?>
                        <tr data-row>
                            <td><div class="user-inline"><?= avatar($instructor['name'], 'avatar-sm') ?><span class="user-name"><?= e($instructor['name']) ?></span></div></td>
                            <td><?= e($instructor['phone']) ?><div class="cell-sub"><?= e($instructor['email']) ?></div></td>
                            <td><?= e($instructor['specialty']) ?></td>
                            <td><?= e((string) $instructor['classes']) ?></td>
                            <td><?= e((string) $instructor['students']) ?></td>
                            <td><?= status_badge($instructor['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($instructors), 1, 10, 'giáo viên') ?>
        </div>

        <div class="tab-pane fade" id="search-classes" role="tabpanel" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead><tr><th>Lớp học</th><th>Giáo viên</th><th>Lịch</th><th>Phòng</th><th>Sĩ số</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr data-row>
                            <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                            <td><?= e($class['instructor']) ?></td>
                            <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                            <td><?= e($class['room']) ?></td>
                            <td><?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></td>
                            <td><?= status_badge($class['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($classes), 1, 10, 'lớp học') ?>
        </div>

        <div class="tab-pane fade" id="search-registrations" role="tabpanel" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead><tr><th>Hội viên</th><th>Lớp học</th><th>Ngày đăng ký</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($classRegs as $reg): ?>
                        <tr data-row>
                            <td><?= e($reg['member']) ?></td>
                            <td><?= e($reg['class']) ?></td>
                            <td class="text-muted-2"><?= e($reg['registered']) ?></td>
                            <td><?= status_badge($reg['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($classRegs), 1, 10, 'đăng ký') ?>
        </div>

        <div class="tab-pane fade" id="search-payments" role="tabpanel" tabindex="0">
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead><tr><th>Mã GD</th><th>Hội viên</th><th>Gói tập</th><th>Số tiền</th><th>Phương thức</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($packageRegs as $reg): ?>
                        <tr data-row>
                            <td class="cell-strong">GD-<?= e((string) $reg['id']) ?></td>
                            <td><?= e($reg['member']) ?></td>
                            <td><?= e($reg['package']) ?></td>
                            <td class="cell-strong"><?= e(money($reg['price'])) ?></td>
                            <td class="text-muted-2"><?= e($reg['method']) ?></td>
                            <td><?= status_badge($reg['payment']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($packageRegs), 1, 10, 'giao dịch') ?>
        </div>
    </div>
</div>
