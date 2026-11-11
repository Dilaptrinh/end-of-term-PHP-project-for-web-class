(function () {
    'use strict';

    var endpoint = window.location.pathname;

    async function send(action, data) {
        var formData = new FormData();
        Object.keys(data).forEach(function (key) {
            formData.append(key, data[key]);
        });

        var res = await fetch(endpoint + '?action=' + action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData,
            credentials: 'same-origin'
        });
        var json = await res.json().catch(function () {
            return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
        });
        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Có lỗi xảy ra.');
        }
        return json;
    }

    var specialtyPicker = document.getElementById('ins-specialty-picker');
    var specialtyInputs = Array.from(specialtyPicker.querySelectorAll('input[name="chuyen_mon_ids[]"]'));
    var specialtySummary = document.getElementById('ins-specialty-summary');

    function selectedSpecialties() {
        return specialtyInputs.filter(function (input) { return input.checked; });
    }

    function updateSpecialtySummary() {
        specialtySummary.textContent = selectedSpecialties()
            .map(function (input) { return input.nextElementSibling.textContent; })
            .join(' · ') || '—';
    }

    specialtyPicker.addEventListener('change', updateSpecialtySummary);
    document.getElementById('profile-form').addEventListener('reset', function () {
        window.setTimeout(updateSpecialtySummary, 0);
    });

    document.getElementById('profile-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var data = {
            ho_ten: document.getElementById('ins-name').value.trim(),
            sdt: document.getElementById('ins-phone').value.trim(),
            email: document.getElementById('ins-email').value.trim(),
            chuyen_mon_ids: selectedSpecialties().map(function (input) {
                return input.value;
            })
        };

        try {
            var json = await send('profile', data);
            document.getElementById('account-name').textContent = data.ho_ten;
            var specialty = selectedSpecialties().map(function (input) {
                return input.nextElementSibling.textContent;
            }).join(' · ') || '—';
            document.querySelector('#account-specialty-badge .badge-status').textContent = specialty;
            document.getElementById('account-specialty-value').textContent = specialty;
            alert(json.message);
        } catch (error) {
            alert(error.message);
        }
    });

    document.getElementById('password-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        var form = event.currentTarget;
        var data = {
            mat_khau_cu: document.getElementById('ins-pw-current').value,
            mat_khau_moi: document.getElementById('ins-pw-new').value,
            mat_khau_xac_nhan: document.getElementById('ins-pw-confirm').value
        };

        try {
            var json = await send('password', data);
            form.reset();
            alert(json.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
