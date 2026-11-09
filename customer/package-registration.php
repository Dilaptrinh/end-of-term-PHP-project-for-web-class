<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'customer') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $input = json_decode(file_get_contents('php://input') ?: '', true);
    $packageId = (int) (($input['goi_tap_id'] ?? $_POST['goi_tap_id'] ?? 0));
    $db = common_db();

    $memberStmt = $db->prepare('SELECT id FROM hoi_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
    $accountId = (int) $user['id'];
    $memberStmt->bind_param('i', $accountId);
    $memberStmt->execute();
    $member = $memberStmt->get_result()->fetch_assoc();
    $memberStmt->close();

    if (!$member || $packageId <= 0) {
        json_response(['success' => false, 'message' => 'Gói tập không hợp lệ.'], 422);
    }

    $packageStmt = $db->prepare(
        'SELECT id, thoi_han_ngay, gia FROM goi_tap
          WHERE id = ? AND trang_thai = "dang_ban" AND deleted_at IS NULL LIMIT 1'
    );
    $packageStmt->bind_param('i', $packageId);
    $packageStmt->execute();
    $package = $packageStmt->get_result()->fetch_assoc();
    $packageStmt->close();
    if (!$package) {
        json_response(['success' => false, 'message' => 'Gói tập không còn được bán.'], 422);
    }

    $memberId = (int) $member['id'];
    $existingStmt = $db->prepare(
        'SELECT id FROM dang_ky_goi
          WHERE hoi_vien_id = ? AND trang_thai IN ("chua_thanh_toan", "da_thanh_toan")
            AND ngay_ket_thuc >= CURDATE()
          LIMIT 1'
    );
    $existingStmt->bind_param('i', $memberId);
    $existingStmt->execute();
    $existing = $existingStmt->get_result()->fetch_assoc();
    $existingStmt->close();
    if ($existing) {
        json_response(['success' => false, 'message' => 'Bạn đang có gói tập còn hiệu lực hoặc đang chờ thanh toán.'], 409);
    }

    $start = new DateTimeImmutable('today');
    $end = $start->modify('+' . (int) $package['thoi_han_ngay'] . ' days');
    $stmt = $db->prepare(
        'INSERT INTO dang_ky_goi
            (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem, trang_thai)
         VALUES (?, ?, ?, ?, ?, "chua_thanh_toan")'
    );
    $startDate = $start->format('Y-m-d');
    $endDate = $end->format('Y-m-d');
    $price = (int) $package['gia'];
    $stmt->bind_param('iissi', $memberId, $packageId, $startDate, $endDate, $price);
    $stmt->execute();
    $stmt->close();

    json_response(['success' => true, 'message' => 'Đã gửi yêu cầu đăng ký gói. Vui lòng thanh toán tại quầy.'], 201);
}

require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';
$appRole = 'customer';
$activeMenu = 'package-registration';
$pageTitle = 'Đăng ký gói tập';
$pageScripts = [];
$packages = array_values(array_filter(
    data_packages(),
    static fn (array $package): bool => $package['status'] === 'dang_ban'
));
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
                    <h1>Đăng ký gói tập</h1>
                    <p>Chọn gói phù hợp. Yêu cầu sẽ ở trạng thái chờ thanh toán.</p>
                </div>
            </div>
            <div class="row g-3">
                <?php foreach ($packages as $package): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="app-card h-100">
                            <div class="app-card-header">
                                <div>
                                    <h2><?= e($package['name']) ?></h2>
                                    <p class="sub"><?= e($package['desc']) ?></p>
                                </div>
                            </div>
                            <div class="app-card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <strong><?= e(money($package['price'])) ?></strong>
                                    <span><?= (int) $package['days'] ?> ngày</span>
                                </div>
                                <button class="btn btn-primary w-100" type="button"
                                        data-register-package="<?= (int) $package['id'] ?>"
                                        data-register-name="<?= e($package['name']) ?>">
                                    <?= icon('check-lg') ?> Đăng ký gói
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!$packages): ?>
                <div class="app-card mt-3"><div class="app-card-body text-muted-2">Hiện chưa có gói tập đang bán.</div></div>
            <?php endif; ?>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<script>
document.addEventListener('click', async function (event) {
    var button = event.target.closest('[data-register-package]');
    if (!button) return;
    if (!confirm('Đăng ký gói "' + button.dataset.registerName + '"?')) return;
    try {
        var response = await fetch(window.location.pathname, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({goi_tap_id: button.dataset.registerPackage}),
            credentials: 'same-origin'
        });
        var result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Không thể đăng ký gói.');
        alert(result.message);
        window.location.href = <?= json_encode(url('/customer/history')) ?>;
    } catch (error) {
        alert(error.message);
    }
});
</script>
<?php require $partials . '/foot.php'; ?>
