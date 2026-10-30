/* Đăng nhập thật qua API, chuyển hướng theo vai trò trả về từ CSDL. */
(function () {
    'use strict';

    const form = document.getElementById('login-form');
    if (!form) return;

    const username = document.getElementById('login-username');
    const password = document.getElementById('login-password');
    const submit = document.getElementById('login-submit');

    const toggle = document.getElementById('toggle-password');
    if (toggle) {
        toggle.addEventListener('click', function () {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.innerHTML = showing ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
            toggle.setAttribute('aria-label', showing ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
        });
    }

    function redirectFor(role) {
        const key = 'redirect' + role.charAt(0).toUpperCase() + role.slice(1);
        return form.dataset[key] || form.dataset.redirectCustomer || '/';
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (username.value.trim() === '' || password.value.trim() === '') {
            window.showToast('Thiếu thông tin', 'Vui lòng nhập tên đăng nhập và mật khẩu.', 'warning');
            return;
        }

        submit.classList.add('is-loading');
        submit.disabled = true;

        try {
            const response = await window.api.post('/api/auth/login', {
                ten_dang_nhap: username.value.trim(),
                mat_khau: password.value
            });

            const role = response && response.data && response.data.vai_tro
                ? response.data.vai_tro
                : 'customer';

            window.location.href = redirectFor(role);
        } catch (error) {
            window.showToast('Đăng nhập thất bại', error.message || 'Không thể đăng nhập.', 'danger');
            submit.classList.remove('is-loading');
            submit.disabled = false;
        }
    });
})();
