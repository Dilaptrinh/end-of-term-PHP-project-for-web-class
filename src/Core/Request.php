<?php
namespace App\Core;

/**
 * Request
 * ------------------------------------------------------------------
 * Đọc dữ liệu request một cách an toàn:
 *   - Chỉ nhận body JSON (không nhận form-urlencoded cho API)
 *   - JSON sai cú pháp -> 400
 */
class Request
{
    public static function json(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== 0) {
            Response::json([
                'success' => false,
                'message' => 'Content-Type phải là application/json.',
            ], 415);
        }

        $raw = file_get_contents('php://input');

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            Response::json([
                'success' => false,
                'message' => 'Body JSON không hợp lệ.',
            ], 400);
        }

        if (!is_array($data)) {
            Response::json([
                'success' => false,
                'message' => 'Body JSON phải là một object.',
            ], 400);
        }

        return $data;
    }
}
