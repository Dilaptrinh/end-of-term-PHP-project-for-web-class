<?php
namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\MemberService;

class MemberApiController
{
    use ApiResponseTrait;

    public function __construct(private MemberService $memberService)
    {
    }

    public function index(): void
    {
        Response::json(['success' => true, 'data' => $this->memberService->getAll()]);
    }

    public function show(string $id): void
    {
        $row = $this->memberService->getById((int) $id);
        if (!$row) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy hội viên.'], 404);
        }
        Response::json(['success' => true, 'data' => $row]);
    }

    public function store(): void
    {
        $this->respond($this->memberService->create(Request::json()), 'Đã tạo hội viên.', 201);
    }

    public function update(string $id): void
    {
        $this->respond($this->memberService->update((int) $id, Request::json()), 'Đã cập nhật hội viên.');
    }

    public function destroy(string $id): void
    {
        if (!$this->memberService->delete((int) $id)) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy hội viên.'], 404);
        }
        Response::json(['success' => true, 'message' => 'Đã xóa hội viên.']);
    }
}
