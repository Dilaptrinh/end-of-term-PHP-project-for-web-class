<?php
/**
 * API lịch rảnh / duyệt lịch dạy.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var \App\Core\Auth $auth
 * @var callable $requireAuth
 * @var callable $requireAdmin
 * @var callable $requireInstructor
 */

use App\Controllers\Api\AvailabilityApiController;
use App\Repositories\AvailabilityRepository;
use App\Repositories\InstructorRepository;
use App\Services\AvailabilityService;

$availabilityController = new AvailabilityApiController(
    new AvailabilityService(new AvailabilityRepository($db)),
    $auth,
    new InstructorRepository($db)
);

$router->add('GET',  '/api/availability',              [$availabilityController, 'index'],   [$requireAuth]);
$router->add('POST', '/api/availability',              [$availabilityController, 'store'],   [$requireAuth, $requireInstructor]);
$router->add('POST', '/api/availability/{id}/approve', [$availabilityController, 'approve'], [$requireAuth, $requireAdmin]);
$router->add('POST', '/api/availability/{id}/reject',  [$availabilityController, 'reject'],  [$requireAuth, $requireAdmin]);
$router->add('POST', '/api/availability/{id}/cancel',  [$availabilityController, 'cancel'],  [$requireAuth]);
