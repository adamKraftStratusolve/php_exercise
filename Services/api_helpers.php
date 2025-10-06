<?php
require_once __DIR__ . '/cors_config.php';
class ApiResponse {
    public static function sendJson($data, $statusCode = 200) {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    public static function success($message, $statusCode = 200) {
        self::sendJson(['success' => $message], $statusCode);
    }

    public static function error($message, $statusCode = 400) {
        self::sendJson(['error' => $message], $statusCode);
    }

    public static function requirePostMethod() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::error('Only POST method is accepted.', 405);
        }
    }
}
