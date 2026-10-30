<?php
namespace App\Services;

use App\Models\GiaoVien;
use App\Repositories\InstructorRepository;
use App\Repositories\TaiKhoanRepository;

class InstructorService
{
    public function __construct(
        private InstructorRepository $instructorRepository,
        private TaiKhoanRepository $taiKhoanRepository
    ) {
    }

    public function getAll(): array
    {
        return $this->instructorRepository->getAll();
    }

    public function getById(int $id): ?GiaoVien
    {
        $instructor = $this->instructorRepository->find($id);
        return $instructor instanceof GiaoVien ? $instructor : null;
    }

    public function create(array $input): array
    {
        $data = $this->normalize($input);
        $errors = $this->validate($data, true);

        if (!$errors && $this->instructorRepository->emailExists($data['email'])) {
            $errors['email'] = 'Email đã được dùng cho giáo viên khác.';
        }
        if (!$errors && $this->taiKhoanRepository->existsByUsername($data['ten_dang_nhap'])) {
            $errors['ten_dang_nhap'] = 'Tên đăng nhập đã tồn tại.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $taiKhoanId = $this->taiKhoanRepository->insert([
            'ten_dang_nhap' => $data['ten_dang_nhap'],
            'mat_khau'      => password_hash($data['mat_khau'], PASSWORD_DEFAULT),
            'ho_ten'        => $data['ho_ten'],
            'vai_tro'       => 'instructor',
            'trang_thai'    => 1,
        ]);

        try {
            $id = $this->instructorRepository->save(new GiaoVien(
                null,
                $taiKhoanId,
                $data['ho_ten'],
                $data['sdt'],
                $data['email'],
                $data['chuyen_mon'],
                $data['gioi_thieu'],
                $data['trang_thai']
            ));
        } catch (\Throwable $e) {
            // Không để lại tài khoản mồ côi nếu tạo hồ sơ thất bại
            $this->taiKhoanRepository->delete($taiKhoanId);
            throw $e;
        }

        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        $data = $this->normalize($input);
        $errors = $this->validate($data, false);

        $instructor = $this->getById($id);
        if (!$instructor) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }

        if (!$errors && $this->instructorRepository->emailExists($data['email'], $id)) {
            $errors['email'] = 'Email đã được dùng cho giáo viên khác.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->instructorRepository->update($id, [
            'ho_ten'       => $data['ho_ten'],
            'sdt'          => $data['sdt'],
            'email'        => $data['email'],
            'chuyen_mon'   => $data['chuyen_mon'],
            'gioi_thieu'   => $data['gioi_thieu'],
            'trang_thai'   => $data['trang_thai'],
        ]);

        // Chỉ đổi mật khẩu khi admin nhập mật khẩu mới
        if ($data['mat_khau'] !== '') {
            $taiKhoanId = $instructor->getTaiKhoanId();
            if ($taiKhoanId !== null) {
                $this->taiKhoanRepository->updatePassword(
                    $taiKhoanId,
                    password_hash($data['mat_khau'], PASSWORD_DEFAULT)
                );
            }
        }

        return ['ok' => true, 'errors' => []];
    }

    public function delete(int $id): bool
    {
        $instructor = $this->getById($id);
        if (!$instructor) {
            return false;
        }

        $ok = $this->instructorRepository->delete($id);
        if ($ok && $instructor->getTaiKhoanId() !== null) {
            $this->taiKhoanRepository->delete($instructor->getTaiKhoanId());
        }

        return $ok;
    }

    private function normalize(array $input): array
    {
        $phone = trim((string) ($input['sdt'] ?? $input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        return [
            'ten_dang_nhap' => trim((string) ($input['ten_dang_nhap'] ?? $input['username'] ?? '')),
            'mat_khau'      => (string) ($input['mat_khau'] ?? $input['password'] ?? ''),
            'ho_ten'        => trim((string) ($input['ho_ten'] ?? $input['name'] ?? '')),
            'chuyen_mon'    => ($value = trim((string) ($input['chuyen_mon'] ?? $input['specialty'] ?? ''))) === '' ? null : $value,
            'gioi_thieu'    => ($value = trim((string) ($input['gioi_thieu'] ?? $input['introduction'] ?? ''))) === '' ? null : $value,
            'sdt'           => $phone,
            'email'         => $email,
            'trang_thai'    => trim((string) ($input['trang_thai'] ?? $input['status'] ?? 'dang_lam')),
        ];
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];

        if (mb_strlen($data['ho_ten']) < 2 || mb_strlen($data['ho_ten']) > 100) {
            $errors['ho_ten'] = 'Họ tên giáo viên phải từ 2 đến 100 ký tự.';
        }
        if ($data['chuyen_mon'] !== null && mb_strlen($data['chuyen_mon']) > 100) {
            $errors['chuyen_mon'] = 'Chuyên môn không được quá 100 ký tự.';
        }
        if ($data['sdt'] === '' || mb_strlen($data['sdt']) > 15) {
            $errors['sdt'] = 'Số điện thoại phải từ 1 đến 15 ký tự.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if (!in_array($data['trang_thai'], ['dang_lam', 'nghi'], true)) {
            $errors['trang_thai'] = 'Trạng thái không hợp lệ.';
        }

        if ($isCreate) {
            $errors += $this->validateAccount($data['ten_dang_nhap'], $data['mat_khau'], true);
        } elseif ($data['mat_khau'] !== '') {
            $errors += $this->validateAccount('', $data['mat_khau'], false);
        }

        return $errors;
    }

    /**
     * @return array<string,string>
     */
    private function validateAccount(string $tenDangNhap, string $matKhau, bool $requireUsername): array
    {
        $errors = [];

        if ($requireUsername) {
            $length = mb_strlen($tenDangNhap);
            if ($length < 3 || $length > 50) {
                $errors['ten_dang_nhap'] = 'Tên đăng nhập phải từ 3 đến 50 ký tự.';
            } elseif (!preg_match('/^[A-Za-z0-9._]+$/', $tenDangNhap)) {
                $errors['ten_dang_nhap'] = 'Tên đăng nhập chỉ gồm chữ, số, dấu chấm hoặc gạch dưới.';
            }
        }

        $length = strlen($matKhau);
        if ($length < 6 || $length > 72) {
            $errors['mat_khau'] = 'Mật khẩu phải từ 6 đến 72 ký tự.';
        }

        return $errors;
    }
}
