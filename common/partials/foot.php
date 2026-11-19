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
        if (window.innerWidth >= 1200) closeSidebar();
    });

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-toast]');
        if (!trigger) return;
        event.preventDefault();
        alert(trigger.dataset.toast || 'Thông báo');
    });

})();
</script>
</body>
</html>
