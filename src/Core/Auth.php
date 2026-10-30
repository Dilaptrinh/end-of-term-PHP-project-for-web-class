<?php
namespace App\Core;

use App\Models\TaiKhoan;
use App\Repositories\TaiKhoanRepository;

/**
 * Auth
 * ------------------------------------------------------------------
 * Xác thực + phân quyền đơn giản:
 *   - attempt(): kiểm tra mật khẩu bằng password_verify()
 *   - user(): mỗi request đọc lại tài khoản từ DB (khóa/xóa là mất quyền ngay)
 *   - check() / isAdmin()
 *   - logout(): hủy toàn bộ session
 *
 * Session chỉ giữ tai_khoan_id; KHÔNG giữ role/quyền để tránh bị sửa.
 */
class Auth
{
    private ?TaiKhoan $user = null;
    private bool $loaded = false;

    public function __construct(private TaiKhoanRepository $taiKhoanRepository)
    {
    }

    public function attempt(string $tenDangNhap, string $matKhau): bool
    {
        $row = $this->taiKhoanRepository->findCredentials(trim($tenDangNhap));

        // Không tìm thấy tài khoản vẫn chạy password_verify() với hash giả
        // để thời gian phản hồi không tiết lộ tài khoản có tồn tại hay không.
        if (!$row) {
            password_verify($matKhau, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
            return false;
        }

        $hash = (string) $row['mat_khau'];
        if (!password_verify($matKhau, $hash)) {
            return false;
        }

        if ((int) $row['trang_thai'] !== 1) {
            return false;
        }

        // Nâng cấp hash nếu PHP đổi thuật toán mặc định
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $this->taiKhoanRepository->updatePassword(
                (int) $row['id'],
                password_hash($matKhau, PASSWORD_DEFAULT)
            );
        }

        // Chống session fixation: đổi session id sau khi đăng nhập
        session_regenerate_id(true);
        $_SESSION['tai_khoan_id'] = (int) $row['id'];
        $_SESSION['_last_activity'] = time();

        $this->user   = null;
        $this->loaded = false;

        return true;
    }

    public function user(): ?TaiKhoan
    {
        if ($this->loaded) {
            return $this->user;
        }
        $this->loaded = true;

        $id = (int) ($_SESSION['tai_khoan_id'] ?? 0);
        if ($id <= 0) {
            return null;
        }

        $user = $this->taiKhoanRepository->find($id);

        // Tài khoản bị khóa/xóa -> coi như chưa đăng nhập
        if (!$user || !$user->isActive()) {
            $this->logout();
            return null;
        }

        return $this->user = $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function isAdmin(): bool
    {
        return $this->user()?->getVaiTro() === 'admin';
    }

    public function logout(): void
    {
        $this->user   = null;
        $this->loaded = true;
        Session::destroy();
    }
}
