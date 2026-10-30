<?php
declare(strict_types=1);

/**
 * Helper dùng chung cho mọi nhóm dữ liệu view.
 * Không chứa truy vấn nghiệp vụ.
 */

function view_db(): mysqli
{
    static $conn = null;
    if ($conn === null) {
        $conn = require __DIR__ . '/../../../mysql/connect_mysql.php';
    }
    return $conn;
}

function view_weekday_name(string $date): string
{
    $names = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
    return $names[(int) date('N', strtotime($date))] ?? '';
}

function view_current_account_id(): int
{
    return (int) ($_SESSION['tai_khoan_id'] ?? 0);
}

function view_cast_ints(array $rows, array $keys): array
{
    foreach ($rows as &$row) {
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                $row[$key] = (int) $row[$key];
            }
        }
    }
    unset($row);
    return $rows;
}
