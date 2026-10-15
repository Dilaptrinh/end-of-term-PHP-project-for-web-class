<?php
/**
 * src/bootstrap.php
 * ------------------------------------------------------------------
 * Chạy MỘT lần ở đầu chương trình.
 * Đăng ký autoload: tự nạp file class khi PHP cần mà chưa có.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Đăng ký hàm autoload với PHP
spl_autoload_register(function (string $className): void {
    $prefix  = 'App\\';           // mọi class của dự án bắt đầu bằng "App\"
    $baseDir = __DIR__ . '/';     // __DIR__ đang là thư mục "src/"

    // 1) Nếu class KHÔNG bắt đầu bằng "App\" thì bỏ qua
    if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    // 2) Bỏ "App\" để lấy phần còn lại
    //    "App\Repositories\UserRepository"  ->  "Repositories\UserRepository"
    $relative = substr($className, strlen($prefix));

    // 3) Đổi '\' thành '/', ghép với src/ và thêm '.php'
    //    "Repositories/UserRepository"  ->  src/Repositories/UserRepository.php
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    // 4) Nếu file tồn tại thì nạp nó
    if (is_file($file)) {
        require $file;
    }
});

// Nạp các hàm dùng chung (url, redirect, e...)
require __DIR__ . '/Core/helpers.php';