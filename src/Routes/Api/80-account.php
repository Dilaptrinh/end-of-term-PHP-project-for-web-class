<?php
/**
 * API đăng ký tài khoản + hồ sơ + mật khẩu.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var \App\Core\Auth $auth
 * @var \App\Repositories\TaiKhoanRepository $taiKhoanRepo
 * @var callable $requireAuth
 */

use App\Controllers\Api\AccountApiController;
use App\Repositories\InstructorRepository;
use App\Repositories\MemberRepository;
use App\Services\AccountService;

$accountController = new AccountApiController(
    new AccountService($taiKhoanRepo, new MemberRepository($db), new InstructorRepository($db)),
    $auth
);

$router->add('POST', '/api/register',         [$accountController, 'register']);
$router->add('PUT',  '/api/profile',          [$accountController, 'updateProfile'],  [$requireAuth]);
$router->add('POST', '/api/profile/password', [$accountController, 'changePassword'], [$requireAuth]);
