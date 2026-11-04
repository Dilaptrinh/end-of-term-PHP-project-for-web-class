(function () {
    'use strict';

    var endpoint = window.location.pathname;

    document.getElementById('register-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var data = {};
        new FormData(event.currentTarget).forEach(function (value, key) {
            data[key] = value;
        });

        try {
            var res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });
            var json = await res.json().catch(function () {
                return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
            });
            if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
            alert(json.message);
            window.location.href = (window.APP_BASE || '') + '/login.php';
        } catch (error) {
            alert(error.message);
        }
    });
})();
