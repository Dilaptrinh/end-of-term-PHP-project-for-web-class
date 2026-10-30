<?php
/**
 * Route công khai: trang chủ, đăng nhập, đăng ký.
 *
 * File này được public/index.php nạp tự động qua glob().
 * @var \App\Core\Router $router
 */

use App\Core\Page;

$router->add('GET', '/', function (): void {
    Page::public('public/home', [], [
        'title' => 'Phòng tập & lớp học hiện đại',
    ]);
});

$router->add('GET', '/login', function (): void {
    Page::auth('auth/login', [], [
        'title' => 'Đăng nhập',
        'scripts' => ['login.js'],
    ]);
});

$router->add('POST', '/login', function (): void {
    $role = (string) ($_POST['role'] ?? 'customer');
    if (!in_array($role, ['admin', 'instructor', 'customer'], true)) {
        $role = 'customer';
    }
    redirect('/' . $role);
});

$router->add('GET', '/register', function (): void {
    Page::auth('auth/register', [], ['title' => 'Đăng ký hội viên']);
});

$router->add('POST', '/register', function (): void {
    redirect('/customer');
});
