<?php

function admin_username_exists(string $username): bool
{
    $stmt = common_db()->prepare('SELECT id FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool) $row;
}

function admin_availability_update(int $id, string $status, string $approver): bool
{
    $stmt = common_db()->prepare('UPDATE lich_ranh SET trang_thai = ?, nguoi_duyet = ? WHERE id = ?');
    $stmt->bind_param('ssi', $status, $approver, $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_class_save(array $values, int $id = 0): int
{
    $db = common_db();
    $columns = 'ten_lop,giao_vien_id,thu_trong_tuan,gio_bat_dau,gio_ket_thuc,ngay_bat_dau,ngay_ket_thuc,suc_chua,phong_hoc,mo_ta,trinh_do,trang_thai';

    if ($id === 0) {
        $marks = implode(',', array_fill(0, count($values), '?'));
        $stmt = $db->prepare("INSERT INTO lop_hoc ($columns) VALUES ($marks)");
        $stmt->bind_param('siissssissss', ...$values);
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
    $sets = implode(',', array_map(static fn ($column): string => "$column = ?", explode(',', $columns)));
    $stmt = $db->prepare("UPDATE lop_hoc SET $sets WHERE id = ?");
    $stmt->bind_param('siissssissssi', ...$values);
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

function admin_member_save(array $values, int $id = 0): int
{
    $db = common_db();
    $columns = 'ho_ten,sdt,email,gioi_tinh,ngay_sinh,dia_chi,ghi_chu,trang_thai';

    if ($id === 0) {
        $marks = implode(',', array_fill(0, count($values), '?'));
        $stmt = $db->prepare("INSERT INTO hoi_vien ($columns) VALUES ($marks)");
        $stmt->bind_param('ssssssss', ...$values);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    $exists = $db->prepare('SELECT id FROM hoi_vien WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $exists->bind_param('i', $id);
    $exists->execute();
    $found = $exists->get_result()->fetch_assoc();
    $exists->close();
    if (!$found) {
        return 0;
    }

    $values[] = $id;
    $sets = implode(',', array_map(static fn ($column): string => "$column = ?", explode(',', $columns)));
    $stmt = $db->prepare("UPDATE hoi_vien SET $sets WHERE id = ?");
    $stmt->bind_param('ssssssssi', ...$values);
    $stmt->execute();
    $stmt->close();
    return $id;
}

function admin_member_delete(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE hoi_vien SET deleted_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_package_save(array $values, int $id = 0): int
{
    $db = common_db();
    $columns = 'ten_goi,thoi_han_ngay,gia,mo_ta,trang_thai';

    if ($id === 0) {
        $stmt = $db->prepare("INSERT INTO goi_tap ($columns) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sidss', ...$values);
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
    $stmt = $db->prepare('UPDATE goi_tap SET ten_goi = ?, thoi_han_ngay = ?, gia = ?, mo_ta = ?, trang_thai = ? WHERE id = ?');
    $stmt->bind_param('sidssi', ...$values);
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

function admin_instructor_find(int $id): ?array
{
    $stmt = common_db()->prepare('SELECT gv.id, gv.ho_ten, gv.sdt, gv.email,
                                         COALESCE(GROUP_CONCAT(DISTINCT cm.ten_chuyen_mon ORDER BY cm.ten_chuyen_mon SEPARATOR " · "), gv.chuyen_mon) AS chuyen_mon,
                                         COALESCE(GROUP_CONCAT(DISTINCT cm.id ORDER BY cm.id), "") AS chuyen_mon_ids,
                                         gv.gioi_thieu, gv.trang_thai, tk.ten_dang_nhap
                                    FROM giao_vien gv JOIN tai_khoan tk ON tk.id = gv.tai_khoan_id
                                    LEFT JOIN giao_vien_chuyen_mon gvcm ON gvcm.giao_vien_id = gv.id
                                    LEFT JOIN chuyen_mon cm ON cm.id = gvcm.chuyen_mon_id AND cm.trang_thai = 1
                                   WHERE gv.id = ? AND gv.deleted_at IS NULL
                                   GROUP BY gv.id LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function admin_instructor_sync_specialties(int $instructorId, array $specialtyIds): void
{
    $db = common_db();
    $stmt = $db->prepare('DELETE FROM giao_vien_chuyen_mon WHERE giao_vien_id = ?');
    $stmt->bind_param('i', $instructorId);
    $stmt->execute();
    $stmt->close();
    $stmt = $db->prepare('INSERT INTO giao_vien_chuyen_mon (giao_vien_id, chuyen_mon_id) SELECT ?, id FROM chuyen_mon WHERE id = ? AND trang_thai = 1');
    foreach (array_unique(array_map('intval', $specialtyIds)) as $specialtyId) {
        if ($specialtyId <= 0) continue;
        $stmt->bind_param('ii', $instructorId, $specialtyId);
        $stmt->execute();
    }
    $stmt->close();
}

function admin_instructor_create(string $username, string $password, string $name, string $phone, string $email, array $specialtyIds, string $intro, string $status): int
{
    $db = common_db();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, "instructor", 1)');
    $stmt->bind_param('sss', $username, $hash, $name);
    $stmt->execute();
    $accountId = $stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare('INSERT INTO giao_vien (tai_khoan_id, ho_ten, sdt, email, gioi_thieu, trang_thai) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssss', $accountId, $name, $phone, $email, $intro, $status);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();
    admin_instructor_sync_specialties($newId, $specialtyIds);
    return $newId;
}

function admin_instructor_update(int $id, string $name, string $phone, string $email, array $specialtyIds, string $intro, string $status, string $password): bool
{
    $db = common_db();

    $exists = $db->prepare('SELECT id FROM giao_vien WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $exists->bind_param('i', $id);
    $exists->execute();
    $found = $exists->get_result()->fetch_assoc();
    $exists->close();
    if (!$found) {
        return false;
    }

    $stmt = $db->prepare('UPDATE giao_vien SET ho_ten = ?, sdt = ?, email = ?, gioi_thieu = ?, trang_thai = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $name, $phone, $email, $intro, $status, $id);
    $stmt->execute();
    $stmt->close();
    admin_instructor_sync_specialties($id, $specialtyIds);

    if ($password !== '' && strlen($password) >= 6) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE tai_khoan tk JOIN giao_vien gv ON gv.tai_khoan_id = tk.id SET tk.mat_khau = ? WHERE gv.id = ?');
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        $stmt->close();
    }

    return true;
}

function admin_instructor_delete(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE giao_vien SET deleted_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_account_find(int $id): ?array
{
    $stmt = common_db()->prepare(
        'SELECT id, ten_dang_nhap, ho_ten, vai_tro, trang_thai
           FROM tai_khoan WHERE id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function admin_account_save(string $username, string $name, string $role, string $password, int $active, int $id = 0): int
{
    $db = common_db();
    if ($id === 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare(
            'INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssi', $username, $hash, $name, $role, $active);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    $stmt = $db->prepare('UPDATE tai_khoan SET ho_ten = ?, vai_tro = ?, trang_thai = ? WHERE id = ?');
    $stmt->bind_param('ssii', $name, $role, $active, $id);
    $stmt->execute();
    $stmt->close();

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE tai_khoan SET mat_khau = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        $stmt->close();
    }
    return $id;
}

function admin_account_status(int $id, int $active): bool
{
    $stmt = common_db()->prepare('UPDATE tai_khoan SET trang_thai = ? WHERE id = ?');
    $stmt->bind_param('ii', $active, $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_account_reset_password(int $id, string $password): bool
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = common_db()->prepare('UPDATE tai_khoan SET mat_khau = ? WHERE id = ?');
    $stmt->bind_param('si', $hash, $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}

function admin_account_delete(int $id): bool
{
    $db = common_db();
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('DELETE FROM giao_vien WHERE tai_khoan_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('DELETE FROM hoi_vien WHERE tai_khoan_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('DELETE FROM tai_khoan WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        $db->commit();
        return $changed > 0;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
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

function admin_class_registration_cancel(int $id): bool
{
    $stmt = common_db()->prepare('UPDATE dang_ky_lop SET trang_thai = "da_huy" WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows;
    $stmt->close();
    return $changed > 0;
}
