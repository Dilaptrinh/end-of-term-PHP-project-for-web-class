(function () {
    'use strict';

    // Dùng cho trang admin/accounts.php ("Quản lý tài khoản").
    var endpoint = window.location.pathname;
    var accountsBody = document.getElementById('accounts-body');

    // ---------------------------------------------
    // Hàm dựng HTML cho bảng tài khoản
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

    // Dựng HTML cho một dòng tài khoản.
    function renderAccountRow(item) {
        var roleLabels = { admin: 'Admin', instructor: 'Instructor', customer: 'Customer' };
        var currentId = accountsBody ? Number(accountsBody.dataset.currentId || 0) : 0;
        var isActive = item.status === 'hoat_dong';

        var statusHtml = isActive
            ? '<span class="badge-status success">Đang hoạt động</span>'
            : '<span class="badge-status secondary">Bị khóa</span>';

        var statusButton = '';
        if (item.role !== 'admin' || Number(item.id) !== currentId) {
            statusButton =
                '<button class="btn btn-sm btn-outline-secondary" type="button" ' +
                    'data-account-status="' + Number(item.id) + '" ' +
                    'data-status="' + (isActive ? '0' : '1') + '">' +
                    (isActive ? 'Khóa' : 'Mở khóa') +
                '</button>';
        }

        return '<tr data-account-row="' + Number(item.id) + '">' +
            '<td class="cell-strong">' + escapeHtml(item.name) + '</td>' +
            '<td>' + escapeHtml(item.username) + '</td>' +
            '<td>' + escapeHtml(roleLabels[item.role] || item.role) + '</td>' +
            '<td>' + statusHtml + '</td>' +
            '<td>' + escapeHtml(item.created) + '</td>' +
            '<td class="cell-actions">' +
                '<button class="btn btn-sm btn-outline-primary" type="button" ' +
                    'data-account-detail="' + Number(item.id) + '">Chi tiết</button>' +
                statusButton +
            '</td>' +
        '</tr>';
    }

    // Thêm một dòng tài khoản mới vào cuối bảng.
    function appendAccountRow(html) {
        if (!accountsBody || !html) return;

        // Bỏ dòng "Không tìm thấy tài khoản" nếu đang có.
        var emptyRow = accountsBody.querySelector('[data-empty-row]');
        if (emptyRow) emptyRow.remove();

        accountsBody.insertAdjacentHTML('beforeend', html);
    }

    // Thay dòng tài khoản cũ bằng dòng mới.
    function replaceAccountRow(oldRow, html) {
        if (!oldRow || !html) return;
        oldRow.outerHTML = html;
    }

    // ---------------------------------------------
    // Tạo tài khoản
    // ---------------------------------------------

    var createForm = document.getElementById('account-create-form');
    var roleField = document.getElementById('new-account-role');
    var profileFields = document.querySelectorAll('.account-profile-field');

    // Ẩn/hiện ô hồ sơ khi chọn vai trò Admin / Instructor / Customer.
    function updateProfileFields() {
        var needsProfile = roleField.value !== 'admin';

        profileFields.forEach(function (field) {
            field.hidden = !needsProfile;

            field.querySelectorAll('input').forEach(function (input) {
                input.required = needsProfile;
            });
        });
    }

    if (createForm && roleField) {
        roleField.addEventListener('change', updateProfileFields);
        updateProfileFields();

        createForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            try {
                var response = await fetch(endpoint + '?action=create', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(createForm),
                    credentials: 'same-origin'
                });

                var result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Không thể tạo tài khoản.');
                }

                // Server trả dữ liệu tài khoản mới, JS tự dựng dòng.
                appendAccountRow(renderAccountRow(result.data));
                createForm.reset();

                var createModal = document.getElementById('accountCreateModal');
                if (createModal) bootstrap.Modal.getOrCreateInstance(createModal).hide();

                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // ---------------------------------------------
    // Modal xem chi tiết tài khoản
    // ---------------------------------------------

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-account-detail]');
        if (!button) return;

        var modalElement = document.getElementById('accountDetailModal');
        bootstrap.Modal.getOrCreateInstance(modalElement).show();

        try {
            var response = await fetch(endpoint + '?action=detail&id=' + button.dataset.accountDetail, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            var result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể tải chi tiết.');
            }

            var data = result.data;
            var roleNames = { admin: 'Admin', instructor: 'Instructor', customer: 'Customer' };
            var form = document.getElementById('account-detail-form');

            form.dataset.id = data.id;
            document.getElementById('account-detail-title').textContent = data.ho_ten;
            document.getElementById('account-detail-subtitle').textContent = roleNames[data.vai_tro] + ' · ' + data.ten_dang_nhap;
            document.getElementById('detail-role').value = data.vai_tro;
            document.getElementById('detail-name').value = data.ho_ten || '';
            document.getElementById('detail-username').value = data.ten_dang_nhap || '';
            document.getElementById('detail-email').value = data.email || '';
            document.getElementById('detail-phone').value = data.sdt || '';
            document.getElementById('detail-status').value = Number(data.trang_thai) === 1 ? '1' : '0';
            document.getElementById('detail-birth').value = data.ngay_sinh || '';
            document.getElementById('detail-goal').value = data.muc_tieu || '';
            document.getElementById('detail-height').value = data.chieu_cao || '';
            document.getElementById('detail-weight').value = data.can_nang || '';
            document.getElementById('detail-address').value = data.dia_chi || '';
            document.getElementById('detail-intro').value = data.gioi_thieu || '';

            // Chọn các chuyên môn của Instructor.
            document.querySelectorAll('#detail-specialty input[name="chuyen_mon_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = (data.specialty_ids || '').split(',').indexOf(checkbox.value) !== -1;
            });

            // Chỉ hiện ô dành riêng cho Customer / Instructor.
            document.querySelectorAll('[data-detail-customer]').forEach(function (field) {
                field.hidden = data.vai_tro !== 'customer';
            });

            document.querySelectorAll('[data-detail-instructor]').forEach(function (field) {
                field.hidden = data.vai_tro !== 'instructor';
            });
        } catch (error) {
            alert(error.message);
        }
    });

    // Lưu thay đổi trong modal chi tiết.
    var detailForm = document.getElementById('account-detail-form');
    if (detailForm) {
        detailForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            try {
                var response = await fetch(endpoint + '?action=update&id=' + detailForm.dataset.id, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(detailForm),
                    credentials: 'same-origin'
                });

                var result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Không thể cập nhật tài khoản.');
                }

                var oldRow = null;
                if (accountsBody) {
                    oldRow = accountsBody.querySelector('[data-account-row="' + detailForm.dataset.id + '"]');
                }

                replaceAccountRow(oldRow, renderAccountRow(result.data));

                var detailModal = document.getElementById('accountDetailModal');
                if (detailModal) bootstrap.Modal.getOrCreateInstance(detailModal).hide();

                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // ---------------------------------------------
    // Khóa / mở khóa tài khoản
    // ---------------------------------------------

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-account-status]');
        if (!button) return;

        var question = button.dataset.status === '1' ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?';
        if (!confirm(question)) return;

        var formData = new FormData();
        formData.append('trang_thai', button.dataset.status);

        try {
            var response = await fetch(endpoint + '?action=status&id=' + button.dataset.accountStatus, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });

            var result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể cập nhật trạng thái.');
            }

            replaceAccountRow(button.closest('tr'), renderAccountRow(result.data));
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
