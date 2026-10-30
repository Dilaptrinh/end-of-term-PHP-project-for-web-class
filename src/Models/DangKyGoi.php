<?php
namespace App\Models;

class DangKyGoi
{
    public function __construct(
        private ?int $id,
        private int $hoiVienId,
        private int $goiTapId,
        private string $ngayBatDau,
        private string $ngayKetThuc,
        private int|float $giaTaiThoiDiem,
        private string $trangThai = 'dang_ky',
        private ?int $createdBy = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getHoiVienId(): int { return $this->hoiVienId; }
    public function getGoiTapId(): int { return $this->goiTapId; }
    public function getNgayBatDau(): string { return $this->ngayBatDau; }
    public function getNgayKetThuc(): string { return $this->ngayKetThuc; }
    public function getGiaTaiThoiDiem(): int|float { return $this->giaTaiThoiDiem; }
    public function getTrangThai(): string { return $this->trangThai; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
