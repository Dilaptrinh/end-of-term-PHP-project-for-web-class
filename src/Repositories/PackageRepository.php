<?php
namespace App\Repositories;

class PackageRepository extends BaseRepository
{
    protected string $table = 'goi_tap';

    protected function mapRow(array $row): object
    {
        return (object) $row;
    }

    public function listAll(): array
    {
        $sql = 'SELECT * FROM `goi_tap` WHERE deleted_at IS NULL ORDER BY id';
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
