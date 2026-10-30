<?php
namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * AuthController (API)
 * ------------------------------------------------------------------
 * POST /api/auth/login   đăng nhập
 * POST /api/auth/logout  đăng xuất
 * GET  /api/auth/me      thông tin tài khoản đang đăng nhập
 */
class AuthController
{
    public function __construct(private Auth $auth)
    {
    }

    public function login(): void
    {
        $data = Request::json();

        $tenDangNhap = is_string($data['ten_dang_nhap'] ?? null)
            ? trim($data['ten_dang_nhap'])
            : '';
        $matKhau = is_string($data['mat_khau'] ?? null) ? $data['mat_khau'] : '';

        if ($tenDangNhap === '' || $matKhau === '') {
            Response::json([
                'success' => false,
                'message' => 'Vui lòng nhập tên đăng nhập và mật khẩu.',
            ], 422);
        }

        if (!$this->auth->attempt($tenDangNhap, $matKhau)) {
            // Thông báo chung, không nói rõ sai tên hay sai mật khẩu
            Response::json([
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
            ], 401);
        }

        $user = $this->auth->user();

        Response::json([
            'success' => true,
            'data'    => [
                'id'            => $user?->getId(),
                'ten_dang_nhap' => $user?->getTenDangNhap(),
                'ho_ten'        => $user?->getHoTen(),
                'vai_tro'       => $user?->getVaiTro(),
            ],
            'message' => 'Đăng nhập thành công.',
        ]);
    }

    public function logout(): void
    {
        $this->auth->logout();

        Response::json([
            'success' => true,
            'message' => 'Đã đăng xuất.',
        ]);
    }

    public function me(): void
    {
        $user = $this->auth->user();

        Response::json([
            'success' => true,
            'data'    => [
                'id'            => $user?->getId(),
                'ten_dang_nhap' => $user?->getTenDangNhap(),
                'ho_ten'        => $user?->getHoTen(),
                'vai_tro'       => $user?->getVaiTro(),
            ],
        ]);
    }
}
