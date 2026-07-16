<?php
// =====================================================
// POS SYSTEM - JWT AUTHENTICATION CLASS
// =====================================================

class JWTAuth {
    private $secret_key;
    private $algorithm = 'HS256';
    private $exp_time = 86400; // 24 hours

    public function __construct() {
        $this->secret_key = getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production';
    }

    /**
     * Generate JWT Token
     */
    public function generateToken($user_id, $email, $role) {
        $issuedAt = time();
        $expire = $issuedAt + $this->exp_time;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $user_id,
            'email' => $email,
            'role' => $role
        ];

        return $this->encode($payload);
    }

    /**
     * Verify and Decode Token
     */
    public function verifyToken($token) {
        try {
            $payload = $this->decode($token);
            
            if ($payload['exp'] < time()) {
                throw new Exception('Token Expired');
            }

            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Encode JWT
     */
    private function encode($payload) {
        $header = $this->base64urlEncoded(json_encode(['alg' => $this->algorithm, 'typ' => 'JWT']));
        $payload = $this->base64urlEncoded(json_encode($payload));
        $signature = $this->base64urlEncoded(
            hash_hmac('sha256', "$header.$payload", $this->secret_key, true)
        );

        return "$header.$payload.$signature";
    }

    /**
     * Decode JWT
     */
    private function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($header, $payload, $signature) = $parts;

        $expected_signature = $this->base64urlEncoded(
            hash_hmac('sha256', "$header.$payload", $this->secret_key, true)
        );

        if ($signature !== $expected_signature) {
            throw new Exception('Invalid signature');
        }

        return json_decode($this->base64urlDecode($payload), true);
    }

    /**
     * Base64 URL Encode
     */
    private function base64urlEncoded($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL Decode
     */
    private function base64urlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Get Token from Headers
     */
    public static function getTokenFromHeaders() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
