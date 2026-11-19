(function () {
    'use strict';

    var endpoint = window.location.pathname;

    var escapeHtml = AppUI.escapeHtml;
    var money = AppUI.money;
    var statusBadge = AppUI.statusBadge;

    function actionsCell(registration) {
        if (registration.payment !== 'chua_thanh_toan') {
            return '<span class="text-muted-2 text-tiny">—</span>';
        }

        var id = Number(registration.id);

        return '<button class="btn btn-primary btn-sm" type="button" data-reg-confirm="' + id + '"><i class="bi bi-check-lg"></i> Xác nhận</button>' +
            '<button class="btn btn-danger-soft btn-sm" type="button" data-reg-cancel="' + id + '">Hủy</button>';
    }

    function renderRow(registration) {
        var paidAt = registration.paid_at && registration.paid_at !== '—'
            ? '<br>' + escapeHtml(registration.paid_at)
            : '';

        return '<tr data-row data-reg-row="' + Number(registration.id) + '">' +
            '<td class="cell-strong">DK-' + Number(registration.id) + '</td>' +
            '<td>' + escapeHtml(registration.member) + '</td>' +
            '<td>' + escapeHtml(registration.package) + (registration.loai_giao_dich === 'nang_cap'
                ? '<div class="cell-sub">Nâng cấp · khấu trừ ' + escapeHtml(money(registration.so_tien_giam || 0)) + '</div>'
                : '') + '</td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(registration.from) + '<br>' + escapeHtml(registration.to) + '</td>' +
            '<td class="cell-strong">' + escapeHtml(money(registration.price)) + '</td>' +
            '<td>' + statusBadge(registration.payment) + '<div class="cell-sub">' + escapeHtml(registration.method) + '</div></td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(registration.staff) + paidAt + '</td>' +
            '<td class="cell-actions">' + actionsCell(registration) + '</td>' +
        '</tr>';
    }

    function replaceRow(id, registration) {
        var row = document.querySelector('#package-regs-table [data-reg-row="' + Number(id) + '"]');
        if (!row) return;

        row.outerHTML = renderRow(registration);
    }

    async function updateRegistration(button, action, message) {
        var id = Number(button.dataset.regConfirm || button.dataset.regCancel || 0);
        if (id <= 0) return;

        if (!confirm(message)) return;

        button.disabled = true;

        try {
            var formData = new FormData();
            formData.append('id', id);

            var res = await fetch(endpoint + '?action=' + action + '&id=' + id, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });

            var result = await res.json();

            if (!res.ok || !result.success) {
                throw new Error(result.message || 'Có lỗi xảy ra.');
            }

            if (result.data) {
                replaceRow(id, result.data);
            }

            alert(result.message);
        } catch (error) {
            alert(error.message);
        } finally {
            button.disabled = false;
        }
    }

    document.addEventListener('click', function (event) {
        var confirmButton = event.target.closest('[data-reg-confirm]');
        if (confirmButton) {
            updateRegistration(confirmButton, 'confirm-package', 'Xác nhận thanh toán cho đăng ký này?');
            return;
        }

        var cancelButton = event.target.closest('[data-reg-cancel]');
        if (cancelButton) {
            updateRegistration(cancelButton, 'cancel-package', 'Hủy đăng ký này?');
        }
    });
})();
