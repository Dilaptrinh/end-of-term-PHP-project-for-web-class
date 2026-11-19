<?php
declare(strict_types=1);

/**
 * Nghiệp vụ gói tập dùng chung cho hội viên và quản trị.
 */

if (!function_exists('package_level_rank')) {
    function package_level_rank(?string $level): int
    {
        return ['basic' => 1, 'standard' => 2, 'premium' => 3][(string) $level] ?? 1;
    }
}

if (!function_exists('package_active_registration')) {
    function package_active_registration(int $memberId, bool $forUpdate = false): ?array
    {
        $sql = 'SELECT d.id, d.goi_tap_id, d.ngay_bat_dau, d.ngay_ket_thuc, d.gia_tai_thoi_diem,
                       d.gia_niem_yet, d.loai_giao_dich, d.dang_ky_goc_id,
                       gt.ten_goi AS name, gt.cap_do, gt.thoi_han_ngay, gt.gia
                  FROM dang_ky_goi d
                  JOIN goi_tap gt ON gt.id = d.goi_tap_id AND gt.deleted_at IS NULL
                 WHERE d.hoi_vien_id = ? AND d.trang_thai = "da_thanh_toan"
                   AND d.ngay_bat_dau <= CURDATE() AND d.ngay_ket_thuc >= CURDATE()
                 ORDER BY CASE gt.cap_do WHEN "premium" THEN 3 WHEN "standard" THEN 2 ELSE 1 END DESC,
                          d.ngay_ket_thuc DESC
                 LIMIT 1' . ($forUpdate ? ' FOR UPDATE' : '');

        $stmt = common_db()->prepare($sql);
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}

if (!function_exists('package_upgrade_quote')) {
    /**
     * Mô hình B: đổi ngang hạn.
     * Giữ nguyên ngày kết thúc của gói đang dùng, chỉ trả chênh lệch giá trị
     * của số ngày còn lại giữa gói mới và gói cũ.
     */
    function package_upgrade_quote(array $active, array $newPackage): array
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $active['ngay_bat_dau']);
        $end = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $active['ngay_ket_thuc']);
        $today = new DateTimeImmutable('today');

        if (!$start || !$end) {
            return [
                'remaining_days' => 0,
                'old_days' => 0,
                'credit' => 0,
                'new_segment_value' => 0,
                'fee' => 0,
                'start' => $today->format('Y-m-d'),
                'end' => $today->format('Y-m-d'),
            ];
        }

        $oldDays = max(1, (int) $start->diff($end)->days + 1);
        $remaining = $end >= $today ? (int) $today->diff($end)->days + 1 : 0;

        $oldBaseValue = (int) ($active['gia_niem_yet'] ?? 0);
        if ($oldBaseValue <= 0) {
            $oldBaseValue = (int) ($active['gia_tai_thoi_diem'] ?? 0);
        }
        $credit = $remaining > 0 ? intdiv($oldBaseValue * $remaining, $oldDays) : 0;

        $newDays = max(1, (int) ($newPackage['thoi_han_ngay'] ?? $newPackage['days'] ?? 1));
        $newPrice = (int) ($newPackage['gia'] ?? $newPackage['price'] ?? 0);
        $newSegmentValue = $remaining > 0 ? intdiv($newPrice * $remaining, $newDays) : 0;

        return [
            'remaining_days' => $remaining,
            'old_days' => $oldDays,
            'credit' => $credit,
            'new_segment_value' => $newSegmentValue,
            'fee' => max(0, $newSegmentValue - $credit),
            'start' => $today->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ];
    }
}

if (!function_exists('package_pending_upgrade')) {
    function package_pending_upgrade(int $memberId): ?array
    {
        $stmt = common_db()->prepare(
            'SELECT id, goi_tap_id FROM dang_ky_goi
              WHERE hoi_vien_id = ? AND loai_giao_dich = "nang_cap" AND trang_thai = "chua_thanh_toan"
              LIMIT 1'
        );
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}
