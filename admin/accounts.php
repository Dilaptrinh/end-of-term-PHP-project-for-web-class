<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

// API GET: trả chi tiết một tài khoản cho modal bằng JSON.
$requestAction = (string) ($_GET['action'] ?? '');
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $requestAction === 'detail') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }
    $detail = admin_account_detail((int) ($_GET['id'] ?? 0));
    json_response($detail ? ['success' => true, 'data' => $detail] : ['success' => false, 'message' => 'Không tìm thấy tài khoản.'], $detail ? 200 : 404);
    //detail có người trả về data chi tiết , ko có trả message ko tìm thấy
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // API POST: xử lý tạo, cập nhật và khóa/mở khóa tài khoản.
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $accountId = (int) $user['id'];
    $action = (string) ($_GET['action'] ?? '');
    // post_string(), post_int(), post_array(): common/functions.php.
    // admin_account_*(): admin/queries.php, nơi tập trung SQL tài khoản.

    if ($action === 'create') {
        // Tạo tài khoản và hồ sơ Instructor/Customer liên kết nếu cần.
        $username = post_string('ten_dang_nhap');
        $name = post_string('ho_ten');
        $password = (string) ($_POST['mat_khau'] ?? '');
        $role = post_string('vai_tro');
        $phone = post_string('sdt');
        $email = post_string('email');
        //validate input create user
        if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username) || mb_strlen($name) < 2) {
            json_response(['success' => false, 'message' => 'Tên đăng nhập hoặc họ tên không hợp lệ.'], 422);
        }
        if (!in_array($role, ['admin', 'instructor', 'customer'], true)) {
            json_response(['success' => false, 'message' => 'Vai trò không hợp lệ.'], 422);
        }
        if (strlen($password) < 6) {
            json_response(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.'], 422);
        }
        if (in_array($role, ['instructor', 'customer'], true) && ($phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            json_response(['success' => false, 'message' => 'Giáo viên và hội viên cần số điện thoại, email hợp lệ.'], 422);
        }
        if (admin_username_exists($username)) {
            json_response(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'], 422);
        }

        try {
            admin_account_create($username, $password, $name, $role, $phone, $email);
        } catch (mysqli_sql_exception $error) {
            json_response(['success' => false, 'message' => 'Email đã được sử dụng hoặc dữ liệu bị trùng.'], 422);
        }
        json_response(['success' => true, 'message' => 'Đã tạo tài khoản và hồ sơ liên quan.'], 201);
    }

    if ($action === 'status') {
        // Đổi trạng thái đăng nhập của tài khoản.
        $targetId = (int) ($_GET['id'] ?? 0);
        $active = post_int('trang_thai');
        if ($targetId <= 0 || !in_array($active, [0, 1], true)) {
            json_response(['success' => false, 'message' => 'Dữ liệu trạng thái không hợp lệ.'], 422);
        }
        if (!admin_account_status($targetId, $active)) {
            json_response(['success' => false, 'message' => 'Không thể thay đổi tài khoản admin hoặc tài khoản không tồn tại.'], 422);
        }
        json_response(['success' => true, 'message' => $active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.']);
    }

    if ($action === 'update') {
        // Cập nhật thông tin chung và thông tin hồ sơ theo role.
        $targetId = (int) ($_GET['id'] ?? 0);
        $name = post_string('ho_ten');
        $phone = post_string('sdt');
        $email = post_string('email');
        $active = post_int('trang_thai');
        $birth = post_string('ngay_sinh') ?: null;
        $address = post_string('dia_chi') ?: null;
        $goal = post_string('muc_tieu') ?: null;
        $height = post_string('chieu_cao') ?: null;
        $weight = post_string('can_nang') ?: null;
        $intro = post_string('gioi_thieu') ?: null;
        $specialtyIds = array_values(array_filter(array_map('intval', post_array('chuyen_mon_ids')), static fn (int $id): bool => $id > 0));

        if ($targetId <= 0 || mb_strlen($name) < 2 || !in_array($active, [0, 1], true)) {
            json_response(['success' => false, 'message' => 'Thông tin tài khoản không hợp lệ.'], 422);
        }
        if (($phone !== '' && !preg_match('/^[0-9+ ()-]{8,20}$/', $phone)) || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            json_response(['success' => false, 'message' => 'Email hoặc số điện thoại không hợp lệ.'], 422);
        }
        if ($targetId === $accountId && $active === 0) {
            json_response(['success' => false, 'message' => 'Không thể khóa tài khoản đang đăng nhập.'], 422);
        }

        try {
            admin_account_update_details($targetId, $name, $phone, $email, $active, $birth, $address, $goal, $height, $weight, $intro, $specialtyIds);
        } catch (mysqli_sql_exception $error) {
            json_response(['success' => false, 'message' => 'Email đã được sử dụng hoặc dữ liệu không hợp lệ.'], 422);
        }
        json_response(['success' => true, 'message' => 'Đã cập nhật tài khoản.']);
    }

    json_response(['success' => false, 'message' => 'Thao tác không hợp lệ.'], 404);
}

require_role('admin');
// Phần dưới chỉ chuẩn bị dữ liệu để hiển thị trang quản lý tài khoản.
$menu = require __DIR__ . '/../common/menus/admin.php';
$appRole = 'admin';
$activeMenu = 'accounts';
$pageTitle = 'Quản lý tài khoản';
$pageScripts = [];
$account = common_user(); // common/bootstrap.php: lấy Admin đang đăng nhập.
$accounts = data_accounts(); // common/data/admin.php: lấy danh sách tài khoản.
$specialties = data_specialties(); // common/data/shared.php: lấy danh sách chuyên môn.
$roleFilter = (string) ($_GET['role'] ?? '');
$statusFilter = (string) ($_GET['status'] ?? '');
$keyword = trim((string) ($_GET['q'] ?? ''));
$filteredAccounts = [];
foreach ($accounts as $item) {
    // Lọc theo role, trạng thái và tên/username người dùng nhập trên form GET.
    if ($roleFilter !== '' && $item['role'] !== $roleFilter) {
        continue;
    }
    if ($statusFilter !== '' && $item['status'] !== $statusFilter) {
        continue;
    }
    if ($keyword !== ''
        && stripos($item['name'], $keyword) === false
        && stripos($item['username'], $keyword) === false) {
        continue;
    }
    $filteredAccounts[] = $item;
}
$accounts = $filteredAccounts;
$totalAccounts = count($accounts);
$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$totalPages = max(1, (int) ceil($totalAccounts / $perPage));
$page = min($page, $totalPages);
$accounts = array_slice($accounts, ($page - 1) * $perPage, $perPage);
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
                    <h1>Quản lý tài khoản</h1>
                    <p><?= $totalAccounts ?> tài khoản phù hợp bộ lọc.</p>
                </div>
                <div class="page-head-actions">
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#accountCreateModal"><?= icon('person-plus') ?> Tạo tài khoản</button>
                </div>
            </div>

            <div class="app-card mb-3">
                <div class="app-card-body">
                    <form class="row g-2 align-items-end" method="get">
                        <div class="col-md-5">
                            <label class="form-label" for="account-q">Tìm kiếm</label>
                            <input class="form-control" id="account-q" name="q" value="<?= e($keyword) ?>" placeholder="Họ tên hoặc tên đăng nhập">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="account-role">Vai trò</label>
                            <select class="form-select" id="account-role" name="role">
                                <option value="">Tất cả vai trò</option>
                                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="instructor" <?= $roleFilter === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                                <option value="customer" <?= $roleFilter === 'customer' ? 'selected' : '' ?>>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="account-status">Trạng thái</label>
                            <select class="form-select" id="account-status" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="hoat_dong" <?= $statusFilter === 'hoat_dong' ? 'selected' : '' ?>>Đang hoạt động</option>
                                <option value="bi_khoa" <?= $statusFilter === 'bi_khoa' ? 'selected' : '' ?>>Bị khóa</option>
                            </select>
                        </div>
                        <div class="col-md-1"><button class="btn btn-outline-primary w-100" type="submit"><?= icon('search') ?></button></div>
                    </form>
                </div>
            </div>

            <div class="app-card mb-4">
                <div class="table-responsive">
                    <table class="table app-table mb-0">
                        <thead><tr><th>Họ tên</th><th>Tên đăng nhập</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th class="cell-actions">Thao tác</th></tr></thead>
                        <tbody>
                        <?php if ($accounts === []): ?>
                            <tr><td colspan="6" class="text-center text-muted-2 py-4">Không tìm thấy tài khoản.</td></tr>
                        <?php else: foreach ($accounts as $item): ?>
                            <tr>
                                <td class="cell-strong"><?= e($item['name']) ?></td>
                                <td><?= e($item['username']) ?></td>
                                <td><?= e(['admin' => 'Admin', 'instructor' => 'Instructor', 'customer' => 'Customer'][$item['role']] ?? $item['role']) ?></td>
                                <td><?= $item['status'] === 'hoat_dong' ? badge('Đang hoạt động', 'success') : badge('Bị khóa', 'secondary') ?></td>
                                <td><?= e($item['created']) ?></td>
                                <td class="cell-actions">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-account-detail="<?= (int) $item['id'] ?>">Chi tiết</button>
                                    <?php if ($item['role'] !== 'admin' || (int) $item['id'] !== (int) $account['id']): ?>
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-account-status="<?= (int) $item['id'] ?>" data-status="<?= $item['status'] === 'hoat_dong' ? '0' : '1' ?>">
                                            <?= $item['status'] === 'hoat_dong' ? 'Khóa' : 'Mở khóa' ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="app-card-body border-top">
                        <nav aria-label="Phân trang tài khoản">
                            <ul class="pagination mb-0 justify-content-end">
                                <?php for ($number = 1; $number <= $totalPages; $number++): ?>
                                    <?php
                                    $query = http_build_query([
                                        'q' => $keyword,
                                        'role' => $roleFilter,
                                        'status' => $statusFilter,
                                        'page' => $number,
                                    ]);
                                    ?>
                                    <li class="page-item <?= $number === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= e(url('/admin/accounts') . '?' . $query) ?>"><?= $number ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>
<div class="modal fade" id="accountDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Chi tiết tài khoản</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
            <form id="account-detail-form">
                <div class="modal-body">
                    <div class="account-detail-hero mb-3">
                        <div><h3 class="mb-1" id="account-detail-title">Chi tiết tài khoản</h3><div class="text-muted-2" id="account-detail-subtitle"></div></div>
                    </div>
                    <input type="hidden" name="vai_tro" id="detail-role">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Họ tên</label><input class="form-control" name="ho_ten" id="detail-name" required></div>
                        <div class="col-md-6"><label class="form-label">Tên đăng nhập</label><input class="form-control" id="detail-username" disabled></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" id="detail-email" type="email"></div>
                        <div class="col-md-6"><label class="form-label">Số điện thoại</label><input class="form-control" name="sdt" id="detail-phone"></div>
                        <div class="col-md-6"><label class="form-label">Trạng thái</label><select class="form-select" name="trang_thai" id="detail-status"><option value="1">Đang hoạt động</option><option value="0">Bị khóa</option></select></div>
                        <div class="col-md-6" data-detail-customer><label class="form-label">Ngày sinh</label><input class="form-control" name="ngay_sinh" id="detail-birth" type="date"></div>
                        <div class="col-md-6" data-detail-customer><label class="form-label">Mục tiêu</label><input class="form-control" name="muc_tieu" id="detail-goal"></div>
                        <div class="col-md-6" data-detail-customer><label class="form-label">Chiều cao (cm)</label><input class="form-control" name="chieu_cao" id="detail-height" type="number" step="0.1"></div>
                        <div class="col-md-6" data-detail-customer><label class="form-label">Cân nặng (kg)</label><input class="form-control" name="can_nang" id="detail-weight" type="number" step="0.1"></div>
                        <div class="col-12" data-detail-customer><label class="form-label">Địa chỉ</label><input class="form-control" name="dia_chi" id="detail-address"></div>
                        <div class="col-12" data-detail-instructor><label class="form-label">Giới thiệu Instructor</label><textarea class="form-control" name="gioi_thieu" id="detail-intro" rows="3"></textarea></div>
                        <div class="col-12" data-detail-instructor>
                            <label class="form-label">Chuyên môn</label>
                            <div class="row g-2" id="detail-specialty">
                                <?php foreach ($specialties as $specialty): ?>
                                    <div class="col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="chuyen_mon_ids[]" value="<?= (int) $specialty['id'] ?>"><span class="form-check-label"><?= e($specialty['name']) ?></span></label></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-ghost" type="button" data-bs-dismiss="modal">Đóng</button><button class="btn btn-primary" type="submit">Lưu thay đổi</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="accountCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Tạo tài khoản</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
            <form id="account-create-form">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="new-account-name">Họ và tên <span class="req">*</span></label><input class="form-control" id="new-account-name" name="ho_ten" required></div>
                        <div class="col-md-6"><label class="form-label" for="new-account-username">Tên đăng nhập <span class="req">*</span></label><input class="form-control" id="new-account-username" name="ten_dang_nhap" pattern="[A-Za-z0-9_]{3,50}" required></div>
                        <div class="col-md-6"><label class="form-label" for="new-account-password">Mật khẩu <span class="req">*</span></label><input class="form-control" id="new-account-password" name="mat_khau" type="password" minlength="6" required></div>
                        <div class="col-md-6"><label class="form-label" for="new-account-role">Vai trò <span class="req">*</span></label><select class="form-select" id="new-account-role" name="vai_tro" required><option value="customer">Customer</option><option value="instructor">Instructor</option><option value="admin">Admin</option></select></div>
                        <div class="col-md-6 account-profile-field"><label class="form-label" for="new-account-phone">Số điện thoại <span class="req">*</span></label><input class="form-control" id="new-account-phone" name="sdt" required></div>
                        <div class="col-md-6 account-profile-field"><label class="form-label" for="new-account-email">Email <span class="req">*</span></label><input class="form-control" id="new-account-email" name="email" type="email" required></div>
                    </div>
                    <div class="form-text mt-3">Tài khoản Instructor và Customer sẽ được tạo hồ sơ liên kết tự động.</div>
                </div>
                <div class="modal-footer"><button class="btn btn-ghost" type="button" data-bs-dismiss="modal">Hủy</button><button class="btn btn-primary" type="submit">Tạo tài khoản</button></div>
            </form>
        </div>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<script src="<?= e(url('/common/assets/js/admin/account.js')) ?>?v=3"></script>
<?php require $partials . '/foot.php'; ?>
