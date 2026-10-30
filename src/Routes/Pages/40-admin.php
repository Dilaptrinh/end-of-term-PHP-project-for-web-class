<?php
/**
 * Route khu vực quản trị.
 *
 * @var \App\Core\Router $router
 */

use App\Core\Page;

$router->add('GET', '/admin', function (): void {
    Page::app('admin', 'admin/dashboard', [], [
        'title' => 'Tổng quan hệ thống',
        'active' => 'dashboard',
        'breadcrumbs' => [['Quản trị'], ['Tổng quan']],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/search', function (): void {
    Page::app('admin', 'admin/search', [], [
        'title' => 'Tra cứu toàn hệ thống',
        'active' => 'search',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Tra cứu']],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/classes', function (): void {
    Page::app('admin', 'admin/classes', [], [
        'title' => 'Quản lý lớp học',
        'active' => 'classes',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Lớp học']],
        'scripts' => ['admin-classes.js'],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/reports', function (): void {
    Page::app('admin', 'admin/reports', [], [
        'title' => 'Thống kê & báo cáo',
        'active' => 'reports',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Thống kê']],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/members', function (): void {
    Page::app('admin', 'admin/members', [], [
        'title' => 'Quản lý hội viên',
        'active' => 'members',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Hội viên']],
        'scripts' => ['admin-members.js'],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/instructors', function (): void {
    Page::app('admin', 'admin/instructors', [], [
        'title' => 'Quản lý giáo viên',
        'active' => 'instructors',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Giáo viên']],
        'scripts' => ['admin-instructors.js'],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/packages', function (): void {
    Page::app('admin', 'admin/packages', [], [
        'title' => 'Quản lý gói tập',
        'active' => 'packages',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Gói tập']],
        'scripts' => ['admin-packages.js'],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/registrations', function (): void {
    Page::app('admin', 'admin/registrations', [], [
        'title' => 'Quản lý đăng ký',
        'active' => 'registrations',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Đăng ký']],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/availability', function (): void {
    Page::app('admin', 'admin/availability', [], [
        'title' => 'Duyệt lịch dạy',
        'active' => 'availability',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Duyệt lịch dạy']],
        'scripts' => ['admin-availability.js'],
    ]);
}, [$adminGuard]);

$router->add('GET', '/admin/accounts', function (): void {
    Page::app('admin', 'admin/accounts', [], [
        'title' => 'Quản lý tài khoản',
        'active' => 'accounts',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Tài khoản']],
    ]);
}, [$adminGuard]);
