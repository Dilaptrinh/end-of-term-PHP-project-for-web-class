<?php
/**
 * mysql/seed_data.php
 * ------------------------------------------------------------------
 * Tạo tài khoản đăng nhập và liên kết hồ sơ người dùng.
 * Dữ liệu mẫu nghiệp vụ được tạo trong seed_core_data().
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

if (!function_exists('seed_core_data')) {
    function seed_core_data(mysqli $conn): void
    {
        $specialties = ['Yoga', 'Pilates', 'HIIT', 'Boxing', 'Gym & Strength', 'Zumba', 'Cardio'];
        $stmt = $conn->prepare('INSERT IGNORE INTO chuyen_mon (ten_chuyen_mon) VALUES (?)');
        foreach ($specialties as $specialty) {
            $stmt->bind_param('s', $specialty);
            $stmt->execute();
        }
        $stmt->close();

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM giao_vien')->fetch_assoc()['c'] === 0) {
            $instructors = [
                ['Trần Thu Hà', '0911222333', 'thuha@fitzone.vn', 'dang_lam'],
                ['Nguyễn Đức Anh', '0912333444', 'ducanh@fitzone.vn', 'dang_lam'],
                ['Lê Ngọc Mai', '0913444555', 'ngocmai@fitzone.vn', 'dang_lam'],
                ['Phạm Quốc Bảo', '0914555666', 'quocbao@fitzone.vn', 'dang_lam'],
                ['Đặng Hồng Nhung', '0915666777', 'hongnhung@fitzone.vn', 'nghi'],
            ];
            $stmt = $conn->prepare('INSERT INTO giao_vien (ho_ten, sdt, email, trang_thai) VALUES (?, ?, ?, ?)');
            foreach ($instructors as [$name, $phone, $email, $status]) {
                $stmt->bind_param('ssss', $name, $phone, $email, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

        $specialtiesByEmail = [
            'thuha@fitzone.vn' => ['Yoga', 'Pilates'],
            'ducanh@fitzone.vn' => ['HIIT', 'Boxing'],
            'ngocmai@fitzone.vn' => ['Pilates', 'Cardio'],
            'quocbao@fitzone.vn' => ['Gym & Strength'],
            'hongnhung@fitzone.vn' => ['Zumba'],
        ];
        $find = $conn->prepare('SELECT id FROM chuyen_mon WHERE ten_chuyen_mon = ? LIMIT 1');
        $link = $conn->prepare('INSERT IGNORE INTO giao_vien_chuyen_mon (giao_vien_id, chuyen_mon_id) VALUES (?, ?)');
        $rows = $conn->query('SELECT id, email FROM giao_vien');
        while ($row = $rows->fetch_assoc()) {
            foreach ($specialtiesByEmail[$row['email']] ?? [] as $name) {
                $find->bind_param('s', $name);
                $find->execute();
                $specialty = $find->get_result()->fetch_assoc();
                if (!$specialty) {
                    continue;
                }
                $instructorId = (int) $row['id'];
                $specialtyId = (int) $specialty['id'];
                $link->bind_param('ii', $instructorId, $specialtyId);
                $link->execute();
            }
        }

        if (!function_exists('seed_customer_accounts')) {
            function seed_customer_accounts(mysqli $conn): void
            {
                $members = $conn->query('SELECT id, tai_khoan_id, ho_ten FROM hoi_vien ORDER BY id')->fetch_all(MYSQLI_ASSOC);
                $findUsername = $conn->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
                $insertAccount = $conn->prepare(
                    'INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai)
                     VALUES (?, ?, ?, "customer", 1)'
                );
                $linkMember = $conn->prepare('UPDATE hoi_vien SET tai_khoan_id = ? WHERE id = ?');
                $hash = password_hash('123456', PASSWORD_DEFAULT);

                foreach ($members as $member) {
                    if ((int) ($member['tai_khoan_id'] ?? 0) > 0) {
                        continue;
                    }

                    $username = 'hoivien' . str_pad((string) $member['id'], 2, '0', STR_PAD_LEFT);
                    $findUsername->bind_param('s', $username);
                    $findUsername->execute();
                    $existing = $findUsername->get_result()->fetch_assoc();

                    if ($existing) {
                        $accountId = (int) $existing['id'];
                    } else {
                        $name = (string) $member['ho_ten'];
                        $insertAccount->bind_param('sss', $username, $hash, $name);
                        $insertAccount->execute();
                        $accountId = (int) $insertAccount->insert_id;
                    }

                    $memberId = (int) $member['id'];
                    $linkMember->bind_param('ii', $accountId, $memberId);
                    $linkMember->execute();
                }

                $findUsername->close();
                $insertAccount->close();
                $linkMember->close();
            }
        }
        $find->close();
        $link->close();

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM hoi_vien')->fetch_assoc()['c'] === 0) {
            $members = [
                ['Lê Hoàng Nam', '0901234567', 'hoangnam@gmail.com', 'nam', '1998-04-12', 'hoat_dong', '2026-08-12', 68.5, 172, 'Duy trì thể lực'],
                ['Phạm Anh Tuấn', '0902345678', 'anhtuan@gmail.com', 'nam', '2000-09-03', 'hoat_dong', '2026-09-20', 72, 175, 'Tăng cơ'],
                ['Nguyễn Thị Mai', '0903456789', 'thimai@gmail.com', 'nu', '1996-01-25', 'hoat_dong', '2026-07-05', 55, 160, 'Giảm cân'],
                ['Trần Quốc Huy', '0904567890', 'quochuy@gmail.com', 'nam', '1994-11-18', 'hoat_dong', '2026-10-01', 80, 178, 'Tăng sức bền'],
                ['Đỗ Thu Trang', '0905678901', 'thutrang@gmail.com', 'nu', '2001-06-30', 'hoat_dong', '2026-08-15', 52, 158, 'Dẻo dai'],
                ['Vũ Minh Khang', '0906789012', 'minhkhang@gmail.com', 'nam', '1999-02-14', 'ngung_hoat_dong', '2026-06-10', 75, 176, 'Giảm cân'],
                ['Bùi Ngọc Hân', '0907890123', 'ngochan@gmail.com', 'nu', '2002-12-08', 'hoat_dong', '2026-09-22', 50, 157, 'Sức khỏe'],
                ['Hoàng Gia Bảo', '0908901234', 'giabao@gmail.com', 'nam', '1997-03-21', 'hoat_dong', '2026-10-02', 70, 173, 'Tăng cơ'],
                ['Ngô Thanh Vân', '0909012345', 'thanhvan@gmail.com', 'nu', '1995-07-17', 'hoat_dong', '2026-07-18', 54, 162, 'Duy trì thể lực'],
                ['Lý Tuấn Kiệt', '0910123456', 'tuankiet@gmail.com', 'nam', '2003-05-05', 'ngung_hoat_dong', '2026-05-01', 66, 170, 'Tăng cân'],
            ];
            $stmt = $conn->prepare('INSERT INTO hoi_vien (ho_ten, sdt, email, gioi_tinh, ngay_sinh, trang_thai, created_at, can_nang, chieu_cao, muc_tieu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($members as [$name, $phone, $email, $gender, $birth, $status, $joined, $weight, $height, $goal]) {
                $stmt->bind_param('sssssssdds', $name, $phone, $email, $gender, $birth, $status, $joined, $weight, $height, $goal);
                $stmt->execute();
            }
            $stmt->close();
        }

        $memberCount = (int) $conn->query('SELECT COUNT(*) AS c FROM hoi_vien')->fetch_assoc()['c'];
        if ($memberCount < 50) {
            $stmt = $conn->prepare('
                INSERT IGNORE INTO hoi_vien
                    (ho_ten, sdt, email, gioi_tinh, ngay_sinh, trang_thai, created_at, can_nang, chieu_cao, muc_tieu)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            for ($number = $memberCount + 1; $number <= 50; $number++) {
                $name = 'Hội viên mẫu ' . $number;
                $phone = '098' . str_pad((string) $number, 7, '0', STR_PAD_LEFT);
                $email = 'hoivien' . $number . '@fitzone.vn';
                $gender = $number % 2 === 0 ? 'nu' : 'nam';
                $birth = '1995-01-01';
                $status = $number % 10 === 0 ? 'ngung_hoat_dong' : 'hoat_dong';
                $joined = '2026-10-01';
                $weight = 60.0;
                $height = 165.0;
                $goal = 'Duy trì thể lực';
                $stmt->bind_param('sssssssdds', $name, $phone, $email, $gender, $birth, $status, $joined, $weight, $height, $goal);
                $stmt->execute();
            }
            $stmt->close();
        }

        $packages = [
            ['Basic 1 tháng', 30, 500000, 'Truy cập phòng gym, tủ đồ, phòng tắm trong 30 ngày.', 'dang_ban'],
            ['Standard 3 tháng', 90, 1200000, 'Toàn bộ quyền lợi Basic + tham gia lớp nhóm không giới hạn.', 'dang_ban'],
            ['Premium 6 tháng', 180, 2100000, 'Bao gồm Standard + 1 buổi PT/tháng và tư vấn dinh dưỡng.', 'dang_ban'],
            ['PT 1-1 (10 buổi)', 30, 3000000, '10 buổi tập cá nhân cùng huấn luyện viên chuyên trách.', 'dang_ban'],
            ['Basic 1 tháng (cũ)', 30, 450000, 'Gói đã ngừng bán, giữ lại cho lịch sử.', 'ngung_ban'],
        ];
        if ((int) $conn->query('SELECT COUNT(*) AS c FROM goi_tap')->fetch_assoc()['c'] === 0) {
            $stmt = $conn->prepare('INSERT INTO goi_tap (ten_goi, thoi_han_ngay, gia, mo_ta, trang_thai) VALUES (?, ?, ?, ?, ?)');
            foreach ($packages as [$name, $days, $price, $description, $status]) {
                $stmt->bind_param('siiss', $name, $days, $price, $description, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

        $classes = [
            ['Yoga cơ bản', 1, 1, '18:00', '19:00', '2026-09-01', '2026-11-30', 24, 'P.101', 'Cơ bản', 'Làm quen hơi thở, tư thế nền tảng và thư giãn sâu.', 'dang_mo'],
            ['HIIT đốt mỡ', 2, 2, '06:00', '07:00', '2026-09-01', '2026-12-31', 16, 'P.202', 'Trung cấp', 'Bài tập cường độ cao ngắt quãng, đốt cháy calo tối đa.', 'dang_mo'],
            ['Gym & Strength', 3, 4, '17:30', '18:30', '2026-09-05', '2026-12-30', 20, 'Khu A', 'Cơ bản', 'Kỹ thuật nâng tạ cơ bản, xây dựng sức mạnh toàn thân.', 'dang_mo'],
            ['Zumba Dance', 4, 1, '19:00', '20:00', '2026-09-01', '2026-11-30', 30, 'P.101', 'Mọi trình độ', 'Kết hợp âm nhạc và vũ đạo Latin, đốt calo vui vẻ.', 'dang_mo'],
            ['Pilates kiểm soát', 5, 3, '07:00', '08:00', '2026-09-01', '2026-10-31', 15, 'P.103', 'Trung cấp', 'Tăng cường core, cải thiện tư thế và độ dẻo dai.', 'dang_mo'],
            ['Boxing cơ bản', 6, 2, '08:00', '09:00', '2026-09-12', '2026-12-30', 18, 'Khu B', 'Cơ bản', 'Làm quen kỹ thuật đấm, di chuyển và phản xạ.', 'dang_mo'],
            ['Yoga nâng cao', 6, 1, '09:30', '10:30', '2026-09-01', '2026-11-30', 20, 'P.101', 'Nâng cao', 'Chuỗi tư thế nâng cao, giữ thăng bằng và kiểm soát hơi thở.', 'dang_mo'],
            ['Cardio & Core', 7, 3, '16:00', '17:00', '2026-09-01', '2026-12-31', 25, 'P.202', 'Mọi trình độ', 'Cardio nhẹ nhàng kết hợp bài tập săn chắc vùng bụng.', 'dang_mo'],
            ['Gym nâng cao', 5, 4, '18:00', '19:30', '2026-10-01', '2026-12-31', 12, 'Khu A', 'Nâng cao', 'Chương trình sức mạnh chuyên sâu cho hội viên lâu năm.', 'da_dong'],
        ];
        if ((int) $conn->query('SELECT COUNT(*) AS c FROM lop_hoc')->fetch_assoc()['c'] === 0) {
            $stmt = $conn->prepare('INSERT INTO lop_hoc (ten_lop, thu_trong_tuan, giao_vien_id, gio_bat_dau, gio_ket_thuc, ngay_bat_dau, ngay_ket_thuc, suc_chua, phong_hoc, trinh_do, mo_ta, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($classes as [$name, $weekday, $instructorId, $start, $end, $from, $to, $capacity, $room, $level, $description, $status]) {
                $stmt->bind_param('siissssissss', $name, $weekday, $instructorId, $start, $end, $from, $to, $capacity, $room, $level, $description, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM dang_ky_goi')->fetch_assoc()['c'] === 0) {
            $registrations = [
                [1, 3, '2026-08-12', '2027-02-12', 2100000, 'da_thanh_toan', 'Chuyển khoản', '2026-08-12 09:14:00', 'admin'],
                [2, 2, '2026-09-20', '2026-12-20', 1200000, 'chua_thanh_toan', null, null, null],
                [3, 3, '2026-07-05', '2027-01-05', 2100000, 'da_thanh_toan', 'Tiền mặt', '2026-07-05 16:40:00', 'admin'],
                [4, 1, '2026-10-01', '2026-11-01', 500000, 'chua_thanh_toan', null, null, null],
                [7, 3, '2026-09-22', '2027-03-22', 2100000, 'da_thanh_toan', 'Thẻ', '2026-09-22 10:05:00', 'admin'],
                [8, 4, '2026-10-02', '2026-11-02', 3000000, 'da_thanh_toan', 'Chuyển khoản', '2026-10-02 14:22:00', 'admin'],
                [6, 1, '2026-06-10', '2026-07-10', 500000, 'da_huy', null, null, null],
            ];
            $stmt = $conn->prepare('INSERT INTO dang_ky_goi (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem, trang_thai, phuong_thuc, ngay_thanh_toan, nhan_vien_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, (SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1))');
            foreach ($registrations as [$memberId, $packageId, $from, $to, $price, $status, $method, $paidAt, $staff]) {
                $stmt->bind_param('iississss', $memberId, $packageId, $from, $to, $price, $status, $method, $paidAt, $staff);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM dang_ky_lop')->fetch_assoc()['c'] === 0) {
            $registrations = [
                [1, 1, '2026-09-02', 'dang_ky'], [1, 2, '2026-09-05', 'dang_ky'],
                [3, 1, '2026-09-03', 'dang_ky'], [4, 4, '2026-09-06', 'dang_ky'],
                [2, 6, '2026-09-14', 'dang_ky'], [5, 3, '2026-09-08', 'da_huy'],
                [7, 5, '2026-09-01', 'dang_ky'], [8, 3, '2026-09-10', 'dang_ky'],
            ];
            $stmt = $conn->prepare('INSERT INTO dang_ky_lop (hoi_vien_id, lop_hoc_id, ngay_dang_ky, trang_thai) VALUES (?, ?, ?, ?)');
            foreach ($registrations as [$memberId, $classId, $registered, $status]) {
                $stmt->bind_param('iiss', $memberId, $classId, $registered, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM doanh_thu')->fetch_assoc()['c'] === 0) {
            $stmt = $conn->prepare('INSERT INTO doanh_thu (thang, nam, so_tien) VALUES (?, 2026, ?)');
            foreach ([42, 38, 51, 47, 58, 63, 55, 71, 78, 86, 0, 0] as $index => $value) {
                $month = $index + 1;
                $amount = $value * 1000000;
                $stmt->bind_param('ii', $month, $amount);
                $stmt->execute();
            }
            $stmt->close();
        }
    }
}
