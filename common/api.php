<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function api_json(array $body, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_input(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

function api_require_auth(?string $role = null): array
{
    $user = common_user();
    if (!$user) {
        api_json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
    }
    if ($role !== null && $user['vai_tro'] !== $role) {
        api_json(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
    }
    return $user;
}

function api_id(string $value): int
{
    $id = (int) $value;
    if ($id <= 0) {
        api_json(['success' => false, 'message' => 'ID không hợp lệ.'], 422);
    }
    return $id;
}

function api_row(string $table, int $id): ?array
{
    $allowed = ['hoi_vien', 'giao_vien', 'goi_tap', 'lop_hoc'];
    if (!in_array($table, $allowed, true)) {
        return null;
    }
    $stmt = common_db()->prepare("SELECT * FROM `$table` WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function api_validate(array $data, string $type): array
{
    $errors = [];
    if ($type === 'member') {
        if (mb_strlen(trim((string) ($data['ho_ten'] ?? ''))) < 2) $errors['ho_ten'] = 'Họ tên không hợp lệ.';
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ.';
        if (trim((string) ($data['sdt'] ?? '')) === '') $errors['sdt'] = 'Số điện thoại không hợp lệ.';
    } elseif ($type === 'package') {
        if (mb_strlen(trim((string) ($data['ten_goi'] ?? ''))) < 2) $errors['ten_goi'] = 'Tên gói không hợp lệ.';
        if ((int) ($data['thoi_han_ngay'] ?? 0) < 1) $errors['thoi_han_ngay'] = 'Thời hạn không hợp lệ.';
    } elseif ($type === 'class') {
        foreach (['ten_lop', 'gio_bat_dau', 'gio_ket_thuc', 'ngay_bat_dau', 'ngay_ket_thuc'] as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') $errors[$field] = 'Trường này bắt buộc.';
        }
        if ((int) ($data['giao_vien_id'] ?? 0) <= 0) $errors['giao_vien_id'] = 'Vui lòng chọn giáo viên.';
        if ((int) ($data['suc_chua'] ?? 0) < 1) $errors['suc_chua'] = 'Sức chứa không hợp lệ.';
    }
    return $errors;
}

function api_crud(string $table, string $type, string $idPart, string $method): never
{
    $user = api_require_auth();
    $db = common_db();
    if ($method === 'GET') {
        if ($idPart !== '') {
            $row = api_row($table, api_id($idPart));
            api_json($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Không tìm thấy dữ liệu.'], $row ? 200 : 404);
        }
        $result = $db->query("SELECT * FROM `$table` WHERE deleted_at IS NULL ORDER BY id DESC");
        api_json(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    }
    api_require_auth('admin');
    $data = api_input();
    if ($method === 'DELETE') {
        $id = api_id($idPart);
        $stmt = $db->prepare("UPDATE `$table` SET deleted_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        api_json(['success' => $changed > 0, 'message' => $changed ? 'Đã xóa dữ liệu.' : 'Không tìm thấy dữ liệu.'], $changed ? 200 : 404);
    }
    $errors = api_validate($data, $type);
    if ($errors) api_json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $errors], 422);
    if ($type === 'instructor') {
        $name = trim((string) ($data['ho_ten'] ?? $data['name'] ?? ''));
        $phone = trim((string) ($data['sdt'] ?? $data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') {
            api_json(['success' => false, 'message' => 'Dữ liệu giáo viên không hợp lệ.', 'errors' => ['ho_ten' => 'Họ tên, điện thoại và email là bắt buộc.']], 422);
        }
        if ($idPart === '') {
            $username = trim((string) ($data['ten_dang_nhap'] ?? $data['username'] ?? ''));
            $password = (string) ($data['mat_khau'] ?? $data['password'] ?? '');
            if ($username === '' || strlen($password) < 6) api_json(['success' => false, 'message' => 'Tài khoản giáo viên không hợp lệ.'], 422);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, "instructor", 1)');
            $stmt->bind_param('sss', $username, $hash, $name); $stmt->execute(); $accountId = $stmt->insert_id; $stmt->close();
            $specialty = trim((string) ($data['chuyen_mon'] ?? $data['specialty'] ?? ''));
            $status = (string) ($data['trang_thai'] ?? $data['status'] ?? 'dang_lam');
            $stmt = $db->prepare('INSERT INTO giao_vien (tai_khoan_id, ho_ten, sdt, email, chuyen_mon, trang_thai) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('isssss', $accountId, $name, $phone, $email, $specialty, $status); $stmt->execute(); $newId = $stmt->insert_id; $stmt->close();
            api_json(['success' => true, 'data' => ['id' => $newId], 'message' => 'Đã tạo giáo viên.'], 201);
        }
        $id = api_id($idPart);
        $specialty = trim((string) ($data['chuyen_mon'] ?? $data['specialty'] ?? ''));
        $status = (string) ($data['trang_thai'] ?? $data['status'] ?? 'dang_lam');
        $stmt = $db->prepare('UPDATE giao_vien SET ho_ten = ?, sdt = ?, email = ?, chuyen_mon = ?, trang_thai = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $name, $phone, $email, $specialty, $status, $id); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã cập nhật giáo viên.']);
    }
    $values = $type === 'member'
        ? [trim((string) $data['ho_ten']), trim((string) ($data['sdt'] ?? '')), trim((string) $data['email']), $data['gioi_tinh'] ?? 'khac', $data['ngay_sinh'] ?? null, $data['dia_chi'] ?? null, $data['ghi_chu'] ?? null, $data['trang_thai'] ?? 'hoat_dong']
        : ($type === 'package'
            ? [trim((string) $data['ten_goi']), (int) $data['thoi_han_ngay'], (float) ($data['gia'] ?? 0), $data['mo_ta'] ?? null, $data['trang_thai'] ?? 'dang_ban']
            : [trim((string) $data['ten_lop']), (int) $data['giao_vien_id'], (int) $data['thu_trong_tuan'], $data['gio_bat_dau'], $data['gio_ket_thuc'], $data['ngay_bat_dau'], $data['ngay_ket_thuc'], (int) $data['suc_chua'], $data['phong_hoc'] ?? null, $data['mo_ta'] ?? null, $data['trinh_do'] ?? null, $data['trang_thai'] ?? 'dang_mo']);
    $columns = $type === 'member'
        ? 'ho_ten,sdt,email,gioi_tinh,ngay_sinh,dia_chi,ghi_chu,trang_thai'
        : ($type === 'package' ? 'ten_goi,thoi_han_ngay,gia,mo_ta,trang_thai' : 'ten_lop,giao_vien_id,thu_trong_tuan,gio_bat_dau,gio_ket_thuc,ngay_bat_dau,ngay_ket_thuc,suc_chua,phong_hoc,mo_ta,trinh_do,trang_thai');
    $id = $idPart === '' ? 0 : api_id($idPart);
    if ($id === 0) {
        $marks = implode(',', array_fill(0, count($values), '?'));
        $types = $type === 'member' ? 'ssssssss' : ($type === 'package' ? 'sidss' : 'siissssi ssss');
        $types = str_replace(' ', '', $types);
        $stmt = $db->prepare("INSERT INTO `$table` ($columns) VALUES ($marks)");
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        api_json(['success' => true, 'data' => ['id' => $newId], 'message' => 'Đã tạo dữ liệu.'], 201);
    }
    $row = api_row($table, $id);
    if (!$row) api_json(['success' => false, 'message' => 'Không tìm thấy dữ liệu.'], 404);
    $sets = implode(',', array_map(static fn ($column): string => "$column = ?", explode(',', $columns)));
    $types = $type === 'member' ? 'ssssssssi' : ($type === 'package' ? 'sidssi' : 'siissssissssi');
    $values[] = $id;
    $stmt = $db->prepare("UPDATE `$table` SET $sets WHERE id = ?");
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();
    api_json(['success' => true, 'message' => 'Đã cập nhật dữ liệu.']);
}

$path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$path = preg_replace('#^.*?/(?:index\.php|api\.php)/?#', '', $path) ?: '';
$parts = $path === '' ? [] : explode('/', $path);
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (($parts[0] ?? '') !== 'api') api_json(['success' => false, 'message' => 'Không tìm thấy endpoint.'], 404);
$resource = $parts[1] ?? '';
$idPart = $parts[2] ?? '';

if ($resource === 'register' && $method === 'POST') {
    $input = api_input();
    $username = trim((string) ($input['ten_dang_nhap'] ?? $input['username'] ?? ''));
    $password = (string) ($input['mat_khau'] ?? $input['password'] ?? '');
    $name = trim((string) ($input['ho_ten'] ?? $input['name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    if (strlen($username) < 3 || strlen($password) < 6 || strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_json(['success' => false, 'message' => 'Thông tin đăng ký không hợp lệ.'], 422);
    }
    $db = common_db();
    $stmt = $db->prepare('INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ho_ten, vai_tro, trang_thai) VALUES (?, ?, ?, "customer", 1)');
    $hash = password_hash($password, PASSWORD_DEFAULT); $stmt->bind_param('sss', $username, $hash, $name); $stmt->execute(); $accountId = $stmt->insert_id; $stmt->close();
    $stmt = $db->prepare('INSERT INTO hoi_vien (tai_khoan_id, ho_ten, sdt, email, gioi_tinh, trang_thai) VALUES (?, ?, ?, ?, "khac", "hoat_dong")');
    $phone = trim((string) ($input['sdt'] ?? $input['phone'] ?? '')); $stmt->bind_param('isss', $accountId, $name, $phone, $email); $stmt->execute(); $stmt->close();
    api_json(['success' => true, 'message' => 'Tạo tài khoản thành công.'], 201);
}
if ($resource === 'profile') {
    $user = api_require_auth();
    $input = api_input(); $db = common_db(); $accountId = (int) $user['id'];
    if ($method === 'PUT') {
        $name = trim((string) ($input['ho_ten'] ?? $user['ho_ten']));
        $stmt = $db->prepare('UPDATE tai_khoan SET ho_ten = ? WHERE id = ?'); $stmt->bind_param('si', $name, $accountId); $stmt->execute(); $stmt->close();
        $table = $user['vai_tro'] === 'instructor' ? 'giao_vien' : 'hoi_vien';
        $stmt = $db->prepare("UPDATE `$table` SET ho_ten = ?, sdt = ?, email = ? WHERE tai_khoan_id = ?");
        $phone = trim((string) ($input['sdt'] ?? $input['phone'] ?? '')); $email = trim((string) ($input['email'] ?? ''));
        $stmt->bind_param('sssi', $name, $phone, $email, $accountId); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã cập nhật hồ sơ.']);
    }
    if (($parts[2] ?? '') === 'password' && $method === 'POST') {
        $old = (string) ($input['mat_khau_cu'] ?? $input['current_password'] ?? '');
        $new = (string) ($input['mat_khau_moi'] ?? $input['new_password'] ?? '');
        $stmt = $db->prepare('SELECT mat_khau FROM tai_khoan WHERE id = ?'); $stmt->bind_param('i', $accountId); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$row || !password_verify($old, (string) $row['mat_khau']) || strlen($new) < 6) api_json(['success' => false, 'message' => 'Mật khẩu hiện tại hoặc mật khẩu mới không hợp lệ.'], 422);
        $hash = password_hash($new, PASSWORD_DEFAULT); $stmt = $db->prepare('UPDATE tai_khoan SET mat_khau = ? WHERE id = ?'); $stmt->bind_param('si', $hash, $accountId); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã đổi mật khẩu.']);
    }
}
if ($resource === 'registrations') {
    $user = api_require_auth();
    $db = common_db(); $kind = $parts[2] ?? ''; $action = $parts[4] ?? ''; $registrationId = $parts[3] ?? '';
    if ($kind === 'classes' && $method === 'POST' && $registrationId === '') {
        api_require_auth('customer'); $input = api_input(); $classId = (int) ($input['lop_hoc_id'] ?? 0);
        $stmt = $db->prepare('SELECT id FROM hoi_vien WHERE tai_khoan_id = ? LIMIT 1'); $accountId = (int) $user['id']; $stmt->bind_param('i', $accountId); $stmt->execute(); $member = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$member || $classId <= 0) api_json(['success' => false, 'message' => 'Dữ liệu đăng ký không hợp lệ.'], 422);
        $memberId = (int) $member['id']; $stmt = $db->prepare('INSERT INTO dang_ky_lop (hoi_vien_id, lop_hoc_id, trang_thai, ngay_dang_ky) VALUES (?, ?, "dang_ky", NOW())'); $stmt->bind_param('ii', $memberId, $classId); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã đăng ký lớp học.'], 201);
    }
    if ($kind === 'classes' && $action === 'cancel') {
        $id = api_id($registrationId); $stmt = $db->prepare('UPDATE dang_ky_lop SET trang_thai = "da_huy" WHERE id = ?'); $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã hủy đăng ký lớp.']);
    }
    if ($kind === 'packages' && $action === 'confirm') {
        api_require_auth('admin'); $id = api_id($registrationId); $input = api_input(); $methodName = trim((string) ($input['phuong_thuc'] ?? 'tien_mat'));
        $stmt = $db->prepare('UPDATE dang_ky_goi SET trang_thai = "da_thanh_toan", phuong_thuc = ?, ngay_thanh_toan = NOW() WHERE id = ?'); $stmt->bind_param('si', $methodName, $id); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã xác nhận thanh toán.']);
    }
    if ($kind === 'packages' && $action === 'cancel') {
        api_require_auth('admin'); $id = api_id($registrationId); $stmt = $db->prepare('UPDATE dang_ky_goi SET trang_thai = "da_huy" WHERE id = ?'); $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã hủy đăng ký gói.']);
    }
}

if ($resource === 'auth' && ($parts[2] ?? '') === 'login' && $method === 'POST') {
    $input = api_input();
    $stmt = common_db()->prepare('SELECT id, mat_khau, trang_thai, vai_tro FROM tai_khoan WHERE ten_dang_nhap = ? LIMIT 1');
    $username = trim((string) ($input['ten_dang_nhap'] ?? $input['username'] ?? ''));
    $password = (string) ($input['mat_khau'] ?? $input['password'] ?? '');
    $stmt->bind_param('s', $username); $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc() ?: null; $stmt->close();
    if (!$account || (int) $account['trang_thai'] !== 1 || !password_verify($password, (string) $account['mat_khau'])) api_json(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.'], 422);
    session_regenerate_id(true); $_SESSION['tai_khoan_id'] = (int) $account['id'];
    api_json(['success' => true, 'data' => ['role' => $account['vai_tro'], 'vai_tro' => $account['vai_tro']], 'message' => 'Đăng nhập thành công.']);
}
if ($resource === 'auth' && ($parts[2] ?? '') === 'logout') {
    $_SESSION = []; session_destroy(); api_json(['success' => true, 'message' => 'Đã đăng xuất.']);
}
if ($resource === 'members') api_crud('hoi_vien', 'member', $idPart, $method);
if ($resource === 'instructors') api_crud('giao_vien', 'instructor', $idPart, $method);
if ($resource === 'packages') api_crud('goi_tap', 'package', $idPart, $method);
if ($resource === 'classes') api_crud('lop_hoc', 'class', $idPart, $method);
if ($resource === 'availability') {
    $user = api_require_auth();
    if ($method === 'GET') {
        $rows = common_db()->query('SELECT lr.*, gv.ho_ten AS instructor FROM lich_ranh lr JOIN giao_vien gv ON gv.id = lr.giao_vien_id ORDER BY lr.ngay DESC, lr.id DESC')->fetch_all(MYSQLI_ASSOC);
        api_json(['success' => true, 'data' => $rows]);
    }
    $id = $idPart !== '' ? api_id($idPart) : 0;
    if (($parts[3] ?? '') === 'approve' || ($parts[3] ?? '') === 'reject') {
        api_require_auth('admin');
        $status = ($parts[3] ?? '') === 'approve' ? 'da_duyet' : 'tu_choi';
        $stmt = common_db()->prepare('UPDATE lich_ranh SET trang_thai = ?, nguoi_duyet = ? WHERE id = ?');
        $name = (string) $user['ho_ten']; $stmt->bind_param('ssi', $status, $name, $id); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã cập nhật lịch dạy.']);
    }
    api_require_auth('instructor');
    $input = api_input();
    $instructor = common_db()->prepare('SELECT id FROM giao_vien WHERE tai_khoan_id = ? LIMIT 1');
    $accountId = (int) $user['id']; $instructor->bind_param('i', $accountId); $instructor->execute(); $row = $instructor->get_result()->fetch_assoc(); $instructor->close();
    if (!$row) api_json(['success' => false, 'message' => 'Không tìm thấy hồ sơ giáo viên.'], 404);
    if (($parts[3] ?? '') === 'cancel') {
        $stmt = common_db()->prepare('UPDATE lich_ranh SET trang_thai = "da_huy" WHERE id = ?'); $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
        api_json(['success' => true, 'message' => 'Đã hủy lịch dạy.']);
    }
    $date = (string) ($input['ngay'] ?? $input['date'] ?? '');
    $start = (string) ($input['gio_bat_dau'] ?? $input['start'] ?? '');
    $end = (string) ($input['gio_ket_thuc'] ?? $input['end'] ?? '');
    $stmt = common_db()->prepare('INSERT INTO lich_ranh (giao_vien_id, ngay, gio_bat_dau, gio_ket_thuc, trang_thai) VALUES (?, ?, ?, ?, "cho_duyet")');
    $instructorId = (int) $row['id']; $stmt->bind_param('isss', $instructorId, $date, $start, $end); $stmt->execute(); $stmt->close();
    api_json(['success' => true, 'message' => 'Đã gửi yêu cầu lịch dạy.'], 201);
}
api_json(['success' => false, 'message' => 'Không tìm thấy endpoint.'], 404);
