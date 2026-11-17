<?php
declare(strict_types=1);

/**
 * Helper dùng chung cho mọi nhóm dữ liệu view.
 * Không chứa truy vấn nghiệp vụ.
 */

function view_db(): mysqli
{
    return common_db();
}

function view_current_account_id(): int
{
    return (int) ($_SESSION['tai_khoan_id'] ?? 0);
}

function view_cast_ints(array $rows, array $keys): array //Chức năng: ép các cột chỉ định sang kiểu
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
