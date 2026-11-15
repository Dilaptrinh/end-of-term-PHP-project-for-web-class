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
    if ($id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
        json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 422);
    }

    $status = $action === 'approve' ? 'dang_mo' : 'tu_choi';
    $room = trim((string) ($_POST['phong_hoc'] ?? ''));
    $requiredPackage = trim((string) ($_POST['goi_tap_toi_thieu'] ?? 'basic'));
    if ($action === 'approve' && $room === '') {
        json_response(['success' => false, 'message' => 'Vui lòng nhập phòng học trước khi duyệt.'], 422);
    }
    if (!in_array($requiredPackage, ['basic', 'standard', 'premium'], true)) {
        json_response(['success' => false, 'message' => 'Gói tối thiểu không hợp lệ.'], 422);
    }

    $changed = admin_class_proposal_update($id, $status, $room, $requiredPackage);
    json_response([
        'success' => $changed,
        'message' => $changed ? ($action === 'approve' ? 'Đã duyệt đề xuất lớp.' : 'Đã từ chối đề xuất lớp.') : 'Không tìm thấy đề xuất đang chờ duyệt.',
    ], $changed ? 200 : 404);
}

require_role('admin');
$menu = require __DIR__ . '/../common/menus/admin.php';
$appRole = 'admin';
$activeMenu = 'availability';
$pageTitle = 'Duyệt đề xuất lớp';
$pageScripts = [];
$proposals = array_values(array_filter(data_classes(), static fn ($class) => $class['status'] === 'cho_duyet'));
$partials = __DIR__ . '/../common/partials';
require $partials . '/head.php';
?>
<div class="app-shell">
    <?php require $partials . '/sidebar.php'; ?>
    <div class="app-main">
        <?php require $partials . '/topbar.php'; ?>
        <main class="app-content" id="main-content">
            <?php require $partials . '/flash.php'; ?>
            <div class="page-head"><div><h1>Duyệt đề xuất lớp</h1><p>Kiểm tra thông tin lớp trước khi mở cho học viên đăng ký.</p></div></div>
            <div class="app-card">
                <div class="app-card-header"><h2>Đề xuất đang chờ duyệt</h2><span class="badge-soft ms-auto"><?= count($proposals) ?> đề xuất</span></div>
                <div class="app-table-wrap"><table class="app-table"><thead><tr><th>Lớp học</th><th>Giáo viên</th><th>Lịch học</th><th>Phòng</th><th>Sức chứa</th><th>Thao tác</th></tr></thead><tbody>
                <?php if (!$proposals): ?><tr><td colspan="6" class="text-center text-muted-2 py-4">Không có đề xuất đang chờ duyệt.</td></tr>
                <?php else: foreach ($proposals as $proposal): ?>
                    <tr>
                        <td class="cell-strong"><?= e($proposal['name']) ?><div class="cell-sub"><?= e($proposal['level']) ?> · Gói <?= e(strtoupper($proposal['required_package'])) ?></div></td>
                        <td><?= e($proposal['instructor']) ?></td>
                        <td><?= e(weekday($proposal['weekday'])) ?><div class="cell-sub"><?= e($proposal['start'] . ' – ' . $proposal['end']) ?><br><?= e($proposal['from'] . ' - ' . $proposal['to']) ?></div></td>
                        <td><?= e($proposal['room'] ?: 'Chưa xếp') ?></td>
                        <td><?= (int) $proposal['capacity'] ?></td>
                        <td class="cell-actions">
                            <button class="btn btn-primary btn-sm" type="button" data-proposal-action="approve" data-proposal-id="<?= (int) $proposal['id'] ?>" data-proposal-room="<?= e($proposal['room']) ?>" data-proposal-package="<?= e($proposal['required_package']) ?>">Duyệt</button>
                            <button class="btn btn-danger-soft btn-sm" type="button" data-proposal-action="reject" data-proposal-id="<?= (int) $proposal['id'] ?>">Từ chối</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody></table></div>
            </div>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<script src="<?= e(url('/common/assets/js/admin/availability.js')) ?>?v=1"></script>
<?php require $partials . '/foot.php'; ?>
