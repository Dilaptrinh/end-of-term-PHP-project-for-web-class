<?php
/**
 * API xác thực.
 *
 * File được public/index.php nạp tự động, chỉ khi DB sẵn sàng.
 *
 * @var \App\Core\Router $router
 * @var \App\Controllers\Api\AuthController $authController
 * @var callable $requireAuth
 */

$router->add('POST', '/api/auth/login',  [$authController, 'login']);
$router->add('GET',  '/api/auth/me',     [$authController, 'me'],     [$requireAuth]);
$router->add('POST', '/api/auth/logout', [$authController, 'logout'], [$requireAuth]);
