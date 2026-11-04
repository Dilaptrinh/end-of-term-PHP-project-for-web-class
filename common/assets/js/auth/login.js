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
            alert('Vui lòng nhập tên đăng nhập và mật khẩu.');
            return;
        }

        submit.classList.add('is-loading');
        submit.disabled = true;

        try {
            const response = await fetch(form.dataset.loginUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    ten_dang_nhap: username.value.trim(),
                    mat_khau: password.value
                }),
                credentials: 'same-origin'
            });

            const data = await response.json().catch(function () {
                return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
            });

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Không thể đăng nhập.');
            }

            const role = data.data && data.data.vai_tro ? data.data.vai_tro : 'customer';
            window.location.href = redirectFor(role);
        } catch (error) {
            alert(error.message || 'Không thể đăng nhập.');
            submit.classList.remove('is-loading');
            submit.disabled = false;
        }
    });
})();
