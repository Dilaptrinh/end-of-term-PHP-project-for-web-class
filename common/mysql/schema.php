<?php
/**
 * Tạo cấu trúc CSDL 
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
                gioi_thieu   TEXT NULL,
                trang_thai   VARCHAR(20) NOT NULL DEFAULT 'dang_lam',
                deleted_at   DATETIME NULL,
                created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_giao_vien_email (email),
                UNIQUE KEY uq_giao_vien_tai_khoan (tai_khoan_id),
                CONSTRAINT fk_giao_vien_tai_khoan FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `chuyen_mon` (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                ten_chuyen_mon VARCHAR(100) NOT NULL,
                trang_thai TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY uq_chuyen_mon_ten (ten_chuyen_mon)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $conn->query("
            CREATE TABLE IF NOT EXISTS `giao_vien_chuyen_mon` (
                giao_vien_id INT UNSIGNED NOT NULL,
                chuyen_mon_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (giao_vien_id, chuyen_mon_id),
                CONSTRAINT fk_gvcm_giao_vien FOREIGN KEY (giao_vien_id) REFERENCES giao_vien(id) ON DELETE CASCADE,
                CONSTRAINT fk_gvcm_chuyen_mon FOREIGN KEY (chuyen_mon_id) REFERENCES chuyen_mon(id) ON DELETE CASCADE
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
                UNIQUE KEY uq_hoi_vien_email (email),
                CONSTRAINT fk_hoi_vien_tai_khoan FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
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
                ,CONSTRAINT fk_lop_hoc_giao_vien FOREIGN KEY (giao_vien_id) REFERENCES giao_vien(id)
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
                nhan_vien_id      INT UNSIGNED NULL,
                created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ,CONSTRAINT fk_dang_ky_goi_hoi_vien FOREIGN KEY (hoi_vien_id) REFERENCES hoi_vien(id)
                ,CONSTRAINT fk_dang_ky_goi_goi_tap FOREIGN KEY (goi_tap_id) REFERENCES goi_tap(id)
                ,CONSTRAINT fk_dang_ky_goi_nhan_vien FOREIGN KEY (nhan_vien_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
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
                ,CONSTRAINT fk_dang_ky_lop_hoi_vien FOREIGN KEY (hoi_vien_id) REFERENCES hoi_vien(id)
                ,CONSTRAINT fk_dang_ky_lop_lop_hoc FOREIGN KEY (lop_hoc_id) REFERENCES lop_hoc(id)
                ,UNIQUE KEY uq_dang_ky_lop (hoi_vien_id, lop_hoc_id)
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
    }
}
