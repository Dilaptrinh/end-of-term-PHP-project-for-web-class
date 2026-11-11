(function () {
    'use strict';

    var endpoint = window.location.pathname;

    document.getElementById('availability-create-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var form = event.currentTarget;
        var formData = new FormData();
        formData.append('ngay_day', document.getElementById('av-date').value);
        formData.append('gio_bat_dau', document.getElementById('av-start').value);
        formData.append('gio_ket_thuc', document.getElementById('av-end').value);
        formData.append('ghi_chu', document.getElementById('av-specialty').value);

        try {
            var res = await fetch(endpoint + '?action=create', {
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
            alert(json.message);
            form.reset();
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-cancel-id]');
        if (!button) return;

        if (!confirm('Hủy yêu cầu lịch dạy ngày ' + button.dataset.cancelDate + '?')) {
            return;
        }

        try {
            var res = await fetch(endpoint + '?action=cancel&id=' + button.dataset.cancelId, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var json = await res.json().catch(function () {
                return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
            });
            if (!res.ok || !json.success) {
                throw new Error(json.message || 'Có lỗi xảy ra.');
            }
            alert(json.message);
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });
})();
