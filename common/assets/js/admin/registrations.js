(function () {
    'use strict';

    // Địa chỉ trang hiện tại, ví dụ: /fitness/admin/registrations.php
    var endpoint = window.location.pathname;
    var tableBody = document.getElementById('package-regs-body');

    // Chuyển ký tự đặc biệt sang HTML để tránh lỗi hiển thị.
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

    // Định dạng tiền giống PHP: 500000 -> 500.000đ
    function money(amount) {
        return Number(amount || 0).toLocaleString('vi-VN') + 'đ';
    }

    // Tạo badge trạng thái thanh toán.
    function statusBadge(status) {
        var labels = {
            chua_thanh_toan: ['Chưa thanh toán', 'warning'],
            da_thanh_toan: ['Đã thanh toán', 'success'],
            da_huy: ['Đã hủy', 'danger']
        };
        var item = labels[status] || [status, 'muted'];

        return '<span class="badge-status ' + item[1] + '">' + escapeHtml(item[0]) + '</span>';
    }

    // Dựng HTML cho một dòng đăng ký gói.
    function renderRegistrationRow(reg) {
        var actionCell;

        if (reg.payment === 'chua_thanh_toan') {
            actionCell =
                '<button class="btn btn-primary btn-sm" type="button" ' +
                    'data-reg-action="confirm-package" ' +
                    'data-reg-id="' + Number(reg.id) + '" ' +
                    'data-reg-confirm="Xác nhận thanh toán ' + escapeHtml(money(reg.price)) + ' cho ' + escapeHtml(reg.member) + '?">' +
                    '<i class="bi bi-check-lg"></i> Xác nhận</button>' +
                '<button class="btn btn-danger-soft btn-sm" type="button" ' +
                    'data-reg-action="cancel-package" ' +
                    'data-reg-id="' + Number(reg.id) + '" ' +
                    'data-reg-confirm="Hủy đăng ký của ' + escapeHtml(reg.member) + '?">Hủy</button>';
        } else {
            actionCell =
                '<button class="btn btn-ghost btn-sm" type="button" ' +
                    'data-detail="' + escapeHtml(reg.member + ' · ' + reg.package + ' · ' + money(reg.price)) + '">' +
                    '<i class="bi bi-eye"></i> Xem</button>';
        }

        return '<tr data-row data-reg-row="' + Number(reg.id) + '">' +
            '<td class="cell-strong">DK-' + Number(reg.id) + '</td>' +
            '<td>' + escapeHtml(reg.member) + '</td>' +
            '<td>' + escapeHtml(reg.package) + '</td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(reg.from) + '<br>' + escapeHtml(reg.to) + '</td>' +
            '<td class="cell-strong">' + escapeHtml(money(reg.price)) + '</td>' +
            '<td>' + statusBadge(reg.payment) +
                '<div class="cell-sub">' + escapeHtml(reg.method) + '</div></td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(reg.staff) +
                (reg.paid_at !== '—' ? '<br>' + escapeHtml(reg.paid_at) : '') + '</td>' +
            '<td class="cell-actions">' + actionCell + '</td>' +
        '</tr>';
    }

    // Thêm một dòng mới vào cuối bảng.
    function appendRow(html) {
        if (!tableBody || !html) return;

        var emptyRow = tableBody.querySelector('[data-empty-row]');
        if (emptyRow) emptyRow.remove();

        tableBody.insertAdjacentHTML('beforeend', html);
    }

    // Thay dòng cũ bằng dòng mới.
    function replaceRow(oldRow, html) {
        if (!oldRow || !html) return;
        oldRow.outerHTML = html;
    }

    // Bắt sự kiện click vào nút "Xác nhận" hoặc "Hủy".
    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-reg-action]');
        if (!button) return;

        if (!confirm(button.dataset.regConfirm || 'Xác nhận thao tác?')) {
            return;
        }

        try {
            var url = endpoint + '?action=' + button.dataset.regAction + '&id=' + button.dataset.regId;
            var response = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            var result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Có lỗi xảy ra.');
            }

            // Server trả dữ liệu đăng ký mới, JS tự dựng lại dòng.
            replaceRow(button.closest('tr'), renderRegistrationRow(result.data));
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });

    // Xử lý form "Tạo đăng ký gói".
    var form = document.getElementById('registration-form');
    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            try {
                var response = await fetch(endpoint + '?action=create-package', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });

                var result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra.');
                }

                // Thêm dòng mới vào bảng, không reload trang.
                appendRow(renderRegistrationRow(result.data));
                form.reset();

                var modal = document.getElementById('registrationFormModal');
                if (modal) bootstrap.Modal.getOrCreateInstance(modal).hide();

                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }
})();
