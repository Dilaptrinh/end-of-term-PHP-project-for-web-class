<?php
namespace App\Services;

use App\Repositories\InstructorRepository;
use App\Repositories\MemberRepository;
use App\Repositories\TaiKhoanRepository;

class AccountService
{
    public function __construct(
        private TaiKhoanRepository $taiKhoanRepository,
        private MemberRepository $memberRepository,
        private InstructorRepository $instructorRepository
    ) {
    }

    public function register(array $input): array
    {
        $data = [
            'ten_dang_nhap' => trim((string) ($input['ten_dang_nhap'] ?? '')),
            'mat_khau'      => (string) ($input['mat_khau'] ?? ''),
            'xac_nhan'      => (string) ($input['mat_khau_xac_nhan'] ?? ''),
            'ho_ten'        => trim((string) ($input['ho_ten'] ?? '')),
            'sdt'           => trim((string) ($input['sdt'] ?? '')),
            'email'         => trim((string) ($input['email'] ?? '')),
            'gioi_tinh'     => trim((string) ($input['gioi_tinh'] ?? 'khac')),
            'ngay_sinh'     => trim((string) ($input['ngay_sinh'] ?? '')),
            'dia_chi'       => trim((string) ($input['dia_chi'] ?? '')),
        ];

        $errors = $this->validateRegister($data);
        if (!$errors && $this->taiKhoanRepository->existsByUsername($data['ten_dang_nhap'])) {
            $errors['ten_dang_nhap'] = 'Tên đăng nhập đã tồn tại.';
        }
        if (!$errors && $this->memberRepository->emailExists($data['email'])) {
            $errors['email'] = 'Email đã được sử dụng.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $accountId = $this->taiKhoanRepository->insert([
            'ten_dang_nhap' => $data['ten_dang_nhap'],
            'mat_khau'      => password_hash($data['mat_khau'], PASSWORD_DEFAULT),
            'ho_ten'        => $data['ho_ten'],
            'vai_tro'       => 'customer',
            'trang_thai'    => 1,
        ]);

        try {
            $this->memberRepository->insert([
                'tai_khoan_id' => $accountId,
                'ho_ten'       => $data['ho_ten'],
                'sdt'          => $data['sdt'],
                'email'        => $data['email'],
                'gioi_tinh'    => in_array($data['gioi_tinh'], ['nam', 'nu', 'khac'], true) ? $data['gioi_tinh'] : 'khac',
                'ngay_sinh'    => $data['ngay_sinh'] === '' ? null : $data['ngay_sinh'],
                'dia_chi'      => $data['dia_chi'] === '' ? null : $data['dia_chi'],
                'trang_thai'   => 'hoat_dong',
            ]);
        } catch (\Throwable $e) {
            $this->taiKhoanRepository->delete($accountId);
            throw $e;
        }

        return ['ok' => true, 'errors' => [], 'id' => $accountId];
    }

    public function updateProfile(int $accountId, string $role, array $input): array
    {
        if ($role === 'customer') {
            $member = $this->memberRepository->findByAccount($accountId);
            if (!$member) {
                return ['ok' => false, 'not_found' => true, 'errors' => []];
            }
            $email = trim((string) ($input['email'] ?? ''));
            $errors = [];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            }
            if (!$errors && $this->memberRepository->emailExists($email, (int) $member['id'])) {
                $errors['email'] = 'Email đã được dùng.';
            }
            if ($errors) {
                return ['ok' => false, 'errors' => $errors];
            }
            $this->memberRepository->update((int) $member['id'], [
                'ho_ten'     => trim((string) ($input['ho_ten'] ?? $member['ho_ten'])),
                'sdt'        => trim((string) ($input['sdt'] ?? $member['sdt'])),
                'email'      => $email,
                'gioi_tinh'  => trim((string) ($input['gioi_tinh'] ?? $member['gioi_tinh'])),
                'ngay_sinh'  => ($v = trim((string) ($input['ngay_sinh'] ?? ''))) === '' ? null : $v,
                'dia_chi'    => ($v = trim((string) ($input['dia_chi'] ?? ''))) === '' ? null : $v,
                'ghi_chu'    => ($v = trim((string) ($input['ghi_chu'] ?? ''))) === '' ? null : $v,
                'can_nang'   => ($v = trim((string) ($input['can_nang'] ?? ''))) === '' ? null : (float) $v,
                'chieu_cao'  => ($v = trim((string) ($input['chieu_cao'] ?? ''))) === '' ? null : (float) $v,
                'muc_tieu'   => ($v = trim((string) ($input['muc_tieu'] ?? ''))) === '' ? null : $v,
            ]);
            return ['ok' => true, 'errors' => []];
        }

        if ($role === 'instructor') {
            $instructor = $this->instructorRepository->findByAccount($accountId);
            if (!$instructor) {
                return ['ok' => false, 'not_found' => true, 'errors' => []];
            }
            $email = trim((string) ($input['email'] ?? ''));
            $errors = [];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email không hợp lệ.';
            }
            if (!$errors && $this->instructorRepository->emailExists($email, (int) $instructor['id'])) {
                $errors['email'] = 'Email đã được dùng.';
            }
            if ($errors) {
                return ['ok' => false, 'errors' => $errors];
            }
            $this->instructorRepository->update((int) $instructor['id'], [
                'ho_ten'     => trim((string) ($input['ho_ten'] ?? $instructor['ho_ten'])),
                'sdt'        => trim((string) ($input['sdt'] ?? $instructor['sdt'])),
                'email'      => $email,
                'chuyen_mon' => ($v = trim((string) ($input['chuyen_mon'] ?? ''))) === '' ? null : $v,
                'gioi_thieu' => ($v = trim((string) ($input['gioi_thieu'] ?? ''))) === '' ? null : $v,
            ]);
            return ['ok' => true, 'errors' => []];
        }

        return ['ok' => false, 'errors' => ['role' => 'Vai trò không hỗ trợ.']];
    }

    public function changePassword(int $accountId, array $input): array
    {
        $current = (string) ($input['mat_khau_cu'] ?? '');
        $new = (string) ($input['mat_khau_moi'] ?? '');
        $confirm = (string) ($input['mat_khau_xac_nhan'] ?? '');

        $errors = [];
        $hash = $this->taiKhoanRepository->findHashById($accountId);
        if (!$hash || !password_verify($current, $hash)) {
            $errors['mat_khau_cu'] = 'Mật khẩu hiện tại không đúng.';
        }
        if (strlen($new) < 6 || strlen($new) > 72) {
            $errors['mat_khau_moi'] = 'Mật khẩu mới phải từ 6 đến 72 ký tự.';
        }
        if ($confirm !== '' && $new !== $confirm) {
            $errors['mat_khau_xac_nhan'] = 'Mật khẩu xác nhận không khớp.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->taiKhoanRepository->updatePassword($accountId, password_hash($new, PASSWORD_DEFAULT));
        return ['ok' => true, 'errors' => []];
    }

    private function validateRegister(array $data): array
    {
        $errors = [];
        $length = mb_strlen($data['ten_dang_nhap']);
        if ($length < 3 || $length > 50) {
            $errors['ten_dang_nhap'] = 'Tên đăng nhập phải từ 3 đến 50 ký tự.';
        } elseif (!preg_match('/^[A-Za-z0-9._]+$/', $data['ten_dang_nhap'])) {
            $errors['ten_dang_nhap'] = 'Tên đăng nhập chỉ gồm chữ, số, dấu chấm hoặc gạch dưới.';
        }
        if (mb_strlen($data['ho_ten']) < 2) {
            $errors['ho_ten'] = 'Họ tên không hợp lệ.';
        }
        if ($data['sdt'] === '' || mb_strlen($data['sdt']) > 15) {
            $errors['sdt'] = 'Số điện thoại không hợp lệ.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }
        if (strlen($data['mat_khau']) < 6 || strlen($data['mat_khau']) > 72) {
            $errors['mat_khau'] = 'Mật khẩu phải từ 6 đến 72 ký tự.';
        }
        if ($data['xac_nhan'] !== '' && $data['xac_nhan'] !== $data['mat_khau']) {
            $errors['mat_khau_xac_nhan'] = 'Mật khẩu xác nhận không khớp.';
        }
        return $errors;
    }
}
