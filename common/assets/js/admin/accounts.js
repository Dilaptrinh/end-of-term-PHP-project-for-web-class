(function () {
    'use strict';

    var pageUrl = window.location.pathname;
    var accountsBody = document.getElementById('accounts-body');
    var currentId = accountsBody ? Number(accountsBody.dataset.currentId || 0) : 0;

    var roleLabels = {
        admin: 'Admin',
        instructor: 'Instructor',
        customer: 'Customer'
    };

    var escapeHtml = AppUI.escapeHtml;

    function renderRow(item) {
        var isActive = item.status === 'hoat_dong';
        var actionLabel = isActive ? 'Khóa' : 'Mở khóa';
        var canToggle = item.role !== 'admin' || Number(item.id) !== currentId;

        return '<tr data-account-row="' + Number(item.id) + '">' +
            '<td class="cell-strong">' + escapeHtml(item.name) + '</td>' +
            '<td>' + escapeHtml(item.username) + '</td>' +
            '<td>' + escapeHtml(roleLabels[item.role] || item.role) + '</td>' +
            '<td>' + (isActive
                ? '<span class="badge-status success">Đang hoạt động</span>'
                : '<span class="badge-status secondary">Bị khóa</span>') + '</td>' +
            '<td>' + escapeHtml(item.created) + '</td>' +
            '<td class="cell-actions">' +
                '<button class="btn btn-sm btn-outline-primary" type="button" data-account-detail="' + Number(item.id) + '">Chi tiết</button>' +
                (canToggle
                    ? '<form method="post" action="' + escapeHtml(pageUrl) + '?action=status" class="d-inline" onsubmit="return confirm(\'' + actionLabel + ' tài khoản này?\');">' +
                        '<input type="hidden" name="id" value="' + Number(item.id) + '">' +
                        '<input type="hidden" name="trang_thai" value="' + (isActive ? '0' : '1') + '">' +
                        '<button class="btn btn-sm btn-outline-secondary" type="submit">' + actionLabel + '</button>' +
                    '</form>'
                    : '') +
            '</td>' +
        '</tr>';
    }

    function renderEmptyRow() {
        return '<tr data-empty-row><td colspan="6" class="text-center text-muted-2 py-4">Không tìm thấy tài khoản.</td></tr>';
    }

    function renderDetail(data, specialties) {
        var role = data.vai_tro;
        var isActive = Number(data.trang_thai) === 1;
        var specialtyIds = String(data.specialty_ids || '').split(',').map(Number);
        var html = '<div class="account-detail-hero mb-3">' +
                '<div><h3 class="mb-1">' + escapeHtml(data.ho_ten) + '</h3>' +
                '<div class="text-muted-2">' + escapeHtml((roleLabels[role] || role) + ' · ' + data.ten_dang_nhap) + '</div></div>' +
            '</div>' +
            '<input type="hidden" name="id" value="' + Number(data.id) + '">' +
            '<div class="row g-3">' +
                '<div class="col-md-6"><label class="form-label">Họ tên</label><input class="form-control" name="ho_ten" value="' + escapeHtml(data.ho_ten) + '" required></div>' +
                '<div class="col-md-6"><label class="form-label">Tên đăng nhập</label><input class="form-control" value="' + escapeHtml(data.ten_dang_nhap) + '" disabled></div>' +
                '<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="' + escapeHtml(data.email) + '"></div>' +
                '<div class="col-md-6"><label class="form-label">Số điện thoại</label><input class="form-control" name="sdt" value="' + escapeHtml(data.sdt) + '"></div>' +
                '<div class="col-md-6"><label class="form-label">Trạng thái</label><select class="form-select" name="trang_thai">' +
                    '<option value="1"' + (isActive ? ' selected' : '') + '>Đang hoạt động</option>' +
                    '<option value="0"' + (isActive ? '' : ' selected') + '>Bị khóa</option>' +
                '</select></div>';

        if (role === 'customer') {
            html += '<div class="col-md-6"><label class="form-label">Ngày sinh</label><input class="form-control" name="ngay_sinh" type="date" value="' + escapeHtml(data.ngay_sinh) + '"></div>' +
                '<div class="col-md-6"><label class="form-label">Mục tiêu</label><input class="form-control" name="muc_tieu" value="' + escapeHtml(data.muc_tieu) + '"></div>' +
                '<div class="col-md-6"><label class="form-label">Chiều cao (cm)</label><input class="form-control" name="chieu_cao" type="number" step="0.1" value="' + escapeHtml(data.chieu_cao) + '"></div>' +
                '<div class="col-md-6"><label class="form-label">Cân nặng (kg)</label><input class="form-control" name="can_nang" type="number" step="0.1" value="' + escapeHtml(data.can_nang) + '"></div>' +
                '<div class="col-12"><label class="form-label">Địa chỉ</label><input class="form-control" name="dia_chi" value="' + escapeHtml(data.dia_chi) + '"></div>';
        } else if (role === 'instructor') {
            html += '<div class="col-12"><label class="form-label">Giới thiệu Instructor</label><textarea class="form-control" name="gioi_thieu" rows="3">' + escapeHtml(data.gioi_thieu) + '</textarea></div>' +
                '<div class="col-12"><label class="form-label">Chuyên môn</label><div class="row g-2">' +
                (specialties || []).map(function (specialty) {
                    var checked = specialtyIds.indexOf(Number(specialty.id)) !== -1 ? ' checked' : '';

                    return '<div class="col-md-4"><label class="form-check">' +
                        '<input class="form-check-input" type="checkbox" name="chuyen_mon_ids[]" value="' + Number(specialty.id) + '"' + checked + '>' +
                        '<span class="form-check-label">' + escapeHtml(specialty.name) + '</span></label></div>';
                }).join('') +
                '</div></div>';
        }

        return html + '</div>';
    }

    function callApi(url, options) {
        return fetch(url, Object.assign({
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        }, options || {})).then(function (response) {
            return response.json().then(function (result) {
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra.');
                }
                return result;
            });
        });
    }

    // Render bảng ban đầu từ JSON PHP in ra.
    var initial = document.getElementById('account-init');
    if (initial && accountsBody) {
        var rows = JSON.parse(initial.textContent || '[]');

        accountsBody.innerHTML = rows.map(renderRow).join('');
        if (!rows.length) {
            accountsBody.innerHTML = renderEmptyRow();
        }
    }

    // Ẩn/hiện trường hồ sơ theo vai trò khi tạo tài khoản.
    var roleSelect = document.getElementById('new-account-role');
    var profileFields = document.querySelectorAll('.account-profile-field');

    function updateCreateFields() {
        var isAdmin = roleSelect.value === 'admin';

        profileFields.forEach(function (field) {
            field.hidden = isAdmin;

            field.querySelectorAll('input').forEach(function (input) {
                input.required = !isAdmin;
            });
        });
    }

    if (roleSelect && profileFields.length) {
        roleSelect.addEventListener('change', updateCreateFields);
        updateCreateFields();
    }

    // Xem chi tiết tài khoản

    var detailModal = document.getElementById('accountDetailModal');
    var detailBody = document.getElementById('account-detail-body');
    var detailForm = document.getElementById('account-detail-form');

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-account-detail]');
        if (!button || !detailBody) return;

        try {
            var result = await callApi(
                pageUrl + '?action=detail&id=' + encodeURIComponent(button.dataset.accountDetail)
            );

            detailBody.innerHTML = renderDetail(result.data, result.specialties);
            bootstrap.Modal.getOrCreateInstance(detailModal).show();
        } catch (error) {
            alert(error.message);
        }
    });

    // Lưu thay đổi trong modal chi tiết

    if (detailForm) {
        detailForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            var idInput = detailForm.querySelector('input[name="id"]');
            var id = idInput ? idInput.value : '';
            var submitButton = detailForm.querySelector('[type="submit"]');
            if (submitButton) submitButton.disabled = true;

            try {
                var result = await callApi(pageUrl + '?action=update', {
                    method: 'POST',
                    body: new FormData(detailForm)
                });

                bootstrap.Modal.getOrCreateInstance(detailModal).hide();

                var oldRow = accountsBody && accountsBody.querySelector('[data-account-row="' + id + '"]');
                if (oldRow && result.data) {
                    oldRow.outerHTML = renderRow(result.data);
                }

                alert(result.message);
            } catch (error) {
                alert(error.message);
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }
})();
