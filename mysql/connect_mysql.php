<?php
/**
 * mysql/connect_mysql.php
 * ------------------------------------------------------------------
 * Mở kết nối tới MySQL bằng mysqli và TRẢ VỀ đối tượng kết nối.
 *
 * Cách dùng ở file khác:
 *   $conn = require __DIR__ . '/connect_mysql.php';
 *   $result = $conn->query('SELECT * FROM ...');
 *
 * Cách test nhanh (chạy trực tiếp):
 *   C:\xampp\php\php.exe mysql\connect_mysql.php
 */

// 1) Bật báo lỗi để dễ thấy vấn đề khi đang học
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 2) Đọc thông tin cấu hình từ config.php
$config = require __DIR__ . '/config.php';

// 3) Cho mysqli NÉM ngoại lệ khi có lỗi, thay vì trả false im lặng.
//    Nhờ vậy ta bắt lỗi gọn gàng bằng try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 4) Tạo kết nối.
    //    Thứ tự tham số: host, user, pass, database, port
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name'],
        (int) $config['port']
    );

    // 5) Đặt bảng mã để lưu/đọc tiếng Việt đúng
    $conn->set_charset($config['charset']);

    // 6) Nếu chạy tới đây tức là kết nối thành công
    if (PHP_SAPI === 'cli') {
        echo "Kết nối MySQL thành công! Database: {$config['name']}" . PHP_EOL;
    }
} catch (mysqli_sql_exception $e) {
    // Có lỗi (sai mật khẩu, database không tồn tại, MySQL chưa chạy...)
    die('Lỗi kết nối MySQL: ' . $e->getMessage() . PHP_EOL);
}

// 7) Trả về đối tượng kết nối để file khác dùng tiếp
return $conn;
