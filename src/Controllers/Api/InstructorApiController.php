<?php
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\GiaoVien;
use App\Services\InstructorService;

/**
 * InstructorApiController
 * ------------------------------------------------------------------
 * CRUD giáo viên qua API:
 *   GET    /api/instructors       -> danh sách
 *   GET    /api/instructors/{id}  -> chi tiết
 *   POST   /api/instructors       -> tạo hồ sơ + tài khoản đăng nhập
 *   PUT    /api/instructors/{id}  -> cập nhật (mật khẩu tùy chọn)
 *   DELETE /api/instructors/{id}  -> xóa
 */
class InstructorApiController
{
    public function __construct(private InstructorService $instructorService)
    {
    }

    public function index(): void
    {
        $data = array_map(
            fn (GiaoVien $giaoVien) => $this->serialize($giaoVien),
            $this->instructorService->getAll()
        );
        Response::json(['success' => true, 'data' => $data]);
    }

    public function show(string $id): void
    {
        $giaoVien = $this->instructorService->getById((int) $id);
        if (!$giaoVien) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy giáo viên.'], 404);
        }

        Response::json(['success' => true, 'data' => $this->serialize($giaoVien)]);
    }

    public function store(): void
    {
        $this->write('create');
    }

    public function update(string $id): void
    {
        $this->write('update', (int) $id);
    }

    public function destroy(string $id): void
    {
        if (!$this->instructorService->delete((int) $id)) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy giáo viên.'], 404);
        }

        Response::json(['success' => true, 'message' => 'Đã xóa giáo viên.']);
    }

    private function write(string $operation, ?int $id = null): void
    {
        $input = Request::json();

        try {
            $result = $operation === 'create'
                ? $this->instructorService->create($input)
                : $this->instructorService->update($id ?? 0, $input);
        } catch (\Throwable $e) {
            error_log('[api/instructors] ' . $e->getMessage());
            Response::json(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau.'], 500);
        }

        if (!empty($result['not_found'])) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy giáo viên.'], 404);
        }
        if (!$result['ok']) {
            Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $result['errors'],
            ], 422);
        }

        Response::json([
            'success' => true,
            'message' => $operation === 'create' ? 'Đã tạo giáo viên.' : 'Đã cập nhật giáo viên.',
            'data'    => $operation === 'create' ? ['id' => $result['id']] : null,
        ], $operation === 'create' ? 201 : 200);
    }

    private function serialize(GiaoVien $giaoVien): array
    {
        return [
            'id'            => $giaoVien->getId(),
            'tai_khoan_id'  => $giaoVien->getTaiKhoanId(),
            'ten_dang_nhap' => $giaoVien->getTenDangNhap(),
            'ho_ten'        => $giaoVien->getHoTen(),
            'sdt'           => $giaoVien->getSdt(),
            'email'         => $giaoVien->getEmail(),
            'chuyen_mon'    => $giaoVien->getChuyenMon(),
            'gioi_thieu'    => $giaoVien->getGioiThieu(),
            'trang_thai'    => $giaoVien->getTrangThai(),
        ];
    }
}
