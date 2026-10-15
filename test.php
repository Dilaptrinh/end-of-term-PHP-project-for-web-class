<?php
/**
 * test.php  (nằm ở gốc dự án Project_ck)
 * ------------------------------------------------------------------
 * File "chạy thử" để xem kết quả. KHÔNG phải code của ứng dụng.
 * Chạy bằng:  C:\xampp\php\php.exe test.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// 1) Nạp class User (vì User chưa có autoload)
require __DIR__ . '/src/Models/User.php';

// 2) Cho biết "User" là App\Models\User
use App\Models\User;

// 3) Tạo 1 user với dữ liệu GÕ TAY (hard data)
$user = new User(1, 'Nguyễn Văn A', 20);

// 4) Lấy dữ liệu RA và in
echo "ID  : " . $user->getId() . PHP_EOL;
echo "Tên : " . $user->getName() . PHP_EOL;
echo "Tuổi: " . $user->getAge() . PHP_EOL;

echo "----" . PHP_EOL;

// 5) Tạo một danh sách nhiều user (gõ tay)
$danhSach = [
    new User(1, 'Nguyễn Văn A', 20),
    new User(2, 'Trần Thị B', 22),
    new User(3, 'Lê Văn C', 25),
];

foreach ($danhSach as $u) {
    echo $u->getId() . " - " . $u->getName() . " - " . $u->getAge() . PHP_EOL;
}
