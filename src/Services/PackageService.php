<?php
namespace App\Services;

use App\Repositories\PackageRepository;

class PackageService
{
    public function __construct(private PackageRepository $packageRepository)
    {
    }

    public function getAll(): array
    {
        return $this->packageRepository->listAll();
    }

    public function getById(int $id): ?array
    {
        $row = $this->packageRepository->find($id);
        return $row ? (array) $row : null;
    }

    public function create(array $input): array
    {
        $data = $this->normalize($input);
        $errors = $this->validate($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        $id = $this->packageRepository->insert($data);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        if (!$this->packageRepository->find($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        $data = $this->normalize($input);
        $errors = $this->validate($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        $this->packageRepository->update($id, $data);
        return ['ok' => true, 'errors' => []];
    }

    public function delete(int $id): bool
    {
        if (!$this->packageRepository->find($id)) {
            return false;
        }
        $this->packageRepository->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        return true;
    }

    private function normalize(array $input): array
    {
        return [
            'ten_goi'       => trim((string) ($input['ten_goi'] ?? '')),
            'thoi_han_ngay' => (int) ($input['thoi_han_ngay'] ?? 0),
            'gia'           => (float) ($input['gia'] ?? 0),
            'mo_ta'         => ($v = trim((string) ($input['mo_ta'] ?? ''))) === '' ? null : $v,
            'trang_thai'    => trim((string) ($input['trang_thai'] ?? 'dang_ban')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['ten_goi']) < 2 || mb_strlen($data['ten_goi']) > 100) {
            $errors['ten_goi'] = 'Tên gói phải từ 2 đến 100 ký tự.';
        }
        if ($data['thoi_han_ngay'] < 1 || $data['thoi_han_ngay'] > 3650) {
            $errors['thoi_han_ngay'] = 'Thời hạn phải từ 1 đến 3650 ngày.';
        }
        if ($data['gia'] < 0) {
            $errors['gia'] = 'Giá không được âm.';
        }
        if (!in_array($data['trang_thai'], ['dang_ban', 'ngung_ban'], true)) {
            $errors['trang_thai'] = 'Trạng thái không hợp lệ.';
        }
        return $errors;
    }
}
