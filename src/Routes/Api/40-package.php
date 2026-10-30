<?php
/**
 * API quản lý gói tập.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var callable $requireAuth
 * @var callable $requireAdmin
 */

use App\Controllers\Api\PackageApiController;
use App\Repositories\PackageRepository;
use App\Services\PackageService;

$packageController = new PackageApiController(new PackageService(new PackageRepository($db)));

$router->add('GET',    '/api/packages',       [$packageController, 'index'],   [$requireAuth]);
$router->add('GET',    '/api/packages/{id}',  [$packageController, 'show'],    [$requireAuth]);
$router->add('POST',   '/api/packages',       [$packageController, 'store'],   [$requireAuth, $requireAdmin]);
$router->add('PUT',    '/api/packages/{id}',  [$packageController, 'update'],  [$requireAuth, $requireAdmin]);
$router->add('DELETE', '/api/packages/{id}',  [$packageController, 'destroy'], [$requireAuth, $requireAdmin]);
