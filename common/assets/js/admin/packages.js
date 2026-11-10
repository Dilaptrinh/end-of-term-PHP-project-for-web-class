(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('packageFormModal');
    var form = document.getElementById('package-form');
    if (!modal || !form) return;

    var title = document.getElementById('packageFormTitle');
    var query = document.getElementById('pk-q');
    var status = document.getElementById('pk-status');
    var reset = document.getElementById('pk-reset');

    function filterCards() {
        var text = (query.value || '').trim().toLowerCase();
        var selected = status.value;
        document.querySelectorAll('#packages-table [data-row]').forEach(function (card) {
            var matchesText = !text || card.dataset.packageId === text;
            var matchesStatus = selected === 'Tất cả' || (selected === 'Đang bán' && card.dataset.packageStatus === 'dang_ban') || (selected === 'Ngừng bán' && card.dataset.packageStatus === 'ngung_ban');
            card.hidden = !matchesText || !matchesStatus;
        });
    }
    if (query && status) {
        query.addEventListener('input', filterCards);
        status.addEventListener('change', filterCards);
        reset.addEventListener('click', function () { query.value = ''; status.value = 'Tất cả'; filterCards(); });
    }

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;

        if (trigger && trigger.dataset.packageId) {
            form.dataset.id = trigger.dataset.packageId;
            title.textContent = 'Chỉnh sửa gói tập';

            form.querySelector('#pf-name').value = trigger.dataset.packageName || '';
            form.querySelector('#pf-days').value = trigger.dataset.packageDays || '30';
            form.querySelector('#pf-price').value = trigger.dataset.packagePrice || '0';
            form.querySelector('#pf-desc').value = trigger.dataset.packageDesc || '';
            form.querySelector('#pf-status').value = trigger.dataset.packageStatus || 'dang_ban';
            return;
        }

        form.reset();
        form.dataset.id = '';
        title.textContent = 'Thông tin gói tập';
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

        if (!confirm('Xóa gói ' + button.dataset.deleteName + '? Dữ liệu sẽ không thể khôi phục.')) {
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
