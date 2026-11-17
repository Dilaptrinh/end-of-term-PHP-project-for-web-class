<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$config = require __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name'],
        (int) $config['port']
    );

    $conn->set_charset($config['charset']);

} catch (mysqli_sql_exception $e) {
    die('Lỗi kết nối MySQL: ' . $e->getMessage() . PHP_EOL);
}

return $conn;
