<?php
namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\MemberRepository;
use App\Services\RegistrationService;

class RegistrationApiController
{
    use ApiResponseTrait;

    public function __construct(
        private RegistrationService $registrationService,
        private Auth $auth,
        private MemberRepository $memberRepository
    ) {
    }

    public function indexPackages(): void
    {
        Response::json(['success' => true, 'data' => $this->registrationService->getPackageRegistrations()]);
    }

    public function indexClasses(): void
    {
        Response::json(['success' => true, 'data' => $this->registrationService->getClassRegistrations()]);
    }

    public function storePackage(): void
    {
        $this->respond(
            $this->registrationService->createPackageRegistration(Request::json()),
            'Đã tạo đăng ký gói.',
            201
        );
    }

    public function confirmPackage(string $id): void
    {
        $input = Request::json();
        $method = trim((string) ($input['phuong_thuc'] ?? '')) ?: null;
        $this->respond(
            $this->registrationService->confirmPackageRegistration((int) $id, $method),
            'Đã xác nhận thanh toán.'
        );
    }

    public function cancelPackage(string $id): void
    {
        $this->respond(
            $this->registrationService->cancelPackageRegistration((int) $id),
            'Đã hủy đăng ký gói.'
        );
    }

    public function storeClass(): void
    {
        $accountId = $this->auth->user()?->getId() ?? 0;
        $member = $this->memberRepository->findByAccount($accountId);
        if (!$member) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy hồ sơ hội viên.'], 404);
        }
        $input = Request::json();
        $this->respond(
            $this->registrationService->createClassRegistration((int) $member['id'], (int) ($input['lop_hoc_id'] ?? 0)),
            'Đã đăng ký lớp học.',
            201
        );
    }

    public function cancelClass(string $id): void
    {
        $role = $this->auth->user()?->getVaiTro();
        $memberId = null;
        if ($role === 'customer') {
            $member = $this->memberRepository->findByAccount($this->auth->user()?->getId() ?? 0);
            $memberId = $member ? (int) $member['id'] : 0;
        }
        $this->respond(
            $this->registrationService->cancelClassRegistration((int) $id, $memberId),
            'Đã hủy đăng ký lớp.'
        );
    }
}
