<?php
require __DIR__ . '/../common/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = common_user();
    if (!$user || $user['vai_tro'] !== 'customer') {
        json_response(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }

    $input = $_POST;

    $packageId = (int) ($input['goi_tap_id'] ?? 0);
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

    if (!$member || $packageId <= 0) {
        json_response(['success' => false, 'message' => 'Gói tập không hợp lệ.'], 422);
    }

    $stmt = $db->prepare(
        'SELECT id, ten_goi, thoi_han_ngay, gia, cap_do FROM goi_tap
          WHERE id = ? AND trang_thai = "dang_ban" AND deleted_at IS NULL LIMIT 1'
    );
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$package) {
        json_response(['success' => false, 'message' => 'Gói tập không còn được bán.'], 422);
    }

    $memberId = (int) $member['id'];

    $db->begin_transaction();

    try {
        $stmt = $db->prepare(
            'SELECT id FROM dang_ky_goi
              WHERE hoi_vien_id = ? AND goi_tap_id = ? AND trang_thai = "chua_thanh_toan"
              LIMIT 1'
        );
        $stmt->bind_param('ii', $memberId, $packageId);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($pending) {
            $db->rollback();
            json_response(['success' => false, 'message' => 'Bạn đã gửi yêu cầu cho gói này và đang chờ thanh toán.'], 422);
        }

        $active = package_active_registration($memberId, true);

        if ($active) {
            $activeRank = package_level_rank($active['cap_do'] ?? 'basic');
            $newRank = package_level_rank($package['cap_do'] ?? 'basic');

            if ($newRank < $activeRank) {
                $db->rollback();
                json_response([
                    'success' => false,
                    'message' => 'Gói hiện tại còn hiệu lực đến ' . date('d/m/Y', strtotime((string) $active['ngay_ket_thuc']))
                        . '. Bạn không thể mua gói thấp hơn khi gói hiện tại chưa hết hạn.',
                ], 422);
            }

            if ($newRank === $activeRank) {
                $db->rollback();
                json_response([
                    'success' => false,
                    'message' => 'Bạn đang có gói cùng cấp còn hiệu lực. Vui lòng chờ hết hạn hoặc nâng cấp lên gói cao hơn.',
                ], 422);
            }

            if (package_pending_upgrade($memberId)) {
                $db->rollback();
                json_response(['success' => false, 'message' => 'Bạn đã có yêu cầu nâng cấp đang chờ thanh toán.'], 422);
            }

            $quote = package_upgrade_quote($active, $package);
            if ($quote['remaining_days'] <= 0) {
                $db->rollback();
                json_response(['success' => false, 'message' => 'Gói hiện tại đã hết hạn, vui lòng đăng ký gói mới.'], 422);
            }

            $stmt = $db->prepare(
                'INSERT INTO dang_ky_goi
                    (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem,
                     gia_niem_yet, so_tien_giam, trang_thai, loai_giao_dich, dang_ky_goc_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, "chua_thanh_toan", "nang_cap", ?)'
            );
            $fee = (int) $quote['fee'];
            $newValue = (int) $quote['new_segment_value'];
            $credit = (int) $quote['credit'];
            $fromValue = (string) $quote['start'];
            $toValue = (string) $quote['end'];
            $sourceId = (int) $active['id'];
            $stmt->bind_param('iissiiii', $memberId, $packageId, $fromValue, $toValue, $fee, $newValue, $credit, $sourceId);
            $stmt->execute();
            $stmt->close();
            $db->commit();

            json_response([
                'success' => true,
                'message' => $fee > 0
                    ? 'Đã gửi yêu cầu nâng cấp lên ' . $package['ten_goi'] . '. Phí nâng cấp: ' . money($fee) . '. Vui lòng thanh toán tại quầy.'
                    : 'Đã gửi yêu cầu nâng cấp lên ' . $package['ten_goi'] . ' miễn phí. Vui lòng xác nhận tại quầy.',
                'goi_tap_id' => $packageId,
                'loai_giao_dich' => 'nang_cap',
                'phi_nang_cap' => $fee,
                'so_ngay_con_lai' => (int) $quote['remaining_days'],
                'ngay_ket_thuc' => $toValue,
            ], 201);
        }

        $from = new DateTimeImmutable('today');
        $to = $from->modify('+' . ((int) $package['thoi_han_ngay'] - 1) . ' days');
        $fromValue = $from->format('Y-m-d');
        $toValue = $to->format('Y-m-d');
        $price = (int) $package['gia'];

        $stmt = $db->prepare(
            'INSERT INTO dang_ky_goi
                (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem,
                 gia_niem_yet, so_tien_giam, trang_thai, loai_giao_dich)
             VALUES (?, ?, ?, ?, ?, ?, 0, "chua_thanh_toan", "mua_moi")'
        );
        $stmt->bind_param('iissii', $memberId, $packageId, $fromValue, $toValue, $price, $price);
        $stmt->execute();
        $stmt->close();
        $db->commit();

        json_response([
            'success' => true,
            'message' => 'Đã gửi yêu cầu đăng ký. Vui lòng thanh toán tại quầy.',
            'goi_tap_id' => $packageId,
            'loai_giao_dich' => 'mua_moi',
        ], 201);
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
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

// Trạng thái đăng ký gói của hội viên: active (còn hạn) / pending (chờ thanh toán).
$memberId = (int) (data_current_member()['id'] ?? 0);
$packageStates = [];
if ($memberId > 0) {
    $stmt = common_db()->prepare(
        'SELECT goi_tap_id, trang_thai, ngay_ket_thuc FROM dang_ky_goi WHERE hoi_vien_id = ?'
    );
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $today = date('Y-m-d');
    foreach ($rows as $row) {
        $pid = (int) $row['goi_tap_id'];
        if ($row['trang_thai'] === 'da_thanh_toan' && $row['ngay_ket_thuc'] >= $today) {
            $packageStates[$pid] = 'active';
        } elseif ($row['trang_thai'] === 'chua_thanh_toan' && ($packageStates[$pid] ?? '') !== 'active') {
            $packageStates[$pid] = 'pending';
        }
    }
}

$activeRegistration = $memberId > 0 ? package_active_registration($memberId) : null;
$activeRank = $activeRegistration ? package_level_rank($activeRegistration['cap_do'] ?? 'basic') : 0;
$activeEndLabel = $activeRegistration
    ? date('d/m/Y', strtotime((string) $activeRegistration['ngay_ket_thuc']))
    : '';

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
                <div class="page-head-actions">
                    <a class="btn btn-ghost" href="<?= e(url('/customer/history')) ?>"><?= icon('clock-history') ?> Lịch sử gói</a>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach ($packages as $package):
                    $state = $packageStates[(int) $package['id']] ?? '';
                    $packageRank = package_level_rank($package['level'] ?? 'basic');
                    $upgradeQuote = null;
                    $canUpgrade = false;
                    $blockedLower = false;
                    $blockedSame = false;
                    if ($state === '' && $activeRegistration) {
                        if ($packageRank < $activeRank) {
                            $blockedLower = true;
                        } elseif ($packageRank === $activeRank) {
                            $blockedSame = true;
                        } else {
                            $canUpgrade = true;
                            $upgradeQuote = package_upgrade_quote($activeRegistration, $package);
                        }
                    }
                    $upgradeFee = (int) ($upgradeQuote['fee'] ?? 0);
                ?>
                    <div class="col-md-6 col-xl-4" data-package-card data-package-id="<?= (int) $package['id'] ?>">
                        <article class="app-card h-100">
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
                                <span data-package-action>
                                    <?php if ($state === 'active'): ?>
                                        <span class="badge-status success">Đang sử dụng</span>
                                        <button class="btn btn-outline-secondary w-100 mt-2" type="button" disabled>Đang sử dụng</button>
                                    <?php elseif ($state === 'pending'): ?>
                                        <span class="badge-status warning">Chờ thanh toán</span>
                                        <button class="btn btn-outline-secondary w-100 mt-2" type="button" disabled>Chờ thanh toán</button>
                                    <?php elseif ($blockedLower): ?>
                                        <span class="badge-status muted">Gói hiện tại cao hơn</span>
                                        <button class="btn btn-outline-secondary w-100 mt-2" type="button" disabled>Không thể mua gói thấp hơn</button>
                                        <div class="text-tiny text-muted-2 mt-2">Đang dùng đến <?= e($activeEndLabel) ?></div>
                                    <?php elseif ($blockedSame): ?>
                                        <span class="badge-status muted">Đã có gói cùng cấp</span>
                                        <button class="btn btn-outline-secondary w-100 mt-2" type="button" disabled>Chờ hết hạn</button>
                                        <div class="text-tiny text-muted-2 mt-2">Đang dùng đến <?= e($activeEndLabel) ?></div>
                                    <?php elseif ($canUpgrade && $upgradeQuote): ?>
                                        <span class="badge-status warning">Nâng cấp</span>
                                        <button class="btn btn-primary w-100 mt-2" type="button"
                                                data-package-upgrade="<?= (int) $package['id'] ?>"
                                                data-package-name="<?= e($package['name']) ?>"
                                                data-package-fee="<?= $upgradeFee ?>"
                                                data-package-end="<?= e($activeEndLabel) ?>"
                                                data-package-credit="<?= (int) $upgradeQuote['credit'] ?>"
                                                data-package-remaining="<?= (int) $upgradeQuote['remaining_days'] ?>">
                                            <?= icon('arrow-up-circle') ?> Nâng cấp · <?= $upgradeFee > 0 ? e(money($upgradeFee)) : 'Miễn phí' ?>
                                        </button>
                                        <div class="text-tiny text-muted-2 mt-2">
                                            Giữ hạn đến <?= e($activeEndLabel) ?> ·
                                            khấu trừ <?= e(money((int) $upgradeQuote['credit'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-primary w-100" type="button"
                                                data-package-register="<?= (int) $package['id'] ?>"
                                                data-package-name="<?= e($package['name']) ?>">
                                            <?= icon('check-lg') ?> Đăng ký gói
                                        </button>
                                    <?php endif; ?>
                                </span>
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
<script src="<?= e(url('/common/assets/js/customer/package-registration.js')) ?>?v=2"></script>
<?php require $partials . '/foot.php'; ?>
