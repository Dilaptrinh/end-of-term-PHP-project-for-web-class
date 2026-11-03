(function () {
    'use strict';

    const form = document.getElementById('instructor-form');
    const modalElement = document.getElementById('instructorFormModal');
    if (!form || !modalElement || !window.api) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const idInput = document.getElementById('if-id');
    const title = document.getElementById('instructorFormTitle');
    const username = document.getElementById('if-username');
    const password = document.getElementById('if-password');
    const fields = {
        ho_ten: document.getElementById('if-name'),
        sdt: document.getElementById('if-phone'),
        email: document.getElementById('if-email'),
        chuyen_mon: document.getElementById('if-specialty'),
        gioi_thieu: document.getElementById('if-desc')
    };

    function resetForm() {
        form.reset();
        idInput.value = '';
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
        fields.chuyen_mon.value = instructor.chuyen_mon || '';
        fields.gioi_thieu.value = instructor.gioi_thieu || '';
        username.value = instructor.ten_dang_nhap || '';
        username.readOnly = true;
        password.value = '';
        password.required = false;
        password.placeholder = 'Để trống nếu không đổi';
        title.textContent = 'Sửa giáo viên';
    }

    document.querySelector('[data-bs-target="#instructorFormModal"]')?.addEventListener('click', resetForm);

    document.addEventListener('click', async function (event) {
        const editButton = event.target.closest('[data-instructor-edit]');
        const deleteButton = event.target.closest('[data-instructor-delete]');

        if (editButton) {
            try {
                const response = await window.api.get('/api/instructors/' + editButton.dataset.instructorEdit);
                fillForm(response.data);
                modal.show();
            } catch (error) {
                window.showToast('Không thể tải dữ liệu', error.message, 'danger');
            }
        }

        if (deleteButton) {
            if (!window.confirm('Xóa giáo viên này?')) return;
            try {
                await window.api.delete('/api/instructors/' + deleteButton.dataset.instructorDelete);
                window.location.reload();
            } catch (error) {
                window.showToast('Không thể xóa giáo viên', error.message, 'danger');
            }
        }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const payload = {
            ho_ten: fields.ho_ten.value.trim(),
            sdt: fields.sdt.value.trim(),
            email: fields.email.value.trim(),
            chuyen_mon: fields.chuyen_mon.value.trim(),
            gioi_thieu: fields.gioi_thieu.value.trim(),
            trang_thai: 'dang_lam'
        };
        const id = idInput.value.trim();

        try {
            if (id) {
                if (password.value) payload.mat_khau = password.value;
                await window.api.put('/api/instructors/' + id, payload);
            } else {
                payload.ten_dang_nhap = username.value.trim();
                payload.mat_khau = password.value;
                await window.api.post('/api/instructors', payload);
            }
            modal.hide();
            window.location.reload();
        } catch (error) {
            const details = error.data && error.data.errors
                ? Object.values(error.data.errors).join(' ')
                : error.message;
            window.showToast('Không thể lưu giáo viên', details, 'danger');
        }
    });
})();
