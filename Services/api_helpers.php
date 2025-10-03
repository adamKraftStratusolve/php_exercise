<?php
class ApiResponse {
    public static function sendJson($data, $statusCode = 200) {
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

function convertKeysToCamelCase($array) {
    $result = [];
    foreach ($array as $key => $value) {

        $newKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
        // Recursively convert keys in nested arrays
        if (is_array($value)) {
            $value = array_map('convertKeysToCamelCase', $value);
        }
        $result[$newKey] = $value;
    }
    return $result;
}
