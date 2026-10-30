<?php
declare(strict_types=1);

/**
 * UI helpers — dùng chung cho mọi view.
 * Chỉ sinh HTML, không xử lý nghiệp vụ.
 */

if (!function_exists('icon')) {
    function icon(string $name, string $class = ''): string
    {
        $name = preg_replace('/[^a-z0-9-]/i', '', $name) ?: 'circle';
        return '<i class="bi bi-' . e($name) . ($class !== '' ? ' ' . e($class) : '') . '" aria-hidden="true"></i>';
    }
}

if (!function_exists('role_name')) {
    function role_name(string $role): string
    {
        return match ($role) {
            'admin' => 'Quản trị viên',
            'instructor' => 'Giáo viên',
            'customer' => 'Hội viên',
            default => 'Khách',
        };
    }
}

if (!function_exists('role_icon')) {
    function role_icon(string $role): string
    {
        return match ($role) {
            'admin' => 'shield-lock',
            'instructor' => 'person-badge',
            default => 'person',
        };
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
        if ($parts === []) {
            return '?';
        }
        $last = $parts[count($parts) - 1];
        $first = $parts[0];
        $initials = mb_substr($first, 0, 1, 'UTF-8');
        if (count($parts) > 1) {
            $initials .= mb_substr($last, 0, 1, 'UTF-8');
        }
        return mb_strtoupper($initials, 'UTF-8');
    }
}

if (!function_exists('avatar')) {
    function avatar(string $name, string $size = '', string $extra = ''): string
    {
        $palette = ['alt-1', 'alt-2', 'alt-3', 'alt-4', 'alt-5'];
        $alt = $palette[abs(crc32($name)) % count($palette)];
        $classes = trim('avatar ' . $alt . ($size !== '' ? ' ' . $size : '') . ($extra !== '' ? ' ' . $extra : ''));
        return '<span class="' . e($classes) . '" title="' . e($name) . '">' . e(initials($name)) . '</span>';
    }
}

if (!function_exists('badge')) {
    function badge(string $label, string $variant = 'primary', bool $dot = true): string
    {
        $dotClass = $dot ? '' : ' no-dot';
        return '<span class="badge-status ' . e($variant) . $dotClass . '">' . e($label) . '</span>';
    }
}

if (!function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'hoat_dong' => ['Đang hoạt động', 'success'],
            'ngung_hoat_dong' => ['Ngừng hoạt động', 'muted'],
            'dang_mo' => ['Đang mở', 'success'],
            'da_dong' => ['Đã đóng', 'muted'],
            'dang_ban' => ['Đang bán', 'success'],
            'ngung_ban' => ['Ngừng bán', 'muted'],
            'cho_duyet' => ['Chờ duyệt', 'warning'],
            'da_duyet' => ['Đã duyệt', 'success'],
            'tu_choi' => ['Từ chối', 'danger'],
            'da_huy' => ['Đã hủy', 'danger'],
            'da_thanh_toan' => ['Đã thanh toán', 'success'],
            'chua_thanh_toan' => ['Chưa thanh toán', 'warning'],
            'dang_ky' => ['Đã đăng ký', 'primary'],
            'co_mat' => ['Có mặt', 'success'],
            'vang' => ['Vắng', 'danger'],
            'phep' => ['Có phép', 'warning'],
            'cho_xac_nhan' => ['Chờ xác nhận', 'warning'],
            'bi_khoa' => ['Đã khóa', 'danger'],
            'hoan_thanh' => ['Hoàn thành', 'success'],
        ];
        [$label, $variant] = $map[$status] ?? [$status, 'muted'];
        return badge($label, $variant);
    }
}

if (!function_exists('money')) {
    function money(int|float $amount): string
    {
        return number_format((float) $amount, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('weekday')) {
    function weekday(int $day): string
    {
        return ['', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'][$day] ?? '';
    }
}

if (!function_exists('weekday_short')) {
    function weekday_short(int $day): string
    {
        return ['', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'][$day] ?? '';
    }
}

if (!function_exists('empty_state')) {
    function empty_state(string $title, string $desc, string $icon = 'inbox', string $action = ''): string
    {
        $html = '<div class="empty-state">';
        $html .= '<div class="empty-icon">' . icon($icon) . '</div>';
        $html .= '<h4>' . e($title) . '</h4>';
        $html .= '<p>' . e($desc) . '</p>';
        $html .= $action;
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('pagination')) {
    function pagination(int $total, int $page = 1, int $perPage = 10, string $label = 'bản ghi'): string
    {
        $pages = max(1, (int) ceil($total / max(1, $perPage)));
        $page = max(1, min($page, $pages));
        $from = $total === 0 ? 0 : ($page - 1) * $perPage + 1;
        $to = min($total, $page * $perPage);

        $html = '<nav class="app-pagination" aria-label="Phân trang">';
        $html .= '<span class="page-info">Hiển thị ' . $from . '–' . $to . ' / ' . $total . ' ' . e($label) . '</span>';
        $html .= '<a href="#" class="page-btn' . ($page === 1 ? ' disabled' : '') . '" aria-label="Trang trước">' . icon('chevron-left') . '</a>';

        $start = max(1, $page - 2);
        $end = min($pages, $start + 4);
        $start = max(1, $end - 4);
        for ($i = $start; $i <= $end; $i++) {
            $html .= '<a href="#" class="page-btn' . ($i === $page ? ' active' : '') . '">' . $i . '</a>';
        }

        $html .= '<a href="#" class="page-btn' . ($page === $pages ? ' disabled' : '') . '" aria-label="Trang sau">' . icon('chevron-right') . '</a>';
        $html .= '</nav>';
        return $html;
    }
}

if (!function_exists('progress_bar')) {
    function progress_bar(int $percent, string $variant = '', bool $thick = false): string
    {
        $percent = max(0, min(100, $percent));
        $classes = trim('app-progress' . ($variant !== '' ? ' ' . $variant : '') . ($thick ? ' thick' : ''));
        return '<div class="' . e($classes) . '" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100">'
            . '<span style="width:' . $percent . '%"></span></div>';
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $limit = 80): string
    {
        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit, 'UTF-8') . '…';
    }
}
