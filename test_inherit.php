<?php
/**
 * test_inherit.php  — Minh hoạ "lớp con ghi đè thuộc tính".
 * Chạy: C:\xampp\php\php.exe test_inherit.php
 */

// ---- LỚP CHA ----
class Cha
{
    protected string $table = 'chua_dat';   // giá trị mặc định

    public function xemBang(): void
    {
        // Đọc giá trị HIỆN TẠI của $table (của chính object đang gọi)
        echo "Cha đọc \$table = " . $this->table . PHP_EOL;
    }
}

// ---- LỚP CON 1 ----
class ConUser extends Cha
{
    protected string $table = 'user';       // GHI ĐÈ giá trị
}

// ---- LỚP CON 2 ----
class ConProduct extends Cha
{
    protected string $table = 'product';    // GHI ĐÈ giá trị khác
}

// ---- Chạy thử ----
$c1 = new ConUser();
$c1->xemBang();        // Cha đọc $table = user

$c2 = new ConProduct();
$c2->xemBang();        // Cha đọc $table = product

$c3 = new Cha();
$c3->xemBang();        // Cha đọc $table = chua_dat
