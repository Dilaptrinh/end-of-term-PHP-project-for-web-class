<?php
require_once __DIR__ . '/../data/data.php';

$genderOptions = ['nam' => 'Nam', 'nu' => 'Nữ', 'khac' => 'Khác'];
$profile = data_customer_profile();
$member = data_current_member();
$memberCode = 'HV-' . str_pad((string) ($member['id'] ?? 0), 4, '0', STR_PAD_LEFT);
?>
<div class="page-head">
    <div>
        <h1>Quản lý tài khoản</h1>
        <p>Cập nhật hồ sơ cá nhân và bảo mật tài khoản của bạn.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-ghost" href="<?= e(url('/customer')) ?>"><?= icon('arrow-left') ?> Về tổng quan</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="app-card mb-3">
            <div class="app-card-body text-center">
                <?= avatar($member['ho_ten'] ?? 'Hội viên', 'avatar-xl', 'mx-auto') ?>
                <h2 class="mt-3 mb-1" style="font-size:1.05rem;"><?= e($member['ho_ten'] ?? '') ?></h2>
                <p class="text-muted-2 text-small mb-2">Hội viên · Mã <?= e($memberCode) ?></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <?= badge('Đang hoạt động', 'success') ?>
                    <?= badge('Premium', 'primary') ?>
                </div>
                <button class="btn btn-outline-primary btn-sm" type="button" data-toast="Tính năng tải ảnh sẽ được nối API." data-toast-title="Đổi ảnh đại diện">
                    <?= icon('camera') ?> Đổi ảnh đại diện
                </button>
                <div class="form-text mt-2">JPG hoặc PNG, tối đa 2MB.</div>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header"><h2>Thông tin hội viên</h2></div>
            <div class="app-card-body">
                <div class="kv-list">
                    <div class="kv"><span>Mã hội viên</span><span><?= e($memberCode) ?></span></div>
                    <div class="kv"><span>Ngày tham gia</span><span><?= e($member['joined'] ?? '—') ?></span></div>
                    <div class="kv"><span>Gói hiện tại</span><span><?= e($member['package'] ?? '—') ?></span></div>
                    <div class="kv"><span>Hết hạn</span><span><?= e($member['package_expiry'] ?? '—') ?></span></div>
                    <div class="kv"><span>Lần đăng nhập cuối</span><span><?= e(date('d/m/Y H:i')) ?></span></div>
                </div>
                <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(url('/customer/history')) ?>"><?= icon('clock-history') ?> Xem lịch sử gói tập</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="app-card mb-3">
            <div class="app-card-header">
                <div>
                    <h2>Hồ sơ cá nhân</h2>
                    <p class="sub">Thông tin dùng để liên hệ và quản lý tài khoản</p>
                </div>
            </div>
            <div class="app-card-body">
                <form data-api-url="/api/profile" data-api-method="PUT" data-api-success-title="Đã lưu hồ sơ">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="acc-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="acc-name" name="ho_ten" type="text" value="<?= e($member['ho_ten'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="acc-phone">Số điện thoại <span class="req">*</span></label>
                            <input class="form-control" id="acc-phone" name="sdt" type="tel" value="<?= e($member['sdt'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="acc-email">Email <span class="req">*</span></label>
                            <input class="form-control" id="acc-email" name="email" type="email" value="<?= e($member['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="acc-birth">Ngày sinh</label>
                            <input class="form-control" id="acc-birth" name="ngay_sinh" type="date" value="<?= e($member['ngay_sinh'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="acc-gender">Giới tính</label>
                            <select class="form-select" id="acc-gender" name="gioi_tinh">
                                <?php foreach ($genderOptions as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= (($member['gioi_tinh'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="acc-status">Trạng thái hội viên</label>
                            <input class="form-control" id="acc-status" type="text" value="Đang hoạt động" disabled>
                            <div class="form-text">Liên hệ quản trị viên nếu cần thay đổi.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="acc-weight">Cân nặng (kg)</label>
                            <input class="form-control" id="acc-weight" name="can_nang" type="number" min="1" max="500" step="0.1" value="<?= e($profile['weight']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="acc-height">Chiều cao (cm)</label>
                            <input class="form-control" id="acc-height" name="chieu_cao" type="number" min="50" max="250" step="1" value="<?= e($profile['height']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="acc-goal">Mục tiêu tập luyện</label>
                            <select class="form-select" id="acc-goal" name="muc_tieu">
                                <option selected><?= e($profile['goal']) ?></option>
                                <option>Giảm cân</option>
                                <option>Tăng cơ</option>
                                <option>Cải thiện sức bền</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="acc-address">Địa chỉ</label>
                            <input class="form-control" id="acc-address" name="dia_chi" type="text" value="<?= e($member['dia_chi'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="acc-note">Ghi chú sức khỏe</label>
                            <textarea class="form-control" id="acc-note" name="ghi_chu" rows="3" placeholder="Dị ứng, chấn thương cũ, lưu ý khi tập…"><?= e($member['ghi_chu'] ?? '') ?></textarea>
                            <div class="form-text">Thông tin này giúp giáo viên điều chỉnh bài tập phù hợp.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary" type="submit"><?= icon('check-lg') ?> Lưu thay đổi</button>
                        <button class="btn btn-ghost" type="reset">Hủy</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h2>Đổi mật khẩu</h2>
                    <p class="sub">Nên đổi mật khẩu định kỳ 3–6 tháng</p>
                </div>
                <span class="stat-icon ms-auto"><?= icon('shield-lock') ?></span>
            </div>
            <div class="app-card-body">
                <form data-api-url="/api/profile/password" data-api-method="POST" data-api-success-title="Đã đổi mật khẩu">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="pw-current">Mật khẩu hiện tại <span class="req">*</span></label>
                            <input class="form-control" id="pw-current" name="mat_khau_cu" type="password" required autocomplete="current-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pw-new">Mật khẩu mới <span class="req">*</span></label>
                            <input class="form-control" id="pw-new" name="mat_khau_moi" type="password" required minlength="6" autocomplete="new-password">
                            <div class="form-text">Tối thiểu 6 ký tự.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="pw-confirm">Nhập lại mật khẩu <span class="req">*</span></label>
                            <input class="form-control" id="pw-confirm" name="mat_khau_xac_nhan" type="password" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><?= icon('key') ?> Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>
