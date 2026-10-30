<?php
namespace App\Repositories;

class AvailabilityRepository extends BaseRepository
{
    protected string $table = 'lich_ranh';

    protected function mapRow(array $row): object
    {
        return (object) $row;
    }

    public function listAll(): array
    {
        $sql = "
            SELECT lr.*, gv.ho_ten AS instructor_name
            FROM `lich_ranh` lr
            JOIN `giao_vien` gv ON gv.id = lr.giao_vien_id
            ORDER BY lr.ngay DESC, lr.id DESC
        ";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function listForInstructor(int $instructorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT lr.*, gv.ho_ten AS instructor_name
               FROM `lich_ranh` lr
               JOIN `giao_vien` gv ON gv.id = lr.giao_vien_id
              WHERE lr.giao_vien_id = ?
              ORDER BY lr.ngay DESC, lr.id DESC"
        );
        $stmt->bind_param('i', $instructorId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function findRow(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `lich_ranh` WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function setStatus(int $id, string $status, ?string $reason = null, ?string $approver = null): void
    {
        $stmt = $this->db->prepare(
            'UPDATE `lich_ranh`
                SET trang_thai = ?, ghi_chu = COALESCE(?, ghi_chu), nguoi_duyet = COALESCE(?, nguoi_duyet)
              WHERE id = ?'
        );
        $stmt->bind_param('sssi', $status, $reason, $approver, $id);
        $stmt->execute();
        $stmt->close();
    }
}
