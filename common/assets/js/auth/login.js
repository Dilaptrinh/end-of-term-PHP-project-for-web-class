(function () {
    'use strict';

    var toggle = document.getElementById('toggle-password');
    var password = document.getElementById('login-password');
    if (!toggle || !password) return;

    toggle.addEventListener('click', function () {
        var showing = password.type === 'text';
        password.type = showing ? 'password' : 'text';
        toggle.innerHTML = showing ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        toggle.setAttribute('aria-label', showing ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
    });
})();
