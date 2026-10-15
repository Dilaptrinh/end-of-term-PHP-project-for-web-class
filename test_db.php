<?php
/**
 * test_db.php (ở gốc dự án)
 * ------------------------------------------------------------------
 * Chạy thử BaseRepository qua UserRepository.
 * Chạy:  C:\xampp\php\php.exe test_db.php
 */

use App\Repositories\UserRepository;
use App\Models\User;

require __DIR__ . '/src/bootstrap.php';                    // autoload

$db = require __DIR__ . '/mysql/connect_mysql.php';        // kết nối

$repo = new UserRepository($db);                           // tiêm kết nối


// 4) Tìm theo id
$u = $repo->find(5);
echo "--- find({4}) ---" . PHP_EOL;
echo "  " . $u->getName() . PHP_EOL;
