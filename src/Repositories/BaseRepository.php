<?php
namespace App\Repositories;

use mysqli;

/**
 * BaseRepository
 * ------------------------------------------------------------------
 * Lớp CHA TRỪU TƯỢNG chứa các hàm SQL dùng chung cho mọi model.
 * Lớp con chỉ cần:
 *   - khai báo tên bảng:      protected string $table = 'ten_bang';
 *   - cài đặt mapRow():       chuyển 1 dòng DB -> 1 object model
 * Các hàm getAll/find/insert/update/delete/count thì THỪA HƯỞNG.
 */
abstract class BaseRepository
{
    protected mysqli $db;

    // Lớp con PHẢI điền tên bảng
    protected string $table = '';

    /**
     * Lớp con cài đặt: biến 1 dòng (mảng từ DB) thành 1 object model.
     */
    abstract protected function mapRow(array $row): object;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // ---- Lấy tất cả, trả về MẢNG CÁC OBJECT ----
    public function getAll(): array
    {
        $result = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY id");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        return array_map(fn(array $row) => $this->mapRow($row), $rows);
    }

    // ---- Tìm theo id, trả về object hoặc null ----
    public function find(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? $this->mapRow($row) : null;
    }

    // ---- Thêm mới từ mảng [cột => giá trị], trả về id vừa sinh ----
    public function insert(array $data): int
    {
        $columns      = array_keys($data);
        $colList      = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $sql  = "INSERT INTO `{$this->table}` ($colList) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        $values = array_values($data);
        $types  = $this->detectTypes($values);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        $newId = $this->db->insert_id;
        $stmt->close();

        return $newId;
    }

    // ---- Cập nhật 1 dòng theo id từ mảng [cột => giá trị] ----
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $sql  = "UPDATE `{$this->table}` SET $sets WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        $values   = array_values($data);
        $values[] = $id;
        $types    = $this->detectTypes(array_values($data)) . 'i';
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // ---- Xóa theo id ----
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    // ---- Đếm số dòng ----
    public function count(): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS c FROM `{$this->table}`");
        return (int) $result->fetch_assoc()['c'];
    }

    // ---- Nội bộ: đoán kiểu bind_param (i/d/s) từ giá trị ----
    private function detectTypes(array $values): string
    {
        $types = '';
        foreach ($values as $value) {
            $types .= match (true) {
                is_int($value)   => 'i',
                is_float($value) => 'd',
                default          => 's',
            };
        }
        return $types;
    }
}
