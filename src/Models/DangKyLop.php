<?php
namespace App\Models;

class DangKyLop
{
    public function __construct(
        private int $hoiVienId,
        private int $lopHocId,
        private ?string $ngayDangKy = null,
        private string $trangThai = 'dang_ky',
        private ?int $createdBy = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getHoiVienId(): int { return $this->hoiVienId; }
    public function getLopHocId(): int { return $this->lopHocId; }
    public function getNgayDangKy(): ?string { return $this->ngayDangKy; }
    public function getTrangThai(): string { return $this->trangThai; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
