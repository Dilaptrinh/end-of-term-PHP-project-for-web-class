<?php
require_once __DIR__ . '/../data/data.php';

$specialties = ['Yoga', 'Pilates', 'HIIT', 'Boxing', 'Gym & Strength', 'Zumba', 'Cardio'];
$instructor = data_current_instructor();
$instructorCode = 'GV-' . str_pad((string) ($instructor['id'] ?? 0), 4, '0', STR_PAD_LEFT);
?>
<div class="page-head">
    <div>
        <h1>Quản lý tài khoản</h1>
        <p>Thông tin cá nhân, chuyên môn và bảo mật tài khoản giáo viên.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-ghost" href="<?= e(url('/instructor')) ?>"><?= icon('arrow-left') ?> Về tổng quan</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="app-card mb-3">
            <div class="app-card-body text-center">
                <?= avatar($instructor['name'] ?? 'Giáo viên', 'avatar-xl', 'mx-auto') ?>
                <h2 class="mt-3 mb-1" style="font-size:1.05rem;"><?= e($instructor['name'] ?? '') ?></h2>
                <p class="text-muted-2 text-small mb-2">Giáo viên · Mã <?= e($instructorCode) ?></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <?= badge(($instructor['status'] ?? '') === 'dang_lam' ? 'Đang hoạt động' : 'Ngừng hoạt động', ($instructor['status'] ?? '') === 'dang_lam' ? 'success' : 'secondary') ?>
                    <?= badge($instructor['specialty'] ?? '—', 'primary') ?>
                </div>
                <button class="btn btn-outline-primary btn-sm" type="button" data-toast="Tính năng tải ảnh sẽ được nối API." data-toast-title="Đổi ảnh đại diện">
                    <?= icon('camera') ?> Đổi ảnh đại diện
                </button>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header"><h2>Thông tin giảng dạy</h2></div>
            <div class="app-card-body">
                <div class="kv-list">
                    <div class="kv"><span>Mã giáo viên</span><span><?= e($instructorCode) ?></span></div>
                    <div class="kv"><span>Chuyên môn</span><span><?= e($instructor['specialty'] ?? '—') ?></span></div>
                    <div class="kv"><span>Số lớp phụ trách</span><span><?= e((string) ($instructor['classes'] ?? 0)) ?></span></div>
                    <div class="kv"><span>Tổng học viên</span><span><?= e((string) ($instructor['students'] ?? 0)) ?></span></div>
                </div>
                <a class="btn btn-outline-primary btn-sm w-100 mt-3" href="<?= e(url('/instructor/classes')) ?>"><?= icon('journal-bookmark') ?> Lớp đang dạy</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="app-card mb-3">
            <div class="app-card-header">
                <div>
                    <h2>Hồ sơ giáo viên</h2>
                    <p class="sub">Thông tin liên hệ và chuyên môn</p>
                </div>
            </div>
            <div class="app-card-body">
                <form data-api-url="/api/profile" data-api-method="PUT" data-api-success-title="Đã lưu hồ sơ">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="ins-name">Họ và tên <span class="req">*</span></label>
                            <input class="form-control" id="ins-name" name="ho_ten" type="text" value="<?= e($instructor['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-phone">Số điện thoại <span class="req">*</span></label>
                            <input class="form-control" id="ins-phone" name="sdt" type="tel" value="<?= e($instructor['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-email">Email <span class="req">*</span></label>
                            <input class="form-control" id="ins-email" name="email" type="email" value="<?= e($instructor['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-specialty">Chuyên môn</label>
                            <select class="form-select" id="ins-specialty" name="chuyen_mon">
                                <?php foreach ($specialties as $specialty): ?>
                                    <option <?= $specialty === ($instructor['specialty'] ?? '') ? 'selected' : '' ?>><?= e($specialty) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ins-desc">Giới thiệu</label>
                            <textarea class="form-control" id="ins-desc" name="gioi_thieu" rows="3"><?= e($instructor['gioi_thieu'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-account">Tên đăng nhập</label>
                            <input class="form-control" id="ins-account" type="text" value="thuha" disabled>
                            <div class="form-text">Liên hệ quản trị viên để thay đổi.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ins-last-login">Đăng nhập cuối</label>
                            <input class="form-control" id="ins-last-login" type="text" value="<?= e(date('d/m/Y H:i')) ?>" disabled>
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
                    <p class="sub">Bảo vệ tài khoản giảng dạy của bạn</p>
                </div>
                <span class="stat-icon ms-auto"><?= icon('shield-lock') ?></span>
            </div>
            <div class="app-card-body">
                <form data-api-url="/api/profile/password" data-api-method="POST" data-api-success-title="Đã đổi mật khẩu">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="ins-pw-current">Mật khẩu hiện tại <span class="req">*</span></label>
                            <input class="form-control" id="ins-pw-current" name="mat_khau_cu" type="password" required autocomplete="current-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ins-pw-new">Mật khẩu mới <span class="req">*</span></label>
                            <input class="form-control" id="ins-pw-new" name="mat_khau_moi" type="password" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ins-pw-confirm">Nhập lại mật khẩu <span class="req">*</span></label>
                            <input class="form-control" id="ins-pw-confirm" name="mat_khau_xac_nhan" type="password" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><?= icon('key') ?> Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>
