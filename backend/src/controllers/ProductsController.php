<?php
// =====================================================
// POS SYSTEM - PRODUCTS CONTROLLER
// =====================================================

class ProductsController {
    private $db;
    private $product_model;

    public function __construct($db) {
        $this->db = $db;
        $this->product_model = new Product($db);
    }

    /**
     * Get all products
     */
    public function getAll() {
        try {
            new AuthMiddleware();

            $filters = [
                'category_id' => $_GET['category_id'] ?? null,
                'search' => $_GET['search'] ?? null,
                'is_active' => $_GET['is_active'] ?? true
            ];

            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;

            $products = $this->product_model->getAll($filters, $page, $limit);
            $total = $this->product_model->getCount($filters);

            Response::success([
                'data' => $products,
                'page' => $page,
                'limit' => $limit,
                'total' => $total
            ], 'Products retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get product by ID
     */
    public function getById($id) {
        try {
            new AuthMiddleware();

            $product = $this->product_model->getById($id);

            if (!$product) {
                Response::notFound('Product not found');
            }

            Response::success($product, 'Product retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get product by barcode
     */
    public function getByBarcode($barcode) {
        try {
            new AuthMiddleware();

            $product = $this->product_model->getByBarcode($barcode);

            if (!$product) {
                Response::notFound('Product not found');
            }

            Response::success($product, 'Product retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Create new product
     */
    public function create() {
        try {
            $auth = new AuthMiddleware();
            AuthMiddleware::checkRole(['ADMIN', 'MANAGER']);

            $data = json_decode(file_get_contents("php://input"), true);

            // Validation
            $validator = new Validator();
            if (!$validator->validate($data, [
                'sku' => 'required',
                'name' => 'required',
                'category_id' => 'required|numeric',
                'unit_price' => 'required|numeric'
            ])) {
                Response::validationError($validator->getErrors());
            }

            if ($this->product_model->create($data)) {
                Response::success([], 'Product created successfully', 201);
            } else {
                Response::error('Failed to create product', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Update product
     */
    public function update($id) {
        try {
            $auth = new AuthMiddleware();
            AuthMiddleware::checkRole(['ADMIN', 'MANAGER']);

            $product = $this->product_model->getById($id);
            if (!$product) {
                Response::notFound('Product not found');
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if ($this->product_model->update($id, $data)) {
                Response::success([], 'Product updated successfully');
            } else {
                Response::error('Failed to update product', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Delete product
     */
    public function delete($id) {
        try {
            $auth = new AuthMiddleware();
            AuthMiddleware::checkRole(['ADMIN']);

            $product = $this->product_model->getById($id);
            if (!$product) {
                Response::notFound('Product not found');
            }

            if ($this->product_model->delete($id)) {
                Response::success([], 'Product deleted successfully');
            } else {
                Response::error('Failed to delete product', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStock() {
        try {
            new AuthMiddleware();

            $products = $this->product_model->getLowStockProducts();

            Response::success($products, 'Low stock products retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }
}
