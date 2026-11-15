<?php

function admin_account_update_name(int $accountId, string $name): bool
{
    $stmt = common_db()->prepare('UPDATE tai_khoan SET ho_ten = ? WHERE id = ? AND vai_tro = "admin"');
    $stmt->bind_param('si', $name, $accountId);
    $stmt->execute();
    $stmt->close();
    return true;
}

function admin_account_password_hash(int $accountId): ?string
{
    $stmt = common_db()->prepare('SELECT mat_khau FROM tai_khoan WHERE id = ? AND vai_tro = "admin" LIMIT 1');
    $stmt->bind_param('i', $accountId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (string) $row['mat_khau'] : null;
}

function admin_account_update_password(int $accountId, string $passwordHash): bool
{
    $stmt = common_db()->prepare('UPDATE tai_khoan SET mat_khau = ? WHERE id = ? AND vai_tro = "admin"');
    $stmt->bind_param('si', $passwordHash, $accountId);
    $stmt->execute();
    $stmt->close();
    return true;
}

function admin_username_exists(string $username): bool
{
    $stmt = common_db()->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool) $row;
}

function admin_class_proposal_update(int $id, string $status, string $room = '', string $requiredPackage = 'basic'): bool
{
    if ($status === 'dang_mo') {
        $stmt = common_db()->prepare('UPDATE lop_hoc SET phong_hoc = ?, goi_tap_toi_thieu = ?, trang_thai = ? WHERE id = ? AND trang_thai = "cho_duyet" AND deleted_at IS NULL');
        $stmt->bind_param('sssi', $room, $requiredPackage, $status, $id);
    } else {
        $stmt = common_db()->prepare('UPDATE lop_hoc SET trang_thai = ? WHERE id = ? AND trang_thai = "cho_duyet" AND deleted_at IS NULL');
        $stmt->bind_param('si', $status, $id);
    }
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_class_save(array $values, int $id = 0): int
{
    $db = common_db();
    $columns = 'ten_lop,giao_vien_id,thu_trong_tuan,gio_bat_dau,gio_ket_thuc,ngay_bat_dau,ngay_ket_thuc,suc_chua,phong_hoc,mo_ta,trinh_do,goi_tap_toi_thieu,trang_thai';

    if ($id === 0) {
        $marks = implode(',', array_fill(0, count($values), '?'));
        $stmt = $db->prepare("INSERT INTO lop_hoc ($columns) VALUES ($marks)");
        $stmt->bind_param('siissssisssss', ...$values);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    $exists = $db->prepare('SELECT id FROM lop_hoc WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $exists->bind_param('i', $id);
    $exists->execute();
    $found = $exists->get_result()->fetch_assoc();
    $exists->close();
    if (!$found) {
        return 0;
    }

    $values[] = $id;
    $setParts = [];
    foreach (explode(',', $columns) as $column) {
        $setParts[] = $column . ' = ?';
    }
    $sets = implode(',', $setParts);
    $stmt = $db->prepare("UPDATE lop_hoc SET $sets WHERE id = ?");
    $stmt->bind_param('siissssisssssi', ...$values);
    $stmt->execute();
    $stmt->close();
    return $id;
}

function admin_class_delete(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE lop_hoc SET deleted_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_package_save(array $values, int $id = 0): int
{
    $db = common_db();
    $columns = 'ten_goi,thoi_han_ngay,gia,mo_ta,cap_do,trang_thai';

    if ($id === 0) {
        $stmt = $db->prepare("INSERT INTO goi_tap ($columns) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sidsss', ...$values);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    $exists = $db->prepare('SELECT id FROM goi_tap WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $exists->bind_param('i', $id);
    $exists->execute();
    $found = $exists->get_result()->fetch_assoc();
    $exists->close();
    if (!$found) {
        return 0;
    }

    $values[] = $id;
    $stmt = $db->prepare('UPDATE goi_tap SET ten_goi = ?, thoi_han_ngay = ?, gia = ?, mo_ta = ?, cap_do = ?, trang_thai = ? WHERE id = ?');
    $stmt->bind_param('sidsssi', ...$values);
    $stmt->execute();
    $stmt->close();
    return $id;
}

function admin_package_delete(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE goi_tap SET deleted_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_instructor_sync_specialties(int $instructorId, array $specialtyIds): void
{
    $db = common_db();
    $stmt = $db->prepare('DELETE FROM giao_vien_chuyen_mon WHERE giao_vien_id = ?');
    $stmt->bind_param('i', $instructorId);
    $stmt->execute();
    $stmt->close();
    $stmt = $db->prepare('INSERT INTO giao_vien_chuyen_mon (giao_vien_id, chuyen_mon_id) SELECT ?, id FROM chuyen_mon WHERE id = ? AND trang_thai = 1');
    $usedSpecialtyIds = [];
    foreach ($specialtyIds as $specialtyId) {
        $specialtyId = (int) $specialtyId;
        if ($specialtyId <= 0 || isset($usedSpecialtyIds[$specialtyId])) {
            continue;
        }
        $usedSpecialtyIds[$specialtyId] = true;
        $stmt->bind_param('ii', $instructorId, $specialtyId);
        $stmt->execute();
    }
    $stmt->close();
}

function admin_account_detail(int $id): ?array
{
    $stmt = common_db()->prepare(
        'SELECT tk.id, tk.ten_dang_nhap, tk.ho_ten, tk.vai_tro, tk.trang_thai, tk.created_at,
                COALESCE(gv.sdt, hv.sdt, "") AS sdt,
                COALESCE(gv.email, hv.email, "") AS email,
                COALESCE(GROUP_CONCAT(DISTINCT cm.ten_chuyen_mon ORDER BY cm.ten_chuyen_mon SEPARATOR " · "), "") AS specialty,
                COALESCE(GROUP_CONCAT(DISTINCT cm.id ORDER BY cm.id), "") AS specialty_ids,
                gv.gioi_thieu,
                gv.trang_thai AS instructor_status,
                hv.gioi_tinh, hv.ngay_sinh, hv.dia_chi, hv.muc_tieu,
                hv.chieu_cao, hv.can_nang
           FROM tai_khoan tk
           LEFT JOIN giao_vien gv ON gv.tai_khoan_id = tk.id AND gv.deleted_at IS NULL
           LEFT JOIN giao_vien_chuyen_mon gvcm ON gvcm.giao_vien_id = gv.id
           LEFT JOIN chuyen_mon cm ON cm.id = gvcm.chuyen_mon_id AND cm.trang_thai = 1
           LEFT JOIN hoi_vien hv ON hv.tai_khoan_id = tk.id AND hv.deleted_at IS NULL
          WHERE tk.id = ?
          GROUP BY tk.id
          LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function admin_account_update_details(int $id, string $name, string $phone, string $email, int $active, ?string $birth, ?string $address, ?string $goal, ?string $height, ?string $weight, ?string $intro, array $specialtyIds): bool
{
    $db = common_db();
    $stmt = $db->prepare('UPDATE tai_khoan SET ho_ten = ?, trang_thai = ? WHERE id = ?');
    $stmt->bind_param('sii', $name, $active, $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();

    $stmt = $db->prepare('UPDATE giao_vien SET ho_ten = ?, sdt = ?, email = ?, gioi_thieu = ? WHERE tai_khoan_id = ?');
    $stmt->bind_param('ssssi', $name, $phone, $email, $intro, $id);
    $stmt->execute();
    $changed = max($changed, $stmt->affected_rows);
    $stmt->close();

    $stmt = $db->prepare(
        'UPDATE hoi_vien SET ho_ten = ?, sdt = ?, email = ?, ngay_sinh = ?, dia_chi = ?, muc_tieu = ?, chieu_cao = ?, can_nang = ?
          WHERE tai_khoan_id = ?'
    );
    $stmt->bind_param('ssssssssi', $name, $phone, $email, $birth, $address, $goal, $height, $weight, $id);
    $stmt->execute();
    $changed = max($changed, $stmt->affected_rows);
    $stmt->close();

    $stmt = $db->prepare('SELECT id FROM giao_vien WHERE tai_khoan_id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $instructor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($instructor) {
        admin_instructor_sync_specialties((int) $instructor['id'], $specialtyIds);
    }

    return $changed > 0;
}

function admin_account_create(string $username, string $password, string $name, string $role, string $phone, string $email): int
{
    $db = common_db();
    $db->begin_transaction();

    try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare(
            'INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai)
             VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->bind_param('ssss', $username, $hash, $name, $role);
        $stmt->execute();
        $accountId = $stmt->insert_id;
        $stmt->close();

        if ($role === 'instructor') {
            $status = 'dang_lam';
            $stmt = $db->prepare(
                'INSERT INTO giao_vien (tai_khoan_id, ho_ten, sdt, email, trang_thai)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('issss', $accountId, $name, $phone, $email, $status);
            $stmt->execute();
            $stmt->close();
        } elseif ($role === 'customer') {
            $gender = 'khac';
            $stmt = $db->prepare(
                'INSERT INTO hoi_vien (tai_khoan_id, ho_ten, sdt, email, gioi_tinh, trang_thai)
                 VALUES (?, ?, ?, ?, ?, "hoat_dong")'
            );
            $stmt->bind_param('issss', $accountId, $name, $phone, $email, $gender);
            $stmt->execute();
            $stmt->close();
        }

        $db->commit();
        return $accountId;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}

function admin_account_status(int $id, int $active): bool
{
    $stmt = common_db()->prepare('UPDATE tai_khoan SET trang_thai = ? WHERE id = ? AND vai_tro <> "admin"');
    $stmt->bind_param('ii', $active, $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_package_registration_create(int $memberId, int $packageId, string $from, string $to, ?string $method): int
{
    $db = common_db();

    $stmt = $db->prepare('SELECT gia FROM goi_tap WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('i', $packageId);
    $stmt->execute();
    $package = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$package) {
        return 0;
    }

    $price = (int) $package['gia'];
    $stmt = $db->prepare('INSERT INTO dang_ky_goi (hoi_vien_id, goi_tap_id, ngay_bat_dau, ngay_ket_thuc, gia_tai_thoi_diem, phuong_thuc, trang_thai) VALUES (?, ?, ?, ?, ?, ?, "chua_thanh_toan")');
    $stmt->bind_param('iissis', $memberId, $packageId, $from, $to, $price, $method);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return $newId;
}

function admin_package_registration_confirm(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE dang_ky_goi SET trang_thai = "da_thanh_toan", phuong_thuc = "tien_mat", ngay_thanh_toan = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_package_registration_cancel(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE dang_ky_goi SET trang_thai = "da_huy" WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}
