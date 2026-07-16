<?php
// =====================================================
// POS SYSTEM - AUTH CONTROLLER
// =====================================================

class AuthController {
    private $db;
    private $user_model;
    private $jwt;

    public function __construct($db) {
        $this->db = $db;
        $this->user_model = new User($db);
        $this->jwt = new JWTAuth();
    }

    /**
     * User Login
     */
    public function login() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            // Validation
            $validator = new Validator();
            if (!$validator->validate($data, [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ])) {
                Response::validationError($validator->getErrors());
            }

            // Check user exists
            $user = $this->user_model->getByEmail($data['email']);
            if (!$user) {
                Response::error('Invalid email or password', [], 401);
            }

            // Check status
            if ($user['status'] !== 'ACTIVE') {
                Response::error('Account is ' . strtolower($user['status']), [], 403);
            }

            // Verify password
            if (!$this->user_model->verifyPassword($data['password'], $user['password_hash'])) {
                Response::error('Invalid email or password', [], 401);
            }

            // Update last login
            $this->user_model->updateLastLogin($user['id']);

            // Generate token
            $token = $this->jwt->generateToken($user['id'], $user['email'], $user['role']);

            Response::success([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'uuid' => $user['uuid'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'profile_image' => $user['profile_image']
                ]
            ], 'Login successful', 200);

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        try {
            $auth = new AuthMiddleware();
            $payload = $auth->verify();

            $user = $this->user_model->getById($payload['user_id']);

            if (!$user) {
                Response::notFound('User not found');
            }

            Response::success($user, 'User fetched successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken() {
        try {
            $auth = new AuthMiddleware();
            $payload = $auth->verify();

            $token = $this->jwt->generateToken($payload['user_id'], $payload['email'], $payload['role']);

            Response::success([
                'token' => $token
            ], 'Token refreshed successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword() {
        try {
            $auth = new AuthMiddleware();
            $payload = $auth->verify();

            $data = json_decode(file_get_contents("php://input"), true);

            // Validation
            $validator = new Validator();
            if (!$validator->validate($data, [
                'current_password' => 'required',
                'new_password' => 'required|min:6'
            ])) {
                Response::validationError($validator->getErrors());
            }

            $user = $this->user_model->getById($payload['user_id']);

            // Verify current password
            if (!$this->user_model->verifyPassword($data['current_password'], $user['password_hash'])) {
                Response::error('Current password is incorrect', [], 401);
            }

            // Update password
            $new_hash = password_hash($data['new_password'], PASSWORD_BCRYPT);
            $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$new_hash, $user['id']]);

            Response::success([], 'Password changed successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }
}
