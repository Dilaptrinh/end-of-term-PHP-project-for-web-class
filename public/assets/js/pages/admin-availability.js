(function () {
    'use strict';

    const modal = document.getElementById('rejectModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        const instructor = modal.querySelector('[data-reject-instructor]');
        const date = modal.querySelector('[data-reject-date]');
        if (instructor) instructor.textContent = trigger.dataset.instructor || '—';
        if (date) date.textContent = trigger.dataset.date || '—';
    });
})();
