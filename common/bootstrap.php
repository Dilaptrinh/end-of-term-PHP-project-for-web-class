<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/components/ui.php';
require_once __DIR__ . '/data/data.php';

function common_db(): mysqli
{
    static $db = null;
    if ($db === null) {
        $db = require __DIR__ . '/mysql/connect_mysql.php';
    }
    return $db;
}

function common_user(): ?array
{
    $id = (int) ($_SESSION['tai_khoan_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }

    $stmt = common_db()->prepare(
        'SELECT id, ten_dang_nhap, ho_ten, vai_tro, trang_thai FROM tai_khoan WHERE id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if (!$user || (int) $user['trang_thai'] !== 1) {
        unset($_SESSION['tai_khoan_id']);
        return null;
    }
    return $user;
}

function require_role(string $role): array
{
    $user = common_user();
    if (!$user) {
        header('Location: ' . common_url('/login.php'));
        exit;
    }
    if ($user['vai_tro'] !== $role) {
        header('Location: ' . common_url('/' . $user['vai_tro'] . '/index.php'));
        exit;
    }
return $user;
}
