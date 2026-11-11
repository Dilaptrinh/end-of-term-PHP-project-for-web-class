(function () {
    'use strict';

    var endpoint = window.location.pathname;

    var modal = document.getElementById('registerClassModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;
            modal.querySelector('[data-modal-class-name]').textContent = trigger.dataset.className || '';
            modal.querySelector('[data-modal-class-meta]').textContent = trigger.dataset.classMeta || '';
            modal.querySelector('[data-modal-class-teacher]').textContent = trigger.dataset.classTeacher || '';
            var idInput = document.getElementById('register-class-id');
            if (idInput) idInput.value = trigger.dataset.classId || '';
        });
    }

    var onlyAvailable = document.getElementById('only-available');
    if (onlyAvailable) {
        onlyAvailable.addEventListener('change', function () {
            document.querySelectorAll('[data-class-card]').forEach(function (card) {
                card.hidden = onlyAvailable.checked && card.dataset.available === '0';
            });
        });
    }

    document.getElementById('register-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append('lop_hoc_id', document.getElementById('register-class-id').value);

        try {
            var res = await fetch(endpoint + '?action=register', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });
            var json = await res.json().catch(function () {
                return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
            });
            if (!res.ok || !json.success) {
                throw new Error(json.message || 'Có lỗi xảy ra.');
            }
            alert(json.message);
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });
})();
