<?php
namespace App\Models;

class User
{
    // id cho phép null vì user MỚI chưa có id (MySQL tự cấp khi INSERT)
    private ?int $id;
    private string $name;
    private int $age;

    public function __construct(?int $id, string $name, int $age)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->age  = $age;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}
