(function () {
    'use strict';

    var endpoint = window.location.pathname;

    async function send(action, data) {
        var formData = new FormData();
        Object.keys(data).forEach(function (key) {
            formData.append(key, data[key]);
        });

        var response = await fetch(endpoint + '?action=' + action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin'
        });
        var result = await response.json().catch(function () {
            return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
        });
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra.');
        }
        return result;
    }

    var profileForm = document.getElementById('profile-form');
    if (profileForm) profileForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        var name = document.getElementById('admin-name').value.trim();
        try {
            var result = await send('profile', { ho_ten: name });
            document.getElementById('account-name').textContent = name;
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });

    var passwordForm = document.getElementById('password-form');
    if (passwordForm) passwordForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        var form = event.currentTarget;
        try {
            var result = await send('password', {
                mat_khau_cu: document.getElementById('pw-current').value,
                mat_khau_moi: document.getElementById('pw-new').value,
                mat_khau_xac_nhan: document.getElementById('pw-confirm').value
            });
            form.reset();
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });

    var createForm = document.getElementById('account-create-form');
    var roleField = document.getElementById('new-account-role');
    var profileFields = document.querySelectorAll('.account-profile-field');

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
                if (!response.ok || !result.success) throw new Error(result.message || 'Không thể tạo tài khoản.');
                alert(result.message);
                window.location.reload();
            } catch (error) {
                alert(error.message);
            }
        });
    }

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
            if (!response.ok || !result.success) throw new Error(result.message || 'Không thể tải chi tiết.');

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
            document.querySelectorAll('#detail-specialty input[name="chuyen_mon_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = (data.specialty_ids || '').split(',').indexOf(checkbox.value) !== -1;
            });
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

    var detailForm = document.getElementById('account-detail-form');
    if (detailForm) {
        detailForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            var form = event.currentTarget;
            try {
                var response = await fetch(endpoint + '?action=update&id=' + form.dataset.id, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });
                var result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'Không thể cập nhật tài khoản.');
                alert(result.message);
                window.location.reload();
            } catch (error) {
                alert(error.message);
            }
        });
    }

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-account-status]');
        if (!button) return;
        if (!confirm(button.dataset.status === '1' ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?')) return;

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
            if (!response.ok || !result.success) throw new Error(result.message || 'Không thể cập nhật trạng thái.');
            alert(result.message);
            window.location.reload();
        } catch (error) {
            alert(error.message);
        }
    });
})();
