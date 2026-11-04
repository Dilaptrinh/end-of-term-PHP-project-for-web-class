<?php
declare(strict_types=1);

function common_root_url(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $directory = trim(basename(dirname($script)), '/');
    if (in_array($directory, ['admin', 'customer', 'instructor', 'common'], true)) {
        return rtrim(dirname(dirname($script)), '/');
    }
    return rtrim(dirname($script), '/');
}

function common_url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');
    $map = [
        '/' => '/index.php',
        '/login' => '/login.php',
        '/register' => '/register.php',
        '/admin' => '/admin/index.php',
        '/customer' => '/customer/index.php',
        '/instructor' => '/instructor/index.php',
    ];
    if (isset($map[$path])) {
        $path = $map[$path];
    } elseif (
        !str_contains($path, '.')
        && !str_contains($path, '#')
        && !str_starts_with($path, '/common/')
    ) {
        $path .= '.php';
    }
    return common_root_url() . $path;
}

function base_url(): string
{
    return common_root_url();
}

function url(string $path = '/'): string
{
    return common_url($path);
}

function redirect(string $path): never
{
    header('Location: ' . common_url($path));
    exit;
}

function json_response(array $body, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
