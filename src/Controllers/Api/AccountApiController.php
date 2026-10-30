<?php
namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Services\AccountService;

class AccountApiController
{
    use ApiResponseTrait;

    public function __construct(private AccountService $accountService, private Auth $auth)
    {
    }

    public function register(): void
    {
        $this->respond($this->accountService->register(Request::json()), 'Tạo tài khoản thành công.', 201);
    }

    public function updateProfile(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
        }
        $this->respond(
            $this->accountService->updateProfile($user->getId(), $user->getVaiTro(), Request::json()),
            'Đã cập nhật hồ sơ.'
        );
    }

    public function changePassword(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
        }
        $this->respond(
            $this->accountService->changePassword($user->getId(), Request::json()),
            'Đã đổi mật khẩu.'
        );
    }
}
