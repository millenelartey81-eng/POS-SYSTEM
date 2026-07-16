<?php
// =====================================================
// POS SYSTEM - RESPONSE HANDLER
// =====================================================

class Response {
    public static function success($data = [], $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error($message = 'Error', $data = [], $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function notFound($message = 'Resource not found') {
        self::error($message, [], 404);
    }

    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, [], 401);
    }

    public static function forbidden($message = 'Access Forbidden') {
        self::error($message, [], 403);
    }

    public static function validationError($errors) {
        self::error('Validation failed', $errors, 422);
    }
}
