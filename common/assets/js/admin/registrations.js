(function () {
    'use strict';

    var endpoint = window.location.pathname;

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-reg-action]');
        if (!button) return;

        if (!confirm(button.dataset.regConfirm || 'Xác nhận thao tác?')) {
            return;
        }

        try {
            var res = await fetch(endpoint + '?action=' + button.dataset.regAction + '&id=' + button.dataset.regId, {
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

    var form = document.getElementById('registration-form');
    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            var data = {};
            new FormData(form).forEach(function (value, key) {
                data[key] = value;
            });

            try {
                var res = await fetch(endpoint + '?action=create-package', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data),
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
    }
})();
