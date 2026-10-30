<?php

use App\Controllers\Api\AdminApiController;
use App\Repositories\ClassRepository;
use App\Repositories\MemberRepository;
use App\Repositories\PackageRepository;
use App\Repositories\RegistrationRepository;
use App\Services\ClassService;
use App\Services\MemberService;
use App\Services\PackageService;

$adminApi = new AdminApiController(
    new MemberService(new MemberRepository($db)),
    new PackageService(new PackageRepository($db)),
    new ClassService(new ClassRepository($db)),
    new RegistrationRepository($db)
);

$adminMiddleware = [$requireAuth, $requireAdmin];

$router->add('GET', '/api/package-registrations', [$adminApi, 'packageRegistrations'], $adminMiddleware);
$router->add('GET', '/api/class-registrations', [$adminApi, 'classRegistrations'], $adminMiddleware);
$router->add('PUT', '/api/package-registrations/{id}', [$adminApi, 'updatePackageRegistration'], $adminMiddleware);
$router->add('PUT', '/api/class-registrations/{id}', [$adminApi, 'updateClassRegistration'], $adminMiddleware);
