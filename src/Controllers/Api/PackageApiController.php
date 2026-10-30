<?php
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\PackageService;

class PackageApiController
{
    use ApiResponseTrait;

    public function __construct(private PackageService $packageService)
    {
    }

    public function index(): void
    {
        Response::json(['success' => true, 'data' => $this->packageService->getAll()]);
    }

    public function show(string $id): void
    {
        $row = $this->packageService->getById((int) $id);
        if (!$row) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy gói tập.'], 404);
        }
        Response::json(['success' => true, 'data' => $row]);
    }

    public function store(): void
    {
        $this->respond($this->packageService->create(Request::json()), 'Đã tạo gói tập.', 201);
    }

    public function update(string $id): void
    {
        $this->respond($this->packageService->update((int) $id, Request::json()), 'Đã cập nhật gói tập.');
    }

    public function destroy(string $id): void
    {
        if (!$this->packageService->delete((int) $id)) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy gói tập.'], 404);
        }
        Response::json(['success' => true, 'message' => 'Đã xóa gói tập.']);
    }
}
