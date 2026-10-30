# Làm việc song song theo role

Mục tiêu: 3 người làm **admin / customer / instructor** cùng lúc mà ít đụng file của nhau.

Nguyên tắc chính:

```text
Mỗi người sở hữu thư mục view + route + data + JS của role mình.
File dùng chung chỉ sửa khi thật cần, và phải báo trước.
Thêm route mới = thêm file trong src/Routes/... (index.php tự nạp).
```

---

## 1. Phân vai

| Người | Role | Phạm vi chính |
| --- | --- | --- |
| A | Admin | Quản trị hội viên, giáo viên, gói, lớp, đăng ký, duyệt lịch, báo cáo, tài khoản |
| B | Customer | Trang hội viên: tổng quan, đăng ký lớp, lịch tập, lịch sử, hồ sơ |
| C | Instructor | Trang giáo viên: tổng quan, lịch dạy, đăng ký lịch, lớp, tra cứu, hồ sơ |

---

## 2. Bản đồ sở hữu file

### 2.1. File mỗi người **được tự do sửa**

| Admin (A) | Customer (B) | Instructor (C) |
| --- | --- | --- |
| `src/Views/admin/*` | `src/Views/customer/*` | `src/Views/instructor/*` |
| `src/Routes/Pages/40-admin.php` | `src/Routes/Pages/20-customer.php` | `src/Routes/Pages/30-instructor.php` |
| `src/Routes/Api/3x-*.php` (domain admin) | `src/Routes/Api/4x-*.php` (domain customer) | `src/Routes/Api/5x-*.php` (domain instructor) |
| `src/Views/data/admin.php` | `src/Views/data/customer.php` | `src/Views/data/instructor.php` |
| `src/Views/partials/menu/admin.php` | `src/Views/partials/menu/customer.php` | `src/Views/partials/menu/instructor.php` |
| `public/assets/js/pages/admin-*.js` | `public/assets/js/pages/customer-*.js` | `public/assets/js/pages/instructor-*.js` |
| Repository/Service/Controller của domain mình | (tương tự) | (tương tự) |

### 2.2. File **dùng chung — phải báo trước**

| File | Ai sửa | Quy tắc |
| --- | --- | --- |
| `public/index.php` | Hạn chế tối đa | Chỉ sửa phần bootstrap/DB/middleware chung; **không thêm route** (dùng `src/Routes/`) |
| `src/Core/*` | Một người phụ trách | Thay đổi ảnh hưởng toàn hệ thống; PR riêng, review |
| `src/Routes/Pages/10-public.php` | Chung | Login/register/home |
| `src/Routes/Api/10-auth.php` | Chung | API đăng nhập |
| `src/Views/data/common.php` | Chung | Helper DB dùng chung |
| `src/Views/data/shared.php` | Chung | Dữ liệu nhiều role dùng (lớp, gói, giáo viên…) |
| `src/Views/partials/menu.php` | Chung | Chỉ chứa loader + `app_user`/`app_notifications` |
| `src/Views/layouts/*` | Chung | Thay đổi layout ảnh hưởng mọi role |
| `public/assets/css/app.css`, `pages.css` | Chung | Thêm CSS mới ở cuối file hoặc file riêng, báo team |
| `public/assets/js/api.js`, `ui.js` | Chung | Wrapper/UI toàn cục |
| `mysql/schema.php` | Chung | Xem mục 5 |
| `README.md`, `AGENTS.md`, `docs/` | Chung | Cập nhật khi thêm route/bảng/quy ước |
| `mysql/seed_accounts.php` | Chung | Tài khoản mẫu |

---

## 3. Cơ chế giảm xung đột đã có trong code

### 3.1. Route tự nạp

`public/index.php` không còn danh sách route dài. Nó tự nạp:

```php
// Trang
$pageRouteFiles = glob(__DIR__ . '/../src/Routes/Pages/*.php');
sort($pageRouteFiles);
foreach ($pageRouteFiles as $file) {
    require $file;
}

// API (chỉ khi DB OK)
$apiRouteFiles = glob(__DIR__ . '/../src/Routes/Api/*.php');
sort($apiRouteFiles);
foreach ($apiRouteFiles as $file) {
    require $file;
}
```

→ Thêm route mới chỉ cần **tạo file mới**, không sửa `index.php`.

Quy ước tên file:

```text
src/Routes/Pages/
  10-public.php
  20-customer.php
  30-instructor.php
  40-admin.php

src/Routes/Api/
  10-auth.php
  20-instructor.php
  30-member.php      (ví dụ admin làm)
  40-customer.php    (ví dụ customer làm)
  50-teacher.php     (ví dụ instructor làm)
```

Số đầu file quyết định thứ tự nạp (và thứ tự so khớp route). Trong cùng một domain, route tĩnh phải khai báo trước route `{id}`.

### 3.2. Dữ liệu view tách theo role

`src/Views/data/data.php` chỉ là loader:

```php
require_once __DIR__ . '/common.php';     // helper dùng chung
require_once __DIR__ . '/shared.php';     // dữ liệu nhiều role
require_once __DIR__ . '/admin.php';      // riêng admin
require_once __DIR__ . '/customer.php';   // riêng customer
require_once __DIR__ . '/instructor.php'; // riêng instructor
```

View vẫn chỉ cần:

```php
require_once __DIR__ . '/../data/data.php';
```

→ Mỗi người thêm `data_*()` vào file của role mình, hạn chế sửa chung.

### 3.3. Menu tách theo role

```text
src/Views/partials/menu.php        # loader + app_user/app_notifications
src/Views/partials/menu/admin.php
src/Views/partials/menu/customer.php
src/Views/partials/menu/instructor.php
```

Mỗi người sửa menu role mình.

### 3.4. JS theo trang

Mỗi trang một file trong `public/assets/js/pages/`. Không sửa `api.js`/`ui.js` nếu không thật cần.

---

## 4. Quy trình thêm một tính năng

### 4.1. Trang GET

1. Tạo/sửa view trong `src/Views/<role>/`.
2. Thêm `data_xxx()` vào `src/Views/data/<role>.php` (hoặc `shared.php` nếu nhiều role dùng).
3. Thêm route vào `src/Routes/Pages/<role>.php`.
4. Thêm menu vào `src/Views/partials/menu/<role>.php`.
5. Nếu cần JS: tạo `public/assets/js/pages/<role>-<ten>.js`, khai báo `'scripts' => [...]` trong route, tăng `?v=` trong layout.
6. Test trang + menu active + dữ liệu DB.

### 4.2. API

1. Bảng (nếu mới): thêm vào `mysql/schema.php` (báo team — xem mục 5).
2. Model (nếu cần): `src/Models/`.
3. Repository: `src/Repositories/`.
4. Service: `src/Services/`.
5. Controller: `src/Controllers/Api/`.
6. Route: tạo `src/Routes/Api/<số>-<domain>.php`, tự khởi tạo Repo → Service → Controller trong file đó.
7. JS: gọi `window.api`.
8. Test 201/401/403/422/500.

---

## 5. Khi cần sửa CSDL (file chung)

`mysql/schema.php` là file chung. Cách làm an toàn:

1. Báo trong nhóm: "Tôi thêm bảng/cột X cho tính năng Y".
2. Chỉ thêm **một khối** `CREATE TABLE IF NOT EXISTS` + seed có kiểm tra rỗng.
3. Không sửa/xóa bảng của người khác.
4. Chạy lại:

```bash
C:\xampp\php\php.exe mysql\setup.php
```

5. Test trên DB hiện tại; nếu cần reset, báo team trước.
6. Cập nhật README mục 11 (CSDL).
7. PR nhỏ, chỉ chứa thay đổi schema + code liên quan.

Tránh:

- Đổi tên cột/bảng đang dùng.
- Xóa dữ liệu seed của người khác.
- Sửa `seed_accounts.php` khi không liên quan.

---

## 6. Git và làm việc nhóm

### 6.1. Nhánh

```bash
git checkout main
git pull origin main
git checkout -b feature/admin-members-crud
```

Đặt tên nhánh theo role + tính năng:

```text
feature/admin-...
feature/customer-...
feature/instructor-...
fix/<role>-...
docs/...
```

### 6.2. Trong lúc làm

- Kéo `main` mỗi ngày:

```bash
git fetch origin
git rebase origin/main
```

- Commit nhỏ, message rõ:

```text
feat(admin): thêm CRUD hội viên
feat(customer): cập nhật hồ sơ cá nhân
feat(instructor): gửi yêu cầu lịch dạy
fix(auth): sửa redirect sau đăng nhập
```

### 6.3. Trước khi mở PR

```bash
C:\xampp\php\php.exe -l đường\dẫn\file.php
```

Checklist:

- [ ] Trang/API chạy với DB thật.
- [ ] Không sửa file ngoài phạm vi role (nếu có, ghi rõ lý do).
- [ ] Không có `var_dump`, `print_r`, log tạm.
- [ ] Đã test 401/403/422 nếu có.
- [ ] Cập nhật menu/route/data đúng file.
- [ ] Không commit secrets.

### 6.4. Review chéo

- A review B, B review C, C review A.
- Người review kiểm tra: route có nằm đúng file không, SQL có prepared statement không, view có `e()` không, có mock không.
- Chỉ merge khi CI/kiểm tra tay đạt.

### 6.5. Merge

- Ưu tiên rebase + merge commit nhỏ.
- Nếu conflict ở file chung (`shared.php`, `schema.php`, `app.css`): người tạo PR và người sở hữu file chung cùng xử lý, không tự ý ghi đè.

---

## 7. Ví dụ chia việc song song

| Tuần | Admin (A) | Customer (B) | Instructor (C) |
| --- | --- | --- | --- |
| 1 | CRUD hội viên (`MemberApiController`, route `30-member.php`, view admin) | Cập nhật hồ sơ + đổi mật khẩu (route `40-customer.php`) | Gửi yêu cầu lịch dạy (route `50-teacher.php`) |
| 2 | CRUD gói tập | Đăng ký lớp (API + JS) | Duyệt học viên |
| 3 | CRUD lớp + đăng ký | Lịch sử + lịch tập | Lịch dạy + lớp của tôi |

Mỗi tuần chỉ có 1 người sửa `mysql/schema.php` (hoặc chia nhau theo bảng), và thông báo trước.

---

## 8. Khi nào phải sửa file chung?

Chỉ khi:

- Cần middleware/quyền mới → `public/index.php` (phần DB block).
- Cần helper DB mới → `src/Views/data/common.php`.
- Cần dữ liệu nhiều role dùng → `src/Views/data/shared.php`.
- Cần bảng/cột mới → `mysql/schema.php`.
- Cần layout/UI toàn cục → `src/Views/layouts/`.
- Cần thay đổi cách gọi API → `public/assets/js/api.js`.

Khi đó:

1. Tạo PR riêng, nhỏ.
2. Ghi rõ lý do + ảnh hưởng.
3. Báo team trước khi merge.
4. Người còn lại pull ngay sau khi merge.

---

## 9. Tóm tắt một câu

```text
Mỗi role sở hữu route + view + data + menu + JS của mình.
Thêm route = thêm file, không sửa index.php.
File chung chỉ sửa khi thật cần, theo PR nhỏ và báo trước.
```
