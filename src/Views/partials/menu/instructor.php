<?php
/**
 * Menu khu vực giáo viên.
 * Trả về mảng item cho app_menu('instructor').
 */

return [
    ['label' => 'Tổng quan', 'icon' => 'grid-1x2', 'href' => '/instructor', 'key' => 'dashboard'],
    ['group' => 'Giảng dạy'],
    ['label' => 'Lịch dạy', 'icon' => 'calendar-week', 'href' => '/instructor/schedule', 'key' => 'schedule'],
    ['label' => 'Đăng ký lịch dạy', 'icon' => 'calendar-plus', 'href' => '/instructor/availability', 'key' => 'availability'],
    ['label' => 'Lớp của tôi', 'icon' => 'journal-bookmark', 'href' => '/instructor/classes', 'key' => 'classes'],
    ['label' => 'Tra cứu học viên', 'icon' => 'person-lines-fill', 'href' => '/instructor/search', 'key' => 'search'],
    ['group' => 'Tài khoản'],
    ['label' => 'Quản lý tài khoản', 'icon' => 'person-gear', 'href' => '/instructor/account', 'key' => 'account'],
];
