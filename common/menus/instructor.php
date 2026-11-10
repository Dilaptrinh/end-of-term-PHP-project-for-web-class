<?php
return [
    ['label' => 'Tổng quan', 'icon' => 'grid-1x2', 'href' => '/instructor', 'key' => 'dashboard'],
    ['group' => 'Giảng dạy'],
    ['label' => 'Lịch dạy', 'icon' => 'calendar-week', 'href' => '/instructor/schedule', 'key' => 'schedule'],
    ['label' => 'Lịch sử đã dạy', 'icon' => 'clock-history', 'href' => '/instructor/history', 'key' => 'history'],
    ['label' => 'Đề xuất lớp học', 'icon' => 'calendar-plus', 'href' => '/instructor/availability', 'key' => 'availability'],
    ['label' => 'Lớp của tôi', 'icon' => 'journal-bookmark', 'href' => '/instructor/classes', 'key' => 'classes'],
    ['label' => 'Tra cứu học viên', 'icon' => 'person-lines-fill', 'href' => '/instructor/search', 'key' => 'search'],
    ['group' => 'Tài khoản'],
    ['label' => 'Quản lý tài khoản', 'icon' => 'person-gear', 'href' => '/instructor/account', 'key' => 'account'],
];
