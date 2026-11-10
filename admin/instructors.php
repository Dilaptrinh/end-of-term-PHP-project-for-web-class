<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$requestAction = (string) ($_GET['action'] ?? '');

if (($requestMethod === 'GET' && $requestAction === 'detail') || $requestMethod === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    $db = common_db();
    $id = (int) ($_GET['id'] ?? 0);

    if ($requestMethod === 'GET') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }
        $row = admin_instructor_find($id);
        json_response($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Không tìm thấy giáo viên.'], $row ? 200 : 404);
    }

    if ($requestAction === 'delete') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }
        $changed = admin_instructor_delete($id);
        json_response([
            'success' => $changed,
            'message' => $changed ? 'Đã xóa giáo viên.' : 'Không tìm thấy giáo viên.',
        ], $changed ? 200 : 404);
    }

    if ($requestAction !== 'save') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
    }

    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $name = trim((string) ($input['ho_ten'] ?? ''));
    $phone = trim((string) ($input['sdt'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $specialtyIds = array_values(array_filter(array_map('intval', (array) ($input['chuyen_mon_ids'] ?? []))));
    $intro = trim((string) ($input['mo_ta'] ?? ''));
    $status = (string) ($input['trang_thai'] ?? 'dang_lam');

    $errors = [];
    if (mb_strlen($name) < 2) {
        $errors['ho_ten'] = 'Họ tên không hợp lệ.';
    }
    if ($phone === '') {
        $errors['sdt'] = 'Số điện thoại không hợp lệ.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    if ($errors) {
        json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
    }

    if ($id === 0) {
        $username = trim((string) ($input['ten_dang_nhap'] ?? ''));
        $password = (string) ($input['mat_khau'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
            json_response(['success' => false, 'message' => 'Tên đăng nhập không hợp lệ.', 'errors' => ['ten_dang_nhap' => 'Tên đăng nhập 3–50 ký tự, chỉ chữ/số/gạch dưới.']], 422);
        }
        if (strlen($password) < 6) {
            json_response(['success' => false, 'message' => 'Mật khẩu quá ngắn.', 'errors' => ['mat_khau' => 'Mật khẩu tối thiểu 6 ký tự.']], 422);
        }

        if (admin_username_exists($username)) {
            json_response(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.', 'errors' => ['ten_dang_nhap' => 'Tên đăng nhập đã tồn tại.']], 422);
        }

        $newId = admin_instructor_create($username, $password, $name, $phone, $email, $specialtyIds, $intro, $status);
        json_response(['success' => true, 'data' => ['id' => $newId], 'message' => 'Đã tạo giáo viên.'], 201);
    }

    $password = (string) ($input['mat_khau'] ?? '');
    if (!admin_instructor_update($id, $name, $phone, $email, $specialtyIds, $intro, $status, $password)) {
        json_response(['success' => false, 'message' => 'Không tìm thấy giáo viên.'], 404);
    }

    json_response(['success' => true, 'message' => 'Đã cập nhật giáo viên.']);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'instructors';
$pageTitle = 'Quản lý giáo viên';
$pageScripts = [];

$instructors = data_instructors();
$active = count(array_filter($instructors, static fn ($i) => $i['status'] === 'hoat_dong'));
$classes = data_classes();
$openClasses = count(array_filter($classes, static fn ($c) => $c['status'] === 'dang_mo'));
$studentCount = count(array_unique(array_column(data_class_registrations(), 'member')));
$pendingSlots = count(array_filter(data_availability(), static fn ($r) => $r['status'] === 'cho_duyet'));
$specialties = data_specialties();

$partials = __DIR__ . '/../common/partials';

require $partials . '/head.php';
?>
<div class="app-shell">
    <?php require $partials . '/sidebar.php'; ?>

    <div class="app-main">
        <?php require $partials . '/topbar.php'; ?>

        <main class="app-content" id="main-content">
            <?php require $partials . '/flash.php'; ?>
<div class="page-head">
    <div>
        <h1>Quản lý giáo viên</h1>
        <p><?= count($instructors) ?> giáo viên · <?= $active ?> đang hoạt động.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#instructorFormModal"><?= icon('person-plus') ?> Thêm giáo viên</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Tổng giáo viên</div>
            <div class="stat-value"><?= count($instructors) ?></div>
            <div class="stat-foot"><?= $active ?> đang hoạt động</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Lớp đang phụ trách</div>
            <div class="stat-value"><?= count($classes) ?></div>
            <div class="stat-foot"><?= $openClasses ?> lớp đang mở</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Tổng học viên</div>
            <div class="stat-value"><?= $studentCount ?></div>
            <div class="stat-foot">Không tính trùng lớp</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-label">Chờ duyệt lịch dạy</div>
            <div class="stat-value" style="color:var(--warning);"><?= $pendingSlots ?></div>
            <div class="stat-foot"><a href="<?= e(url('/admin/availability')) ?>">Đi tới duyệt <?= icon('arrow-right') ?></a></div>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-field filter-search">
        <label class="form-label" for="ins-q">Tìm giáo viên</label>
        <input class="form-control" id="ins-q" type="search" placeholder="Tên, email, chuyên môn…" data-table-filter="#instructors-table">
    </div>
    <div class="filter-field">
        <label class="form-label" for="ins-status-filter">Trạng thái</label>
        <select class="form-select" id="ins-status-filter">
            <option>Tất cả</option>
            <option>Đang hoạt động</option>
            <option>Ngừng hoạt động</option>
        </select>
    </div>
    <div class="filter-field ms-auto" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-ghost" type="button" data-reload-page><?= icon('arrow-counterclockwise') ?> Đặt lại</button>
    </div>
</div>

<div class="app-card">
    <div class="app-card-header">
        <h2>Danh sách giáo viên</h2>
        <span class="badge-soft ms-auto"><?= count($instructors) ?> giáo viên</span>
    </div>
    <div class="app-table-wrap">
        <table class="app-table" id="instructors-table">
            <thead><tr><th>Giáo viên</th><th>Liên hệ</th><th>Chuyên môn</th><th>Tài khoản</th><th>Lớp</th><th>Học viên</th><th>Trạng thái</th><th class="cell-actions">Thao tác</th></tr></thead>
            <tbody>
                <?php foreach ($instructors as $instructor): ?>
                    <tr data-row>
                        <td><div class="user-inline"><?= avatar($instructor['name'], 'avatar-sm') ?><span class="user-name"><?= e($instructor['name']) ?></span></div></td>
                        <td><?= e($instructor['phone']) ?><div class="cell-sub"><?= e($instructor['email']) ?></div></td>
                        <td><?= e($instructor['specialty']) ?></td>
                        <td class="text-muted-2"><?= e($instructor['account']) ?></td>
                        <td><?= e((string) $instructor['classes']) ?></td>
                        <td><?= e((string) $instructor['students']) ?></td>
                        <td><?= status_badge($instructor['status']) ?></td>
                        <td class="cell-actions">
                            <button class="btn btn-ghost btn-sm" type="button" data-instructor-edit="<?= e((string) $instructor['id']) ?>" aria-label="Sửa"><?= icon('pencil') ?></button>
                            <button class="btn btn-ghost btn-sm text-danger" type="button" data-instructor-delete="<?= e((string) $instructor['id']) ?>" aria-label="Xóa"><?= icon('trash3') ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="instructorFormModal" tabindex="-1" aria-labelledby="instructorFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="instructor-form">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="instructorFormTitle">Thông tin giáo viên</h2>
                        <p class="text-tiny text-muted-2 mb-0">Hồ sơ và tài khoản đăng nhập</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="if-id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="if-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="if-name" name="ho_ten" type="text" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="if-phone">Số điện thoại <span class="req">*</span></label>
                            <input class="form-control" id="if-phone" name="sdt" type="tel" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="if-email">Email <span class="req">*</span></label>
                            <input class="form-control" id="if-email" name="email" type="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chuyên môn</label>
                            <select class="form-select" id="if-specialty" name="chuyen_mon_ids[]">
                                <?php foreach ($specialties as $specialty): ?>
                                    <option value="<?= (int) $specialty['id'] ?>"><?= e($specialty['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="if-username">Tên đăng nhập <span class="req">*</span></label>
                            <input class="form-control" id="if-username" name="ten_dang_nhap" type="text" required>
                            <div class="form-text">Dùng để đăng nhập cổng giáo viên.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="if-password">Mật khẩu ban đầu</label>
                            <input class="form-control" id="if-password" name="mat_khau" type="password" placeholder="Tối thiểu 6 ký tự">
                            <div class="form-text">Bắt buộc khi thêm mới; để trống khi sửa nếu không đổi.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="if-desc">Giới thiệu</label>
                            <textarea class="form-control" id="if-desc" name="mo_ta" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><?= icon('save') ?> Lưu giáo viên</button>
                </div>
            </form>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php require $partials . '/foot.php'; ?>
<script src="<?= e(url('/common/assets/js/admin/instructors.js')) ?>?v=2"></script>