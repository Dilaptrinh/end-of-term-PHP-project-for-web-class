<?php
/*
 * File: admin/classes.php
 * Chức năng: quản lý lớp học (xem, thêm, sửa, xóa).
 *
 * File này vừa là TRANG HTML vừa là API cho AJAX:
 * 1. POST action=save   -> thêm/sửa lớp, trả JSON cho classes.js.
 * 2. POST action=delete -> xóa lớp, trả JSON cho classes.js.
 * 3. GET  action=members -> trả JSON chi tiết lớp cho modal.
 * 4. GET bình thường     -> render trang danh sách lớp.
 */
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

// Tìm một lớp theo ID trong danh sách lớp đang có.
// Trả về mảng dữ liệu lớp, hoặc null nếu không tìm thấy.
function class_find(int $id): ?array
{
    foreach (data_classes() as $class) {
        if ((int) $class['id'] === $id) {
            return $class;
        }
    }
    return null;
}

/*
 * PHẦN API CHO AJAX (POST)
 * classes.js gửi POST lên chính file này với:
 * - ?action=save&id=0   -> thêm lớp mới
 * - ?action=save&id=... -> sửa lớp
 * - ?action=delete&id=.. -> xóa lớp
 * Server luôn trả JSON để JS cập nhật bảng, không reload trang.
 */
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // Chỉ admin đã đăng nhập mới được thao tác.
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    $action = (string) ($_GET['action'] ?? '');
    $id = (int) ($_GET['id'] ?? 0);
    $db = common_db();

    // ---------------- XÓA LỚP ----------------
    if ($action === 'delete') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }

        $changed = admin_class_delete($id);

        // mode=remove: classes.js biết phải xóa dòng khỏi bảng.
        json_response([
            'success' => $changed,
            'message' => $changed ? 'Đã xóa lớp học.' : 'Không tìm thấy lớp học.',
            'id' => $id,
        ], $changed ? 200 : 404);
    }

    // Chỉ còn action=save, action khác thì báo lỗi.
    if ($action !== 'save') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
    }

    $input = $_POST;

    // ---------------- KIỂM TRA DỮ LIỆU ----------------
    // Mảng $errors chứa lỗi theo từng trường. Có lỗi thì trả JSON 422.
    $errors = [];
    // Các trường bắt buộc không được rỗng.
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

    // Thứ trong tuần phải từ 1 (Thứ 2) đến 7 (Chủ nhật).
    $weekday = (int) ($input['thu_trong_tuan'] ?? 0);
    if ($weekday < 1 || $weekday > 7) {
        $errors['thu_trong_tuan'] = 'Thứ trong tuần không hợp lệ.';
    }

    // Giờ kết thúc phải sau giờ bắt đầu (so sánh chuỗi dạng HH:mm).
    $startTime = (string) ($input['gio_bat_dau'] ?? '');
    $endTime = (string) ($input['gio_ket_thuc'] ?? '');
    if ($startTime !== '' && $endTime !== '' && $startTime >= $endTime) {
        $errors['gio_ket_thuc'] = 'Giờ kết thúc phải sau giờ bắt đầu.';
    }

    // Ngày kết thúc phải sau ngày bắt đầu (so sánh chuỗi dạng YYYY-MM-DD).
    $startDateValue = (string) ($input['ngay_bat_dau'] ?? '');
    $endDateValue = (string) ($input['ngay_ket_thuc'] ?? '');
    if ($startDateValue !== '' && $endDateValue !== '' && $startDateValue > $endDateValue) {
        $errors['ngay_ket_thuc'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
    }

    // Gói tối thiểu chỉ được là basic / standard / premium.
    $requiredPackage = (string) ($input['goi_tap_toi_thieu'] ?? 'basic');
    if (!in_array($requiredPackage, ['basic', 'standard', 'premium'], true)) {
        $errors['goi_tap_toi_thieu'] = 'Gói tối thiểu không hợp lệ.';
    }

    // Trạng thái lớp chỉ được nằm trong danh sách cho phép.
    $statusValue = (string) ($input['trang_thai'] ?? 'dang_mo');
    if (!in_array($statusValue, ['dang_mo', 'cho_duyet', 'tu_choi', 'da_dong', 'da_huy'], true)) {
        $errors['trang_thai'] = 'Trạng thái lớp không hợp lệ.';
    }

    // Kiểm tra giáo viên có tồn tại và chưa bị xóa mềm.
    $teacherStmt = $db->prepare('SELECT id FROM giao_vien WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $teacherId = (int) ($input['giao_vien_id'] ?? 0);
    $teacherStmt->bind_param('i', $teacherId);
    $teacherStmt->execute();
    $teacherExists = $teacherStmt->get_result()->fetch_assoc();
    $teacherStmt->close();
    if (!$teacherExists) {
        $errors['giao_vien_id'] = 'Giáo viên không tồn tại.';
    }

    // Nếu có bất kỳ lỗi nào thì dừng và trả danh sách lỗi.
    if ($errors) {
        json_response(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
    }

    // Các giá trị đưa vào admin_class_save().
    // Thứ tự phải khớp với danh sách cột trong admin_class_save() ở queries.php.
    $values = [
        trim((string) $input['ten_lop']),
        $teacherId,
        $weekday,
        $startTime,
        $endTime,
        $startDateValue,
        $endDateValue,
        (int) $input['suc_chua'],
        ($input['phong_hoc'] ?? '') !== '' ? (string) $input['phong_hoc'] : null,
        ($input['mo_ta'] ?? '') !== '' ? (string) $input['mo_ta'] : null,
        ($input['trinh_do'] ?? '') !== '' ? (string) $input['trinh_do'] : null,
        $requiredPackage,
        $statusValue,
    ];

    // id = 0 là thêm mới, id > 0 là cập nhật.
    $savedId = admin_class_save($values, $id);

    // Trường hợp THÊM MỚI.
    if ($id === 0) {
        $class = class_find($savedId);

        json_response([
            'success' => true,
            'message' => 'Đã tạo lớp học.',
            'data' => $class,
        ], 201);
    }

    // Trường hợp CẬP NHẬT nhưng không tìm thấy lớp.
    if ($savedId === 0) {
        json_response(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
    }

    // Trường hợp CẬP NHẬT thành công.
    $class = class_find($savedId);

    json_response([
        'success' => true,
        'message' => 'Đã cập nhật lớp học.',
        'data' => $class,
    ]);
}

/*
 * API GET: trả JSON chi tiết một lớp + danh sách học viên.
 * classes.js dùng dữ liệu này để đổ vào modal "Xem chi tiết".
 */
$requestAction = (string) ($_GET['action'] ?? '');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $requestAction === 'members') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    // Lấy thông tin lớp.
    $detail = admin_class_detail((int) ($_GET['id'] ?? 0));
    if (!$detail) {
        json_response(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
    }

    // Trả về cả thông tin lớp và danh sách học viên đã đăng ký.
    json_response([
        'success' => true,
        'data' => [
            'class' => $detail,
            'members' => admin_class_members((int) $detail['id']),
        ],
    ]);
}

// Từ đây trở xuống là phần render trang HTML bình thường.
// Chặn người không phải admin.
require_role('admin');

$menu = require __DIR__ . '/../common/menus/admin.php';

$appRole = 'admin';
$activeMenu = 'classes';
$pageTitle = 'Quản lý lớp học';
$pageScripts = [];

// Lấy toàn bộ lớp học.
$allClasses = data_classes();

// Lọc theo mã lớp nếu trên URL có ?class_id=...
// ctype_digit() kiểm tra chuỗi chỉ gồm chữ số.
$classId = trim((string) ($_GET['class_id'] ?? ''));
$classes = $allClasses;
if ($classId !== '' && ctype_digit($classId)) {
    $classes = array_values(array_filter($allClasses, static function (array $class) use ($classId): bool {
        return (string) $class['id'] === $classId;
    }));
}

// Danh sách giáo viên để đổ vào select trong form thêm/sửa.
$instructors = data_instructors();

$partials = __DIR__ . '/../common/partials';

require $partials . '/head.php';
?>
<div class="app-shell">
    <?php require $partials . '/sidebar.php'; ?>

    <div class="app-main">
        <?php require $partials . '/topbar.php'; ?>

        <main class="app-content" id="main-content">
            <?php require $partials . '/flash.php'; ?>
<?php // Tiêu đề trang + nút mở modal thêm lớp. ?>
<div class="page-head">
    <div>
        <h1>Quản lý lớp học</h1>
        <p>Danh sách toàn bộ lớp học trong hệ thống.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#classFormModal" data-form-mode="create"><?= icon('plus-lg') ?> Thêm lớp học</button>
    </div>
</div>

<?php // Form lọc theo mã lớp (submit bằng GET, tải lại trang với ?class_id=...). ?>
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

<?php // Bảng danh sách lớp. Mỗi lớp được in ra một dòng <tr>. ?>
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
            <tbody id="classes-body">
                <?php if (!$classes): ?>
                    <tr data-empty-row><td colspan="8" class="text-center text-muted-2 py-4">Chưa có lớp học nào.</td></tr>
                <?php endif; ?>
                <?php foreach ($classes as $class):
                    $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
                ?>
                    <tr data-row data-class-row="<?= (int) $class['id'] ?>">
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
                                    <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#classDetailModal"
                                                data-class-id="<?= (int) $class['id'] ?>"
                                                data-class-name="<?= e($class['name']) ?>"><?= icon('info-circle') ?> Xem chi tiết</button></li>
                                    <li><hr class="dropdown-divider"></li>
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
                                           data-class-package="<?= e($class['required_package']) ?>"
                                           data-class-status="<?= e($class['status']) ?>"
                                           data-class-desc="<?= e($class['desc']) ?>"><?= icon('pencil') ?> Sửa lớp</a></li>
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

<?php // Modal dùng chung cho cả THÊM và SỬA lớp. classes.js đổ dữ liệu khi bấm Sửa. ?>
<div class="modal fade" id="classFormModal" tabindex="-1" aria-labelledby="classFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="class-form">
                <input type="hidden" name="trang_thai" id="cf-status" value="dang_mo">
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
                        <div class="col-md-4">
                            <label class="form-label" for="cf-package">Gói tối thiểu</label>
                            <select class="form-select" id="cf-package" name="goi_tap_toi_thieu" data-field="package">
                                <option value="basic">Basic</option>
                                <option value="standard">Standard</option>
                                <option value="premium">Premium</option>
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

<?php // Modal xem chi tiết. classes.js gọi ?action=members rồi đổ JSON vào đây. ?>
<div class="modal fade" id="classDetailModal" tabindex="-1" aria-labelledby="classDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="classDetailTitle">Chi tiết lớp học</h2>
                    <p class="text-tiny text-muted-2 mb-0" id="classDetailSubtitle">Thông tin lớp và danh sách học viên đã đăng ký.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div id="classDetailLoading" class="text-center text-muted-2 py-4">
                    <?= icon('arrow-repeat') ?> Đang tải thông tin lớp học...
                </div>
                <div id="classDetailError" class="alert alert-danger d-none" role="alert"></div>
                <div id="classDetailBody" class="d-none">
                    <div class="row g-3" id="classDetailInfo"></div>
                    <div class="app-card mt-3">
                        <div class="app-card-header">
                            <div>
                                <h3 class="mb-0">Danh sách học viên</h3>
                                <p class="sub mb-0">Học viên đã đăng ký lớp này</p>
                            </div>
                            <span class="badge-soft ms-auto" id="classMemberCount">0 học viên</span>
                        </div>
                        <div class="app-table-wrap">
                            <table class="app-table">
                                <thead>
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th>Học viên</th>
                                        <th>Điện thoại</th>
                                        <th>Email</th>
                                        <th>Ngày đăng ký</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="classDetailMembers">
                                    <tr><td colspan="6" class="text-center text-muted-2 py-4">Chưa có học viên.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php // classes.js xử lý AJAX thêm/sửa/xóa và modal chi tiết. ?>
<script src="<?= e(url('/common/assets/js/admin/classes.js')) ?>?v=8"></script>
<?php require $partials . '/foot.php'; ?>