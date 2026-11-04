(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('classFormModal');
    var form = document.getElementById('class-form');
    if (!modal || !form) return;

    var title = document.getElementById('classFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        var mode = trigger && trigger.dataset.formMode === 'edit' ? 'edit' : 'create';
        title.textContent = mode === 'edit' ? 'Chỉnh sửa lớp học' : 'Thêm lớp học';

        if (mode !== 'edit') {
            form.reset();
            form.dataset.id = '';
            form.querySelector('[data-field="capacity"]').value = '20';
            return;
        }

        form.dataset.id = trigger.dataset.classId || '';
        form.querySelector('[data-field="name"]').value = trigger.dataset.className || '';
        form.querySelector('[data-field="room"]').value = trigger.dataset.classRoom || '';
        form.querySelector('[data-field="day"]').value = trigger.dataset.classDay || '1';
        form.querySelector('[data-field="start"]').value = trigger.dataset.classStart || '18:00';
        form.querySelector('[data-field="end"]').value = trigger.dataset.classEnd || '19:00';
        form.querySelector('[data-field="capacity"]').value = trigger.dataset.classCapacity || '20';
        form.querySelector('[data-field="level"]').value = trigger.dataset.classLevel || 'Cơ bản';
        form.querySelector('[data-field="desc"]').value = trigger.dataset.classDesc || '';
        form.querySelector('[data-field="instructor"]').value = trigger.dataset.classInstructor || '';
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var data = {};
        new FormData(form).forEach(function (value, key) {
            data[key] = value;
        });

        try {
            var res = await fetch(endpoint + '?action=save&id=' + (form.dataset.id || '0'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data),
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

        if (!confirm('Xóa lớp ' + button.dataset.deleteName + '? Dữ liệu sẽ không thể khôi phục.')) {
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
