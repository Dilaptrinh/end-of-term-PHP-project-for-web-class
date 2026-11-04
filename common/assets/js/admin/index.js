(function () {
    'use strict';

    var endpoint = window.location.pathname;

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-confirm-package]');
        if (!button) return;

        if (!confirm(button.dataset.confirmText || 'Xác nhận đã thanh toán?')) {
            return;
        }

        try {
            var res = await fetch(endpoint + '?action=confirm-package&id=' + button.dataset.confirmPackage, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
            alert(json.message);
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });
})();
