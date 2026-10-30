<?php
namespace App\Models;

class GiaoVien
{
    public function __construct(
        private ?int $id,
        private ?int $taiKhoanId,
        private string $hoTen,
        private string $sdt,
        private string $email,
        private ?string $chuyenMon,
        private ?string $gioiThieu,
        private string $trangThai,
        private ?string $deletedAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
        private ?string $tenDangNhap = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getTaiKhoanId(): ?int { return $this->taiKhoanId; }
    public function getHoTen(): string { return $this->hoTen; }
    public function getSdt(): string { return $this->sdt; }
    public function getEmail(): string { return $this->email; }
    public function getChuyenMon(): ?string { return $this->chuyenMon; }
    public function getGioiThieu(): ?string { return $this->gioiThieu; }
    public function getTrangThai(): string { return $this->trangThai; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getTenDangNhap(): ?string { return $this->tenDangNhap; }
}
