# FitZone — Hệ thống quản lý phòng tập & lớp học

Ứng dụng web viết bằng **PHP thuần + MySQL**, giao diện server-side kết hợp JavaScript cho các thao tác cần thiết. Hệ thống phân quyền 3 vai trò: **Quản trị viên**, **Giáo viên**, **Hội viên**.

## Công nghệ

- PHP 8+ (`mysqli`, session, `password_hash`)
- MySQL / MariaDB
- HTML, CSS, JavaScript thuần
- Bootstrap 5 + Bootstrap Icons (đặt trong `common/assets/vendor`)

## Chức năng

### Công khai
- Trang chủ giới thiệu phòng tập, gói tập và lớp học nổi bật.
- Đăng ký hội viên mới bằng form POST server-side, kiểm tra và thông báo lỗi tại chỗ.
- Đăng nhập một cổng bằng form POST, tự chuyển về đúng khu vực theo vai trò.

### Hội viên (`customer`)
- Đăng ký gói, đăng ký/hủy lớp, xem lịch tập và lịch sử gói/lớp.
- Cập nhật hồ sơ, đổi mật khẩu.

### Giáo viên (`instructor`)
- Xem lịch dạy và lớp đang dạy, danh sách học viên theo từng lớp.
- Cập nhật hồ sơ, chuyên môn, đổi mật khẩu.

### Quản trị viên (`admin`)
- Tổng quan, thống kê doanh thu và thanh toán chờ xử lý.
- Quản lý tài khoản, gói tập, lớp học và đăng ký gói.
- Xem chi tiết lớp, danh sách học viên, xác nhận thanh toán.
- Cập nhật tài khoản quản trị và đổi mật khẩu.

## Cấu trúc dự án

```
fitness/
├── index.php                 # Trang chủ
├── login.php                 # Đăng nhập
├── register.php              # Đăng ký hội viên
├── admin/                    # 6 trang quản trị
├── instructor/               # 3 trang giáo viên
├── customer/                 # 5 trang hội viên
└── common/
    ├── bootstrap.php         # session, nạp hàm, common_db/common_user/require_role
    ├── functions.php         # url(), redirect(), e(), base_url()
    ├── components/ui.php     # hàm sinh HTML: icon, avatar, badge, money,...
    ├── data/
    │   ├── data.php          # loader nạp toàn bộ data layer
    │   ├── common.php        # view_db(), helper
    │   ├── shared.php        # dữ liệu dùng chung (lớp, gói, giáo viên,...)
    │   ├── admin.php         # dữ liệu admin
    │   ├── customer.php      # dữ liệu hội viên
    │   ├── instructor.php    # dữ liệu giáo viên
    │   └── user.php          # app_user()
    ├── menus/                # dữ liệu menu theo vai trò
    ├── partials/             # head, sidebar, topbar, flash, foot
    ├── mysql/                # config, connect và file SQL
    └── assets/               # css, js, vendor
```

## Cài đặt & chạy

1. Cài **XAMPP** (PHP 8+, MySQL/MariaDB).
2. Clone project vào `C:\xampp\htdocs\fitness`.
3. Bật **Apache** và **MySQL** trong XAMPP.
4. Kiểm tra cấu hình ở `common/mysql/config.php` (mặc định `127.0.0.1`, user `root`, mật khẩu rỗng).
5. Mở trình duyệt: `http://localhost/fitness/`.

Import `common/mysql/fitzone_phongtap.sql` vào MySQL trước khi mở ứng dụng. Ứng dụng chỉ kết nối tới database đã có, không tự tạo database, bảng hoặc dữ liệu mẫu.

## Tài khoản mẫu

Mật khẩu chung: `123456`

| Vai trò | Tên đăng nhập |
| --- | --- |
| Quản trị viên | `admin` |
| Giáo viên | `thuha`, `ducanh`, `ngocmai`, `quocbao`, `hongnhung`, `namm` |
| Hội viên | `hoangnam` |

## Luồng hoạt động

Mỗi trang chạy theo mẫu:

```php
require __DIR__ . '/../common/bootstrap.php';
require_role('customer');

$menu = require __DIR__ . '/../common/menus/customer.php';
$appRole = 'customer';
$activeMenu = 'schedule';
$pageTitle = 'Lịch tập';
$pageScripts = [];
$partials = __DIR__ . '/../common/partials';

require $partials . '/head.php';
// ... HTML + require sidebar/topbar/flash ...
require $partials . '/foot.php';
```

- **Dữ liệu** lấy qua các hàm `data_*()` trong `common/data/`, chạy SQL qua `view_db()` → `common_db()`.
- **Giao diện** ghép từ các partial dùng chung (`head`, `sidebar`, `topbar`, `flash`, `foot`).
- **Menu** khai báo một nơi duy nhất trong `common/menus/{role}.php`.
- Các thao tác quản trị dùng `fetch` tới chính trang hiện tại kèm `?action=...`; riêng form tạo tài khoản và khóa/mở khóa tài khoản dùng POST server-side + flash message.

## Endpoint chính

Các trang vừa render HTML vừa xử lý AJAX qua tham số `?action=...`:

| Trang | Action tiêu biểu | Quyền |
| --- | --- | --- |
| `admin/accounts.php` | `detail`, `create`, `update`, `status` | Admin |
| `admin/packages.php` | `save`, `delete`; lọc `package_id`, `status` | Admin |
| `admin/classes.php` | `save`, `delete`, `detail`; lọc `class_id` | Admin |
| `admin/registrations.php` | `confirm-package`, `cancel-package`; lọc `package_id`, `payment` | Admin |
| `instructor/classes.php` | `members`; lọc `status=current\|completed` | Giáo viên |
| `customer/package-registration.php` | Đăng ký gói bằng POST `goi_tap_id` | Hội viên |
| `customer/class-registration.php` | `register`; lọc `q`, `weekday`, `package` | Hội viên |
| `customer/history.php` | `cancel`, `cancel-package` | Hội viên |

## Quy ước code

- Không viết SQL trong view; đặt ở `common/data/`.
- Dữ liệu menu chỉ sửa ở `common/menus/`.
- UI helper dùng chung ở `common/components/ui.php`.
- Endpoint AJAX dùng `bootstrap.php` và trả JSON qua `json_response()`.
- Mật khẩu lưu bằng `password_hash()`.

## Ghi chú

- `common/mysql/config.php` mặc định cấu hình cho XAMPP (`root`, không mật khẩu). Khi deploy cần đổi thông tin kết nối.
- Khi chạy trên máy khác, dùng URL đầy đủ dạng `/fitness/...` (không có `.htaccess` rút gọn).
- Dự án phục vụ môn học nhóm; ưu tiên dễ đọc, ít file hơn là kiến trúc MVC đầy đủ.
