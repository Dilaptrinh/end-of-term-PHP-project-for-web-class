<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

function class_find(int $id): ?array
{
    foreach (data_classes() as $class) {
        if ((int) $class['id'] === $id) {
            return $class;
        }
    }
    return null;
}

function class_date_value(?string $formatted): string
{
    if ($formatted === null || $formatted === '') {
        return '';
    }
    $date = DateTimeImmutable::createFromFormat('!d/m/Y', (string) $formatted);
    return $date ? $date->format('Y-m-d') : '';
}

function class_row_html(array $class): string
{
    return '<tr data-row data-class-row="' . (int) $class['id'] . '">' .
        '<td class="cell-strong">' . e($class['name']) .
            '<div class="cell-sub">Mã lớp: ' . (int) $class['id'] . ' · ' . e($class['level']) . '</div></td>' .
        '<td>' . e($class['instructor']) . '</td>' .
        '<td>' . e(weekday((int) $class['weekday'])) .
            '<div class="cell-sub">' . e($class['start'] . ' – ' . $class['end']) . '</div></td>' .
        '<td>' . e($class['room']) . '</td>' .
        '<td>' . (int) $class['enrolled'] . '/' . (int) $class['capacity'] . '</td>' .
        '<td class="text-muted-2 text-tiny">' . e($class['from']) . '<br>' . e($class['to']) . '</td>' .
        '<td>' . status_badge((string) $class['status']) . '</td>' .
        '<td class="cell-actions">' .
            '<button class="btn btn-ghost btn-sm" type="button" data-class-detail="' . (int) $class['id'] . '" title="Chi tiết" aria-label="Chi tiết"><i class="bi bi-info-circle"></i></button>' .
        '</td>' .
    '</tr>';
}

function class_info_card(string $label, string $value): string
{
    return '<div class="col-sm-6 col-lg-3"><div class="border rounded-3 p-3 h-100">' .
        '<div class="text-tiny text-muted-2 mb-1">' . $label . '</div>' .
        '<div class="fw-semibold">' . $value . '</div></div></div>';
}

function class_member_rows(array $members): string
{
    if (!$members) {
        return '<tr><td colspan="6" class="text-center text-muted-2 py-4">Chưa có học viên đăng ký lớp này.</td></tr>';
    }

    $genders = ['nam' => 'Nam', 'nu' => 'Nữ', 'khac' => 'Khác'];
    $html = '';
    foreach ($members as $index => $member) {
        $html .= '<tr>' .
            '<td>' . ($index + 1) . '</td>' .
            '<td class="cell-strong">' . e($member['name']) .
                '<div class="cell-sub">' . e($genders[$member['gender']] ?? '—') . '</div></td>' .
            '<td>' . e($member['phone'] ?: '—') . '</td>' .
            '<td>' . e($member['email'] ?: '—') . '</td>' .
            '<td>' . e($member['registered'] ?: '—') . '</td>' .
            '<td>' . status_badge((string) $member['status']) . '</td>' .
        '</tr>';
    }
    return $html;
}

function class_detail_html(?array $class, array $members, array $instructors): string
{
    $isEdit = $class !== null;
    $id = $isEdit ? (int) $class['id'] : 0;

    $values = [
        'name' => $isEdit ? (string) $class['name'] : '',
        'level' => $isEdit ? (string) $class['level'] : 'Cơ bản',
        'package' => $isEdit ? (string) $class['required_package'] : 'basic',
        'instructor' => $isEdit ? (int) $class['giao_vien_id'] : (int) ($instructors[0]['id'] ?? 0),
        'room' => $isEdit ? (string) ($class['room'] ?? '') : '',
        'weekday' => $isEdit ? (int) $class['weekday'] : 1,
        'start' => $isEdit ? (string) $class['start'] : '18:00',
        'end' => $isEdit ? (string) $class['end'] : '19:00',
        'capacity' => $isEdit ? (int) $class['capacity'] : 20,
        'status' => $isEdit ? (string) $class['status'] : 'dang_mo',
        'desc' => $isEdit ? (string) ($class['desc'] ?? '') : '',
        'from' => $isEdit ? class_date_value($class['from']) : date('Y-m-d'),
        'to' => $isEdit ? class_date_value($class['to']) : date('Y-m-d', strtotime('+3 months')),
    ];

    $levels = ['Cơ bản', 'Trung cấp', 'Nâng cao', 'Mọi trình độ'];
    $packages = ['basic' => 'Basic', 'standard' => 'Standard', 'premium' => 'Premium'];
    $statuses = ['dang_mo' => 'Đang mở', 'da_dong' => 'Đã đóng', 'da_huy' => 'Đã hủy'];

    ob_start();
    ?>
    <?php if ($isEdit): ?>
        <div class="row g-3">
            <?= class_info_card('Giáo viên', e($class['instructor'] ?: '—')) ?>
            <?= class_info_card('Lịch học', e(weekday((int) $class['weekday']) . ' · ' . $class['start'] . ' – ' . $class['end'])) ?>
            <?= class_info_card('Phòng học', e($class['room'] ?: 'Chưa xếp')) ?>
            <?= class_info_card('Sĩ số', (int) $class['enrolled'] . '/' . (int) $class['capacity']) ?>
            <?= class_info_card('Trình độ', e($class['level'] ?: '—')) ?>
            <?= class_info_card('Gói tối thiểu', e(strtoupper((string) ($class['required_package'] ?: 'basic')))) ?>
            <?= class_info_card('Thời gian', e($class['from'] . ' – ' . $class['to'])) ?>
            <?= class_info_card('Trạng thái', status_badge((string) $class['status'])) ?>
            <div class="col-12">
                <div class="border rounded-3 p-3">
                    <div class="text-tiny text-muted-2 mb-1">Mô tả lớp học</div>
                    <div><?= e($class['desc'] ?: 'Chưa có mô tả.') ?></div>
                </div>
            </div>
        </div>

        <div class="app-card mt-3">
            <div class="app-card-header">
                <div>
                    <h3 class="mb-0">Danh sách học viên</h3>
                    <p class="sub mb-0">Học viên đã đăng ký lớp này</p>
                </div>
                <span class="badge-soft ms-auto"><?= count($members) ?> học viên</span>
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
                    <tbody><?= class_member_rows($members) ?></tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <form id="class-form" class="mt-3" data-class-id="<?= $id ?>">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="cf-name">Tên lớp <span class="req">*</span></label>
                <input class="form-control" id="cf-name" name="ten_lop" type="text" required placeholder="VD: Yoga cơ bản" value="<?= e($values['name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-level">Trình độ</label>
                <select class="form-select" id="cf-level" name="trinh_do">
                    <?php foreach ($levels as $level): ?>
                        <option<?= $level === $values['level'] ? ' selected' : '' ?>><?= e($level) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-package">Gói tối thiểu</label>
                <select class="form-select" id="cf-package" name="goi_tap_toi_thieu">
                    <?php foreach ($packages as $value => $label): ?>
                        <option value="<?= e($value) ?>"<?= $value === $values['package'] ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cf-teacher">Giáo viên <span class="req">*</span></label>
                <select class="form-select" id="cf-teacher" name="giao_vien_id" required>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?= (int) $instructor['id'] ?>"<?= (int) $instructor['id'] === $values['instructor'] ? ' selected' : '' ?>><?= e($instructor['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cf-room">Phòng học</label>
                <input class="form-control" id="cf-room" name="phong_hoc" type="text" placeholder="VD: P.101" value="<?= e($values['room']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-day">Thứ trong tuần <span class="req">*</span></label>
                <select class="form-select" id="cf-day" name="thu_trong_tuan" required>
                    <?php for ($d = 1; $d <= 7; $d++): ?>
                        <option value="<?= $d ?>"<?= $d === $values['weekday'] ? ' selected' : '' ?>><?= e(weekday($d)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-start">Giờ bắt đầu <span class="req">*</span></label>
                <input class="form-control" id="cf-start" name="gio_bat_dau" type="time" required value="<?= e($values['start']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-end">Giờ kết thúc <span class="req">*</span></label>
                <input class="form-control" id="cf-end" name="gio_ket_thuc" type="time" required value="<?= e($values['end']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-capacity">Sức chứa <span class="req">*</span></label>
                <input class="form-control" id="cf-capacity" name="suc_chua" type="number" min="1" required value="<?= (int) $values['capacity'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-status">Trạng thái</label>
                <select class="form-select" id="cf-status" name="trang_thai">
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= e($value) ?>"<?= $value === $values['status'] ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-from">Ngày bắt đầu <span class="req">*</span></label>
                <input class="form-control" id="cf-from" name="ngay_bat_dau" type="date" required value="<?= e($values['from']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="cf-to">Ngày kết thúc <span class="req">*</span></label>
                <input class="form-control" id="cf-to" name="ngay_ket_thuc" type="date" required value="<?= e($values['to']) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="cf-desc">Mô tả</label>
                <textarea class="form-control" id="cf-desc" name="mo_ta" rows="3" placeholder="Mô tả nội dung lớp học…"><?= e($values['desc']) ?></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
            <?php if ($isEdit): ?>
                <button type="button" class="btn btn-danger-soft" data-class-delete="<?= $id ?>" data-class-name="<?= e($values['name']) ?>">Xóa lớp</button>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><?= icon('save') ?> <?= $isEdit ? 'Cập nhật' : 'Thêm lớp' ?></button>
        </div>
    </form>
    <?php
    return (string) ob_get_clean();
}
/*
 * - ?action=save&id=0   -> thêm lớp mới
 * - ?action=save&id=... -> sửa lớp
 * - ?action=delete&id=.. -> xóa lớp
 */
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // Chỉ admin đã đăng nhập mới được thao tác.
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    $action = (string) ($_GET['action'] ?? '');
    $id = post_int('id') ?: (int) ($_GET['id'] ?? 0);

    // XÓA LỚP
    if ($action === 'delete') {
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }

        $changed = admin_class_delete($id);

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

    // KIỂM TRA DỮ LIỆU
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
    if (!in_array($statusValue, ['dang_mo', 'da_dong', 'da_huy'], true)) {
        $errors['trang_thai'] = 'Trạng thái lớp không hợp lệ.';
    }

    // Kiểm tra giáo viên có tồn tại và chưa bị xóa mềm.
    $teacherId = (int) ($input['giao_vien_id'] ?? 0);
    if (!admin_instructor_exists($teacherId)) {
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

    $class = $savedId > 0 ? class_find($savedId) : null;
    if (!$class) {
        json_response(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
    }

    json_response([
        'success' => true,
        'message' => $id === 0 ? 'Đã tạo lớp học.' : 'Đã cập nhật lớp học.',
        'created' => $id === 0,
        'id' => $savedId,
        'html' => class_row_html($class),
    ], $id === 0 ? 201 : 200);
}

/*
 * Api get: trả HTML fragment
 * id = 0 là form thêm mới; id > 0 là chi tiết + form cập nhật + học viên.
 */
$requestAction = (string) ($_GET['action'] ?? '');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $requestAction === 'detail') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }

    $detailId = (int) ($_GET['id'] ?? 0);
    $detail = $detailId > 0 ? admin_class_detail($detailId) : null;
    if ($detailId > 0 && !$detail) {
        json_response(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
    }

    json_response([
        'success' => true,
        'title' => $detail ? 'Chi tiết lớp học' : 'Thêm lớp học',
        'html' => class_detail_html($detail, $detail ? admin_class_members((int) $detail['id']) : [], data_instructors()),
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
        <button class="btn btn-primary" type="button" data-class-detail="0"><?= icon('plus-lg') ?> Thêm lớp học</button>
    </div>
</div>

<?php // Form lọc theo mã lớp (submit bằng GET, tải lại trang với ?class_id=...). ?>
<form class="filter-bar" method="get" action="<?= e(url('/admin/classes')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="cl-id">Tìm theo mã lớp</label>
        <input class="form-control" id="cl-id" name="class_id" type="search" inputmode="numeric" value="<?= e($classId) ?>" placeholder="Nhập mã lớp...">
    </div>
    <div class="filter-actions">
        <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm</button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/classes')) ?>"><?= icon('arrow-counterclockwise') ?> Xóa lọc</a>
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
                <?php foreach ($classes as $class): ?>
                    <?= class_row_html($class) ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination(count($classes), 1, 10, 'lớp học') ?>
</div>

<?php // Một modal dùng chung: PHP trả HTML fragment cho cả thêm mới và chi tiết/cập nhật. ?>
<div class="modal fade" id="classDetailModal" tabindex="-1" aria-labelledby="classDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="classDetailTitle">Chi tiết lớp học</h2>
                    <p class="text-tiny text-muted-2 mb-0">Thông tin lớp, học viên và cập nhật lớp.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body" id="classDetailBody">
                <div class="text-center text-muted-2 py-4"><?= icon('arrow-repeat') ?> Đang tải...</div>
            </div>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<?php // classes.js chỉ tải HTML fragment và gắn kết quả vào bảng. ?>
<script src="<?= e(url('/common/assets/js/admin/classes.js')) ?>?v=17"></script>
<?php require $partials . '/foot.php'; ?>
