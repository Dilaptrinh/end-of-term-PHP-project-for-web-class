(function () {
    'use strict';

    // Dùng cho trang admin/account.php ("Tài khoản của tôi").
    var endpoint = window.location.pathname;

    // ---------------------------------------------
    // Cập nhật họ tên admin
    // ---------------------------------------------

    var profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            var name = document.getElementById('admin-name').value.trim();

            // Tạo dữ liệu gửi lên server.
            var formData = new FormData();
            formData.append('ho_ten', name);

            try {
                var response = await fetch(endpoint + '?action=profile', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                var result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra.');
                }

                // Cập nhật tên trên giao diện, không reload.
                document.getElementById('account-name').textContent = name;
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    // ---------------------------------------------
    // Đổi mật khẩu
    // ---------------------------------------------

    var passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            var oldPassword = document.getElementById('pw-current').value;
            var newPassword = document.getElementById('pw-new').value;
            var confirmPassword = document.getElementById('pw-confirm').value;

            // Append từng trường cho dễ đọc.
            var formData = new FormData();
            formData.append('mat_khau_cu', oldPassword);
            formData.append('mat_khau_moi', newPassword);
            formData.append('mat_khau_xac_nhan', confirmPassword);

            try {
                var response = await fetch(endpoint + '?action=password', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });

                var result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Có lỗi xảy ra.');
                }

                passwordForm.reset();
                alert(result.message);
            } catch (error) {
                alert(error.message);
            }
        });
    }
})();
