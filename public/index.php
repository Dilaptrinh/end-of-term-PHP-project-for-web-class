<?php
/**
 * public/index.php
 * ------------------------------------------------------------------
 * FRONT CONTROLLER — MỘT CỬA DUY NHẤT cho mọi request.
 */

require __DIR__ . '/../src/bootstrap.php';

use App\Core\Router;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Controllers\UserController;

// 1) Kết nối MySQL (một lần)
$db = require __DIR__ . '/../mysql/connect_mysql.php';

// 2) Dựng chuỗi phụ thuộc thủ công
$userRepo       = new UserRepository($db);
$userService    = new UserService($userRepo);
$userController = new UserController($userService);

// 3) Router + routes
$router = new Router();

$router->add('GET', '/', function () {
    echo '<h1>Trang chủ</h1>';
    echo '<a href="' . url('/users') . '">Xem danh sách User</a>';
});

// ---- CRUD User ----
$router->add('GET',  '/users',             [$userController, 'index']);
$router->add('GET',  '/users/create',      [$userController, 'create']);
$router->add('POST', '/users/create',      [$userController, 'store']);
$router->add('GET',  '/users/edit/{id}',   [$userController, 'edit']);
$router->add('POST', '/users/edit/{id}',   [$userController, 'update']);
$router->add('POST', '/users/delete/{id}', [$userController, 'destroy']);

// 4) Chạy
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
