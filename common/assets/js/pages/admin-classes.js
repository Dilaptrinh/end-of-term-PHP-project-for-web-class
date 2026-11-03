(function () {
    'use strict';

    const modal = document.getElementById('classFormModal');
    const form = document.getElementById('class-form');
    if (!modal || !form) return;

    const title = document.getElementById('classFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const mode = trigger && trigger.dataset.formMode === 'edit' ? 'edit' : 'create';
        title.textContent = mode === 'edit' ? 'Chỉnh sửa lớp học' : 'Thêm lớp học';

        if (mode !== 'edit') {
            form.reset();
            form.dataset.apiUrl = '/api/classes';
            form.dataset.apiMethod = 'POST';
            form.querySelector('[data-field="capacity"]').value = '20';
            return;
        }

        form.dataset.apiUrl = '/api/classes/' + trigger.dataset.classId;
        form.dataset.apiMethod = 'PUT';

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
})();
