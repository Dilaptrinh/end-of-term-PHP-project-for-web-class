<?php
namespace App\Repositories;

class MemberRepository extends BaseRepository
{
    protected string $table = 'hoi_vien';

    protected function mapRow(array $row): object
    {
        return (object) $row;
    }

    public function listAll(): array
    {
        $sql = 'SELECT * FROM `hoi_vien` WHERE deleted_at IS NULL ORDER BY id';
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findByAccount(int $accountId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM `hoi_vien` WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM `hoi_vien` WHERE email = ? AND deleted_at IS NULL';
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
        }
        $sql .= ' LIMIT 1';
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
}
