(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('memberFormModal');
    var form = document.getElementById('member-form');
    if (!modal || !form) return;

    var title = document.getElementById('memberFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;

        if (trigger && trigger.dataset.memberId) {
            form.dataset.id = trigger.dataset.memberId;
            title.textContent = 'Sửa hồ sơ hội viên';

            form.querySelector('#mf-name').value = trigger.dataset.memberName || '';
            form.querySelector('#mf-phone').value = trigger.dataset.memberPhone || '';
            form.querySelector('#mf-email').value = trigger.dataset.memberEmail || '';
            form.querySelector('#mf-birth').value = trigger.dataset.memberBirth || '';
            form.querySelector('#mf-gender').value = trigger.dataset.memberGender || 'khac';
            form.querySelector('#mf-address').value = trigger.dataset.memberAddress || '';
            form.querySelector('#mf-note').value = trigger.dataset.memberNote || '';
            return;
        }

        form.reset();
        form.dataset.id = '';
        title.textContent = 'Thông tin hội viên';
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var formData = new FormData(form);

        try {
            var res = await fetch(endpoint + '?action=save&id=' + (form.dataset.id || '0'), {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });
            var json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
            alert(json.message);
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-delete-id]');
        if (!button) return;

        if (!confirm('Xóa hội viên ' + button.dataset.deleteName + '? Hồ sơ sẽ không thể khôi phục.')) {
            return;
        }

        try {
            var res = await fetch(endpoint + '?action=delete&id=' + button.dataset.deleteId, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
            alert(json.message);
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });
})();
