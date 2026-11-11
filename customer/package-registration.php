<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'customer') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $packageId = (int) ($input['goi_tap_id'] ?? 0);
    $db = common_db();
    $accountId = (int) $user['id'];

    $stmt = $db->prepare('SELECT id FROM hoi_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member || $packageId <= 0) {
        json_response(['success' => false, 'message' => 'Gói tập không hợp lệ.'], 422);
    }

    $stmt = $db->prepare(
        'SELECT id, thoi_han_ngay, gia FROM goi_tap
          WHERE id = ? AND trang_thai = "dang_ban" AND deleted_at IS NULL LIMIT 1'
    );
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$package) {
        json_response(['success' => false, 'message' => 'Gói tập không còn được bán.'], 422);
    }

    $from = new DateTimeImmutable('today');
    $to = $from->modify('+' . ((int) $package['thoi_han_ngay'] - 1) . ' days');
    $memberId = (int) $member['id'];
    $fromValue = $from->format('Y-m-d');
    $toValue = $to->format('Y-m-d');

    $stmt = $db->prepare(
        'INSERT INTO dang_ky_goi
            (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem, trang_thai)
         VALUES (?, ?, ?, ?, ?, "chua_thanh_toan")'
    );
    $price = (int) $package['gia'];
    $stmt->bind_param('iissi', $memberId, $packageId, $fromValue, $toValue, $price);
    $stmt->execute();
    $stmt->close();

    json_response(['success' => true, 'message' => 'Đã gửi yêu cầu đăng ký. Vui lòng thanh toán tại quầy.'], 201);
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
                    <p>Chọn gói phù hợp. Bạn thanh toán và nhận kích hoạt tại quầy.</p>
                </div>
                <div class="page-head-actions">
                    <a class="btn btn-ghost" href="<?= e(url('/customer/history')) ?>"><?= icon('clock-history') ?> Lịch sử gói</a>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($packages as $package): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="app-card h-100">
                            <div class="app-card-header">
                                <div>
                                    <h2><?= e($package['name']) ?></h2>
                                    <p class="sub"><?= (int) $package['days'] ?> ngày</p>
                                </div>
                            </div>
                            <div class="app-card-body d-flex flex-column">
                                <div class="fs-4 fw-700 text-brand mb-2"><?= e(money($package['price'])) ?></div>
                                <p class="text-muted-2 flex-grow-1"><?= e($package['desc'] ?? '') ?></p>
                                <button class="btn btn-primary w-100" type="button"
                                        data-package-register="<?= (int) $package['id'] ?>"
                                        data-package-name="<?= e($package['name']) ?>">
                                    <?= icon('check-lg') ?> Đăng ký gói này
                                </button>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!$packages): ?>
                <div class="app-card"><div class="app-card-body text-muted-2">Hiện chưa có gói tập đang bán.</div></div>
            <?php endif; ?>
        </main>
    </div>
</div>
<div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>
<script>
document.addEventListener('click', async function (event) {
    var button = event.target.closest('[data-package-register]');
    if (!button) return;
    if (!confirm('Đăng ký gói "' + button.dataset.packageName + '"?')) return;

    button.disabled = true;
    try {
        var response = await fetch(window.location.pathname, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify({goi_tap_id: button.dataset.packageRegister}),
            credentials: 'same-origin'
        });
        var result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Không thể đăng ký gói tập.');
        alert(result.message);
        window.location.href = <?= json_encode(url('/customer/history')) ?>;
    } catch (error) {
        alert(error.message);
        button.disabled = false;
    }
});
</script>
<?php require $partials . '/foot.php'; ?>
