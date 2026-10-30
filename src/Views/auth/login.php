<?php
/** @var string $pageTitle */
?>
<div class="auth-card">
    <h1>Chào mừng trở lại</h1>
    <p class="auth-sub">Đăng nhập một cổng cho hội viên, giáo viên và quản trị viên.</p>

    <form id="login-form" class="mt-3" novalidate
          data-redirect-admin="<?= e(url('/admin')) ?>"
          data-redirect-instructor="<?= e(url('/instructor')) ?>"
          data-redirect-customer="<?= e(url('/customer')) ?>">
        <div class="mb-3">
            <label class="form-label" for="login-username">Tên đăng nhập <span class="req">*</span></label>
            <input class="form-control" id="login-username" name="username" type="text" placeholder="Nhập tên đăng nhập" required autocomplete="username">
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label" for="login-password">Mật khẩu <span class="req">*</span></label>
                <a class="text-tiny fw-700" href="#" data-toast="Tính năng quên mật khẩu sẽ được nối API." data-toast-title="Quên mật khẩu">Quên mật khẩu?</a>
            </div>
            <div class="input-group">
                <input class="form-control" id="login-password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
                <button class="btn btn-outline-primary" type="button" id="toggle-password" aria-label="Hiện mật khẩu">
                    <?= icon('eye') ?>
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="form-check">
                <input class="form-check-input" id="remember" name="remember" type="checkbox" checked>
                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <a class="text-tiny" href="<?= e(url('/register')) ?>">Chưa có tài khoản?</a>
        </div>

        <button class="btn btn-primary w-100 btn-lg" type="submit" id="login-submit">Đăng nhập</button>
    </form>

    <p class="text-center text-tiny text-muted-2 mt-3 mb-0">
        Bằng việc đăng nhập, bạn đồng ý với <a href="#">Điều khoản sử dụng</a> của FitZone.
    </p>
</div>
