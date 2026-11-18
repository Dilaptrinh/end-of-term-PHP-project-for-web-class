(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('classStudentsModal');
    var list = document.getElementById('class-students-list');
    var title = document.getElementById('classStudentsTitle');

    if (!modal || !list) return;

    function escapeHtml(value) {
        var characters = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
            return characters[character];
        });
    }

    // Tạo mã học viên HV-0001.
    function memberCode(id) {
        var text = String(id || 0);
        while (text.length < 4) text = '0' + text;
        return 'HV-' + text;
    }

    // Tạo một dòng học viên.
    function memberRow(member) {
        return '<tr>' +
            '<td>' +
                '<div class="user-inline">' +
                    '<span>' +
                        '<span class="d-block user-name">' + escapeHtml(member.name) + '</span>' +
                        '<span class="d-block user-sub">' + escapeHtml(memberCode(member.member_id)) + '</span>' +
                    '</span>' +
                '</div>' +
            '</td>' +
            '<td class="text-muted-2">' + escapeHtml(member.phone || '—') + '</td>' +
            '<td class="text-muted-2">' + escapeHtml(member.email || '—') + '</td>' +
            '<td class="text-muted-2">' + escapeHtml(member.registered || '—') + '</td>' +
        '</tr>';
    }

    // Render danh sách học viên.
    function renderStudents(members) {
        if (!members.length) {
            return '<tr><td colspan="4" class="text-muted-2">Lớp chưa có học viên.</td></tr>';
        }

        return members.map(memberRow).join('');
    }

    // Hiển thị lỗi trong bảng.
    function showError(message) {
        list.innerHTML = '<tr><td colspan="4" class="text-danger">' + escapeHtml(message) + '</td></tr>';
    }

    modal.addEventListener('show.bs.modal', async function (event) {
        var trigger = event.relatedTarget;
        var classId = trigger && trigger.dataset.classId;
        if (!classId) return;

        if (title && trigger.dataset.className) {
            title.textContent = 'Học viên lớp ' + trigger.dataset.className;
        }

        list.innerHTML = '<tr><td colspan="4" class="text-muted-2">Đang tải học viên…</td></tr>';

        try {
            var response = await fetch(endpoint + '?action=members&id=' + encodeURIComponent(classId), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể tải học viên.');
            }

            list.innerHTML = renderStudents(Array.isArray(result.data) ? result.data : []);
        } catch (error) {
            showError(error.message);
        }
    });
})();
