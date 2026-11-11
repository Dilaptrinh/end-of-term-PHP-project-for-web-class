<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    $action = (string) ($_GET['action'] ?? '');
    $id = (int) ($_GET['id'] ?? 0);
    $db = common_db();

    if ($action === 'delete') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }
        $changed = admin_class_delete($id);
        json_response([
            'success' => $changed,
            'message' => $changed ? 'Đã xóa lớp học.' : 'Không tìm thấy lớp học.',
        ], $changed ? 200 : 404);
    }

    if ($action !== 'save') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
    }

    $input = $_POST;

    $errors = [];
    foreach (['ten_lop', 'gio_bat_dau', 'gio_ket_thuc', 'ngay_bat_dau', 'ngay_ket_thuc'] as $field) {
        if (trim((string) ($input[$field] ?? '')) === '') {
            $errors[$field] = 'Trường này bắt buộc.';
        }
    }
    if ((int) ($input['giao_vien_id'] ?? 0) <= 0) {
        $errors['giao_vien_id'] = 'Vui lòng chọn giáo viên.';
    }
    if ((int) ($input['suc_chua'] ?? 0) < 1) {
        $errors['suc_chua'] = 'Sức chứa không hợp lệ.';
    }
    if ($errors) {
        json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
    }

    $values = [
        trim((string) $input['ten_lop']),
        (int) $input['giao_vien_id'],
        (int) ($input['thu_trong_tuan'] ?? 1),
        (string) $input['gio_bat_dau'],
        (string) $input['gio_ket_thuc'],
        (string) $input['ngay_bat_dau'],
        (string) $input['ngay_ket_thuc'],
        (int) $input['suc_chua'],
        ($input['phong_hoc'] ?? '') !== '' ? (string) $input['phong_hoc'] : null,
        ($input['mo_ta'] ?? '') !== '' ? (string) $input['mo_ta'] : null,
        ($input['trinh_do'] ?? '') !== '' ? (string) $input['trinh_do'] : null,
        (string) ($input['trang_thai'] ?? 'dang_mo'),
    ];
    $savedId = admin_class_save($values, $id);

    if ($id === 0) {
        json_response(['success' => true, 'data' => ['id' => $savedId], 'message' => 'Đã tạo lớp học.'], 201);
    }
    if ($savedId === 0) {
        json_response(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
    }

    json_response(['success' => true, 'message' => 'Đã cập nhật lớp học.']);
}

require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'classes';
$pageTitle = 'Quản lý lớp học';
$pageScripts = [];

$allClasses = data_classes();
$classId = trim((string) ($_GET['class_id'] ?? ''));
$classes = $allClasses;
if ($classId !== '' && ctype_digit($classId)) {
    $classes = array_values(array_filter($allClasses, static function (array $class) use ($classId): bool {
        return (string) $class['id'] === $classId;
    }));
}
$instructors = data_instructors();
$openCount = 0;
$fullCount = 0;
foreach ($allClasses as $class) {
    if ($class['status'] === 'dang_mo') {
        $openCount++;
    }
    if ($class['enrolled'] >= $class['capacity']) {
        $fullCount++;
    }
}

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
        <h1>Quản lý lớp học</h1>
        <p><?= count($classes) ?> lớp · <?= $openCount ?> đang mở · <?= $fullCount ?> đã đầy.</p>
    </div>
    <div class="page-head-actions">
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
        <label class="form-label" for="cl-id">Tìm theo mã lớp</label>
        <input class="form-control" id="cl-id" name="class_id" type="search" inputmode="numeric" value="<?= e($classId) ?>" placeholder="Nhập mã lớp...">
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
    <div class="app-table-wrap">
        <table class="app-table" id="classes-table">
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
                        <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub">Mã lớp: <?= (int) $class['id'] ?> · <?= e($class['level']) ?></div></td>
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
                                                data-delete-id="<?= (int) $class['id'] ?>"
                                                data-delete-name="<?= e($class['name']) ?>"><?= icon('trash3') ?> Xóa lớp</button></li>
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
            <form id="class-form">
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
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/admin/classes.js')) ?>?v=1"></script>
<?php require $partials . '/foot.php'; ?>