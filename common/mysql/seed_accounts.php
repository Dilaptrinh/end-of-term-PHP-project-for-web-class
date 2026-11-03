<?php
/**
 * mysql/seed_accounts.php
 * ------------------------------------------------------------------
 * Tạo tài khoản đăng nhập cho từng vai trò và liên kết hồ sơ.
 * Idempotent: chạy lại không nhân đôi dữ liệu.
 */

if (!function_exists('seed_initial_accounts')) {
    function seed_initial_accounts(mysqli $conn): void
    {
        $accounts = [
            ['admin',     'Quản trị viên',    'admin'],
            ['thuha',     'Trần Thu Hà',       'instructor'],
            ['ducanh',    'Nguyễn Đức Anh',    'instructor'],
            ['ngocmai',   'Lê Ngọc Mai',       'instructor'],
            ['quocbao',   'Phạm Quốc Bảo',     'instructor'],
            ['hongnhung', 'Đặng Hồng Nhung',   'instructor'],
            ['hoangnam',  'Lê Hoàng Nam',      'customer'],
        ];

        $find = $conn->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
        $insert = $conn->prepare(
            'INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai)
             VALUES (?, ?, ?, ?, 1)'
        );

        foreach ($accounts as [$username, $fullName, $role]) {
            $find->bind_param('s', $username);
            $find->execute();
            $existing = $find->get_result()->fetch_assoc();

            if ($existing) {
                continue;
            }

            $hash = password_hash('123456', PASSWORD_DEFAULT);
            $insert->bind_param('ssss', $username, $hash, $fullName, $role);
            $insert->execute();
        }

        $find->close();
        $insert->close();

        $links = [
            ['thuha', 'thuha@fitzone.vn'],
            ['ducanh', 'ducanh@fitzone.vn'],
            ['ngocmai', 'ngocmai@fitzone.vn'],
            ['quocbao', 'quocbao@fitzone.vn'],
            ['hongnhung', 'hongnhung@fitzone.vn'],
        ];
        $getAccount = $conn->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
        $linkInstructor = $conn->prepare(
            'UPDATE giao_vien SET tai_khoan_id = ? WHERE email = ? AND (tai_khoan_id IS NULL OR tai_khoan_id <> ?)'
        );

        foreach ($links as [$username, $email]) {
            $getAccount->bind_param('s', $username);
            $getAccount->execute();
            $account = $getAccount->get_result()->fetch_assoc();
            if (!$account) {
                continue;
            }
            $id = (int) $account['id'];
            $linkInstructor->bind_param('isi', $id, $email, $id);
            $linkInstructor->execute();
        }
        $getAccount->close();
        $linkInstructor->close();

        $getAccount = $conn->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
        $linkMember = $conn->prepare(
            'UPDATE hoi_vien SET tai_khoan_id = ? WHERE email = ? AND (tai_khoan_id IS NULL OR tai_khoan_id <> ?)'
        );
        $username = 'hoangnam';
        $getAccount->bind_param('s', $username);
        $getAccount->execute();
        $account = $getAccount->get_result()->fetch_assoc();
        if ($account) {
            $id = (int) $account['id'];
            $email = 'hoangnam@gmail.com';
            $linkMember->bind_param('isi', $id, $email, $id);
            $linkMember->execute();
        }
        $getAccount->close();
        $linkMember->close();
    }
}
