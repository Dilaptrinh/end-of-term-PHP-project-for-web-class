(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var modal = document.getElementById('classFormModal');
    var form = document.getElementById('class-form');
    if (!modal || !form) return;

    var title = document.getElementById('classFormTitle');

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        var mode = trigger && trigger.dataset.formMode === 'edit' ? 'edit' : 'create';
        title.textContent = mode === 'edit' ? 'Chỉnh sửa lớp học' : 'Thêm lớp học';

        if (mode !== 'edit') {
            form.reset();
            form.dataset.id = '';
            form.querySelector('[data-field="capacity"]').value = '20';
            return;
        }

        form.dataset.id = trigger.dataset.classId || '';
        form.querySelector('[data-field="name"]').value = trigger.dataset.className || '';
        form.querySelector('[data-field="room"]').value = trigger.dataset.classRoom || '';
        form.querySelector('[data-field="day"]').value = trigger.dataset.classDay || '1';
        form.querySelector('[data-field="start"]').value = trigger.dataset.classStart || '18:00';
        form.querySelector('[data-field="end"]').value = trigger.dataset.classEnd || '19:00';
        form.querySelector('[data-field="capacity"]').value = trigger.dataset.classCapacity || '20';
        form.querySelector('[data-field="level"]').value = trigger.dataset.classLevel || 'Cơ bản';
        form.querySelector('[data-field="package"]').value = trigger.dataset.classPackage || 'basic';
        form.querySelector('[data-field="desc"]').value = trigger.dataset.classDesc || '';
        form.querySelector('[data-field="instructor"]').value = trigger.dataset.classInstructor || '';
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var formData = new FormData(form);

        try {
            var res = await fetch(endpoint + '?action=save&id=' + (form.dataset.id || '0'), {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
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

        if (!confirm('Xóa lớp ' + button.dataset.deleteName + '? Dữ liệu sẽ không thể khôi phục.')) {
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

(function () {
    'use strict';

    var modal = document.getElementById('classDetailModal');
    if (!modal) return;

    var endpoint = window.location.pathname;
    var title = document.getElementById('classDetailTitle');
    var subtitle = document.getElementById('classDetailSubtitle');
    var loading = document.getElementById('classDetailLoading');
    var errorBox = document.getElementById('classDetailError');
    var body = document.getElementById('classDetailBody');
    var info = document.getElementById('classDetailInfo');
    var memberBody = document.getElementById('classDetailMembers');
    var memberCount = document.getElementById('classMemberCount');

    var statusLabels = {
        dang_mo: ['Đang mở', 'success'],
        cho_duyet: ['Chờ duyệt', 'warning'],
        tu_choi: ['Từ chối', 'danger'],
        da_dong: ['Đã đóng', 'muted'],
        dang_ky: ['Đã đăng ký', 'primary'],
        da_huy: ['Đã hủy', 'danger'],
        cho_xac_nhan: ['Chờ xác nhận', 'warning'],
        hoan_thanh: ['Hoàn thành', 'success']
    };
    var weekdays = ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
    var genders = { nam: 'Nam', nu: 'Nữ', khac: 'Khác' };

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char];
        });
    }

    function badge(status) {
        var item = statusLabels[status] || [status || '—', 'muted'];
        return '<span class="badge-status ' + item[1] + '">' + escapeHtml(item[0]) + '</span>';
    }

    function infoCard(label, value) {
        return '<div class="col-sm-6 col-lg-3">' +
            '<div class="border rounded-3 p-3 h-100">' +
            '<div class="text-tiny text-muted-2 mb-1">' + label + '</div>' +
            '<div class="fw-semibold">' + value + '</div>' +
            '</div></div>';
    }

    modal.addEventListener('show.bs.modal', async function (event) {
        var trigger = event.relatedTarget;
        var classId = trigger && trigger.dataset.classId;
        if (!classId) return;

        title.textContent = trigger.dataset.className || 'Chi tiết lớp học';
        subtitle.textContent = 'Mã lớp #' + classId;
        loading.classList.remove('d-none');
        body.classList.add('d-none');
        errorBox.classList.add('d-none');

        try {
            var response = await fetch(endpoint + '?action=members&id=' + encodeURIComponent(classId), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể tải chi tiết lớp học.');
            }
            render(result.data);
        } catch (error) {
            errorBox.textContent = error.message;
            errorBox.classList.remove('d-none');
        } finally {
            loading.classList.add('d-none');
        }
    });

    function render(data) {
        var classInfo = data.class || {};
        var members = Array.isArray(data.members) ? data.members : [];

        subtitle.textContent = 'Mã lớp #' + classInfo.id + ' · ' + (classInfo.instructor || 'Chưa phân công');
        info.innerHTML = [
            infoCard('Giáo viên', escapeHtml(classInfo.instructor || '—')),
            infoCard('Lịch học', escapeHtml((weekdays[classInfo.weekday] || '—') + ' · ' + classInfo.start + ' – ' + classInfo.end)),
            infoCard('Phòng học', escapeHtml(classInfo.room || 'Chưa xếp')),
            infoCard('Sĩ số', escapeHtml(String(classInfo.enrolled) + '/' + String(classInfo.capacity))),
            infoCard('Trình độ', escapeHtml(classInfo.level || '—')),
            infoCard('Gói tối thiểu', escapeHtml(String(classInfo.required_package || 'basic').toUpperCase())),
            infoCard('Thời gian', escapeHtml((classInfo.from || '—') + ' – ' + (classInfo.to || '—'))),
            infoCard('Trạng thái', badge(classInfo.status)),
            '<div class="col-12"><div class="border rounded-3 p-3">' +
                '<div class="text-tiny text-muted-2 mb-1">Mô tả lớp học</div>' +
                '<div>' + escapeHtml(classInfo.desc || 'Chưa có mô tả.') + '</div>' +
            '</div></div>'
        ].join('');

        memberCount.textContent = members.length + ' học viên';

        if (!members.length) {
            memberBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted-2 py-4">Chưa có học viên đăng ký lớp này.</td></tr>';
        } else {
            memberBody.innerHTML = members.map(function (member, index) {
                return '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td class="cell-strong">' + escapeHtml(member.name) +
                        '<div class="cell-sub">' + escapeHtml(genders[member.gender] || '—') + '</div></td>' +
                    '<td>' + escapeHtml(member.phone || '—') + '</td>' +
                    '<td>' + escapeHtml(member.email || '—') + '</td>' +
                    '<td>' + escapeHtml(member.registered || '—') + '</td>' +
                    '<td>' + badge(member.status) + '</td>' +
                    '</tr>';
            }).join('');
        }

        body.classList.remove('d-none');
    }
})();
