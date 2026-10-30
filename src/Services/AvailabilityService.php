<?php
namespace App\Services;

use App\Repositories\AvailabilityRepository;

class AvailabilityService
{
    public function __construct(private AvailabilityRepository $availabilityRepository)
    {
    }

    public function getAll(): array
    {
        return $this->availabilityRepository->listAll();
    }

    public function getForInstructor(int $instructorId): array
    {
        return $this->availabilityRepository->listForInstructor($instructorId);
    }

    public function create(int $instructorId, array $input): array
    {
        $date = trim((string) ($input['ngay'] ?? $input['ngay_day'] ?? ''));
        $start = trim((string) ($input['gio_bat_dau'] ?? ''));
        $end = trim((string) ($input['gio_ket_thuc'] ?? ''));
        $note = trim((string) ($input['ghi_chu'] ?? ''));

        $errors = [];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['ngay'] = 'Ngày không hợp lệ.';
        }
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end)) {
            $errors['gio_bat_dau'] = 'Giờ không hợp lệ.';
        } elseif ($end <= $start) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải sau giờ bắt đầu.';
        }
        if ($instructorId <= 0) {
            $errors['giao_vien_id'] = 'Không xác định được giáo viên.';
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->availabilityRepository->insert([
            'giao_vien_id' => $instructorId,
            'ngay'         => $date,
            'gio_bat_dau'  => $start,
            'gio_ket_thuc' => $end,
            'trang_thai'   => 'cho_duyet',
            'ghi_chu'      => $note === '' ? null : $note,
        ]);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function approve(int $id, string $approver): array
    {
        if (!$this->availabilityRepository->findRow($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        $this->availabilityRepository->setStatus($id, 'da_duyet', null, $approver);
        return ['ok' => true, 'errors' => []];
    }

    public function reject(int $id, string $reason, string $approver): array
    {
        if (!$this->availabilityRepository->findRow($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        if (mb_strlen(trim($reason)) < 3) {
            return ['ok' => false, 'errors' => ['ly_do' => 'Vui lòng nhập lý do từ chối.']];
        }
        $this->availabilityRepository->setStatus($id, 'tu_choi', trim($reason), $approver);
        return ['ok' => true, 'errors' => []];
    }

    public function cancel(int $id, ?int $instructorId = null): array
    {
        $row = $this->availabilityRepository->findRow($id);
        if (!$row) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        if ($instructorId !== null && (int) $row['giao_vien_id'] !== $instructorId) {
            return ['ok' => false, 'forbidden' => true, 'errors' => []];
        }
        if ($row['trang_thai'] === 'da_duyet') {
            return ['ok' => false, 'errors' => ['trang_thai' => 'Không thể hủy lịch đã duyệt.']];
        }
        $this->availabilityRepository->setStatus($id, 'da_huy');
        return ['ok' => true, 'errors' => []];
    }
}
