<?php
namespace App\Repositories;

use App\Models\TaiKhoan;

class TaiKhoanRepository extends BaseRepository
{
    protected string $table = 'tai_khoan';

    protected function mapRow(array $row): object
    {
        return new TaiKhoan(
            (int) $row['id'],
            $row['ten_dang_nhap'],
            $row['ho_ten'],
            (int) ($row['role_id'] ?? 0),
            $row['role_code'] ?? ($row['vai_tro'] ?? ''),
            (int) $row['trang_thai'] === 1,
            $row['lan_dang_nhap_cuoi'] ?? null,
            $row['deleted_at'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }

    public function find(int $id): ?object
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? $this->mapRow($row) : null;
    }

    /**
     * Chỉ dùng cho đăng nhập: trả về hash mật khẩu.
     * Hash KHÔNG bao giờ được đưa ra ngoài tầng Auth.
     */
    public function findCredentials(string $tenDangNhap): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, mat_khau, trang_thai
             FROM `{$this->table}`
             WHERE ten_dang_nhap = ?
             LIMIT 1"
        );
        $stmt->bind_param('s', $tenDangNhap);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function findHashById(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT mat_khau FROM `tai_khoan` WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (string) $row['mat_khau'] : null;
    }

    public function existsByUsername(string $tenDangNhap): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM `{$this->table}` WHERE ten_dang_nhap = ? LIMIT 1"
        );
        $stmt->bind_param('s', $tenDangNhap);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        return $exists;
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET mat_khau = ? WHERE id = ?"
        );
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        $stmt->close();
    }
}
