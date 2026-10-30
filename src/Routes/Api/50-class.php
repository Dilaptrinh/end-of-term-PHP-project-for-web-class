<?php
/**
 * API quản lý lớp học.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var callable $requireAuth
 * @var callable $requireAdmin
 */

use App\Controllers\Api\ClassApiController;
use App\Repositories\ClassRepository;
use App\Services\ClassService;

$classController = new ClassApiController(new ClassService(new ClassRepository($db)));

$router->add('GET',    '/api/classes',       [$classController, 'index'],   [$requireAuth]);
$router->add('GET',    '/api/classes/{id}',  [$classController, 'show'],    [$requireAuth]);
$router->add('POST',   '/api/classes',       [$classController, 'store'],   [$requireAuth, $requireAdmin]);
$router->add('PUT',    '/api/classes/{id}',  [$classController, 'update'],  [$requireAuth, $requireAdmin]);
$router->add('DELETE', '/api/classes/{id}',  [$classController, 'destroy'], [$requireAuth, $requireAdmin]);
