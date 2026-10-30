<?php
/**
 * mysql/setup.php
 * ------------------------------------------------------------------
 * Khởi tạo CSDL và dữ liệu nền cho dự án.
 *
 * Chạy:  C:\xampp\php\php.exe mysql\setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$conn = require __DIR__ . '/connect_mysql.php';
require __DIR__ . '/schema.php';
require __DIR__ . '/seed_accounts.php';

ensure_schema($conn);
seed_initial_accounts($conn);

echo 'Đã khởi tạo cấu trúc và dữ liệu nền.' . PHP_EOL;
echo 'Tài khoản: admin / thuha / ducanh / ngocmai / quocbao / hongnhung / hoangnam (mật khẩu 123456).' . PHP_EOL;
