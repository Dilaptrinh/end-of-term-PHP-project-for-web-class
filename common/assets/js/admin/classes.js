(function () {
    'use strict';

    var classesBody = document.getElementById('classes-body');
    var modal = document.getElementById('classDetailModal');
    var detailBody = document.getElementById('classDetailBody');
    var detailTitle = document.getElementById('classDetailTitle');

    async function callApi(url, options) {
        var response = await fetch(url, options);
        var result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra.');
        }

        return result;
    }

    function appendRow(html) {
        if (!classesBody || !html) return;

        var empty = classesBody.querySelector('[data-empty-row]');
        if (empty) empty.remove();

        classesBody.insertAdjacentHTML('beforeend', html);
    }

    function replaceRow(id, html) {
        var row = document.querySelector('#classes-body [data-class-row="' + Number(id) + '"]');
        if (!row) {
            appendRow(html);
            return;
        }

        row.outerHTML = html;
    }

    function removeRow(id) {
        var row = document.querySelector('#classes-body [data-class-row="' + Number(id) + '"]');
        if (row) row.remove();

        if (classesBody && !classesBody.querySelector('[data-row]')) {
            classesBody.innerHTML = '<tr data-empty-row><td colspan="8" class="text-center text-muted-2 py-4">Chưa có lớp học nào.</td></tr>';
        }
    }

    async function openDetail(id) {
        if (!modal || !detailBody) return;

        detailTitle.textContent = id > 0 ? 'Chi tiết lớp học' : 'Thêm lớp học';
        detailBody.innerHTML = '<div class="text-center text-muted-2 py-4">Đang tải...</div>';
        bootstrap.Modal.getOrCreateInstance(modal).show();

        try {
            var result = await callApi(location.pathname + '?action=detail&id=' + encodeURIComponent(id), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            if (result.title) detailTitle.textContent = result.title;
            detailBody.innerHTML = result.html;
        } catch (error) {
            detailBody.innerHTML = '<div class="alert alert-danger mb-0" role="alert"></div>';
            detailBody.firstChild.textContent = error.message;
        }
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-class-detail]');
        if (trigger) {
            openDetail(Number(trigger.dataset.classDetail || 0));
        }
    });

    if (detailBody) {
        detailBody.addEventListener('submit', async function (event) {
            var form = event.target.closest('#class-form');
            if (!form) return;
            event.preventDefault();

            var id = Number(form.dataset.classId || 0);
            var submit = form.querySelector('[type="submit"]');
            if (submit) submit.disabled = true;

            try {
                var result = await callApi(location.pathname + '?action=save&id=' + id, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });

                if (result.created) {
                    appendRow(result.html);
                } else {
                    replaceRow(result.id, result.html);
                }

                bootstrap.Modal.getOrCreateInstance(modal).hide();
                alert(result.message);
            } catch (error) {
                alert(error.message);
            } finally {
                if (submit) submit.disabled = false;
            }
        });

        detailBody.addEventListener('click', async function (event) {
            var button = event.target.closest('[data-class-delete]');
            if (!button) return;

            var name = button.dataset.className || 'lớp học này';
            if (!confirm('Xóa ' + name + '? Dữ liệu sẽ không thể khôi phục.')) return;

            button.disabled = true;

            try {
                var result = await callApi(location.pathname + '?action=delete&id=' + button.dataset.classDelete, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(),
                    credentials: 'same-origin'
                });

                removeRow(button.dataset.classDelete);
                bootstrap.Modal.getOrCreateInstance(modal).hide();
                alert(result.message);
            } catch (error) {
                alert(error.message);
                button.disabled = false;
            }
        });
    }
})();
