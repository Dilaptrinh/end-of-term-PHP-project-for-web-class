(function () {
    'use strict';

    var endpoint = window.location.pathname;
    var form = document.getElementById('instructor-form');
    var modalElement = document.getElementById('instructorFormModal');
    if (!form || !modalElement) return;

    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    var idInput = document.getElementById('if-id');
    var title = document.getElementById('instructorFormTitle');
    var username = document.getElementById('if-username');
    var password = document.getElementById('if-password');
    var specialtyInputs = document.querySelectorAll('#if-specialties input[name="chuyen_mon_ids[]"]');
    var fields = {
        ho_ten: document.getElementById('if-name'),
        sdt: document.getElementById('if-phone'),
        email: document.getElementById('if-email'),
        mo_ta: document.getElementById('if-desc')
    };

    function resetForm() {
        form.reset();
        idInput.value = '';
        specialtyInputs.forEach(function (input) { input.checked = false; });
        title.textContent = 'Thêm giáo viên';
        username.readOnly = false;
        password.required = true;
        password.placeholder = 'Tối thiểu 6 ký tự';
    }

    function fillForm(instructor) {
        idInput.value = instructor.id;
        fields.ho_ten.value = instructor.ho_ten || '';
        fields.sdt.value = instructor.sdt || '';
        fields.email.value = instructor.email || '';
        var selectedSpecialties = String(instructor.chuyen_mon_ids || '').split(',');
        specialtyInputs.forEach(function (input) {
            input.checked = selectedSpecialties.indexOf(input.value) !== -1;
        });
        fields.mo_ta.value = instructor.gioi_thieu || '';
        username.value = instructor.ten_dang_nhap || '';
        username.readOnly = true;
        password.value = '';
        password.required = false;
        password.placeholder = 'Để trống nếu không đổi';
        title.textContent = 'Sửa giáo viên';
    }

    var addButton = document.querySelector('[data-bs-target="#instructorFormModal"]');
    if (addButton) addButton.addEventListener('click', resetForm);

    document.addEventListener('click', async function (event) {
        var editButton = event.target.closest('[data-instructor-edit]');
        if (editButton) {
            try {
                var res = await fetch(endpoint + '?action=detail&id=' + editButton.dataset.instructorEdit, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                var json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
                fillForm(json.data);
                modal.show();
            } catch (error) {
                alert(error.message);
            }
            return;
        }

        var deleteButton = event.target.closest('[data-instructor-delete]');
        if (!deleteButton) return;

        if (!confirm('Xóa giáo viên này?')) return;

        try {
            var res2 = await fetch(endpoint + '?action=delete&id=' + deleteButton.dataset.instructorDelete, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            var json2 = await res2.json();
            if (!res2.ok || !json2.success) throw new Error(json2.message || 'Có lỗi xảy ra.');
            alert(json2.message);
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var data = {
            ho_ten: fields.ho_ten.value.trim(),
            sdt: fields.sdt.value.trim(),
            email: fields.email.value.trim(),
            chuyen_mon_ids: Array.from(document.querySelectorAll('#if-specialties input[name="chuyen_mon_ids[]"]:checked')).map(function (input) {
                return input.value;
            }),
            mo_ta: fields.mo_ta.value.trim(),
            trang_thai: 'dang_lam'
        };
        var id = idInput.value.trim();

        if (id) {
            if (password.value) data.mat_khau = password.value;
        } else {
            data.ten_dang_nhap = username.value.trim();
            data.mat_khau = password.value;
        }

        try {
            var res = await fetch(endpoint + '?action=save&id=' + (id || '0'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });
            var json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || 'Có lỗi xảy ra.');
            alert(json.message);
            modal.hide();
            location.reload();
        } catch (error) {
            alert(error.message);
        }
    });
})();
