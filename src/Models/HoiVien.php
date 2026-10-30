<?php
namespace App\Models;

class HoiVien
{
    public function __construct(
        private ?int $id,
        private ?int $taiKhoanId,
        private string $hoTen,
        private string $sdt,
        private string $email,
        private ?string $gioiTinh = null,
        private ?string $ngaySinh = null,
        private ?string $diaChi = null,
        private ?string $ghiChu = null,
        private string $trangThai = 'hoat_dong',
        private ?string $deletedAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getTaiKhoanId(): ?int { return $this->taiKhoanId; }
    public function getHoTen(): string { return $this->hoTen; }
    public function getSdt(): string { return $this->sdt; }
    public function getEmail(): string { return $this->email; }
    public function getGioiTinh(): ?string { return $this->gioiTinh; }
    public function getNgaySinh(): ?string { return $this->ngaySinh; }
    public function getDiaChi(): ?string { return $this->diaChi; }
    public function getGhiChu(): ?string { return $this->ghiChu; }
    public function getTrangThai(): string { return $this->trangThai; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
}
