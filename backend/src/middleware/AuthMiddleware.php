<?php
// =====================================================
// POS SYSTEM - AUTH MIDDLEWARE
// =====================================================

class AuthMiddleware {
    private $jwt;
    private $allowed_routes = [
        'POST /api/auth/login',
        'POST /api/auth/register',
        'GET /api/health'
    ];

    public function __construct() {
        $this->jwt = new JWTAuth();
    }

    /**
     * Check if route is allowed without authentication
     */
    public function isPublicRoute($method, $path) {
        $route = "$method $path";
        foreach ($this->allowed_routes as $allowed) {
            if ($allowed === $route || strpos($allowed, '*') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verify authentication
     */
    public function verify() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/api', '', $path);

        if ($this->isPublicRoute($method, $path)) {
            return true;
        }

        $token = JWTAuth::getTokenFromHeaders();

        if (!$token) {
            Response::unauthorized('Token not provided');
        }

        $payload = $this->jwt->verifyToken($token);

        if (!$payload) {
            Response::unauthorized('Invalid or expired token');
        }

        return $payload;
    }

    /**
     * Check user role
     */
    public static function checkRole($required_roles) {
        $token = JWTAuth::getTokenFromHeaders();
        $jwt = new JWTAuth();
        $payload = $jwt->verifyToken($token);

        if (!$payload) {
            Response::unauthorized('Invalid token');
        }

        if (!in_array($payload['role'], $required_roles)) {
            Response::forbidden('You do not have permission to access this resource');
        }

        return $payload;
    }
}
