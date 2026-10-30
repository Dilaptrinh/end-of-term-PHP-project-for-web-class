<?php
declare(strict_types=1);

/**
 * Dữ liệu riêng khu vực giáo viên.
 */

function data_students_for_class(int $classId = 1, ?string $date = null): array
{
    $conn = view_db();
    $sql = "
        SELECT dkl.hoi_vien_id AS member_id, hv.ho_ten AS name, hv.sdt AS phone
        FROM dang_ky_lop dkl
        JOIN hoi_vien hv ON hv.id = dkl.hoi_vien_id
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

function data_students_for_instructor(int $instructorId, ?int $classId = null, string $keyword = '', string $status = ''): array
{
    $conn = view_db();
    $sql = "
        SELECT DISTINCT hv.id AS member_id, hv.ho_ten AS name, hv.sdt AS phone,
               hv.trang_thai AS status, lh.ten_lop AS class_name,
               lh.thu_trong_tuan AS weekday, TIME_FORMAT(lh.gio_bat_dau, '%H:%i') AS start
        FROM `dang_ky_lop` dkl
        JOIN `hoi_vien` hv ON hv.id = dkl.hoi_vien_id
        JOIN `lop_hoc` lh ON lh.id = dkl.lop_hoc_id
        WHERE lh.giao_vien_id = ? AND dkl.trang_thai = 'dang_ky'
    ";
    $types = 'i';
    $params = [$instructorId];

    if ($classId !== null && $classId > 0) {
        $sql .= ' AND lh.id = ?';
        $types .= 'i';
        $params[] = $classId;
    }
    if ($keyword !== '') {
        $sql .= ' AND (hv.ho_ten LIKE ? OR hv.sdt LIKE ?)';
        $types .= 'ss';
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
    }
    if ($status !== '') {
        $sql .= ' AND hv.trang_thai = ?';
        $types .= 's';
        $params[] = $status;
    }
    $sql .= ' ORDER BY hv.ho_ten';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return view_cast_ints($rows, ['member_id', 'weekday']);
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
