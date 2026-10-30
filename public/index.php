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
use App\Core\Page;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Repositories\TaiKhoanRepository;
use App\Repositories\InstructorRepository;
use App\Services\InstructorService;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\InstructorApiController;

// 1) Session an toàn (phải chạy trước khi in bất kỳ nội dung nào)
Session::start();

// 2) Router
$router = new Router();

/* ============================================================
   PUBLIC
   ============================================================ */
$router->add('GET', '/', function (): void {
    Page::public('public/home', [], [
        'title' => 'Phòng tập & lớp học hiện đại',
    ]);
});

$router->add('GET', '/login', function (): void {
    Page::auth('auth/login', [], [
        'title' => 'Đăng nhập',
        'scripts' => ['login.js'],
    ]);
});

$router->add('POST', '/login', function (): void {
    $role = (string) ($_POST['role'] ?? 'customer');
    if (!in_array($role, ['admin', 'instructor', 'customer'], true)) {
        $role = 'customer';
    }
    redirect('/' . $role);
});

$router->add('GET', '/register', function (): void {
    Page::auth('auth/register', [], ['title' => 'Đăng ký hội viên']);
});

$router->add('POST', '/register', function (): void {
    redirect('/customer');
});

/* ============================================================
   CUSTOMER
   ============================================================ */
$router->add('GET', '/customer', function (): void {
    Page::app('customer', 'customer/dashboard', [], [
        'title' => 'Tổng quan hội viên',
        'active' => 'dashboard',
        'breadcrumbs' => [['Hội viên'], ['Tổng quan']],
    ]);
});

$router->add('GET', '/customer/class-registration', function (): void {
    Page::app('customer', 'customer/class-registration', [], [
        'title' => 'Đăng ký lớp học',
        'active' => 'class-registration',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Đăng ký lớp học']],
        'scripts' => ['class-registration.js'],
    ]);
});

$router->add('GET', '/customer/schedule', function (): void {
    Page::app('customer', 'customer/schedule', [], [
        'title' => 'Lịch tập',
        'active' => 'schedule',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Lịch tập']],
    ]);
});

$router->add('GET', '/customer/history', function (): void {
    Page::app('customer', 'customer/history', [], [
        'title' => 'Lịch sử hoạt động',
        'active' => 'history',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Lịch sử']],
    ]);
});

$router->add('GET', '/customer/account', function (): void {
    Page::app('customer', 'customer/account', [], [
        'title' => 'Quản lý tài khoản',
        'active' => 'account',
        'breadcrumbs' => [['Hội viên', '/customer'], ['Tài khoản']],
    ]);
});

/* ============================================================
   INSTRUCTOR
   ============================================================ */
$router->add('GET', '/instructor', function (): void {
    Page::app('instructor', 'instructor/dashboard', [], [
        'title' => 'Tổng quan giáo viên',
        'active' => 'dashboard',
        'breadcrumbs' => [['Giáo viên'], ['Tổng quan']],
    ]);
});

$router->add('GET', '/instructor/schedule', function (): void {
    Page::app('instructor', 'instructor/schedule', [], [
        'title' => 'Lịch dạy',
        'active' => 'schedule',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Lịch dạy']],
    ]);
});

$router->add('GET', '/instructor/availability', function (): void {
    Page::app('instructor', 'instructor/availability', [], [
        'title' => 'Đăng ký lịch dạy',
        'active' => 'availability',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Đăng ký lịch dạy']],
    ]);
});

$router->add('GET', '/instructor/classes', function (): void {
    Page::app('instructor', 'instructor/classes', [], [
        'title' => 'Lớp tôi đang dạy',
        'active' => 'classes',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Lớp học']],
    ]);
});

$router->add('GET', '/instructor/search', function (): void {
    Page::app('instructor', 'instructor/search', [], [
        'title' => 'Tra cứu học viên',
        'active' => 'search',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Tra cứu']],
    ]);
});

$router->add('GET', '/instructor/account', function (): void {
    Page::app('instructor', 'instructor/account', [], [
        'title' => 'Quản lý tài khoản',
        'active' => 'account',
        'breadcrumbs' => [['Giáo viên', '/instructor'], ['Tài khoản']],
    ]);
});

/* ============================================================
   ADMIN
   ============================================================ */
$router->add('GET', '/admin', function (): void {
    Page::app('admin', 'admin/dashboard', [], [
        'title' => 'Tổng quan hệ thống',
        'active' => 'dashboard',
        'breadcrumbs' => [['Quản trị'], ['Tổng quan']],
    ]);
});

$router->add('GET', '/admin/search', function (): void {
    Page::app('admin', 'admin/search', [], [
        'title' => 'Tra cứu toàn hệ thống',
        'active' => 'search',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Tra cứu']],
    ]);
});

$router->add('GET', '/admin/classes', function (): void {
    Page::app('admin', 'admin/classes', [], [
        'title' => 'Quản lý lớp học',
        'active' => 'classes',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Lớp học']],
        'scripts' => ['admin-classes.js'],
    ]);
});

$router->add('GET', '/admin/reports', function (): void {
    Page::app('admin', 'admin/reports', [], [
        'title' => 'Thống kê & báo cáo',
        'active' => 'reports',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Thống kê']],
    ]);
});

$router->add('GET', '/admin/members', function (): void {
    Page::app('admin', 'admin/members', [], [
        'title' => 'Quản lý hội viên',
        'active' => 'members',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Hội viên']],
    ]);
});

$router->add('GET', '/admin/instructors', function (): void {
    Page::app('admin', 'admin/instructors', [], [
        'title' => 'Quản lý giáo viên',
        'active' => 'instructors',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Giáo viên']],
        'scripts' => ['admin-instructors.js'],
    ]);
});

$router->add('GET', '/admin/packages', function (): void {
    Page::app('admin', 'admin/packages', [], [
        'title' => 'Quản lý gói tập',
        'active' => 'packages',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Gói tập']],
    ]);
});

$router->add('GET', '/admin/registrations', function (): void {
    Page::app('admin', 'admin/registrations', [], [
        'title' => 'Quản lý đăng ký',
        'active' => 'registrations',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Đăng ký']],
    ]);
});

$router->add('GET', '/admin/availability', function (): void {
    Page::app('admin', 'admin/availability', [], [
        'title' => 'Duyệt lịch dạy',
        'active' => 'availability',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Duyệt lịch dạy']],
        'scripts' => ['admin-availability.js'],
    ]);
});

$router->add('GET', '/admin/accounts', function (): void {
    Page::app('admin', 'admin/accounts', [], [
        'title' => 'Quản lý tài khoản',
        'active' => 'accounts',
        'breadcrumbs' => [['Quản trị', '/admin'], ['Tài khoản']],
    ]);
});

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
    $probe->query('USE `' . $dbConfig['name'] . '`');
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
    $instructorRepo = new InstructorRepository($db);
    $instructorService = new InstructorService($instructorRepo, $taiKhoanRepo);
    $instructorController  = new InstructorApiController($instructorService);

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

    // ---- API có bảo mật ----
    $router->add('POST', '/api/auth/login',  [$authController, 'login']);
    $router->add('GET',  '/api/auth/me',     [$authController, 'me'],    [$requireAuth]);
    $router->add('POST', '/api/auth/logout', [$authController, 'logout'], [$requireAuth]);

    // ---- CRUD Instructor ----
    $router->add('GET',    '/api/instructors',       [$instructorController, 'index'],   [$requireAuth]);
    $router->add('GET',    '/api/instructors/{id}',  [$instructorController, 'show'],    [$requireAuth]);
    $router->add('POST',   '/api/instructors',       [$instructorController, 'store'],   [$requireAuth, $requireAdmin]);
    $router->add('PUT',    '/api/instructors/{id}',  [$instructorController, 'update'],  [$requireAuth, $requireAdmin]);
    $router->add('DELETE', '/api/instructors/{id}',  [$instructorController, 'destroy'], [$requireAuth, $requireAdmin]);
}

// 3) Chạy
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
