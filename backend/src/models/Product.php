<?php
// =====================================================
// POS SYSTEM - PRODUCT MODEL
// =====================================================

class Product {
    private $db;
    private $table = 'products';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get product by ID
     */
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table} p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.id = ? AND p.deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get product by SKU
     */
    public function getBySku($sku) {
        $query = "SELECT * FROM {$this->table} WHERE sku = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sku]);
        return $stmt->fetch();
    }

    /**
     * Get product by barcode
     */
    public function getByBarcode($barcode) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table} p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.barcode = ? AND p.deleted_at IS NULL AND p.is_active = TRUE";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$barcode]);
        return $stmt->fetch();
    }

    /**
     * Get all products with filters
     */
    public function getAll($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table} p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.deleted_at IS NULL";
        $params = [];

        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['is_active'])) {
            $query .= " AND p.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
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

        if (!empty($filters['category_id'])) {
            $query .= " AND category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['is_active'])) {
            $query .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Create product
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table}
                  (uuid, sku, name, description, category_id, unit_price, cost_price, 
                   quantity_in_stock, reorder_level, reorder_quantity, image, barcode, tax_rate)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->generateUUID(),
            $data['sku'],
            $data['name'],
            $data['description'] ?? null,
            $data['category_id'],
            $data['unit_price'],
            $data['cost_price'] ?? null,
            $data['quantity_in_stock'] ?? 0,
            $data['reorder_level'] ?? 10,
            $data['reorder_quantity'] ?? 50,
            $data['image'] ?? null,
            $data['barcode'] ?? null,
            $data['tax_rate'] ?? 0
        ]);
    }

    /**
     * Update product
     */
    public function update($id, $data) {
        $allowed_fields = ['name', 'description', 'category_id', 'unit_price', 'cost_price',
                          'reorder_level', 'reorder_quantity', 'image', 'barcode', 'is_active', 'tax_rate'];
        
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
     * Delete product (soft delete)
     */
    public function delete($id) {
        $query = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts() {
        $query = "SELECT p.*, c.name as category_name,
                         (p.reorder_level - p.quantity_in_stock) as quantity_needed
                  FROM {$this->table} p
                  JOIN categories c ON p.category_id = c.id
                  WHERE p.quantity_in_stock <= p.reorder_level
                  AND p.is_active = TRUE
                  AND p.deleted_at IS NULL
                  ORDER BY p.quantity_in_stock ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
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
