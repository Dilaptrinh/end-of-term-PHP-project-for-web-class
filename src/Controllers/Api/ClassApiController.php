<?php
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\ClassService;

class ClassApiController
{
    use ApiResponseTrait;

    public function __construct(private ClassService $classService)
    {
    }

    public function index(): void
    {
        Response::json(['success' => true, 'data' => $this->classService->getAll()]);
    }

    public function show(string $id): void
    {
        $row = $this->classService->getById((int) $id);
        if (!$row) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
        }
        Response::json(['success' => true, 'data' => $row]);
    }

    public function store(): void
    {
        $this->respond($this->classService->create(Request::json()), 'Đã tạo lớp học.', 201);
    }

    public function update(string $id): void
    {
        $this->respond($this->classService->update((int) $id, Request::json()), 'Đã cập nhật lớp học.');
    }

    public function destroy(string $id): void
    {
        if (!$this->classService->delete((int) $id)) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy lớp học.'], 404);
        }
        Response::json(['success' => true, 'message' => 'Đã xóa lớp học.']);
    }
}
