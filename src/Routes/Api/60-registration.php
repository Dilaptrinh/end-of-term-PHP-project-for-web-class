<?php
/**
 * API đăng ký gói / lớp.
 *
 * @var \App\Core\Router $router
 * @var \mysqli $db
 * @var \App\Core\Auth $auth
 * @var callable $requireAuth
 * @var callable $requireAdmin
 * @var callable $requireCustomer
 */

use App\Controllers\Api\RegistrationApiController;
use App\Repositories\ClassRepository;
use App\Repositories\MemberRepository;
use App\Repositories\PackageRepository;
use App\Repositories\RegistrationRepository;
use App\Services\RegistrationService;

$registrationController = new RegistrationApiController(
    new RegistrationService(new RegistrationRepository($db), new PackageRepository($db), new ClassRepository($db)),
    $auth,
    new MemberRepository($db)
);

$router->add('GET',  '/api/registrations/packages',              [$registrationController, 'indexPackages'], [$requireAuth, $requireAdmin]);
$router->add('GET',  '/api/registrations/classes',               [$registrationController, 'indexClasses'],  [$requireAuth, $requireAdmin]);
$router->add('POST', '/api/registrations/packages',              [$registrationController, 'storePackage'],  [$requireAuth, $requireAdmin]);
$router->add('POST', '/api/registrations/packages/{id}/confirm', [$registrationController, 'confirmPackage'], [$requireAuth, $requireAdmin]);
$router->add('POST', '/api/registrations/packages/{id}/cancel',  [$registrationController, 'cancelPackage'],  [$requireAuth, $requireAdmin]);
$router->add('POST', '/api/registrations/classes',               [$registrationController, 'storeClass'],    [$requireAuth, $requireCustomer]);
$router->add('POST', '/api/registrations/classes/{id}/cancel',   [$registrationController, 'cancelClass'],   [$requireAuth]);
