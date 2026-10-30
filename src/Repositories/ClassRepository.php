<?php
namespace App\Repositories;

class ClassRepository extends BaseRepository
{
    protected string $table = 'lop_hoc';

    protected function mapRow(array $row): object
    {
        return (object) $row;
    }

    public function listAll(): array
    {
        $sql = "
            SELECT lh.*, gv.ho_ten AS instructor_name,
                   (SELECT COUNT(*) FROM dang_ky_lop d
                     WHERE d.lop_hoc_id = lh.id AND d.trang_thai = 'dang_ky') AS enrolled
            FROM `lop_hoc` lh
            JOIN `giao_vien` gv ON gv.id = lh.giao_vien_id
            WHERE lh.deleted_at IS NULL
            ORDER BY lh.id
        ";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findRow(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `lop_hoc` WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}
