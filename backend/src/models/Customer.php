<?php
// =====================================================
// POS SYSTEM - CUSTOMER MODEL
// =====================================================

class Customer {
    private $db;
    private $table = 'customers';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get customer by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get customer by phone
     */
    public function getByPhone($phone) {
        $query = "SELECT * FROM {$this->table} WHERE phone = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }

    /**
     * Get all customers
     */
    public function getAll($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['customer_type'])) {
            $query .= " AND customer_type = ?";
            $params[] = $filters['customer_type'];
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get total count
     */
    public function getCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Create customer
     */
    public function create($data) {
        $customer_code = $this->generateCustomerCode();

        $query = "INSERT INTO {$this->table}
                  (uuid, customer_code, first_name, last_name, email, phone, address, city, state, postal_code, country, customer_type)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->generateUUID(),
            $customer_code,
            $data['first_name'],
            $data['last_name'],
            $data['email'] ?? null,
            $data['phone'],
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? null,
            $data['customer_type'] ?? 'WALK_IN'
        ]);
    }

    /**
     * Update customer
     */
    public function update($id, $data) {
        $allowed_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 
                          'state', 'postal_code', 'country', 'customer_type', 'is_active'];
        
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        $params[] = $id;

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Delete customer (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Add loyalty points
     */
    public function addLoyaltyPoints($id, $points) {
        $query = "UPDATE {$this->table} SET loyalty_points = loyalty_points + ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$points, $id]);
    }

    /**
     * Generate customer code
     */
    private function generateCustomerCode() {
        $prefix = 'CUST';
        $timestamp = substr(time(), -6);
        $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
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
