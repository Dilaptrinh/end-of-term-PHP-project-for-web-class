(function () {
    'use strict';

    var endpoint = window.location.pathname;

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

    function setText(id, value) {
        var element = document.getElementById(id);
        if (element && value !== undefined) element.textContent = value;
    }

    // Dựng HTML cho một dòng đăng ký chờ thanh toán.
    function renderPendingRow(row) {
        return '<tr data-reg-pending-row="' + Number(row.id) + '">' +
            '<td>' + escapeHtml(row.member) + '</td>' +
            '<td>' + escapeHtml(row.package) + '</td>' +
            '<td class="text-muted-2">' + escapeHtml(row.from) + ' → ' + escapeHtml(row.to) + '</td>' +
            '<td class="cell-strong">' + escapeHtml(money(row.price)) + '</td>' +
            '<td><span class="badge-status warning">Chưa thanh toán</span></td>' +
            '<td class="cell-actions">' +
                '<button class="btn btn-primary btn-sm" type="button" ' +
                    'data-confirm-package="' + Number(row.id) + '" ' +
                    'data-confirm-text="Xác nhận đã thanh toán cho ' + escapeHtml(row.member) + '?">Xác nhận</button>' +
            '</td>' +
        '</tr>';
    }

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

            // Server trả danh sách chờ thanh toán mới, JS tự dựng lại bảng.
            var body = document.getElementById('dash-pending-body');
            if (body && Array.isArray(json.pending_rows)) {
                if (json.pending_rows.length === 0) {
                    body.innerHTML = '<tr data-empty-row><td colspan="6" class="text-center text-muted-2 py-4">Không có đăng ký chờ thanh toán.</td></tr>';
                } else {
                    body.innerHTML = json.pending_rows.map(renderPendingRow).join('');
                }
            }

            var stats = json.stats || {};
            setText('dash-pending-total', stats.pending_total);
            setText('dash-pending-foot', stats.pending_foot);
            setText('dash-pending-sub', stats.pending_sub);
            setText('dash-paid-revenue', stats.paid_revenue);
            setText('dash-pending-revenue', stats.pending_revenue);

            if (stats.paid_packages !== undefined) {
                setText('dash-paid-foot', stats.paid_packages + ' gói đã thanh toán');
            }

            // Cập nhật dòng "thanh toán chờ xác nhận" trong timeline.
            var summary = document.getElementById('dash-payment-summary');
            if (summary && stats.pending_payments !== undefined) {
                if (Number(stats.pending_payments) === 0) {
                    summary.remove();
                } else {
                    setText('dash-payment-title', stats.pending_payments + ' thanh toán chờ xác nhận');
                    setText('dash-payment-meta', 'Tổng giá trị ' + stats.pending_revenue);
                }
            }

            alert(json.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
