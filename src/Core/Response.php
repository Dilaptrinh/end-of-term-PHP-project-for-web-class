<?php
namespace App\Core;

/**
 * Response
 * ------------------------------------------------------------------
 * Trả JSON theo đúng quy ước API của dự án:
 *   { "success": true,  "data": ..., "message": "..." }
 *   { "success": false, "message": "...", "errors": {...} }
 */
class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }
}
