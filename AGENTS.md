# AGENTS.md — Hướng dẫn cho AI agent và đồng đội

File này mô tả **luật chơi** của dự án FitZone. Đọc trước khi sửa code. Mục tiêu: code mới đúng cấu trúc, không phá luồng hiện có.

## 1. Tóm tắt dự án

- PHP thuần, tự viết MVC, không framework, không Composer.
- Chạy trên XAMPP (Apache + MySQL + PHP 8.0+).
- Front controller duy nhất: `public/index.php`.
- Dữ liệu thật từ MySQL; **không mock**.
- CSRF **đã bỏ** theo yêu cầu môn học; không thêm lại.
- Tài liệu luồng: `docs/workflow-tong-hop.md`; cú pháp PHP: `docs/tao-giao-vien-workflow.md`.

## 2. Chạy dự án

```bash
C:\xampp\php\php.exe mysql\setup.php
```

Mở: `http://localhost/Project_ck/public`

Tài khoản: `admin` / `123456` (và các tài khoản trong `mysql/seed_accounts.php`).

Kiểm tra cú pháp:

```bash
C:\xampp\php\php.exe -l đường\dẫn\file.php
```

## 3. Bản đồ kiến trúc

```text
public/index.php        khai báo route + dispatch, KHÔNG chứa nghiệp vụ
src/Core/Router.php     lọc method + path, chạy middleware, gọi handler
src/Controllers/Api/    nhận HTTP, đọc Request, trả Response
src/Services/           validate + nghiệp vụ, không biết HTTP
src/Repositories/       SQL, prepared statements
src/Models/             object giữ dữ liệu + getter
src/Views/              HTML; dữ liệu qua src/Views/data/data.php
mysql/                  config, schema, seed, setup
```

Luật phụ thuộc:

```text
Controller → Service → Repository → MySQL
```

Không được đảo ngược (Repository không gọi Service, View không gọi Controller).

## 4. Bất biến (không được phá)

1. **Không tạo mock data** hoặc file JSON giả. Dữ liệu phải từ MySQL.
2. **Không thêm CSRF/XSRF**. Dự án đã bỏ theo yêu cầu.
3. **Route API nằm trong `if ($db !== null)`** ở `public/index.php`. Ngoài khối này không có `$db`, `$auth`, `$requireAuth`...
4. **Mọi response API dùng `Response::json()`** và `exit` (hàm đã tự exit).
5. **SQL dùng prepared statements**: `prepare()` + `bind_param()`, không nối chuỗi giá trị.
6. **Mật khẩu**: `password_hash()` / `password_verify()`, không lưu thô.
7. **Escape HTML** bằng `e()` trong view.
8. **Không tin dữ liệu client**: validate lại ở Service.
9. **Không đổi tên route/bảng hiện có** nếu không cập nhật toàn bộ tham chiếu (README, JS, view).
10. **Không commit** file log, file tạm, `.env`, mật khẩu thật.

## 5. Thêm trang GET

1. View: `src/Views/<role>/<ten-trang>.php`
   - `require_once __DIR__ . '/../data/data.php';`
   - Lấy dữ liệu qua `data_*()`.
   - In dữ liệu bằng `e()`.
2. Hàm dữ liệu: thêm `data_xxx()` trong `src/Views/data/data.php`.
   - Dùng `view_db()`.
   - Trả mảng kết hợp.
   - Ép số bằng `view_cast_ints()` nếu cần tính toán.
3. Route: `public/index.php`

```php
$router->add('GET', '/admin/xxx', function (): void {
    Page::app('admin', 'admin/xxx', [], [
        'title' => '...',
        'active' => 'xxx',
        'scripts' => ['admin-xxx.js'], // nếu có
    ]);
});
```

4. Menu: thêm item vào `app_menu($role)` trong `src/Views/partials/menu.php`.
5. Nếu có JS: thêm file vào `public/assets/js/pages/`, tăng `?v=` trong `src/Views/layouts/app.php`.

**Không** viết SQL trực tiếp trong view nếu có thể đặt vào `data.php`.

## 6. Thêm API

1. **Bảng**: bổ sung `CREATE TABLE` + seed trong `mysql/schema.php`.
2. **Model** (nếu cần): `src/Models/Xxx.php`.
3. **Repository**: `src/Repositories/XxxRepository.php`

```php
class XxxRepository extends BaseRepository
{
    protected string $table = 'xxx';
}
```

4. **Service**: `src/Services/XxxService.php`
   - Nhận repository qua constructor.
   - `normalize()` + `validate()`.
   - Trả `['ok' => bool, 'errors' => [], 'id' => ?int]`.
5. **Controller**: `src/Controllers/Api/XxxApiController.php`
   - Đọc `Request::json()`.
   - Gọi Service.
   - Trả 201/200/400/401/403/404/415/422/500 đúng nghĩa.
6. **Khởi tạo + route** trong `public/index.php`, bên trong `if ($db !== null)`:

```php
$xxxRepo = new XxxRepository($db);
$xxxService = new XxxService($xxxRepo);
$xxxController = new XxxApiController($xxxService);

$router->add('POST', '/api/xxx', [$xxxController, 'store'], [$requireAuth, $requireAdmin]);
```

7. **JS**: gọi qua `window.api.post('/api/xxx', payload)`.

## 7. Quy ước JSON

```php
Response::json(['success' => true, 'data' => $data, 'message' => '...'], 200);
Response::json(['success' => false, 'message' => '...', 'errors' => $errors], 422);
```

- Thành công tạo mới: `201`.
- Lỗi validate: `422`, `errors` là map `field => message`.
- Không lộ exception/DB ra client; ghi `error_log()`.

## 8. Quy ước đặt tên

| Loại | Kiểu | Ví dụ |
| --- | --- | --- |
| Namespace | `App\<Layer>` | `App\Services` |
| Class | PascalCase | `InstructorService` |
| Method | camelCase | `create`, `getAll` |
| Biến | camelCase | `$instructorRepo` |
| Bảng/cột | snake_case | `tai_khoan`, `ten_dang_nhap` |
| Route | kebab-case | `/admin/instructors` |
| View/JS | kebab-case | `class-registration.php` |

## 9. Những quirk phải nhớ

- **Base URL**: `base_url()` = thư mục chứa `index.php` (`/Project_ck/public`). Đừng hardcode.
- **API base trong JS**: `window.APP_BASE + '/index.php'`, nên URL là `.../index.php/api/...`.
- **Route order**: route tĩnh phải đăng ký trước route `{id}`.
- **`match()`**: `[]` là khớp, `null` là không khớp. Không dùng `if ($params)`.
- **DB lỗi**: `$db = null` → mọi route API không được đăng ký → 404 JSON.
- **`bind_param`**: tham số phải là biến, không truyền literal.
- **MySQL trả chuỗi**: ép `(int)` khi tính toán.
- **Cache JS**: đổi file nhớ tăng `?v=` trong layout.
- **`.htaccess` gốc `htdocs`** không thuộc repo; máy khác dùng URL đầy đủ.

## 10. Trạng thái hiện tại

Đã xong:
- Đăng nhập, phân quyền admin.
- CRUD giáo viên + tạo tài khoản giáo viên.
- Toàn bộ trang đọc dữ liệu MySQL.
- Schema + seed tự động.

Chưa xong (nếu làm tiếp):
- CRUD hội viên, gói tập, lớp học, đăng ký, duyệt lịch.
- Cập nhật hồ sơ/mật khẩu cho customer/instructor.
- Middleware đăng nhập cho các route trang GET.
- Đăng ký tài khoản hội viên thật (route `/register` hiện chỉ redirect).

## 11. Checklist trước khi commit

- [ ] `php -l` không lỗi.
- [ ] Trang/API chạy được với DB thật.
- [ ] Đã test 401/403/422 nếu có phân quyền/validate.
- [ ] Không còn `var_dump`, `print_r`, `dd()`.
- [ ] Không có mock/CSRF mới.
- [ ] Route mới đã ghi vào README.
- [ ] Bảng mới đã có trong `mysql/schema.php`.
- [ ] JS đổi thì tăng `?v=`.

## 12. Git

```bash
git checkout -b feature/ten-tinh-nang
git add .
git commit -m "feat: mô tả ngắn"
git push -u origin feature/ten-tinh-nang
```

Không commit trực tiếp thay đổi lớn lên `main` khi chưa review. Không commit secrets.

## 13. Đọc thêm

- `docs/workflow-tong-hop.md` — luồng GET/POST đầy đủ.
- `docs/tao-giao-vien-workflow.md` — giải thích cú pháp PHP qua code thật.
- `README.md` — cài đặt, cấu trúc, quy ước, route.
