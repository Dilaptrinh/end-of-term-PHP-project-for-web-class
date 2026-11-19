<?php
require __DIR__ . '/../common/bootstrap.php';
require __DIR__ . '/queries.php';

function account_flash_redirect(string $type, string $message): never
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
    redirect('/admin/accounts');
}

// API GET: trả JSON chi tiết tài khoản cho modal (JS render HTML).
$requestAction = (string) ($_GET['action'] ?? '');
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $requestAction === 'detail') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }
    $detail = admin_account_detail((int) ($_GET['id'] ?? 0));
    if (!$detail) {
        json_response(['success' => false, 'message' => 'Không tìm thấy tài khoản.'], 404);
    }
    json_response([
        'success' => true,
        'data' => $detail,
        'specialties' => data_specialties(),
    ]);
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'admin') {
        redirect('/login');
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
            account_flash_redirect('danger', 'Tên đăng nhập hoặc họ tên không hợp lệ.');
        }
        if (!in_array($role, ['admin', 'instructor', 'customer'], true)) {
            account_flash_redirect('danger', 'Vai trò không hợp lệ.');
        }
        if (strlen($password) < 6) {
            account_flash_redirect('danger', 'Mật khẩu phải có ít nhất 6 ký tự.');
        }
        if (in_array($role, ['instructor', 'customer'], true) && ($phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            account_flash_redirect('danger', 'Giáo viên và hội viên cần số điện thoại, email hợp lệ.');
        }
        if (admin_username_exists($username)) {
            account_flash_redirect('danger', 'Tên đăng nhập đã tồn tại.');
        }

        try {
            admin_account_create($username, $password, $name, $role, $phone, $email);
        } catch (mysqli_sql_exception $error) {
            account_flash_redirect('danger', 'Email đã được sử dụng hoặc dữ liệu bị trùng.');
        }
        account_flash_redirect('success', 'Đã tạo tài khoản và hồ sơ liên quan.');
    }

    if ($action === 'status') {
        // Đổi trạng thái đăng nhập của tài khoản.
        $targetId = post_int('id') ?: (int) ($_GET['id'] ?? 0);
        $active = post_int('trang_thai');
        if ($targetId <= 0 || !in_array($active, [0, 1], true)) {
            account_flash_redirect('danger', 'Dữ liệu trạng thái không hợp lệ.');
        }
        if (!admin_account_status($targetId, $active)) {
            account_flash_redirect('danger', 'Không thể thay đổi tài khoản admin hoặc tài khoản không tồn tại.');
        }
        account_flash_redirect('success', $active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.');
    }

    if ($action === 'update') {
        // Cập nhật thông tin chung và thông tin hồ sơ theo role.
        $targetId = post_int('id') ?: (int) ($_GET['id'] ?? 0);
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

        // Trả JSON dòng đã cập nhật; JS render HTML.
        $row = null;
        foreach (data_accounts() as $item) {
            if ((int) $item['id'] === $targetId) {
                $row = $item;
                break;
            }
        }
        json_response(['success' => true, 'message' => 'Đã cập nhật tài khoản.', 'data' => $row]);
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
// Modal chi tiết được nạp bằng AJAX, nội dung HTML do PHP render.
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
                        <div class="col-md-2">
                            <label class="form-label" for="account-role">Vai trò</label>
                            <select class="form-select" id="account-role" name="role">
                                <option value="">Tất cả vai trò</option>
                                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="instructor" <?= $roleFilter === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                                <option value="customer" <?= $roleFilter === 'customer' ? 'selected' : '' ?>>Customer</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="account-status">Trạng thái</label>
                            <select class="form-select" id="account-status" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="hoat_dong" <?= $statusFilter === 'hoat_dong' ? 'selected' : '' ?>>Đang hoạt động</option>
                                <option value="bi_khoa" <?= $statusFilter === 'bi_khoa' ? 'selected' : '' ?>>Bị khóa</option>
                            </select>
                        </div>
                        <div class="col-md-1"><button class="btn btn-outline-primary w-100" type="submit" title="Tìm"><?= icon('search') ?></button></div>
                        <div class="col-md-1"><a class="btn btn-ghost w-100" href="<?= e(url('/admin/accounts')) ?>" title="Đặt lại"><?= icon('arrow-counterclockwise') ?></a></div>
                    </form>
                </div>
            </div>

            <div class="app-card mb-4">
                <div class="table-responsive">
                    <table class="table app-table mb-0">
                        <thead><tr><th>Họ tên</th><th>Tên đăng nhập</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th class="cell-actions">Thao tác</th></tr></thead>
                        <tbody id="accounts-body" data-current-id="<?= (int) $account['id'] ?>"></tbody>
                    </table>
                </div>
                <script type="application/json" id="account-init"><?= json_encode($accounts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
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
                <div class="modal-body" id="account-detail-body"></div>
                <div class="modal-footer"><button class="btn btn-ghost" type="button" data-bs-dismiss="modal">Đóng</button><button class="btn btn-primary" type="submit">Lưu thay đổi</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="accountCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Tạo tài khoản</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
            <form id="account-create-form" method="post" action="<?= e(url('/admin/accounts')) ?>?action=create">
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
<script src="<?= e(url('/common/assets/js/admin/accounts.js')) ?>?v=7"></script>
<?php require $partials . '/foot.php'; ?>
