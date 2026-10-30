(function () {
    'use strict';

    const modal = document.getElementById('registerClassModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;
            modal.querySelector('[data-modal-class-name]').textContent = trigger.dataset.className || '';
            modal.querySelector('[data-modal-class-meta]').textContent = trigger.dataset.classMeta || '';
            modal.querySelector('[data-modal-class-teacher]').textContent = trigger.dataset.classTeacher || '';
            const idInput = document.getElementById('register-class-id');
            if (idInput) idInput.value = trigger.dataset.classId || '';
        });
    }

    const onlyAvailable = document.getElementById('only-available');
    if (onlyAvailable) {
        onlyAvailable.addEventListener('change', function () {
            document.querySelectorAll('[data-class-card]').forEach(function (card) {
                card.hidden = onlyAvailable.checked && card.dataset.available === '0';
            });
        });
    }
})();
