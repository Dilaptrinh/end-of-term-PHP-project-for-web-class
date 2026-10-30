<?php
require_once __DIR__ . '/../data/data.php';

$accounts = data_accounts();
$locked = count(array_filter($accounts, static fn ($a) => $a['status'] === 'bi_khoa'));
$activeAccounts = count($accounts) - $locked;
$adminCount = count(array_filter($accounts, static fn ($a) => $a['role'] === 'admin'));
$activePercent = count($accounts) > 0 ? (int) round(100 * $activeAccounts / count($accounts)) : 0;
?>
<div class="page-head">
    <div>
        <h1>Quản lý tài khoản</h1>
        <p><?= count($accounts) ?> tài khoản · <?= $locked ?> đang bị khóa.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#accountFormModal"><?= icon('person-plus') ?> Thêm tài khoản</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Tổng tài khoản</div>
            <div class="stat-value"><?= count($accounts) ?></div>
            <div class="stat-foot">Toàn hệ thống</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đang hoạt động</div>
            <div class="stat-value text-success"><?= $activeAccounts ?></div>
            <div class="stat-foot"><?= $activePercent ?>% tài khoản</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Đang khóa</div>
            <div class="stat-value" style="color:var(--danger);"><?= $locked ?></div>
            <div class="stat-foot">Có thể mở khóa</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Quản trị viên</div>
            <div class="stat-value"><?= $adminCount ?></div>
            <div class="stat-foot">Toàn quyền hệ thống</div>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-field filter-search">
        <label class="form-label" for="ac-q">Tìm tài khoản</label>
        <input class="form-control" id="ac-q" type="search" placeholder="Tên đăng nhập, họ tên…" data-table-filter="#accounts-table">
    </div>
    <div class="filter-field">
        <label class="form-label" for="ac-role">Vai trò</label>
        <select class="form-select" id="ac-role">
            <option>Tất cả vai trò</option>
            <option>Quản trị viên</option>
            <option>Giáo viên</option>
            <option>Hội viên</option>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="ac-status">Trạng thái</label>
        <select class="form-select" id="ac-status">
            <option>Tất cả</option>
            <option>Đang hoạt động</option>
            <option>Đã khóa</option>
        </select>
    </div>
    <div class="filter-field ms-auto" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-ghost" type="button" data-toast="Đã xóa bộ lọc." data-toast-title="Bộ lọc"><?= icon('arrow-counterclockwise') ?> Đặt lại</button>
    </div>
</div>

<div class="app-card">
    <div class="app-table-wrap">
        <table class="app-table" id="accounts-table">
            <thead>
                <tr>
                    <th>Tài khoản</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th>Đăng nhập cuối</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th class="cell-actions">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $account): ?>
                    <tr data-row>
                        <td>
                            <div class="user-inline">
                                <?= avatar($account['name'], 'avatar-sm') ?>
                                <span>
                                    <span class="d-block user-name"><?= e($account['name']) ?></span>
                                    <span class="d-block user-sub">TK-<?= str_pad((string) $account['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                </span>
                            </div>
                        </td>
                        <td class="cell-strong"><?= e($account['username']) ?></td>
                        <td>
                            <?php
                            $roleVariant = $account['role'] === 'admin' ? 'primary' : ($account['role'] === 'instructor' ? 'success' : 'muted');
                            ?>
                            <span class="badge-status <?= $roleVariant ?> no-dot"><?= icon(role_icon($account['role'])) ?> <?= e(role_name($account['role'])) ?></span>
                        </td>
                        <td class="text-muted-2"><?= e($account['last_login']) ?></td>
                        <td class="text-muted-2"><?= e($account['created']) ?></td>
                        <td><?= status_badge($account['status']) ?></td>
                        <td class="cell-actions">
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Thao tác"><?= icon('three-dots') ?></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" data-toast="Trang chi tiết tài khoản sẽ được nối API."><?= icon('eye') ?> Xem chi tiết</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountFormModal"><?= icon('pencil') ?> Sửa tài khoản</a></li>
                                    <li><a class="dropdown-item" href="#" data-toast="Đã gửi mật khẩu tạm thời." data-toast-title="Đặt lại mật khẩu"><?= icon('key') ?> Đặt lại mật khẩu</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php if ($account['status'] === 'bi_khoa'): ?>
                                        <li><button class="dropdown-item text-success" type="button" data-confirm-action="Mở khóa tài khoản <?= e($account['username']) ?>?" data-confirm-title="Đã mở khóa"><?= icon('unlock') ?> Mở khóa</button></li>
                                    <?php else: ?>
                                        <li><button class="dropdown-item text-warning" type="button" data-confirm-action="Khóa tài khoản <?= e($account['username']) ?>?" data-confirm-title="Đã khóa tài khoản"><?= icon('lock') ?> Khóa tài khoản</button></li>
                                    <?php endif; ?>
                                    <li><button class="dropdown-item text-danger" type="button" data-confirm-action="Xóa tài khoản <?= e($account['username']) ?>? Dữ liệu sẽ không thể khôi phục." data-confirm-title="Đã xóa tài khoản"><?= icon('trash3') ?> Xóa</button></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($accounts), 1, 10, 'tài khoản') ?>
</div>

<div class="modal fade" id="accountFormModal" tabindex="-1" aria-labelledby="accountFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form >
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="accountFormTitle">Thông tin tài khoản</h2>
                        <p class="text-tiny text-muted-2 mb-0">Tạo mới hoặc cập nhật tài khoản đăng nhập.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="af-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="af-name" name="ho_ten" type="text" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="af-username">Tên đăng nhập <span class="req">*</span></label>
                            <input class="form-control" id="af-username" name="ten_dang_nhap" type="text" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="af-role">Vai trò <span class="req">*</span></label>
                            <select class="form-select" id="af-role" name="role_id" required>
                                <option value="3">Hội viên</option>
                                <option value="2">Giáo viên</option>
                                <option value="1">Quản trị viên</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="af-password">Mật khẩu ban đầu <span class="req">*</span></label>
                            <input class="form-control" id="af-password" name="mat_khau" type="password" required minlength="6">
                            <div class="form-text">Người dùng nên đổi mật khẩu sau lần đăng nhập đầu tiên.</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" id="af-active" name="trang_thai" type="checkbox" checked>
                                <label class="form-check-label" for="af-active">Kích hoạt tài khoản ngay</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu tài khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>
