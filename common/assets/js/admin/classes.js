(function () {
    'use strict';

    // Địa chỉ trang hiện tại: /fitness/admin/classes.php
    var pageUrl = window.location.pathname;
    var classesBody = document.getElementById('classes-body');

    // ---------------------------------------------
    // Hàm dùng chung
    // ---------------------------------------------

    // Gửi request lên server và trả về JSON.
    async function callApi(url, options) {
        var response = await fetch(url, options);
        var result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Có lỗi xảy ra.');
        }

        return result;
    }

    // Hiện dòng thông báo khi bảng không còn lớp nào.
    function showEmptyRow() {
        if (!classesBody) return;

        classesBody.innerHTML =
            '<tr data-empty-row>' +
                '<td colspan="8" class="text-center text-muted-2 py-4">Chưa có lớp học nào.</td>' +
            '</tr>';
    }

    // Thêm lớp mới vào cuối bảng.
    function appendClassRow(html) {
        if (!classesBody || !html) return;

        var emptyRow = classesBody.querySelector('[data-empty-row]');
        if (emptyRow) emptyRow.remove();

        classesBody.insertAdjacentHTML('beforeend', html);
    }

    // Thay dòng lớp cũ bằng dòng mới server trả về.
    function replaceClassRow(oldRow, html) {
        if (!oldRow || !html) return;
        oldRow.outerHTML = html;
    }

    // Xóa dòng lớp khỏi bảng.
    function removeClassRow(row) {
        if (!row) return;

        row.remove();

        if (!classesBody.querySelector('[data-row]')) {
            showEmptyRow();
        }
    }

    // Dựng thanh phần trăm sĩ số.
    function progressBar(percent) {
        var variant = percent >= 100 ? 'red' : (percent >= 80 ? 'amber' : '');
        var className = 'app-progress' + (variant ? ' ' + variant : '');

        return '<div class="' + className + '" role="progressbar" aria-valuenow="' + percent +
            '" aria-valuemin="0" aria-valuemax="100"><span style="width:' + percent + '%"></span></div>';
    }

    // Dựng HTML cho một dòng lớp học.
    function renderClassRow(item) {
        var enrolled = Number(item.enrolled || 0);
        var capacity = Number(item.capacity || 0);
        var percent = Math.round(enrolled / Math.max(1, capacity) * 100);
        var dayName = weekdays[Number(item.weekday)] || '—';

        return '<tr data-row data-class-row="' + Number(item.id) + '">' +
            '<td class="cell-strong">' + escapeHtml(item.name) +
                '<div class="cell-sub">Mã lớp: ' + Number(item.id) + ' · ' + escapeHtml(item.level) + '</div></td>' +
            '<td>' + escapeHtml(item.instructor) + '</td>' +
            '<td>' + escapeHtml(dayName) +
                '<div class="cell-sub">' + escapeHtml(item.start + ' – ' + item.end) + '</div></td>' +
            '<td>' + escapeHtml(item.room) + '</td>' +
            '<td style="min-width:130px;">' +
                '<div class="d-flex justify-content-between text-tiny mb-1">' +
                    '<span>' + enrolled + '/' + capacity + '</span><span>' + percent + '%</span>' +
                '</div>' + progressBar(percent) +
            '</td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(item.from) + '<br>' + escapeHtml(item.to) + '</td>' +
            '<td>' + statusBadge(item.status) + '</td>' +
            '<td class="cell-actions">' +
                '<div class="dropdown">' +
                    '<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Thao tác">' +
                        '<i class="bi bi-three-dots"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#classDetailModal" ' +
                            'data-class-id="' + Number(item.id) + '" ' +
                            'data-class-name="' + escapeHtml(item.name) + '">' +
                            '<i class="bi bi-info-circle"></i> Xem chi tiết</button></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#classFormModal" ' +
                            'data-form-mode="edit" ' +
                            'data-class-id="' + Number(item.id) + '" ' +
                            'data-class-name="' + escapeHtml(item.name) + '" ' +
                            'data-class-instructor="' + Number(item.giao_vien_id) + '" ' +
                            'data-class-day="' + Number(item.weekday) + '" ' +
                            'data-class-start="' + escapeHtml(item.start) + '" ' +
                            'data-class-end="' + escapeHtml(item.end) + '" ' +
                            'data-class-room="' + escapeHtml(item.room) + '" ' +
                            'data-class-capacity="' + capacity + '" ' +
                            'data-class-level="' + escapeHtml(item.level) + '" ' +
                            'data-class-package="' + escapeHtml(item.required_package) + '" ' +
                            'data-class-status="' + escapeHtml(item.status) + '" ' +
                            'data-class-desc="' + escapeHtml(item.desc) + '">' +
                            '<i class="bi bi-pencil"></i> Sửa lớp</a></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><button class="dropdown-item text-danger" type="button" ' +
                            'data-delete-id="' + Number(item.id) + '" ' +
                            'data-delete-name="' + escapeHtml(item.name) + '">' +
                            '<i class="bi bi-trash3"></i> Xóa lớp</button></li>' +
                    '</ul>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }

    // ---------------------------------------------
    // Form thêm / sửa lớp học
    // ---------------------------------------------

    function getField(form, name) {
        return form.querySelector('[data-field="' + name + '"]');
    }

    function setField(form, name, value) {
        var input = getField(form, name);
        if (input) {
            input.value = value;
        }
    }

    // Giá trị mặc định khi thêm lớp mới.
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

    // Xóa trắng form (dùng khi thêm mới).
    function resetClassForm(form) {
        var defaults = formDefaults();

        form.reset();
        form.dataset.id = '';

        document.getElementById('cf-status').value = defaults.status;
        setField(form, 'day', defaults.day);
        setField(form, 'start', defaults.start);
        setField(form, 'end', defaults.end);
        setField(form, 'capacity', defaults.capacity);
        setField(form, 'level', defaults.level);
        setField(form, 'package', defaults.package);
    }

    // Đổ dữ liệu của lớp vào form (dùng khi sửa).
    function fillClassForm(form, button) {
        var defaults = formDefaults();

        form.dataset.id = button.dataset.classId || '';
        document.getElementById('cf-status').value =
            button.dataset.classStatus || defaults.status;

        setField(form, 'name', button.dataset.className || '');
        setField(form, 'room', button.dataset.classRoom || '');
        setField(form, 'day', button.dataset.classDay || defaults.day);
        setField(form, 'start', button.dataset.classStart || defaults.start);
        setField(form, 'end', button.dataset.classEnd || defaults.end);
        setField(form, 'capacity', button.dataset.classCapacity || defaults.capacity);
        setField(form, 'level', button.dataset.classLevel || defaults.level);
        setField(form, 'package', button.dataset.classPackage || defaults.package);
        setField(form, 'desc', button.dataset.classDesc || '');
        setField(form, 'instructor', button.dataset.classInstructor || '');
    }

    function setupClassForm() {
        var modal = document.getElementById('classFormModal');
        var form = document.getElementById('class-form');
        var title = document.getElementById('classFormTitle');

        if (!modal || !form || !title) return;

        // Khi mở modal: nếu bấm nút "Sửa" thì đổ dữ liệu, ngược lại reset form.
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

        // Lưu lớp học (thêm mới hoặc cập nhật).
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            var id = form.dataset.id || '0';

            // Nếu đang sửa thì tìm dòng cũ để thay thế.
            var oldRow = null;
            if (id !== '0') {
                oldRow = document.querySelector('#classes-body [data-class-row="' + id + '"]');
            }

            try {
                var result = await callApi(pageUrl + '?action=save&id=' + id, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: new FormData(form),
                    credentials: 'same-origin'
                });

                // Server trả dữ liệu lớp mới, JS tự dựng lại dòng.
                var html = renderClassRow(result.data);

                if (id === '0') {
                    appendClassRow(html);
                } else {
                    replaceClassRow(oldRow, html);
                }

                bootstrap.Modal.getOrCreateInstance(modal).hide();
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // ---------------------------------------------
    // Xóa lớp học
    // ---------------------------------------------

    function setupClassDeletion() {
        document.addEventListener('click', async function (event) {
            var button = event.target.closest('[data-delete-id]');
            if (!button) return;

            var name = button.dataset.deleteName || 'lớp học này';
            if (!confirm('Xóa ' + name + '? Dữ liệu sẽ không thể khôi phục.')) {
                return;
            }

            try {
                var result = await callApi(pageUrl + '?action=delete&id=' + button.dataset.deleteId, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                removeClassRow(button.closest('tr'));
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // ---------------------------------------------
    // Modal xem chi tiết lớp học
    // ---------------------------------------------

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

    var genders = {
        nam: 'Nam',
        nu: 'Nữ',
        khac: 'Khác'
    };

    // Chuyển ký tự đặc biệt thành mã HTML để tránh lỗi hiển thị.
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

    function statusBadge(status) {
        var item = statusLabels[status] || [status || '—', 'muted'];

        return '<span class="badge-status ' + item[1] + '">' +
            escapeHtml(item[0]) +
            '</span>';
    }

    function infoCard(label, value) {
        return '<div class="col-sm-6 col-lg-3">' +
            '<div class="border rounded-3 p-3 h-100">' +
            '<div class="text-tiny text-muted-2 mb-1">' + label + '</div>' +
            '<div class="fw-semibold">' + value + '</div>' +
            '</div>' +
            '</div>';
    }

    // Vẽ nội dung chi tiết lớp + danh sách học viên.
    function renderClassDetails(elements, data) {
        var classInfo = data.class || {};
        var members = Array.isArray(data.members) ? data.members : [];
        var schedule = (weekdays[classInfo.weekday] || '—') +
            ' · ' + (classInfo.start || '—') +
            ' – ' + (classInfo.end || '—');

        elements.subtitle.textContent =
            'Mã lớp #' + classInfo.id + ' · ' +
            (classInfo.instructor || 'Chưa phân công');

        elements.info.innerHTML = [
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

        elements.memberCount.textContent = members.length + ' học viên';

        if (members.length === 0) {
            elements.memberBody.innerHTML =
                '<tr>' +
                    '<td colspan="6" class="text-center text-muted-2 py-4">' +
                        'Chưa có học viên đăng ký lớp này.' +
                    '</td>' +
                '</tr>';
        } else {
            elements.memberBody.innerHTML = members.map(function (member, index) {
                return '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td class="cell-strong">' +
                        escapeHtml(member.name) +
                        '<div class="cell-sub">' + escapeHtml(genders[member.gender] || '—') + '</div>' +
                    '</td>' +
                    '<td>' + escapeHtml(member.phone || '—') + '</td>' +
                    '<td>' + escapeHtml(member.email || '—') + '</td>' +
                    '<td>' + escapeHtml(member.registered || '—') + '</td>' +
                    '<td>' + statusBadge(member.status) + '</td>' +
                '</tr>';
            }).join('');
        }

        elements.body.classList.remove('d-none');
    }

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
                    pageUrl + '?action=members&id=' + encodeURIComponent(classId),
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

    // Khởi động các chức năng của trang.
    setupClassForm();
    setupClassDeletion();
    setupClassDetails();
})();
