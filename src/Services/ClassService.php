<?php
namespace App\Services;

use App\Repositories\ClassRepository;

class ClassService
{
    public function __construct(private ClassRepository $classRepository)
    {
    }

    public function getAll(): array
    {
        return $this->classRepository->listAll();
    }

    public function getById(int $id): ?array
    {
        return $this->classRepository->findRow($id);
    }

    public function create(array $input): array
    {
        $data = $this->normalize($input);
        $errors = $this->validate($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        $id = $this->classRepository->insert($data);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        if (!$this->classRepository->findRow($id)) {
            return ['ok' => false, 'not_found' => true, 'errors' => []];
        }
        $data = $this->normalize($input);
        $errors = $this->validate($data);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        $this->classRepository->update($id, $data);
        return ['ok' => true, 'errors' => []];
    }

    public function delete(int $id): bool
    {
        if (!$this->classRepository->findRow($id)) {
            return false;
        }
        $this->classRepository->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        return true;
    }

    private function normalize(array $input): array
    {
        return [
            'ten_lop'        => trim((string) ($input['ten_lop'] ?? '')),
            'giao_vien_id'   => (int) ($input['giao_vien_id'] ?? 0),
            'thu_trong_tuan' => (int) ($input['thu_trong_tuan'] ?? 0),
            'gio_bat_dau'    => trim((string) ($input['gio_bat_dau'] ?? '')),
            'gio_ket_thuc'   => trim((string) ($input['gio_ket_thuc'] ?? '')),
            'ngay_bat_dau'   => trim((string) ($input['ngay_bat_dau'] ?? '')),
            'ngay_ket_thuc'  => trim((string) ($input['ngay_ket_thuc'] ?? '')),
            'suc_chua'       => (int) ($input['suc_chua'] ?? 0),
            'phong_hoc'      => ($v = trim((string) ($input['phong_hoc'] ?? ''))) === '' ? null : $v,
            'mo_ta'          => ($v = trim((string) ($input['mo_ta'] ?? ''))) === '' ? null : $v,
            'trinh_do'       => ($v = trim((string) ($input['trinh_do'] ?? ''))) === '' ? null : $v,
            'trang_thai'     => trim((string) ($input['trang_thai'] ?? 'dang_mo')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (mb_strlen($data['ten_lop']) < 2 || mb_strlen($data['ten_lop']) > 100) {
            $errors['ten_lop'] = 'Tên lớp phải từ 2 đến 100 ký tự.';
        }
        if ($data['giao_vien_id'] <= 0) {
            $errors['giao_vien_id'] = 'Vui lòng chọn giáo viên.';
        }
        if ($data['thu_trong_tuan'] < 1 || $data['thu_trong_tuan'] > 7) {
            $errors['thu_trong_tuan'] = 'Thứ trong tuần không hợp lệ.';
        }
        foreach (['gio_bat_dau', 'gio_ket_thuc'] as $field) {
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data[$field])) {
                $errors[$field] = 'Giờ không hợp lệ.';
            }
        }
        if ($data['gio_bat_dau'] >= $data['gio_ket_thuc']) {
            $errors['gio_ket_thuc'] = 'Giờ kết thúc phải sau giờ bắt đầu.';
        }
        foreach (['ngay_bat_dau', 'ngay_ket_thuc'] as $field) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$field])) {
                $errors[$field] = 'Ngày không hợp lệ.';
            }
        }
        if ($data['suc_chua'] < 1 || $data['suc_chua'] > 200) {
            $errors['suc_chua'] = 'Sức chứa phải từ 1 đến 200.';
        }
        if (!in_array($data['trang_thai'], ['dang_mo', 'da_dong'], true)) {
            $errors['trang_thai'] = 'Trạng thái không hợp lệ.';
        }
        return $errors;
    }
}
