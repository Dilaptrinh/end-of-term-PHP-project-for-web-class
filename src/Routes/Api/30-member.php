<?php
/**
 * API quản lý hội viên.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var callable $requireAuth
 * @var callable $requireAdmin
 */

use App\Controllers\Api\MemberApiController;
use App\Repositories\MemberRepository;
use App\Services\MemberService;

$memberController = new MemberApiController(new MemberService(new MemberRepository($db)));

$router->add('GET',    '/api/members',       [$memberController, 'index'],   [$requireAuth]);
$router->add('GET',    '/api/members/{id}',  [$memberController, 'show'],    [$requireAuth]);
$router->add('POST',   '/api/members',       [$memberController, 'store'],   [$requireAuth, $requireAdmin]);
$router->add('PUT',    '/api/members/{id}',  [$memberController, 'update'],  [$requireAuth, $requireAdmin]);
$router->add('DELETE', '/api/members/{id}',  [$memberController, 'destroy'], [$requireAuth, $requireAdmin]);
