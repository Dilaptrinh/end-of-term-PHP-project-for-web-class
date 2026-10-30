<?php
declare(strict_types=1);

/**
 * Dữ liệu riêng khu vực quản trị:
 * tài khoản, hội viên, đăng ký gói, doanh thu.
 */

function data_accounts(): array
{
    $sql = "
        SELECT id, ten_dang_nhap AS username, ho_ten AS name, vai_tro AS role,
               CASE trang_thai WHEN 1 THEN 'hoat_dong' ELSE 'bi_khoa' END AS status,
               '—' AS last_login,
               DATE_FORMAT(created_at, '%d/%m/%Y') AS created
          FROM tai_khoan
         ORDER BY id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id']);
}

function data_members(): array
{
    $sql = "
        SELECT hv.id, hv.ho_ten AS name, hv.sdt AS phone, hv.email,
               hv.gioi_tinh AS gender_raw, hv.dia_chi, hv.ghi_chu,
               CASE hv.gioi_tinh WHEN 'nam' THEN 'Nam' WHEN 'nu' THEN 'Nữ' ELSE 'Khác' END AS gender,
               COALESCE(DATE_FORMAT(hv.ngay_sinh, '%Y-%m-%d'), '') AS birth,
               hv.trang_thai AS status,
               COALESCE((SELECT gt.ten_goi
                           FROM dang_ky_goi dkg
                           JOIN goi_tap gt ON gt.id = dkg.goi_tap_id
                          WHERE dkg.hoi_vien_id = hv.id
                          ORDER BY dkg.ngay_bat_dau DESC LIMIT 1), '—') AS package,
               DATE_FORMAT(hv.created_at, '%d/%m/%Y') AS joined
        FROM hoi_vien hv
        WHERE hv.deleted_at IS NULL
        ORDER BY hv.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id']);
}

function data_package_registrations(): array
{
    $sql = "
        SELECT d.id, hv.ho_ten AS member, gt.ten_goi AS package,
               DATE_FORMAT(d.ngay_bat_dau, '%d/%m/%Y') AS `from`,
               DATE_FORMAT(d.ngay_ket_thuc, '%d/%m/%Y') AS `to`,
               d.gia_tai_thoi_diem AS price, d.trang_thai AS payment,
               COALESCE(d.phuong_thuc, '—') AS method,
               COALESCE(DATE_FORMAT(d.ngay_thanh_toan, '%d/%m/%Y %H:%i'), '—') AS paid_at,
               COALESCE(d.nhan_vien, '—') AS staff
        FROM dang_ky_goi d
        JOIN hoi_vien hv ON hv.id = d.hoi_vien_id
        JOIN goi_tap gt ON gt.id = d.goi_tap_id
        ORDER BY d.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id', 'price']);
}

function data_revenue(): array
{
    $rows = view_db()->query('SELECT thang, so_tien FROM doanh_thu WHERE nam = 2026 ORDER BY thang')->fetch_all(MYSQLI_ASSOC);
    $result = [];
    foreach ($rows as $row) {
        $result[] = ['month' => 'T' . $row['thang'], 'value' => (int) round($row['so_tien'] / 1000000)];
    }
    return $result;
}
