<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * UserService
 * ------------------------------------------------------------------
 * Tầng nghiệp vụ: kiểm tra dữ liệu rồi gọi Repository.
 * Controller KHÔNG validate; nó gọi Service.
 */
class UserService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function getAll(): array
    {
        return $this->userRepository->getAll();
    }

    public function getById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Tạo user mới.
     * @return array{ok: bool, errors: array<int,string>}
     */
    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->userRepository->save(new User(
            null,
            trim((string) $input['name']),
            (int) $input['age']
        ));

        return ['ok' => true, 'errors' => []];
    }

    /**
     * Cập nhật user theo id.
     * @return array{ok: bool, errors: array<int,string>}
     */
    public function update(int $id, array $input): array
    {
        $errors = $this->validate($input);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->userRepository->update($id, [
            'name' => trim((string) $input['name']),
            'age'  => (int) $input['age'],
        ]);

        return ['ok' => true, 'errors' => []];
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * Kiểm tra dữ liệu đầu vào. Trả về mảng lỗi (rỗng = hợp lệ).
     */
    private function validate(array $input): array
    {
        $errors = [];

        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Vui lòng nhập tên.';
        }

        $age = (int) ($input['age'] ?? 0);
        if ($age <= 0 || $age > 150) {
            $errors[] = 'Tuổi phải là số từ 1 đến 150.';
        }

        return $errors;
    }
}
