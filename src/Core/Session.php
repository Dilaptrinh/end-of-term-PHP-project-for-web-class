<?php
namespace App\Core;

/**
 * Session
 * ------------------------------------------------------------------
 * Khởi động PHP session với cấu hình an toàn:
 *   - Cookie HttpOnly, SameSite=Lax, Secure khi chạy HTTPS
 *   - use_strict_mode: không nhận session id do client tự bịa
 *   - Tự hết hạn sau 30 phút không hoạt động
 */
class Session
{
    private const IDLE_TIMEOUT = 1800; // 30 phút

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', '1');
        }

        session_name('pt_session');
        session_start();

        // Trang phụ thuộc session không được cache lại
        header('Cache-Control: no-store');

        $now  = time();
        $last = (int) ($_SESSION['_last_activity'] ?? 0);

        // Quá lâu không hoạt động -> hủy session cũ, tạo session mới
        if ($last > 0 && ($now - $last) > self::IDLE_TIMEOUT) {
            self::destroy();
            session_start();
        }

        $_SESSION['_last_activity'] = $now;
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        session_destroy();
    }
}
