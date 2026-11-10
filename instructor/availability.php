<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'instructor') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $db = common_db();
    $accountId = (int) $user['id'];
    $stmt = $db->prepare('SELECT id FROM giao_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $instructor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$instructor) {
        json_response(['success' => false, 'message' => 'Không tìm thấy hồ sơ giáo viên.'], 404);
    }

    $name = trim((string) ($input['ten_lop'] ?? ''));
    $weekday = (int) ($input['thu_trong_tuan'] ?? 0);
    $start = trim((string) ($input['gio_bat_dau'] ?? ''));
    $end = trim((string) ($input['gio_ket_thuc'] ?? ''));
    $from = trim((string) ($input['ngay_bat_dau'] ?? ''));
    $to = trim((string) ($input['ngay_ket_thuc'] ?? ''));
    $capacity = (int) ($input['suc_chua'] ?? 0);
    $level = trim((string) ($input['trinh_do'] ?? ''));

    if ($name === '' || $weekday < 1 || $weekday > 7 || $start === '' || $end === '' || $from === '' || $to === '' || $capacity < 1) {
        json_response(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin đề xuất lớp.'], 422);
    }
    if (!in_array($level, ['Cơ bản', 'Mọi trình độ', 'Nâng cao'], true)) {
        json_response(['success' => false, 'message' => 'Trình độ lớp không hợp lệ.'], 422);
    }
    if ($start >= $end || $from > $to) {
        json_response(['success' => false, 'message' => 'Thời gian lớp học không hợp lệ.'], 422);
    }

    $instructorId = (int) $instructor['id'];
    $specialtyStmt = $db->prepare(
        'SELECT cm.id
           FROM giao_vien_chuyen_mon gvcm
           JOIN chuyen_mon cm ON cm.id = gvcm.chuyen_mon_id AND cm.trang_thai = 1
          WHERE gvcm.giao_vien_id = ? AND cm.ten_chuyen_mon = ?
          LIMIT 1'
    );
    $specialtyStmt->bind_param('is', $instructorId, $name);
    $specialtyStmt->execute();
    $specialty = $specialtyStmt->get_result()->fetch_assoc();
    $specialtyStmt->close();
    if (!$specialty) {
        json_response(['success' => false, 'message' => 'Môn học không hợp lệ hoặc không thuộc chuyên môn của bạn.'], 422);
    }

    $room = null;
    $description = null;
    $stmt = $db->prepare(
        'INSERT INTO lop_hoc
            (ten_lop, giao_vien_id, thu_trong_tuan, gio_bat_dau, gio_ket_thuc, ngay_bat_dau, ngay_ket_thuc, suc_chua, phong_hoc, mo_ta, trinh_do, trang_thai)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "cho_duyet")'
    );
    $stmt->bind_param('siissssisss', $name, $instructorId, $weekday, $start, $end, $from, $to, $capacity, $room, $description, $level);
    $stmt->execute();
    $stmt->close();

    json_response(['success' => true, 'message' => 'Đã gửi đề xuất lớp học cho quản trị viên.'], 201);
}

require_role('instructor');
$menu = require __DIR__ . '/../common/menus/instructor.php';
$appRole = 'instructor';
$activeMenu = 'availability';
$pageTitle = 'Đề xuất lớp học';
$pageScripts = [];
$instructorName = data_current_instructor_name();
$instructorSpecialtyIds = data_current_instructor()['specialty_ids'] ?? [];
$specialties = array_values(array_filter(
    data_specialties(),
    static fn (array $specialty): bool => in_array($specialty['id'], $instructorSpecialtyIds, true)
));
$classes = array_values(array_filter(data_classes(), static fn ($class) => $class['instructor'] === $instructorName));
$pending = count(array_filter($classes, static fn ($class) => $class['status'] === 'cho_duyet'));
$approved = count(array_filter($classes, static fn ($class) => $class['status'] === 'dang_mo'));
$rejected = count(array_filter($classes, static fn ($class) => $class['status'] === 'tu_choi'));
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
                    <h1>Đề xuất lớp học</h1>
                    <p>Tạo lớp học để quản trị viên duyệt trước khi học viên đăng ký.</p>
                </div>
                <div class="page-head-actions"><a class="btn btn-outline-primary" href="<?= e(url('/instructor/classes')) ?>"><?= icon('journal-bookmark') ?> Lớp của tôi</a></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-4"><div class="stat-card"><div class="stat-label">Chờ duyệt</div><div class="stat-value" style="color:var(--warning);"><?= $pending ?></div></div></div>
                <div class="col-4"><div class="stat-card"><div class="stat-label">Đã mở</div><div class="stat-value text-success"><?= $approved ?></div></div></div>
                <div class="col-4"><div class="stat-card"><div class="stat-label">Từ chối</div><div class="stat-value" style="color:var(--danger);"><?= $rejected ?></div></div></div>
            </div>

            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="app-card">
                        <div class="app-card-header"><div><h2>Đề xuất lớp mới</h2><p class="sub">Thông tin lớp sẽ hiển thị cho học viên sau khi được duyệt</p></div></div>
                        <div class="app-card-body">
                            <form id="class-proposal-form">
                                <div class="mb-3">
                                    <label class="form-label" for="proposal-name">Môn học <span class="req">*</span></label>
                                    <select class="form-select" id="proposal-name" required <?= $specialties === [] ? 'disabled' : '' ?>>
                                        <option value="">-- Chọn môn học --</option>
                                        <?php foreach ($specialties as $specialty): ?>
                                            <option value="<?= e($specialty['name']) ?>"><?= e($specialty['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($specialties === []): ?>
                                        <div class="form-text text-danger">Tài khoản chưa có chuyên môn. Vui lòng liên hệ quản trị viên.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><label class="form-label" for="proposal-weekday">Thứ <span class="req">*</span></label><select class="form-select" id="proposal-weekday" required><option value="1">Thứ 2</option><option value="2">Thứ 3</option><option value="3">Thứ 4</option><option value="4">Thứ 5</option><option value="5">Thứ 6</option><option value="6">Thứ 7</option><option value="7">Chủ nhật</option></select></div>
                                    <div class="col-6"><label class="form-label" for="proposal-capacity">Sức chứa <span class="req">*</span></label><input class="form-control" id="proposal-capacity" type="number" min="1" value="15" required></div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><label class="form-label" for="proposal-start">Bắt đầu <span class="req">*</span></label><input class="form-control" id="proposal-start" type="time" value="07:00" required></div>
                                    <div class="col-6"><label class="form-label" for="proposal-end">Kết thúc <span class="req">*</span></label><input class="form-control" id="proposal-end" type="time" value="08:00" required></div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6"><label class="form-label" for="proposal-from">Bắt đầu từ <span class="req">*</span></label><input class="form-control" id="proposal-from" type="date" required></div>
                                    <div class="col-6"><label class="form-label" for="proposal-to">Đến ngày <span class="req">*</span></label><input class="form-control" id="proposal-to" type="date" required></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="proposal-level">Trình độ <span class="req">*</span></label>
                                    <select class="form-select" id="proposal-level" required>
                                        <option value="Cơ bản">Cơ bản</option>
                                        <option value="Mọi trình độ">Mọi trình độ</option>
                                        <option value="Nâng cao">Nâng cao</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary" type="submit"><?= icon('send') ?> Gửi đề xuất</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="app-card"><div class="app-card-header"><h2>Đề xuất của tôi</h2></div><div class="app-table-wrap"><table class="app-table"><thead><tr><th>Lớp học</th><th>Lịch</th><th>Sức chứa</th><th>Trạng thái</th></tr></thead><tbody>
                    <?php if (!$classes): ?><tr><td colspan="4" class="text-center text-muted-2 py-4">Chưa có đề xuất lớp.</td></tr>
                    <?php else: foreach ($classes as $class): ?><tr><td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td><td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?><br><?= e($class['from'] . ' - ' . $class['to']) ?></div></td><td><?= (int) $class['capacity'] ?></td><td><?= status_badge($class['status']) ?></td></tr><?php endforeach; endif; ?>
                    </tbody></table></div></div>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<script>
document.getElementById('class-proposal-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    var form = event.currentTarget;
    var data = {
        ten_lop: document.getElementById('proposal-name').value.trim(),
        thu_trong_tuan: document.getElementById('proposal-weekday').value,
        gio_bat_dau: document.getElementById('proposal-start').value,
        gio_ket_thuc: document.getElementById('proposal-end').value,
        ngay_bat_dau: document.getElementById('proposal-from').value,
        ngay_ket_thuc: document.getElementById('proposal-to').value,
        suc_chua: document.getElementById('proposal-capacity').value,
        trinh_do: document.getElementById('proposal-level').value
    };
    try {
        var response = await fetch(window.location.pathname, { method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify(data), credentials: 'same-origin' });
        var result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Không thể gửi đề xuất.');
        alert(result.message);
        form.reset();
        window.location.reload();
    } catch (error) { alert(error.message); }
});
</script>
<?php require $partials . '/foot.php'; ?>
