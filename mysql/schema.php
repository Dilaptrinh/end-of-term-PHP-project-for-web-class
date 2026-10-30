<?php
/**
 * mysql/schema.php
 * ------------------------------------------------------------------
 * Tạo cấu trúc CSDL và dữ liệu khởi tạo (idempotent).
 * Được gọi mỗi khi ứng dụng khởi động, trước khi tạo tài khoản.
 */

if (!function_exists('ensure_schema')) {
    function ensure_schema(mysqli $conn): void
    {
        $conn->query("
            CREATE TABLE IF NOT EXISTS `tai_khoan` (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ten_dang_nhap VARCHAR(50) NOT NULL,
                mat_khau      VARCHAR(255) NOT NULL,
                ho_ten        VARCHAR(100) NOT NULL,
                vai_tro       VARCHAR(20) NOT NULL DEFAULT 'customer',
                trang_thai    TINYINT(1) NOT NULL DEFAULT 1,
                created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_tai_khoan_ten (ten_dang_nhap)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `giao_vien` (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tai_khoan_id INT UNSIGNED NULL,
                ho_ten       VARCHAR(100) NOT NULL,
                sdt          VARCHAR(15) NOT NULL,
                email        VARCHAR(100) NOT NULL,
                chuyen_mon   VARCHAR(100) NULL,
                gioi_thieu   TEXT NULL,
                trang_thai   VARCHAR(20) NOT NULL DEFAULT 'dang_lam',
                deleted_at   DATETIME NULL,
                created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_giao_vien_email (email),
                UNIQUE KEY uq_giao_vien_tai_khoan (tai_khoan_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `hoi_vien` (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                tai_khoan_id INT UNSIGNED NULL,
                ho_ten       VARCHAR(100) NOT NULL,
                sdt          VARCHAR(15) NOT NULL,
                email        VARCHAR(100) NOT NULL,
                gioi_tinh    VARCHAR(10) NULL,
                ngay_sinh    DATE NULL,
                dia_chi      VARCHAR(255) NULL,
                ghi_chu      TEXT NULL,
                chieu_cao    DECIMAL(5,1) NULL,
                can_nang     DECIMAL(5,1) NULL,
                muc_tieu     VARCHAR(100) NULL,
                trang_thai   VARCHAR(20) NOT NULL DEFAULT 'hoat_dong',
                deleted_at   DATETIME NULL,
                created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_hoi_vien_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `goi_tap` (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ten_goi       VARCHAR(100) NOT NULL,
                thoi_han_ngay INT NOT NULL,
                gia           BIGINT NOT NULL,
                mo_ta         TEXT NULL,
                trang_thai    VARCHAR(20) NOT NULL DEFAULT 'dang_ban',
                deleted_at    DATETIME NULL,
                created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `lop_hoc` (
                id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ten_lop        VARCHAR(100) NOT NULL,
                giao_vien_id   INT UNSIGNED NOT NULL,
                thu_trong_tuan TINYINT NOT NULL,
                gio_bat_dau    TIME NOT NULL,
                gio_ket_thuc   TIME NOT NULL,
                ngay_bat_dau   DATE NOT NULL,
                ngay_ket_thuc  DATE NOT NULL,
                suc_chua       INT NOT NULL,
                phong_hoc      VARCHAR(50) NULL,
                mo_ta          TEXT NULL,
                trinh_do       VARCHAR(50) NULL,
                trang_thai     VARCHAR(20) NOT NULL DEFAULT 'dang_mo',
                deleted_at     DATETIME NULL,
                created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `dang_ky_goi` (
                id                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                hoi_vien_id       INT UNSIGNED NOT NULL,
                goi_tap_id        INT UNSIGNED NOT NULL,
                ngay_bat_dau      DATE NOT NULL,
                ngay_ket_thuc     DATE NOT NULL,
                gia_tai_thoi_diem BIGINT NOT NULL,
                trang_thai        VARCHAR(30) NOT NULL DEFAULT 'chua_thanh_toan',
                phuong_thuc       VARCHAR(30) NULL,
                ngay_thanh_toan   DATETIME NULL,
                nhan_vien         VARCHAR(100) NULL,
                created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `dang_ky_lop` (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                hoi_vien_id   INT UNSIGNED NOT NULL,
                lop_hoc_id    INT UNSIGNED NOT NULL,
                ngay_dang_ky  DATE NOT NULL,
                trang_thai    VARCHAR(20) NOT NULL DEFAULT 'dang_ky',
                created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `lich_ranh` (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                giao_vien_id INT UNSIGNED NOT NULL,
                ngay         DATE NOT NULL,
                gio_bat_dau  TIME NOT NULL,
                gio_ket_thuc TIME NOT NULL,
                trang_thai   VARCHAR(20) NOT NULL DEFAULT 'cho_duyet',
                ghi_chu      VARCHAR(255) NULL,
                nguoi_duyet  VARCHAR(100) NULL,
                created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `doanh_thu` (
                id     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                thang  TINYINT NOT NULL,
                nam    SMALLINT NOT NULL,
                so_tien BIGINT NOT NULL,
                UNIQUE KEY uq_doanh_thu_thang (thang, nam)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        seed_core_data($conn);
    }
}

if (!function_exists('seed_core_data')) {
    function seed_core_data(mysqli $conn): void
    {
        if ((int) $conn->query('SELECT COUNT(*) AS c FROM giao_vien')->fetch_assoc()['c'] === 0) {
            $instructors = [
                ['Trần Thu Hà', '0911222333', 'thuha@fitzone.vn', 'Yoga · Pilates', 'dang_lam'],
                ['Nguyễn Đức Anh', '0912333444', 'ducanh@fitzone.vn', 'HIIT · Boxing', 'dang_lam'],
                ['Lê Ngọc Mai', '0913444555', 'ngocmai@fitzone.vn', 'Pilates · Cardio', 'dang_lam'],
                ['Phạm Quốc Bảo', '0914555666', 'quocbao@fitzone.vn', 'Gym · Strength', 'dang_lam'],
                ['Đặng Hồng Nhung', '0915666777', 'hongnhung@fitzone.vn', 'Zumba · Dance', 'nghi'],
            ];
            $stmt = $conn->prepare(
                'INSERT INTO giao_vien (ho_ten, sdt, email, chuyen_mon, trang_thai) VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($instructors as [$name, $phone, $email, $specialty, $status]) {
                $stmt->bind_param('sssss', $name, $phone, $email, $specialty, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

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
            $stmt = $conn->prepare(
                'INSERT INTO hoi_vien (ho_ten, sdt, email, gioi_tinh, ngay_sinh, trang_thai, created_at, can_nang, chieu_cao, muc_tieu)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($members as [$name, $phone, $email, $gender, $birth, $status, $joined, $weight, $height, $goal]) {
                $stmt->bind_param('sssssssdds', $name, $phone, $email, $gender, $birth, $status, $joined, $weight, $height, $goal);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM goi_tap')->fetch_assoc()['c'] === 0) {
            $packages = [
                ['Basic 1 tháng', 30, 500000, 'Truy cập phòng gym, tủ đồ, phòng tắm trong 30 ngày.', 'dang_ban'],
                ['Standard 3 tháng', 90, 1200000, 'Toàn bộ quyền lợi Basic + tham gia lớp nhóm không giới hạn.', 'dang_ban'],
                ['Premium 6 tháng', 180, 2100000, 'Bao gồm Standard + 1 buổi PT/tháng và tư vấn dinh dưỡng.', 'dang_ban'],
                ['PT 1-1 (10 buổi)', 30, 3000000, '10 buổi tập cá nhân cùng huấn luyện viên chuyên trách.', 'dang_ban'],
                ['Basic 1 tháng (cũ)', 30, 450000, 'Gói đã ngừng bán, giữ lại cho lịch sử.', 'ngung_ban'],
            ];
            $stmt = $conn->prepare('INSERT INTO goi_tap (ten_goi, thoi_han_ngay, gia, mo_ta, trang_thai) VALUES (?, ?, ?, ?, ?)');
            foreach ($packages as [$name, $days, $price, $desc, $status]) {
                $stmt->bind_param('siiss', $name, $days, $price, $desc, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM lop_hoc')->fetch_assoc()['c'] === 0) {
            $classes = [
                ['Yoga cơ bản', 1, 1, '18:00', '19:00', '2026-09-01', '2026-11-30', 24, 'P.101', 'Cơ bản', 'Làm quen hơi thở, tư thế nền tảng và thư giãn sâu.', 'dang_mo'],
                ['HIIT đốt mỡ', 2, 2, '06:00', '07:00', '2026-09-01', '2026-12-31', 16, 'P.202', 'Trung cấp', 'Bài tập cường độ cao ngắt quãng, đốt cháy calo tối đa.', 'dang_mo'],
                ['Gym & Strength', 3, 4, '17:30', '18:30', '2026-09-05', '2026-12-30', 20, 'Khu A', 'Cơ bản', 'Kỹ thuật nâng tạ cơ bản, xây dựng sức mạnh toàn thân.', 'dang_mo'],
                ['Zumba Dance', 4, 1, '19:00', '20:00', '2026-09-01', '2026-11-30', 30, 'P.101', 'Mọi trình độ', 'Kết hợp âm nhạc và vũ đạo Latin, đốt calo vui vẻ.', 'dang_mo'],
                ['Pilates kiểm soát', 5, 3, '07:00', '08:00', '2026-09-01', '2026-10-31', 15, 'P.103', 'Trung cấp', 'Tăng cường core, cải thiện tư thế và độ dẻo dai.', 'dang_mo'],
                ['Boxing cơ bản', 6, 2, '08:00', '09:00', '2026-09-12', '2026-12-30', 18, 'Khu B', 'Cơ bản', 'Làm quen kỹ thuật đấm, di chuyển và phản xạ.', 'dang_mo'],
                ['Yoga nâng cao', 6, 1, '09:30', '10:30', '2026-09-01', '2026-11-30', 20, 'P.101', 'Nâng cao', 'Chuỗi tư thế nâng cao, giữ thăng bằng và kiểm soát hơi thở.', 'dang_mo'],
                ['Cardio & Core', 7, 3, '16:00', '17:00', '2026-09-01', '2026-12-31', 25, 'P.202', 'Mọi trình độ', 'Cardio nhẹ nhàng kết hợp bài tập săn chắc vùng bụng.', 'dang_mo'],
                ['Gym nâng cao', 5, 4, '18:00', '19:30', '2026-10-01', '2026-12-31', 12, 'Khu A', 'Nâng cao', 'Chương trình sức mạnh chuyên sâu cho học viên lâu năm.', 'da_dong'],
            ];
            $stmt = $conn->prepare(
                'INSERT INTO lop_hoc (ten_lop, thu_trong_tuan, giao_vien_id, gio_bat_dau, gio_ket_thuc, ngay_bat_dau, ngay_ket_thuc, suc_chua, phong_hoc, trinh_do, mo_ta, trang_thai)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($classes as [$name, $weekday, $instructorId, $start, $end, $from, $to, $capacity, $room, $level, $desc, $status]) {
                $stmt->bind_param('siissssissss', $name, $weekday, $instructorId, $start, $end, $from, $to, $capacity, $room, $level, $desc, $status);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM dang_ky_goi')->fetch_assoc()['c'] === 0) {
            $registrations = [
                [1, 3, '2026-08-12', '2027-02-12', 2100000, 'da_thanh_toan', 'Chuyển khoản', '2026-08-12 09:14:00', 'Nguyễn Minh Quân'],
                [2, 2, '2026-09-20', '2026-12-20', 1200000, 'chua_thanh_toan', null, null, null],
                [3, 3, '2026-07-05', '2027-01-05', 2100000, 'da_thanh_toan', 'Tiền mặt', '2026-07-05 16:40:00', 'Nguyễn Minh Quân'],
                [4, 1, '2026-10-01', '2026-11-01', 500000, 'chua_thanh_toan', null, null, null],
                [7, 3, '2026-09-22', '2027-03-22', 2100000, 'da_thanh_toan', 'Thẻ', '2026-09-22 10:05:00', 'Nguyễn Minh Quân'],
                [8, 4, '2026-10-02', '2026-11-02', 3000000, 'da_thanh_toan', 'Chuyển khoản', '2026-10-02 14:22:00', 'Nguyễn Minh Quân'],
                [6, 1, '2026-06-10', '2026-07-10', 500000, 'da_huy', null, null, 'Nguyễn Minh Quân'],
            ];
            $stmt = $conn->prepare(
                'INSERT INTO dang_ky_goi (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem, trang_thai, phuong_thuc, ngay_thanh_toan, nhan_vien)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
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

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM lich_ranh')->fetch_assoc()['c'] === 0) {
            $rows = [
                [1, '2026-10-26', '07:00', '09:00', 'cho_duyet', 'Có thể nhận lớp Yoga bổ sung', null],
                [1, '2026-10-27', '18:00', '20:00', 'da_duyet', 'Lịch dạy Yoga cơ bản', 'Nguyễn Minh Quân'],
                [2, '2026-10-27', '06:00', '07:30', 'cho_duyet', 'HIIT sáng sớm', null],
                [3, '2026-10-28', '17:00', '19:00', 'cho_duyet', 'Pilates bổ sung', null],
                [1, '2026-10-24', '09:00', '11:00', 'da_duyet', 'Yoga nâng cao', 'Nguyễn Minh Quân'],
                [4, '2026-10-23', '18:00', '19:30', 'tu_choi', 'Trùng lịch lớp Gym nâng cao', 'Nguyễn Minh Quân'],
                [1, '2026-10-29', '19:00', '20:00', 'da_huy', 'Giáo viên đã hủy yêu cầu', null],
            ];
            $stmt = $conn->prepare(
                'INSERT INTO lich_ranh (giao_vien_id, ngay, gio_bat_dau, gio_ket_thuc, trang_thai, ghi_chu, nguoi_duyet)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($rows as [$instructorId, $date, $start, $end, $status, $note, $approver]) {
                $stmt->bind_param('issssss', $instructorId, $date, $start, $end, $status, $note, $approver);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ((int) $conn->query('SELECT COUNT(*) AS c FROM doanh_thu')->fetch_assoc()['c'] === 0) {
            $values = [42, 38, 51, 47, 58, 63, 55, 71, 78, 86, 0, 0];
            $stmt = $conn->prepare('INSERT INTO doanh_thu (thang, nam, so_tien) VALUES (?, 2026, ?)');
            foreach ($values as $index => $value) {
                $month = $index + 1;
                $amount = $value * 1000000;
                $stmt->bind_param('ii', $month, $amount);
                $stmt->execute();
            }
            $stmt->close();
        }
    }
}
