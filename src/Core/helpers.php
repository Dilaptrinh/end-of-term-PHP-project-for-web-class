<?php
/**
 * helpers.php
 * ------------------------------------------------------------------
 * Các HÀM dùng chung (không phải class). Được require trong bootstrap.php.
 */

if (!function_exists('base_url')) {
    // Phần đường dẫn thư mục chứa index.php, ví dụ '/Project_ck/public'
    function base_url(): string
    {
        return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    }
}

if (!function_exists('url')) {
    // url('/users') -> '/Project_ck/public/users'
    function url(string $path = '/'): string
    {
        return base_url() . $path;
    }
}

if (!function_exists('redirect')) {
    // Chuyển hướng trình duyệt rồi dừng
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('e')) {
    // Escape khi in ra HTML (chống XSS)
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
