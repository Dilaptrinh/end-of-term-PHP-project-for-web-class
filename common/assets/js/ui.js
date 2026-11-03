/* ============================================================
   FitZone — Hành vi UI dùng chung
   Không xử lý nghiệp vụ. Chỉ điều khiển giao diện demo.
   ============================================================ */
(function () {
    'use strict';

    /* ---------- Sidebar mobile ---------- */
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

    /* ---------- Toast ---------- */
    window.showToast = function (title, text, tone) {
        const existing = document.querySelector('.app-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'app-toast ' + (tone || 'success');
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');

        const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill' };
        toast.innerHTML =
            '<i class="bi bi-' + (icons[tone] || icons.success) + ' fs-5 text-' + (tone === 'danger' ? 'danger' : tone === 'warning' ? 'warning' : 'success') + '"></i>' +
            '<div><div class="toast-title"></div><div class="toast-text"></div></div>' +
            '<button type="button" class="btn-close ms-auto" aria-label="Đóng"></button>';

        toast.querySelector('.toast-title').textContent = title || 'Thành công';
        toast.querySelector('.toast-text').textContent = text || '';
        toast.querySelector('.btn-close').addEventListener('click', function () { toast.remove(); });

        document.body.appendChild(toast);
        window.setTimeout(function () { toast.remove(); }, 4200);
    };

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-toast]');
        if (!trigger) return;
        event.preventDefault();
        showToast(trigger.dataset.toastTitle || 'Thông báo', trigger.dataset.toast, trigger.dataset.toastTone || 'success');
    });

    /* ---------- Form demo (không gửi dữ liệu) ---------- */
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('form[data-demo-submit]');
        if (!form) return;
        event.preventDefault();

        const submitter = event.submitter || form.querySelector('[type="submit"]');
        if (submitter) submitter.classList.add('is-loading');

        window.setTimeout(function () {
            if (submitter) submitter.classList.remove('is-loading');
            const modalEl = form.closest('.modal');
            if (modalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
            showToast(form.dataset.successTitle || 'Đã lưu', form.dataset.success || 'Dữ liệu đã được cập nhật (demo giao diện).');
            if (form.dataset.redirect) {
                window.setTimeout(function () { window.location.href = form.dataset.redirect; }, 700);
            }
        }, 650);
    });

    /* ---------- Xác nhận thao tác ---------- */
    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-confirm-action]');
        if (!trigger) return;
        event.preventDefault();
        const message = trigger.dataset.confirmAction || 'Bạn có chắc muốn thực hiện thao tác này?';
        if (window.confirm(message)) {
            showToast(trigger.dataset.confirmTitle || 'Đã xử lý', trigger.dataset.confirm || 'Thao tác đã được ghi nhận (demo).');
            const modalEl = trigger.closest('.modal');
            if (modalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }
    });

    /* ---------- Lọc bảng tại client ---------- */
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

    /* ---------- Chọn tất cả ---------- */
    document.addEventListener('change', function (event) {
        const master = event.target.closest('[data-check-all]');
        if (!master) return;
        const table = document.querySelector(master.dataset.checkAll);
        if (!table) return;
        table.querySelectorAll('tbody input[type="checkbox"]').forEach(function (box) {
            box.checked = master.checked;
        });
    });

    /* ---------- Sao chép ---------- */
    document.addEventListener('click', function (event) {
        const link = event.target.closest('[data-copy]');
        if (!link) return;
        event.preventDefault();
        const value = link.dataset.copy;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(value).then(function () {
                showToast('Đã sao chép', value);
            }).catch(function () {
                showToast('Không sao chép được', value, 'warning');
            });
        }
    });

    /* ---------- Form gửi API (data-api-url) ---------- */
    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('form[data-api-url]');
        if (!form || !window.api) return;
        event.preventDefault();

        const submitter = event.submitter || form.querySelector('[type="submit"]');
        if (submitter) submitter.classList.add('is-loading');

        const payload = {};
        new FormData(form).forEach(function (value, key) {
            payload[key] = value;
        });

        try {
            const response = await window.api.request(
                (form.dataset.apiMethod || 'POST').toUpperCase(),
                form.dataset.apiUrl,
                payload
            );

            const modalEl = form.closest('.modal');
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            showToast(
                form.dataset.apiSuccessTitle || 'Thành công',
                response.message || form.dataset.apiSuccess || 'Đã lưu dữ liệu.',
                'success'
            );

            if (form.dataset.apiRedirect) {
                window.location.href = form.dataset.apiRedirect;
                return;
            }
            if (form.dataset.apiReload !== 'false') {
                window.setTimeout(function () { window.location.reload(); }, 500);
            }
        } catch (error) {
            const details = error.data && error.data.errors
                ? Object.values(error.data.errors).join(' ')
                : (error.message || 'Đã xảy ra lỗi.');
            showToast('Không thể lưu', details, 'danger');
        } finally {
            if (submitter) submitter.classList.remove('is-loading');
        }
    });

    /* ---------- Nút gọi API (data-api-url) ---------- */
    document.addEventListener('click', async function (event) {
        const trigger = event.target.closest('[data-api-url]');
        if (!trigger || trigger.tagName === 'FORM' || !window.api) return;
        event.preventDefault();

        if (trigger.dataset.apiConfirm && !window.confirm(trigger.dataset.apiConfirm)) {
            return;
        }

        let body = {};
        if (trigger.dataset.apiBody) {
            try {
                body = JSON.parse(trigger.dataset.apiBody);
            } catch (error) {
                body = {};
            }
        }

        trigger.classList.add('is-loading');
        try {
            const response = await window.api.request(
                (trigger.dataset.apiMethod || 'POST').toUpperCase(),
                trigger.dataset.apiUrl,
                body
            );
            showToast(
                trigger.dataset.apiSuccessTitle || 'Thành công',
                response.message || 'Đã xử lý.',
                'success'
            );
            if (trigger.dataset.apiReload !== 'false') {
                window.setTimeout(function () { window.location.reload(); }, 400);
            }
        } catch (error) {
            const details = error.data && error.data.errors
                ? Object.values(error.data.errors).join(' ')
                : (error.message || 'Đã xảy ra lỗi.');
            showToast('Không thể thực hiện', details, 'danger');
            trigger.classList.remove('is-loading');
        }
    });

    /* ---------- Tooltip ---------- */
    if (window.bootstrap) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }
})();
