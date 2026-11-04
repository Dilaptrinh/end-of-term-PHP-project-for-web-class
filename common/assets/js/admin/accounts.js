(function () {
    'use strict';
    var endpoint = window.location.pathname;
    var form = document.getElementById('account-form');
    var modalElement = document.getElementById('accountFormModal');
    if (!form || !modalElement) return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    var title = document.getElementById('accountFormTitle');
    var password = document.getElementById('af-password');
    var username = document.getElementById('af-username');

    function resetForm() {
        form.reset();
        form.dataset.id = '';
        username.readOnly = false;
        password.required = true;
        title.textContent = 'Thông tin tài khoản';
    }
    document.querySelector('[data-bs-target="#accountFormModal"]').addEventListener('click', resetForm);

    document.addEventListener('click', async function (event) {
        var edit = event.target.closest('[data-account-edit]');
        if (edit) {
            try {
                var res = await fetch(endpoint + '?action=detail&id=' + edit.dataset.accountEdit, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                var json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.message || 'Không thể tải tài khoản.');
                var account = json.data;
                form.dataset.id = account.id;
                document.getElementById('af-name').value = account.ho_ten || '';
                username.value = account.ten_dang_nhap || '';
                username.readOnly = true;
                document.getElementById('af-role').value = account.vai_tro;
                document.getElementById('af-active').checked = Number(account.trang_thai) === 1;
                password.value = '';
                password.required = false;
                title.textContent = 'Sửa tài khoản';
                modal.show();
            } catch (error) { alert(error.message); }
            return;
        }
        var toggle = event.target.closest('[data-account-toggle]');
        var remove = event.target.closest('[data-account-delete]');
        var reset = event.target.closest('[data-account-reset]');
        if (reset) {
            var newPassword = prompt('Nhập mật khẩu mới (ít nhất 6 ký tự):');
            if (newPassword === null) return;
            try {
                var resetBody = new URLSearchParams({ mat_khau: newPassword });
                var resetResponse = await fetch(endpoint + '?action=reset&id=' + reset.dataset.accountReset, { method: 'POST', body: resetBody, headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                var resetJson = await resetResponse.json();
                if (!resetResponse.ok || !resetJson.success) throw new Error(resetJson.message || 'Đặt lại mật khẩu thất bại.');
                alert(resetJson.message);
            } catch (error) { alert(error.message); }
            return;
        }
        if (!toggle && !remove) return;
        var id = toggle ? toggle.dataset.accountToggle : remove.dataset.accountDelete;
        var message = toggle ? (toggle.dataset.active === '1' ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?') : 'Xóa tài khoản ' + remove.dataset.accountName + '?';
        if (!confirm(message)) return;
        try {
            var action = toggle ? 'toggle&active=' + toggle.dataset.active : 'delete';
            var res = await fetch(endpoint + '?action=' + action + '&id=' + id, { method: 'POST', headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            var json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Thao tác thất bại.');
            alert(json.message);
            location.reload();
        } catch (error) { alert(error.message); }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        var id = form.dataset.id;
        var data = {
            ho_ten: document.getElementById('af-name').value.trim(),
            ten_dang_nhap: username.value.trim(),
            vai_tro: document.getElementById('af-role').value,
            mat_khau: password.value,
            trang_thai: document.getElementById('af-active').checked ? 1 : 0
        };
        try {
            var res = await fetch(endpoint + '?action=' + (id ? 'update&id=' + id : 'create'), {
                method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(data), credentials: 'same-origin'
            });
            var json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Lưu tài khoản thất bại.');
            alert(json.message);
            modal.hide();
            location.reload();
        } catch (error) { alert(error.message); }
    });
})();
