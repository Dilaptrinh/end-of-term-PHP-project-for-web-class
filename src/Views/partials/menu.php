<?php
declare(strict_types=1);

if (!function_exists('app_menu')) {
    /**
     * Menu theo role. Mỗi item: label, icon, href, key, badge?, group?
     *
     * @return array<int,array<string,mixed>>
     */
    function app_menu(string $role): array
    {
        if (!in_array($role, ['admin', 'instructor', 'customer'], true)) {
            return [];
        }

        $file = __DIR__ . '/menu/' . $role . '.php';
        return is_file($file) ? (require $file) : [];
    }
}

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
        $id = (int) ($_SESSION['tai_khoan_id'] ?? 0);

        if ($id > 0) {
            $conn = view_db();
            $stmt = $conn->prepare('SELECT ten_dang_nhap, ho_ten, vai_tro FROM tai_khoan WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $account = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($account) {
                $name = (string) $account['ho_ten'];

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

if (!function_exists('app_notifications')) {
    /**
     * @return array<int,array<string,string>>
     */
    function app_notifications(string $role): array
    {
        $conn = view_db();

        if ($role === 'admin') {
            $pending = (int) $conn->query("SELECT COUNT(*) AS c FROM lich_ranh WHERE trang_thai = 'cho_duyet'")->fetch_assoc()['c'];
            $unpaid = (int) $conn->query("SELECT COUNT(*) AS c FROM dang_ky_goi WHERE trang_thai = 'chua_thanh_toan'")->fetch_assoc()['c'];
            $latest = $conn->query('SELECT ho_ten FROM hoi_vien ORDER BY id DESC LIMIT 1')->fetch_assoc();

            return [
                ['icon' => 'calendar-check', 'title' => $pending . ' lịch dạy chờ duyệt', 'time' => 'Cập nhật từ CSDL', 'tone' => 'warning'],
                ['icon' => 'receipt', 'title' => $unpaid . ' đăng ký gói chưa thanh toán', 'time' => 'Cập nhật từ CSDL', 'tone' => 'primary'],
                ['icon' => 'person-plus', 'title' => 'Hội viên mới nhất: ' . ($latest['ho_ten'] ?? '—'), 'time' => 'Cập nhật từ CSDL', 'tone' => 'success'],
            ];
        }

        if ($role === 'instructor') {
            $pending = (int) $conn->query("SELECT COUNT(*) AS c FROM lich_ranh WHERE trang_thai = 'cho_duyet'")->fetch_assoc()['c'];
            $classes = (int) $conn->query('SELECT COUNT(*) AS c FROM lop_hoc')->fetch_assoc()['c'];

            return [
                ['icon' => 'calendar-check', 'title' => $pending . ' yêu cầu lịch dạy đang chờ duyệt', 'time' => 'Cập nhật từ CSDL', 'tone' => 'warning'],
                ['icon' => 'journal-bookmark', 'title' => $classes . ' lớp học trong hệ thống', 'time' => 'Cập nhật từ CSDL', 'tone' => 'primary'],
            ];
        }

        $packages = (int) $conn->query('SELECT COUNT(*) AS c FROM goi_tap')->fetch_assoc()['c'];
        return [
            ['icon' => 'journal-plus', 'title' => $packages . ' gói tập đang mở bán', 'time' => 'Cập nhật từ CSDL', 'tone' => 'primary'],
        ];
    }
}
