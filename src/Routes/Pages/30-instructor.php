<?php
/**
 * Route khu vực giáo viên.
 *
 * @var \App\Core\Router $router
 */

use App\Core\Page;

$router->add('GET', '/instructor', function (): void {
    Page::app('instructor', 'instructor/dashboard', [], [
        'title' => 'Tổng quan giáo viên',
        'active' => 'dashboard',
        'breadcrumbs' => [['Giáo viên'], ['Tổng quan']],
    ]);
}, [$instructorGuard]);

$router->add('GET', '/instructor/schedule', function (): void {
    Page::app('instructor', 'instructor/schedule', [], [
        'title' => 'Lịch dạy',
        'active' => 'schedule',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Lịch dạy']],
    ]);
}, [$instructorGuard]);

$router->add('GET', '/instructor/availability', function (): void {
    Page::app('instructor', 'instructor/availability', [], [
        'title' => 'Đăng ký lịch dạy',
        'active' => 'availability',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Đăng ký lịch dạy']],
    ]);
}, [$instructorGuard]);

$router->add('GET', '/instructor/classes', function (): void {
    Page::app('instructor', 'instructor/classes', [], [
        'title' => 'Lớp tôi đang dạy',
        'active' => 'classes',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Lớp học']],
        'scripts' => ['instructor-classes.js'],
    ]);
}, [$instructorGuard]);

$router->add('GET', '/instructor/search', function (): void {
    Page::app('instructor', 'instructor/search', [], [
        'title' => 'Tra cứu học viên',
        'active' => 'search',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Tra cứu']],
    ]);
}, [$instructorGuard]);

$router->add('GET', '/instructor/account', function (): void {
    Page::app('instructor', 'instructor/account', [], [
        'title' => 'Quản lý tài khoản',
        'active' => 'account',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Tài khoản']],
    ]);
}, [$instructorGuard]);
