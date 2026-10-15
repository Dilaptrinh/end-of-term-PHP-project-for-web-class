<?php
namespace App\Repositories;

use App\Models\User;

/**
 * UserRepository
 * ------------------------------------------------------------------
 * Chỉ cần khai báo tên bảng + cách chuyển DB row -> User.
 * Các hàm getAll/find/insert/update/delete/count THỪA HƯỞNG từ BaseRepository.
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'user';

    // 1 dòng DB -> 1 object User
    protected function mapRow(array $row): object
    {
        return new User((int) $row['id'], $row['name'], (int) $row['age']);
    }

    // Lưu 1 User -> tự gọi insert() của lớp cha
    public function save(User $user): int
    {
        return $this->insert([
            'name' => $user->getName(),
            'age'  => $user->getAge(),
        ]);
    }

    // Ví dụ hàm ĐẶC THÙ chỉ User mới có: tìm theo tên
    public function findByName(string $name): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE name = ?");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return array_map(fn(array $row) => $this->mapRow($row), $rows);
    }

}
