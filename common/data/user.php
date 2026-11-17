<?php
declare(strict_types=1);

/*
 * Thông tin người đang đăng nhập cho khung app.
 * Tách khỏi menu để topbar không phải kéo theo logic menu.
 */

if (!function_exists('app_user')) {
    /** @return array<string,string> */
    function app_user(string $role): array
    {
        $labels = [
            'admin' => 'Quản trị viên',
            'instructor' => 'Giáo viên',
            'customer' => 'Hội viên',
        ];

        $name = '';
        $email = '';
        $account = common_user();

        if ($account) {
            $name = (string) $account['ho_ten'];
            $id = (int) $account['id'];
            $conn = view_db();

            if ($role === 'instructor') {
                $stmt = $conn->prepare('SELECT email FROM giao_vien WHERE tai_khoan_id = ? LIMIT 1');
            } elseif ($role === 'customer') {
                $stmt = $conn->prepare('SELECT email FROM hoi_vien WHERE tai_khoan_id = ? LIMIT 1');
            } else {
                $stmt = null;
            }

            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $profile = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $email = (string) ($profile['email'] ?? '');
            }

            if ($email === '') {
                $email = $account['ten_dang_nhap'] . '@fitzone.vn';
            }
        }

        if ($name === '') {
            $name = match ($role) {
                'admin' => 'Quản trị viên',
                'instructor' => 'Giáo viên',
                default => 'Hội viên',
            };
        }

        return [
            'name'  => $name,
            'sub'   => $labels[$role] ?? 'Thành viên',
            'email' => $email,
        ];
    }
}


