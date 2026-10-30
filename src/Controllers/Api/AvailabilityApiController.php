<?php
namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\InstructorRepository;
use App\Services\AvailabilityService;

class AvailabilityApiController
{
    use ApiResponseTrait;

    public function __construct(
        private AvailabilityService $availabilityService,
        private Auth $auth,
        private InstructorRepository $instructorRepository
    ) {
    }

    public function index(): void
    {
        $role = $this->auth->user()?->getVaiTro();
        if ($role === 'instructor') {
            $instructor = $this->instructorRepository->findByAccount($this->auth->user()?->getId() ?? 0);
            $data = $instructor
                ? $this->availabilityService->getForInstructor((int) $instructor['id'])
                : [];
        } else {
            $data = $this->availabilityService->getAll();
        }
        Response::json(['success' => true, 'data' => $data]);
    }

    public function store(): void
    {
        $instructor = $this->instructorRepository->findByAccount($this->auth->user()?->getId() ?? 0);
        if (!$instructor) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy hồ sơ giáo viên.'], 404);
        }
        $this->respond(
            $this->availabilityService->create((int) $instructor['id'], Request::json()),
            'Đã gửi yêu cầu lịch dạy.',
            201
        );
    }

    public function approve(string $id): void
    {
        $approver = $this->auth->user()?->getHoTen() ?? 'Quản trị viên';
        $this->respond(
            $this->availabilityService->approve((int) $id, $approver),
            'Đã duyệt lịch dạy.'
        );
    }

    public function reject(string $id): void
    {
        $input = Request::json();
        $approver = $this->auth->user()?->getHoTen() ?? 'Quản trị viên';
        $this->respond(
            $this->availabilityService->reject((int) $id, (string) ($input['ly_do'] ?? ''), $approver),
            'Đã từ chối lịch dạy.'
        );
    }

    public function cancel(string $id): void
    {
        $role = $this->auth->user()?->getVaiTro();
        $instructorId = null;
        if ($role === 'instructor') {
            $instructor = $this->instructorRepository->findByAccount($this->auth->user()?->getId() ?? 0);
            $instructorId = $instructor ? (int) $instructor['id'] : 0;
        }
        $this->respond(
            $this->availabilityService->cancel((int) $id, $instructorId),
            'Đã hủy yêu cầu lịch dạy.'
        );
    }
}
