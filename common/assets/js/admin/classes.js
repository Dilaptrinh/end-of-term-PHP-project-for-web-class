(function () {
    'use strict';

    // Địa chỉ trang hiện tại: /fitness/admin/classes
    var pageUrl = window.location.pathname;
    var classesBody = document.getElementById('classes-body');
    var tpl = window.ClassTemplates;

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

        classesBody.innerHTML = tpl.emptyRow();
    }

    // Thêm lớp mới vào cuối bảng.
    function appendClassRow(html) {
        if (!classesBody || !html) return;

        var emptyRow = classesBody.querySelector('[data-empty-row]');
        if (emptyRow) emptyRow.remove();

        classesBody.insertAdjacentHTML('beforeend', html);
    }

    // Thay dòng lớp cũ bằng dòng mới.
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

    // Form thêm / sửa lớp học

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

        // Lớp cũ có thể ở trạng thái đề xuất (đã bỏ luồng đó); đưa về đang mở.
        var status = button.dataset.classStatus || defaults.status;
        if (['dang_mo', 'da_dong', 'da_huy'].indexOf(status) === -1) {
            status = 'dang_mo';
        }
        document.getElementById('cf-status').value = status;

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

                if (!result.data) {
                    throw new Error('Không tìm thấy lớp học.');
                }

                if (id === '0') {
                    appendClassRow(tpl.row(result.data, pageUrl));
                } else {
                    replaceClassRow(oldRow, tpl.row(result.data, pageUrl));
                }

                bootstrap.Modal.getOrCreateInstance(modal).hide();
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // Xóa lớp học

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

                var result = await callApi(pageUrl + '?action=delete&id=' + button.dataset.deleteId, {
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

    // Modal xem chi tiết lớp học

    function renderClassDetails(elements, data) {
        var classInfo = data.class || {};
        var members = Array.isArray(data.members) ? data.members : [];

        elements.subtitle.textContent =
            'Mã lớp #' + classInfo.id + ' · ' +
            (classInfo.instructor || 'Chưa phân công');

        elements.info.innerHTML = tpl.detail(classInfo);
        elements.memberCount.textContent = tpl.memberCount(members.length);
        elements.memberBody.innerHTML = tpl.memberRows(members);
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

    // Render bảng ban đầu từ JSON PHP in ra.
    var initial = document.getElementById('class-init');
    if (initial && classesBody) {
        var rows = JSON.parse(initial.textContent || '[]');

        classesBody.innerHTML = rows.map(function (row) {
            return tpl.row(row, pageUrl);
        }).join('');

        if (!rows.length) {
            showEmptyRow();
        }
    }

    // Khởi động các chức năng của trang.
    setupClassForm();
    setupClassDeletion();
    setupClassDetails();
})();
