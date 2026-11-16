(function () {
    'use strict';

    document.addEventListener('click', async function (event) {
        var button = event.target.closest('[data-proposal-action]');
        if (!button) return;

        var action = button.dataset.proposalAction;
        var room = '';
        var requiredPackage = 'basic';

        if (action === 'approve') {
            room = window.prompt('Nhập phòng học trước khi duyệt:', button.dataset.proposalRoom || '');
            if (room === null || room.trim() === '') {
                alert('Vui lòng nhập phòng học để duyệt đề xuất.');
                return;
            }

            requiredPackage = window.prompt(
                'Gói tối thiểu (basic, standard hoặc premium):',
                button.dataset.proposalPackage || 'basic'
            );
            if (requiredPackage === null || !['basic', 'standard', 'premium'].includes(requiredPackage.trim().toLowerCase())) {
                alert('Vui lòng nhập đúng gói tối thiểu.');
                return;
            }
        }

        if (!confirm(action === 'approve' ? 'Duyệt đề xuất lớp này?' : 'Từ chối đề xuất lớp này?')) {
            return;
        }

        try {
            var formData = new FormData();
            if (action === 'approve') {
                formData.append('phong_hoc', room.trim());
                formData.append('goi_tap_toi_thieu', requiredPackage.trim().toLowerCase());
            }

            var response = await fetch(window.location.pathname + '?action=' + action + '&id=' + button.dataset.proposalId, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });
            var result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Không thể cập nhật đề xuất.');
            }

            // Xóa dòng vừa xử lý khỏi bảng.
            var row = button.closest('tr');
            if (row) row.remove();

            // Nếu bảng không còn dòng nào thì hiện thông báo rỗng.
            var body = document.getElementById('proposals-body');
            if (body && !body.querySelector('[data-proposal-row]')) {
                body.innerHTML = '<tr data-empty-row><td colspan="6" class="text-center text-muted-2 py-4">Không có đề xuất đang chờ duyệt.</td></tr>';
            }

            // Cập nhật số đề xuất còn lại.
            var count = document.getElementById('proposals-count');
            if (count && result.remaining !== undefined) {
                count.textContent = result.remaining + ' đề xuất';
            }
            alert(result.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
