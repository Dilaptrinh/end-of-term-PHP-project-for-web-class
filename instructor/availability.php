<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'instructor') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $action = (string) ($_GET['action'] ?? 'create');
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

    $instructorId = (int) $instructor['id'];

    if ($action === 'cancel') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
        }

        $stmt = $db->prepare('SELECT giao_vien_id FROM lich_ranh WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            json_response(['success' => false, 'message' => 'Không tìm thấy lịch dạy.'], 404);
        }
        if ((int) $row['giao_vien_id'] !== $instructorId) {
            json_response(['success' => false, 'message' => 'Bạn không có quyền hủy lịch dạy này.'], 403);
        }

        $stmt = $db->prepare('UPDATE lich_ranh SET trang_thai = "da_huy" WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        json_response(['success' => true, 'message' => 'Đã hủy lịch dạy.']);
    }

    $date = trim((string) ($input['ngay_day'] ?? $input['ngay'] ?? ''));
    $start = trim((string) ($input['gio_bat_dau'] ?? ''));
    $end = trim((string) ($input['gio_ket_thuc'] ?? ''));
    $specialty = trim((string) ($input['ghi_chu'] ?? ''));

    if ($date === '' || $start === '' || $end === '' || $specialty === '') {
        json_response(['success' => false, 'message' => 'Vui lòng chọn ngày, khung giờ và môn dạy.'], 422);
    }

    $stmt = $db->prepare(
        'SELECT cm.id
           FROM giao_vien_chuyen_mon gvcm
           JOIN chuyen_mon cm ON cm.id = gvcm.chuyen_mon_id AND cm.trang_thai = 1
          WHERE gvcm.giao_vien_id = ? AND cm.ten_chuyen_mon = ?
          LIMIT 1'
    );
    $stmt->bind_param('is', $instructorId, $specialty);
    $stmt->execute();
    $validSpecialty = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$validSpecialty) {
        json_response(['success' => false, 'message' => 'Môn dạy không hợp lệ hoặc không thuộc chuyên môn của bạn.'], 422);
    }

    $stmt = $db->prepare('INSERT INTO lich_ranh (giao_vien_id, ngay, gio_bat_dau, gio_ket_thuc, ghi_chu, trang_thai) VALUES (?, ?, ?, ?, ?, "cho_duyet")');
    $stmt->bind_param('issss', $instructorId, $date, $start, $end, $specialty);
    $stmt->execute();
    $stmt->close();

    json_response(['success' => true, 'message' => 'Đã gửi yêu cầu lịch dạy.'], 201);
}

require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';

$appRole = 'instructor';
$activeMenu = 'availability';
$pageTitle = 'Đăng ký lịch dạy';
$pageScripts = [];

$instructor = data_current_instructor();
$specialties = array_values(array_filter(
    data_specialties(),
    static fn (array $specialty): bool => in_array($specialty['id'], $instructor['specialty_ids'] ?? [], true)
));
$requests = array_values(array_filter(data_availability(), static fn ($a) => $a['instructor'] === data_current_instructor_name()));
$pending = count(array_filter($requests, static fn ($r) => $r['status'] === 'cho_duyet'));
$approved = count(array_filter($requests, static fn ($r) => $r['status'] === 'da_duyet'));
$rejected = count(array_filter($requests, static fn ($r) => $r['status'] === 'tu_choi'));
$totalHours = 0;
foreach ($requests as $request) {
    $totalHours += max(0, (strtotime($request['end']) - strtotime($request['start'])) / 3600);
}
$totalHours = (int) round($totalHours);

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
        <h1>Đăng ký lịch dạy</h1>
        <p>Gửi khung giờ bạn có thể dạy, quản trị viên sẽ duyệt hoặc từ chối.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-outline-primary" href="<?= e(url('/instructor/schedule')) ?>"><?= icon('calendar-week') ?> Xem lịch dạy</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Chờ duyệt</div>
            <div class="stat-value" style="color:var(--warning);"><?= $pending ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Đã duyệt</div>
            <div class="stat-value text-success"><?= $approved ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Từ chối</div>
            <div class="stat-value" style="color:var(--danger);"><?= $rejected ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-label">Tổng giờ đăng ký</div>
            <div class="stat-value"><?= $totalHours ?> <span class="text-tiny text-muted-2 fw-500">giờ</span></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="app-card" id="availability-form">
            <div class="app-card-header">
                <div>
                    <h2>Gửi yêu cầu mới</h2>
                    <p class="sub">Chọn ngày, khung giờ và môn dạy</p>
                </div>
                <span class="stat-icon ms-auto"><?= icon('calendar-plus') ?></span>
            </div>
            <div class="app-card-body">
                <form id="availability-create-form">
                    <div class="mb-3">
                        <label class="form-label" for="av-date">Ngày dạy <span class="req">*</span></label>
                        <input class="form-control" id="av-date" name="ngay_day" type="date" required min="2026-10-23">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="av-start">Bắt đầu <span class="req">*</span></label>
                            <input class="form-control" id="av-start" name="gio_bat_dau" type="time" value="07:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="av-end">Kết thúc <span class="req">*</span></label>
                            <input class="form-control" id="av-end" name="gio_ket_thuc" type="time" value="09:00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="av-specialty">Môn dạy <span class="req">*</span></label>
                        <select class="form-select" id="av-specialty" name="ghi_chu" required <?= $specialties === [] ? 'disabled' : '' ?>>
                            <option value="">-- Chọn môn dạy --</option>
                            <?php foreach ($specialties as $specialty): ?>
                                <option value="<?= e($specialty['name']) ?>"><?= e($specialty['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($specialties === []): ?>
                            <div class="form-text text-danger">Tài khoản chưa có chuyên môn. Vui lòng liên hệ quản trị viên.</div>
                        <?php endif; ?>
                    </div>
                    <div class="alert alert-warning d-flex gap-2 py-2 mb-3">
                        <?= icon('exclamation-triangle') ?>
                        <span class="text-tiny">Kiểm tra kỹ khung giờ trước khi gửi. Yêu cầu đã duyệt chỉ có thể hủy trước 24 giờ.</span>
                    </div>
                    <button class="btn btn-primary w-100" type="submit"><?= icon('send') ?> Gửi yêu cầu</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Yêu cầu của tôi</h2>
                    <p class="sub"><?= count($requests) ?> yêu cầu gần đây</p>
                </div>
            </div>
            <div class="app-table-wrap">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Khung giờ</th>
                            <th>Môn dạy</th>
                            <th>Trạng thái</th>
                            <th>Người duyệt</th>
                            <th class="cell-actions">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr data-row>
                                <td class="cell-strong"><?= e($request['date']) ?><div class="cell-sub"><?= e($request['weekday']) ?></div></td>
                                <td><?= e($request['start'] . ' – ' . $request['end']) ?></td>
                                <td class="text-muted-2" style="max-width:220px;"><?= e(truncate($request['note'], 48)) ?></td>
                                <td><?= status_badge($request['status']) ?></td>
                                <td class="text-muted-2"><?= e($request['approved_by']) ?></td>
                                <td class="cell-actions">
                                    <?php if ($request['status'] === 'cho_duyet'): ?>
                                        <button class="btn btn-danger-soft btn-sm" type="button"
                                                data-cancel-id="<?= (int) $request['id'] ?>"
                                                data-cancel-date="<?= e($request['date']) ?>">
                                            <?= icon('x-lg') ?> Hủy
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-sm" type="button" disabled>—</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pagination(count($requests), 1, 10, 'yêu cầu') ?>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/instructor/availability.js')) ?>?v=2"></script>
<?php require $partials . '/foot.php'; ?>