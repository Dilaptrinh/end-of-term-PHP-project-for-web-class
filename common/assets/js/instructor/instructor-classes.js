/*
 * FILE: common/assets/js/instructor/instructor-classes.js
 * Trang dùng: instructor/classes.php
 *
 * Bấm "Học viên": AJAX lấy danh sách học viên (HTML do PHP render)
 * rồi gắn vào modal, không tải lại trang.
 */
(function () {
    'use strict';

    var pageUrl = window.location.pathname;
    var modal = document.getElementById('classStudentsModal');
    var list = document.getElementById('class-students-list');
    var title = document.getElementById('classStudentsTitle');

    if (!modal || !list) return;

    modal.addEventListener('show.bs.modal', async function (event) {
        var trigger = event.relatedTarget;
        var classId = trigger && trigger.dataset.classId;
        if (!classId) return;

        if (title && trigger.dataset.className) {
            title.textContent = 'Học viên lớp ' + trigger.dataset.className;
        }

        list.innerHTML = '<tr><td colspan="4" class="text-muted-2">Đang tải học viên…</td></tr>';

        try {
            var response = await fetch(pageUrl + '?action=members&id=' + encodeURIComponent(classId), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể tải học viên.');
            }

            list.innerHTML = result.html;
        } catch (error) {
            list.textContent = '';
            var row = document.createElement('tr');
            var cell = document.createElement('td');
            cell.colSpan = 4;
            cell.className = 'text-danger';
            cell.textContent = error.message;
            row.appendChild(cell);
            list.appendChild(row);
        }
    });
})();
