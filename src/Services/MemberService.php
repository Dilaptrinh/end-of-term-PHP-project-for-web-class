<?php
namespace App\Services;

use App\Repositories\MemberRepository;

class MemberService
{
    public function __construct(private MemberRepository $memberRepository)
    {
    }

    public function getAll(): array
    {
        return $this->memberRepository->listAll();
    }

    public function getById(int $id): ?array
    {
        $row = $this->memberRepository->find($id);
        return $row ? (array) $row : null;
    }

    public function create(array $input): array
    {
        $data = $this->normalize($input);
        $errors = $this->validate($data);
        if (!$errors && $this->memberRepository->emailExists($data['email'])) {
            $errors['email'] = 'Email đã được dùng cho hội viên khác.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->memberRepository->insert($data);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        if (!$this->memberRepository->find($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }

        $data = $this->normalize($input);
        $errors = $this->validate($data);
        if (!$errors && $this->memberRepository->emailExists($data['email'], $id)) {
            $errors['email'] = 'Email đã được dùng cho hội viên khác.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->memberRepository->update($id, $data);
        return ['ok' => true, 'errors' => []];
    }

    public function delete(int $id): bool
    {
        if (!$this->memberRepository->find($id)) {
            return false;
        }
        $this->memberRepository->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        return true;
    }

    private function normalize(array $input): array
    {
        $gender = trim((string) ($input['gioi_tinh'] ?? 'khac'));
        return [
            'ho_ten'     => trim((string) ($input['ho_ten'] ?? '')),
            'sdt'        => trim((string) ($input['sdt'] ?? '')),
            'email'      => trim((string) ($input['email'] ?? '')),
            'gioi_tinh'  => in_array($gender, ['nam', 'nu', 'khac'], true) ? $gender : 'khac',
            'ngay_sinh'  => ($v = trim((string) ($input['ngay_sinh'] ?? ''))) === '' ? null : $v,
            'dia_chi'    => ($v = trim((string) ($input['dia_chi'] ?? ''))) === '' ? null : $v,
            'ghi_chu'    => ($v = trim((string) ($input['ghi_chu'] ?? ''))) === '' ? null : $v,
            'trang_thai' => trim((string) ($input['trang_thai'] ?? 'hoat_dong')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['ho_ten']) < 2 || mb_strlen($data['ho_ten']) > 100) {
            $errors['ho_ten'] = 'Họ tên phải từ 2 đến 100 ký tự.';
        }
        if ($data['sdt'] === '' || mb_strlen($data['sdt']) > 15) {
            $errors['sdt'] = 'Số điện thoại không hợp lệ.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if (!in_array($data['trang_thai'], ['hoat_dong', 'ngung_hoat_dong'], true)) {
            $errors['trang_thai'] = 'Trạng thái không hợp lệ.';
        }
        return $errors;
    }
}
