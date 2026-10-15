<?php
namespace App\Repositories;

/**
 * ProductRepository
 * ------------------------------------------------------------------
 * Ví dụ lớp con của BaseRepository. Bảng `product` cần có cột: id, name, price.
 * TODO: khi có class App\Models\Product, thay mapRow() để trả về new Product(...).
 */
class ProductRepository extends BaseRepository
{
    protected string $table = 'product';

    protected function mapRow(array $row): object
    {
        return (object) $row;
    }
}
