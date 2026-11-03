<?php
declare(strict_types=1);

/**
 * Dữ liệu riêng khu vực hội viên (customer).
 * Phụ thuộc data_classes() trong shared.php.
 */

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
        'SELECT d.id AS id, lh.ten_lop AS class, gv.ho_ten AS teacher,
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

