<?php
/**
 * public/index.php
 * ------------------------------------------------------------------
 * FRONT CONTROLLER — MỘT CỬA DUY NHẤT cho mọi request.
 *
 * Dữ liệu được đọc từ MySQL qua các repository và lớp view data.
 * API/CRUD cũ chỉ được đăng ký khi MySQL sẵn sàng.
 */

// Cho PHP built-in server phục vụ file tĩnh (CSS/JS/font).
if (PHP_SAPI === 'cli-server') {
    $requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $staticFile = realpath(__DIR__ . $requestPath);
    if (
        $staticFile !== false
        && $staticFile !== __FILE__
        && is_file($staticFile)
        && strncmp($staticFile, __DIR__, strlen(__DIR__)) === 0
    ) {
        return false;
    }
}

require __DIR__ . '/../src/bootstrap.php';

use App\Core\Auth;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Repositories\TaiKhoanRepository;
use App\Controllers\Api\AuthController;

// 1) Session an toàn (phải chạy trước khi in bất kỳ nội dung nào)
Session::start();

// 2) Router
$router = new Router();

/* ============================================================
   ROUTE CÔNG KHAI (không cần DB): trang chủ, login, register
   ============================================================ */
require __DIR__ . '/../src/Routes/Pages/10-public.php';
/* ============================================================
   BACKEND CŨ — chỉ đăng ký khi MySQL sẵn sàng
   ============================================================ */
$db = null;
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $dbConfig = require __DIR__ . '/../mysql/config.php';
    $probe = new mysqli(
        $dbConfig['host'],
        $dbConfig['user'],
        $dbConfig['pass'],
        '',
        (int) $dbConfig['port']
    );
    $probe->set_charset($dbConfig['charset']);
    $probe->query(
        'CREATE DATABASE IF NOT EXISTS `' . $dbConfig['name'] . '` CHARACTER SET utf8mb4'
    );
    $probe->select_db($dbConfig['name']);
    $probe->close();

    $db = require __DIR__ . '/../mysql/connect_mysql.php';

    // Bảo đảm CSDL có cấu trúc + dữ liệu nền, rồi tạo tài khoản đăng nhập
    require_once __DIR__ . '/../mysql/schema.php';
    require_once __DIR__ . '/../mysql/seed_accounts.php';
    try {
        ensure_schema($db);
        seed_initial_accounts($db);
    } catch (Throwable $seedError) {
        error_log('[database_bootstrap] ' . $seedError->getMessage());
    }
} catch (Throwable $e) {
    $db = null;
}

if ($db !== null) {
    $taiKhoanRepo   = new TaiKhoanRepository($db);
    $auth           = new Auth($taiKhoanRepo);
    $authController = new AuthController($auth);

    $requireAuth = function () use ($auth): void {
        if (!$auth->check()) {
            Response::json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
        }
    };

    $requireAdmin = function () use ($auth): void {
        if (!$auth->isAdmin()) {
            Response::json(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
        }
    };

    $requireInstructor = function () use ($auth): void {
        if (($auth->user()?->getVaiTro() ?? '') !== 'instructor') {
            Response::json(['success' => false, 'message' => 'Chỉ giáo viên được thực hiện.'], 403);
        }
    };

    $requireCustomer = function () use ($auth): void {
        if (($auth->user()?->getVaiTro() ?? '') !== 'customer') {
            Response::json(['success' => false, 'message' => 'Chỉ hội viên được thực hiện.'], 403);
        }
    };

    // Guard cho route trang: chưa đăng nhập -> /login, sai role -> trang đúng role
    $roleGuard = function (string $role) use ($auth): callable {
        return function () use ($auth, $role): void {
            $user = $auth->user();
            if (!$user) {
                redirect('/login');
            }
            if ($user->getVaiTro() !== $role) {
                redirect('/' . $user->getVaiTro());
            }
        };
    };
    $adminGuard = $roleGuard('admin');
    $customerGuard = $roleGuard('customer');
    $instructorGuard = $roleGuard('instructor');

    // Nạp route trang theo role (bỏ qua file public đã nạp)
    $pageRouteFiles = glob(__DIR__ . '/../src/Routes/Pages/*.php') ?: [];
    sort($pageRouteFiles);
    foreach ($pageRouteFiles as $pageRouteFile) {
        if (basename($pageRouteFile) === '10-public.php') {
            continue;
        }
        require $pageRouteFile;
    }

    // Mỗi domain API một file trong src/Routes/Api/
    $apiRouteFiles = glob(__DIR__ . '/../src/Routes/Api/*.php') ?: [];
    sort($apiRouteFiles);
    foreach ($apiRouteFiles as $apiRouteFile) {
        require $apiRouteFile;
    }
}

// 3) Chạy
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
