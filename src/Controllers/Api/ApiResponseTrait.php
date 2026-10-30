<?php
namespace App\Controllers\Api;

use App\Core\Response;

trait ApiResponseTrait
{
    protected function respond(array $result, string $message, int $okStatus = 200, mixed $data = null): void
    {
        if (!empty($result['not_found'])) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy dữ liệu.'], 404);
        }
        if (!empty($result['forbidden'])) {
            Response::json(['success' => false, 'message' => 'Bạn không có quyền thực hiện.'], 403);
        }
        if (empty($result['ok'])) {
            Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $result['errors'] ?? [],
            ], 422);
        }

        Response::json([
            'success' => true,
            'message' => $message,
            'data'    => $data ?? ($result['id'] ?? null),
        ], $okStatus);
    }
}
