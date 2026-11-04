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
    $stmt = common_db()->prepare('SELECT gv.id, gv.ho_ten, gv.sdt, gv.email, gv.chuyen_mon, gv.gioi_thieu, gv.trang_thai, tk.ten_dang_nhap
                                    FROM giao_vien gv JOIN tai_khoan tk ON tk.id = gv.tai_khoan_id
                                   WHERE gv.id = ? AND gv.deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function admin_instructor_create(string $username, string $password, string $name, string $phone, string $email, string $specialty, string $intro, string $status): int
{
    $db = common_db();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, "instructor", 1)');
    $stmt->bind_param('sss', $username, $hash, $name);
    $stmt->execute();
    $accountId = $stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare('INSERT INTO giao_vien (tai_khoan_id, ho_ten, sdt, email, chuyen_mon, gioi_thieu, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('issssss', $accountId, $name, $phone, $email, $specialty, $intro, $status);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    return $newId;
}

function admin_instructor_update(int $id, string $name, string $phone, string $email, string $specialty, string $intro, string $status, string $password): bool
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

    $stmt = $db->prepare('UPDATE giao_vien SET ho_ten = ?, sdt = ?, email = ?, chuyen_mon = ?, gioi_thieu = ?, trang_thai = ? WHERE id = ?');
    $stmt->bind_param('ssssssi', $name, $phone, $email, $specialty, $intro, $status, $id);
    $stmt->execute();
    $stmt->close();

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
