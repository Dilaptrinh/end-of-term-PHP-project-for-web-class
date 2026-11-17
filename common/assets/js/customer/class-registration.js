/*
 * FILE: common/assets/js/customer/class-registration.js
 * Trang dùng: customer/class-registration.php
 *
 * - Đổ dữ liệu lớp vào modal khi bấm Đăng ký.
 * - Lọc "Chỉ lớp còn chỗ" (client-side, không gọi server).
 * - Đăng ký lớp bằng AJAX và cập nhật dữ liệu do máy chủ trả về.
 */
(function () {
    'use strict';

    var endpoint = window.location.pathname;

    var modal = document.getElementById('registerClassModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;
            modal.querySelector('[data-modal-class-name]').textContent = trigger.dataset.className || '';
            modal.querySelector('[data-modal-class-meta]').textContent = trigger.dataset.classMeta || '';
            modal.querySelector('[data-modal-class-teacher]').textContent = trigger.dataset.classTeacher || '';
            var idInput = document.getElementById('register-class-id');
            if (idInput) idInput.value = trigger.dataset.classId || '';
        });
    }

    var onlyAvailable = document.getElementById('only-available');
    if (onlyAvailable) {
        onlyAvailable.addEventListener('change', function () {
            document.querySelectorAll('[data-class-card]').forEach(function (card) {
                card.hidden = onlyAvailable.checked && card.dataset.available === '0';
            });
        });
    }

    function updateRegisteredCard(data) {
        var card = document.querySelector('[data-class-card][data-class-id="' + data.class_id + '"]');
        if (!card) return;

        var enrolledBox = card.querySelector('[data-enrolled]');
        if (enrolledBox) {
            enrolledBox.textContent = data.enrolled + '/' + data.capacity;
        }

        var availableBox = card.querySelector('[data-available-text]');
        if (availableBox) {
            availableBox.textContent = data.availability_text;
        }

        var availableLabel = card.querySelector('[data-available-label]');
        if (availableLabel) {
            availableLabel.textContent = data.availability_label;
            availableLabel.className = 'availability ' + data.availability_class;
        }

        var action = card.querySelector('[data-class-action]');
        if (action) {
            action.innerHTML =
                '<span class="availability text-success">Đã đăng ký</span>' +
                '<button class="btn btn-outline-secondary btn-sm" type="button" disabled>Đã đăng ký</button>';
        }

        card.dataset.available = data.is_full ? '0' : '1';
        if (onlyAvailable && onlyAvailable.checked && data.is_full) {
            card.hidden = true;
        }
    }

    var form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        var classId = document.getElementById('register-class-id').value;
        var formData = new FormData();
        formData.append('lop_hoc_id', classId);

        try {
            var res = await fetch(endpoint + '?action=register', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin'
            });
            var responseText = await res.text();
            var json;
            try {
                json = JSON.parse(responseText);
            } catch (parseError) {
                throw new Error('Phản hồi không hợp lệ từ máy chủ: ' + responseText.slice(0, 200));
            }

            if (!res.ok || !json.success) {
                throw new Error(json.message || 'Có lỗi xảy ra.');
            }

            updateRegisteredCard(json);
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).hide();
            }
            alert(json.message);
        } catch (error) {
            alert(error.message);
        }
    });
})();
