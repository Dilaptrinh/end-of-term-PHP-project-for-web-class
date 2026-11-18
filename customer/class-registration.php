<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'customer') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    if (($_GET['action'] ?? '') !== 'register') {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 422);
    }

    $input = $_POST;

    $classId = (int) ($input['lop_hoc_id'] ?? 0);
    $db = common_db();
    $accountId = (int) $user['id'];

    $stmt = $db->prepare(
        'SELECT id FROM hoi_vien
          WHERE tai_khoan_id = ? AND trang_thai = "hoat_dong" AND deleted_at IS NULL
          LIMIT 1'
    );
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member || $classId <= 0) {
        json_response(['success' => false, 'message' => 'Dữ liệu đăng ký không hợp lệ.'], 422);
    }

    $memberId = (int) $member['id'];

    $stmt = $db->prepare(
        'SELECT gt.cap_do FROM dang_ky_goi d
           JOIN goi_tap gt ON gt.id = d.goi_tap_id AND gt.deleted_at IS NULL
          WHERE d.hoi_vien_id = ? AND d.trang_thai = "da_thanh_toan"
            AND d.ngay_bat_dau <= CURDATE() AND d.ngay_ket_thuc >= CURDATE()
          ORDER BY CASE gt.cap_do WHEN "premium" THEN 3 WHEN "standard" THEN 2 ELSE 1 END DESC,
                   d.ngay_ket_thuc DESC
          LIMIT 1'
    );
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $activePackage = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$activePackage) {
        json_response(['success' => false, 'message' => 'Bạn cần có gói tập còn hạn để đăng ký lớp.'], 422);
    }

    $stmt = $db->prepare(
        'SELECT id, ten_lop, suc_chua, goi_tap_toi_thieu, thu_trong_tuan,
                gio_bat_dau, gio_ket_thuc, ngay_bat_dau, ngay_ket_thuc,
                (SELECT COUNT(*) FROM dang_ky_lop
                  WHERE lop_hoc_id = lop_hoc.id AND trang_thai = "dang_ky") AS da_dang_ky
           FROM lop_hoc
          WHERE id = ? AND trang_thai = "dang_mo" AND deleted_at IS NULL
            AND ngay_ket_thuc >= CURDATE()
          LIMIT 1'
    );
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $class = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$class) {
        json_response(['success' => false, 'message' => 'Lớp không tồn tại hoặc chưa mở đăng ký.'], 422);
    }

    $packageLevels = ['basic' => 1, 'standard' => 2, 'premium' => 3];
    $packageLevel = $packageLevels[$activePackage['cap_do'] ?? 'basic'] ?? 1;
    $requiredLevel = $packageLevels[$class['goi_tap_toi_thieu'] ?? 'basic'] ?? 1;
    if ($requiredLevel > $packageLevel) {
        json_response(['success' => false, 'message' => 'Gói hiện tại không hỗ trợ lớp này. Hãy nâng cấp gói để đăng ký.'], 422);
    }

    if ((int) $class['da_dang_ky'] >= (int) $class['suc_chua']) {
        json_response(['success' => false, 'message' => 'Lớp đã đủ số lượng.'], 422);
    }

    $stmt = $db->prepare(
        'SELECT id FROM dang_ky_lop
          WHERE hoi_vien_id = ? AND lop_hoc_id = ? AND trang_thai = "dang_ky"
          LIMIT 1'
    );
    $stmt->bind_param('ii', $memberId, $classId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        json_response([
            'success' => false,
            'message' => 'Bạn đã đăng ký lớp này rồi.',
            'class_id' => $classId,
        ], 422);
    }

    $stmt = $db->prepare(
        'SELECT lh.ten_lop
           FROM dang_ky_lop d
           JOIN lop_hoc lh ON lh.id = d.lop_hoc_id
          WHERE d.hoi_vien_id = ? AND d.trang_thai = "dang_ky"
            AND lh.trang_thai = "dang_mo" AND lh.deleted_at IS NULL
            AND lh.thu_trong_tuan = ?
            AND lh.gio_bat_dau < ? AND lh.gio_ket_thuc > ?
            AND lh.ngay_bat_dau <= ? AND lh.ngay_ket_thuc >= ?
          LIMIT 1'
    );
    $stmt->bind_param(
        'iissss',
        $memberId,
        $class['thu_trong_tuan'],
        $class['gio_ket_thuc'],
        $class['gio_bat_dau'],
        $class['ngay_ket_thuc'],
        $class['ngay_bat_dau']
    );
    $stmt->execute();
    $conflict = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($conflict) {
        json_response([
            'success' => false,
            'message' => 'Bạn đã có lớp "' . $conflict['ten_lop'] . '" trùng khung giờ này.',
        ], 422);
    }

    $stmt = $db->prepare('INSERT INTO dang_ky_lop (hoi_vien_id, lop_hoc_id, trang_thai, ngay_dang_ky) VALUES (?, ?, "dang_ky", NOW())');
    $stmt->bind_param('ii', $memberId, $classId);
    $stmt->execute();
    $stmt->close();

    $stmt = $db->prepare('SELECT COUNT(*) AS total FROM dang_ky_lop WHERE lop_hoc_id = ? AND trang_thai = "dang_ky"');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $enrolled = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    $available = max(0, (int) $class['suc_chua'] - $enrolled);
    json_response([
        'success' => true,
        'message' => 'Đã đăng ký lớp học.',
        'class_id' => $classId,
        'enrolled' => $enrolled,
        'capacity' => (int) $class['suc_chua'],
        'available' => $available,
        'is_full' => $available === 0,
        'availability_text' => $available === 0 ? 'Lớp đã đầy' : 'Còn ' . $available . ' chỗ',
        'availability_label' => $available === 0 ? 'Hết chỗ' : 'Còn ' . $available . ' chỗ',
        'availability_class' => $available === 0 ? 'text-danger' : 'text-success',
    ], 201);
}

require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';

$appRole = 'customer';
$activeMenu = 'class-registration';
$pageTitle = 'Đăng ký lớp học';
$pageScripts = [];

$classes = data_classes();
$registeredIds = array_map(static fn (array $c): int => (int) $c['id'], data_customer_classes());
$member = data_current_member();
$currentPackage = $member['package'] ?? 'Chưa có gói';
$packageExpiry = $member['package_expiry'] ?? '—';
$packageLevels = ['basic' => 1, 'standard' => 2, 'premium' => 3];
$currentPackageLevel = $packageLevels[$member['package_level'] ?? 'basic'] ?? 1;
$today = (new DateTimeImmutable('today', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d');
$open = array_values(array_filter($classes, static fn ($c) => $c['status'] === 'dang_mo'));
foreach ($open as &$class) {
    if ($class['start_date'] > $today) {
        $class['status'] = 'sap_mo';
    }
}
unset($class);

$keyword = trim((string) ($_GET['q'] ?? ''));
$weekdayFilter = (int) ($_GET['weekday'] ?? 0);
$packageFilter = trim((string) ($_GET['package'] ?? ''));
$packageNames = [
    'basic' => 'Basic - Cơ bản',
    'standard' => 'Standard - Trung cấp',
    'premium' => 'Premium - Tất cả lớp',
];
if ($weekdayFilter < 1 || $weekdayFilter > 7) {
    $weekdayFilter = 0;
}
if (!isset($packageNames[$packageFilter])) {
    $packageFilter = '';
}

$open = array_values(array_filter($open, static function (array $class) use ($keyword, $weekdayFilter, $packageFilter): bool {
    if ($keyword !== ''
        && mb_stripos($class['name'], $keyword) === false
        && mb_stripos($class['instructor'], $keyword) === false) {
        return false;
    }
    if ($weekdayFilter > 0 && (int) $class['weekday'] !== $weekdayFilter) {
        return false;
    }
    if ($packageFilter !== '' && ($class['required_package'] ?? 'basic') !== $packageFilter) {
        return false;
    }
    return true;
}));

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
        <h1>Đăng ký lớp học</h1>
        <p>Chọn lớp phù hợp với lịch của bạn.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-outline-primary" href="<?= e(url('/customer')) ?>"><?= icon('calendar-week') ?> Lịch của tôi</a>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center gap-2 mb-3" role="note">
    <?= icon('info-circle') ?>
    <div class="flex-grow-1">Bạn đang dùng <strong><?= e($currentPackage) ?></strong> — hết hạn <?= e($packageExpiry) ?>.</div>
    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/customer/history')) ?>">Xem gói</a>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/customer/class-registration')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="cls-q">Tìm lớp / giáo viên</label>
        <div class="position-relative">
            <input class="form-control" id="cls-q" name="q" type="search" value="<?= e($keyword) ?>" placeholder="VD: Yoga, Hà…">
        </div>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cls-day">Thứ</label>
        <select class="form-select" id="cls-day" name="weekday">
            <option value="">Tất cả</option>
            <?php for ($d = 1; $d <= 7; $d++): ?>
                <option value="<?= $d ?>" <?= $weekdayFilter === $d ? 'selected' : '' ?>><?= e(weekday($d)) ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cls-package">Gói tập</label>
        <select class="form-select" id="cls-package" name="package">
            <option value="">Tất cả</option>
            <?php foreach ($packageNames as $packageValue => $packageName): ?>
                <option value="<?= e($packageValue) ?>" <?= $packageFilter === $packageValue ? 'selected' : '' ?>><?= e($packageName) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm</button>
            <a class="btn btn-ghost" href="<?= e(url('/customer/class-registration')) ?>">Xóa lọc</a>
        </div>
    </div>
</form>

<div class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <div class="text-small text-muted-2">Tìm thấy <strong class="text-brand"><?= count($open) ?></strong> lớp đã được duyệt</div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <label class="form-check form-switch mb-0 d-flex align-items-center gap-2">
            <input class="form-check-input" type="checkbox" id="only-available" checked>
            <span class="form-check-label text-small">Chỉ lớp còn chỗ</span>
        </label>
    </div>
</div>

<div class="row g-3" id="class-grid">
    <?php foreach ($open as $class):
        $available = $class['capacity'] - $class['enrolled'];
        $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
        $tone = $available === 0 ? 'red' : ($percent >= 80 ? 'amber' : '');
        $requiredLevel = $packageLevels[$class['required_package'] ?? 'basic'] ?? 1;
        $allowed = $requiredLevel <= $currentPackageLevel;
        $isRegistered = in_array((int) $class['id'], $registeredIds, true);
    ?>
        <div class="col-md-6 col-xl-4" data-class-card
             data-class-id="<?= (int) $class['id'] ?>"
             data-capacity="<?= (int) $class['capacity'] ?>"
             data-available="<?= $available > 0 ? '1' : '0' ?>">
            <article class="class-card">
                <div class="class-card-top">
                    <div class="day-chip">
                        <strong><?= e(weekday_short($class['weekday'])) ?></strong>
                        <span><?= e($class['start']) ?></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-start gap-2">
                            <h3 class="flex-grow-1"><?= e($class['name']) ?></h3>
                            <span class="badge-soft"><?= e($class['level']) ?></span>
                        </div>
                        <div class="teacher"><?= icon('person') ?> <?= e($class['instructor']) ?></div>
                    </div>
                </div>
                <div class="mt-2"><?= status_badge($class['status']) ?></div>
                <div class="class-card-body">
                    <div class="class-meta">
                        <div class="item"><?= icon('clock') ?> <?= e($class['start'] . ' – ' . $class['end']) ?></div>
                        <div class="item"><?= icon('geo-alt') ?> <?= e($class['room']) ?></div>
                        <div class="item"><?= icon('calendar3') ?> <?= e(weekday($class['weekday'])) ?></div>
                        <div class="item"><?= icon('people') ?> <span data-enrolled><?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></span></div>
                    </div>
                    <div class="class-capacity">
                        <span data-available-text><?= $available === 0 ? 'Lớp đã đầy' : 'Còn ' . $available . ' chỗ' ?></span>
                        <span><?= $percent ?>%</span>
                    </div>
                    <?= progress_bar($percent, $tone) ?>
                    <p class="text-muted-2 text-tiny mt-2 mb-0"><?= e($class['desc']) ?></p>
                </div>
                <div class="class-card-foot">
                    <span class="availability <?= $available === 0 ? 'text-danger' : 'text-success' ?>" data-available-label>
                        <?= $available === 0 ? 'Hết chỗ' : ($class['status'] === 'sap_mo' ? 'Đăng ký trước · còn ' . $available . ' chỗ' : 'Còn ' . $available . ' chỗ') ?>
                    </span>
                    <span data-class-action>
                        <?php if ($isRegistered): ?>
                            <span class="availability text-success">Đã đăng ký</span>
                            <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Đã đăng ký</button>
                        <?php elseif (!$allowed): ?>
                            <span class="availability text-warning">Cần gói cao hơn</span>
                            <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Nâng cấp gói</button>
                        <?php elseif ($available === 0): ?>
                            <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Đã đầy</button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm" type="button"
                                    data-bs-toggle="modal" data-bs-target="#registerClassModal"
                                    data-class-id="<?= (int) $class['id'] ?>"
                                    data-class-name="<?= e($class['name']) ?>"
                                    data-class-meta="<?= e(weekday($class['weekday']) . ' · ' . $class['start'] . ' – ' . $class['end'] . ' · ' . $class['room']) ?>"
                                    data-class-teacher="<?= e($class['instructor']) ?>">
                                Đăng ký
                            </button>
                        <?php endif; ?>
                    </span>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!$open): ?>
    <div class="app-card">
        <div class="app-card-body text-center text-muted-2 py-4">Không tìm thấy lớp phù hợp với bộ lọc.</div>
    </div>
<?php endif; ?>

<div class="app-card mt-3">
    <?= pagination(count($open), 1, 12, 'lớp học') ?>
</div>

<div class="modal fade" id="registerClassModal" tabindex="-1" aria-labelledby="registerClassTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="registerClassTitle">Xác nhận đăng ký lớp</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="stat-icon"><?= icon('journal-plus') ?></span>
                    <div>
                        <div class="fw-800" data-modal-class-name>Yoga cơ bản</div>
                        <div class="text-tiny text-muted-2" data-modal-class-meta></div>
                    </div>
                </div>
                <div class="kv-list mb-3">
                    <div class="kv"><span>Giáo viên</span><span data-modal-class-teacher></span></div>
                    <div class="kv"><span>Gói đang dùng</span><span><?= e($currentPackage) ?></span></div>
                    <div class="kv"><span>Học phí lớp</span><span class="text-success">Đã bao gồm trong gói</span></div>
                </div>
                <div class="alert alert-warning mb-0 d-flex gap-2">
                    <?= icon('exclamation-triangle') ?>
                    <span class="text-tiny">Vui lòng có mặt trước giờ học 10 phút. Hủy đăng ký trước 12 giờ để không bị tính vắng.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                <form id="register-form">
                    <input type="hidden" name="lop_hoc_id" id="register-class-id" value="">
                    <button type="submit" class="btn btn-primary"><?= icon('check-lg') ?> Xác nhận đăng ký</button>
                </form>
            </div>
        </div>
    </div>
</div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

<script src="<?= e(url('/common/assets/js/customer/class-registration.js')) ?>?v=3"></script>
<?php require $partials . '/foot.php'; ?>
