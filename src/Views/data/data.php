<?php
declare(strict_types=1);

/**
 * Nguồn dữ liệu cho các view — đọc trực tiếp từ MySQL.
 */

function view_db(): mysqli
{
    static $conn = null;
    if ($conn === null) {
        $conn = require __DIR__ . '/../../../mysql/connect_mysql.php';
    }
    return $conn;
}

function view_weekday_name(string $date): string
{
    $names = [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
    return $names[(int) date('N', strtotime($date))] ?? '';
}

function view_current_account_id(): int
{
    return (int) ($_SESSION['tai_khoan_id'] ?? 0);
}

function view_cast_ints(array $rows, array $keys): array
{
    foreach ($rows as &$row) {
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                $row[$key] = (int) $row[$key];
            }
        }
    }
    unset($row);
    return $rows;
}

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

function data_members(): array
{
    $sql = "
        SELECT hv.id, hv.ho_ten AS name, hv.sdt AS phone, hv.email,
               CASE hv.gioi_tinh WHEN 'nam' THEN 'Nam' WHEN 'nu' THEN 'Nữ' ELSE 'Khác' END AS gender,
               COALESCE(DATE_FORMAT(hv.ngay_sinh, '%Y-%m-%d'), '') AS birth,
               hv.trang_thai AS status,
               COALESCE((SELECT gt.ten_goi
                           FROM dang_ky_goi dkg
                           JOIN goi_tap gt ON gt.id = dkg.goi_tap_id
                          WHERE dkg.hoi_vien_id = hv.id
                          ORDER BY dkg.ngay_bat_dau DESC LIMIT 1), '—') AS package,
               DATE_FORMAT(hv.created_at, '%d/%m/%Y') AS joined,
               COALESCE((SELECT ROUND(100 * SUM(dd.trang_thai = 'co_mat') / COUNT(*))
                           FROM diem_danh dd WHERE dd.hoi_vien_id = hv.id), 0) AS rate
        FROM hoi_vien hv
        WHERE hv.deleted_at IS NULL
        ORDER BY hv.id
    ";
    return view_cast_ints(view_db()->query($sql)->fetch_all(MYSQLI_ASSOC), ['id', 'rate']);
}

function data_classes(): array
{
    $sql = "
        SELECT lh.id, lh.ten_lop AS name, gv.ho_ten AS instructor,
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

function data_revenue(): array
{
    $rows = view_db()->query('SELECT thang, so_tien FROM doanh_thu WHERE nam = 2026 ORDER BY thang')->fetch_all(MYSQLI_ASSOC);
    $result = [];
    foreach ($rows as $row) {
        $result[] = ['month' => 'T' . $row['thang'], 'value' => (int) round($row['so_tien'] / 1000000)];
    }
    return $result;
}

function view_member_id(): int
{
    $conn = view_db();
    $accountId = view_current_account_id();
    if ($accountId > 0) {
        $stmt = $conn->prepare('SELECT id FROM hoi_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            return (int) $row['id'];
        }
    }
    $row = $conn->query('SELECT id FROM hoi_vien WHERE deleted_at IS NULL ORDER BY id LIMIT 1')->fetch_assoc();
    return $row ? (int) $row['id'] : 0;
}

function data_current_member(): array
{
    $conn = view_db();
    $id = view_member_id();
    $stmt = $conn->prepare(
        'SELECT id, ho_ten, sdt, email, gioi_tinh, ngay_sinh, chieu_cao, can_nang, muc_tieu,
                DATE_FORMAT(created_at, "%d/%m/%Y") AS joined, trang_thai
           FROM hoi_vien WHERE id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return [];
    }

    $package = $conn->prepare(
        'SELECT gt.ten_goi, DATE_FORMAT(d.ngay_ket_thuc, "%d/%m/%Y") AS het_han
           FROM dang_ky_goi d JOIN goi_tap gt ON gt.id = d.goi_tap_id
          WHERE d.hoi_vien_id = ? AND d.trang_thai = "da_thanh_toan"
          ORDER BY d.ngay_ket_thuc DESC LIMIT 1'
    );
    $package->bind_param('i', $id);
    $package->execute();
    $pkg = $package->get_result()->fetch_assoc();
    $package->close();

    $row['package'] = $pkg['ten_goi'] ?? '—';
    $row['package_expiry'] = $pkg['het_han'] ?? '—';
    return $row;
}

function data_customer_packages(): array
{
    $conn = view_db();
    $id = view_member_id();
    $stmt = $conn->prepare(
        'SELECT gt.ten_goi AS name,
                DATE_FORMAT(d.ngay_bat_dau, "%d/%m/%Y") AS `from`,
                DATE_FORMAT(d.ngay_ket_thuc, "%d/%m/%Y") AS `to`,
                d.gia_tai_thoi_diem AS price, d.trang_thai AS payment,
                COALESCE(d.phuong_thuc, "—") AS method,
                COALESCE(DATE_FORMAT(d.ngay_thanh_toan, "%d/%m/%Y %H:%i"), "—") AS paid_at
           FROM dang_ky_goi d JOIN goi_tap gt ON gt.id = d.goi_tap_id
          WHERE d.hoi_vien_id = ?
          ORDER BY d.ngay_bat_dau DESC'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return view_cast_ints($rows, ['price']);
}

function data_customer_class_history(): array
{
    $conn = view_db();
    $id = view_member_id();
    $stmt = $conn->prepare(
        'SELECT lh.ten_lop AS class, gv.ho_ten AS teacher,
                CONCAT("Thứ ", lh.thu_trong_tuan, " · ", TIME_FORMAT(lh.gio_bat_dau, "%H:%i"), " – ", TIME_FORMAT(lh.gio_ket_thuc, "%H:%i")) AS schedule,
                DATE_FORMAT(d.ngay_dang_ky, "%d/%m/%Y") AS registered, d.trang_thai AS status
           FROM dang_ky_lop d
           JOIN lop_hoc lh ON lh.id = d.lop_hoc_id
           JOIN giao_vien gv ON gv.id = lh.giao_vien_id
          WHERE d.hoi_vien_id = ?
          ORDER BY d.ngay_dang_ky DESC'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function data_customer_classes(): array
{
    $conn = view_db();
    $id = view_member_id();
    $stmt = $conn->prepare(
        'SELECT d.lop_hoc_id AS id FROM dang_ky_lop d WHERE d.hoi_vien_id = ? AND d.trang_thai = "dang_ky"'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $ids = array_map(static fn ($r) => (int) $r['id'], $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    $stmt->close();

    if (!$ids) {
        return [];
    }

    return array_values(array_filter(
        data_classes(),
        static fn ($class) => in_array((int) $class['id'], $ids, true)
    ));
}

function data_customer_profile(): array
{
    $conn = view_db();
    $id = view_current_account_id();
    $row = null;
    if ($id > 0) {
        $stmt = $conn->prepare('SELECT * FROM hoi_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$row) {
        $row = $conn->query('SELECT * FROM hoi_vien WHERE deleted_at IS NULL ORDER BY id LIMIT 1')->fetch_assoc();
    }

    return [
        'weight' => $row ? (string) ($row['can_nang'] ?? '') : '',
        'height' => $row ? (string) ($row['chieu_cao'] ?? '') : '',
        'goal'   => $row ? (string) ($row['muc_tieu'] ?? '') : '',
    ];
}

function data_customer_attendance(): array
{
    $conn = view_db();
    $id = view_current_account_id();
    $sql = "
        SELECT dd.ngay, lh.ten_lop AS class, gv.ho_ten AS instructor,
               dd.trang_thai AS status, TIME_FORMAT(lh.gio_bat_dau, '%H:%i') AS time
        FROM diem_danh dd
        JOIN lop_hoc lh ON lh.id = dd.lop_hoc_id
        JOIN giao_vien gv ON gv.id = lh.giao_vien_id
        JOIN hoi_vien hv ON hv.id = dd.hoi_vien_id
        WHERE (? = 0 OR hv.tai_khoan_id = ?)
        ORDER BY dd.ngay DESC, dd.id DESC
        LIMIT 20
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id, $id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as &$row) {
        $row['date'] = date('d/m/Y', strtotime($row['ngay']));
        unset($row['ngay']);
    }
    unset($row);
    return $rows;
}

function data_students_for_class(int $classId = 1, ?string $date = null): array
{
    $conn = view_db();
    $sql = "
        SELECT dkl.hoi_vien_id AS member_id, hv.ho_ten AS name, hv.sdt AS phone,
               COALESCE(dd.trang_thai, 'co_mat') AS status, COALESCE(dd.ghi_chu, '') AS note
        FROM dang_ky_lop dkl
        JOIN hoi_vien hv ON hv.id = dkl.hoi_vien_id
        LEFT JOIN diem_danh dd ON dd.hoi_vien_id = hv.id AND dd.lop_hoc_id = dkl.lop_hoc_id
        WHERE dkl.lop_hoc_id = ? AND dkl.trang_thai = 'dang_ky'
        ORDER BY hv.ho_ten
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return view_cast_ints($rows, ['member_id']);
}

function data_current_instructor(): array
{
    $conn = view_db();
    $accountId = view_current_account_id();
    $row = null;

    if ($accountId > 0) {
        $stmt = $conn->prepare('SELECT * FROM giao_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$row) {
        $row = $conn->query('SELECT * FROM giao_vien WHERE deleted_at IS NULL ORDER BY id LIMIT 1')->fetch_assoc();
    }
    if (!$row) {
        return [];
    }

    $id = (int) $row['id'];
    $classes = (int) $conn->query("SELECT COUNT(*) AS c FROM lop_hoc WHERE giao_vien_id = $id AND deleted_at IS NULL")->fetch_assoc()['c'];
    $students = (int) $conn->query("
        SELECT COUNT(DISTINCT dkl.hoi_vien_id) AS c
          FROM dang_ky_lop dkl
          JOIN lop_hoc lh ON lh.id = dkl.lop_hoc_id
         WHERE lh.giao_vien_id = $id AND dkl.trang_thai = 'dang_ky'
    ")->fetch_assoc()['c'];

    return [
        'id'        => $id,
        'name'      => $row['ho_ten'],
        'phone'     => $row['sdt'],
        'email'     => $row['email'],
        'specialty' => (string) ($row['chuyen_mon'] ?? ''),
        'status'    => $row['trang_thai'],
        'classes'   => $classes,
        'students'  => $students,
    ];
}

function data_current_instructor_name(): string
{
    $conn = view_db();
    $id = view_current_account_id();
    if ($id > 0) {
        $stmt = $conn->prepare('SELECT ho_ten FROM giao_vien WHERE tai_khoan_id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            return (string) $row['ho_ten'];
        }
    }
    $row = $conn->query('SELECT ho_ten FROM giao_vien ORDER BY id LIMIT 1')->fetch_assoc();
    return $row ? (string) $row['ho_ten'] : '';
}
