<?php
declare(strict_types=1);

/**
 * Dữ liệu dùng chung giữa nhiều role:
 * giáo viên, lớp học, gói tập, đăng ký lớp, lịch rảnh.
 */

function data_instructors(): array
{
    $sql = "
        SELECT gv.id, gv.ho_ten AS name, gv.sdt AS phone, gv.email, gv.chuyen_mon AS specialty,
               (SELECT COUNT(*) FROM lop_hoc lh WHERE lh.giao_vien_id = gv.id AND lh.deleted_at IS NULL) AS classes,
               (SELECT COUNT(DISTINCT dkl.hoi_vien_id)
                  FROM dang_ky_lop dkl
                  JOIN lop_hoc lh2 ON lh2.id = dkl.lop_hoc_id
                 WHERE lh2.giao_vien_id = gv.id AND dkl.trang_thai = 'dang_ky') AS students,
               CASE gv.trang_thai WHEN 'dang_lam' THEN 'hoat_dong' ELSE 'ngung_hoat_dong' END AS status,
               COALESCE(tk.ten_dang_nhap, '—') AS account
        FROM giao_vien gv
        LEFT JOIN tai_khoan tk ON tk.id = gv.tai_khoan_id
        WHERE gv.deleted_at IS NULL
        ORDER BY gv.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id', 'classes', 'students']);
}

function data_classes(): array
{
    $sql = "
        SELECT lh.id, lh.ten_lop AS name, gv.ho_ten AS instructor, lh.giao_vien_id,
               lh.thu_trong_tuan AS weekday,
               TIME_FORMAT(lh.gio_bat_dau, '%H:%i') AS start,
               TIME_FORMAT(lh.gio_ket_thuc, '%H:%i') AS end,
               lh.phong_hoc AS room, lh.suc_chua AS capacity,
               (SELECT COUNT(*) FROM dang_ky_lop d
                 WHERE d.lop_hoc_id = lh.id AND d.trang_thai = 'dang_ky') AS enrolled,
               DATE_FORMAT(lh.ngay_bat_dau, '%d/%m/%Y') AS `from`,
               DATE_FORMAT(lh.ngay_ket_thuc, '%d/%m/%Y') AS `to`,
               lh.trang_thai AS status, lh.trinh_do AS level, lh.mo_ta AS `desc`
        FROM lop_hoc lh
        JOIN giao_vien gv ON gv.id = lh.giao_vien_id
        WHERE lh.deleted_at IS NULL
        ORDER BY lh.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id', 'weekday', 'capacity', 'enrolled']);
}

function data_packages(): array
{
    $sql = "
        SELECT gt.id, gt.ten_goi AS name, gt.thoi_han_ngay AS days, gt.gia AS price,
               (SELECT COUNT(*) FROM dang_ky_goi d
                 WHERE d.goi_tap_id = gt.id AND d.trang_thai = 'da_thanh_toan') AS sold,
               gt.trang_thai AS status, gt.mo_ta AS `desc`
        FROM goi_tap gt
        WHERE gt.deleted_at IS NULL
        ORDER BY gt.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id', 'days', 'price', 'sold']);
}

function data_class_registrations(): array
{
    $sql = "
        SELECT d.id, hv.ho_ten AS member, d.lop_hoc_id AS class_id, lh.ten_lop AS class,
               DATE_FORMAT(d.ngay_dang_ky, '%d/%m/%Y') AS registered, d.trang_thai AS status
        FROM dang_ky_lop d
        JOIN hoi_vien hv ON hv.id = d.hoi_vien_id
        JOIN lop_hoc lh ON lh.id = d.lop_hoc_id
        ORDER BY d.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id', 'class_id']);
}

function data_availability(): array
{
    $sql = "
        SELECT lr.id, gv.ho_ten AS instructor, lr.ngay,
               TIME_FORMAT(lr.gio_bat_dau, '%H:%i') AS start,
               TIME_FORMAT(lr.gio_ket_thuc, '%H:%i') AS end,
               lr.trang_thai AS status, COALESCE(lr.ghi_chu, '') AS note,
               COALESCE(lr.nguoi_duyet, '—') AS approved_by
        FROM lich_ranh lr
        JOIN giao_vien gv ON gv.id = lr.giao_vien_id
        ORDER BY lr.ngay DESC, lr.id DESC
    ";
    $rows = view_db()->query($sql)->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$row) {
        $row['date'] = date('d/m/Y', strtotime($row['ngay']));
        $row['weekday'] = view_weekday_name($row['ngay']);
        unset($row['ngay']);
    }
    unset($row);
    return view_cast_ints($rows, ['id']);
}
