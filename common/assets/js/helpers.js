(function () {
    'use strict';

    var statusLabels = {
        hoat_dong: ['Đang hoạt động', 'success'],
        ngung_hoat_dong: ['Ngừng hoạt động', 'muted'],
        dang_mo: ['Đang mở', 'success'],
        sap_mo: ['Sắp mở', 'warning'],
        da_dong: ['Đã đóng', 'muted'],
        dang_ban: ['Đang bán', 'success'],
        ngung_ban: ['Ngừng bán', 'muted'],
        da_huy: ['Đã hủy', 'danger'],
        da_thanh_toan: ['Đã thanh toán', 'success'],
        chua_thanh_toan: ['Chưa thanh toán', 'warning'],
        dang_ky: ['Đã đăng ký', 'primary'],
        bi_khoa: ['Đã khóa', 'danger']
    };

    function escapeHtml(value) {
        var characters = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
            return characters[character];
        });
    }

    function money(amount) {
        return Number(amount || 0).toLocaleString('vi-VN') + 'đ';
    }

    function truncateText(value, limit) {
        var text = String(value == null ? '' : value);
        return text.length > limit ? text.slice(0, limit) + '…' : text;
    }

    function statusBadge(status) {
        var item = statusLabels[status] || [status || '—', 'muted'];

        return '<span class="badge-status ' + item[1] + '">' + escapeHtml(item[0]) + '</span>';
    }

    window.AppUI = {
        escapeHtml: escapeHtml,
        money: money,
        truncateText: truncateText,
        statusBadge: statusBadge
    };
})();
