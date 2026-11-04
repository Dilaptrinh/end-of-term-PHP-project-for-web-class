<script src="<?= e(url('/common/assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script>
    window.APP_BASE = <?= json_encode(base_url(), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script>
(function () {
    'use strict';

    const body = document.body;

    function openSidebar() {
        body.classList.add('sidebar-open');
        const backdrop = document.querySelector('.app-backdrop');
        if (backdrop) backdrop.removeAttribute('hidden');
    }

    function closeSidebar() {
        body.classList.remove('sidebar-open');
    }

    document.addEventListener('click', function (event) {
        const openBtn = event.target.closest('[data-sidebar-open]');
        if (openBtn) {
            event.preventDefault();
            openSidebar();
            return;
        }

        const closeBtn = event.target.closest('[data-sidebar-close]');
        if (closeBtn) {
            if (closeBtn.classList.contains('app-backdrop')) {
                event.preventDefault();
            }
            closeSidebar();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeSidebar();
    });

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-toast]');
        if (!trigger) return;
        event.preventDefault();
        alert(trigger.dataset.toast || 'Thông báo');
    });

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-confirm-action]');
        if (!trigger) return;
        event.preventDefault();
        const message = trigger.dataset.confirmAction || 'Bạn có chắc muốn thực hiện thao tác này?';
        if (window.confirm(message)) {
            alert(trigger.dataset.confirm || 'Thao tác đã được ghi nhận (demo).');
            const modalEl = trigger.closest('.modal');
            if (modalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }
    });

    document.addEventListener('input', function (event) {
        const input = event.target.closest('[data-table-filter]');
        if (!input) return;
        const table = document.querySelector(input.dataset.tableFilter);
        if (!table) return;
        const query = input.value.trim().toLowerCase();
        table.querySelectorAll('tbody tr[data-row]').forEach(function (row) {
            row.hidden = query !== '' && !row.textContent.toLowerCase().includes(query);
        });
    });

})();
</script>
</body>
</html>
