<?php
/**
 * mysql/setup.php
 * ------------------------------------------------------------------
 * Script "cài đặt" cho việc học:
 *   1. Kết nối MySQL
 *   2. Tạo bảng `user` (nếu chưa có)
 *   3. Xóa dữ liệu cũ + thêm 3 user mẫu
 *   4. Đọc lại và in ra để kiểm tra
 *
 * Chạy:  C:\xampp\php\php.exe mysql\setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// 1) Lấy kết nối (file connect_mysql.php trả về $conn)
$conn = require __DIR__ . '/connect_mysql.php';

// 2) Tạo bảng `user` nếu chưa tồn tại
//    Cấu trúc khớp với class User: id, name, age
$conn->query("
    CREATE TABLE IF NOT EXISTS `user` (
        id   INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100),
        age  INT
    )
");
echo "1. Đã tạo bảng `user`." . PHP_EOL;

// 3) Xóa dữ liệu cũ để mỗi lần chạy lại không bị trùng
$conn->query("DELETE FROM `user`");

// 4) Thêm dữ liệu mẫu
$conn->query("INSERT INTO `user` (name, age) VALUES ('Nguyễn Văn A', 20)");
$conn->query("INSERT INTO `user` (name, age) VALUES ('Trần Thị B', 22)");
$conn->query("INSERT INTO `user` (name, age) VALUES ('Lê Văn C', 25)");
echo "2. Đã thêm 3 user." . PHP_EOL;

// 5) Đọc lại để kiểm tra dữ liệu nằm trong CSDL thật
$result = $conn->query("SELECT * FROM `user` ORDER BY id");
$rows = $result->fetch_all(MYSQLI_ASSOC);

echo "3. Đọc từ MySQL ra được " . count($rows) . " dòng:" . PHP_EOL;
foreach ($rows as $row) {
    echo "   ID={$row['id']} | name={$row['name']} | age={$row['age']}" . PHP_EOL;
}
