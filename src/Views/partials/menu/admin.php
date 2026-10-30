<?php
/**
 * Menu khu vực quản trị.
 * Trả về mảng item cho app_menu('admin').
 */

return [
    ['label' => 'Tổng quan', 'icon' => 'grid-1x2', 'href' => '/admin', 'key' => 'dashboard'],
    ['group' => 'Quản trị'],
    ['label' => 'Hội viên', 'icon' => 'people', 'href' => '/admin/members', 'key' => 'members'],
    ['label' => 'Giáo viên', 'icon' => 'person-badge', 'href' => '/admin/instructors', 'key' => 'instructors'],
    ['label' => 'Gói tập', 'icon' => 'box-seam', 'href' => '/admin/packages', 'key' => 'packages'],
    ['label' => 'Lớp học', 'icon' => 'calendar2-week', 'href' => '/admin/classes', 'key' => 'classes'],
    ['label' => 'Đăng ký', 'icon' => 'receipt', 'href' => '/admin/registrations', 'key' => 'registrations'],
    ['label' => 'Duyệt lịch dạy', 'icon' => 'calendar-check', 'href' => '/admin/availability', 'key' => 'availability'],
    ['label' => 'Tài khoản', 'icon' => 'person-gear', 'href' => '/admin/accounts', 'key' => 'accounts'],
    ['group' => 'Phân tích'],
    ['label' => 'Thống kê', 'icon' => 'bar-chart-line', 'href' => '/admin/reports', 'key' => 'reports'],
    ['label' => 'Tra cứu', 'icon' => 'search', 'href' => '/admin/search', 'key' => 'search'],
];
