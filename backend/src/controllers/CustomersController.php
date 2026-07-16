<?php
// =====================================================
// POS SYSTEM - CUSTOMERS CONTROLLER
// =====================================================

class CustomersController {
    private $db;
    private $customer_model;

    public function __construct($db) {
        $this->db = $db;
        $this->customer_model = new Customer($db);
    }

    /**
     * Get all customers
     */
    public function getAll() {
        try {
            new AuthMiddleware();

            $filters = [
                'search' => $_GET['search'] ?? null,
                'customer_type' => $_GET['customer_type'] ?? null
            ];

            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;

            $customers = $this->customer_model->getAll($filters, $page, $limit);
            $total = $this->customer_model->getCount($filters);

            Response::success([
                'data' => $customers,
                'page' => $page,
                'limit' => $limit,
                'total' => $total
            ], 'Customers retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get customer by ID
     */
    public function getById($id) {
        try {
            new AuthMiddleware();

            $customer = $this->customer_model->getById($id);

            if (!$customer) {
                Response::notFound('Customer not found');
            }

            Response::success($customer, 'Customer retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Create new customer
     */
    public function create() {
        try {
            new AuthMiddleware();

            $data = json_decode(file_get_contents("php://input"), true);

            // Validation
            $validator = new Validator();
            if (!$validator->validate($data, [
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required'
            ])) {
                Response::validationError($validator->getErrors());
            }

            if ($this->customer_model->create($data)) {
                Response::success([], 'Customer created successfully', 201);
            } else {
                Response::error('Failed to create customer', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Update customer
     */
    public function update($id) {
        try {
            new AuthMiddleware();

            $customer = $this->customer_model->getById($id);
            if (!$customer) {
                Response::notFound('Customer not found');
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if ($this->customer_model->update($id, $data)) {
                Response::success([], 'Customer updated successfully');
            } else {
                Response::error('Failed to update customer', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Delete customer
     */
    public function delete($id) {
        try {
            $auth = new AuthMiddleware();
            AuthMiddleware::checkRole(['ADMIN', 'MANAGER']);

            $customer = $this->customer_model->getById($id);
            if (!$customer) {
                Response::notFound('Customer not found');
            }

            if ($this->customer_model->delete($id)) {
                Response::success([], 'Customer deleted successfully');
            } else {
                Response::error('Failed to delete customer', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Add loyalty points
     */
    public function addLoyaltyPoints($id) {
        try {
            $auth = new AuthMiddleware();
            AuthMiddleware::checkRole(['ADMIN', 'MANAGER']);

            $customer = $this->customer_model->getById($id);
            if (!$customer) {
                Response::notFound('Customer not found');
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['points'])) {
                Response::error('Points field is required', [], 400);
            }

            if ($this->customer_model->addLoyaltyPoints($id, $data['points'])) {
                Response::success([], 'Loyalty points added successfully');
            } else {
                Response::error('Failed to add loyalty points', [], 500);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }
}
