(function () {
    'use strict';

    var endpoint = window.location.pathname;

    async function send(action, data) {
        var formData = new FormData();
        Object.keys(data).forEach(function (key) {
            formData.append(key, data[key]);
        });

        var res = await fetch(endpoint + '?action=' + action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin'
        });
        var json = await res.json().catch(function () {
            return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
        });
        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Có lỗi xảy ra.');
        }
        return json;
    }

    document.getElementById('profile-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var data = {
            ho_ten: document.getElementById('acc-name').value.trim(),
            sdt: document.getElementById('acc-phone').value.trim(),
            email: document.getElementById('acc-email').value.trim(),
            ngay_sinh: document.getElementById('acc-birth').value,
            gioi_tinh: document.getElementById('acc-gender').value,
            can_nang: document.getElementById('acc-weight').value,
            chieu_cao: document.getElementById('acc-height').value,
            muc_tieu: document.getElementById('acc-goal').value,
            dia_chi: document.getElementById('acc-address').value.trim(),
            ghi_chu: document.getElementById('acc-note').value.trim()
        };

        try {
            var json = await send('profile', data);
            document.getElementById('account-name').textContent = data.ho_ten;
            alert(json.message);
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('password-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var form = event.currentTarget;
        var data = {
            mat_khau_cu: document.getElementById('pw-current').value,
            mat_khau_moi: document.getElementById('pw-new').value,
            mat_khau_xac_nhan: document.getElementById('pw-confirm').value
        };

        try {
            var json = await send('password', data);
            form.reset();
            alert(json.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
