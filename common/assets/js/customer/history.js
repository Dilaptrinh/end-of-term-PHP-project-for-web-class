(function () {
    'use strict';

    var endpoint = window.location.pathname;

    function markCancelled(row) {
        var statusCell = row.querySelector('[data-status-cell]');
        if (statusCell) {
            statusCell.innerHTML = AppUI.statusBadge('da_huy');
        }

        var actionsCell = row.querySelector('td.cell-actions');
        if (actionsCell) {
            actionsCell.innerHTML = '<button class="btn btn-ghost btn-sm" type="button" disabled>—</button>';
        }
    }

    async function sendCancel(action, id, button) {
        var formData = new FormData();
        formData.append('id', id);

        var res = await fetch(endpoint + '?action=' + action + '&id=' + id, {
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

        var row = button.closest('tr');
        if (row) {
            markCancelled(row);
        }
        alert(json.message);
    }

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-cancel-id]');
        if (!button) return;

        if (!confirm('Hủy đăng ký lớp ' + button.dataset.cancelClass + '?')) {
            return;
        }

        try {
            await sendCancel('cancel', button.dataset.cancelId, button);
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
            await sendCancel('cancel-package', button.dataset.cancelPackageId, button);
        } catch (error) {
            alert(error.message);
        }
    });
})();
