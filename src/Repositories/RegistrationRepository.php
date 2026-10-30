<?php
namespace App\Repositories;

class RegistrationRepository extends BaseRepository
{
    protected string $table = 'dang_ky_goi';

    protected function mapRow(array $row): object
    {
        return (object) $row;
    }

    public function listPackageRegistrations(): array
    {
        $sql = "
            SELECT d.*, hv.ho_ten AS member_name, gt.ten_goi AS package_name
            FROM `dang_ky_goi` d
            JOIN `hoi_vien` hv ON hv.id = d.hoi_vien_id
            JOIN `goi_tap` gt ON gt.id = d.goi_tap_id
            ORDER BY d.id DESC
        ";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findPackageRegistration(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `dang_ky_goi` WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function setPackageStatus(int $id, string $status, ?string $method = null): void
    {
        if ($status === 'da_thanh_toan') {
            $stmt = $this->db->prepare(
                "UPDATE `dang_ky_goi`
                    SET trang_thai = ?, phuong_thuc = COALESCE(?, phuong_thuc),
                        ngay_thanh_toan = NOW()
                  WHERE id = ?"
            );
            $stmt->bind_param('ssi', $status, $method, $id);
        } else {
            $stmt = $this->db->prepare('UPDATE `dang_ky_goi` SET trang_thai = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $id);
        }
        $stmt->execute();
        $stmt->close();
    }

    public function listClassRegistrations(): array
    {
        $sql = "
            SELECT d.*, hv.ho_ten AS member_name, lh.ten_lop AS class_name
            FROM `dang_ky_lop` d
            JOIN `hoi_vien` hv ON hv.id = d.hoi_vien_id
            JOIN `lop_hoc` lh ON lh.id = d.lop_hoc_id
            ORDER BY d.id DESC
        ";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findClassRegistration(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `dang_ky_lop` WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function createClassRegistration(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO `dang_ky_lop` (hoi_vien_id, lop_hoc_id, ngay_dang_ky, trang_thai)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iiss',
            $data['hoi_vien_id'],
            $data['lop_hoc_id'],
            $data['ngay_dang_ky'],
            $data['trang_thai']
        );
        $stmt->execute();
        $id = $this->db->insert_id;
        $stmt->close();
        return $id;
    }

    public function countEnrolled(int $classId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS c FROM `dang_ky_lop` WHERE lop_hoc_id = ? AND trang_thai = 'dang_ky'"
        );
        $stmt->bind_param('i', $classId);
        $stmt->execute();
        $count = (int) $stmt->get_result()->fetch_assoc()['c'];
        $stmt->close();
        return $count;
    }

    public function hasClassRegistration(int $memberId, int $classId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM `dang_ky_lop`
              WHERE hoi_vien_id = ? AND lop_hoc_id = ? AND trang_thai = 'dang_ky' LIMIT 1"
        );
        $stmt->bind_param('ii', $memberId, $classId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();
        return $exists;
    }

    public function setClassStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE `dang_ky_lop` SET trang_thai = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();
    }
}
