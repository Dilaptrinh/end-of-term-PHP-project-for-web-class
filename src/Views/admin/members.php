<?php
require_once __DIR__ . '/../data/data.php';

$members = data_members();
$packages = data_packages();
$active = count(array_filter($members, static fn ($m) => $m['status'] === 'hoat_dong'));
$inactive = count($members) - $active;
$activePercent = count($members) > 0 ? (int) round(100 * $active / count($members)) : 0;
$today = new DateTime('today');
$expiring = 0;
foreach (data_package_registrations() as $reg) {
    if ($reg['payment'] !== 'da_thanh_toan') {
        continue;
    }
    $end = DateTime::createFromFormat('d/m/Y', $reg['to']);
    if ($end && $end >= $today && $end <= (clone $today)->modify('+7 days')) {
        $expiring++;
    }
}
?>
<div class="page-head">
    <div>
        <h1>Quản lý hội viên</h1>
        <p><?= count($members) ?> hội viên · <?= $active ?> đang hoạt động · <?= $inactive ?> ngừng hoạt động.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-outline-primary" type="button" data-toast="Danh sách hội viên sẽ được xuất Excel khi nối API." data-toast-title="Xuất dữ liệu"><?= icon('download') ?> Xuất Excel</button>
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#memberFormModal"><?= icon('person-plus') ?> Thêm hội viên</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Tổng hội viên</div>
            <div class="stat-value"><?= count($members) ?></div>
            <div class="stat-foot">Tổng hồ sơ hội viên</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đang hoạt động</div>
            <div class="stat-value text-success"><?= $active ?></div>
            <div class="stat-foot"><?= $activePercent ?>% tổng hội viên</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Gói sắp hết hạn</div>
            <div class="stat-value" style="color:var(--warning);"><?= $expiring ?></div>
            <div class="stat-foot">Trong 7 ngày tới</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Ngừng hoạt động</div>
            <div class="stat-value text-muted-2"><?= $inactive ?></div>
            <div class="stat-foot">Theo dõi trạng thái</div>
        </div>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/admin/members')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="mb-q">Tìm hội viên</label>
        <input class="form-control" id="mb-q" name="q" type="search" placeholder="Tên, email, số điện thoại…">
    </div>
    <div class="filter-field">
        <label class="form-label" for="mb-status">Trạng thái</label>
        <select class="form-select" id="mb-status" name="status">
            <option value="">Tất cả</option>
            <option value="hoat_dong">Đang hoạt động</option>
            <option value="ngung_hoat_dong">Ngừng hoạt động</option>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="mb-package">Gói tập</label>
        <select class="form-select" id="mb-package" name="package">
            <option value="">Tất cả</option>
            <?php foreach ($packages as $package): ?><option><?= e($package['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm</button>
            <a class="btn btn-ghost" href="<?= e(url('/admin/members')) ?>">Xóa lọc</a>
        </div>
    </div>
</form>

<div class="app-card">
    <div class="table-toolbar">
        <input class="form-control toolbar-search" type="search" placeholder="Lọc nhanh trong bảng…" data-table-filter="#members-table" aria-label="Lọc nhanh hội viên">
        <span class="ms-auto text-tiny text-muted-2">Trang 1 / 1</span>
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="members-table">
            <thead>
                <tr>
                    <th>Hội viên</th>
                    <th>Liên hệ</th>
                    <th>Gói tập</th>
                    <th>Ngày tham gia</th>
                    <th>Trạng thái</th>
                    <th class="cell-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr data-row>
                        <td>
                            <div class="user-inline">
                                <?= avatar($member['name'], 'avatar-sm') ?>
                                <span>
                                    <span class="d-block user-name"><?= e($member['name']) ?></span>
                                    <span class="d-block user-sub">HV-<?= str_pad((string) $member['id'], 4, '0', STR_PAD_LEFT) ?> · <?= e($member['gender']) ?></span>
                                </span>
                            </div>
                        </td>
                        <td><?= e($member['phone']) ?><div class="cell-sub"><?= e($member['email']) ?></div></td>
                        <td><?= e($member['package']) ?></td>
                        <td class="text-muted-2"><?= e($member['joined']) ?></td>
                        <td><?= status_badge($member['status']) ?></td>
                        <td class="cell-actions">
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Thao tác"><?= icon('three-dots') ?></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" data-toast="Trang chi tiết hội viên sẽ được nối API."><?= icon('eye') ?> Xem chi tiết</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#memberFormModal"><?= icon('pencil') ?> Sửa hồ sơ</a></li>
                                    <li><a class="dropdown-item" href="#" data-toast="Đã gửi liên kết tạo tài khoản."><?= icon('person-gear') ?> Tạo tài khoản</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button class="dropdown-item text-danger" type="button" data-confirm-action="Xóa hội viên <?= e($member['name']) ?>? Hồ sơ sẽ không thể khôi phục." data-confirm-title="Đã xóa hội viên"><?= icon('trash3') ?> Xóa</button></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($members), 1, 10, 'hội viên') ?>
</div>

<div class="modal fade" id="memberFormModal" tabindex="-1" aria-labelledby="memberFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form >
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="memberFormTitle">Thông tin hội viên</h2>
                        <p class="text-tiny text-muted-2 mb-0">Tạo mới hoặc cập nhật hồ sơ hội viên.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="mf-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="mf-name" name="ho_ten" type="text" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mf-phone">Số điện thoại <span class="req">*</span></label>
                            <input class="form-control" id="mf-phone" name="sdt" type="tel" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mf-email">Email <span class="req">*</span></label>
                            <input class="form-control" id="mf-email" name="email" type="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mf-birth">Ngày sinh</label>
                            <input class="form-control" id="mf-birth" name="ngay_sinh" type="date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mf-gender">Giới tính</label>
                            <select class="form-select" id="mf-gender" name="gioi_tinh">
                                <option>Khác</option>
                                <option>Nam</option>
                                <option>Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="mf-package">Gói tập</label>
                            <select class="form-select" id="mf-package" name="goi_tap_id">
                                <option value="">Chưa chọn gói</option>
                                <?php foreach ($packages as $package): ?><option><?= e($package['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="mf-address">Địa chỉ</label>
                            <input class="form-control" id="mf-address" name="dia_chi" type="text">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="mf-note">Ghi chú</label>
                            <textarea class="form-control" id="mf-note" name="ghi_chu" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu hội viên</button>
                </div>
            </form>
        </div>
    </div>
</div>
