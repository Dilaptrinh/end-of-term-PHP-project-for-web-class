<?php
namespace App\Core;

/**
 * View
 * ------------------------------------------------------------------
 * Nạp file HTML trong src/Views/ và truyền dữ liệu vào.
 * View::render('UserView', ['users' => $mang]);
 */
class View
{
    public static function render(string $template, array $data = []): void
    {
        // Biến mỗi phần tử của mảng thành một biến
        // ['users' => [...]]  ->  $users = [...]
        extract($data, EXTR_SKIP);

        $file = __DIR__ . '/../Views/' . $template . '.php';
        if (!is_file($file)) {
            die('Không tìm thấy view: ' . htmlspecialchars($template));
        }

        require $file;
    }
}
