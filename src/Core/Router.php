<?php
namespace App\Core;

/**
 * Router
 * ------------------------------------------------------------------
 * Bộ định tuyến. Hỗ trợ tham số trên URL dạng {ten}, ví dụ:
 *   GET /users/edit/{id}  ->  UserController::edit($id)
 */
class Router
{
    // Mỗi route là: [method, path, handler]
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [$method, $path, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Bỏ tiền tố thư mục dự án (ví dụ '/Project_ck/public')
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base !== '' && strncmp($path, $base, strlen($base)) === 0) {
            $path = substr($path, strlen($base));
        }
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as [$m, $p, $handler]) {
            if ($m !== $method) {
                continue;
            }
            $params = $this->match($p, $path);
            if ($params !== null) {
                call_user_func($handler, ...$params);   // truyền tham số {id} vào hàm
                return;
            }
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>404 - Không tìm thấy</h1>';
        echo '<p>' . htmlspecialchars($method . ' ' . $path) . '</p>';
    }

    /**
     * So khớp route với path. Trả về mảng tham số, hoặc null nếu không khớp.
     * Ví dụ: route '/users/edit/{id}', path '/users/edit/5' -> ['5']
     */
    private function match(string $route, string $path): ?array
    {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts  = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (preg_match('#^\{.+\}$#', $part)) {
                $params[] = $pathParts[$i];       // {id} khớp bất kỳ 1 đoạn
            } elseif ($part !== $pathParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}
