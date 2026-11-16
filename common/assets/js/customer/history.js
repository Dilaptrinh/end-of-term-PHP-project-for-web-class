(function () {
    'use strict';

    var endpoint = window.location.pathname;

    async function sendCancel(action, id) {
        var res = await fetch(endpoint + '?action=' + action + '&id=' + id, {
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
    }

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-cancel-id]');
        if (!button) return;

        if (!confirm('Hủy đăng ký lớp ' + button.dataset.cancelClass + '?')) {
            return;
        }

        try {
            await sendCancel('cancel', button.dataset.cancelId);
        } catch (error) {
            alert(error.message);
        }
    });

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-cancel-package-id]');
        if (!button) return;

        if (!confirm('Hủy đăng ký gói ' + button.dataset.cancelPackageName + '?')) {
            return;
        }

        try {
            await sendCancel('cancel-package', button.dataset.cancelPackageId);
        } catch (error) {
            alert(error.message);
        }
    });
})();
