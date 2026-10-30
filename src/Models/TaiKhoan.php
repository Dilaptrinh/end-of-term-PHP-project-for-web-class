<?php
namespace App\Models;

class TaiKhoan
{
    public function __construct(
        private int $id,
        private string $tenDangNhap,
        private string $hoTen,
        private int $roleId,
        private string $maVaiTro,
        private bool $trangThai,
        private ?string $lanDangNhapCuoi = null,
        private ?string $deletedAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenDangNhap(): string
    {
        return $this->tenDangNhap;
    }

    public function getHoTen(): string
    {
        return $this->hoTen;
    }

    public function getVaiTro(): string
    {
        return $this->maVaiTro;
    }

    public function isActive(): bool
    {
        return $this->trangThai;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getLastLoginAt(): ?string
    {
        return $this->lanDangNhapCuoi;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
