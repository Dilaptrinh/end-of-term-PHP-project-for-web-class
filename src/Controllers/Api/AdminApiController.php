<?php
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\RegistrationRepository;
use App\Services\ClassService;
use App\Services\MemberService;
use App\Services\PackageService;

class AdminApiController
{
    public function __construct(
        private MemberService $memberService,
        private PackageService $packageService,
        private ClassService $classService,
        private RegistrationRepository $registrationRepository
    ) {
    }

    public function members(): void { Response::json(['success' => true, 'data' => $this->memberService->getAll()]); }
    public function member(string $id): void
    {
        $row = $this->memberService->getById((int) $id);
        $row ? Response::json(['success' => true, 'data' => $row]) : $this->notFound('Không tìm thấy hội viên.');
    }
    public function createMember(): void { $this->writeService($this->memberService, 'create', 'Đã tạo hội viên.'); }
    public function updateMember(string $id): void { $this->writeService($this->memberService, 'update', 'Đã cập nhật hội viên.', (int) $id); }
    public function deleteMember(string $id): void { $this->deleteService($this->memberService, (int) $id, 'hội viên'); }

    public function packages(): void { Response::json(['success' => true, 'data' => $this->packageService->getAll()]); }
    public function package(string $id): void
    {
        $row = $this->packageService->getById((int) $id);
        $row ? Response::json(['success' => true, 'data' => $row]) : $this->notFound('Không tìm thấy gói tập.');
    }
    public function createPackage(): void { $this->writeService($this->packageService, 'create', 'Đã tạo gói tập.'); }
    public function updatePackage(string $id): void { $this->writeService($this->packageService, 'update', 'Đã cập nhật gói tập.', (int) $id); }
    public function deletePackage(string $id): void { $this->deleteService($this->packageService, (int) $id, 'gói tập'); }

    public function classes(): void { Response::json(['success' => true, 'data' => $this->classService->getAll()]); }
    public function class(string $id): void
    {
        $row = $this->classService->getById((int) $id);
        $row ? Response::json(['success' => true, 'data' => $row]) : $this->notFound('Không tìm thấy lớp học.');
    }
    public function createClass(): void { $this->writeService($this->classService, 'create', 'Đã tạo lớp học.'); }
    public function updateClass(string $id): void { $this->writeService($this->classService, 'update', 'Đã cập nhật lớp học.', (int) $id); }
    public function deleteClass(string $id): void { $this->deleteService($this->classService, (int) $id, 'lớp học'); }

    public function packageRegistrations(): void
    {
        Response::json(['success' => true, 'data' => $this->registrationRepository->listPackageRegistrations()]);
    }

    public function classRegistrations(): void
    {
        Response::json(['success' => true, 'data' => $this->registrationRepository->listClassRegistrations()]);
    }

    public function updatePackageRegistration(string $id): void
    {
        $this->setRegistrationStatus((int) $id, true);
    }

    public function updateClassRegistration(string $id): void
    {
        $this->setRegistrationStatus((int) $id, false);
    }

    private function writeService(object $service, string $operation, string $message, ?int $id = null): void
    {
        try {
            $result = $operation === 'create'
                ? $service->create(Request::json())
                : $service->update($id ?? 0, Request::json());
        } catch (\Throwable $e) {
            error_log('[api/admin] ' . $e->getMessage());
            Response::json(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau.'], 500);
        }
        if (!empty($result['not_found'])) {
            $this->notFound('Không tìm thấy dữ liệu.');
        }
        if (!$result['ok']) {
            Response::json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $result['errors']], 422);
        }
        Response::json([
            'success' => true,
            'message' => $message,
            'data' => $operation === 'create' ? ['id' => $result['id']] : null,
        ], $operation === 'create' ? 201 : 200);
    }

    private function deleteService(object $service, int $id, string $label): void
    {
        if (!$service->delete($id)) {
            $this->notFound('Không tìm thấy ' . $label . '.');
        }
        Response::json(['success' => true, 'message' => 'Đã xóa ' . $label . '.']);
    }

    private function setRegistrationStatus(int $id, bool $package): void
    {
        $data = Request::json();
        $status = is_string($data['trang_thai'] ?? null) ? $data['trang_thai'] : '';
        $allowed = $package ? ['chua_thanh_toan', 'da_thanh_toan', 'da_huy'] : ['dang_ky', 'da_huy'];
        if (!in_array($status, $allowed, true)) {
            Response::json(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], 422);
        }
        $row = $package
            ? $this->registrationRepository->findPackageRegistration($id)
            : $this->registrationRepository->findClassRegistration($id);
        if (!$row) {
            $this->notFound('Không tìm thấy đăng ký.');
        }
        if ($package) {
            $method = is_string($data['phuong_thuc'] ?? null) ? trim($data['phuong_thuc']) : null;
            $this->registrationRepository->setPackageStatus($id, $status, $method ?: null);
        } else {
            $this->registrationRepository->setClassStatus($id, $status);
        }
        Response::json(['success' => true, 'message' => 'Đã cập nhật trạng thái đăng ký.']);
    }

    private function notFound(string $message): never
    {
        Response::json(['success' => false, 'message' => $message], 404);
    }
}
