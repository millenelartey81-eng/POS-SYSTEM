<?php
// =====================================================
// POS SYSTEM - USER MODEL
// =====================================================

class User {
    private $db;
    private $table = 'users';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT id, uuid, first_name, last_name, email, phone, role, status, profile_image, created_at 
                  FROM {$this->table} WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Create new user
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (uuid, first_name, last_name, email, phone, password_hash, role, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->generateUUID(),
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'] ?? null,
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'] ?? 'STAFF',
            $data['status'] ?? 'ACTIVE'
        ]);
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $query = "UPDATE {$this->table} SET ";
        $params = [];

        $allowed_fields = ['first_name', 'last_name', 'phone', 'role', 'status', 'profile_image'];
        $updates = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $query .= implode(', ', $updates) . ", updated_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        $params[] = $id;

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Verify password
     */
    public function verifyPassword($plain_password, $hash) {
        return password_verify($plain_password, $hash);
    }

    /**
     * Get all users
     */
    public function getAll($filters = []) {
        $query = "SELECT id, uuid, first_name, last_name, email, phone, role, status, created_at 
                  FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['role'])) {
            $query .= " AND role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Delete user (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        $query = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Generate UUID
     */
    private function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
