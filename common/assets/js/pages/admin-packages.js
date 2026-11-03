(function () {
    'use strict';

    const modal = document.getElementById('packageFormModal');
    const form = document.getElementById('package-form');
    if (!modal || !form) return;

    const title = document.getElementById('packageFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;

        if (trigger && trigger.dataset.packageId) {
            form.dataset.apiUrl = '/api/packages/' + trigger.dataset.packageId;
            form.dataset.apiMethod = 'PUT';
            title.textContent = 'Chỉnh sửa gói tập';

            form.querySelector('#pf-name').value = trigger.dataset.packageName || '';
            form.querySelector('#pf-days').value = trigger.dataset.packageDays || '30';
            form.querySelector('#pf-price').value = trigger.dataset.packagePrice || '0';
            form.querySelector('#pf-desc').value = trigger.dataset.packageDesc || '';
            form.querySelector('#pf-status').value = trigger.dataset.packageStatus || 'dang_ban';
            return;
        }

        form.reset();
        form.dataset.apiUrl = '/api/packages';
        form.dataset.apiMethod = 'POST';
        title.textContent = 'Thông tin gói tập';
    });
})();
