<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Page
 * ------------------------------------------------------------------
 * Render view con vào layout tương ứng.
 * Chỉ phục vụ GUI: không xử lý nghiệp vụ, không truy vấn CSDL.
 */
final class Page
{
    /**
     * Trang trong khu vực đã đăng nhập (sidebar + topbar).
     *
     * @param array<string,mixed> $data Biến truyền vào view
     * @param array<string,mixed> $meta title, active, breadcrumbs, actions, scripts
     */
    public static function app(string $role, string $view, array $data = [], array $meta = []): void
    {
        $content = self::capture($view, $data);
        $pageTitle = (string) ($meta['title'] ?? 'FitZone');
        $activeMenu = (string) ($meta['active'] ?? '');
        $breadcrumbs = (array) ($meta['breadcrumbs'] ?? []);
        $pageActions = (string) ($meta['actions'] ?? '');
        $pageScripts = (array) ($meta['scripts'] ?? []);
        $appRole = $role;

        require __DIR__ . '/../Views/layouts/app.php';
    }

    /**
     * Trang auth (login/register) — layout split 45/55.
     *
     * @param array<string,mixed> $data
     * @param array<string,mixed> $meta
     */
    public static function auth(string $view, array $data = [], array $meta = []): void
    {
        $content = self::capture($view, $data);
        $pageTitle = (string) ($meta['title'] ?? 'FitZone');
        $pageScripts = (array) ($meta['scripts'] ?? []);

        require __DIR__ . '/../Views/layouts/auth.php';
    }

    /**
     * Trang công khai — header + footer.
     *
     * @param array<string,mixed> $data
     * @param array<string,mixed> $meta
     */
    public static function public(string $view, array $data = [], array $meta = []): void
    {
        $content = self::capture($view, $data);
        $pageTitle = (string) ($meta['title'] ?? 'FitZone');
        $pageScripts = (array) ($meta['scripts'] ?? []);

        require __DIR__ . '/../Views/layouts/public.php';
    }

    /** @param array<string,mixed> $data */
    private static function capture(string $view, array $data): string
    {
        require_once __DIR__ . '/../Views/components/ui.php';

        $file = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            return '<div class="alert alert-danger">Không tìm thấy view: ' . e($view) . '</div>';
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
