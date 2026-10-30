<?php
namespace App\Repositories;

use App\Models\GiaoVien;

class InstructorRepository extends BaseRepository
{
    protected string $table = 'giao_vien';

    protected function mapRow(array $row): object
    {
        return new GiaoVien(
            (int) $row['id'],
            isset($row['tai_khoan_id']) ? (int) $row['tai_khoan_id'] : null,
            $row['ho_ten'],
            $row['sdt'],
            $row['email'],
            $row['chuyen_mon'],
            $row['gioi_thieu'],
            $row['trang_thai'],
            $row['deleted_at'],
            $row['created_at'],
            $row['updated_at'],
            $row['ten_dang_nhap'] ?? null
        );
    }

    public function getAll(): array
    {
        $result = $this->db->query(
            "SELECT gv.*, tk.ten_dang_nhap
             FROM `{$this->table}` gv
             LEFT JOIN `tai_khoan` tk ON tk.id = gv.tai_khoan_id
             WHERE gv.deleted_at IS NULL
             ORDER BY gv.id"
        );

        return array_map(
            fn (array $row) => $this->mapRow($row),
            $result->fetch_all(MYSQLI_ASSOC)
        );
    }

    public function find(int $id): ?object
    {
        $stmt = $this->db->prepare(
            "SELECT gv.*, tk.ten_dang_nhap
             FROM `{$this->table}` gv
             LEFT JOIN `tai_khoan` tk ON tk.id = gv.tai_khoan_id
             WHERE gv.id = ? AND gv.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? $this->mapRow($row) : null;
    }

    public function findByAccount(int $accountId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM `giao_vien` WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = "SELECT 1 FROM `{$this->table}` WHERE email = ?";
        if ($ignoreId !== null) {
            $sql .= " AND id <> ?";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if ($ignoreId !== null) {
            $stmt->bind_param('si', $email, $ignoreId);
        } else {
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        return $exists;
    }

    public function save(GiaoVien $instructor): int
    {
        return $this->insert([
            'tai_khoan_id' => $instructor->getTaiKhoanId(),
            'ho_ten'       => $instructor->getHoTen(),
            'sdt'          => $instructor->getSdt(),
            'email'        => $instructor->getEmail(),
            'chuyen_mon'   => $instructor->getChuyenMon(),
            'gioi_thieu'   => $instructor->getGioiThieu(),
            'trang_thai'   => $instructor->getTrangThai(),
        ]);
    }
}
