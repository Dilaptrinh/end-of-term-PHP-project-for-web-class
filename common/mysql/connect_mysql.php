<?php


// 1) Bật báo lỗi để dễ thấy vấn đề khi đang học
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 2) Đọc thông tin cấu hình từ config.php
$config = require __DIR__ . '/config.php';

// 3) Cho mysqli NÉM ngoại lệ khi có lỗi, thay vì trả false im lặng.
//    Nhờ vậy ta bắt lỗi gọn gàng bằng try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 4) Kết nối trực tiếp tới database đã được tạo/import từ file SQL.
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name'],
        (int) $config['port']
    );

    // 5) Đặt bảng mã để lưu tiếng Việt đúng
    $conn->set_charset($config['charset']);

} catch (mysqli_sql_exception $e) {
    die('Lỗi kết nối MySQL: ' . $e->getMessage() . PHP_EOL);
}

return $conn;
