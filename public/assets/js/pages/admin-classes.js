(function () {
    'use strict';

    const modal = document.getElementById('classFormModal');
    if (!modal) return;

    const title = document.getElementById('classFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const mode = trigger && trigger.dataset.formMode === 'edit' ? 'edit' : 'create';
        title.textContent = mode === 'edit' ? 'Chỉnh sửa lớp học' : 'Thêm lớp học';

        if (mode !== 'edit') {
            modal.querySelector('[data-field="name"]').value = '';
            modal.querySelector('[data-field="room"]').value = '';
            modal.querySelector('[data-field="capacity"]').value = '20';
            modal.querySelector('[data-field="desc"]').value = '';
            return;
        }

        modal.querySelector('[data-field="name"]').value = trigger.dataset.className || '';
        modal.querySelector('[data-field="room"]').value = trigger.dataset.classRoom || '';
        modal.querySelector('[data-field="day"]').value = trigger.dataset.classDay || '1';
        modal.querySelector('[data-field="start"]').value = trigger.dataset.classStart || '18:00';
        modal.querySelector('[data-field="end"]').value = trigger.dataset.classEnd || '19:00';
        modal.querySelector('[data-field="capacity"]').value = trigger.dataset.classCapacity || '20';
        modal.querySelector('[data-field="level"]').value = trigger.dataset.classLevel || 'Cơ bản';
        modal.querySelector('[data-field="desc"]').value = trigger.dataset.classDesc || '';

        const instructorSelect = modal.querySelector('[data-field="instructor"]');
        if (instructorSelect) instructorSelect.value = trigger.dataset.classInstructor || '';
    });
})();
