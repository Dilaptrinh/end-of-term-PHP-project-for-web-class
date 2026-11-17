/*
 * FILE: common/assets/js/admin/classes-templates.js
 *
 * Chỉ chứa HTML template của trang Quản lý lớp học.
 * classes.js dùng window.ClassTemplates để render.
 */
(function () {
    'use strict';

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

        return '<span class="badge-status ' + item[1] + '">' + escapeHtml(item[0]) + '</span>';
    }

    // HTML một dòng lớp trong bảng.
    function row(item, editUrl) {
        return '<tr data-row data-class-row="' + Number(item.id) + '">' +
            '<td class="cell-strong">' + escapeHtml(item.name) + '<div class="cell-sub">Mã lớp: ' + Number(item.id) + ' · ' + escapeHtml(item.level) + '</div></td>' +
            '<td>' + escapeHtml(item.instructor) + '</td>' +
            '<td>' + escapeHtml(weekdays[item.weekday] || '—') + '<div class="cell-sub">' + escapeHtml(item.start + ' – ' + item.end) + '</div></td>' +
            '<td>' + escapeHtml(item.room) + '</td>' +
            '<td>' + escapeHtml(String(item.enrolled) + '/' + String(item.capacity)) + '</td>' +
            '<td class="text-muted-2 text-tiny">' + escapeHtml(item.from) + '<br>' + escapeHtml(item.to) + '</td>' +
            '<td>' + statusBadge(item.status) + '</td>' +
            '<td class="cell-actions">' +
                '<div class="dropdown">' +
                    '<button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Thao tác"><i class="bi bi-three-dots"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#classDetailModal"' +
                            ' data-class-id="' + Number(item.id) + '"' +
                            ' data-class-name="' + escapeHtml(item.name) + '"><i class="bi bi-info-circle"></i> Xem chi tiết</button></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><a class="dropdown-item" href="' + escapeHtml(editUrl || '') + '" data-bs-toggle="modal" data-bs-target="#classFormModal"' +
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
                            ' data-class-desc="' + escapeHtml(item.desc) + '"><i class="bi bi-pencil"></i> Sửa lớp</a></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><button class="dropdown-item text-danger" type="button"' +
                            ' data-delete-id="' + Number(item.id) + '"' +
                            ' data-delete-name="' + escapeHtml(item.name) + '"><i class="bi bi-trash3"></i> Xóa lớp</button></li>' +
                    '</ul>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }

    function infoCard(label, value) {
        return '<div class="col-sm-6 col-lg-3">' +
            '<div class="border rounded-3 p-3 h-100">' +
                '<div class="text-tiny text-muted-2 mb-1">' + label + '</div>' +
                '<div class="fw-semibold">' + value + '</div>' +
            '</div>' +
        '</div>';
    }

    // HTML phần thông tin lớp trong modal chi tiết.
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

    // HTML danh sách học viên trong modal chi tiết.
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

    function memberCount(count) {
        return count + ' học viên';
    }

    function emptyRow() {
        return '<tr data-empty-row><td colspan="8" class="text-center text-muted-2 py-4">Chưa có lớp học nào.</td></tr>';
    }

    window.ClassTemplates = {
        row: row,
        detail: detail,
        memberRows: memberRows,
        memberCount: memberCount,
        emptyRow: emptyRow
    };
})();
