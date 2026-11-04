(function () {
    'use strict';

    var endpoint = window.location.pathname;

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-cancel-id]');
        if (!button) return;

        if (!confirm('Hủy đăng ký lớp ' + button.dataset.cancelClass + '?')) {
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
