# FitZone — Hệ thống quản lý phòng tập & lớp học

Ứng dụng PHP thuần (tự viết MVC) cho đồ án môn học. Toàn bộ dữ liệu hiển thị được đọc từ MySQL; **không còn mock data**.

Tài liệu này là nguồn tham chiếu chính cho cả đồng đội và AI agent. Đọc hết mục **5 → 9** trước khi thêm code mới.

---

## 1. Chức năng

### Quản trị viên
- Tổng quan doanh thu, hội viên, lớp học.
- Quản lý hội viên, giáo viên, gói tập, lớp học.
- Quản lý đăng ký gói/lớp, duyệt lịch dạy.
- Quản lý tài khoản, thống kê, tra cứu toàn hệ thống.

### Giáo viên
- Lịch dạy, lớp đang phụ trách, đăng ký lịch dạy.
- Tra cứu học viên, quản lý tài khoản.

### Hội viên
- Đăng ký lớp học, xem lịch tập và lịch sử hoạt động.
- Quản lý hồ sơ cá nhân.

### Trạng thái hoàn thiện
- ✅ Đăng nhập, phân quyền admin, CRUD giáo viên (kèm tài khoản đăng nhập).
- ✅ Toàn bộ trang đọc dữ liệu thật từ MySQL.
- ⚠️ Các form thêm/sửa hội viên, gói tập, lớp, đăng ký mới chỉ hiển thị giao diện; chưa có endpoint lưu.
- ⚠️ Các route trang (GET) chưa gắn middleware đăng nhập (phục vụ demo đồ án).

---

## 2. Yêu cầu

- XAMPP: Apache + MySQL + PHP 8.0+
- Trình duyệt: Chrome/Edge/Firefox
- Không cần Node/npm để chạy

---

## 3. Cài đặt

1. Khởi động **Apache** và **MySQL** trong XAMPP.
2. Kiểm tra kết nối trong `mysql/config.php`.
3. Tạo CSDL và dữ liệu nền:

```bash
C:\xampp\php\php.exe mysql\setup.php
```

Nếu CSDL trống, ứng dụng cũng tự tạo cấu trúc + dữ liệu nền ở lần truy cập đầu tiên.

4. Mở:

```text
http://localhost/Project_ck/public
```

> Nếu đã cấu hình `.htaccess` ở gốc `htdocs` (xem mục 11), có thể gọi tắt `http://localhost/api/...`.

---

## 4. Tài khoản mẫu

| Vai trò | Tên đăng nhập | Mật khẩu |
| --- | --- | --- |
| Quản trị viên | `admin` | `123456` |
| Giáo viên | `thuha`, `ducanh`, `ngocmai`, `quocbao`, `hongnhung` | `123456` |
| Hội viên | `hoangnam` | `123456` |

Tài khoản do `mysql/seed_accounts.php` tạo. Không hiển thị danh sách này trên trang login.

---

## 5. Cấu trúc thư mục

```text
Project_ck/
├── public/                       # Document root (Apache trỏ vào đây)
│   ├── index.php                 # FRONT CONTROLLER: bootstrap + khai báo route
│   ├── .htaccess                 # Đưa URL không tồn tại về index.php
│   └── assets/
│       ├── css/                  # tokens.css, app.css, pages.css
│       ├── js/
│       │   ├── api.js            # Wrapper fetch dùng chung
│       │   ├── ui.js             # Toast, sidebar, hành vi UI
│       │   └── pages/            # JS riêng từng trang
│       └── vendor/               # Bootstrap, Bootstrap Icons
│
├── src/
│   ├── bootstrap.php             # Autoload PSR-4 + helpers
│   ├── Core/                     # Hạ tầng dùng chung
│   │   ├── Router.php            # Định tuyến URL
│   │   ├── Request.php           # Đọc body JSON
│   │   ├── Response.php          # Trả JSON + status
│   │   ├── Session.php           # Session an toàn
│   │   ├── Auth.php              # Đăng nhập + phân quyền
│   │   ├── Page.php              # Render view vào layout
│   │   ├── View.php              # Render view đơn giản (legacy)
│   │   └── helpers.php           # url(), redirect(), e(), money(), weekday()
│   │
│   ├── Controllers/Api/          # TẦNG HTTP
│   │   ├── AuthController.php
│   │   └── InstructorApiController.php
│   │
│   ├── Services/                 # TẦNG NGHIỆP VỤ
│   │   └── InstructorService.php
│   │
│   ├── Repositories/             # TẦNG SQL
│   │   ├── BaseRepository.php
│   │   ├── InstructorRepository.php
│   │   └── TaiKhoanRepository.php
│   │
│   ├── Models/                   # Object giữ dữ liệu
│   │   ├── GiaoVien.php
│   │   ├── TaiKhoan.php
│   │   ├── HoiVien.php
│   │   ├── LopHoc.php
│   │   ├── GoiTap.php
│   │   ├── DangKyGoi.php
│   │   └── DangKyLop.php
│   │
│   └── Views/
│       ├── layouts/              # public.php, auth.php, app.php
│       ├── partials/             # sidebar, topbar, menu, flash
│       ├── components/ui.php     # icon(), badge(), avatar(), ...
│       ├── data/data.php         # Truy vấn dữ liệu cho view
│       ├── admin/                # Trang quản trị
│       ├── customer/             # Trang hội viên
│       ├── instructor/           # Trang giáo viên
│       ├── auth/                 # login, register
│       └── public/               # Trang công khai
│
├── mysql/
│   ├── config.php                # host/port/name/user/pass
│   ├── connect_mysql.php         # Trả về mysqli
│   ├── schema.php                # CREATE TABLE + seed dữ liệu nền
│   ├── seed_accounts.php         # Tạo tài khoản theo vai trò
│   └── setup.php                 # Chạy toàn bộ khởi tạo CSDL
│
├── docs/                         # Tài liệu chi tiết
│   ├── workflow-tong-hop.md      # Luồng GET/POST đầy đủ
│   └── tao-giao-vien-workflow.md # Giải thích cú pháp PHP + luồng tạo GV
│
├── AGENTS.md                     # Quy ước cho AI agent
└── README.md
```

---

## 6. Kiến trúc và luồng request

### 6.1. Phân lớp

```text
Browser (HTML/JS)
   ↓ HTTP
Apache + .htaccess
   ↓
public/index.php  (bootstrap, session, DB, khai báo route)
   ↓
src/Core/Router.php
   ↓
Controller (HTTP) → Service (nghiệp vụ) → Repository (SQL) → MySQL
   ↓
GET:  Page::app() → View + data.php + Layout → HTML
POST: Response::json() → JSON → JS xử lý
```

### 6.2. Luật phụ thuộc

```text
Controller  →  Service  →  Repository  →  MySQL
     ↓
  Request/Response
```

- **Controller** không viết SQL, không validate sâu.
- **Service** không đọc `$_POST`, không `echo`, không biết HTTP.
- **Repository** không biết HTTP, không validate nghiệp vụ.
- **View** không viết SQL trực tiếp; dùng `src/Views/data/data.php`.

### 6.3. GET và POST

| | GET trang | POST API |
| --- | --- | --- |
| Kích hoạt | Click link / reload | Submit form qua `fetch` |
| Route | `/admin/instructors` | `/api/instructors` |
| Handler | Closure → `Page::app` | `Controller::method` |
| Middleware | Không (hiện tại) | `requireAuth` + `requireAdmin` |
| Kết quả | HTML | JSON |

Chi tiết từng bước: xem `docs/workflow-tong-hop.md`.

---

## 7. Quy ước code

### 7.1. PHP

- `declare(strict_types=1);` ở file mới nếu phù hợp.
- Mọi class đặt trong namespace `App\...`, khớp đường dẫn file (PSR-4).
- Khai báo kiểu tham số + kiểu trả về:

```php
public function create(array $input): array
```

- Dùng `===` / `!==` thay vì `==` / `!=`.
- Không dùng `echo` trong Controller/Service; chỉ View được in HTML.
- Không `var_dump`/`print_r` trong code commit lên main.

### 7.2. Đặt tên

| Loại | Quy ước | Ví dụ |
| --- | --- | --- |
| Class | PascalCase | `InstructorService` |
| Method | camelCase | `getAll`, `create` |
| Biến | camelCase | `$instructorRepo` |
| Bảng/cột DB | snake_case | `tai_khoan`, `ten_dang_nhap` |
| View | kebab-case | `class-registration.php` |
| Route | kebab-case | `/admin/instructors` |

### 7.3. Response API

Luôn dùng `Response::json()`:

```php
// Thành công
Response::json([
    'success' => true,
    'data'    => $data,
    'message' => '...',
], 200);

// Lỗi validate
Response::json([
    'success' => false,
    'message' => 'Dữ liệu không hợp lệ.',
    'errors'  => $errors,
], 422);

// Lỗi hệ thống (không lộ chi tiết)
Response::json([
    'success' => false,
    'message' => 'Lỗi hệ thống, vui lòng thử lại sau.',
], 500);
```

### 7.4. Bảo mật

- **SQL**: luôn prepared statement (`prepare` + `bind_param`). Không nối chuỗi.
- **Mật khẩu**: `password_hash()` khi tạo/đổi, `password_verify()` khi đăng nhập.
- **XSS**: mọi dữ liệu in ra HTML phải qua `e()`.
- **Quyền**: kiểm tra ở route bằng `$requireAuth` / `$requireAdmin`; không tin dữ liệu client.
- **CSRF**: dự án đã bỏ theo yêu cầu môn học (không dùng lại).

---

## 8. Thêm một trang mới (GET)

Ví dụ thêm trang `/admin/rooms`.

### Bước 1 — Tạo view
`src/Views/admin/rooms.php`

```php
<?php
require_once __DIR__ . '/../data/data.php';

$rooms = data_rooms();
?>
<div class="page-head">
    <h1>Quản lý phòng tập</h1>
</div>
<?php foreach ($rooms as $room): ?>
    <div><?= e($room['name']) ?></div>
<?php endforeach; ?>
```

### Bước 2 — Thêm dữ liệu cho view
`src/Views/data/data.php`

```php
function data_rooms(): array
{
    $sql = "SELECT id, ten_phong AS name FROM phong_tap ORDER BY id";
    return view_db()->query($sql)->fetch_all(MYSQLI_ASSOC);
}
```

Nếu dữ liệu cần tái sử dụng cho API, tạo Repository thay vì query trong view.

### Bước 3 — Đăng ký route
`public/index.php`

```php
$router->add('GET', '/admin/rooms', function (): void {
    Page::app('admin', 'admin/rooms', [], [
        'title' => 'Quản lý phòng tập',
        'active' => 'rooms',
    ]);
});
```

### Bước 4 — Thêm menu
`src/Views/partials/menu.php` → thêm item vào `app_menu('admin')`:

```php
['label' => 'Phòng tập', 'icon' => 'door-open', 'href' => '/admin/rooms', 'key' => 'rooms'],
```

### Bước 5 — Kiểm tra
- Mở URL, xem có lỗi PHP không.
- Kiểm tra menu active, breadcrumb, quyền (nếu cần).

---

## 9. Thêm một API mới (POST/CRUD)

Ví dụ thêm API tạo phòng tập `POST /api/rooms`.

### Bước 1 — Model (nếu cần)
`src/Models/PhongTap.php` — chỉ giữ dữ liệu + getter.

### Bước 2 — Repository
`src/Repositories/RoomRepository.php`

```php
<?php
namespace App\Repositories;

class RoomRepository extends BaseRepository
{
    protected string $table = 'phong_tap';
}
```

`insert()`, `find()`, `update()`, `delete()` đã có ở `BaseRepository`.

### Bước 3 — Service
`src/Services/RoomService.php`

```php
<?php
namespace App\Services;

use App\Repositories\RoomRepository;

class RoomService
{
    public function __construct(private RoomRepository $roomRepository)
    {
    }

    public function create(array $input): array
    {
        $name = trim((string) ($input['ten_phong'] ?? ''));
        if ($name === '') {
            return ['ok' => false, 'errors' => ['ten_phong' => 'Tên phòng không được rỗng.']];
        }

        $id = $this->roomRepository->insert(['ten_phong' => $name]);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }
}
```

### Bước 4 — Controller
`src/Controllers/Api/RoomApiController.php`

```php
<?php
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\RoomService;

class RoomApiController
{
    public function __construct(private RoomService $roomService)
    {
    }

    public function store(): void
    {
        $result = $this->roomService->create(Request::json());

        if (!$result['ok']) {
            Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $result['errors'],
            ], 422);
        }

        Response::json([
            'success' => true,
            'message' => 'Đã tạo phòng tập.',
            'data'    => ['id' => $result['id']],
        ], 201);
    }
}
```

### Bước 5 — Đăng ký route + khởi tạo trong `index.php`

```php
if ($db !== null) {
    // ...
    $roomRepo = new RoomRepository($db);
    $roomService = new RoomService($roomRepo);
    $roomController = new RoomApiController($roomService);

    $router->add('POST', '/api/rooms', [$roomController, 'store'], [$requireAuth, $requireAdmin]);
}
```

### Bước 6 — Gọi từ JS

```js
await window.api.post('/api/rooms', { ten_phong: 'P.101' });
```

### Checklist bắt buộc
- [ ] Bảng có trong `mysql/schema.php`
- [ ] Repository dùng prepared statement
- [ ] Service validate dữ liệu
- [ ] Controller trả `Response::json` đúng mã
- [ ] Route nằm trong khối `if ($db !== null)`
- [ ] Có middleware `$requireAuth` / `$requireAdmin` nếu cần
- [ ] Test bằng Network tab + kiểm tra DB

---

## 10. Route hiện có

### Trang (GET)

| URL | View |
| --- | --- |
| `/` | `public/home` |
| `/login`, `/register` | `auth/*` |
| `/admin` | `admin/dashboard` |
| `/admin/members` | `admin/members` |
| `/admin/instructors` | `admin/instructors` |
| `/admin/packages` | `admin/packages` |
| `/admin/classes` | `admin/classes` |
| `/admin/registrations` | `admin/registrations` |
| `/admin/availability` | `admin/availability` |
| `/admin/accounts` | `admin/accounts` |
| `/admin/reports` | `admin/reports` |
| `/admin/search` | `admin/search` |
| `/customer` | `customer/dashboard` |
| `/customer/class-registration` | `customer/class-registration` |
| `/customer/schedule` | `customer/schedule` |
| `/customer/history` | `customer/history` |
| `/customer/account` | `customer/account` |
| `/instructor` | `instructor/dashboard` |
| `/instructor/schedule` | `instructor/schedule` |
| `/instructor/availability` | `instructor/availability` |
| `/instructor/classes` | `instructor/classes` |
| `/instructor/search` | `instructor/search` |
| `/instructor/account` | `instructor/account` |

### API

| Method | Endpoint | Quyền |
| --- | --- | --- |
| POST | `/api/auth/login` | Công khai |
| GET | `/api/auth/me` | Đăng nhập |
| POST | `/api/auth/logout` | Đăng nhập |
| GET | `/api/instructors` | Đăng nhập |
| GET | `/api/instructors/{id}` | Đăng nhập |
| POST | `/api/instructors` | Admin |
| PUT | `/api/instructors/{id}` | Admin |
| DELETE | `/api/instructors/{id}` | Admin |

API base URL trong JS:

```text
/Project_ck/public/index.php
```

nên request thực tế là:

```text
/Project_ck/public/index.php/api/instructors
```

---

## 11. CSDL

Các bảng do `mysql/schema.php` tạo:

| Bảng | Vai trò |
| --- | --- |
| `tai_khoan` | Tài khoản đăng nhập (`admin`, `instructor`, `customer`) |
| `giao_vien` | Hồ sơ giáo viên, liên kết `tai_khoan_id` |
| `hoi_vien` | Hồ sơ hội viên, chỉ số cơ thể |
| `goi_tap` | Gói tập |
| `lop_hoc` | Lớp học |
| `dang_ky_goi` | Đăng ký gói + thanh toán |
| `dang_ky_lop` | Đăng ký lớp |
| `lich_ranh` | Lịch rảnh / yêu cầu dạy của giáo viên |
| `diem_danh` | Điểm danh |
| `doanh_thu` | Doanh thu theo tháng (biểu đồ) |

Quan hệ chính:

```text
giao_vien.tai_khoan_id → tai_khoan.id
hoi_vien.tai_khoan_id  → tai_khoan.id
dang_ky_goi.hoi_vien_id → hoi_vien.id
dang_ky_goi.goi_tap_id  → goi_tap.id
dang_ky_lop.hoi_vien_id → hoi_vien.id
dang_ky_lop.lop_hoc_id  → lop_hoc.id
lop_hoc.giao_vien_id    → giao_vien.id
lich_ranh.giao_vien_id  → giao_vien.id
diem_danh.lop_hoc_id    → lop_hoc.id
diem_danh.hoi_vien_id   → hoi_vien.id
```

---

## 12. Debug và lỗi thường gặp

### 12.1. Trình tự debug

1. **Network tab**: URL, method, payload, status, response.
2. **Apache log**: `C:\xampp\apache\logs\error.log`.
3. **PHP log**: dòng `error_log(...)` do code ghi ra.
4. **MySQL**: query trực tiếp bảng liên quan.

### 12.2. Lỗi thường gặp

| Hiện tượng | Nguyên nhân | Cách xử lý |
| --- | --- | --- |
| 404 HTML của Apache | Sai URL / `.htaccess` không chạy / `mod_rewrite` tắt | Dùng đúng `.../Project_ck/public/...`, bật rewrite |
| 404 JSON | Route chưa đăng ký / DB lỗi (`$db = null`) | Kiểm tra MySQL, `public/index.php` |
| 401 | Chưa đăng nhập | Đăng nhập `admin/123456` |
| 403 | Không phải admin | Kiểm tra `vai_tro` trong `tai_khoan` |
| 415 | Thiếu `Content-Type: application/json` | Sửa header trong `api.js`/Postman |
| 422 | Dữ liệu sai/trùng | Đọc `response.errors` |
| 500 | Exception/DB | Xem Apache error log |
| Trang trắng | Lỗi PHP bị tắt hiển thị | Bật `display_errors`, xem log |
| CSS/JS không tải | Sai `base_url()` / file không tồn tại | Kiểm tra đường dẫn `/assets/...` |
| Sửa JS không thấy đổi | Cache trình duyệt | Ctrl+F5; tăng `?v=` trong layout |
| `bind_param` lỗi reference | Truyền literal thay vì biến | Gán vào biến trước |
| Số liệu lệch | MySQL trả chuỗi | Ép `(int)` hoặc dùng `view_cast_ints()` |

### 12.3. Lưu ý về `.htaccess` gốc `htdocs`

File `C:\xampp\htdocs\.htaccess` **không thuộc repo**. Nó chỉ giúp gọi tắt `/api/...` khi chạy XAMPP. Khi clone project sang máy khác, dùng URL đầy đủ:

```text
http://localhost/Project_ck/public/...
```

---

## 13. Tài liệu chi tiết

| File | Nội dung |
| --- | --- |
| `docs/workflow-tong-hop.md` | Toàn bộ luồng GET mở trang và POST tạo giáo viên, kèm trace giá trị thực, bảng lỗi, debug, cheat sheet |
| `docs/tao-giao-vien-workflow.md` | Giải thích sâu cú pháp PHP qua đúng đoạn code của dự án |
| `AGENTS.md` | Quy ước + checklist dành cho AI agent/đồng đội khi sửa code |

---

## 14. Quy trình làm việc nhóm

### Trước khi code
- `git pull origin main`
- Đọc `README.md` + `AGENTS.md`
- Xem route hiện có để tránh trùng

### Khi code
- Tạo nhánh:

```bash
git checkout -b feature/ten-tinh-nang
```

- Chỉ sửa trong phạm vi tính năng.
- Không commit file cấu hình cá nhân, log, file tạm.
- Chạy kiểm tra cú pháp:

```bash
C:\xampp\php\php.exe -l đường\dẫn\file.php
```

### Trước khi commit
- [ ] Trang/API chạy được
- [ ] Không có `var_dump`/`print_r`
- [ ] Không có mật khẩu/token trong code
- [ ] Đã test cả trường hợp lỗi (401/403/422)
- [ ] Đã cập nhật README nếu thêm route/bảng mới

### Commit & push

```bash
git add .
git commit -m "feat: thêm quản lý phòng tập"
git push origin main
```

Nếu làm nhánh riêng:

```bash
git push -u origin feature/ten-tinh-nang
```

Sau đó tạo Pull Request trên GitHub để đồng đội review.

---

## 15. Dành cho AI agent

Trước khi sửa code, đọc `AGENTS.md`. Các điểm bắt buộc:

- Giữ đúng phân lớp Controller → Service → Repository.
- Không tạo mock data; dữ liệu đọc từ MySQL.
- Không thêm CSRF (đã bỏ theo yêu cầu môn học).
- Route API phải nằm trong khối `if ($db !== null)`.
- API trả JSON theo `Response::json`.
- SQL dùng prepared statement.
- View escape bằng `e()`.
- Cập nhật README/docs khi thêm route hoặc bảng.
