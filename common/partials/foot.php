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

    document.addEventListener('click', function (event) {
        const reload = event.target.closest('[data-reload-page]');
        if (reload) {
            event.preventDefault();
            window.location.reload();
            return;
        }
        const print = event.target.closest('[data-print-page]');
        if (print) {
            event.preventDefault();
            window.print();
            return;
        }
        const exportButton = event.target.closest('[data-export-table]');
        if (!exportButton) return;
        const table = document.querySelector(exportButton.dataset.exportTable);
        if (!table) return;
        const rows = Array.from(table.querySelectorAll('tr')).map(function (row) {
            return Array.from(row.querySelectorAll('th,td')).map(function (cell) {
                return '"' + cell.innerText.replace(/"/g, '""').replace(/\n/g, ' ') + '"';
            }).join(',');
        });
        const blob = new Blob(["\ufeff" + rows.join('\r\n')], { type: 'text/csv;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = exportButton.dataset.exportName || 'fitzone-export.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });

    document.addEventListener('click', function (event) {
        const detail = event.target.closest('[data-detail]');
        if (detail) {
            event.preventDefault();
            alert(detail.dataset.detail || 'Không có thông tin chi tiết.');
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
