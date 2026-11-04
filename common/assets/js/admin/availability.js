(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var rejectId = 0;
    var modal = document.getElementById('rejectModal');
    var rejectForm = document.getElementById('reject-form');

    if (modal && rejectForm) {
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;

            rejectId = parseInt(trigger.dataset.requestId || '0', 10);

            var instructor = modal.querySelector('[data-reject-instructor]');
            var date = modal.querySelector('[data-reject-date]');
            if (instructor) instructor.textContent = trigger.dataset.instructor || '—';
            if (date) date.textContent = trigger.dataset.date || '—';
        });

        rejectForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            var reason = document.getElementById('reject-reason');
            if (reason) reason.value = reason.value.trim();

            try {
                var res = await fetch(endpoint + '?action=reject&id=' + rejectId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ ly_do: reason ? reason.value : '' }),
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
    }

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-approve-id]');
        if (!button) return;

        if (!confirm('Duyệt lịch dạy của ' + button.dataset.approveInstructor + ' ngày ' + button.dataset.approveDate + '?')) {
            return;
        }

        try {
            var res = await fetch(endpoint + '?action=approve&id=' + button.dataset.approveId, {
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
