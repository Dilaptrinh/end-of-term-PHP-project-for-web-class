<?php
namespace App\Services;

use App\Repositories\ClassRepository;
use App\Repositories\PackageRepository;
use App\Repositories\RegistrationRepository;

class RegistrationService
{
    public function __construct(
        private RegistrationRepository $registrationRepository,
        private PackageRepository $packageRepository,
        private ClassRepository $classRepository
    ) {
    }

    public function getPackageRegistrations(): array
    {
        return $this->registrationRepository->listPackageRegistrations();
    }

    public function getClassRegistrations(): array
    {
        return $this->registrationRepository->listClassRegistrations();
    }

    public function createPackageRegistration(array $input): array
    {
        $memberId = (int) ($input['hoi_vien_id'] ?? 0);
        $packageId = (int) ($input['goi_tap_id'] ?? 0);
        $from = trim((string) ($input['ngay_bat_dau'] ?? ''));
        $to = trim((string) ($input['ngay_ket_thuc'] ?? ''));
        $method = trim((string) ($input['phuong_thuc_thanh_toan'] ?? 'tien_mat'));
        $methodMap = ['tien_mat' => 'Tiền mặt', 'chuyen_khoan' => 'Chuyển khoản', 'the' => 'Thẻ'];

        $errors = [];
        if ($memberId <= 0) {
            $errors['hoi_vien_id'] = 'Vui lòng chọn hội viên.';
        }
        if ($packageId <= 0) {
            $errors['goi_tap_id'] = 'Vui lòng chọn gói tập.';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $errors['ngay_bat_dau'] = 'Ngày không hợp lệ.';
        } elseif ($to <= $from) {
            $errors['ngay_ket_thuc'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
        }
        $package = $packageId > 0 ? $this->packageRepository->find($packageId) : null;
        if (!$package) {
            $errors['goi_tap_id'] = 'Gói tập không tồn tại.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->registrationRepository->insert([
            'hoi_vien_id'       => $memberId,
            'goi_tap_id'        => $packageId,
            'ngay_bat_dau'      => $from,
            'ngay_ket_thuc'     => $to,
            'gia_tai_thoi_diem' => (int) ($package->gia ?? 0),
            'trang_thai'        => 'chua_thanh_toan',
            'phuong_thuc'       => $methodMap[$method] ?? 'Tiền mặt',
        ]);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function confirmPackageRegistration(int $id, ?string $method = null): array
    {
        if (!$this->registrationRepository->findPackageRegistration($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        $method = $method ?: 'Tiền mặt';
        $this->registrationRepository->setPackageStatus($id, 'da_thanh_toan', $method);
        return ['ok' => true, 'errors' => []];
    }

    public function cancelPackageRegistration(int $id): array
    {
        if (!$this->registrationRepository->findPackageRegistration($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        $this->registrationRepository->setPackageStatus($id, 'da_huy');
        return ['ok' => true, 'errors' => []];
    }

    public function createClassRegistration(int $memberId, int $classId): array
    {
        $class = $this->classRepository->findRow($classId);
        if (!$class) {
            return ['ok' => false, 'errors' => ['lop_hoc_id' => 'Lớp học không tồn tại.']];
        }
        if ($class['trang_thai'] !== 'dang_mo') {
            return ['ok' => false, 'errors' => ['lop_hoc_id' => 'Lớp học đã đóng.']];
        }
        if ($this->registrationRepository->hasClassRegistration($memberId, $classId)) {
            return ['ok' => false, 'errors' => ['lop_hoc_id' => 'Bạn đã đăng ký lớp này.']];
        }
        if ($this->registrationRepository->countEnrolled($classId) >= (int) $class['suc_chua']) {
            return ['ok' => false, 'errors' => ['lop_hoc_id' => 'Lớp học đã đầy.']];
        }

        $id = $this->registrationRepository->createClassRegistration([
            'hoi_vien_id'  => $memberId,
            'lop_hoc_id'   => $classId,
            'ngay_dang_ky' => date('Y-m-d'),
            'trang_thai'   => 'dang_ky',
        ]);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function cancelClassRegistration(int $id, ?int $memberId = null): array
    {
        $row = $this->registrationRepository->findClassRegistration($id);
        if (!$row) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        if ($memberId !== null && (int) $row['hoi_vien_id'] !== $memberId) {
            return ['ok' => false, 'forbidden' => true, 'errors' => []];
        }
        $this->registrationRepository->setClassStatus($id, 'da_huy');
        return ['ok' => true, 'errors' => []];
    }
}
