(function () {
    'use strict';

    const modal = document.getElementById('memberFormModal');
    const form = document.getElementById('member-form');
    if (!modal || !form) return;

    const title = document.getElementById('memberFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;

        if (trigger && trigger.dataset.memberId) {
            form.dataset.apiUrl = '/api/members/' + trigger.dataset.memberId;
            form.dataset.apiMethod = 'PUT';
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
        form.dataset.apiUrl = '/api/members';
        form.dataset.apiMethod = 'POST';
        title.textContent = 'Thông tin hội viên';
    });
})();
