(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('packageFormModal');
    var form = document.getElementById('package-form');
    var tbody = document.getElementById('packages-body');

    var escapeHtml = AppUI.escapeHtml;
    var money = AppUI.money;
    var truncateText = AppUI.truncateText;
    var statusBadge = AppUI.statusBadge;

    function renderRow(item) {
        return '<tr data-row data-package-id="' + Number(item.id) + '" data-package-status="' + escapeHtml(item.status) + '">' +
            '<td class="cell-strong">' + escapeHtml(item.name) +
                '<div class="cell-sub">' + escapeHtml(truncateText(item.desc, 46)) + '</div>' +
            '</td>' +
            '<td>' + Number(item.days) + ' ngày</td>' +
            '<td class="cell-strong">' + escapeHtml(money(item.price)) + '</td>' +
            '<td>' + Number(item.sold) + '</td>' +
            '<td class="cell-strong">' + escapeHtml(money(Number(item.price) * Number(item.sold))) + '</td>' +
            '<td>' + statusBadge(item.status) + '</td>' +
            '<td class="cell-actions">' +
                '<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal"' +
                    ' data-package-id="' + Number(item.id) + '"' +
                    ' data-package-name="' + escapeHtml(item.name) + '"' +
                    ' data-package-days="' + Number(item.days) + '"' +
                    ' data-package-price="' + Number(item.price) + '"' +
                    ' data-package-desc="' + escapeHtml(item.desc || '') + '"' +
                    ' data-package-level="' + escapeHtml(item.level || 'basic') + '"' +
                    ' data-package-status="' + escapeHtml(item.status) + '"><i class="bi bi-pencil"></i></button>' +
                '<button class="btn btn-ghost btn-sm text-danger" type="button"' +
                    ' data-delete-id="' + Number(item.id) + '"' +
                    ' data-delete-name="' + escapeHtml(item.name) + '"><i class="bi bi-trash3"></i></button>' +
            '</td>' +
        '</tr>';
    }

    function renderEmptyRow() {
        return '<tr data-empty-row><td colspan="7" class="text-center text-muted-2 py-4">Chưa có gói tập nào.</td></tr>';
    }

    function emptyRow() {
        if (!tbody) return;
        tbody.innerHTML = renderEmptyRow();
    }

    function appendRow(html) {
        if (!tbody || !html) return;

        var empty = tbody.querySelector('[data-empty-row]');
        if (empty) empty.remove();

        tbody.insertAdjacentHTML('beforeend', html);
    }

    function replaceRow(oldRow, html) {
        if (!oldRow || !html) return;
        oldRow.outerHTML = html;
    }

    function removeRow(row) {
        if (!row) return;

        row.remove();
        if (!tbody.querySelector('[data-row]')) emptyRow();
    }

    // Render bảng ban đầu từ JSON PHP in ra.
    var initial = document.getElementById('package-init');
    if (initial && tbody) {
        var rows = JSON.parse(initial.textContent || '[]');

        tbody.innerHTML = rows.map(renderRow).join('');
        if (!rows.length) {
            emptyRow();
        }
    }

    if (!modal || !form) return;

    var title = document.getElementById('packageFormTitle');

    // Modal thêm / sửa gói tập

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;

        if (trigger && trigger.dataset.packageId) {
            form.dataset.id = trigger.dataset.packageId;
            title.textContent = 'Chỉnh sửa gói tập';

            form.querySelector('#pf-name').value = trigger.dataset.packageName || '';
            form.querySelector('#pf-days').value = trigger.dataset.packageDays || '30';
            form.querySelector('#pf-price').value = trigger.dataset.packagePrice || '0';
            form.querySelector('#pf-desc').value = trigger.dataset.packageDesc || '';
            form.querySelector('#pf-level').value = trigger.dataset.packageLevel || 'basic';
            form.querySelector('#pf-status').value = trigger.dataset.packageStatus || 'dang_ban';
            return;
        }

        form.reset();
        form.dataset.id = '';
        title.textContent = 'Thông tin gói tập';
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var submitButton = form.querySelector('[type="submit"]');
        var editingId = form.dataset.id || '';
        var oldRow = editingId
            ? document.querySelector('#packages-table [data-package-id="' + editingId + '"]')
            : null;

        if (submitButton) submitButton.disabled = true;

        try {
            var res = await fetch(endpoint + '?action=save&id=' + (editingId || '0'), {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form),
                credentials: 'same-origin'
            });

            var result = await res.json();

            if (!res.ok || !result.success) {
                throw new Error(result.message || 'Có lỗi xảy ra.');
            }

            if (!result.data) {
                throw new Error('Không tìm thấy gói tập.');
            }

            if (editingId) {
                replaceRow(oldRow, renderRow(result.data));
            } else {
                appendRow(renderRow(result.data));
            }

            bootstrap.Modal.getOrCreateInstance(modal).hide();
            alert(result.message);
        } catch (error) {
            alert(error.message);
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });

    // Xóa gói tập

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-delete-id]');
        if (!button) return;

        if (!confirm('Xóa gói ' + button.dataset.deleteName + '? Dữ liệu sẽ không thể khôi phục.')) {
            return;
        }

        try {
            var formData = new FormData();
            formData.append('id', button.dataset.deleteId);

            var res = await fetch(endpoint + '?action=delete&id=' + button.dataset.deleteId, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });

            var result = await res.json();

            if (!res.ok || !result.success) {
                throw new Error(result.message || 'Có lỗi xảy ra.');
            }

            removeRow(button.closest('tr'));
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
