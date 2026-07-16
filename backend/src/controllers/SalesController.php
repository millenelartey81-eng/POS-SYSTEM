<?php
// =====================================================
// POS SYSTEM - SALES CONTROLLER
// =====================================================

class SalesController {
    private $db;
    private $sale_model;
    private $product_model;

    public function __construct($db) {
        $this->db = $db;
        $this->sale_model = new Sale($db);
        $this->product_model = new Product($db);
    }

    /**
     * Create new sale
     */
    public function create() {
        try {
            $auth = new AuthMiddleware();
            $user = $auth->verify();

            $data = json_decode(file_get_contents("php://input"), true);

            // Validation
            $validator = new Validator();
            if (!$validator->validate($data, [
                'items' => 'required',
                'total_amount' => 'required|numeric',
                'payment_method' => 'required'
            ])) {
                Response::validationError($validator->getErrors());
            }

            $data['cashier_id'] = $user['user_id'];

            $sale_id = $this->sale_model->create($data);

            Response::success([
                'id' => $sale_id,
                'receipt_number' => 'RCP' . date('YmdHis') . rand(1000, 9999)
            ], 'Sale created successfully', 201);

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get sale details
     */
    public function getSale($id) {
        try {
            new AuthMiddleware();

            $sale = $this->sale_model->getById($id);

            if (!$sale) {
                Response::notFound('Sale not found');
            }

            Response::success($sale, 'Sale retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get all sales
     */
    public function getAll() {
        try {
            new AuthMiddleware();

            $filters = [
                'start_date' => $_GET['start_date'] ?? null,
                'end_date' => $_GET['end_date'] ?? null,
                'status' => $_GET['status'] ?? null,
                'payment_method' => $_GET['payment_method'] ?? null
            ];

            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;

            $sales = $this->sale_model->getAll($filters, $page, $limit);

            Response::success([
                'data' => $sales,
                'page' => $page,
                'limit' => $limit
            ], 'Sales retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Refund sale
     */
    public function refund($id) {
        try {
            $auth = new AuthMiddleware();
            AuthMiddleware::checkRole(['ADMIN', 'MANAGER']);

            $data = json_decode(file_get_contents("php://input"), true);

            $this->sale_model->refund($id, $data['reason'] ?? 'No reason provided');

            Response::success([], 'Sale refunded successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }

    /**
     * Get sales summary
     */
    public function getSummary() {
        try {
            new AuthMiddleware();

            $date_from = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $date_to = $_GET['end_date'] ?? date('Y-m-d');

            $summary = $this->sale_model->getSalesSummary($date_from, $date_to);

            Response::success($summary, 'Sales summary retrieved successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage(), [], 500);
        }
    }
}
