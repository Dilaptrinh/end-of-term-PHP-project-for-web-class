(function () {
    'use strict';

    var classesBody = document.getElementById('classes-body');

    var statusLabels = {
        dang_mo: ['Đang mở', 'success'],
        da_dong: ['Đã đóng', 'muted'],
        dang_ky: ['Đã đăng ký', 'primary'],
        da_huy: ['Đã hủy', 'danger']
    };

    var weekdays = ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
    var genders = { nam: 'Nam', nu: 'Nữ', khac: 'Khác' };

    // Escape ký tự HTML.
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

    // Tạo badge trạng thái.
    function statusBadge(status) {
        var item = statusLabels[status] || [status || '—', 'muted'];

        return '<span class="badge-status ' + item[1] + '">' + escapeHtml(item[0]) + '</span>';
    }

    // Tạo một dòng lớp trong bảng.
    function row(item) {
        return '<tr data-row data-class-row="' + Number(item.id) + '">' +
            '<td class="cell-strong">' + escapeHtml(item.name) + '<div class="cell-sub">Mã lớp: ' + Number(item.id) + ' · ' + escapeHtml(item.level) + '</div></td>' +
            '<td>' + escapeHtml(item.instructor) + '</td>' +
            '<td>' + escapeHtml(weekdays[item.weekday] || '—') + '<div class="cell-sub">' + escapeHtml(item.start + ' – ' + item.end) + '</div></td>' +
            '<td>' + escapeHtml(item.room) + '</td>' +
            '<td>' + escapeHtml(String(item.enrolled) + '/' + String(item.capacity)) + '</td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(item.from) + '<br>' + escapeHtml(item.to) + '</td>' +
            '<td>' + statusBadge(item.status) + '</td>' +
            '<td class="cell-actions">' +
                '<div class="d-inline-flex gap-1">' +
                    '<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#classDetailModal"' +
                        ' data-class-id="' + Number(item.id) + '"' +
                        ' data-class-name="' + escapeHtml(item.name) + '" title="Xem chi tiết" aria-label="Xem chi tiết"><i class="bi bi-info-circle"></i></button>' +
                    '<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#classFormModal"' +
                        ' data-form-mode="edit"' +
                        ' data-class-id="' + Number(item.id) + '"' +
                        ' data-class-name="' + escapeHtml(item.name) + '"' +
                        ' data-class-instructor="' + Number(item.giao_vien_id || 0) + '"' +
                        ' data-class-day="' + Number(item.weekday) + '"' +
                        ' data-class-start="' + escapeHtml(item.start) + '"' +
                        ' data-class-end="' + escapeHtml(item.end) + '"' +
                        ' data-class-room="' + escapeHtml(item.room) + '"' +
                        ' data-class-capacity="' + Number(item.capacity) + '"' +
                        ' data-class-level="' + escapeHtml(item.level) + '"' +
                        ' data-class-package="' + escapeHtml(item.required_package) + '"' +
                        ' data-class-status="' + escapeHtml(item.status) + '"' +
                        ' data-class-desc="' + escapeHtml(item.desc) + '" title="Sửa lớp" aria-label="Sửa lớp"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-ghost btn-sm text-danger" type="button"' +
                        ' data-delete-id="' + Number(item.id) + '"' +
                        ' data-delete-name="' + escapeHtml(item.name) + '" title="Xóa lớp" aria-label="Xóa lớp"><i class="bi bi-trash3"></i></button>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }

    // Tạo một ô thông tin.
    function infoCard(label, value) {
        return '<div class="col-sm-6 col-lg-3">' +
            '<div class="border rounded-3 p-3 h-100">' +
                '<div class="text-tiny text-muted-2 mb-1">' + label + '</div>' +
                '<div class="fw-semibold">' + value + '</div>' +
            '</div>' +
        '</div>';
    }

    // Tạo phần thông tin lớp trong modal chi tiết.
    function detail(classInfo) {
        var schedule = (weekdays[classInfo.weekday] || '—') +
            ' · ' + (classInfo.start || '—') +
            ' – ' + (classInfo.end || '—');

        return [
            infoCard('Giáo viên', escapeHtml(classInfo.instructor || '—')),
            infoCard('Lịch học', escapeHtml(schedule)),
            infoCard('Phòng học', escapeHtml(classInfo.room || 'Chưa xếp')),
            infoCard('Sĩ số', escapeHtml(String(classInfo.enrolled) + '/' + String(classInfo.capacity))),
            infoCard('Trình độ', escapeHtml(classInfo.level || '—')),
            infoCard('Gói tối thiểu', escapeHtml(String(classInfo.required_package || 'basic').toUpperCase())),
            infoCard('Thời gian', escapeHtml((classInfo.from || '—') + ' – ' + (classInfo.to || '—'))),
            infoCard('Trạng thái', statusBadge(classInfo.status)),
            '<div class="col-12">' +
                '<div class="border rounded-3 p-3">' +
                    '<div class="text-tiny text-muted-2 mb-1">Mô tả lớp học</div>' +
                    '<div>' + escapeHtml(classInfo.desc || 'Chưa có mô tả.') + '</div>' +
                '</div>' +
            '</div>'
        ].join('');
    }

    // Tạo danh sách học viên trong modal chi tiết.
    function memberRows(members) {
        if (!members.length) {
            return '<tr><td colspan="6" class="text-center text-muted-2 py-4">Chưa có học viên đăng ký lớp này.</td></tr>';
        }

        return members.map(function (member, index) {
            return '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td class="cell-strong">' + escapeHtml(member.name) +
                    '<div class="cell-sub">' + escapeHtml(genders[member.gender] || '—') + '</div>' +
                '</td>' +
                '<td>' + escapeHtml(member.phone || '—') + '</td>' +
                '<td>' + escapeHtml(member.email || '—') + '</td>' +
                '<td>' + escapeHtml(member.registered || '—') + '</td>' +
                '<td>' + statusBadge(member.status) + '</td>' +
            '</tr>';
        }).join('');
    }

    // Tạo dòng bảng trống.
    function emptyRow() {
        return '<tr data-empty-row><td colspan="8" class="text-center text-muted-2 py-4">Chưa có lớp học nào.</td></tr>';
    }

    // Gọi API và trả JSON.
    async function callApi(url, options) {
        var response = await fetch(url, options);
        var result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra.');
        }

        return result;
    }

    // Hiện dòng bảng trống.
    function showEmptyRow() {
        if (!classesBody) return;

        classesBody.innerHTML = emptyRow();
    }

    // Thêm dòng lớp mới.
    function appendClassRow(html) {
        if (!classesBody || !html) return;

        var empty = classesBody.querySelector('[data-empty-row]');
        if (empty) empty.remove();

        classesBody.insertAdjacentHTML('beforeend', html);
    }

    // Thay dòng lớp cũ.
    function replaceClassRow(oldRow, html) {
        if (!oldRow || !html) return;
        oldRow.outerHTML = html;
    }

    // Xóa dòng lớp.
    function removeClassRow(row) {
        if (!row) return;

        row.remove();

        if (!classesBody.querySelector('[data-row]')) {
            showEmptyRow();
        }
    }

    // Map tên field sang name của input.
    var fieldNames = {
        name: 'ten_lop',
        level: 'trinh_do',
        package: 'goi_tap_toi_thieu',
        instructor: 'giao_vien_id',
        room: 'phong_hoc',
        day: 'thu_trong_tuan',
        start: 'gio_bat_dau',
        end: 'gio_ket_thuc',
        capacity: 'suc_chua',
        status: 'trang_thai',
        desc: 'mo_ta'
    };

    var validStatuses = ['dang_mo', 'da_dong', 'da_huy'];

    // Gán giá trị cho một field.
    function setField(form, key, value) {
        var input = form.elements[fieldNames[key]];
        if (input) input.value = value;
    }

    // Giá trị mặc định của form.
    function formDefaults() {
        return {
            status: 'dang_mo',
            day: '1',
            start: '18:00',
            end: '19:00',
            capacity: '20',
            level: 'Cơ bản',
            package: 'basic'
        };
    }

    // Reset form thêm mới.
    function resetClassForm(form) {
        var defaults = formDefaults();

        form.reset();
        form.dataset.id = '';

        Object.keys(defaults).forEach(function (key) {
            setField(form, key, defaults[key]);
        });
    }

    // Đổ dữ liệu lớp vào form khi sửa.
    function fillClassForm(form, button) {
        var defaults = formDefaults();
        form.dataset.id = button.dataset.classId || '';

        var values = {
            name: button.dataset.className,
            room: button.dataset.classRoom,
            day: button.dataset.classDay,
            start: button.dataset.classStart,
            end: button.dataset.classEnd,
            capacity: button.dataset.classCapacity,
            level: button.dataset.classLevel,
            package: button.dataset.classPackage,
            desc: button.dataset.classDesc,
            instructor: button.dataset.classInstructor,
            status: button.dataset.classStatus
        };

        Object.keys(values).forEach(function (key) {
            setField(form, key, values[key] || defaults[key] || '');
        });
    }

    // Gắn sự kiện cho modal thêm/sửa.
    function setupClassForm() {
        var modal = document.getElementById('classFormModal');
        var form = document.getElementById('class-form');
        var title = document.getElementById('classFormTitle');

        if (!modal || !form || !title) return;

        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var isEdit = button && button.dataset.formMode === 'edit';

            title.textContent = isEdit ? 'Chỉnh sửa lớp học' : 'Thêm lớp học';

            if (isEdit) {
                fillClassForm(form, button);
            } else {
                resetClassForm(form);
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            var id = form.dataset.id || '0';
            var oldRow = id === '0'
                ? null
                : document.querySelector('#classes-body [data-class-row="' + id + '"]');

            try {
                var result = await callApi(location.pathname + '?action=save&id=' + id, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });

                if (!result.data) {
                    throw new Error('Không tìm thấy lớp học.');
                }

                if (id === '0') {
                    appendClassRow(row(result.data));
                } else {
                    replaceClassRow(oldRow, row(result.data));
                }

                bootstrap.Modal.getOrCreateInstance(modal).hide();
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // Gắn sự kiện xóa lớp.
    function setupClassDeletion() {
        document.addEventListener('click', async function (event) {
            var button = event.target.closest('[data-delete-id]');
            if (!button) return;

            var name = button.dataset.deleteName || 'lớp học này';
            if (!confirm('Xóa ' + name + '? Dữ liệu sẽ không thể khôi phục.')) {
                return;
            }

            try {
                var formData = new FormData();
                formData.append('id', button.dataset.deleteId);

                var result = await callApi(location.pathname + '?action=delete&id=' + button.dataset.deleteId, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                removeClassRow(button.closest('tr'));
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // Hiển thị chi tiết lớp.
    function renderClassDetails(elements, data) {
        var classInfo = data.class || {};
        var members = Array.isArray(data.members) ? data.members : [];

        elements.subtitle.textContent =
            'Mã lớp #' + classInfo.id + ' · ' + (classInfo.instructor || 'Chưa phân công');

        elements.info.innerHTML = detail(classInfo);
        elements.memberCount.textContent = members.length + ' học viên';
        elements.memberBody.innerHTML = memberRows(members);
        elements.body.classList.remove('d-none');
    }

    // Gắn sự kiện cho modal chi tiết.
    function setupClassDetails() {
        var modal = document.getElementById('classDetailModal');
        if (!modal) return;

        var elements = {
            title: document.getElementById('classDetailTitle'),
            subtitle: document.getElementById('classDetailSubtitle'),
            loading: document.getElementById('classDetailLoading'),
            error: document.getElementById('classDetailError'),
            body: document.getElementById('classDetailBody'),
            info: document.getElementById('classDetailInfo'),
            memberBody: document.getElementById('classDetailMembers'),
            memberCount: document.getElementById('classMemberCount')
        };

        modal.addEventListener('show.bs.modal', async function (event) {
            var button = event.relatedTarget;
            var classId = button && button.dataset.classId;
            if (!classId) return;

            elements.title.textContent = button.dataset.className || 'Chi tiết lớp học';
            elements.subtitle.textContent = 'Mã lớp #' + classId;
            elements.loading.classList.remove('d-none');
            elements.body.classList.add('d-none');
            elements.error.classList.add('d-none');

            try {
                var result = await callApi(
                    location.pathname + '?action=members&id=' + encodeURIComponent(classId),
                    {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }
                );

                renderClassDetails(elements, result.data);
            } catch (error) {
                elements.error.textContent = error.message;
                elements.error.classList.remove('d-none');
            } finally {
                elements.loading.classList.add('d-none');
            }
        });
    }

    // Render bảng ban đầu từ JSON của PHP.
    var initial = document.getElementById('class-init');
    if (initial && classesBody) {
        var rows = JSON.parse(initial.textContent || '[]');

        classesBody.innerHTML = rows.map(row).join('');

        if (!rows.length) {
            showEmptyRow();
        }
    }

    setupClassForm();
    setupClassDeletion();
    setupClassDetails();
})();
