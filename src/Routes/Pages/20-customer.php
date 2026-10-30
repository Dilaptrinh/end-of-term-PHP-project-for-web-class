<?php
/**
 * Route khu vực hội viên.
 *
 * @var \App\Core\Router $router
 */

use App\Core\Page;

$router->add('GET', '/customer', function (): void {
    Page::app('customer', 'customer/dashboard', [], [
        'title' => 'Tổng quan hội viên',
        'active' => 'dashboard',
        'breadcrumbs' => [['Hội viên'], ['Tổng quan']],
    ]);
}, [$customerGuard]);

$router->add('GET', '/customer/class-registration', function (): void {
    Page::app('customer', 'customer/class-registration', [], [
        'title' => 'Đăng ký lớp học',
        'active' => 'class-registration',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Đăng ký lớp học']],
        'scripts' => ['class-registration.js'],
    ]);
}, [$customerGuard]);

$router->add('GET', '/customer/schedule', function (): void {
    Page::app('customer', 'customer/schedule', [], [
        'title' => 'Lịch tập',
        'active' => 'schedule',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Lịch tập']],
    ]);
}, [$customerGuard]);

$router->add('GET', '/customer/history', function (): void {
    Page::app('customer', 'customer/history', [], [
        'title' => 'Lịch sử hoạt động',
        'active' => 'history',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Lịch sử']],
    ]);
}, [$customerGuard]);

$router->add('GET', '/customer/account', function (): void {
    Page::app('customer', 'customer/account', [], [
        'title' => 'Quản lý tài khoản',
        'active' => 'account',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Tài khoản']],
    ]);
}, [$customerGuard]);
