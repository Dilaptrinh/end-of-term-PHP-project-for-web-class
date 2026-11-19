<?php
declare(strict_types=1);

/**
 * Dữ liệu riêng khu vực giáo viên.
 */

function data_students_for_class(int $classId): array
{
    $conn = view_db();
    $sql = "
        SELECT dkl.hoi_vien_id AS member_id, hv.ho_ten AS name, hv.sdt AS phone,
               hv.email, DATE_FORMAT(dkl.ngay_dang_ky, '%d/%m/%Y') AS registered
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
        return [];
    }

    $id = (int) $row['id'];
    $specialtyIds = [];
    $specialtyNames = [];
    $specialtyStmt = $conn->prepare(
        'SELECT cm.id, cm.ten_chuyen_mon
           FROM giao_vien_chuyen_mon gvcm
           JOIN chuyen_mon cm ON cm.id = gvcm.chuyen_mon_id AND cm.trang_thai = 1
          WHERE gvcm.giao_vien_id = ?
          ORDER BY cm.ten_chuyen_mon'
    );
    $specialtyStmt->bind_param('i', $id);
    $specialtyStmt->execute();
    foreach ($specialtyStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $specialtyRow) {
        $specialtyIds[] = (int) $specialtyRow['id'];
        $specialtyNames[] = (string) $specialtyRow['ten_chuyen_mon'];
    }
    $specialtyStmt->close();
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
        'specialty' => $specialtyNames ? implode(' · ', $specialtyNames) : '—',
        'specialty_ids' => $specialtyIds,
        'classes'   => $classes,
        'students'  => $students,
    ];
}

