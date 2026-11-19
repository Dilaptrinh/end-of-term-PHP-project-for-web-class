
(function () {
    'use strict';

    var endpoint = window.location.pathname;

    // Đổi nút của gói sang trạng thái chờ thanh toán.
    function markPending(packageId) {
        var card = document.querySelector('[data-package-card][data-package-id="' + packageId + '"]');
        if (!card) return;

        var action = card.querySelector('[data-package-action]');
        if (!action) return;

        action.innerHTML =
            '<span class="badge-status warning">Chờ thanh toán</span>' +
            '<button class="btn btn-outline-secondary w-100 mt-2" type="button" disabled>Chờ thanh toán</button>';
    }

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-package-register], [data-package-upgrade]');
        if (!button) return;

        var isUpgrade = button.hasAttribute('data-package-upgrade');
        var packageId = button.dataset.packageRegister || button.dataset.packageUpgrade;
        var message = isUpgrade
            ? 'Nâng cấp lên "' + button.dataset.packageName + '"?\n'
                + 'Giữ hạn đến ' + button.dataset.packageEnd + ', còn ' + button.dataset.packageRemaining + ' ngày.\n'
                + 'Khấu trừ ' + Number(button.dataset.packageCredit || 0).toLocaleString('vi-VN') + 'đ, '
                + 'phí nâng cấp ' + Number(button.dataset.packageFee || 0).toLocaleString('vi-VN') + 'đ.'
            : 'Đăng ký gói "' + button.dataset.packageName + '"?';

        if (!confirm(message)) return;

        button.disabled = true;

        try {
            var formData = new FormData();
            formData.append('goi_tap_id', packageId);

            var response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });
            var result = await response.json().catch(function () {
                return { success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' };
            });

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể đăng ký gói tập.');
            }

            markPending(result.goi_tap_id || packageId);
            alert(result.message);
        } catch (error) {
            alert(error.message);
        } finally {
            button.disabled = false;
        }
    });
})();
