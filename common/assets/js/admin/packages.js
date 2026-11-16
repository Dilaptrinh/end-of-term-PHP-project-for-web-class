(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('packageFormModal');
    var form = document.getElementById('package-form');
    if (!modal || !form) return;

    var title = document.getElementById('packageFormTitle');
    var query = document.getElementById('pk-q');
    var status = document.getElementById('pk-status');
    var reset = document.getElementById('pk-reset');
    var tbody = document.getElementById('packages-body');

    // ---------------------------------------------
    // Hàm dùng chung để dựng HTML
    // ---------------------------------------------

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function money(amount) {
        return Number(amount || 0).toLocaleString('vi-VN') + 'đ';
    }

    function truncateText(value, limit) {
        var text = String(value == null ? '' : value);
        return text.length > limit ? text.slice(0, limit) + '…' : text;
    }

    function statusBadge(statusValue) {
        var labels = {
            dang_ban: ['Đang bán', 'success'],
            ngung_ban: ['Ngừng bán', 'muted']
        };
        var item = labels[statusValue] || [statusValue, 'muted'];

        return '<span class="badge-status ' + item[1] + '">' + escapeHtml(item[0]) + '</span>';
    }

    // Dựng HTML cho một dòng gói tập.
    function renderPackageRow(pkg) {
        return '<tr data-row data-package-id="' + Number(pkg.id) + '" data-package-status="' + escapeHtml(pkg.status) + '">' +
            '<td class="cell-strong">' + escapeHtml(pkg.name) +
                '<div class="cell-sub">' + escapeHtml(truncateText(pkg.desc, 46)) + '</div></td>' +
            '<td>' + Number(pkg.days) + ' ngày</td>' +
            '<td class="cell-strong">' + escapeHtml(money(pkg.price)) + '</td>' +
            '<td>' + Number(pkg.sold) + '</td>' +
            '<td class="cell-strong">' + escapeHtml(money(Number(pkg.price) * Number(pkg.sold))) + '</td>' +
            '<td>' + statusBadge(pkg.status) + '</td>' +
            '<td class="cell-actions">' +
                '<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#packageFormModal" ' +
                    'data-package-id="' + Number(pkg.id) + '" ' +
                    'data-package-name="' + escapeHtml(pkg.name) + '" ' +
                    'data-package-days="' + Number(pkg.days) + '" ' +
                    'data-package-price="' + Number(pkg.price) + '" ' +
                    'data-package-desc="' + escapeHtml(pkg.desc || '') + '" ' +
                    'data-package-status="' + escapeHtml(pkg.status) + '">' +
                    '<i class="bi bi-pencil"></i></button>' +
                '<button class="btn btn-ghost btn-sm text-danger" type="button" ' +
                    'data-delete-id="' + Number(pkg.id) + '" ' +
                    'data-delete-name="' + escapeHtml(pkg.name) + '">' +
                    '<i class="bi bi-trash3"></i></button>' +
            '</td>' +
        '</tr>';
    }

    // ---------------------------------------------
    // Bảng gói tập
    // ---------------------------------------------

    function emptyRow() {
        if (!tbody) return;
        tbody.innerHTML = '<tr data-empty-row><td colspan="7" class="text-center text-muted-2 py-4">Chưa có gói tập nào.</td></tr>';
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

    function filterCards() {
        var text = (query.value || '').trim().toLowerCase();
        var selected = status.value;

        document.querySelectorAll('#packages-table [data-row]').forEach(function (card) {
            var matchesText = !text || card.dataset.packageId === text;
            var matchesStatus = selected === 'Tất cả'
                || (selected === 'Đang bán' && card.dataset.packageStatus === 'dang_ban')
                || (selected === 'Ngừng bán' && card.dataset.packageStatus === 'ngung_ban');

            card.hidden = !matchesText || !matchesStatus;
        });
    }

    if (query && status) {
        query.addEventListener('input', filterCards);
        status.addEventListener('change', filterCards);
        reset.addEventListener('click', function () {
            query.value = '';
            status.value = 'Tất cả';
            filterCards();
        });
    }

    // ---------------------------------------------
    // Modal thêm / sửa gói tập
    // ---------------------------------------------

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;

        if (trigger && trigger.dataset.packageId) {
            form.dataset.id = trigger.dataset.packageId;
            title.textContent = 'Chỉnh sửa gói tập';

            form.querySelector('#pf-name').value = trigger.dataset.packageName || '';
            form.querySelector('#pf-days').value = trigger.dataset.packageDays || '30';
            form.querySelector('#pf-price').value = trigger.dataset.packagePrice || '0';
            form.querySelector('#pf-desc').value = trigger.dataset.packageDesc || '';
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

            var html = renderPackageRow(result.data);

            if (editingId) {
                replaceRow(oldRow, html);
            } else {
                appendRow(html);
            }

            if (query && status) filterCards();

            bootstrap.Modal.getOrCreateInstance(modal).hide();
            alert(result.message);
        } catch (error) {
            alert(error.message);
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });

    // ---------------------------------------------
    // Xóa gói tập
    // ---------------------------------------------

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-delete-id]');
        if (!button) return;

        if (!confirm('Xóa gói ' + button.dataset.deleteName + '? Dữ liệu sẽ không thể khôi phục.')) {
            return;
        }

        try {
            var res = await fetch(endpoint + '?action=delete&id=' + button.dataset.deleteId, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
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
