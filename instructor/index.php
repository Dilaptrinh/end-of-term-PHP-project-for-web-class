<?php
require __DIR__ . '/../common/bootstrap.php';
require_role('instructor');

$menu = require __DIR__ . '/../common/menus/instructor.php';
$appRole = 'instructor';
$activeMenu = 'dashboard';
$pageTitle = 'Tổng quan giáo viên';
$pageScripts = [];

$instructor = data_current_instructor();
$classes = array_values(array_filter(
    data_classes(),
    function (array $class) use ($instructor): bool {
        $id_giao_vien_cua_lop = (int) $class['giao_vien_id'];
        $id_giao_vien_dang_dang_nhap = (int) ($instructor['id'] ?? 0);

        if ($id_giao_vien_cua_lop === $id_giao_vien_dang_dang_nhap) {
            return true;
        } else {
            return false;
        }
    }
));
$requests = array_values(array_filter(
    data_availability(),
    function (array $request) use ($instructor): bool {
        // Bước 1: Lấy tên giảng viên từ request gửi lên
        $ten_giang_vien_trong_request = $request['instructor'];

        // Bước 2: Lấy tên của giảng viên đang đăng nhập (nếu không có thì để chuỗi rỗng)
        $ten_giang_vien_dang_nhap = $instructor['name'] ?? '';

        // Bước 3: So sánh xem hai tên này có khớp nhau tuyệt đối hay không
        if ($ten_giang_vien_trong_request === $ten_giang_vien_dang_nhap) {
            return true;
        } else {
            return false;
        }
    }
));

$danh_sach_cho_duyet = array_filter(
    $requests,
    function (array $request): bool {
        if ($request['status'] === 'cho_duyet') {
            return true;
        } else {
            return false;
        }
    }
);
$pending = count($danh_sach_cho_duyet);

$danh_sach_da_duyet = array_filter(
    $requests,
    function (array $request): bool {
        if ($request['status'] === 'da_duyet') {
            return true;
        } else {
            return false;
        }
    }
);

$approved = count($danh_sach_da_duyet);

$students = (int) ($instructor['students'] ?? 0);

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
                    <h1>Xin chào, <?= e($instructor['name'] ?? 'Giáo viên') ?></h1>
                    <p>Tổng quan lớp học, học viên và lịch dạy của bạn.</p>
                </div>
                <div class="page-head-actions">
                    <a class="btn btn-primary" href="<?= e(url('/instructor/availability')) ?>"><?= icon('calendar-plus') ?> Đăng ký lịch dạy</a>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Lớp đang dạy</div><div class="stat-value"><?= count($classes) ?></div><div class="stat-foot">Theo phân công hiện tại</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Học viên</div><div class="stat-value"><?= $students ?></div><div class="stat-foot">Đang đăng ký lớp</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Lịch đã duyệt</div><div class="stat-value text-success"><?= $approved ?></div><div class="stat-foot">Khung giờ khả dụng</div></div></div>
                <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-label">Chờ duyệt</div><div class="stat-value" style="color:var(--warning);"><?= $pending ?></div><div class="stat-foot">Yêu cầu đang chờ admin</div></div></div>
            </div>
            <div class="app-card">
                <div class="app-card-header">
                    <div><h2>Lớp đang phụ trách</h2><p class="sub">Dữ liệu lấy trực tiếp từ cơ sở dữ liệu</p></div>
                    <a class="btn btn-ghost btn-sm ms-auto" href="<?= e(url('/instructor/classes')) ?>">Xem tất cả <?= icon('arrow-right') ?></a>
                </div>
                <div class="app-table-wrap">
                    <table class="app-table">
                        <thead><tr><th>Lớp học</th><th>Lịch</th><th>Phòng</th><th>Sĩ số</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td class="cell-strong"><?= e($class['name']) ?><div class="cell-sub"><?= e($class['level']) ?></div></td>
                                <td><?= e(weekday($class['weekday'])) ?><div class="cell-sub"><?= e($class['start'] . ' – ' . $class['end']) ?></div></td>
                                <td><?= e($class['room']) ?></td>
                                <td><?= e($class['enrolled'] . '/' . $class['capacity']) ?></td>
                                <td><?= status_badge($class['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$classes): ?><tr><td colspan="5" class="text-muted-2">Chưa có lớp được phân công.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<?php require $partials . '/foot.php'; ?>