<?php
namespace App\Models;

class LopHoc
{
    public function __construct(
        private ?int $id,
        private string $tenLop,
        private int $giaoVienId,
        private int $thuTrongTuan,
        private string $gioBatDau,
        private string $gioKetThuc,
        private string $ngayBatDau,
        private string $ngayKetThuc,
        private int $sucChua,
        private ?string $phongHoc = null,
        private ?string $moTa = null,
        private string $trangThai = 'dang_mo',
        private ?string $deletedAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getTenLop(): string { return $this->tenLop; }
    public function getGiaoVienId(): int { return $this->giaoVienId; }
    public function getThuTrongTuan(): int { return $this->thuTrongTuan; }
    public function getGioBatDau(): string { return $this->gioBatDau; }
    public function getGioKetThuc(): string { return $this->gioKetThuc; }
    public function getNgayBatDau(): string { return $this->ngayBatDau; }
    public function getNgayKetThuc(): string { return $this->ngayKetThuc; }
    public function getSucChua(): int { return $this->sucChua; }
    public function getPhongHoc(): ?string { return $this->phongHoc; }
    public function getMoTa(): ?string { return $this->moTa; }
    public function getTrangThai(): string { return $this->trangThai; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
