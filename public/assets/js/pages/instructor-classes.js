(function () {
    'use strict';

    const modal = document.getElementById('classStudentsModal');
    const list = document.getElementById('class-students-list');
    if (!modal || !list) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const classId = Number(trigger?.dataset.classId || 0);
        const groups = window.INSTRUCTOR_CLASS_STUDENTS || {};
        const rows = groups[classId] || [];

        if (!rows.length) {
            list.innerHTML = '<tr><td colspan="2" class="text-muted-2">Lớp chưa có học viên.</td></tr>';
            return;
        }

        list.innerHTML = rows.map(function (student) {
            return '<tr>' +
                '<td><div class="user-inline"><span><span class="d-block user-name">' + escapeHtml(student.name) + '</span>' +
                '<span class="d-block user-sub">HV-' + String(student.member_id).padStart(4, '0') + '</span></span></div></td>' +
                '<td class="text-muted-2">' + escapeHtml(student.phone || '') + '</td>' +
                '</tr>';
        }).join('');
    });

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }
})();
