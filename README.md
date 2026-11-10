# FitZone — Hệ thống quản lý phòng tập & lớp học

Ứng dụng web viết bằng **PHP thuần + MySQL**, giao diện server-side kết hợp AJAX/JSON API. Hệ thống phân quyền 3 vai trò: **Quản trị viên**, **Giáo viên**, **Hội viên**.

## Công nghệ

- PHP 8+ (`mysqli`, session, `password_hash`)
- MySQL / MariaDB
- HTML, CSS, JavaScript thuần
- Bootstrap 5 + Bootstrap Icons (đặt trong `common/assets/vendor`)

## Chức năng

### Công khai
- Trang chủ giới thiệu phòng tập, gói tập và lớp học nổi bật.
- Đăng ký hội viên mới (gửi bằng AJAX tới API).
- Đăng nhập một cổng, tự chuyển về đúng khu vực theo vai trò.

### Hội viên (`customer`)
- Tổng quan hồ sơ, chỉ số cơ thể, lớp đã đăng ký.
- Đăng ký lớp học, xem lịch tập, lịch sử gói tập/lớp học.
- Cập nhật hồ sơ, đổi mật khẩu.

### Giáo viên (`instructor`)
- Tổng quan lịch dạy.
- Đăng ký lịch rảnh, gửi yêu cầu chờ duyệt.
- Xem lớp đang dạy, danh sách học viên.
- Tra cứu học viên, quản lý tài khoản.

### Quản trị viên (`admin`)
- Tổng quan, thống kê doanh thu.
- CRUD hội viên, giáo viên, gói tập, lớp học, đăng ký.
- Duyệt/từ chối/hủy lịch dạy.
- Quản lý tài khoản, tra cứu toàn hệ thống.

## Cấu trúc dự án

```
fitness/
├── index.php                 # Trang chủ
├── login.php                 # Đăng nhập
├── register.php              # Đăng ký hội viên
├── admin/                    # 10 trang quản trị
├── instructor/               # 6 trang giáo viên
├── customer/                 # 5 trang hội viên
└── common/
    ├── bootstrap.php         # session, nạp hàm, common_db/common_user/require_role
    ├── functions.php         # url(), redirect(), e(), base_url()
    ├── api.php               # JSON API (router đơn giản)
    ├── components/ui.php     # hàm sinh HTML: icon, avatar, badge, money,...
    ├── data/
    │   ├── data.php          # loader nạp toàn bộ data layer
    │   ├── common.php        # view_db(), helper
    │   ├── shared.php        # dữ liệu dùng chung (lớp, gói, giáo viên,...)
    │   ├── admin.php         # dữ liệu admin
    │   ├── customer.php      # dữ liệu hội viên
    │   ├── instructor.php    # dữ liệu giáo viên
    │   └── user.php          # app_user(), app_notifications()
    ├── menus/                # dữ liệu menu theo vai trò
    ├── partials/             # head, sidebar, topbar, flash, foot
    ├── mysql/                # config, connect, schema, seed_accounts, setup
    └── assets/               # css, js, vendor
```

## Cài đặt & chạy

1. Cài **XAMPP** (PHP 8+, MySQL/MariaDB).
2. Clone project vào `C:\xampp\htdocs\fitness`.
3. Bật **Apache** và **MySQL** trong XAMPP.
4. Kiểm tra cấu hình ở `common/mysql/config.php` (mặc định `127.0.0.1`, user `root`, mật khẩu rỗng).
5. Mở trình duyệt: `http://localhost/fitness/`.

Cơ sở dữ liệu `phongtap` sẽ **tự động được tạo** (bảng + tài khoản mẫu) ở lần query đầu tiên nhờ `common_db()`. Có thể khởi tạo thủ công:

```bash
C:\xampp\php\php.exe common\mysql\setup.php
```

## Tài khoản mẫu

Mật khẩu chung: `123456`

| Vai trò | Tên đăng nhập |
| --- | --- |
| Quản trị viên | `admin` |
| Giáo viên | `thuha`, `ducanh`, `ngocmai`, `quocbao`, `hongnhung` |
| Hội viên | `hoangnam` |

## Luồng hoạt động

Mỗi trang chạy theo mẫu:

```php
require __DIR__ . '/../common/bootstrap.php';
require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';
$appRole = 'customer';
$activeMenu = 'dashboard';
$pageTitle = 'Trang chủ hội viên';
$pageScripts = [];
$partials = __DIR__ . '/../common/partials';

require $partials . '/head.php';
// ... HTML + require sidebar/topbar/flash ...
require $partials . '/foot.php';
```

- **Dữ liệu** lấy qua các hàm `data_*()` trong `common/data/`, chạy SQL qua `view_db()` → `common_db()`.
- **Giao diện** ghép từ các partial dùng chung (`head`, `sidebar`, `topbar`, `flash`, `foot`).
- **Menu** khai báo một nơi duy nhất trong `common/menus/{role}.php`.
- **AJAX**: `common/assets/js/api.js` gọi API, `common/assets/js/ui.js` tự xử lý form có `data-api-*`.

## API chính

Base: `/fitness/common/api.php/api/...`

| Method | Endpoint | Quyền |
| --- | --- | --- |
| POST | `/auth/login` | Công khai |
| POST | `/auth/logout` | Đăng nhập |
| POST | `/register` | Công khai |
| PUT | `/profile` | Đăng nhập |
| POST | `/profile/password` | Đăng nhập |
| GET/POST/PUT/DELETE | `/members`, `/instructors`, `/packages`, `/classes` | Đăng nhập (ghi: admin) |
| POST | `/registrations/classes` | Hội viên |
| POST | `/registrations/classes/{id}/cancel` | Đăng nhập |
| POST | `/registrations/packages/{id}/confirm` \| `/cancel` | Admin |
| GET/POST | `/availability` | Đăng nhập / Giáo viên |
| POST | `/availability/{id}/approve` \| `/reject` \| `/cancel` | Admin / Giáo viên |

## Quy ước code

- Không viết SQL trong view; đặt ở `common/data/`.
- Dữ liệu menu chỉ sửa ở `common/menus/`.
- UI helper dùng chung ở `common/components/ui.php`.
- API dùng `bootstrap.php` và trả JSON qua `api_json()`.
- Mật khẩu lưu bằng `password_hash()`.

## Ghi chú

- `common/mysql/config.php` mặc định cấu hình cho XAMPP (`root`, không mật khẩu). Khi deploy cần đổi thông tin kết nối.
- Khi chạy trên máy khác, dùng URL đầy đủ dạng `/fitness/...` (không có `.htaccess` rút gọn).
- Dự án phục vụ môn học nhóm; ưu tiên dễ đọc, ít file hơn là kiến trúc MVC đầy đủ.
