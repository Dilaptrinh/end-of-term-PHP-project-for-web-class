<?php
/**
 * API CRUD giáo viên.
 *
 * File tự khởi tạo Repository → Service → Controller của domain instructor,
 * nên khi thêm endpoint giáo viên chỉ cần sửa file này + các class liên quan.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var \App\Repositories\TaiKhoanRepository $taiKhoanRepo
 * @var callable $requireAuth
 * @var callable $requireAdmin
 */

use App\Controllers\Api\InstructorApiController;
use App\Repositories\InstructorRepository;
use App\Services\InstructorService;

$instructorRepo      = new InstructorRepository($db);
$instructorService   = new InstructorService($instructorRepo, $taiKhoanRepo);
$instructorController = new InstructorApiController($instructorService);

$router->add('GET',    '/api/instructors',       [$instructorController, 'index'],   [$requireAuth]);
$router->add('GET',    '/api/instructors/{id}',  [$instructorController, 'show'],    [$requireAuth]);
$router->add('POST',   '/api/instructors',       [$instructorController, 'store'],   [$requireAuth, $requireAdmin]);
$router->add('PUT',    '/api/instructors/{id}',  [$instructorController, 'update'],  [$requireAuth, $requireAdmin]);
$router->add('DELETE', '/api/instructors/{id}',  [$instructorController, 'destroy'], [$requireAuth, $requireAdmin]);
