(function () {
    'use strict';

    var endpoint = window.location.pathname;

    async function send(action, data) {
        var formData = new FormData();
        Object.keys(data).forEach(function (key) {
            formData.append(key, data[key]);
        });

        var response = await fetch(endpoint + '?action=' + action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin'
        });
        var result = await response.json().catch(function () {
            return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
        });
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra.');
        }
        return result;
    }

    document.getElementById('profile-form').addEventListener('submit', async function (event) {
        event.preventDefault();
        var name = document.getElementById('admin-name').value.trim();
        try {
            var result = await send('profile', { ho_ten: name });
            document.getElementById('account-name').textContent = name;
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('password-form').addEventListener('submit', async function (event) {
        event.preventDefault();
        var form = event.currentTarget;
        try {
            var result = await send('password', {
                mat_khau_cu: document.getElementById('pw-current').value,
                mat_khau_moi: document.getElementById('pw-new').value,
                mat_khau_xac_nhan: document.getElementById('pw-confirm').value
            });
            form.reset();
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
