<?php
/**
 * Menu khu vực hội viên.
 * Trả về mảng item cho app_menu('customer').
 */

return [
    ['label' => 'Tổng quan', 'icon' => 'grid-1x2', 'href' => '/customer', 'key' => 'dashboard'],
    ['group' => 'Luyện tập'],
    ['label' => 'Đăng ký lớp học', 'icon' => 'journal-plus', 'href' => '/customer/class-registration', 'key' => 'class-registration'],
    ['label' => 'Lịch tập', 'icon' => 'calendar-week', 'href' => '/customer/schedule', 'key' => 'schedule'],
    ['label' => 'Lịch sử', 'icon' => 'clock-history', 'href' => '/customer/history', 'key' => 'history'],
    ['group' => 'Tài khoản'],
    ['label' => 'Quản lý tài khoản', 'icon' => 'person-gear', 'href' => '/customer/account', 'key' => 'account'],
];
