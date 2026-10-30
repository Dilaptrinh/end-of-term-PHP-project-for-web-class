<?php
namespace App\Models;

class GoiTap
{
    public function __construct(
        private ?int $id,
        private string $tenGoi,
        private int $thoiHanNgay,
        private int|float $gia,
        private ?string $moTa = null,
        private string $trangThai = 'dang_ban',
        private ?string $deletedAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getTenGoi(): string { return $this->tenGoi; }
    public function getThoiHanNgay(): int { return $this->thoiHanNgay; }
    public function getGia(): int|float { return $this->gia; }
    public function getMoTa(): ?string { return $this->moTa; }
    public function getTrangThai(): string { return $this->trangThai; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
